@extends('layouts.app')

@section('title', 'Menus')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Menus</h1>
    <a href="{{ route('menus.create') }}" class="btn btn-primary">+ New Menu</a>
</div>

<table class="table table-bordered align-middle">
    <thead>
    <tr>
        <th>Name</th>
        <th>Selling Price</th>
        <th width="220">Action</th>
    </tr>
    </thead>
    <tbody>
    @forelse($menus as $menu)
        <tr>
            <td class="fw-semibold">{{ $menu->name }}</td>
            <td>
                @if($menu->default_selling_price)
                    Rp {{ number_format($menu->default_selling_price, 0) }}
                @else
                    -
                @endif
            </td>
            <td>
                <a href="{{ route('menus.show', $menu) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('menus.destroy', $menu) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Delete this menu?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center py-4">No menus yet.</td>
        </tr>
    @endforelse
    </tbody>
</table>

{{ $menus->links() }}
@endsection
