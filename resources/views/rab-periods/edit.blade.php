@extends('layouts.app')

@section('title', 'Edit RAB Period')

@section('content')
<h1 class="h3 mb-3">Edit RAB Period</h1>

<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('rab-periods.update', $rabPeriod) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Period Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $rabPeriod->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date', $rabPeriod->start_date->toDateString()) }}" required>
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date', $rabPeriod->end_date->toDateString()) }}" required>
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">PK Price (Rp / student)</label>
                    <input type="number" name="pk_price" class="form-control @error('pk_price') is-invalid @enderror"
                           value="{{ old('pk_price', $rabPeriod->pk_price) }}" min="0" required>
                    @error('pk_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">PB Price (Rp / student)</label>
                    <input type="number" name="pb_price" class="form-control @error('pb_price') is-invalid @enderror"
                           value="{{ old('pb_price', $rabPeriod->pb_price) }}" min="0" required>
                    @error('pb_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="draft" @selected(old('status', $rabPeriod->status) === 'draft')>Draft</option>
                    <option value="confirmed" @selected(old('status', $rabPeriod->status) === 'confirmed')>Confirmed</option>
                    <option value="locked" @selected(old('status', $rabPeriod->status) === 'locked')>Locked</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $rabPeriod->notes) }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('rab-periods.show', $rabPeriod) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
