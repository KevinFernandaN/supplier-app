@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<h1 class="h3 mb-3">Edit Product</h1>

<div class="card">
    <div class="card-body">
        <form action="{{ route('products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')
            @include('products._form', ['product' => $product, 'units' => $units])
        </form>
    </div>
</div>
@endsection
