@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
<h1 class="h3 mb-3">Create Product</h1>

<div class="card">
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            @include('products._form', ['units' => $units])
        </form>
    </div>
</div>
@endsection
