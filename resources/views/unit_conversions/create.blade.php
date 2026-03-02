@extends('layouts.app')

@section('title', 'Add Unit Conversion')

@section('content')
<h1 class="h3 mb-3">Add Unit Conversion</h1>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('unit-conversions.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">From Unit</label>
                <select name="from_unit_id" class="form-select @error('from_unit_id') is-invalid @enderror" required>
                    <option value="">-- Select --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(old('from_unit_id') == $u->id)>
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
                        <option value="{{ $u->id }}" @selected(old('to_unit_id') == $u->id)>
                            {{ $u->name }} ({{ $u->symbol }})
                        </option>
                    @endforeach
                </select>
                @error('to_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Multiplier</label>
                <input type="number" step="any" name="multiplier" class="form-control @error('multiplier') is-invalid @enderror"
                       value="{{ old('multiplier') }}" placeholder="e.g. 0.001" required>
                <div class="form-text">qty_to = qty_from × multiplier. Example: g → kg = 0.001</div>
                @error('multiplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('unit-conversions.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
