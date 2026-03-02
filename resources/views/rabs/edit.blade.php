@extends('layouts.app')

@section('title', 'Edit RAB')

@section('content')
<h1 class="h3 mb-3">Edit RAB – {{ $rab->menu->name }}</h1>

<div class="card" style="max-width:560px">
    <div class="card-body">
        <form method="POST" action="{{ route('rabs.update', $rab) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Menu</label>
                <select name="menu_id" class="form-select @error('menu_id') is-invalid @enderror" required>
                    @foreach($menus as $m)
                        <option value="{{ $m->id }}" @selected(old('menu_id', $rab->menu_id) == $m->id)>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
                @error('menu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text text-warning small">Changing the menu does NOT re-populate ingredients.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">RAB Date</label>
                <input type="date" name="rab_date" class="form-control @error('rab_date') is-invalid @enderror"
                       value="{{ old('rab_date', $rab->rab_date) }}" required>
                @error('rab_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Selling Price / Portion (Rp)</label>
                <input type="number" step="0.01" name="selling_price"
                       class="form-control @error('selling_price') is-invalid @enderror"
                       value="{{ old('selling_price', $rab->selling_price) }}" required>
                @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $rab->notes) }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('rabs.show', $rab) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
