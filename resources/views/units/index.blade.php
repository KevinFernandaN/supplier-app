@extends('layouts.app')

@section('title', 'Units')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Units</h1>
    <a href="{{ route('units.create') }}" class="btn btn-primary">+ Add Unit</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Symbol</th>
                    <th width="140">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr>
                        <td>{{ $unit->name }}</td>
                        <td><code>{{ $unit->symbol }}</code></td>
                        <td>
                            <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('units.destroy', $unit) }}" class="d-inline"
                                  onsubmit="return confirm('Delete unit {{ $unit->name }}? This may affect products and conversions.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Del</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">
                            No units yet. <a href="{{ route('units.create') }}">Add one now</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
