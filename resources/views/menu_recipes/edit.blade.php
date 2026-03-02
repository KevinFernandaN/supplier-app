@extends('layouts.app')

@section('title', 'Edit Ingredient')

@section('content')
<h1 class="h3 mb-3">Edit Ingredient - {{ $menu->name }}</h1>

<form method="POST" action="{{ route('menus.recipes.update', [$menu, $recipe]) }}">
    @csrf
    @method('PUT')

    @include('menu_recipes._form', [
        'menu' => $menu,
        'recipe' => $recipe,
        'products' => $products,
        'units' => $units
    ])
</form>
@endsection
