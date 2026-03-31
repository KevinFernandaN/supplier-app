@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Sales Orders</h1>
    <a href="{{ route('sales-orders.create') }}" class="btn btn-primary">+ New Sales Order</a>
</div>

<table class="table table-bordered align-middle">
    <thead>
    <tr>
        <th>Date</th>
        <th>Channel</th>
        <th width="180">Action</th>
    </tr>
    </thead>
    <tbody>
    @forelse($orders as $so)
        <tr>
            <td>{{ $so->sale_date->format('Y-m-d') }}</td>
            <td>{{ $so->channel ?? '-' }}</td>
            <td>
                <form action="{{ route('sales-orders.destroy', $so) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete this sales order?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
                <a href="{{ route('sales-orders.show', $so) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('rabs.index', ['date' => $so->sale_date->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-secondary">RAB</a>
            </td>
        </tr>
    @empty
        <tr><td colspan="3" class="text-center py-4">No sales orders yet.</td></tr>
    @endforelse
    </tbody>
</table>

{{ $orders->links() }}
@endsection
