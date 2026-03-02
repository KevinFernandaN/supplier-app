@extends('layouts.app')

@section('title', 'Add Product to Supplier')

@section('content')
<h1 class="h3 mb-3">Add Product to {{ $supplier->name }}</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.supplier-products.store', $supplier) }}">
            @csrf

            @include('supplier_products._form', [
                'supplier' => $supplier,
                'products' => $products
            ])
        </form>
    </div>
</div>

<a href="{{ route('suppliers.supplier-products.index', $supplier) }}" class="btn btn-outline-secondary mt-3">Back</a>
@endsection
