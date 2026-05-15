@extends('layouts.app')

@section('title', 'Edit Ingredient – ' . $item->product->name)

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.index') }}">RAB Periods</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.show', $rabPeriod) }}">{{ $rabPeriod->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.days.show', [$rabPeriod, $day]) }}">{{ $day->day_date->format('d M Y') }}</a></li>
        <li class="breadcrumb-item active">Edit Ingredient</li>
    </ol>
</nav>

<h1 class="h3 mb-1">Edit Ingredient</h1>
<div class="text-muted small mb-3">
    Menu: <strong>{{ $menu->menu->name }}</strong>
    <span class="badge bg-secondary ms-1">{{ strtoupper($menu->category) }}</span>
</div>

<div class="card" style="max-width:600px">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label text-muted">Product</label>
            <div class="form-control bg-light">{{ $item->product->name }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted">Unit</label>
            <div class="form-control bg-light">{{ $item->unit->name }}</div>
        </div>

        <form method="POST" action="{{ route('rab-periods.days.menus.items.update', [$rabPeriod, $day, $menu, $item]) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" id="supplierSelect"
                        class="form-select @error('supplier_id') is-invalid @enderror">
                    <option value="">-- No Supplier --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}"
                                data-price="{{ $supplierPrices[$s->id] ?? '' }}"
                                @selected(old('supplier_id', $item->supplier_id) == $s->id)>
                            {{ $s->name }}
                            @if(isset($supplierPrices[$s->id]))
                                – LPP: Rp {{ number_format($supplierPrices[$s->id], 2) }}
                            @else
                                – no LPP
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Price per Unit (Rp)</label>
                <input type="number" name="purchase_price" id="purchasePriceInput"
                       class="form-control @error('purchase_price') is-invalid @enderror"
                       value="{{ old('purchase_price', $item->purchase_price) }}"
                       min="0" step="0.0001" required>
                <div class="form-text">Selecting a supplier with a known LPP auto-fills this field.</div>
                @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Kebutuhan PK (gram/porsi)</label>
                    <input type="number" name="pk_gramasi"
                           class="form-control @error('pk_gramasi') is-invalid @enderror"
                           value="{{ old('pk_gramasi', $item->pk_gramasi) }}"
                           min="0" step="0.001" required>
                    @error('pk_gramasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Kebutuhan PB (gram/porsi)</label>
                    <input type="number" name="pb_gramasi"
                           class="form-control @error('pb_gramasi') is-invalid @enderror"
                           value="{{ old('pb_gramasi', $item->pb_gramasi) }}"
                           min="0" step="0.001" required>
                    @error('pb_gramasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('rab-periods.days.show', [$rabPeriod, $day]) }}"
                   class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('supplierSelect').addEventListener('change', function () {
    const price = this.options[this.selectedIndex].getAttribute('data-price');
    if (price) {
        document.getElementById('purchasePriceInput').value = price;
    }
});
</script>
@endsection
