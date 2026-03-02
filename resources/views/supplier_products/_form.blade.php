<div class="mb-3">
    <label class="form-label">Product</label>
    <select name="product_id" class="form-select" required>
        <option value="">-- Select Product --</option>
        @foreach ($products as $p)
            <option value="{{ $p->id }}"
                @selected(old('product_id', $supplierProduct->product_id ?? '') == $p->id)>
                {{ $p->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Specification</label>
    <textarea name="specification_text" class="form-control" rows="3">{{ old('specification_text', $supplierProduct->specification_text ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Lead Time (days)</label>
        <input type="number" name="lead_time_days" class="form-control"
               value="{{ old('lead_time_days', $supplierProduct->lead_time_days ?? '') }}" min="0" placeholder="1 or 2">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Min Order Qty</label>
        <input type="text" name="min_order_qty" class="form-control"
               value="{{ old('min_order_qty', $supplierProduct->min_order_qty ?? '') }}" placeholder="e.g. 10">
    </div>

    <div class="col-md-4 mb-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   id="is_active" @checked(old('is_active', $supplierProduct->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary">
    {{ isset($supplierProduct) ? 'Update' : 'Add Product' }}
</button>
