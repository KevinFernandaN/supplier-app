@php
    $isEdit = isset($supplier);
@endphp

<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control"
           value="{{ old('name', $supplier->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">WhatsApp Number</label>
    <input type="text" name="phone_wa" class="form-control"
           value="{{ old('phone_wa', $supplier->phone_wa ?? '') }}" placeholder="e.g. 628123456789">
    <div class="form-text">Use country code (62...), no plus sign is fine.</div>
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control" rows="3">{{ old('address', $supplier->address ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Latitude</label>
        <input type="text" name="latitude" class="form-control"
               value="{{ old('latitude', $supplier->latitude ?? '') }}" placeholder="-6.200000">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Longitude</label>
        <input type="text" name="longitude" class="form-control"
               value="{{ old('longitude', $supplier->longitude ?? '') }}" placeholder="106.816666">
    </div>
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="is_active" value="1"
           id="is_active" @checked(old('is_active', $supplier->is_active ?? true))>
    <label class="form-check-label" for="is_active">
        Active
    </label>
</div>

<button class="btn btn-primary" type="submit">
    {{ $isEdit ? 'Update' : 'Create' }}
</button>
<a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
