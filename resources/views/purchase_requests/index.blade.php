@extends('layouts.app')

@section('title', 'Purchase Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Purchase Requests</h1>
    <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary">+ New PR</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Kitchen</th>
                    <th>Menu</th>
                    <th class="text-end">Portions</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end" style="width: 120px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($prs as $pr)
                    <tr>
                        <td class="text-muted">{{ $pr->id }}</td>
                        <td class="fw-semibold">{{ $pr->kitchen->name }}</td>
                        <td>{{ $pr->menu->name }}</td>
                        <td class="text-end">{{ number_format($pr->total_portion, 0) }}</td>
                        <td>
                            @if ($pr->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif ($pr->status === 'confirmed')
                                <span class="badge bg-primary">Confirmed</span>
                            @else
                                <span class="badge bg-success">Ordered</span>
                            @endif
                        </td>
                        <td>{{ $pr->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('purchase-requests.show', $pr) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4">No purchase requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $prs->links() }}</div>
@endsection
