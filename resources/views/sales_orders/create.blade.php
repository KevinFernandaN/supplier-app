@extends('layouts.app')

@section('title', 'Create Sales Order')

@section('content')
<h1 class="h3 mb-3">Create Sales Order</h1>

<form method="POST" action="{{ route('sales-orders.store') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Sale Date</label>
        <input type="date" name="sale_date" class="form-control"
               value="{{ old('sale_date') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Channel</label>
        <input type="text" name="channel" class="form-control"
               value="{{ old('channel') }}" placeholder="offline / gofood / grab / etc">
    </div>

    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
    </div>

    <button class="btn btn-primary">Create</button>
    <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
