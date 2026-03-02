@extends('layouts.app')

@section('title', 'Supplier Review')

@section('content')
<h1 class="h3 mb-3">Supplier Review</h1>

<div class="card mb-3">
    <div class="card-body">
        <div><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}</div>
        <div><strong>Review Date:</strong> {{ $review->review_date->format('Y-m-d') }}</div>
        <div><strong>Overall Score:</strong> {{ number_format($review->overall_score, 2) }}</div>
    </div>
</div>

<table class="table table-bordered">
    <tr><th>Goods correct</th><td>{{ $review->goods_correct }}</td></tr>
    <tr><th>Weight correct</th><td>{{ $review->weight_correct }}</td></tr>
    <tr><th>On time</th><td>{{ $review->on_time}}</td></tr>
    <tr><th>Price correct</th><td>{{ $review->price_correct }}</td></tr>
</table>

@if($review->notes)
    <div class="card">
        <div class="card-body">
            <strong>Notes:</strong>
            <div class="mt-2">{{ $review->notes }}</div>
        </div>
    </div>
@endif

<a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary mt-3">Back to PO</a>
@endsection
