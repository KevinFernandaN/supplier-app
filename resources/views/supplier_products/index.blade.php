@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">
    {{ $supplier->name }} - Products
</h1>

<a href="{{ route('suppliers.supplier-products.create', $supplier) }}"
   class="btn btn-primary mb-3">+ Add Product</a>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Product</th>
        <th>Lead Time</th>
        <th>Min Order</th>
        <th>Status</th>
        <th width="200">Action</th>
    </tr>
    </thead>
    <tbody>
    @foreach($supplierProducts as $sp)
        <tr>
            <td>{{ $sp->product->name }}</td>
            <td>{{ $sp->lead_time_days }} days</td>
            <td>{{ $sp->min_order_qty }}</td>
            <td>{{ $sp->is_active ? 'Active' : 'Inactive' }}</td>
            <td>
                <a class="btn btn-sm btn-info"
                   href="{{ route('suppliers.supplier-products.show', [$supplier, $sp]) }}">
                    View
                </a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">Back</a>
@endsection
