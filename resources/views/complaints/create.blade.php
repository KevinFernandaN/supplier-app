@extends('layouts.app')

@section('title', 'Add Complaint')

@section('content')
<h1 class="h3 mb-3">Add Complaint</h1>

<div class="card mb-3">
    <div class="card-body">
        <div><strong>Supplier:</strong> {{ $purchaseOrderItem->purchaseOrder->supplier->name }}</div>
        <div><strong>Product:</strong> {{ $purchaseOrderItem->product->name }}</div>
        <div><strong>PO Date:</strong> {{ $purchaseOrderItem->purchaseOrder->order_date }}</div>
        <div><strong>Ordered Qty:</strong> {{ $purchaseOrderItem->qty }} {{ $purchaseOrderItem->unit->symbol }}</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('purchase-order-items.complaints.store', $purchaseOrderItem) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Complaint Date</label>
                <input type="date" name="complaint_date" class="form-control"
                       value="{{ old('complaint_date', now()->toDateString()) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Complaint Type</label>
                <select name="complaint_type" class="form-select" required>
                    <option value="">-- Select type --</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected(old('complaint_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Type (required)</label>
                <input type="text" name="type" class="form-control"
                    value="{{ old('type') }}" placeholder="e.g. logistics / quality / packaging" required>
                <div class="form-text">This is your internal category field.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Severity (1 - 5)</label>
                <select name="severity" class="form-select" required>
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected(old('severity', 3) == $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Qty affected (optional)</label>
                <input type="number" step="0.001" name="qty" class="form-control"
                       value="{{ old('qty') }}" placeholder="e.g. 2">
                <div class="form-text">Use the same unit as the PO item ({{ $purchaseOrderItem->unit->symbol }}).</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="open" @selected(old('status', 'open') === 'open')>Open</option>
                    <option value="resolved" @selected(old('status') === 'resolved')>Resolved</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <button class="btn btn-primary">Save Complaint</button>
            <a href="{{ route('purchase-orders.show', $purchaseOrderItem->purchase_order_id) }}" class="btn btn-secondary">
                Back
            </a>
        </form>
    </div>
</div>
@endsection
