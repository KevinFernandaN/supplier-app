@extends('layouts.app')

@section('title', 'RAB Periods')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">RAB Periods</h1>
    <a href="{{ route('rab-periods.create') }}" class="btn btn-primary">+ New Period</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Period</th>
                    <th class="text-center">PK Price</th>
                    <th class="text-center">PB Price</th>
                    <th class="text-center">Days</th>
                    <th class="text-center">Status</th>
                    <th width="140">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $period)
                    <tr>
                        <td><strong>{{ $period->name }}</strong></td>
                        <td class="small">
                            {{ $period->start_date->format('d M Y') }}
                            &rarr;
                            {{ $period->end_date->format('d M Y') }}
                        </td>
                        <td class="text-center small">Rp {{ number_format($period->pk_price) }}</td>
                        <td class="text-center small">Rp {{ number_format($period->pb_price) }}</td>
                        <td class="text-center">{{ $period->days_count }}</td>
                        <td class="text-center">
                            @php
                                $badge = match($period->status) {
                                    'draft'     => 'secondary',
                                    'confirmed' => 'primary',
                                    'locked'    => 'success',
                                    default     => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($period->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('rab-periods.show', $period) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('rab-periods.edit', $period) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('rab-periods.destroy', $period) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this period and all its days/menus/items?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Del</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No RAB periods yet. <a href="{{ route('rab-periods.create') }}">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $periods->links() }}</div>
@endsection
