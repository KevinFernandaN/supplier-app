@extends('layouts.app')

@section('title', 'Edit Unit')

@section('content')
<h1 class="h3 mb-4">Edit Unit</h1>

<div class="card" style="max-width: 480px;">
    <div class="card-body">
        <form method="POST" action="{{ route('units.update', $unit) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $unit->name) }}" placeholder="e.g. Kilogram">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Symbol <span class="text-danger">*</span></label>
                <input type="text" name="symbol" class="form-control @error('symbol') is-invalid @enderror"
                       value="{{ old('symbol', $unit->symbol) }}" placeholder="e.g. kg" style="max-width: 120px;">
                @error('symbol') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
