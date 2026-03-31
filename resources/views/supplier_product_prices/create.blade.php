@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Add Price</h1>
<p class="text-muted mb-4">
    {{ $supplierProduct->product->name }} &mdash; {{ $supplierProduct->supplier->name }}
</p>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('supplier-products.prices.store', $supplierProduct) }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Price (Rp)</label>
        <input type="number" name="price" class="form-control" step="0.01" min="0"
               value="{{ old('price') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Effective From</label>
        <input type="date" name="effective_from" class="form-control"
               value="{{ old('effective_from', date('Y-m-d')) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
    </div>

    <button class="btn btn-primary">Save Price</button>
    <a href="{{ route('suppliers.supplier-products.show', [$supplierProduct->supplier_id, $supplierProduct]) }}"
       class="btn btn-secondary ms-2">Cancel</a>
</form>
@endsection
