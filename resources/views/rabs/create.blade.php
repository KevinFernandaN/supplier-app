@extends('layouts.app')

@section('title', 'New RAB')

@section('content')
<h1 class="h3 mb-3">New RAB – Budget Plan</h1>

<div class="card" style="max-width:560px">
    <div class="card-body">
        <form method="POST" action="{{ route('rabs.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Menu</label>
                <select name="menu_id" id="menuSelect" class="form-select @error('menu_id') is-invalid @enderror" required>
                    <option value="">-- Select Menu --</option>
                    @foreach($menus as $m)
                        <option value="{{ $m->id }}"
                                data-price="{{ $m->default_selling_price }}"
                                @selected(old('menu_id') == $m->id)>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
                @error('menu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">RAB Date</label>
                <input type="date" name="rab_date" class="form-control @error('rab_date') is-invalid @enderror"
                       value="{{ old('rab_date', now()->toDateString()) }}" required>
                <div class="form-text">Ingredient prices will be looked up as of this date.</div>
                @error('rab_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Selling Price / Portion (Rp)</label>
                <input type="number" step="0.01" name="selling_price" id="sellingPrice"
                       class="form-control @error('selling_price') is-invalid @enderror"
                       value="{{ old('selling_price', '') }}" required>
                <div class="form-text">Auto-filled from menu default. You can override.</div>
                @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="alert alert-info small mb-3">
                Ingredients will be auto-populated from the menu recipe with the last known purchase price.
                You can adjust quantities and prices after creation.
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Create RAB</button>
                <a href="{{ route('rabs.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('menuSelect').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const price = selected.dataset.price;
    const field = document.getElementById('sellingPrice');
    if (price && !field.value) {
        field.value = price;
    }
});
</script>
@endsection
