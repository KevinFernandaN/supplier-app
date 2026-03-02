<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Region;
use App\Models\ReturnItem;
use App\Models\ReturnOrder;
use App\Models\Unit;
use Illuminate\Http\Request;

class ReturnItemController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function create(ReturnOrder $returnOrder)
    {
        $regionId = $this->currentRegionId();
        abort_if($returnOrder->region_id !== $regionId, 404);

        $products = Product::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('return_items.create', compact('returnOrder', 'products', 'units'));
    }

    public function store(Request $request, ReturnOrder $returnOrder)
    {
        $regionId = $this->currentRegionId();
        abort_if($returnOrder->region_id !== $regionId, 404);

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'unit_id' => ['required', 'exists:units,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        ReturnItem::create([
            'return_order_id' => $returnOrder->id,
            'product_id' => $validated['product_id'],
            'qty' => $validated['qty'],
            'unit_id' => $validated['unit_id'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('returns.show', $returnOrder)->with('success', 'Return item added.');
    }

    public function edit(ReturnOrder $returnOrder, $item)
    {
        $regionId = $this->currentRegionId();
        abort_if($returnOrder->region_id !== $regionId, 404);

        $returnItem = ReturnItem::where('id', $item)->where('return_order_id', $returnOrder->id)->firstOrFail();

        $products = Product::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('return_items.edit', compact('returnOrder', 'returnItem', 'products', 'units'));
    }

    public function update(Request $request, ReturnOrder $returnOrder, $item)
    {
        $regionId = $this->currentRegionId();
        abort_if($returnOrder->region_id !== $regionId, 404);

        $returnItem = ReturnItem::where('id', $item)->where('return_order_id', $returnOrder->id)->firstOrFail();

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'unit_id' => ['required', 'exists:units,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $returnItem->update($validated);

        return redirect()->route('returns.show', $returnOrder)->with('success', 'Return item updated.');
    }

    public function destroy(ReturnOrder $returnOrder, $item)
    {
        $regionId = $this->currentRegionId();
        abort_if($returnOrder->region_id !== $regionId, 404);

        $returnItem = ReturnItem::where('id', $item)->where('return_order_id', $returnOrder->id)->firstOrFail();
        $returnItem->delete();

        return redirect()->route('returns.show', $returnOrder)->with('success', 'Return item deleted.');
    }
}
