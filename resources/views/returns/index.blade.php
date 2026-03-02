@extends('layouts.app')

@section('title','Returns')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Returns</h1>
    <a class="btn btn-primary" href="{{ route('returns.create') }}">+ New Return</a>
</div>

<table class="table table-bordered align-middle">
    <thead>
        <tr>
            <th>Date</th>
            <th>Supplier</th>
            <th>Status</th>
            <th width="180">Action</th>
        </tr>
    </thead>
    <tbody>
    @forelse($returns as $r)
        <tr>
            <td>{{ $r->return_date->format('Y-m-d') }}</td>
            <td>{{ $r->supplier->name }}</td>
            <td>{{ $r->status }}</td>
            <td>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('returns.show',$r) }}">View</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('returns.edit',$r) }}">Edit</a>
                <form method="POST" action="{{ route('returns.destroy',$r) }}" class="d-inline"
                      onsubmit="return confirm('Delete this return?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-center py-4">No returns yet.</td></tr>
    @endforelse
    </tbody>
</table>

{{ $returns->links() }}
@endsection
