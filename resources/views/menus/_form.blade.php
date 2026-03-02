<div class="mb-3">
    <label class="form-label">Menu Name</label>
    <input type="text"
           name="name"
           class="form-control"
           value="{{ old('name', $menu->name ?? '') }}"
           required>
</div>

<div class="mb-3">
    <label class="form-label">Default Selling Price (Rp)</label>
    <input type="number"
           name="default_selling_price"
           class="form-control"
           value="{{ old('default_selling_price', $menu->default_selling_price ?? '') }}"
           min="0">
</div>

<button class="btn btn-primary">
    {{ isset($menu) ? 'Update' : 'Create' }}
</button>

<a href="{{ route('menus.index') }}" class="btn btn-secondary">Cancel</a>
