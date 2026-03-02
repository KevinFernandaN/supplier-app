<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use App\Models\SupplierProductPrice;
use Illuminate\Http\Request;

class SupplierProductPriceController extends Controller
{
    public function create(SupplierProduct $supplierProduct)
    {
        return view('supplier_product_prices.create', compact('supplierProduct'));
    }

    public function store(Request $request, SupplierProduct $supplierProduct)
    {
        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['supplier_product_id'] = $supplierProduct->id;

        SupplierProductPrice::create($validated);

        return redirect()
            ->route('suppliers.supplier-products.show', [
                $supplierProduct->supplier_id,
                $supplierProduct->id
            ])
            ->with('success', 'Price added successfully.');
    }
}
