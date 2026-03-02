@extends('layouts.app')

@section('title', 'Edit Menu')

@section('content')
<h1 class="h3 mb-3">Edit Menu</h1>

<form method="POST" action="{{ route('menus.update', $menu) }}">
    @csrf
    @method('PUT')
    @include('menus._form', ['menu' => $menu])
</form>
@endsection
