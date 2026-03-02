@extends('layouts.app')

@section('title', 'Unit Conversions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Unit Conversions</h1>
    <a href="{{ route('unit-conversions.create') }}" class="btn btn-primary">+ Add Conversion</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>From Unit</th>
                    <th>To Unit</th>
                    <th>Multiplier</th>
                    <th class="text-muted small fw-normal">Example</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conversions as $c)
                    <tr>
                        <td>{{ $c->fromUnit->name }} ({{ $c->fromUnit->symbol }})</td>
                        <td>{{ $c->toUnit->name }} ({{ $c->toUnit->symbol }})</td>
                        <td>{{ rtrim(rtrim(number_format($c->multiplier, 10), '0'), '.') }}</td>
                        <td class="text-muted small">
                            1 {{ $c->fromUnit->symbol }} = {{ rtrim(rtrim(number_format($c->multiplier, 10), '0'), '.') }} {{ $c->toUnit->symbol }}
                        </td>
                        <td>
                            <a href="{{ route('unit-conversions.edit', $c) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('unit-conversions.destroy', $c) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this conversion?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Del</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No conversions defined yet.
                            <a href="{{ route('unit-conversions.create') }}">Add one now</a> to fix unit mismatch in margin reports.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body text-muted small">
        <strong>How it works:</strong>
        Multiplier converts recipe quantities to purchase quantities before COGS is calculated.
        Example: recipe uses <em>500 g</em> flour, purchase price is per <em>kg</em>.
        Add conversion: <strong>g → kg, multiplier = 0.001</strong>.
        COGS = 500 × 0.001 × price/kg = correct result.
    </div>
</div>
@endsection
