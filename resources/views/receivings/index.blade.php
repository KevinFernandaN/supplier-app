@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Receiving</h1>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>PO #</th>
        <th>Supplier</th>
        <th>Kitchen</th>
        <th>Order Date</th>
        <th>Status</th>
        <th width="120">Action</th>
    </tr>
    </thead>
    <tbody>
    @forelse($receivings as $receiving)
        <tr>
            <td>{{ $receiving->purchaseOrder->id }}</td>
            <td>{{ $receiving->purchaseOrder->supplier->name }}</td>
            <td>{{ $receiving->kitchen?->name ?? '—' }}</td>
            <td>{{ $receiving->purchaseOrder->order_date->format('d M Y') }}</td>
            <td>
                @if($receiving->received_at)
                    <span class="badge bg-success">Received</span>
                    <br><small class="text-muted">{{ $receiving->received_at->format('d M Y H:i') }}</small>
                @else
                    <span class="badge bg-warning text-dark">Pending</span>
                @endif
            </td>
            <td>
                <a href="{{ route('receivings.show', $receiving) }}" class="btn btn-sm btn-info">View</a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center text-muted">No receivings yet.</td>
        </tr>
    @endforelse
    </tbody>
</table>

{{ $receivings->links() }}
@endsection