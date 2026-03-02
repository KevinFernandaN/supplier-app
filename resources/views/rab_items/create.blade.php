@extends('layouts.app')

@section('title', 'Add Ingredient')

@section('content')
<h1 class="h3 mb-3">Add Ingredient – {{ $rab->menu->name }}</h1>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('rabs.items.store', $rab) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                    <option value="">-- Select Product --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Unit</label>
                <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                    <option value="">-- Select Unit --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(old('unit_id') == $u->id)>
                            {{ $u->name }} ({{ $u->symbol }})
                        </option>
                    @endforeach
                </select>
                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Qty per Portion</label>
                <input type="number" step="any" name="qty"
                       class="form-control @error('qty') is-invalid @enderror"
                       value="{{ old('qty') }}" required>
                @error('qty')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Purchase Price / Unit (Rp)</label>
                <input type="number" step="any" name="purchase_price"
                       class="form-control @error('purchase_price') is-invalid @enderror"
                       value="{{ old('purchase_price', 0) }}" required>
                @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Add</button>
                <a href="{{ route('rabs.show', $rab) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
