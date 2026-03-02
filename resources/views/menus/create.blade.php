@extends('layouts.app')

@section('title', 'Create Menu')

@section('content')
<h1 class="h3 mb-3">Create Menu</h1>

<form method="POST" action="{{ route('menus.store') }}">
    @csrf
    @include('menus._form')
</form>
@endsection
