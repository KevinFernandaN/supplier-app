<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->value('id');
    }

    public function index()
    {
        $leadSub = "
            SELECT %s(sp.lead_time_days)
            FROM purchase_order_items poi
            JOIN supplier_products sp
                ON sp.supplier_id = purchase_orders.supplier_id
                AND sp.product_id = poi.product_id
                AND sp.is_active = 1
            WHERE poi.purchase_order_id = purchase_orders.id
        ";

        $orders = PurchaseOrder::with('supplier')
            ->selectRaw('purchase_orders.*, '
                . '(' . sprintf($leadSub, 'MIN') . ') as min_lead_days, '
                . '(' . sprintf($leadSub, 'MAX') . ') as max_lead_days')
            ->orderBy('order_date', 'desc')
            ->paginate(10);

        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('purchase_orders.create', compact('products'));
    }

    public function suppliersByProduct(Request $request)
    {
        $productId = (int) $request->input('product_id');

        if (!$productId) {
            return response()->json([]);
        }

        $rows = DB::select("
            SELECT
                s.id,
                s.name,
                sp.lead_time_days,
                latest_kpi.kpi_score,
                latest_price.price
            FROM suppliers s
            JOIN supplier_products sp
                ON sp.supplier_id = s.id
                AND sp.product_id = ?
                AND sp.is_active = 1
            LEFT JOIN (
                SELECT skm.supplier_id, skm.kpi_score
                FROM supplier_kpi_monthly skm
                INNER JOIN (
                    SELECT supplier_id, MAX(month) AS max_month
                    FROM supplier_kpi_monthly
                    GROUP BY supplier_id
                ) latest ON latest.supplier_id = skm.supplier_id
                         AND latest.max_month = skm.month
            ) latest_kpi ON latest_kpi.supplier_id = s.id
            LEFT JOIN (
                SELECT spp.supplier_product_id, spp.price
                FROM supplier_product_prices spp
                INNER JOIN (
                    SELECT supplier_product_id, MAX(effective_from) AS max_date
                    FROM supplier_product_prices
                    GROUP BY supplier_product_id
                ) lp ON lp.supplier_product_id = spp.supplier_product_id
                     AND lp.max_date = spp.effective_from
            ) latest_price ON latest_price.supplier_product_id = sp.id
            WHERE s.is_active = 1
            ORDER BY
                ISNULL(latest_kpi.kpi_score),
                latest_kpi.kpi_score DESC,
                ISNULL(latest_price.price),
                latest_price.price ASC
        ", [$productId]);

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'status' => ['required'],
            'notes' => ['nullable'],
        ]);

        $validated['region_id'] = $this->currentRegionId();

        $po = PurchaseOrder::create($validated);

        return redirect()
            ->route('purchase-orders.show', $po)
            ->with('success', 'Purchase Order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'items.product',
            'items.unit',
            'items.complaints',
            'review',
            'receivings.kitchen',
            'receivings.items',
        ]);

        // Sum received_qty per product across all completed receivings
        $receivedTotals = [];
        foreach ($purchaseOrder->receivings as $receiving) {
            if ($receiving->received_at) {
                foreach ($receiving->items as $ri) {
                    $receivedTotals[$ri->product_id] = ($receivedTotals[$ri->product_id] ?? 0) + (float) $ri->received_qty;
                }
            }
        }

        // Kitchens that haven't yet created a receiving for this PO
        $usedKitchenIds = $purchaseOrder->receivings->pluck('kitchen_id')->filter()->values()->all();
        $availableKitchens = \App\Models\Kitchen::where('is_active', true)
            ->whereNotIn('id', $usedKitchenIds)
            ->orderBy('name')
            ->get();

        return view('purchase_orders.show', compact('purchaseOrder', 'receivedTotals', 'availableKitchens'));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase Order deleted.');
    }
}
