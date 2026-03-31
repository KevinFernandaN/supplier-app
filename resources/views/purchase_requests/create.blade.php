@extends('layouts.app')

@section('title', 'New Purchase Request')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">New Purchase Request</h1>
    <a href="{{ route('purchase-requests.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('purchase-requests.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Kitchen <span class="text-danger">*</span></label>
                <select name="kitchen_id" class="form-select @error('kitchen_id') is-invalid @enderror" required>
                    <option value="">-- Select Kitchen --</option>
                    @foreach ($kitchens as $kitchen)
                        <option value="{{ $kitchen->id }}" @selected(old('kitchen_id') == $kitchen->id)>
                            {{ $kitchen->name }}
                            <span class="text-muted">({{ $kitchen->region->name ?? '' }})</span>
                            — {{ ucfirst($kitchen->type) }}
                        </option>
                    @endforeach
                </select>
                @error('kitchen_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Menu <span class="text-danger">*</span></label>
                <select name="menu_id" class="form-select @error('menu_id') is-invalid @enderror" required>
                    <option value="">-- Select Menu --</option>
                    @foreach ($menus as $menu)
                        <option value="{{ $menu->id }}" @selected(old('menu_id') == $menu->id)>
                            {{ $menu->name }}
                        </option>
                    @endforeach
                </select>
                @error('menu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Total Portions <span class="text-danger">*</span></label>
                <input type="number" name="total_portion" class="form-control @error('total_portion') is-invalid @enderror"
                       value="{{ old('total_portion') }}" min="1" step="1" required>
                <div class="form-text">Ingredient quantities will be auto-calculated from the menu recipe × portions.</div>
                @error('total_portion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary">Generate PR</button>
        </form>
    </div>
</div>
@endsection
