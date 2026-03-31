@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">
    {{ $supplier->name }} - Products
</h1>

<a href="{{ route('suppliers.supplier-products.create', $supplier) }}"
   class="btn btn-primary mb-3">+ Add Product</a>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Product</th>
        <th>Lead Time</th>
        <th>Min Order</th>
        <th>Latest Price</th>
        <th>Active</th>
        <th>Availability</th>
        <th width="200">Action</th>
    </tr>
    </thead>
    <tbody>
    @foreach($supplierProducts as $sp)
        <tr>
            <td>{{ $sp->product->name }}</td>
            <td>{{ $sp->lead_time_days }} days</td>
            <td>{{ $sp->min_order_qty }}</td>
            <td>
                @if($sp->latestPrice)
                    Rp {{ number_format($sp->latestPrice->price, 0, ',', '.') }}
                    <br><small class="text-muted">{{ $sp->latestPrice->effective_from->format('d M Y') }}</small>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>{{ $sp->is_active ? 'Active' : 'Inactive' }}</td>
            <td>
                @php $avail = $sp->availability_status ?? 'ready'; @endphp
                @if ($avail === 'ready')
                    <span class="badge bg-success">Ready</span>
                @elseif ($avail === 'limited')
                    <span class="badge bg-warning text-dark">Limited</span>
                @else
                    <span class="badge bg-secondary">Pre-order</span>
                @endif
            </td>
            <td>
                <a class="btn btn-sm btn-info"
                   href="{{ route('suppliers.supplier-products.show', [$supplier, $sp]) }}">
                    View
                </a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">Back</a>
@endsection
