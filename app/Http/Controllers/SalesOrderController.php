<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index()
    {
        $orders = SalesOrder::orderBy('sale_date', 'desc')->paginate(10);
        return view('sales_orders.index', compact('orders'));
    }

    public function create()
    {
        return view('sales_orders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => ['required', 'date'],
            'channel' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['region_id'] = $this->currentRegionId();

        $so = SalesOrder::create($validated);

        return redirect()
            ->route('sales-orders.show', $so)
            ->with('success', 'Sales order created.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load('items.menu');
        return view('sales_orders.show', compact('salesOrder'));
    }

    public function destroy(SalesOrder $salesOrder)
    {
        $salesOrder->delete();

        return redirect()
            ->route('sales-orders.index')
            ->with('success', 'Sales order deleted.');
    }
}
