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
        <th>Status</th>
        <th width="150">Action</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $po)
        <tr>
            <td>{{ $po->order_date }}</td>
            <td>{{ $po->supplier->name }}</td>
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
