@extends('layouts.app')

@section('title', 'Boss - Return Analytics')

@section('content')
<h1 class="h3 mb-3">Return Analytics</h1>

<form class="row g-2 mb-3" method="GET" action="{{ route('boss.returns.index') }}">
    <div class="col-md-2">
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control" value="{{ $from }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control" value="{{ $to }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select">
            <option value="">All</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected((string)$supplierId === (string)$s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Product</label>
        <select name="product_id" class="form-select">
            <option value="">All</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" @selected((string)$productId === (string)$p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100">Apply</button>
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <a class="btn btn-outline-secondary w-100"
           href="{{ route('boss.returns.export', request()->query()) }}">
            Export
        </a>
    </div>
</form>

{{-- Summary Cards --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted">Total Return Orders</div>
            <div class="fs-4 fw-bold">{{ $totalOrders }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted">Total Qty Returned</div>
            <div class="fs-4 fw-bold">{{ number_format($totalQty, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted">Suppliers Involved</div>
            <div class="fs-4 fw-bold">{{ $totalSuppliers }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted">Distinct Products</div>
            <div class="fs-4 fw-bold">{{ $totalProducts }}</div>
        </div></div>
    </div>
</div>

{{-- Monthly Trend Chart --}}
<div class="card mb-3">
    <div class="card-header fw-semibold">Monthly Trend</div>
    <div class="card-body">
        <canvas id="returnTrendChart" height="80"></canvas>
    </div>
</div>

{{-- Top Products + Top Suppliers --}}
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Top Returned Products</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Total Qty</th>
                            <th class="text-end">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topProducts as $r)
                        <tr>
                            <td>{{ $r->product_name }}</td>
                            <td class="text-end">{{ number_format((float)$r->total_qty, 2) }}</td>
                            <td class="text-end">{{ $r->order_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4">No data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Top Suppliers by Returns</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th class="text-end">Total Qty</th>
                            <th class="text-end">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topSuppliers as $r)
                        <tr>
                            <td>{{ $r->supplier_name }}</td>
                            <td class="text-end">{{ number_format((float)$r->total_qty, 2) }}</td>
                            <td class="text-end">{{ $r->order_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4">No data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Latest Returns --}}
<div class="card">
    <div class="card-header fw-semibold">Latest Return Orders</div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th class="text-end">Items</th>
                    <th class="text-end">Total Qty</th>
                    <th>Notes</th>
                    <th width="80">Detail</th>
                </tr>
            </thead>
            <tbody>
            @forelse($latestReturns as $r)
                <tr>
                    <td>{{ $r->return_date }}</td>
                    <td>{{ $r->supplier_name }}</td>
                    <td class="text-end">{{ $r->item_count }}</td>
                    <td class="text-end">{{ number_format((float)$r->total_qty, 2) }}</td>
                    <td class="text-muted small">{{ $r->notes }}</td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('returns.show', $r->id) }}">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">No returns in this range.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels     = @json($chartLabels);
    const orderCount = @json($chartOrderCount);
    const totalQty   = @json($chartQty);

    new Chart(document.getElementById('returnTrendChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Return Orders',
                    data: orderCount,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    yAxisID: 'y',
                },
                {
                    label: 'Total Qty',
                    data: totalQty,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    yAxisID: 'y2',
                },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: {
                y:  { beginAtZero: true, position: 'left',  title: { display: true, text: 'Orders' } },
                y2: { beginAtZero: true, position: 'right', title: { display: true, text: 'Qty' }, grid: { drawOnChartArea: false } },
            }
        }
    });
</script>
@endsection
