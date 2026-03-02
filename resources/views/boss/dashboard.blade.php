@extends('layouts.app')

@section('title', 'Boss Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Boss Dashboard</h1>

    <form class="d-flex gap-2" method="GET" action="{{ route('boss.dashboard') }}">
        <input type="date" name="from" class="form-control" value="{{ $from }}">
        <input type="date" name="to" class="form-control" value="{{ $to }}">
        <button class="btn btn-primary">Filter</button>
    </form>

    <a class="btn btn-outline-secondary"
        href="{{ route('boss.dashboard.export-margin', ['from' => $from, 'to' => $to]) }}">
            Export Margin CSV
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Revenue</div>
                <div class="fs-4 fw-bold">Rp {{ number_format($totalRevenue, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">COGS</div>
                <div class="fs-4 fw-bold">Rp {{ number_format($totalCogs, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Margin</div>
                <div class="fs-4 fw-bold">Rp {{ number_format($totalMargin, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted">Margin %</div>
                <div class="fs-4 fw-bold">{{ number_format($marginPct, 2) }}%</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Margin Trend (Daily)</span>
            </div>
            <div class="card-body">
                <canvas id="marginChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <div class="text-muted">Purchases (PO Total)</div>
                <div class="fs-4 fw-bold">Rp {{ number_format($purchaseTotal, 0) }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Top Menus by Margin</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="text-end">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topMenus as $m)
                        <tr>
                            <td>{{ $m->menu_name }}</td>
                            <td class="text-end">Rp {{ number_format($m->margin, 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center py-3">No data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header fw-semibold">Top Suppliers KPI ({{ substr($to,0,7) }})</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th class="text-end">KPI</th>
                            <th class="text-end">Reviews</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topSuppliers as $s)
                        <tr>
                            <td>{{ $s->name }}</td>
                            <td class="text-end">{{ number_format($s->kpi_score, 2) }}</td>
                            <td class="text-end">{{ $s->review_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-3">No KPI data for this month.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header fw-semibold">Top Complained Products</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Count</th>
                            <th class="text-end">Avg Sev</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topComplainedProducts as $r)
                        <tr>
                            <td>{{ $r->product_name }}</td>
                            <td class="text-end">{{ $r->complaint_count }}</td>
                            <td class="text-end">{{ number_format((float)$r->avg_severity, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-3">No complaints in range.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('boss.complaints.index', ['from' => $from, 'to' => $to]) }}" class="btn btn-sm btn-outline-secondary">
                    View details
                </a>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header fw-semibold">Top Returned Products</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topReturnedProducts as $r)
                        <tr>
                            <td>{{ $r->product_name }}</td>
                            <td class="text-end">{{ number_format((float)$r->total_qty, 2) }}</td>
                            <td class="text-end">{{ $r->order_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-3">No returns in range.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('boss.returns.index', ['from' => $from, 'to' => $to]) }}" class="btn btn-sm btn-outline-secondary">
                    View details
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels = @json($chartLabels);
    const marginData = @json($chartMargin);

    const ctx = document.getElementById('marginChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Margin',
                data: marginData,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection
