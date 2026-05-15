@extends('layouts.app')

@section('title', 'RAB Report – ' . $rabPeriod->name)

@section('content')
<style>
@media print {
    nav.navbar, .no-print { display: none !important; }
    .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
    .page-break { page-break-before: always; }
    body { font-size: 11px; }
    .container { max-width: 100% !important; padding: 0 !important; }
}
</style>

{{-- Actions (hidden on print) --}}
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('rab-periods.index') }}">RAB Periods</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rab-periods.show', $rabPeriod) }}">{{ $rabPeriod->name }}</a></li>
            <li class="breadcrumb-item active">Report</li>
        </ol>
    </nav>
    <div class="d-flex gap-2">
        <div class="btn-group">
            <a href="{{ route('rab-periods.export', $rabPeriod) }}?format=xlsx" class="btn btn-success">Export Excel</a>
            <a href="{{ route('rab-periods.export', $rabPeriod) }}?format=csv" class="btn btn-outline-secondary">Export CSV</a>
            <a href="{{ route('rab-periods.export', $rabPeriod) }}" class="btn btn-outline-secondary">Export JSON</a>
        </div>
        <button onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
    </div>
</div>

{{-- Report header --}}
<div class="mb-3">
    <h1 class="h3 mb-1">{{ $rabPeriod->name }}</h1>
    <div class="text-muted small">
        {{ $rabPeriod->start_date->format('d M Y') }} &rarr; {{ $rabPeriod->end_date->format('d M Y') }}
        &nbsp;|&nbsp; PK: Rp {{ number_format($rabPeriod->pk_price) }}
        &nbsp;|&nbsp; PB: Rp {{ number_format($rabPeriod->pb_price) }}
        @php
            $badge = match($rabPeriod->status) {
                'confirmed' => 'primary',
                'locked'    => 'success',
                default     => 'secondary',
            };
        @endphp
        &nbsp;|&nbsp; <span class="badge bg-{{ $badge }}">{{ ucfirst($rabPeriod->status) }}</span>
    </div>
    @if($rabPeriod->notes)
        <div class="text-muted small mt-1">{{ $rabPeriod->notes }}</div>
    @endif
</div>

{{-- Period summary --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <div class="small text-muted">Total Budget</div>
                <div class="fw-bold text-primary">Rp {{ number_format($totalBudget, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <div class="small text-muted">Total RFC</div>
                <div class="fw-bold text-danger">Rp {{ number_format($totalRfc, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card text-center">
            <div class="card-body py-2">
                <div class="small text-muted">Net SISA</div>
                <div class="fw-bold {{ $netSurplus >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($netSurplus, 0) }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Daily detail --}}
@foreach($dayStats as $stat)
    @php $day = $stat['day']; @endphp

    <div class="card mb-4">
        {{-- Day header --}}
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <strong>{{ $day->day_date->format('l, d F Y') }}</strong>
                <span class="small text-muted">
                    PK: {{ number_format($day->pk_count) }} students &nbsp;|&nbsp; PB: {{ number_format($day->pb_count) }} students
                </span>
            </div>
            <div class="row g-2 mt-1 small">
                <div class="col-auto">
                    Budget: <span class="fw-semibold text-primary">Rp {{ number_format($stat['budget'], 0) }}</span>
                </div>
                <div class="col-auto text-muted">|</div>
                <div class="col-auto">
                    Realisasi: <span class="fw-semibold text-info">Rp {{ number_format($day->realisasi, 0) }}</span>
                </div>
                <div class="col-auto text-muted">|</div>
                <div class="col-auto">
                    RFC: <span class="fw-semibold text-danger">Rp {{ number_format($stat['rfc'], 0) }}</span>
                </div>
                <div class="col-auto text-muted">|</div>
                <div class="col-auto">
                    SISA: <span class="fw-semibold {{ $stat['surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($stat['surplus'], 0) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @forelse($day->menus as $dayMenu)
                @php
                    $effPk   = $dayMenu->effectivePkCount();
                    $effPb   = $dayMenu->effectivePbCount();
                    $menuRfc = $dayMenu->totalCost();
                @endphp

                {{-- Menu subheader --}}
                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
                    <div>
                        <span class="badge bg-secondary me-1">{{ strtoupper($dayMenu->category) }}</span>
                        <strong>{{ $dayMenu->menu->name }}</strong>
                        @if($dayMenu->is_replacement)
                            <span class="badge bg-warning text-dark ms-1">Allergy Replacement</span>
                            @if($dayMenu->replacesMenu)
                                <span class="text-muted small ms-1">replaces {{ $dayMenu->replacesMenu->menu->name ?? '–' }}</span>
                            @endif
                        @endif
                    </div>
                    <div class="small text-muted">
                        PK: {{ number_format($effPk) }}@if($dayMenu->is_replacement) (allergy)@endif
                        &nbsp;|&nbsp; PB: {{ number_format($effPb) }}
                    </div>
                </div>

                @if($dayMenu->items->isNotEmpty())
                    <table class="table table-sm table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ingredient</th>
                                <th class="text-center">Unit</th>
                                <th class="text-end">Kebutuhan PK (g)</th>
                                <th class="text-end">Kebutuhan PB (g)</th>
                                <th class="text-end">Price/Unit (Rp)</th>
                                <th class="text-end">RFC (Rp)</th>
                                <th class="text-center">Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dayMenu->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td class="text-center">{{ $item->unit->name }}</td>
                                    <td class="text-end">{{ number_format($item->pk_gramasi, 1) }}</td>
                                    <td class="text-end">{{ number_format($item->pb_gramasi, 1) }}</td>
                                    <td class="text-end">
                                        @if($item->purchase_price == 0)
                                            <span class="text-danger">–</span>
                                        @else
                                            {{ number_format($item->purchase_price, 2) }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->costFor($effPk, $effPb), 0) }}</td>
                                    <td class="text-center small text-muted">{{ $item->supplier?->name ?? '–' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Menu RFC</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($menuRfc, 0) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="px-3 py-2 text-muted small fst-italic">No ingredients added.</div>
                @endif
            @empty
                <div class="px-3 py-3 text-muted small">No menus added for this day.</div>
            @endforelse

            {{-- Day RFC footer --}}
            @if($day->menus->isNotEmpty())
                <div class="d-flex justify-content-end align-items-center px-3 py-2 border-top bg-light">
                    <span class="me-4 fw-semibold small">Day RFC Total</span>
                    <span class="fw-bold text-danger" style="min-width:130px;text-align:right">
                        Rp {{ number_format($stat['rfc'], 0) }}
                    </span>
                </div>
            @endif
        </div>
    </div>
@endforeach

<div class="no-print mt-2">
    <a href="{{ route('rab-periods.show', $rabPeriod) }}" class="btn btn-outline-secondary">&larr; Back to Period</a>
</div>
@endsection
