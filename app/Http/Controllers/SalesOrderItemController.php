<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;

class SalesOrderItemController extends Controller
{
    public function create(SalesOrder $salesOrder)
    {
        $menus = Menu::orderBy('name')->get();

        // For JS auto-fill: map id => default price
        $menuPrices = $menus->mapWithKeys(function ($m) { return [$m->id => (float) ($m->default_selling_price ?? 0)]; });

        return view('sales_order_items.create', compact('salesOrder', 'menus', 'menuPrices'));
    }

    public function store(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'qty' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['sales_order_id'] = $salesOrder->id;

        SalesOrderItem::create($validated);

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', 'Item added to sales order.');
    }
}
