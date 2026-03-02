<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Region;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->value('id');
    }

    public function index()
    {
        $orders = PurchaseOrder::with('supplier')
            ->orderBy('order_date', 'desc')
            ->paginate(10);

        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        return view('purchase_orders.create', compact('suppliers'));
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
        $purchaseOrder->load('supplier', 'items.product', 'items.unit', 'items.complaints', 'review');

        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase Order deleted.');
    }
}
