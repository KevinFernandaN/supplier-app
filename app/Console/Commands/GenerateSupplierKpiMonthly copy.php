<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSupplierKpiMonthly extends Command
{
    protected $signature = 'kpi:monthly {--month=} {--region_id=}';
    protected $description = 'Generate supplier_kpi_monthly from supplier_reviews';

    public function handle(): int
    {
        $month = $this->option('month') ?: now()->format('Y-m'); // char(7) e.g. 2026-02
        $regionId = $this->option('region_id');

        // Convert month to date range
        $start = $month . '-01';
        $end = date('Y-m-d', strtotime($start . ' +1 month')); // exclusive

        $this->info("Generating supplier KPI for month={$month} (range {$start} to <{$end})");

        $query = DB::table('supplier_reviews')
            ->selectRaw('region_id, supplier_id')
            ->selectRaw('AVG(goods_correct) as avg_goods_correct')
            ->selectRaw('AVG(weight_correct) as avg_weight_correct')
            ->selectRaw('AVG(on_time) as avg_on_time')
            ->selectRaw('AVG(price_correct) as avg_price_correct')
            ->selectRaw('COUNT(*) as review_count')
            ->where('review_date', '>=', $start)
            ->where('review_date', '<', $end)
            ->groupBy('region_id', 'supplier_id');

        if ($regionId) {
            $query->where('region_id', (int)$regionId);
        }

        $rows = $query->get();

        foreach ($rows as $r) {
            $kpiScore = (
                (float)$r->avg_goods_correct +
                (float)$r->avg_weight_correct +
                (float)$r->avg_on_time +
                (float)$r->avg_price_correct
            ) / 4;

            DB::table('supplier_kpi_monthly')->updateOrInsert(
                [
                    'region_id' => (int)$r->region_id,
                    'supplier_id' => (int)$r->supplier_id,
                    'month' => $month, // char(7)
                ],
                [
                    'review_count' => (int)$r->review_count,
                    'avg_goods_correct' => round((float)$r->avg_goods_correct, 2),
                    'avg_weight_correct' => round((float)$r->avg_weight_correct, 2),
                    'avg_on_time' => round((float)$r->avg_on_time, 2),
                    'avg_price_correct' => round((float)$r->avg_price_correct, 2),
                    'kpi_score' => round($kpiScore, 2),
                    'updated_at' => now(),
                    // only set created_at if insert happens (Laravel will handle it if table has timestamps? updateOrInsert won't)
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }

        $this->info("Done. Suppliers updated: " . $rows->count());
        return self::SUCCESS;
    }
}
