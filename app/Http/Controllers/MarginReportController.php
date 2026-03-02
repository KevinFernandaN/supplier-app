<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarginReportController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    private function getDateRange(Request $request): array
    {
        $from = $request->query('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->query('to') ?: now()->toDateString();

        return [$from, $to];
    }

    /**
     * Core query: returns one row per sale_date (daily)
     * revenue = sum(qty * selling_price)
     * cogs = sum(qty_sold * recipe_qty * last_purchase_price_as_of_sale_date)
     */
    private function dailyRows(string $from, string $to, int $regionId)
    {
        // Subquery: last purchase price per product as-of each sale date
        // We use a correlated subquery for simplicity.
        // NOTE: This assumes purchase_price is comparable in the same unit as recipe (we'll add conversions later if needed).
        $rows = DB::table('sales_orders as so')
            ->join('sales_order_items as soi', 'soi.sales_order_id', '=', 'so.id')
            ->join('menus as m', 'm.id', '=', 'soi.menu_id')
            ->join('menu_recipes as mr', 'mr.menu_id', '=', 'm.id')
            ->where('so.region_id', $regionId)
            ->whereBetween('so.sale_date', [$from, $to])
            ->selectRaw('so.sale_date as sale_date')
            ->selectRaw('SUM(soi.qty * soi.selling_price) as revenue')
            ->selectRaw('
                SUM(
                    soi.qty * mr.qty * (
                        SELECT poi.purchase_price * COALESCE(
                            (SELECT uc.multiplier FROM unit_conversions uc
                             WHERE uc.from_unit_id = mr.unit_id AND uc.to_unit_id = poi.unit_id
                             LIMIT 1),
                            IF(mr.unit_id = poi.unit_id, 1.0, NULL)
                        )
                        FROM purchase_order_items poi
                        JOIN purchase_orders po ON po.id = poi.purchase_order_id
                        WHERE po.region_id = so.region_id
                          AND poi.product_id = mr.product_id
                          AND po.order_date <= so.sale_date
                        ORDER BY po.order_date DESC, poi.id DESC
                        LIMIT 1
                    )
                ) as cogs
            ')
            ->groupBy('so.sale_date')
            ->orderBy('so.sale_date', 'asc')
            ->get();

        return $rows;
    }

    public function daily(Request $request)
    {
        $regionId = $this->currentRegionId();
        list($from, $to) = $this->getDateRange($request);

        $rows = $this->dailyRows($from, $to, $regionId)
            ->map(function ($r) {
                $r->margin = (float)$r->revenue - (float)$r->cogs;
                $r->margin_pct = ((float)$r->revenue > 0)
                    ? ($r->margin / (float)$r->revenue) * 100
                    : 0;
                return $r;
            });

        return view('reports.margin_daily', compact('rows', 'from', 'to'));
    }

    public function monthly(Request $request)
    {
        $regionId = $this->currentRegionId();
        list($from, $to) = $this->getDateRange($request);

        // Build daily first, then aggregate to month in PHP (simple, reliable)
        $daily = $this->dailyRows($from, $to, $regionId);

        $monthly = collect($daily)->groupBy(function ($r) {
            return Carbon::parse($r->sale_date)->format('Y-m');
        })->map(function ($group, $month) {
            $revenue = $group->sum(function ($r) { return (float)$r->revenue; });
            $cogs = $group->sum(function ($r) { return (float)$r->cogs; });
            $margin = $revenue - $cogs;
            $marginPct = $revenue > 0 ? ($margin / $revenue) * 100 : 0;

            return (object)[
                'month' => $month,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'margin' => $margin,
                'margin_pct' => $marginPct,
            ];
        })->values();

        return view('reports.margin_monthly', compact('monthly', 'from', 'to'));
    }

    public function exportDailyCsv(Request $request): StreamedResponse
    {
        $regionId = $this->currentRegionId();
        list($from, $to) = $this->getDateRange($request);

        $rows = $this->dailyRows($from, $to, $regionId);

        $filename = "margin_daily_{$from}_to_{$to}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['sale_date', 'revenue', 'cogs', 'margin', 'margin_pct']);

            foreach ($rows as $r) {
                $revenue = (float)$r->revenue;
                $cogs = (float)$r->cogs;
                $margin = $revenue - $cogs;
                $marginPct = $revenue > 0 ? ($margin / $revenue) * 100 : 0;

                fputcsv($out, [$r->sale_date, $revenue, $cogs, $margin, round($marginPct, 2)]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportMonthlyCsv(Request $request): StreamedResponse
    {
        $regionId = $this->currentRegionId();
        list($from, $to) = $this->getDateRange($request);

        $daily = $this->dailyRows($from, $to, $regionId);

        $monthly = collect($daily)->groupBy(function ($r) {
            return Carbon::parse($r->sale_date)->format('Y-m');
        })->map(function ($group, $month) {
            $revenue = $group->sum(function ($r) { return (float)$r->revenue; });
            $cogs = $group->sum(function ($r) { return (float)$r->cogs; });
            $margin = $revenue - $cogs;
            $marginPct = $revenue > 0 ? ($margin / $revenue) * 100 : 0;

            return [$month, $revenue, $cogs, $margin, round($marginPct, 2)];
        })->values();

        $filename = "margin_monthly_{$from}_to_{$to}.csv";

        return response()->streamDownload(function () use ($monthly) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['month', 'revenue', 'cogs', 'margin', 'margin_pct']);

            foreach ($monthly as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
