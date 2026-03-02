@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Product Details</h1>
    <div>
        <a class="btn btn-outline-primary" href="{{ route('products.edit', $product) }}">Edit</a>
        <a class="btn btn-outline-secondary" href="{{ route('products.index') }}">Back</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="mb-2"><span class="text-muted">Name:</span> <strong>{{ $product->name }}</strong></div>
        <div class="mb-2"><span class="text-muted">Category:</span> {{ $product->category ?? '-' }}</div>
        <div class="mb-2"><span class="text-muted">Base Unit:</span> {{ $product->baseUnit?->name }} ({{ $product->baseUnit?->symbol }})</div>
        <div class="text-muted small">Created: {{ $product->created_at }}</div>
    </div>
</div>
@endsection
