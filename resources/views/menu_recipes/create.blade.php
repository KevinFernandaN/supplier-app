@extends('layouts.app')

@section('title', 'Add Ingredient')

@section('content')
<h1 class="h3 mb-3">
    Add Ingredient - {{ $menu->name }}
</h1>

<form method="POST" action="{{ route('menus.recipes.store', $menu) }}">
    @csrf

    @include('menu_recipes._form', [
        'menu' => $menu,
        'products' => $products,
        'units' => $units
    ])
</form>
@endsection
