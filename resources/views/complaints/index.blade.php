@extends('layouts.app')

@section('title', 'Complaints')

@section('content')
<h1 class="h3 mb-3">Complaints</h1>

<table class="table table-bordered align-middle">
    <thead>
        <tr>
            <th>Date</th>
            <th>Supplier</th>
            <th>Product</th>
            <th>Complaint Type</th>
            <th>Category (type)</th>
            <th>Severity</th>
            <th>Qty</th>
            <th>Status</th>
            <th>Resolved At</th>
            <th width="80">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($complaints as $c)
        <tr>
            <td>{{ $c->complaint_date }}</td>
            <td>{{ $c->supplier->name }}</td>
            <td>{{ $c->product->name }}</td>
            <td>{{ $c->complaint_type }}</td>
            <td>{{ $c->type }}</td>
            <td>{{ $c->severity }}</td>
            <td>{{ $c->qty }}</td>
            <td>{{ $c->status }}</td>
            <td>{{ $c->resolved_at ? $c->resolved_at->format('Y-m-d H:i') : '-' }}</td>
            <td>
                @if($c->status === 'open')
                    <form method="POST" action="{{ route('complaints.resolve', $c) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-sm btn-success" onclick="return confirm('Mark as resolved?')">
                            Resolve
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('complaints.reopen', $c) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-sm btn-warning" onclick="return confirm('Re-open this complaint?')">
                            Re-open
                        </button>
                    </form>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No complaints found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $complaints->links() }}

@endsection
