@extends('layouts.app')

@section('title', 'Add Supplier Review')

@section('content')
<h1 class="h3 mb-3">Add Supplier Review</h1>

<div class="card mb-3">
    <div class="card-body">
        <div><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}</div>
        <div><strong>PO Date:</strong> {{ $purchaseOrder->order_date }}</div>
        <div><strong>Status:</strong> {{ $purchaseOrder->status }}</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('purchase-orders.reviews.store', $purchaseOrder) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Review Date</label>
                <input type="date" name="review_date" class="form-control"
                       value="{{ old('review_date', now()->toDateString()) }}" required>
            </div>

            @php
                $fields = [
                    'goods_correct' => 'Are the goods correct?',
                    'weight_correct' => 'Is the weight correct?',
                    'on_time' => 'Were goods received on time?',
                    'price_correct' => 'Is the price correct?',
                ];
            @endphp

            @foreach($fields as $name => $label)
                <div class="mb-3">
                    <label class="form-label">{{ $label }} (1-5)</label>
                    <select name="{{ $name }}" class="form-select" required>
                        @for($i=1;$i<=5;$i++)
                            <option value="{{ $i }}" @selected((int)old($name, 5) === $i)>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            @endforeach

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>

            <button class="btn btn-primary">Save Review</button>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
