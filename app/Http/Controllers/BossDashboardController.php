<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BossDashboardController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index(Request $request)
    {
        $regionId = $this->currentRegionId();

        $from = $request->query('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->query('to') ?: now()->toDateString();

        $month = substr($to, 0, 7); // use end date month as current month KPI

        // Top Suppliers
        $topSuppliers = DB::table('supplier_kpi_monthly as k')
            ->join('suppliers as s', 's.id', '=', 'k.supplier_id')
            ->where('k.region_id', $regionId)
            ->where('k.month', $month)
            ->orderBy('k.kpi_score', 'desc')
            ->limit(5)
            ->select('s.name', 'k.kpi_score', 'k.review_count')
            ->get();

        // Top returned products (in same date range as dashboard)
        $topReturnedProducts = DB::table('returns as r')
            ->join('return_items as ri', 'ri.return_id', '=', 'r.id')
            ->join('products as p', 'p.id', '=', 'ri.product_id')
            ->where('r.region_id', $regionId)
            ->whereBetween('r.return_date', [$from, $to])
            ->selectRaw('p.name as product_name, SUM(ri.qty) as total_qty, COUNT(DISTINCT r.id) as order_count')
            ->groupBy('p.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Top complained products (in same date range as dashboard)
        $topComplainedProducts = DB::table('complaints as c')
            ->join('products as p', 'p.id', '=', 'c.product_id')
            ->where('c.region_id', $regionId)
            ->whereBetween('c.complaint_date', [$from, $to])
            ->selectRaw('p.name as product_name, COUNT(*) as complaint_count, AVG(c.severity) as avg_severity')
            ->groupBy('p.name')
            ->orderByDesc('complaint_count')
            ->limit(5)
            ->get();

        /**
         * 1) DAILY REVENUE (grouped by sale_date)
         */
        $revenueDaily = DB::table('sales_orders as so')
            ->join('sales_order_items as soi', 'soi.sales_order_id', '=', 'so.id')
            ->where('so.region_id', $regionId)
            ->whereBetween('so.sale_date', [$from, $to])
            ->groupBy('so.sale_date')
            ->orderBy('so.sale_date', 'asc')
            ->selectRaw('so.sale_date as sale_date')
            ->selectRaw('SUM(soi.qty * soi.selling_price) as revenue')
            ->get()
            ->keyBy('sale_date');

        /**
         * 2) DAILY USED QTY PER PRODUCT (grouped by sale_date + product_id)
         * used_qty = SUM(sold_menu_qty * recipe_qty)
         */
        $usedPerProductDaily = DB::table('sales_orders as so')
            ->join('sales_order_items as soi', 'soi.sales_order_id', '=', 'so.id')
            ->join('menu_recipes as mr', 'mr.menu_id', '=', 'soi.menu_id')
            ->where('so.region_id', $regionId)
            ->whereBetween('so.sale_date', [$from, $to])
            ->groupBy('so.sale_date', 'mr.product_id')
            ->selectRaw('so.sale_date as sale_date')
            ->selectRaw('mr.product_id as product_id')
            ->selectRaw('SUM(soi.qty * mr.qty) as used_qty')
            ->get();

        /**
         * 3) For each (sale_date, product_id), lookup LPP as-of sale_date
         * and accumulate COGS by day.
         */
        $cogsByDate = [];
        foreach ($usedPerProductDaily as $row) {
            $saleDate  = $row->sale_date;
            $productId = (int) $row->product_id;
            $usedQty   = (float) $row->used_qty;

            // LPP as-of saleDate
            $lpp = (float) DB::table('purchase_orders as po')
                ->join('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
                ->where('po.region_id', $regionId)
                ->where('poi.product_id', $productId)
                ->where('po.order_date', '<=', $saleDate)
                ->orderBy('po.order_date', 'desc')
                ->orderBy('poi.id', 'desc')
                ->value('poi.purchase_price');

            // If no purchase price exists yet, treat as 0 (MVP)
            $lpp = $lpp ?: 0;

            $cogsByDate[$saleDate] = ($cogsByDate[$saleDate] ?? 0) + ($usedQty * $lpp);
        }

        /**
         * 4) Build DAILY rows for chart/cards
         */
        $dailyRows = [];
        $dates = $revenueDaily->keys()->values();

        foreach ($dates as $d) {
            $revenue = (float) ($revenueDaily[$d]->revenue ?? 0);
            $cogs    = (float) ($cogsByDate[$d] ?? 0);
            $margin  = $revenue - $cogs;

            $dailyRows[] = (object) [
                'sale_date' => $d,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'margin' => $margin,
            ];
        }

        $totalRevenue = array_sum(array_map(function ($r) { return $r->revenue; }, $dailyRows));
        $totalCogs    = array_sum(array_map(function ($r) { return $r->cogs; }, $dailyRows));
        $totalMargin  = $totalRevenue - $totalCogs;
        $marginPct    = $totalRevenue > 0 ? ($totalMargin / $totalRevenue) * 100 : 0;

        /**
         * 5) Purchases total within range (PO total)
         */
        $purchaseTotal = (float) DB::table('purchase_orders as po')
            ->join('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->where('po.region_id', $regionId)
            ->whereBetween('po.order_date', [$from, $to])
            ->selectRaw('COALESCE(SUM(poi.qty * poi.purchase_price), 0) as total')
            ->value('total');

        /**
         * 6) TOP MENUS BY MARGIN (range)
         * Revenue by menu is easy.
         * COGS by menu must consider sale_date (LPP as-of each sale_date).
         */
        $menuRevenue = DB::table('sales_orders as so')
            ->join('sales_order_items as soi', 'soi.sales_order_id', '=', 'so.id')
            ->join('menus as m', 'm.id', '=', 'soi.menu_id')
            ->where('so.region_id', $regionId)
            ->whereBetween('so.sale_date', [$from, $to])
            ->groupBy('soi.menu_id', 'm.name')
            ->selectRaw('soi.menu_id as menu_id')
            ->selectRaw('m.name as menu_name')
            ->selectRaw('SUM(soi.qty * soi.selling_price) as revenue')
            ->get()
            ->keyBy('menu_id');

        // Used qty per (menu_id, sale_date, product_id)
        $menuUsed = DB::table('sales_orders as so')
            ->join('sales_order_items as soi', 'soi.sales_order_id', '=', 'so.id')
            ->join('menu_recipes as mr', 'mr.menu_id', '=', 'soi.menu_id')
            ->where('so.region_id', $regionId)
            ->whereBetween('so.sale_date', [$from, $to])
            ->groupBy('soi.menu_id', 'so.sale_date', 'mr.product_id')
            ->selectRaw('soi.menu_id as menu_id')
            ->selectRaw('so.sale_date as sale_date')
            ->selectRaw('mr.product_id as product_id')
            ->selectRaw('SUM(soi.qty * mr.qty) as used_qty')
            ->get();

        $menuCogs = []; // menu_id => cogs
        foreach ($menuUsed as $row) {
            $menuId    = (int) $row->menu_id;
            $saleDate  = $row->sale_date;
            $productId = (int) $row->product_id;
            $usedQty   = (float) $row->used_qty;

            $lpp = (float) DB::table('purchase_orders as po')
                ->join('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
                ->where('po.region_id', $regionId)
                ->where('poi.product_id', $productId)
                ->where('po.order_date', '<=', $saleDate)
                ->orderBy('po.order_date', 'desc')
                ->orderBy('poi.id', 'desc')
                ->value('poi.purchase_price');

            $lpp = $lpp ?: 0;

            $menuCogs[$menuId] = ($menuCogs[$menuId] ?? 0) + ($usedQty * $lpp);
        }

        $topMenus = [];
        foreach ($menuRevenue as $menuId => $mr) {
            $rev = (float) $mr->revenue;
            $cgs = (float) ($menuCogs[$menuId] ?? 0);
            $mar = $rev - $cgs;
            $pct = $rev > 0 ? ($mar / $rev) * 100 : 0;

            $topMenus[] = (object) [
                'menu_id' => $menuId,
                'menu_name' => $mr->menu_name,
                'revenue' => $rev,
                'cogs' => $cgs,
                'margin' => $mar,
                'margin_pct' => $pct,
            ];
        }

        usort($topMenus, function ($a, $b) { return $b->margin <=> $a->margin; });
        $topMenus = array_slice($topMenus, 0, 5);

        /**
         * Chart data
         */
        $chartLabels = array_map(function ($r) { return $r->sale_date; }, $dailyRows);
        $chartMargin = array_map(function ($r) { return $r->margin; }, $dailyRows);

        return view('boss.dashboard', compact(
            'from',
            'to',
            'totalRevenue',
            'totalCogs',
            'totalMargin',
            'marginPct',
            'purchaseTotal',
            'topMenus',
            'chartLabels',
            'chartMargin',
            'topSuppliers',
            'topComplainedProducts',
            'topReturnedProducts'
        ));
    }

    public function exportMarginCsv(Request $request)
    {
        $regionId = $this->currentRegionId();

        $from = $request->query('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->query('to') ?: now()->toDateString();

        // ====== SAME LOGIC as dashboard daily margin ======
        // 1) Revenue per day
        $revenueDaily = DB::table('sales_orders as so')
            ->join('sales_order_items as soi', 'soi.sales_order_id', '=', 'so.id')
            ->where('so.region_id', $regionId)
            ->whereBetween('so.sale_date', [$from, $to])
            ->groupBy('so.sale_date')
            ->orderBy('so.sale_date', 'asc')
            ->selectRaw('so.sale_date as sale_date')
            ->selectRaw('SUM(soi.qty * soi.selling_price) as revenue')
            ->get()
            ->keyBy('sale_date');

        // 2) Used qty per product per day
        $usedPerProductDaily = DB::table('sales_orders as so')
            ->join('sales_order_items as soi', 'soi.sales_order_id', '=', 'so.id')
            ->join('menu_recipes as mr', 'mr.menu_id', '=', 'soi.menu_id')
            ->where('so.region_id', $regionId)
            ->whereBetween('so.sale_date', [$from, $to])
            ->groupBy('so.sale_date', 'mr.product_id')
            ->selectRaw('so.sale_date as sale_date')
            ->selectRaw('mr.product_id as product_id')
            ->selectRaw('SUM(soi.qty * mr.qty) as used_qty')
            ->get();

        // 3) Build COGS by date using LPP as-of sale_date
        $cogsByDate = [];
        foreach ($usedPerProductDaily as $row) {
            $saleDate  = $row->sale_date;
            $productId = (int) $row->product_id;
            $usedQty   = (float) $row->used_qty;

            $lpp = (float) DB::table('purchase_orders as po')
                ->join('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
                ->where('po.region_id', $regionId)
                ->where('poi.product_id', $productId)
                ->where('po.order_date', '<=', $saleDate)
                ->orderBy('po.order_date', 'desc')
                ->orderBy('poi.id', 'desc')
                ->value('poi.purchase_price');

            $lpp = $lpp ?: 0;

            $cogsByDate[$saleDate] = ($cogsByDate[$saleDate] ?? 0) + ($usedQty * $lpp);
        }

        // 4) Create daily rows
        $dates = $revenueDaily->keys()->values();

        $rows = [];
        foreach ($dates as $d) {
            $revenue = (float) ($revenueDaily[$d]->revenue ?? 0);
            $cogs    = (float) ($cogsByDate[$d] ?? 0);
            $margin  = $revenue - $cogs;
            $pct     = $revenue > 0 ? ($margin / $revenue) * 100 : 0;

            $rows[] = [
                'date' => $d,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'margin' => $margin,
                'margin_pct' => round($pct, 2),
            ];
        }

        $filename = "margin_report_{$from}_to_{$to}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['date', 'revenue', 'cogs', 'margin', 'margin_pct']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['date'],
                    $r['revenue'],
                    $r['cogs'],
                    $r['margin'],
                    $r['margin_pct'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
