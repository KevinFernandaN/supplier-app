@extends('layouts.app')

@section('title', 'Supplier – ' . $supplier->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $supplier->name }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary btn-sm">Edit</a>
        <a href="{{ route('suppliers.supplier-products.index', $supplier) }}" class="btn btn-primary btn-sm">Manage Products</a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
</div>

{{-- Basic Info --}}
<div class="card mb-4">
    <div class="card-header fw-semibold">Supplier Info</div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-6">
                <span class="text-muted">WhatsApp:</span> {{ $supplier->phone_wa ?? '-' }}
            </div>
            <div class="col-md-6">
                <span class="text-muted">Status:</span>
                @if($supplier->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </div>
            <div class="col-12">
                <span class="text-muted">Address:</span> {{ $supplier->address ?? '-' }}
            </div>
            @if($supplier->latitude)
            <div class="col-12">
                <span class="text-muted">Location:</span> {{ $supplier->latitude }}, {{ $supplier->longitude }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Photos --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Photos</h5>
    <a href="{{ route('suppliers.photos.create', $supplier) }}" class="btn btn-outline-secondary btn-sm">+ Upload Photo</a>
</div>

@if($supplier->photos->count())
    <div class="row g-3 mb-4">
        @foreach($supplier->photos as $photo)
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <img src="{{ Storage::url($photo->path) }}" class="card-img-top"
                         style="height:160px;object-fit:cover;" alt="{{ $photo->caption ?? 'Photo' }}">
                    <div class="card-body p-2">
                        @if($photo->caption)
                            <div class="small text-muted mb-1">{{ $photo->caption }}</div>
                        @endif
                        <form method="POST" action="{{ route('suppliers.photos.destroy', [$supplier, $photo]) }}"
                              onsubmit="return confirm('Delete this photo?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger w-100">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p class="text-muted small mb-4">No photos yet.</p>
@endif

{{-- Certifications --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Certifications</h5>
    <a href="{{ route('suppliers.certifications.create', $supplier) }}" class="btn btn-outline-secondary btn-sm">+ Attach Certification</a>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Certification</th>
                    <th>Issuer</th>
                    <th>Cert No.</th>
                    <th>Issued</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>File</th>
                    <th width="110">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supplier->certifications as $sc)
                    @php
                        $today = now()->startOfDay();
                        $soon  = now()->addDays(30)->startOfDay();
                        if (!$sc->expired_at) {
                            $statusBadge = '<span class="badge bg-secondary">No Expiry</span>';
                        } elseif ($sc->expired_at->lt($today)) {
                            $statusBadge = '<span class="badge bg-danger">Expired</span>';
                        } elseif ($sc->expired_at->lte($soon)) {
                            $statusBadge = '<span class="badge bg-warning text-dark">Expiring Soon</span>';
                        } else {
                            $statusBadge = '<span class="badge bg-success">Valid</span>';
                        }
                    @endphp
                    <tr>
                        <td>{{ $sc->certification->name }}</td>
                        <td>{{ $sc->certification->issuer ?? '-' }}</td>
                        <td>{{ $sc->certificate_no ?? '-' }}</td>
                        <td>{{ $sc->issued_at?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $sc->expired_at?->format('d M Y') ?? '-' }}</td>
                        <td>{!! $statusBadge !!}</td>
                        <td>
                            @if($sc->file_path)
                                <a href="{{ Storage::url($sc->file_path) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary">View</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('suppliers.certifications.edit', [$supplier, $sc]) }}"
                               class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST"
                                  action="{{ route('suppliers.certifications.destroy', [$supplier, $sc]) }}"
                                  class="d-inline" onsubmit="return confirm('Remove this certification?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">✕</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-3 text-muted">No certifications attached.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- KPI History --}}
<h5 class="mb-2">KPI History</h5>
@if($latestKpi)
    <div class="mb-2 small text-muted">
        Latest: <strong>{{ $latestKpi->month }}</strong> &mdash;
        KPI: <strong>{{ number_format($latestKpi->kpi_score, 2) }}</strong> &mdash;
        Reviews: {{ $latestKpi->review_count }}
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>KPI Score</th>
                        <th>Reviews</th>
                        <th>Goods</th>
                        <th>Weight</th>
                        <th>On Time</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kpiHistory as $k)
                        <tr>
                            <td>{{ $k->month }}</td>
                            <td>{{ number_format($k->kpi_score, 2) }}</td>
                            <td>{{ $k->review_count }}</td>
                            <td>{{ number_format($k->avg_goods_correct, 2) }}</td>
                            <td>{{ number_format($k->avg_weight_correct, 2) }}</td>
                            <td>{{ number_format($k->avg_on_time, 2) }}</td>
                            <td>{{ number_format($k->avg_price_correct, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="text-muted small">No KPI yet. Run <code>php artisan kpi:monthly</code> after reviews.</div>
@endif
@endsection
