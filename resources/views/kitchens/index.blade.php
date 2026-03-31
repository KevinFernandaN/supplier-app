@extends('layouts.app')

@section('title', 'Kitchens')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Kitchens</h1>
    <a href="{{ route('kitchens.create') }}" class="btn btn-primary">+ New Kitchen</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Region</th>
                    <th>Type</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 160px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($kitchens as $kitchen)
                    <tr>
                        <td class="fw-semibold">{{ $kitchen->name }}</td>
                        <td>{{ $kitchen->region->name ?? '-' }}</td>
                        <td>
                            @if ($kitchen->type === 'assisted')
                                <span class="badge bg-info text-dark">Assisted</span>
                            @else
                                <span class="badge bg-secondary">Open</span>
                            @endif
                        </td>
                        <td>{{ $kitchen->address ?? '-' }}</td>
                        <td>
                            @if ($kitchen->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('kitchens.edit', $kitchen) }}">Edit</a>

                            <form action="{{ route('kitchens.destroy', $kitchen) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this kitchen?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">No kitchens yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $kitchens->links() }}
</div>
@endsection
