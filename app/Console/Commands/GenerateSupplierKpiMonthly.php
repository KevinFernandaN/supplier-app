<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSupplierKpiMonthly extends Command
{
    protected $signature = 'kpi:monthly {--month=} {--region_id=}';
    protected $description = 'Generate supplier_kpi_monthly from supplier_reviews (with complaint + return penalties)';

    public function handle(): int
    {
        $month = $this->option('month') ?: now()->format('Y-m');
        $regionId = $this->option('region_id');

        $start = $month . '-01';
        $end = date('Y-m-d', strtotime($start . ' +1 month')); // exclusive

        $this->info("Generating supplier KPI (complaint + return penalties) for {$month}");

        // === 1) Aggregate reviews ===
        $reviewQuery = DB::table('supplier_reviews')
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
            $reviewQuery->where('region_id', (int)$regionId);
        }

        $reviews = $reviewQuery->get();

        foreach ($reviews as $r) {

            // === Base KPI ===
            $baseKpi = (
                (float)$r->avg_goods_correct +
                (float)$r->avg_weight_correct +
                (float)$r->avg_on_time +
                (float)$r->avg_price_correct
            ) / 4;

            // === 2) Complaint Penalty (severity weighted) ===
            $complaintSeveritySum = (float) DB::table('complaints')
                ->where('region_id', $r->region_id)
                ->where('supplier_id', $r->supplier_id)
                ->where('complaint_date', '>=', $start)
                ->where('complaint_date', '<', $end)
                ->sum('severity');

            $complaintPenalty = $complaintSeveritySum * 0.02;

            // === 3) Return Penalty (0.05 per return order in the month) ===
            $returnCount = (int) DB::table('returns')
                ->where('region_id', $r->region_id)
                ->where('supplier_id', $r->supplier_id)
                ->where('return_date', '>=', $start)
                ->where('return_date', '<', $end)
                ->count();

            $returnPenalty = $returnCount * 0.05;

            $finalKpi = max($baseKpi - $complaintPenalty - $returnPenalty, 0);

            DB::table('supplier_kpi_monthly')->updateOrInsert(
                [
                    'region_id' => $r->region_id,
                    'supplier_id' => $r->supplier_id,
                    'month' => $month,
                ],
                [
                    'review_count' => (int)$r->review_count,
                    'avg_goods_correct' => round((float)$r->avg_goods_correct, 2),
                    'avg_weight_correct' => round((float)$r->avg_weight_correct, 2),
                    'avg_on_time' => round((float)$r->avg_on_time, 2),
                    'avg_price_correct' => round((float)$r->avg_price_correct, 2),
                    'kpi_score' => round($finalKpi, 2),
                    'complaint_penalty' => round($complaintPenalty, 2),
                    'return_penalty' => round($returnPenalty, 2),
                    'return_count' => $returnCount,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->info("KPI generation complete.");
        return self::SUCCESS;
    }
}
