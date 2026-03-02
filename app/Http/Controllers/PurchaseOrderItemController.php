<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Unit;
use Illuminate\Http\Request;

class PurchaseOrderItemController extends Controller
{
    public function create(PurchaseOrder $purchaseOrder)
    {
        $products = Product::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('purchase_order_items.create', compact('purchaseOrder', 'products', 'units'));
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'qty' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['purchase_order_id'] = $purchaseOrder->id;

        PurchaseOrderItem::create($validated);

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Item added to PO.');
    }
}
