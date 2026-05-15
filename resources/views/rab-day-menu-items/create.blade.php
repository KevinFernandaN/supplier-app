@extends('layouts.app')

@section('title', 'Add Ingredient – ' . $menu->menu->name)

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.index') }}">RAB Periods</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.show', $rabPeriod) }}">{{ $rabPeriod->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.days.show', [$rabPeriod, $day]) }}">{{ $day->day_date->format('d M Y') }}</a></li>
        <li class="breadcrumb-item active">Add Ingredient</li>
    </ol>
</nav>

<h1 class="h3 mb-1">Add Ingredient</h1>
<div class="text-muted small mb-3">
    Menu: <strong>{{ $menu->menu->name }}</strong>
    <span class="badge bg-secondary ms-1">{{ strtoupper($menu->category) }}</span>
</div>

<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('rab-periods.days.menus.items.store', [$rabPeriod, $day, $menu]) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                    <option value="">-- Select Product --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Unit</label>
                <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                    <option value="">-- Select Unit --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(old('unit_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Supplier <span class="text-muted">(optional)</span></label>
                <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                    <option value="">-- No Supplier --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Price per Unit (Rp)</label>
                <input type="number" name="purchase_price"
                       class="form-control @error('purchase_price') is-invalid @enderror"
                       value="{{ old('purchase_price', 0) }}" min="0" step="0.0001" required>
                @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Kebutuhan PK (gram/porsi)</label>
                    <input type="number" name="pk_gramasi"
                           class="form-control @error('pk_gramasi') is-invalid @enderror"
                           value="{{ old('pk_gramasi', 0) }}" min="0" step="0.001" required>
                    @error('pk_gramasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Kebutuhan PB (gram/porsi)</label>
                    <input type="number" name="pb_gramasi"
                           class="form-control @error('pb_gramasi') is-invalid @enderror"
                           value="{{ old('pb_gramasi', 0) }}" min="0" step="0.001" required>
                    @error('pb_gramasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success">Add Ingredient</button>
                <a href="{{ route('rab-periods.days.show', [$rabPeriod, $day]) }}"
                   class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
