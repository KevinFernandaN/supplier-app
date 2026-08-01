<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\RabPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RabPeriodPurchaseRequestController extends Controller
{
    public function create(RabPeriod $rabPeriod)
    {
        if ($rabPeriod->status === 'draft') {
            return redirect()->route('rab-periods.show', $rabPeriod)
                ->with('error', 'Only confirmed or locked periods can be sent to Purchase Requests.');
        }

        $existing = PurchaseRequest::where('rab_period_id', $rabPeriod->id)->first();
        $locked   = $rabPeriod->isPastPrLockDate();

        if ($existing) {
            return view('rab-periods.purchase-requests-create', [
                'rabPeriod' => $rabPeriod,
                'kitchens'  => collect(),
                'existing'  => $existing,
                'locked'    => $locked,
            ]);
        }

        if ($locked) {
            return redirect()->route('rab-periods.show', $rabPeriod)
                ->with('error', 'This period is past its H-1 lock date (' . $rabPeriod->prLockDate()->format('d M Y') . ') and can no longer be sent to Purchase Request.');
        }

        $kitchens = Kitchen::where('region_id', $rabPeriod->region_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('rab-periods.purchase-requests-create', [
            'rabPeriod' => $rabPeriod,
            'kitchens'  => $kitchens,
            'existing'  => null,
            'locked'    => false,
        ]);
    }

    public function store(Request $request, RabPeriod $rabPeriod)
    {
        if ($rabPeriod->status === 'draft') {
            return redirect()->route('rab-periods.show', $rabPeriod)
                ->with('error', 'Only confirmed or locked periods can be sent to Purchase Requests.');
        }

        if ($rabPeriod->isPastPrLockDate()) {
            return redirect()->route('rab-periods.show', $rabPeriod)
                ->with('error', 'This period is past its H-1 lock date (' . $rabPeriod->prLockDate()->format('d M Y') . ') and can no longer be sent or updated.');
        }

        $existing = PurchaseRequest::where('rab_period_id', $rabPeriod->id)->first();

        [$totalPortion, $items] = $this->aggregate($rabPeriod);

        if (empty($items)) {
            return redirect()->route('rab-periods.show', $rabPeriod)
                ->with('error', 'No ingredients recorded in this period yet — nothing to send.');
        }

        if ($existing) {
            if (!$request->boolean('confirm_update')) {
                return redirect()->route('rab-periods.purchase-requests.create', $rabPeriod)
                    ->with('error', 'Please confirm the update before it is applied.');
            }

            DB::transaction(function () use ($existing, $items, $totalPortion) {
                $existing->items()->delete();
                $existing->update(['total_portion' => $totalPortion]);

                foreach ($items as $itemData) {
                    PurchaseRequestItem::create([
                        'purchase_request_id' => $existing->id,
                        'product_id'          => $itemData['product_id'],
                        'unit_id'             => $itemData['unit_id'],
                        'required_qty'        => round($itemData['required_qty'], 3),
                        'buffer_pct'          => 0,
                    ]);
                }
            });

            return redirect()->route('purchase-requests.show', $existing)
                ->with('success', 'Purchase Request #' . $existing->id . ' updated with the latest RAB data.');
        }

        $data = $request->validate([
            'kitchen_id' => [
                'required',
                'exists:kitchens,id',
                function ($attribute, $value, $fail) use ($rabPeriod) {
                    $belongs = Kitchen::where('id', $value)->where('region_id', $rabPeriod->region_id)->exists();
                    if (!$belongs) {
                        $fail('Selected kitchen does not belong to this period\'s region.');
                    }
                },
            ],
        ]);

        $pr = DB::transaction(function () use ($items, $data, $rabPeriod, $totalPortion) {
            $pr = PurchaseRequest::create([
                'kitchen_id'    => $data['kitchen_id'],
                'menu_id'       => null,
                'rab_period_id' => $rabPeriod->id,
                'total_portion' => $totalPortion,
                'status'        => 'draft',
                'notes'         => 'Generated from RAB Period: ' . $rabPeriod->name,
            ]);

            foreach ($items as $itemData) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'product_id'          => $itemData['product_id'],
                    'unit_id'             => $itemData['unit_id'],
                    'required_qty'        => round($itemData['required_qty'], 3),
                    'buffer_pct'          => 0,
                ]);
            }

            return $pr;
        });

        return redirect()->route('purchase-requests.show', $pr)
            ->with('success', 'Purchase Request created from this period.');
    }

    // -------------------------------------------------------------------------
    // One combined ingredient list for the whole period: sum every ingredient
    // across every menu and every day, regardless of which menu it came from.
    // -------------------------------------------------------------------------
    private function aggregate(RabPeriod $rabPeriod): array
    {
        $rabPeriod->load(['days' => function ($q) {
            $q->orderBy('day_date')
              ->with(['menus' => function ($q) {
                  $q->orderBy('sort_order')->orderBy('id')
                    ->with(['items', 'replacements']);
              }]);
        }]);

        foreach ($rabPeriod->days as $day) {
            $day->setRelation('period', $rabPeriod);
            foreach ($day->menus as $menu) {
                $menu->setRelation('day', $day);
                foreach ($menu->replacements as $rep) {
                    $rep->setRelation('day', $day);
                }
            }
        }

        $totalPortion = 0;
        $items = [];

        foreach ($rabPeriod->days as $day) {
            $totalPortion += (int) $day->pk_count + (int) $day->pb_count;

            foreach ($day->menus as $dayMenu) {
                $effPk = $dayMenu->effectivePkCount();
                $effPb = $dayMenu->effectivePbCount();

                foreach ($dayMenu->items as $item) {
                    $key = $item->product_id . '_' . $item->unit_id;
                    $items[$key] ??= [
                        'product_id'   => $item->product_id,
                        'unit_id'      => $item->unit_id,
                        'required_qty' => 0,
                    ];
                    $items[$key]['required_qty'] +=
                        ((float) $item->pk_gramasi * $effPk) + ((float) $item->pb_gramasi * $effPb);
                }
            }
        }

        return [$totalPortion, $items];
    }
}
