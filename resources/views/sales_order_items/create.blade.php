@extends('layouts.app')

@section('title', 'Add Sales Item')

@section('content')
<h1 class="h3 mb-3">Add Item ({{ $salesOrder->sale_date->format('Y-m-d') }})</h1>

<form method="POST" action="{{ route('sales-orders.items.store', $salesOrder) }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Menu</label>
        <select name="menu_id" id="menu_id" class="form-select" required>
            <option value="">-- Select Menu --</option>
            @foreach($menus as $menu)
                <option value="{{ $menu->id }}">{{ $menu->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" step="0.001" name="qty" class="form-control"
               value="{{ old('qty') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Selling Price (Rp)</label>
        <input type="number" name="selling_price" id="selling_price" class="form-control"
               value="{{ old('selling_price') }}" required min="0">
        <div class="form-text">Auto-filled from menu default selling price (still editable).</div>
    </div>

    <button class="btn btn-primary">Add Item</button>
    <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-secondary">Back</a>
</form>

<script type="application/json" id="menu-prices-data">{{ json_encode($menuPrices) }}</script>

<script>
    // Simple auto-fill selling price based on selected menu
    const menuPrices = JSON.parse(document.getElementById('menu-prices-data').textContent);
    const menuSelect = document.getElementById('menu_id');
    const priceInput = document.getElementById('selling_price');

    menuSelect.addEventListener('change', function () {
        const menuId = this.value;
        if (menuId && menuPrices[menuId] !== undefined) {
            priceInput.value = Math.round(menuPrices[menuId]);
        }
    });
</script>
@endsection
