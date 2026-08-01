@extends('layouts.app')

@section('title', $rabPeriod->name)

@section('content')

{{-- Period header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h3 mb-1">{{ $rabPeriod->name }}</h1>
        <div class="text-muted small">
            {{ $rabPeriod->start_date->format('d M Y') }} &rarr; {{ $rabPeriod->end_date->format('d M Y') }}
            &nbsp;|&nbsp;
            PK: Rp {{ number_format($rabPeriod->pk_price) }} &nbsp;|&nbsp;
            PB: Rp {{ number_format($rabPeriod->pb_price) }}
            &nbsp;|&nbsp;
            @php
                $badge = match($rabPeriod->status) {
                    'draft'     => 'secondary',
                    'confirmed' => 'primary',
                    'locked'    => 'success',
                    default     => 'secondary',
                };
            @endphp
            <span class="badge bg-{{ $badge }}">{{ ucfirst($rabPeriod->status) }}</span>
        </div>
        @if($rabPeriod->notes)
            <div class="text-muted small mt-1">{{ $rabPeriod->notes }}</div>
        @endif
    </div>
    <div class="d-flex gap-2 flex-shrink-0">
        <a href="{{ route('rab-periods.days.create', $rabPeriod) }}" class="btn btn-success">+ Add Day</a>
        <a href="{{ route('rab-periods.report', $rabPeriod) }}" class="btn btn-outline-primary">View Report</a>
        @if($rabPeriod->status !== 'draft')
            @if($rabPeriod->isPastPrLockDate())
                <button type="button" class="btn btn-outline-secondary" disabled
                        title="Closed — H-1 lock date ({{ $rabPeriod->prLockDate()->format('d M Y') }}) has passed">
                    Send to PR (Closed)
                </button>
            @else
                <a href="{{ route('rab-periods.purchase-requests.create', $rabPeriod) }}" class="btn btn-outline-success">Send to Purchase Request</a>
            @endif
        @endif
        <div class="btn-group">
            <a href="{{ route('rab-periods.export', $rabPeriod) }}?format=xlsx" class="btn btn-success">Export Excel</a>
            <a href="{{ route('rab-periods.export', $rabPeriod) }}?format=csv" class="btn btn-outline-secondary">Export CSV</a>
            <a href="{{ route('rab-periods.export', $rabPeriod) }}" class="btn btn-outline-secondary">Export JSON</a>
        </div>
        <a href="{{ route('rab-periods.edit', $rabPeriod) }}" class="btn btn-outline-secondary">Edit Period</a>
        <form method="POST" action="{{ route('rab-periods.destroy', $rabPeriod) }}"
              onsubmit="return confirm('Delete this entire period?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger">Delete</button>
        </form>
    </div>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="small text-muted">Total Budget</div>
                <div class="fs-5 fw-bold text-primary">Rp {{ number_format($totalBudget, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="small text-muted">Total RFC (Raw Food Cost)</div>
                <div class="fs-5 fw-bold text-danger">Rp {{ number_format($totalRfc, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="small text-muted">Net SISA (Period)</div>
                <div class="fs-5 fw-bold {{ $netSurplus >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($netSurplus, 0) }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Days table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Daily Breakdown</span>
        <a href="{{ route('rab-periods.days.create', $rabPeriod) }}" class="btn btn-sm btn-success">+ Add Day</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle text-end">
            <thead>
                <tr>
                    <th class="text-start">Date</th>
                    <th>PK</th>
                    <th>PB</th>
                    <th>Budget (Rp)</th>
                    <th>Realisasi (Rp)</th>
                    <th>RFC (Rp)</th>
                    <th>Day SISA (Rp)</th>
                    <th class="text-center" width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dayStats as $stat)
                    @php $day = $stat['day']; @endphp
                    <tr>
                        <td class="text-start">
                            <strong>{{ $day->day_date->format('D, d M Y') }}</strong>
                        </td>
                        <td>{{ number_format($day->pk_count) }}</td>
                        <td>{{ number_format($day->pb_count) }}</td>
                        <td>{{ number_format($stat['budget'], 0) }}</td>
                        <td class="{{ $day->realisasi != 0 ? 'text-info' : 'text-muted' }}">
                            {{ number_format($day->realisasi, 0) }}
                        </td>
                        <td>{{ number_format($stat['rfc'], 2) }}</td>
                        <td class="{{ $stat['surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                            <strong>{{ number_format($stat['surplus'], 2) }}</strong>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('rab-periods.days.show', [$rabPeriod, $day]) }}"
                               class="btn btn-sm btn-outline-primary">Menus</a>
                            <a href="{{ route('rab-periods.days.edit', [$rabPeriod, $day]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('rab-periods.days.destroy', [$rabPeriod, $day]) }}"
                                  class="d-inline" onsubmit="return confirm('Remove this day and all its menus/items?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Del</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            No days added yet.
                            <a href="{{ route('rab-periods.days.create', $rabPeriod) }}">Add the first day</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($dayStats->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-start">Total</td>
                        <td class="text-end">{{ number_format($totalBudget, 0) }}</td>
                        <td></td>
                        <td class="text-end">{{ number_format($totalRfc, 2) }}</td>
                        <td class="text-end {{ $netSurplus >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($netSurplus, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@if($rabPeriod->purchaseRequests->isNotEmpty())
<div class="card mt-4">
    <div class="card-header fw-semibold">Purchase Requests Generated from this Period</div>
    <div class="card-body p-0">
        <table class="table table-sm table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Kitchen</th>
                    <th>Menu</th>
                    <th class="text-end">Portions</th>
                    <th>Status</th>
                    <th class="text-center" width="100">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rabPeriod->purchaseRequests as $pr)
                    <tr>
                        <td class="text-muted">{{ $pr->id }}</td>
                        <td>{{ $pr->kitchen->name }}</td>
                        <td>{{ $pr->menu->name ?? 'All Menus (Period)' }}</td>
                        <td class="text-end">{{ number_format($pr->total_portion, 0) }}</td>
                        <td>
                            @php
                                $prBadge = match($pr->status) {
                                    'draft'     => 'secondary',
                                    'confirmed' => 'primary',
                                    'ordered'   => 'success',
                                    default     => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $prBadge }}">{{ ucfirst($pr->status) }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('purchase-requests.show', $pr) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="mt-3">
    <a href="{{ route('rab-periods.index') }}" class="btn btn-outline-secondary">&larr; Back to Periods</a>
</div>
@endsection
