@extends('layouts.app')

@section('title','Edit Return')

@section('content')
<h1 class="h3 mb-3">Edit Return</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('returns.update', $returnOrder) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id', $returnOrder->supplier_id)==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Return Date</label>
                <input type="date" name="return_date" class="form-control"
                       value="{{ old('return_date', $returnOrder->return_date->format('Y-m-d')) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    @foreach(['draft','sent','closed'] as $st)
                        <option value="{{ $st }}" @selected(old('status',$returnOrder->status)===$st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes',$returnOrder->notes) }}</textarea>
            </div>

            <button class="btn btn-primary">Update</button>
            <a class="btn btn-secondary" href="{{ route('returns.show',$returnOrder) }}">Back</a>
        </form>
    </div>
</div>
@endsection
