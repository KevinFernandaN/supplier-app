@extends('layouts.app')

@section('title', 'Best Supplier per Product')

@section('content')
<h1 class="h3 mb-3">Best Supplier per Product</h1>

<form class="row g-2 mb-3" method="GET" action="{{ route('boss.best-suppliers.index') }}">
    <div class="col-md-4">
        <label class="form-label">Product</label>
        <select name="product_id" class="form-select" required>
            <option value="">-- Select Product --</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" @selected((string)$productId === (string)$p->id)>
                    {{ $p->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Month</label>
        <input type="month" name="month" class="form-control" value="{{ $month }}">
        <div class="form-text">Uses KPI month.</div>
    </div>

    <div class="col-md-2">
        <label class="form-label">Min KPI</label>
        <input type="number" step="0.01" name="min_kpi" class="form-control" value="{{ $minKpi }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">Max Premium %</label>
        <input type="number" step="0.01" name="max_premium_pct" class="form-control" value="{{ $maxPremiumPct }}">
        <div class="form-text">Above cheapest eligible.</div>
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Apply</button>
    </div>

    @if($productId)
    <a class="btn btn-outline-secondary"
        href="{{ route('boss.best-suppliers.export', request()->query()) }}">
            Export CSV
    </a>
    @endif
</form>

@if($productId)
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Cheapest Eligible Price</div>
                    <div class="fs-4 fw-bold">
                        @if($cheapestEligiblePrice !== null)
                            Rp {{ number_format($cheapestEligiblePrice, 0) }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="text-muted mt-2">
                        Price cap ({{ $maxPremiumPct }}%):
                        @if($priceCap !== null)
                            Rp {{ number_format($priceCap, 0) }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="text-muted">
                        KPI threshold: {{ number_format($minKpi, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Recommended Supplier</div>
                    <div class="fs-4 fw-bold">
                        @if($recommended)
                            {{ $recommended->supplier_name }}
                        @else
                            No eligible supplier
                        @endif
                    </div>
                    @if($recommended)
                        <div class="mt-2">
                            <div><strong>Price:</strong> Rp {{ number_format($recommended->latest_price, 0) }}</div>
                            <div><strong>KPI:</strong> {{ number_format($recommended->kpi_score, 2) }} ({{ $recommended->review_count }} reviews)</div>
                            <div class="text-muted small">Complaint penalty: -{{ number_format($recommended->complaint_penalty, 2) }} &nbsp;|&nbsp; Return penalty: -{{ number_format($recommended->return_penalty, 2) }} ({{ $recommended->return_count }} returns)</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-semibold">All Suppliers for Selected Product</div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Latest Price</th>
                        <th>KPI ({{ $month }})</th>
                        <th>Reviews</th>
                        <th>Complaint Penalty</th>
                        <th>Return Penalty</th>
                        <th>Eligible?</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        @php
                            $hasPrice = $r->latest_price !== null;
                            $hasKpi = $r->kpi_score !== null;
                            $eligibleFlag = $hasPrice && $hasKpi && $r->kpi_score >= $minKpi;
                        @endphp
                        <tr @if($recommended && $r->supplier_id == $recommended->supplier_id) class="table-success" @endif>
                            <td>{{ $r->supplier_name }}</td>
                            <td>
                                @if($hasPrice) Rp {{ number_format($r->latest_price, 0) }} @else - @endif
                            </td>
                            <td>
                                @if($hasKpi) {{ number_format($r->kpi_score, 2) }} @else - @endif
                            </td>
                            <td>{{ $r->review_count }}</td>
                            <td class="text-end">
                                @if($hasKpi) -{{ number_format($r->complaint_penalty, 2) }} @else - @endif
                            </td>
                            <td class="text-end">
                                @if($hasKpi) -{{ number_format($r->return_penalty, 2) }} ({{ $r->return_count }}) @else - @endif
                            </td>
                            <td>
                                @if($eligibleFlag)
                                    <span class="badge bg-success">Eligible</span>
                                @else
                                    <span class="badge bg-secondary">Not eligible</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">No suppliers found for this product.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
