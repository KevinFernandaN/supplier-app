@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Purchase Orders</h1>

<a href="{{ route('purchase-orders.create') }}" class="btn btn-primary mb-3">
    + New Purchase Order
</a>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Date</th>
        <th>Supplier</th>
        <th>Phone/WA</th>
        <th>Lead Days</th>
        <th>Status</th>
        <th width="150">Action</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $po)
        <tr>
            <td>{{ $po->order_date }}</td>
            <td>{{ $po->supplier->name }}</td>
            <td>
                @if($po->supplier->phone_wa)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $po->supplier->phone_wa) }}" target="_blank">
                        {{ $po->supplier->phone_wa }}
                    </a>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                @if($po->min_lead_days !== null)
                    @if($po->min_lead_days == $po->max_lead_days)
                        {{ $po->min_lead_days }} days
                    @else
                        {{ $po->min_lead_days }} – {{ $po->max_lead_days }} days
                    @endif
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>{{ $po->status }}</td>
            <td>
                <a href="{{ route('purchase-orders.show', $po) }}"
                   class="btn btn-sm btn-info">View</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $orders->links() }}
@endsection
