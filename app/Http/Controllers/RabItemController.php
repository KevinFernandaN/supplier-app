<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\Unit;
use Illuminate\Http\Request;

class RabItemController extends Controller
{
    public function create(Rab $rab)
    {
        $products = Product::orderBy('name')->get();
        $units    = Unit::orderBy('name')->get();
        return view('rab_items.create', compact('rab', 'products', 'units'));
    }

    public function store(Request $request, Rab $rab)
    {
        $data = $request->validate([
            'product_id'     => 'required|exists:products,id',
            'unit_id'        => 'required|exists:units,id',
            'qty'            => 'required|numeric|gt:0',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        $data['rab_id'] = $rab->id;

        RabItem::create($data);

        return redirect()->route('rabs.show', $rab)
            ->with('success', 'Item added.');
    }

    public function edit(Rab $rab, RabItem $item)
    {
        $units = Unit::orderBy('name')->get();
        return view('rab_items.edit', compact('rab', 'item', 'units'));
    }

    public function update(Request $request, Rab $rab, RabItem $item)
    {
        $data = $request->validate([
            'unit_id'        => 'required|exists:units,id',
            'qty'            => 'required|numeric|gt:0',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        $item->update($data);

        return redirect()->route('rabs.show', $rab)
            ->with('success', 'Item updated.');
    }

    public function destroy(Rab $rab, RabItem $item)
    {
        $item->delete();
        return redirect()->route('rabs.show', $rab)
            ->with('success', 'Item removed.');
    }
}
