@extends('layouts.app')

@section('title', 'Sales Order Detail')

@section('content')
<h1 class="h3 mb-3">Sales Order</h1>

<p><strong>Date:</strong> {{ $salesOrder->sale_date->format('Y-m-d') }}</p>
<p><strong>Channel:</strong> {{ $salesOrder->channel ?? '-' }}</p>

<hr>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h4 class="mb-0">Items</h4>
    <a href="{{ route('sales-orders.items.create', $salesOrder) }}" class="btn btn-primary">
        + Add Item
    </a>
</div>

<table class="table table-bordered align-middle">
    <thead>
    <tr>
        <th>Menu</th>
        <th>Qty</th>
        <th>Selling Price</th>
        <th>Line Total</th>
    </tr>
    </thead>
    <tbody>
    @forelse($salesOrder->items as $item)
        <tr>
            <td>{{ $item->menu->name }}</td>
            <td>{{ $item->qty }}</td>
            <td>Rp {{ number_format($item->selling_price, 0) }}</td>
            <td>Rp {{ number_format($item->qty * $item->selling_price, 0) }}</td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-center py-4">No items yet.</td></tr>
    @endforelse
    </tbody>
</table>

<a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">Back</a>
@endsection
