@extends('layouts.app')

@section('title','Return Detail')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Return</h1>
    <div>
        <a class="btn btn-outline-secondary" href="{{ route('returns.edit',$returnOrder) }}">Edit</a>
        <a class="btn btn-primary" href="{{ route('returns.items.create', $returnOrder) }}">+ Add Item</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div><strong>Date:</strong> {{ $returnOrder->return_date->format('Y-m-d') }}</div>
        <div><strong>Supplier:</strong> {{ $returnOrder->supplier->name }}</div>
        <div><strong>Status:</strong> {{ $returnOrder->status }}</div>
        @if($returnOrder->notes)
            <div class="mt-2"><strong>Notes:</strong> {{ $returnOrder->notes }}</div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Items</div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-end">Qty</th>
                    <th>Unit</th>
                    <th>Reason</th>
                    <th width="160">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($returnOrder->items as $it)
                <tr>
                    <td>{{ $it->product->name }}</td>
                    <td class="text-end">{{ $it->qty }}</td>
                    <td>{{ $it->unit->symbol }}</td>
                    <td>{{ $it->reason }}</td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('returns.items.edit', [$returnOrder, $it]) }}">Edit</a>
                        <form method="POST" action="{{ route('returns.items.destroy', [$returnOrder, $it]) }}" class="d-inline"
                              onsubmit="return confirm('Delete this item?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-4">No items yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<a class="btn btn-secondary mt-3" href="{{ route('returns.index') }}">Back</a>
@endsection
