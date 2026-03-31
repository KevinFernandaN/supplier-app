@extends('layouts.app')

@section('title', 'New Kitchen')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">New Kitchen</h1>
    <a href="{{ route('kitchens.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('kitchens.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Region <span class="text-danger">*</span></label>
                <select name="region_id" class="form-select @error('region_id') is-invalid @enderror" required>
                    <option value="">-- Select Region --</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
                @error('region_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Kitchen Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="open" @selected(old('type', 'open') === 'open')>Open — manual vendor selection</option>
                    <option value="assisted" @selected(old('type') === 'assisted')>Assisted — system recommends vendor</option>
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Address</label>
                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                       value="{{ old('address') }}">
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           @checked(old('is_active', true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Kitchen</button>
        </form>
    </div>
</div>
@endsection
