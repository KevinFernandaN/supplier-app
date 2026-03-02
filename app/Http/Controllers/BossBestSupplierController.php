<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BossBestSupplierController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index(Request $request)
    {
        $regionId = $this->currentRegionId();

        // Filters
        $productId = $request->query('product_id');
        $month = $request->query('month') ?: now()->format('Y-m'); // matches supplier_kpi_monthly.month char(7)
        $minKpi = (float) ($request->query('min_kpi') ?: 4.00);
        $maxPremiumPct = (float) ($request->query('max_premium_pct') ?: 20); // % above cheapest eligible

        $products = DB::table('products')->orderBy('name')->get();

        // If no product chosen yet, show page with filters only
        if (!$productId) {
            return view('boss.best_suppliers', compact('products', 'productId', 'month', 'minKpi', 'maxPremiumPct'))
                ->with([
                    'rows' => collect(),
                    'eligible' => collect(),
                    'recommended' => null,
                    'cheapestEligiblePrice' => null,
                    'priceCap' => null,
                ]);
        }

        /**
         * Latest price per supplier_product (MVP approach):
         * - pick newest row by MAX(id) per supplier_product_id
         * If you have effective_date and want that instead, tell me and we’ll switch.
         */
        $latestPriceSub = DB::table('supplier_product_prices')
            ->selectRaw('supplier_product_id, MAX(id) as latest_id')
            ->groupBy('supplier_product_id');

        $rows = DB::table('supplier_products as sp')
            ->join('suppliers as s', 's.id', '=', 'sp.supplier_id')
            ->join('products as p', 'p.id', '=', 'sp.product_id')
            ->leftJoinSub($latestPriceSub, 'lp', function ($join) {
                $join->on('lp.supplier_product_id', '=', 'sp.id');
            })
            ->leftJoin('supplier_product_prices as spp', 'spp.id', '=', 'lp.latest_id')
            ->leftJoin('supplier_kpi_monthly as kpi', function ($join) use ($regionId, $month) {
                $join->on('kpi.supplier_id', '=', 's.id')
                    ->where('kpi.region_id', '=', $regionId)
                    ->where('kpi.month', '=', $month);
            })
            ->where('sp.product_id', $productId)
            ->where('sp.is_active', 1)
            ->where('s.region_id', $regionId)
            ->selectRaw('
                s.id as supplier_id,
                s.name as supplier_name,
                p.id as product_id,
                p.name as product_name,
                spp.price as latest_price,
                kpi.kpi_score as kpi_score,
                kpi.review_count as review_count,
                kpi.complaint_penalty as complaint_penalty,
                kpi.return_penalty as return_penalty,
                kpi.return_count as return_count
            ')
            ->orderBy('s.name')
            ->get()
            ->map(function ($r) {
                $r->latest_price = $r->latest_price !== null ? (float) $r->latest_price : null;
                $r->kpi_score = $r->kpi_score !== null ? (float) $r->kpi_score : null;
                $r->review_count = $r->review_count !== null ? (int) $r->review_count : 0;
                $r->complaint_penalty = $r->complaint_penalty !== null ? (float) $r->complaint_penalty : 0.0;
                $r->return_penalty = $r->return_penalty !== null ? (float) $r->return_penalty : 0.0;
                $r->return_count = $r->return_count !== null ? (int) $r->return_count : 0;
                return $r;
            });

        // Eligible: must have price + KPI and meet KPI threshold
        $eligible = $rows->filter(function ($r) use ($minKpi) {
            return $r->latest_price !== null
                && $r->kpi_score !== null
                && $r->kpi_score >= $minKpi;
        })->values();

        $cheapestEligiblePrice = $eligible->min('latest_price');
        $priceCap = null;

        if ($cheapestEligiblePrice !== null) {
            $priceCap = $cheapestEligiblePrice * (1 + ($maxPremiumPct / 100));
        }

        // Apply price cap (optional “don’t pick overpriced”)
        $eligibleCapped = $eligible;
        if ($priceCap !== null) {
            $eligibleCapped = $eligible->filter(function ($r) use ($priceCap) { return $r->latest_price <= $priceCap; })->values();
        }

        // Pick recommended: lowest price, then highest KPI, then highest review_count
        $recommended = $eligibleCapped
            ->sort(function ($a, $b) {
                if ($a->latest_price !== $b->latest_price) {
                    return $a->latest_price <=> $b->latest_price; // asc
                }
                if ($a->kpi_score !== $b->kpi_score) {
                    return $b->kpi_score <=> $a->kpi_score; // desc
                }
                return $b->review_count <=> $a->review_count; // desc
            })
            ->first();

        return view('boss.best_suppliers', compact(
            'products',
            'productId',
            'month',
            'minKpi',
            'maxPremiumPct',
            'rows',
            'eligible',
            'recommended',
            'cheapestEligiblePrice',
            'priceCap'
        ));
    }

    public function export(Request $request)
    {
        $regionId = $this->currentRegionId();

        $productId = $request->query('product_id');
        $month = $request->query('month') ?: now()->format('Y-m');
        $minKpi = (float) ($request->query('min_kpi') ?: 4.00);

        if (!$productId) {
            return redirect()->route('boss.best-suppliers.index')
                ->with('success', 'Select a product first before exporting.');
        }

        $latestPriceSub = DB::table('supplier_product_prices')
            ->selectRaw('supplier_product_id, MAX(id) as latest_id')
            ->groupBy('supplier_product_id');

        $rows = DB::table('supplier_products as sp')
            ->join('suppliers as s', 's.id', '=', 'sp.supplier_id')
            ->join('products as p', 'p.id', '=', 'sp.product_id')
            ->leftJoinSub($latestPriceSub, 'lp', function ($join) {
                $join->on('lp.supplier_product_id', '=', 'sp.id');
            })
            ->leftJoin('supplier_product_prices as spp', 'spp.id', '=', 'lp.latest_id')
            ->leftJoin('supplier_kpi_monthly as kpi', function ($join) use ($regionId, $month) {
                $join->on('kpi.supplier_id', '=', 's.id')
                    ->where('kpi.region_id', '=', $regionId)
                    ->where('kpi.month', '=', $month);
            })
            ->where('sp.product_id', $productId)
            ->where('sp.is_active', 1)
            ->where('s.region_id', $regionId)
            ->selectRaw('
                p.name as product,
                s.name as supplier,
                spp.price as latest_price,
                kpi.kpi_score as kpi_score,
                kpi.review_count as review_count
            ')
            ->orderBy('s.name')
            ->get();

        $filename = "best_suppliers_product_{$productId}_{$month}.csv";

        return response()->streamDownload(function () use ($rows, $minKpi) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['product', 'supplier', 'latest_price', 'kpi_score', 'review_count', 'eligible_min_kpi', 'eligible']);

            foreach ($rows as $r) {
                $price = $r->latest_price !== null ? (float)$r->latest_price : null;
                $kpi   = $r->kpi_score !== null ? (float)$r->kpi_score : null;

                $eligible = ($price !== null) && ($kpi !== null) && ($kpi >= $minKpi);

                fputcsv($out, [
                    $r->product,
                    $r->supplier,
                    $price,
                    $kpi,
                    (int)($r->review_count ?? 0),
                    $minKpi,
                    $eligible ? 'YES' : 'NO',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
