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

<hr>

<h5>Receiving</h5>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- List of existing receivings --}}
@if($purchaseOrder->receivings->isNotEmpty())
    <table class="table table-sm table-bordered mb-3">
        <thead>
        <tr>
            <th>Kitchen</th>
            <th>Status</th>
            <th width="80">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($purchaseOrder->receivings as $r)
            <tr>
                <td>{{ $r->kitchen?->name ?? '—' }}</td>
                <td>
                    @if($r->received_at)
                        <span class="badge bg-success">Received — {{ $r->received_at->format('d M Y') }}</span>
                    @else
                        <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('receivings.show', $r) }}" class="btn btn-sm btn-info">View</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Received summary per item --}}
    <h6 class="mt-2">Received Summary</h6>
    <table class="table table-sm table-bordered mb-3">
        <thead>
        <tr>
            <th>Product</th>
            <th>Ordered</th>
            <th>Total Received</th>
            <th>Unit</th>
        </tr>
        </thead>
        <tbody>
        @foreach($purchaseOrder->items as $item)
            @php $received = $receivedTotals[$item->product_id] ?? 0; @endphp
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ number_format($item->qty, 3) }}</td>
                <td class="{{ $received >= $item->qty ? 'text-success fw-bold' : 'text-warning fw-bold' }}">
                    {{ number_format($received, 3) }}
                </td>
                <td>{{ $item->unit->symbol }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

{{-- Create receiving for another kitchen --}}
@if($availableKitchens->isNotEmpty())
    <form method="POST" action="{{ route('purchase-orders.receiving.store', $purchaseOrder) }}" class="d-flex gap-2 align-items-center">
        @csrf
        <select name="kitchen_id" class="form-select form-select-sm" style="max-width:260px" required>
            <option value="">— Select Kitchen —</option>
            @foreach($availableKitchens as $k)
                <option value="{{ $k->id }}">{{ $k->name }} ({{ ucfirst($k->type) }})</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-success">+ Create Receiving</button>
    </form>
@else
    <p class="text-muted small mb-0">All active kitchens have a receiving record for this PO.</p>
@endif

<hr>

<a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Back</a>
@endsection
