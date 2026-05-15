@extends('layouts.app')

@section('title', 'Edit Menu – ' . $menu->menu->name)

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.index') }}">RAB Periods</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.show', $rabPeriod) }}">{{ $rabPeriod->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.days.show', [$rabPeriod, $day]) }}">{{ $day->day_date->format('d M Y') }}</a></li>
        <li class="breadcrumb-item active">Edit Menu</li>
    </ol>
</nav>

<h1 class="h3 mb-3">Edit Menu</h1>

<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('rab-periods.days.menus.update', [$rabPeriod, $day, $menu]) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Menu</label>
                <select name="menu_id" class="form-select @error('menu_id') is-invalid @enderror" required>
                    <option value="">-- Select Menu --</option>
                    @foreach($menus as $m)
                        <option value="{{ $m->id }}" @selected(old('menu_id', $menu->menu_id) == $m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
                @error('menu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(old('category', $menu->category) == $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @php $isReplacement = old('is_replacement', $menu->is_replacement); @endphp

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_replacement" id="isReplacement"
                           value="1" @checked($isReplacement)>
                    <label class="form-check-label" for="isReplacement">
                        Allergy Replacement (replaces another menu for allergic students)
                    </label>
                </div>
            </div>

            <div id="allergyFields" class="{{ $isReplacement ? '' : 'd-none' }}">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Replaces Menu</label>
                            <select name="replaces_id" class="form-select @error('replaces_id') is-invalid @enderror">
                                <option value="">-- Select menu this replaces --</option>
                                @foreach($day->menus as $dm)
                                    @if(!$dm->is_replacement && $dm->id !== $menu->id)
                                        <option value="{{ $dm->id }}"
                                                @selected(old('replaces_id', $menu->replaces_id) == $dm->id)>
                                            {{ $dm->menu->name }} ({{ strtoupper($dm->category) }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('replaces_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col">
                                <label class="form-label">Allergy PK Count</label>
                                <input type="number" name="allergy_pk_count"
                                       class="form-control @error('allergy_pk_count') is-invalid @enderror"
                                       value="{{ old('allergy_pk_count', $menu->allergy_pk_count) }}" min="0">
                                @error('allergy_pk_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col">
                                <label class="form-label">Allergy PB Count</label>
                                <input type="number" name="allergy_pb_count"
                                       class="form-control @error('allergy_pb_count') is-invalid @enderror"
                                       value="{{ old('allergy_pb_count', $menu->allergy_pb_count) }}" min="0">
                                @error('allergy_pb_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order"
                       class="form-control @error('sort_order') is-invalid @enderror"
                       value="{{ old('sort_order', $menu->sort_order) }}" min="0" style="max-width:120px">
                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="alert alert-warning small mb-3">
                Changing the menu here does <strong>not</strong> update existing ingredients.
                Delete and re-add this menu row to refresh ingredients from the new recipe.
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('rab-periods.days.show', [$rabPeriod, $day]) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('isReplacement').addEventListener('change', function () {
    document.getElementById('allergyFields').classList.toggle('d-none', !this.checked);
});
</script>
@endsection
