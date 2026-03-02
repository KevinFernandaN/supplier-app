@extends('layouts.app')

@section('title', 'RAB – ' . $rab->menu->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">RAB – {{ $rab->menu->name }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('rabs.items.create', $rab) }}" class="btn btn-outline-secondary btn-sm">+ Add Ingredient</a>
        <a href="{{ route('rabs.edit', $rab) }}" class="btn btn-outline-primary btn-sm">Edit Header</a>
        <a href="{{ route('rabs.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Summary Cards --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">RAB Date</div>
            <div class="fs-5 fw-bold">{{ $rab->rab_date }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Selling Price / Portion</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($rab->selling_price, 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">COGS / Portion</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($totalCogs, 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Margin / Portion</div>
            <div class="fs-5 fw-bold {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format($margin, 0) }}
                <span class="fs-6 fw-normal">({{ number_format($marginPct, 1) }}%)</span>
            </div>
        </div></div>
    </div>
</div>

@if($rab->notes)
    <div class="alert alert-secondary small mb-3">{{ $rab->notes }}</div>
@endif

{{-- Ingredients Table --}}
<div class="card">
    <div class="card-header fw-semibold">Ingredients (per 1 portion)</div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-end">Qty</th>
                    <th>Unit</th>
                    <th class="text-end">Price / Unit (Rp)</th>
                    <th class="text-end">Line Cost (Rp)</th>
                    <th width="100">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rab->items as $item)
                    @php $lineCost = (float)$item->qty * (float)$item->purchase_price; @endphp
                    <tr @if($item->purchase_price == 0) class="table-warning" @endif>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($item->qty, 3), '0'), '.') }}</td>
                        <td>{{ $item->unit->symbol }}</td>
                        <td class="text-end">
                            @if($item->purchase_price == 0)
                                <span class="text-warning fw-bold">0 – set price!</span>
                            @else
                                {{ number_format($item->purchase_price, 2) }}
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($lineCost, 2) }}</td>
                        <td>
                            <a href="{{ route('rabs.items.edit', [$rab, $item]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('rabs.items.destroy', [$rab, $item]) }}" class="d-inline"
                                  onsubmit="return confirm('Remove this ingredient?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">✕</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-3 text-muted">No ingredients. <a href="{{ route('rabs.items.create', $rab) }}">Add one</a>.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-semibold">
                <tr>
                    <td colspan="4" class="text-end">Total COGS / Portion</td>
                    <td class="text-end">Rp {{ number_format($totalCogs, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if($rab->items->where('purchase_price', 0)->count() > 0)
    <div class="alert alert-warning mt-3 small">
        Some ingredients have a price of 0. This means no purchase order was found for them before the RAB date.
        Please edit those items and enter the expected purchase price manually.
    </div>
@endif
@endsection
