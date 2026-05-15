@extends('layouts.app')

@section('title', $day->day_date->format('D, d M Y') . ' – ' . $rabPeriod->name)

@section('content')

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.index') }}">RAB Periods</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.show', $rabPeriod) }}">{{ $rabPeriod->name }}</a></li>
        <li class="breadcrumb-item active">{{ $day->day_date->format('D, d M Y') }}</li>
    </ol>
</nav>

{{-- Day header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h3 mb-1">{{ $day->day_date->format('l, d F Y') }}</h1>
        <div class="text-muted small">
            PK: <strong>{{ number_format($day->pk_count) }}</strong> students &nbsp;|&nbsp;
            PB: <strong>{{ number_format($day->pb_count) }}</strong> students
        </div>
    </div>
    <a href="{{ route('rab-periods.days.menus.create', [$rabPeriod, $day]) }}" class="btn btn-success">+ Add Menu</a>
</div>

{{-- Day summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card text-center">
            <div class="card-body py-2">
                <div class="small text-muted">Budget</div>
                <div class="fw-bold text-primary">Rp {{ number_format($budget, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card text-center">
            <div class="card-body py-2">
                <div class="small text-muted">Realisasi</div>
                <div class="fw-bold text-info">Rp {{ number_format($day->realisasi, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card text-center">
            <div class="card-body py-2">
                <div class="small text-muted">RFC</div>
                <div class="fw-bold text-danger">Rp {{ number_format($rfc, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card text-center">
            <div class="card-body py-2">
                <div class="small text-muted">SISA</div>
                <div class="fw-bold {{ $surplus >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($surplus, 2) }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Menus list --}}
@forelse($day->menus as $dayMenu)
    @php
        $menuRfc = $dayMenu->totalCost();
        $effPk   = $dayMenu->effectivePkCount();
        $effPb   = $dayMenu->effectivePbCount();
    @endphp
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <div>
                <strong>{{ $dayMenu->menu->name }}</strong>
                <span class="badge bg-secondary ms-2">{{ strtoupper($dayMenu->category) }}</span>
                @if($dayMenu->is_replacement)
                    <span class="badge bg-warning text-dark ms-1">Allergy Replacement</span>
                    @if($dayMenu->replacesMenu)
                        <span class="text-muted small ms-1">replaces {{ $dayMenu->replacesMenu->menu->name ?? '–' }}</span>
                    @endif
                @endif
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="text-muted small">
                    PK: {{ number_format($effPk) }}
                    @if($dayMenu->is_replacement)
                        (allergy)
                    @endif
                    &nbsp;|&nbsp;
                    PB: {{ number_format($effPb) }}
                </span>
                <a href="{{ route('rab-periods.days.menus.edit', [$rabPeriod, $day, $dayMenu]) }}"
                   class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST"
                      action="{{ route('rab-periods.days.menus.destroy', [$rabPeriod, $day, $dayMenu]) }}"
                      onsubmit="return confirm('Remove this menu and all its ingredients?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Del</button>
                </form>
            </div>
        </div>

        {{-- Ingredients table --}}
        <div class="card-body p-0">
            @if($dayMenu->items->isNotEmpty())
                {{-- Delete forms declared outside the table to avoid nested-form issues --}}
                @foreach($dayMenu->items as $item)
                    <form id="del-item-{{ $item->id }}" method="POST"
                          action="{{ route('rab-periods.days.menus.items.destroy', [$rabPeriod, $day, $dayMenu, $item]) }}"
                          onsubmit="return confirm('Remove this ingredient?')">
                        @csrf @method('DELETE')
                    </form>
                @endforeach

                <table class="table table-sm table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ingredient</th>
                            <th class="text-center">Unit</th>
                            <th class="text-end">Kebutuhan PK (g)</th>
                            <th class="text-end">Kebutuhan PB (g)</th>
                            <th class="text-end">Price / unit (Rp)</th>
                            <th class="text-end">RFC (Rp)</th>
                            <th class="text-center">Supplier</th>
                            <th class="text-center" width="140">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dayMenu->items as $item)
                            @php
                                $itemCost = $item->costFor($effPk, $effPb);
                            @endphp
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td class="text-center">{{ $item->unit->name }}</td>
                                <td class="text-end">{{ number_format($item->pk_gramasi, 3) }}</td>
                                <td class="text-end">{{ number_format($item->pb_gramasi, 3) }}</td>
                                <td class="text-end">
                                    @if($item->purchase_price == 0)
                                        <span class="text-danger">–</span>
                                    @else
                                        {{ number_format($item->purchase_price, 2) }}
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($itemCost, 2) }}</td>
                                <td class="text-center small text-muted">
                                    {{ $item->supplier?->name ?? '–' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('rab-periods.days.menus.items.edit', [$rabPeriod, $day, $dayMenu, $item]) }}"
                                       class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <button type="submit" form="del-item-{{ $item->id }}"
                                            class="btn btn-sm btn-outline-danger">Del</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end fw-semibold">Menu RFC</td>
                            <td class="text-end fw-bold">{{ number_format($menuRfc, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="p-3 text-muted small">
                    No ingredients yet.
                    <a href="{{ route('rab-periods.days.menus.items.create', [$rabPeriod, $day, $dayMenu]) }}">Add ingredients</a>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info">
        No menus added for this day yet.
        <a href="{{ route('rab-periods.days.menus.create', [$rabPeriod, $day]) }}">Add the first menu</a>.
    </div>
@endforelse

<div class="mt-3 d-flex gap-2">
    <a href="{{ route('rab-periods.days.menus.create', [$rabPeriod, $day]) }}" class="btn btn-success">+ Add Menu</a>
    <a href="{{ route('rab-periods.show', $rabPeriod) }}" class="btn btn-outline-secondary">&larr; Back to Period</a>
</div>
@endsection
