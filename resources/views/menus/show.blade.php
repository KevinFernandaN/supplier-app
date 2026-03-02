@extends('layouts.app')

@section('title', 'Menu Detail')

@section('content')
<h1 class="h3 mb-3">{{ $menu->name }}</h1>

<p>
    <strong>Selling Price:</strong>
    @if($menu->default_selling_price)
        Rp {{ number_format($menu->default_selling_price, 0) }}
    @else
        -
    @endif
</p>

<hr>

<h4>Recipe / Ingredients</h4>

<a href="{{ route('menus.recipes.index', $menu) }}"
   class="btn btn-primary">
    Manage Recipe
</a>

<a href="{{ route('menus.index') }}" class="btn btn-secondary">
    Back
</a>
@endsection
