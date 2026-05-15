@extends('layouts.app')

@section('title', 'Regions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Regions</h1>
    <a href="{{ route('regions.create') }}" class="btn btn-success">+ New Region</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Timezone</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" width="160">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regions as $region)
                    <tr>
                        <td><span class="fw-mono badge bg-secondary">{{ $region->code }}</span></td>
                        <td>{{ $region->name }}</td>
                        <td class="text-muted small">{{ $region->timezone }}</td>
                        <td class="text-center">
                            @if($region->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('regions.edit', $region) }}"
                               class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('regions.destroy', $region) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete region {{ $region->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No regions yet. <a href="{{ route('regions.create') }}">Add the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
