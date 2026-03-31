<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Http\Request;

class SupplierProductController extends Controller
{
    public function index(Supplier $supplier)
    {
        $supplierProducts = $supplier->supplierProducts()
            ->with(['product', 'latestPrice'])
            ->orderBy('id', 'desc')
            ->get();

        return view('supplier_products.index', compact('supplier', 'supplierProducts'));
    }

    public function create(Supplier $supplier)
    {
        $products = Product::orderBy('name')->get();

        return view('supplier_products.create', compact('supplier', 'products'));
    }

    public function store(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                \Illuminate\Validation\Rule::unique('supplier_products')->where('supplier_id', $supplier->id),
            ],
            'specification_text' => ['nullable', 'string'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'min_order_qty' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
            'availability_status' => ['required', 'in:ready,limited,preorder'],
        ], [
            'product_id.unique' => 'This product has already been added to this supplier.',
        ]);

        $validated['supplier_id'] = $supplier->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        SupplierProduct::create($validated);

        return redirect()
            ->route('suppliers.supplier-products.index', $supplier)
            ->with('success', 'Product added to supplier.');
    }

    public function show(Supplier $supplier, SupplierProduct $supplierProduct)
    {
        $supplierProduct->load('product', 'prices');

        return view('supplier_products.show', compact('supplier', 'supplierProduct'));
    }

    public function edit(Supplier $supplier, SupplierProduct $supplierProduct)
    {
        $products = Product::orderBy('name')->get();

        return view('supplier_products.edit', compact('supplier', 'supplierProduct', 'products'));
    }

    public function update(Request $request, Supplier $supplier, SupplierProduct $supplierProduct)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'specification_text' => ['nullable', 'string'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'min_order_qty' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
            'availability_status' => ['required', 'in:ready,limited,preorder'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $supplierProduct->update($validated);

        return redirect()
            ->route('suppliers.supplier-products.index', $supplier)
            ->with('success', 'Supplier product updated.');
    }

    public function destroy(Supplier $supplier, SupplierProduct $supplierProduct)
    {
        $supplierProduct->delete();

        return redirect()
            ->route('suppliers.supplier-products.index', $supplier)
            ->with('success', 'Supplier product deleted.');
    }
}
