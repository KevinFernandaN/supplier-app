@extends('layouts.app')

@section('title','Create Return')

@section('content')
<h1 class="h3 mb-3">Create Return</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('returns.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">-- Select supplier --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Return Date</label>
                <input type="date" name="return_date" class="form-control"
                       value="{{ old('return_date', now()->toDateString()) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="draft" @selected(old('status','draft')==='draft')>draft</option>
                    <option value="sent" @selected(old('status')==='sent')>sent</option>
                    <option value="closed" @selected(old('status')==='closed')>closed</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>

            <button class="btn btn-primary">Save</button>
            <a class="btn btn-secondary" href="{{ route('returns.index') }}">Back</a>
        </form>
    </div>
</div>
@endsection
