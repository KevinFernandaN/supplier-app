@extends('layouts.app')

@section('title', 'Create Orders — PR #' . $purchaseRequest->id)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Create Orders — PR #{{ $purchaseRequest->id }}</h1>
        <small class="text-muted">
            {{ $purchaseRequest->kitchen->name }} &mdash; {{ $purchaseRequest->menu->name }} &mdash;
            {{ number_format($purchaseRequest->total_portion, 0) }} portions
            @if ($isAssisted)
                &mdash; <span class="badge bg-info text-dark">Assisted: vendors pre-selected</span>
            @else
                &mdash; <span class="badge bg-secondary">Open: select vendors manually</span>
            @endif
        </small>
    </div>
    <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="btn btn-outline-secondary">Back</a>
</div>

<form action="{{ route('purchase-requests.orders.store', $purchaseRequest) }}" method="POST">
    @csrf

    @foreach ($purchaseRequest->items as $item)
        @php
            $vendors   = $vendorsByProduct[$item->product_id] ?? collect();
            $recommend = $recommendedByProduct[$item->product_id] ?? null;
        @endphp

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $item->product->name }}</span>
                <span class="text-muted small">
                    Need: <strong>{{ number_format($item->final_qty, 3) }} {{ $item->unit->symbol ?? $item->unit->name }}</strong>
                    @if ($item->buffer_pct > 0)
                        <span class="text-muted">(incl. {{ $item->buffer_pct }}% buffer)</span>
                    @endif
                </span>
            </div>
            <div class="card-body">

                @if ($vendors->isEmpty())
                    <div class="alert alert-warning mb-0">
                        No active suppliers found for this ingredient in this region.
                        <a href="{{ route('suppliers.index') }}">Add a supplier product</a> first.
                    </div>
                @else

                    {{-- Available vendors info --}}
                    <div class="mb-3">
                        <small class="text-muted">Available vendors:</small>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach ($vendors as $v)
                                <span class="badge bg-light text-dark border">
                                    {{ $v->supplier_name }}
                                    @if ($v->latest_price)
                                        — Rp {{ number_format($v->latest_price, 0, ',', '.') }}
                                    @else
                                        — <em>no price</em>
                                    @endif
                                    @if ($v->kpi_score)
                                        — KPI {{ number_format($v->kpi_score, 2) }}
                                    @endif
                                    @if ($v->availability_status === 'limited')
                                        <span class="text-warning">(limited)</span>
                                    @elseif ($v->availability_status === 'preorder')
                                        <span class="text-secondary">(preorder)</span>
                                    @endif
                                    @if ($recommend && $recommend->supplier_id === $v->supplier_id)
                                        <span class="text-success">★ recommended</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Split rows --}}
                    <div id="splits-{{ $item->id }}">

                        {{-- First row: pre-filled for assisted, empty for open --}}
                        <div class="row g-2 align-items-center split-row mb-2">
                            <div class="col-md-6">
                                <select name="items[{{ $item->id }}][splits][0][supplier_id]"
                                        class="form-select form-select-sm">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->supplier_id }}"
                                            @if ($recommend && $recommend->supplier_id === $v->supplier_id) selected @endif>
                                            {{ $v->supplier_name }}
                                            @if ($v->latest_price) (Rp {{ number_format($v->latest_price, 0, ',', '.') }}) @endif
                                            @if ($v->kpi_score) — KPI {{ number_format($v->kpi_score, 2) }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.001" min="0"
                                           name="items[{{ $item->id }}][splits][0][qty]"
                                           class="form-control"
                                           placeholder="Qty"
                                           value="{{ $recommend ? number_format($item->final_qty, 3, '.', '') : '' }}">
                                    <span class="input-group-text">{{ $item->unit->symbol ?? $item->unit->name }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addSplit({{ $item->id }}, {{ $vendors->toJson() }})">
                                    + Split to another vendor
                                </button>
                            </div>
                        </div>

                    </div>{{-- #splits-{id} --}}
                @endif

            </div>
        </div>
    @endforeach

    <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-success btn-lg">Create Orders</button>
        <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
</form>

<script>
function addSplit(itemId, vendors) {
    const container = document.getElementById('splits-' + itemId);
    const rows = container.querySelectorAll('.split-row');
    const idx = rows.length;

    const unitSymbol = rows[0].querySelector('.input-group-text').textContent;

    let options = '<option value="">-- Select Vendor --</option>';
    vendors.forEach(v => {
        let label = v.supplier_name;
        if (v.latest_price) label += ' (Rp ' + Number(v.latest_price).toLocaleString('id-ID') + ')';
        if (v.kpi_score) label += ' — KPI ' + parseFloat(v.kpi_score).toFixed(2);
        options += `<option value="${v.supplier_id}">${label}</option>`;
    });

    const row = document.createElement('div');
    row.className = 'row g-2 align-items-center split-row mb-2';
    row.innerHTML = `
        <div class="col-md-6">
            <select name="items[${itemId}][splits][${idx}][supplier_id]" class="form-select form-select-sm">
                ${options}
            </select>
        </div>
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <input type="number" step="0.001" min="0"
                       name="items[${itemId}][splits][${idx}][qty]"
                       class="form-control" placeholder="Qty">
                <span class="input-group-text">${unitSymbol}</span>
            </div>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.split-row').remove()">
                Remove
            </button>
        </div>
    `;
    container.appendChild(row);
}
</script>

@endsection
