@php
    $isEdit = isset($product);
@endphp

<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control"
           value="{{ old('name', $product->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Category</label>
    <input type="text" name="category" class="form-control"
           value="{{ old('category', $product->category ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">Base Unit</label>
    <select name="base_unit_id" class="form-select" required>
        <option value="">-- Select unit --</option>
        @foreach ($units as $unit)
            <option value="{{ $unit->id }}"
                @selected(old('base_unit_id', $product->base_unit_id ?? '') == $unit->id)>
                {{ $unit->name }} ({{ $unit->symbol }})
            </option>
        @endforeach
    </select>
</div>

<button class="btn btn-primary" type="submit">
    {{ $isEdit ? 'Update' : 'Create' }}
</button>
<a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
