@extends('layouts.app')

@section('title', 'Create Supplier')

@section('content')
<h1 class="h3 mb-3">Create Supplier</h1>

<div class="card">
    <div class="card-body">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            @include('suppliers._form')
        </form>
    </div>
</div>
@endsection
