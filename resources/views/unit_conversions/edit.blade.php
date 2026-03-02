@extends('layouts.app')

@section('title', 'Edit Unit Conversion')

@section('content')
<h1 class="h3 mb-3">Edit Unit Conversion</h1>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('unit-conversions.update', $unitConversion) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">From Unit</label>
                <select name="from_unit_id" class="form-select @error('from_unit_id') is-invalid @enderror" required>
                    <option value="">-- Select --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(old('from_unit_id', $unitConversion->from_unit_id) == $u->id)>
                            {{ $u->name }} ({{ $u->symbol }})
                        </option>
                    @endforeach
                </select>
                @error('from_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">To Unit</label>
                <select name="to_unit_id" class="form-select @error('to_unit_id') is-invalid @enderror" required>
                    <option value="">-- Select --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(old('to_unit_id', $unitConversion->to_unit_id) == $u->id)>
                            {{ $u->name }} ({{ $u->symbol }})
                        </option>
                    @endforeach
                </select>
                @error('to_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Multiplier</label>
                <input type="number" step="any" name="multiplier" class="form-control @error('multiplier') is-invalid @enderror"
                       value="{{ old('multiplier', $unitConversion->multiplier) }}" required>
                <div class="form-text">qty_to = qty_from × multiplier.</div>
                @error('multiplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('unit-conversions.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
