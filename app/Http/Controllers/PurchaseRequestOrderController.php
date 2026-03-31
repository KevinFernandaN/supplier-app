<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestOrderController extends Controller
{
    /**
     * Show the vendor assignment page for a confirmed PR.
     */
    public function create(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'confirmed') {
            return redirect()->route('purchase-requests.show', $purchaseRequest)
                ->with('error', 'Only confirmed PRs can create orders.');
        }

        $purchaseRequest->load(['kitchen.region', 'menu', 'items.product', 'items.unit']);

        $month = now()->format('Y-m');
        $regionId = $purchaseRequest->kitchen->region_id;
        $isAssisted = $purchaseRequest->kitchen->type === 'assisted';

        // For each PR item, load available suppliers with price + KPI
        $vendorsByProduct = [];
        $recommendedByProduct = [];

        foreach ($purchaseRequest->items as $item) {
            $vendors = $this->getVendorsForProduct($item->product_id, $regionId, $month);
            $vendorsByProduct[$item->product_id] = $vendors;

            if ($isAssisted) {
                $recommendedByProduct[$item->product_id] = $this->recommend($vendors);
            }
        }

        return view('purchase_requests.order', compact(
            'purchaseRequest',
            'vendorsByProduct',
            'recommendedByProduct',
            'isAssisted'
        ));
    }

    /**
     * Process vendor assignments, create POs grouped by supplier.
     */
    public function store(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed PRs can create orders.');
        }

        $purchaseRequest->load(['kitchen', 'items.product', 'items.unit']);

        $regionId = $purchaseRequest->kitchen->region_id;

        // items[{pr_item_id}][splits][{n}][supplier_id]
        // items[{pr_item_id}][splits][{n}][qty]
        $itemsInput = $request->input('items', []);

        if (empty($itemsInput)) {
            return back()->withInput()->with('error', 'No vendor assignments provided.');
        }

        // Build: supplierId → [ { product_id, unit_id, qty, purchase_price, supplier_product_price_id } ]
        $poLinesBySupplier = [];
        $errors = [];

        foreach ($purchaseRequest->items as $prItem) {
            $splits = $itemsInput[$prItem->id]['splits'] ?? [];

            foreach ($splits as $split) {
                $supplierId = (int) ($split['supplier_id'] ?? 0);
                $qty        = (float) ($split['qty'] ?? 0);

                if (!$supplierId || $qty <= 0) {
                    continue; // skip empty rows
                }

                // Get latest price for this supplier + product
                $priceRow = DB::table('supplier_products as sp')
                    ->join('supplier_product_prices as spp', function ($j) {
                        $j->on('spp.supplier_product_id', '=', 'sp.id');
                    })
                    ->where('sp.supplier_id', $supplierId)
                    ->where('sp.product_id', $prItem->product_id)
                    ->where('sp.is_active', 1)
                    ->orderByDesc('spp.effective_from')
                    ->orderByDesc('spp.id')
                    ->select('spp.id as price_id', 'spp.price')
                    ->first();

                if (!$priceRow) {
                    $errors[] = "No price found for {$prItem->product->name} from supplier #{$supplierId}. Assign a price first.";
                    continue;
                }

                $poLinesBySupplier[$supplierId][] = [
                    'product_id'               => $prItem->product_id,
                    'unit_id'                  => $prItem->unit_id,
                    'qty'                      => $qty,
                    'purchase_price'           => $priceRow->price,
                    'supplier_product_price_id'=> $priceRow->price_id,
                ];
            }
        }

        if (!empty($errors)) {
            return back()->withInput()->with('error', implode(' | ', $errors));
        }

        if (empty($poLinesBySupplier)) {
            return back()->withInput()->with('error', 'No valid vendor assignments found. Please assign at least one vendor with a qty > 0.');
        }

        DB::transaction(function () use ($purchaseRequest, $poLinesBySupplier, $regionId) {
            foreach ($poLinesBySupplier as $supplierId => $lines) {
                $po = PurchaseOrder::create([
                    'region_id'           => $regionId,
                    'supplier_id'         => $supplierId,
                    'purchase_request_id' => $purchaseRequest->id,
                    'order_date'          => now()->toDateString(),
                    'status'              => 'draft',
                ]);

                foreach ($lines as $line) {
                    PurchaseOrderItem::create([
                        'purchase_order_id'         => $po->id,
                        'product_id'                => $line['product_id'],
                        'unit_id'                   => $line['unit_id'],
                        'qty'                       => $line['qty'],
                        'purchase_price'            => $line['purchase_price'],
                        'supplier_product_price_id' => $line['supplier_product_price_id'],
                    ]);
                }
            }

            $purchaseRequest->update(['status' => 'ordered']);
        });

        $poCount = count($poLinesBySupplier);

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', "{$poCount} Purchase Order(s) created from this PR.");
    }

    // -------------------------------------------------------------------------

    private function getVendorsForProduct(int $productId, int $regionId, string $month): \Illuminate\Support\Collection
    {
        return DB::table('supplier_products as sp')
            ->join('suppliers as s', 's.id', '=', 'sp.supplier_id')
            ->leftJoin('supplier_product_prices as spp', function ($j) {
                $j->on('spp.supplier_product_id', '=', 'sp.id');
            })
            ->leftJoin('supplier_kpi_monthly as kpi', function ($j) use ($regionId, $month) {
                $j->on('kpi.supplier_id', '=', 's.id')
                  ->where('kpi.region_id', $regionId)
                  ->where('kpi.month', $month);
            })
            ->where('sp.product_id', $productId)
            ->where('sp.is_active', 1)
            ->where('s.region_id', $regionId)
            ->select(
                's.id as supplier_id',
                's.name as supplier_name',
                'sp.availability_status',
                DB::raw('MAX(spp.price) as latest_price'),
                DB::raw('MAX(kpi.kpi_score) as kpi_score')
            )
            ->groupBy('s.id', 's.name', 'sp.availability_status')
            ->orderBy('s.name')
            ->get()
            ->map(function ($r) {
                $r->latest_price = $r->latest_price !== null ? (float) $r->latest_price : null;
                $r->kpi_score    = $r->kpi_score    !== null ? (float) $r->kpi_score    : null;
                return $r;
            });
    }

    private function recommend(\Illuminate\Support\Collection $vendors): ?object
    {
        $eligible = $vendors->filter(fn($v) =>
            $v->latest_price !== null && $v->kpi_score !== null && $v->kpi_score >= 4.0
        );

        if ($eligible->isEmpty()) {
            return null;
        }

        $cheapest = $eligible->min('latest_price');
        $cap = $cheapest * 1.20;

        return $eligible
            ->filter(fn($v) => $v->latest_price <= $cap)
            ->sort(function ($a, $b) {
                if ($a->latest_price !== $b->latest_price) return $a->latest_price <=> $b->latest_price;
                return $b->kpi_score <=> $a->kpi_score;
            })
            ->first();
    }
}
