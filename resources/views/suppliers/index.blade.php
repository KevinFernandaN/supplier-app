@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Suppliers</h1>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ New Supplier</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>WhatsApp</th>
                    <th>Active</th>
                    <th class="text-end" style="width: 220px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="fw-semibold">{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone_wa ?? '-' }}</td>
                        <td>
                            @if($supplier->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('suppliers.show', $supplier) }}">View</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('suppliers.edit', $supplier) }}">Edit</a>

                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this supplier?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4">No suppliers yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $suppliers->links() }}
</div>
@endsection
