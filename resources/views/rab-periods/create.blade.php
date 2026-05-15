@extends('layouts.app')

@section('title', 'New RAB Period')

@section('content')
<h1 class="h3 mb-3">New RAB Period</h1>

<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('rab-periods.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Period Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="e.g. MBG Week 2 – Sumbergondo" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date') }}" required>
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date') }}" required>
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">PK Price (Rp / student)</label>
                    <input type="number" name="pk_price" class="form-control @error('pk_price') is-invalid @enderror"
                           value="{{ old('pk_price', 8000) }}" min="0" required>
                    <div class="form-text">Porsi Kecil budget from pusat</div>
                    @error('pk_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">PB Price (Rp / student)</label>
                    <input type="number" name="pb_price" class="form-control @error('pb_price') is-invalid @enderror"
                           value="{{ old('pb_price', 10000) }}" min="0" required>
                    <div class="form-text">Porsi Besar budget from pusat</div>
                    @error('pb_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                    <option value="confirmed" @selected(old('status') === 'confirmed')>Confirmed</option>
                    <option value="locked" @selected(old('status') === 'locked')>Locked</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Create Period</button>
                <a href="{{ route('rab-periods.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
