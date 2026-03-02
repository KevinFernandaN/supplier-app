@extends('layouts.app')

@section('title', 'Monthly Margin')

@section('content')
<h1 class="h3 mb-3">Monthly Margin Report</h1>

<form class="row g-2 mb-3" method="GET" action="{{ route('reports.margin.monthly') }}">
    <div class="col-md-3">
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control" value="{{ $from }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control" value="{{ $to }}">
    </div>
    <div class="col-md-6 d-flex align-items-end gap-2">
        <button class="btn btn-primary">Filter</button>
        <a class="btn btn-outline-secondary"
           href="{{ route('reports.margin.monthly.export', ['from' => $from, 'to' => $to]) }}">
            Export CSV
        </a>
    </div>
</form>

<table class="table table-bordered align-middle">
    <thead>
    <tr>
        <th>Month</th>
        <th>Revenue</th>
        <th>COGS</th>
        <th>Margin</th>
        <th>Margin %</th>
    </tr>
    </thead>
    <tbody>
    @forelse($monthly as $r)
        <tr>
            <td>{{ $r->month }}</td>
            <td>Rp {{ number_format($r->revenue, 0) }}</td>
            <td>Rp {{ number_format($r->cogs, 0) }}</td>
            <td>Rp {{ number_format($r->margin, 0) }}</td>
            <td>{{ number_format($r->margin_pct, 2) }}%</td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center py-4">No data.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
