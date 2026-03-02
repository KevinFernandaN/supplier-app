@extends('layouts.app')

@section('title','Edit Return Item')

@section('content')
<h1 class="h3 mb-3">Edit Return Item</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('returns.items.update', [$returnOrder, $returnItem]) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select" required>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(old('product_id', $returnItem->product_id)==$p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Qty</label>
                <input type="number" step="0.001" name="qty" class="form-control"
                       value="{{ old('qty', $returnItem->qty) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Unit</label>
                <select name="unit_id" class="form-select" required>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(old('unit_id', $returnItem->unit_id)==$u->id)>{{ $u->name }} ({{ $u->symbol }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Reason</label>
                <input type="text" name="reason" class="form-control"
                       value="{{ old('reason', $returnItem->reason) }}">
            </div>

            <button class="btn btn-primary">Update</button>
            <a class="btn btn-secondary" href="{{ route('returns.show', $returnOrder) }}">Back</a>
        </form>
    </div>
</div>
@endsection
