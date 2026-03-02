@extends('layouts.app')

@section('title', 'Menu Recipe')

@section('content')
<h1 class="h3 mb-3">
    Recipe - {{ $menu->name }}
</h1>

<a href="{{ route('menus.recipes.create', $menu) }}"
   class="btn btn-primary mb-3">
    + Add Ingredient
</a>

<table class="table table-bordered align-middle">
    <thead>
    <tr>
        <th>Product</th>
        <th>Quantity</th>
        <th>Unit</th>
        <th width="120">Action</th>
    </tr>
    </thead>
    <tbody>
    @forelse($recipes as $recipe)
        <tr>
            <td>{{ $recipe->product->name }}</td>
            <td>{{ $recipe->qty }}</td>
            <td>{{ $recipe->unit->symbol }}</td>
            <td>
                <a class="btn btn-sm btn-primary"
                href="{{ route('menus.recipes.edit', [$menu, $recipe]) }}">
                    Edit
                </a>

                <form action="{{ route('menus.recipes.destroy', [$menu, $recipe]) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Delete ingredient?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="text-center py-4">No ingredients yet.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<a href="{{ route('menus.show', $menu) }}"
   class="btn btn-secondary">
    Back
</a>
@endsection
