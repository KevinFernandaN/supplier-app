@extends('layouts.app')

@section('title', 'Edit Supplier Product')

@section('content')
<h1 class="h3 mb-3">Edit Supplier Product ({{ $supplier->name }})</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.supplier-products.update', [$supplier, $supplierProduct]) }}">
            @csrf
            @method('PUT')

            @include('supplier_products._form', [
                'supplier' => $supplier,
                'supplierProduct' => $supplierProduct,
                'products' => $products
            ])
        </form>
    </div>
</div>

<a href="{{ route('suppliers.supplier-products.index', $supplier) }}" class="btn btn-outline-secondary mt-3">Back</a>
@endsection
