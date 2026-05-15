@extends('layouts.app')

@section('title', 'Add Menu – ' . $day->day_date->format('d M Y'))

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.index') }}">RAB Periods</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.show', $rabPeriod) }}">{{ $rabPeriod->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rab-periods.days.show', [$rabPeriod, $day]) }}">{{ $day->day_date->format('d M Y') }}</a></li>
        <li class="breadcrumb-item active">Add Menu</li>
    </ol>
</nav>

<h1 class="h3 mb-3">Add Menu</h1>

<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('rab-periods.days.menus.store', [$rabPeriod, $day]) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Menu</label>
                <select name="menu_id" class="form-select @error('menu_id') is-invalid @enderror" required>
                    <option value="">-- Select Menu --</option>
                    @foreach($menus as $m)
                        <option value="{{ $m->id }}" @selected(old('menu_id') == $m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
                @error('menu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(old('category') == $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_replacement" id="isReplacement"
                           value="1" @checked(old('is_replacement'))>
                    <label class="form-check-label" for="isReplacement">
                        Allergy Replacement (replaces another menu for allergic students)
                    </label>
                </div>
            </div>

            {{-- Allergy fields (shown when is_replacement is checked) --}}
            <div id="allergyFields" class="{{ old('is_replacement') ? '' : 'd-none' }}">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Replaces Menu</label>
                            <select name="replaces_id" class="form-select @error('replaces_id') is-invalid @enderror">
                                <option value="">-- Select menu this replaces --</option>
                                @foreach($day->menus as $dm)
                                    @if(!$dm->is_replacement)
                                        <option value="{{ $dm->id }}" @selected(old('replaces_id') == $dm->id)>
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
                                       value="{{ old('allergy_pk_count', 0) }}" min="0">
                                <div class="form-text">Number of PK students allergic to the original menu</div>
                                @error('allergy_pk_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col">
                                <label class="form-label">Allergy PB Count</label>
                                <input type="number" name="allergy_pb_count"
                                       class="form-control @error('allergy_pb_count') is-invalid @enderror"
                                       value="{{ old('allergy_pb_count', 0) }}" min="0">
                                <div class="form-text">Number of PB students allergic to the original menu</div>
                                @error('allergy_pb_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Sort Order <span class="text-muted">(optional)</span></label>
                <input type="number" name="sort_order"
                       class="form-control @error('sort_order') is-invalid @enderror"
                       value="{{ old('sort_order', 0) }}" min="0" style="max-width:120px">
                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="alert alert-info small mb-3">
                Ingredients will be auto-populated from the menu's recipe with the last known purchase price.
                You can adjust gramasi and prices after creation.
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success">Add Menu</button>
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
