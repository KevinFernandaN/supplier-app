@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<h1 class="h3 mb-3">Edit Supplier</h1>

<div class="card">
    <div class="card-body">
        <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
            @csrf
            @method('PUT')
            @include('suppliers._form', ['supplier' => $supplier])
        </form>
    </div>
</div>
@endsection
