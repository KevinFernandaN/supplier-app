@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h3 mb-1">Receiving — PO #{{ $receiving->purchaseOrder->id }}</h1>
        <p class="text-muted mb-0">
            {{ $receiving->purchaseOrder->supplier->name }}
            &mdash; {{ $receiving->purchaseOrder->order_date->format('d M Y') }}
            @if($receiving->kitchen)
                &mdash; Kitchen: <strong>{{ $receiving->kitchen->name }}</strong>
                <span class="badge bg-secondary ms-1">{{ ucfirst($receiving->kitchen->type) }}</span>
            @endif
        </p>
    </div>
    <div>
        @if($receiving->received_at)
            <span class="badge bg-success fs-6">Received — {{ $receiving->received_at->format('d M Y H:i') }}</span>
        @else
            <span class="badge bg-warning text-dark fs-6">Pending</span>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Main quantities form --}}
<form id="qty-form" method="POST" action="{{ route('receivings.update', $receiving) }}">
    @csrf
    @method('PUT')
</form>

{{-- Proof upload / remove forms (outside main form to avoid nesting) --}}
@foreach($receiving->items as $item)
    <form id="proof-form-{{ $item->id }}"
          method="POST"
          action="{{ route('receiving-items.proof.upload', $item) }}"
          enctype="multipart/form-data">
        @csrf
    </form>
    <form id="proof-remove-form-{{ $item->id }}"
          method="POST"
          action="{{ route('receiving-items.proof.delete', $item) }}">
        @csrf
        @method('DELETE')
    </form>
@endforeach

{{-- Mark-as-received form --}}
<form id="receive-form" method="POST" action="{{ route('receivings.receive', $receiving) }}">
    @csrf
</form>

<table class="table table-bordered align-middle">
    <thead>
    <tr>
        <th>Product</th>
        <th>Ordered</th>
        <th>Unit</th>
        <th>Received Qty</th>
        <th>Notes</th>
        <th>Proof Image</th>
        <th>Complaint</th>
    </tr>
    </thead>
    <tbody>
    @foreach($receiving->items as $item)
        <tr>
            <td>{{ $item->product->name }}</td>
            <td>{{ number_format($item->ordered_qty, 3) }}</td>
            <td>{{ $item->unit->name }}</td>
            <td>
                @if($receiving->received_at)
                    {{ number_format($item->received_qty, 3) }}
                @else
                    {{-- form="qty-form" associates this input with the form above --}}
                    <input type="number"
                           name="items[{{ $item->id }}][received_qty]"
                           form="qty-form"
                           class="form-control form-control-sm"
                           step="0.001" min="0"
                           value="{{ old('items.'.$item->id.'.received_qty', $item->received_qty) }}">
                @endif
            </td>
            <td>
                @if($receiving->received_at)
                    {{ $item->notes ?? '—' }}
                @else
                    <input type="text"
                           name="items[{{ $item->id }}][notes]"
                           form="qty-form"
                           class="form-control form-control-sm"
                           value="{{ old('items.'.$item->id.'.notes', $item->notes) }}">
                @endif
            </td>
            <td style="min-width:160px">
                @if($item->proof_image)
                    <a href="{{ Storage::url($item->proof_image) }}" target="_blank">
                        <img src="{{ Storage::url($item->proof_image) }}"
                             alt="proof"
                             class="img-thumbnail mb-2"
                             style="max-width:100px; max-height:75px; object-fit:cover; cursor:zoom-in; display:block;">
                    </a>
                    <button type="submit"
                            form="proof-remove-form-{{ $item->id }}"
                            class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Remove this proof image?')">
                        Remove
                    </button>
                @else
                    <div class="d-flex align-items-center gap-1">
                        <input type="file"
                               id="proof-input-{{ $item->id }}"
                               name="proof_image"
                               form="proof-form-{{ $item->id }}"
                               accept="image/*"
                               class="form-control form-control-sm"
                               style="max-width:160px">
                        <button type="submit"
                                form="proof-form-{{ $item->id }}"
                                class="btn btn-sm btn-success text-nowrap">
                            Upload
                        </button>
                    </div>
                @endif
            </td>
            <td>
                @php $complaints = $item->purchaseOrderItem->complaints; @endphp
                @if($complaints->isEmpty())
                    <a href="{{ route('purchase-order-items.complaints.create', $item->purchaseOrderItem) }}"
                       class="btn btn-sm btn-outline-danger">+ Complaint</a>
                @else
                    <a href="{{ route('purchase-orders.show', $receiving->purchaseOrder) }}"
                       class="btn btn-sm btn-danger">
                        Complaints ({{ $complaints->count() }})
                    </a>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

@if(!$receiving->received_at)
    <div class="d-flex gap-2">
        <button type="submit" form="qty-form" class="btn btn-primary">Save Quantities</button>
        <button type="submit" form="receive-form" class="btn btn-success"
                onclick="return confirm('Mark this delivery as received? This will update stock and RAB prices.')">
            Mark as Received
        </button>
    </div>
@endif

<div class="mt-3">
    <a href="{{ route('receivings.index') }}" class="btn btn-secondary">Back to Receiving</a>
    <a href="{{ route('purchase-orders.show', $receiving->purchaseOrder) }}" class="btn btn-outline-secondary ms-2">View PO</a>
</div>
@endsection
