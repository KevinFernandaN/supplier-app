@extends('layouts.app')

@section('title', 'PR #' . $purchaseRequest->id)

@section('content')

{{-- Buffer update forms declared at top to avoid nesting issues --}}
@foreach ($purchaseRequest->items as $item)
<form id="buffer-form-{{ $item->id }}"
      action="{{ route('purchase-requests.items.update', [$purchaseRequest, $item]) }}"
      method="POST" style="display:none;">
    @csrf
    @method('PATCH')
</form>
@endforeach

{{-- Confirm / Reopen forms --}}
<form id="confirm-form" action="{{ route('purchase-requests.confirm', $purchaseRequest) }}" method="POST" style="display:none;">
    @csrf
</form>
<form id="reopen-form" action="{{ route('purchase-requests.reopen', $purchaseRequest) }}" method="POST" style="display:none;">
    @csrf
</form>
<form id="delete-form" action="{{ route('purchase-requests.destroy', $purchaseRequest) }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Purchase Request #{{ $purchaseRequest->id }}</h1>
        <small class="text-muted">{{ $purchaseRequest->created_at->format('d M Y, H:i') }}</small>
    </div>
    <div class="d-flex gap-2">
        @if ($purchaseRequest->status === 'draft')
            <button class="btn btn-success" form="confirm-form">Confirm PR</button>
            <button class="btn btn-outline-danger"
                    onclick="return confirm('Delete this PR?');" form="delete-form">Delete</button>
        @elseif ($purchaseRequest->status === 'confirmed')
            <a href="{{ route('purchase-requests.orders.create', $purchaseRequest) }}"
               class="btn btn-success">Create Orders</a>
            <button class="btn btn-outline-secondary" form="reopen-form">Re-open to Draft</button>
        @endif
        <a href="{{ route('purchase-requests.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

{{-- Summary card --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Kitchen</div>
                <div class="fw-semibold">{{ $purchaseRequest->kitchen->name }}</div>
                <div class="small text-muted">{{ $purchaseRequest->kitchen->region->name ?? '' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Kitchen Type</div>
                @if ($purchaseRequest->kitchen->type === 'assisted')
                    <span class="badge bg-info text-dark fs-6">Assisted</span>
                @else
                    <span class="badge bg-secondary fs-6">Open</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Menu</div>
                <div class="fw-semibold">{{ $purchaseRequest->menu->name }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Portions</div>
                <div class="fw-semibold fs-5">{{ number_format($purchaseRequest->total_portion, 0) }}</div>
            </div>
        </div>
    </div>
</div>

@if ($purchaseRequest->notes)
    <div class="alert alert-light border mb-4">{{ $purchaseRequest->notes }}</div>
@endif

{{-- Status badge --}}
<div class="mb-3">
    @if ($purchaseRequest->status === 'draft')
        <span class="badge bg-secondary">Draft — you can still edit buffers</span>
    @elseif ($purchaseRequest->status === 'confirmed')
        <span class="badge bg-primary">Confirmed — ready to create orders</span>
    @else
        <span class="badge bg-success">Ordered</span>
    @endif
</div>

{{-- Items table --}}
<div class="card">
    <div class="card-header fw-semibold">Ingredient Requirements</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                <tr>
                    <th>Ingredient</th>
                    <th class="text-end">Required Qty</th>
                    <th style="width: 160px;">Buffer % <span class="text-muted fw-normal small">(max 20%)</span></th>
                    <th class="text-end">Final Qty</th>
                    <th>Unit</th>
                    @if ($purchaseRequest->status === 'draft')
                        <th style="width: 80px;"></th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach ($purchaseRequest->items as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->product->name }}</td>
                        <td class="text-end">{{ number_format($item->required_qty, 3) }}</td>
                        <td>
                            @if ($purchaseRequest->status === 'draft')
                                <input type="number" name="buffer_pct" class="form-control form-control-sm"
                                       form="buffer-form-{{ $item->id }}"
                                       value="{{ $item->buffer_pct }}" min="0" max="20" step="0.5">
                            @else
                                {{ $item->buffer_pct }}%
                            @endif
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($item->final_qty, 3) }}</td>
                        <td class="text-muted">{{ $item->unit->symbol ?? $item->unit->name }}</td>
                        @if ($purchaseRequest->status === 'draft')
                            <td>
                                <button type="submit" form="buffer-form-{{ $item->id }}"
                                        class="btn btn-sm btn-outline-primary">Save</button>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($purchaseRequest->status === 'confirmed')
    <div class="alert alert-info mt-4">
        PR is confirmed. Click <strong>Create Orders</strong> above to assign vendors per ingredient and generate Purchase Orders.
    </div>
@elseif ($purchaseRequest->status === 'ordered')
    <div class="alert alert-success mt-4">
        Orders have been created from this PR. Check <a href="{{ route('purchase-orders.index') }}">Purchase Orders</a>.
    </div>
@endif

@endsection
