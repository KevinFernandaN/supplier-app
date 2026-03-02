@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">
    Purchase Order - {{ $purchaseOrder->supplier->name }}
</h1>

<p><strong>Date:</strong> {{ $purchaseOrder->order_date }}</p>
<p><strong>Status:</strong> {{ $purchaseOrder->status }}</p>

<hr>

<h5>Supplier Review</h5>

@if($purchaseOrder->review)
    <a class="btn btn-sm btn-outline-info"
       href="{{ route('purchase-orders.reviews.show', [$purchaseOrder, $purchaseOrder->review]) }}">
        View Review
    </a>
@else
    <a class="btn btn-sm btn-outline-primary"
       href="{{ route('purchase-orders.reviews.create', $purchaseOrder) }}">
        Add Review
    </a>
@endif

<hr>

<h4>Items</h4>

<a href="{{ route('purchase-orders.items.create', $purchaseOrder) }}"
   class="btn btn-primary mb-3">+ Add Item</a>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Unit</th>
        <th>Price</th>
        <th>Complaint</th>
    </tr>
    </thead>
    <tbody>
    @foreach($purchaseOrder->items as $item)
        <tr>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->qty }}</td>
            <td>{{ $item->unit->symbol }}</td>
            <td>Rp {{ number_format($item->purchase_price, 0) }}</td>
            <td>
                @php $count = $item->complaints->count(); @endphp

                @if($count === 0)
                    <a class="btn btn-sm btn-outline-warning"
                    href="{{ route('purchase-order-items.complaints.create', $item) }}">
                        Add Complaint
                    </a>
                @else
                    <a class="btn btn-sm btn-outline-info"
                    href="{{ route('complaints.index', ['purchase_order_item_id' => $item->id]) }}">
                        View Complaints ({{ $count }})
                    </a>

                    <a class="btn btn-sm btn-outline-warning"
                    href="{{ route('purchase-order-items.complaints.create', $item) }}">
                        + Add Another
                    </a>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Back</a>
@endsection
