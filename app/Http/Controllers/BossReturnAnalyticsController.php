<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BossReturnAnalyticsController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index(Request $request)
    {
        $regionId = $this->currentRegionId();

        // Filters (default: last 3 months)
        $from = $request->query('from') ?: now()->subMonths(2)->startOfMonth()->toDateString();
        $to   = $request->query('to') ?: now()->toDateString();

        $supplierId = $request->query('supplier_id');
        $productId  = $request->query('product_id');

        // Dropdown data
        $suppliers = DB::table('suppliers')->where('region_id', $regionId)->orderBy('name')->get();
        $products  = DB::table('products')->orderBy('name')->get();

        // Base query on returns joined with return_items (FK: ri.return_id)
        $base = DB::table('returns as r')
            ->join('return_items as ri', 'ri.return_id', '=', 'r.id')
            ->where('r.region_id', $regionId)
            ->whereBetween('r.return_date', [$from, $to]);

        if ($supplierId) $base->where('r.supplier_id', $supplierId);
        if ($productId)  $base->where('ri.product_id', $productId);

        // Summary cards
        $totalOrders    = (clone $base)->distinct()->count('r.id');
        $totalQty       = (float) ((clone $base)->sum('ri.qty') ?? 0);
        $totalSuppliers = (clone $base)->distinct()->count('r.supplier_id');
        $totalProducts  = (clone $base)->distinct()->count('ri.product_id');

        // Top returned products (by qty)
        $topProducts = (clone $base)
            ->join('products as p', 'p.id', '=', 'ri.product_id')
            ->selectRaw('ri.product_id, p.name as product_name, SUM(ri.qty) as total_qty, COUNT(DISTINCT r.id) as order_count')
            ->groupBy('ri.product_id', 'p.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Top suppliers with returns (by qty)
        $topSuppliers = (clone $base)
            ->join('suppliers as s', 's.id', '=', 'r.supplier_id')
            ->selectRaw('r.supplier_id, s.name as supplier_name, SUM(ri.qty) as total_qty, COUNT(DISTINCT r.id) as order_count')
            ->groupBy('r.supplier_id', 's.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Monthly trend
        $monthlyTrend = (clone $base)
            ->selectRaw("DATE_FORMAT(r.return_date, '%Y-%m') as month, COUNT(DISTINCT r.id) as order_count, SUM(ri.qty) as total_qty")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Latest return orders
        $latestReturns = DB::table('returns as r')
            ->join('suppliers as s', 's.id', '=', 'r.supplier_id')
            ->leftJoin('return_items as ri', 'ri.return_id', '=', 'r.id')
            ->where('r.region_id', $regionId)
            ->whereBetween('r.return_date', [$from, $to])
            ->when($supplierId, function ($q) use ($supplierId) { return $q->where('r.supplier_id', $supplierId); })
            ->selectRaw('r.id, r.return_date, r.notes, s.name as supplier_name,
                        COUNT(ri.id) as item_count, COALESCE(SUM(ri.qty), 0) as total_qty')
            ->groupBy('r.id', 'r.return_date', 'r.notes', 's.name')
            ->orderBy('r.return_date', 'desc')
            ->orderBy('r.id', 'desc')
            ->limit(25)
            ->get();

        $chartLabels     = $monthlyTrend->pluck('month')->values();
        $chartOrderCount = $monthlyTrend->pluck('order_count')->map(function ($v) { return (int)$v; })->values();
        $chartQty        = $monthlyTrend->pluck('total_qty')->map(function ($v) { return (float)$v; })->values();

        return view('boss.return_analytics', compact(
            'from', 'to', 'supplierId', 'productId',
            'suppliers', 'products',
            'totalOrders', 'totalQty', 'totalSuppliers', 'totalProducts',
            'topProducts', 'topSuppliers',
            'chartLabels', 'chartOrderCount', 'chartQty',
            'latestReturns'
        ));
    }

    public function export(Request $request)
    {
        $regionId = $this->currentRegionId();

        $from = $request->query('from') ?: now()->subMonths(2)->startOfMonth()->toDateString();
        $to   = $request->query('to') ?: now()->toDateString();

        $supplierId = $request->query('supplier_id');
        $productId  = $request->query('product_id');

        $q = DB::table('returns as r')
            ->join('return_items as ri', 'ri.return_id', '=', 'r.id')
            ->join('suppliers as s', 's.id', '=', 'r.supplier_id')
            ->join('products as p', 'p.id', '=', 'ri.product_id')
            ->join('units as u', 'u.id', '=', 'ri.unit_id')
            ->where('r.region_id', $regionId)
            ->whereBetween('r.return_date', [$from, $to])
            ->select([
                'r.id as return_id',
                'r.return_date',
                's.name as supplier',
                'p.name as product',
                'ri.qty',
                'u.symbol as unit',
                'r.notes',
                'r.created_at',
            ])
            ->orderBy('r.return_date', 'asc')
            ->orderBy('r.id', 'asc');

        if ($supplierId) $q->where('r.supplier_id', $supplierId);
        if ($productId)  $q->where('ri.product_id', $productId);

        $rows = $q->get();

        $filename = "returns_{$from}_to_{$to}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['return_id', 'return_date', 'supplier', 'product', 'qty', 'unit', 'notes', 'created_at']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->return_id,
                    $r->return_date,
                    $r->supplier,
                    $r->product,
                    $r->qty,
                    $r->unit,
                    $r->notes,
                    $r->created_at,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
