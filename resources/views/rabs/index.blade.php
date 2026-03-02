@extends('layouts.app')

@section('title', 'RAB (Budget Plans)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">RAB – Budget Plans</h1>
    <a href="{{ route('rabs.create') }}" class="btn btn-primary">+ New RAB</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Menu</th>
                    <th class="text-end">Selling Price / Portion</th>
                    <th>Notes</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rabs as $rab)
                    <tr>
                        <td>{{ $rab->rab_date }}</td>
                        <td>{{ $rab->menu->name }}</td>
                        <td class="text-end">Rp {{ number_format($rab->selling_price, 0) }}</td>
                        <td class="text-muted small">{{ $rab->notes }}</td>
                        <td>
                            <a href="{{ route('rabs.show', $rab) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <form method="POST" action="{{ route('rabs.destroy', $rab) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this RAB and all its items?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Del</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No RABs yet. <a href="{{ route('rabs.create') }}">Create one</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $rabs->links() }}
</div>
@endsection
