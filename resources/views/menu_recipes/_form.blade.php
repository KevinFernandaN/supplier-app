<div class="mb-3">
    <label class="form-label">Product</label>
    <select name="product_id" class="form-select" required>
        <option value="">-- Select Product --</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}"
                @selected(old('product_id', $recipe->product_id ?? '') == $product->id)>
                {{ $product->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Unit</label>
    <select name="unit_id" class="form-select" required>
        @foreach($units as $unit)
            <option value="{{ $unit->id }}"
                @selected(old('unit_id', $recipe->unit_id ?? '') == $unit->id)>
                {{ $unit->symbol }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Quantity per portion</label>
    <input type="number" step="0.001" name="qty" class="form-control"
           value="{{ old('qty', $recipe->qty ?? '') }}" required>
</div>

<button class="btn btn-primary">
    {{ isset($recipe) ? 'Update Ingredient' : 'Add Ingredient' }}
</button>

<a href="{{ route('menus.recipes.index', $menu) }}" class="btn btn-secondary">Back</a>
