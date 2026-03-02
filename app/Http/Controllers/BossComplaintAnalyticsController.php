<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BossComplaintAnalyticsController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index(Request $request)
    {
        $regionId = $this->currentRegionId();

        // Filters (default: this month)
        $from = $request->query('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->query('to') ?: now()->toDateString();

        $supplierId = $request->query('supplier_id');
        $productId  = $request->query('product_id');
        $status     = $request->query('status'); // open/resolved (optional)

        // Dropdown data
        $suppliers = DB::table('suppliers')->where('region_id', $regionId)->orderBy('name')->get();
        $products  = DB::table('products')->orderBy('name')->get();

        // Base query
        $base = DB::table('complaints as c')
            ->where('c.region_id', $regionId)
            ->whereBetween('c.complaint_date', [$from, $to]);

        if ($supplierId) $base->where('c.supplier_id', $supplierId);
        if ($productId)  $base->where('c.product_id', $productId);
        if ($status)     $base->where('c.status', $status);

        // Summary cards
        $totalComplaints = (clone $base)->count();
        $avgSeverity = (float) ((clone $base)->avg('c.severity') ?? 0);
        $openCount = (clone $base)->where('c.status', 'open')->count();
        $resolvedCount = (clone $base)->where('c.status', 'resolved')->count();

        // Top products
        $topProducts = (clone $base)
            ->join('products as p', 'p.id', '=', 'c.product_id')
            ->selectRaw('c.product_id, p.name as product_name, COUNT(*) as complaint_count, AVG(c.severity) as avg_severity')
            ->groupBy('c.product_id', 'p.name')
            ->orderByDesc('complaint_count')
            ->limit(10)
            ->get();

        // Top suppliers
        $topSuppliers = (clone $base)
            ->join('suppliers as s', 's.id', '=', 'c.supplier_id')
            ->selectRaw('c.supplier_id, s.name as supplier_name, COUNT(*) as complaint_count, AVG(c.severity) as avg_severity')
            ->groupBy('c.supplier_id', 's.name')
            ->orderByDesc('complaint_count')
            ->limit(10)
            ->get();

        // Complaint categories breakdown (complaint_type)
        $byComplaintType = (clone $base)
            ->selectRaw('c.complaint_type, COUNT(*) as cnt, AVG(c.severity) as avg_sev')
            ->groupBy('c.complaint_type')
            ->orderByDesc('cnt')
            ->get();

        // Daily trend (count + avg severity)
        $dailyTrend = (clone $base)
            ->selectRaw('c.complaint_date as d, COUNT(*) as cnt, AVG(c.severity) as avg_sev')
            ->groupBy('c.complaint_date')
            ->orderBy('c.complaint_date')
            ->get();

        // Latest complaints list (actionable)
        $latestComplaints = (clone $base)
            ->join('suppliers as s', 's.id', '=', 'c.supplier_id')
            ->join('products as p', 'p.id', '=', 'c.product_id')
            ->selectRaw('c.id, c.complaint_date, c.status, c.severity, c.complaint_type, c.type, c.description, c.resolved_at,
                        s.name as supplier_name, p.name as product_name')
            ->orderBy('c.complaint_date', 'desc')
            ->orderBy('c.id', 'desc')
            ->limit(25)
            ->get();

        $chartLabels = $dailyTrend->pluck('d')->values();
        $chartCounts = $dailyTrend->pluck('cnt')->map(fn($v) => (int)$v)->values();
        $chartAvgSev = $dailyTrend->pluck('avg_sev')->map(fn($v) => round((float)$v, 2))->values();

        return view('boss.complaints_analytics', compact(
            'from','to','supplierId','productId','status',
            'suppliers','products',
            'totalComplaints','avgSeverity','openCount','resolvedCount',
            'topProducts','topSuppliers','byComplaintType',
            'chartLabels','chartCounts','chartAvgSev',
            'latestComplaints'
        ));
    }

    public function export(Request $request)
    {
        $regionId = $this->currentRegionId();

        $from = $request->query('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->query('to') ?: now()->toDateString();

        $supplierId = $request->query('supplier_id');
        $productId  = $request->query('product_id');
        $status     = $request->query('status');

        $q = DB::table('complaints as c')
            ->join('suppliers as s', 's.id', '=', 'c.supplier_id')
            ->join('products as p', 'p.id', '=', 'c.product_id')
            ->where('c.region_id', $regionId)
            ->whereBetween('c.complaint_date', [$from, $to])
            ->select([
                'c.id',
                'c.complaint_date',
                's.name as supplier',
                'p.name as product',
                'c.complaint_type',
                'c.type',
                'c.severity',
                'c.qty',
                'c.status',
                'c.description',
                'c.resolved_at',
                'c.created_at',
            ])
            ->orderBy('c.complaint_date', 'asc')
            ->orderBy('c.id', 'asc');

        if ($supplierId) $q->where('c.supplier_id', $supplierId);
        if ($productId)  $q->where('c.product_id', $productId);
        if ($status)     $q->where('c.status', $status);

        $rows = $q->get();

        $filename = "complaints_{$from}_to_{$to}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // Header row
            fputcsv($out, [
                'id','complaint_date','supplier','product','complaint_type','type',
                'severity','qty','status','description','resolved_at','created_at'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->complaint_date,
                    $r->supplier,
                    $r->product,
                    $r->complaint_type,
                    $r->type,
                    $r->severity,
                    $r->qty,
                    $r->status,
                    $r->description,
                    $r->resolved_at,
                    $r->created_at,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
