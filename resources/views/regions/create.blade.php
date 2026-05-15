@extends('layouts.app')

@section('title', 'New Region')

@section('content')
<h1 class="h3 mb-3">New Region</h1>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('regions.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Code <span class="text-muted small">(short, unique — e.g. LPG, JKT)</span></label>
                <input type="text" name="code"
                       class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code') }}" maxlength="20" required style="max-width:140px">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Timezone</label>
                <input type="text" name="timezone"
                       class="form-control @error('timezone') is-invalid @enderror"
                       value="{{ old('timezone', 'Asia/Jakarta') }}" required>
                @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active"
                           id="isActive" value="1" @checked(old('is_active', true))>
                    <label class="form-check-label" for="isActive">Active</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success">Create Region</button>
                <a href="{{ route('regions.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
