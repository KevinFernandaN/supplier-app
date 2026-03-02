@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Create Purchase Order</h1>

<form method="POST" action="{{ route('purchase-orders.store') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select" required>
            <option value="">-- Select Supplier --</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Order Date</label>
        <input type="date" name="order_date" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Expected Delivery</label>
        <input type="date" name="expected_delivery_date" class="form-control">
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-select">
            <option value="draft">Draft</option>
            <option value="confirmed">Confirmed</option>
            <option value="received">Received</option>
        </select>
    </div>

    <button class="btn btn-primary">Create</button>
</form>
@endsection
