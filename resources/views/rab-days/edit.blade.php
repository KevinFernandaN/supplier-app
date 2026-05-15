@extends('layouts.app')

@section('title', 'Edit Day – ' . $day->day_date->format('d M Y'))

@section('content')
<h1 class="h3 mb-1">Edit Day</h1>
<p class="text-muted mb-3">
    Period: <strong>{{ $rabPeriod->name }}</strong>
</p>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('rab-periods.days.update', [$rabPeriod, $day]) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="day_date"
                       class="form-control @error('day_date') is-invalid @enderror"
                       value="{{ old('day_date', $day->day_date->toDateString()) }}"
                       min="{{ $rabPeriod->start_date->toDateString() }}"
                       max="{{ $rabPeriod->end_date->toDateString() }}"
                       required>
                @error('day_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">PK Count (Porsi Kecil)</label>
                    <input type="number" name="pk_count"
                           class="form-control @error('pk_count') is-invalid @enderror"
                           value="{{ old('pk_count', $day->pk_count) }}" min="0" required>
                    @error('pk_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">PB Count (Porsi Besar)</label>
                    <input type="number" name="pb_count"
                           class="form-control @error('pb_count') is-invalid @enderror"
                           value="{{ old('pb_count', $day->pb_count) }}" min="0" required>
                    @error('pb_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Realisasi (Rp)</label>
                <input type="number" step="0.01" name="realisasi"
                       class="form-control @error('realisasi') is-invalid @enderror"
                       value="{{ old('realisasi', $day->realisasi) }}" min="0">
                <div class="form-text">Actual budget spent on the field. Fill in once field prices are confirmed.</div>
                @error('realisasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('rab-periods.show', $rabPeriod) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
