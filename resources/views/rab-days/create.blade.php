@extends('layouts.app')

@section('title', 'Add Day – ' . $rabPeriod->name)

@section('content')
<h1 class="h3 mb-1">Add Day</h1>
<p class="text-muted mb-3">
    Period: <strong>{{ $rabPeriod->name }}</strong>
    ({{ $rabPeriod->start_date->format('d M Y') }} – {{ $rabPeriod->end_date->format('d M Y') }})
</p>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('rab-periods.days.store', $rabPeriod) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="text" name="day_date" id="dayDatePicker"
                       class="form-control @error('day_date') is-invalid @enderror"
                       value="{{ old('day_date') }}"
                       placeholder="Select a date" required readonly>
                @error('day_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">PK Count (Porsi Kecil)</label>
                    <input type="number" name="pk_count"
                           class="form-control @error('pk_count') is-invalid @enderror"
                           value="{{ old('pk_count', 0) }}" min="0" required>
                    @error('pk_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">PB Count (Porsi Besar)</label>
                    <input type="number" name="pb_count"
                           class="form-control @error('pb_count') is-invalid @enderror"
                           value="{{ old('pb_count', 0) }}" min="0" required>
                    @error('pb_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="alert alert-info small mb-3">
                Budget for this day will be auto-calculated:<br>
                <code>(PK × Rp {{ number_format($rabPeriod->pk_price) }}) + (PB × Rp {{ number_format($rabPeriod->pb_price) }})</code><br>
                Fill in <strong>Realisasi</strong> later (via Edit) once actual field spending is confirmed.
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success">Add Day</button>
                <a href="{{ route('rab-periods.show', $rabPeriod) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr('#dayDatePicker', {
    dateFormat: 'Y-m-d',
    minDate: '{{ $rabPeriod->start_date->toDateString() }}',
    maxDate: '{{ $rabPeriod->end_date->toDateString() }}',
    disable: @json($usedDates),
    disableMobile: true,
});
</script>
@endsection
