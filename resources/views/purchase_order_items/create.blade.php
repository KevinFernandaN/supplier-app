@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Add Item to PO</h1>

<form method="POST"
      action="{{ route('purchase-orders.items.store', $purchaseOrder) }}">
    @csrf

    <div class="mb-3">
        <label>Product</label>
        <select name="product_id" class="form-select" required>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Unit</label>
        <select name="unit_id" class="form-select" required>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}">{{ $unit->symbol }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Quantity</label>
        <input type="number" step="0.001" name="qty" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Purchase Price</label>
        <input type="number" name="purchase_price" class="form-control" required>
    </div>

    <button class="btn btn-primary">Add Item</button>
</form>
@endsection
