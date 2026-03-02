@extends('layouts.app')

@section('title', 'Boss - Complaint Analytics')

@section('content')
<h1 class="h3 mb-3">Complaint Analytics</h1>

<form class="row g-2 mb-3" method="GET" action="{{ route('boss.complaints.index') }}">
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

    <div class="col-md-1">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="">All</option>
            <option value="open" @selected($status==='open')>Open</option>
            <option value="resolved" @selected($status==='resolved')>Resolved</option>
        </select>
    </div>

    <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100">Apply</button>
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <a class="btn btn-outline-secondary w-100"
        href="{{ route('boss.complaints.export', request()->query()) }}">
            Export
        </a>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted">Total Complaints</div>
            <div class="fs-4 fw-bold">{{ $totalComplaints }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted">Avg Severity</div>
            <div class="fs-4 fw-bold">{{ number_format($avgSeverity, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted">Open</div>
            <div class="fs-4 fw-bold">{{ $openCount }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted">Resolved</div>
            <div class="fs-4 fw-bold">{{ $resolvedCount }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold">Daily Trend</div>
            <div class="card-body">
                <canvas id="complaintTrendChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold">By Complaint Type</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th class="text-end">Count</th>
                            <th class="text-end">Avg Sev</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($byComplaintType as $r)
                        <tr>
                            <td>{{ $r->complaint_type }}</td>
                            <td class="text-end">{{ $r->cnt }}</td>
                            <td class="text-end">{{ number_format((float)$r->avg_sev, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-3">No data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Top Suppliers</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th class="text-end">Count</th>
                            <th class="text-end">Avg Sev</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topSuppliers as $r)
                        <tr>
                            <td>{{ $r->supplier_name }}</td>
                            <td class="text-end">{{ $r->complaint_count }}</td>
                            <td class="text-end">{{ number_format((float)$r->avg_severity, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-3">No data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header fw-semibold">Top Products</div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-end">Complaint Count</th>
                    <th class="text-end">Avg Severity</th>
                </tr>
            </thead>
            <tbody>
            @forelse($topProducts as $r)
                <tr>
                    <td>{{ $r->product_name }}</td>
                    <td class="text-end">{{ $r->complaint_count }}</td>
                    <td class="text-end">{{ number_format((float)$r->avg_severity, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-4">No data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header fw-semibold">Latest Complaints (Action)</div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Product</th>
                    <th>Complaint</th>
                    <th class="text-end">Sev</th>
                    <th>Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($latestComplaints as $c)
                <tr>
                    <td>{{ $c->complaint_date }}</td>
                    <td>{{ $c->supplier_name }}</td>
                    <td>{{ $c->product_name }}</td>
                    <td>
                        <div><strong>{{ $c->complaint_type }}</strong> ({{ $c->type }})</div>
                        @if($c->description)
                            <div class="text-muted small">{{ $c->description }}</div>
                        @endif
                    </td>
                    <td class="text-end">{{ $c->severity }}</td>
                    <td>{{ $c->status }}</td>
                    <td>
                        @if($c->status === 'open')
                            <form method="POST" action="{{ route('complaints.resolve', $c->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-success"
                                        onclick="return confirm('Mark as resolved?')">
                                    Resolve
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('complaints.reopen', $c->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-warning"
                                        onclick="return confirm('Re-open this complaint?')">
                                    Re-open
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4">No complaints in this range.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels = @json($chartLabels);
    const counts = @json($chartCounts);
    const avgSev = @json($chartAvgSev);

    const ctx = document.getElementById('complaintTrendChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Complaints (count)', data: counts, tension: 0.3 },
                { label: 'Avg severity', data: avgSev, tension: 0.3 },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endsection
