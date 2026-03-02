@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">
    {{ $supplierProduct->product->name }} - {{ $supplier->name }}
</h1>

<p><strong>Specification:</strong> {{ $supplierProduct->specification_text ?? '-' }}</p>
<p><strong>Lead Time:</strong> {{ $supplierProduct->lead_time_days }} days</p>
<p><strong>Min Order:</strong> {{ $supplierProduct->min_order_qty }}</p>

<hr>

<h4>Price History</h4>

<a href="{{ route('supplier-products.prices.create', $supplierProduct) }}"
   class="btn btn-primary mb-3">+ Add Price</a>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Effective From</th>
        <th>Price</th>
        <th>Notes</th>
    </tr>
    </thead>
    <tbody>
    @foreach($supplierProduct->prices as $price)
        <tr>
            <td>{{ $price->effective_from->format('Y-m-d') }}</td>
            <td>Rp {{ number_format($price->price, 0) }}</td>
            <td>{{ $price->notes }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<a href="{{ route('suppliers.supplier-products.index', $supplier) }}"
   class="btn btn-secondary">Back</a>
@endsection
