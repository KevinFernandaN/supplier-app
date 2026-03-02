@extends('layouts.app')

@section('title', 'New Certification Type')

@section('content')
<h1 class="h3 mb-3">New Certification Type</h1>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('certifications.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="e.g. Halal, ISO 9001, BPOM" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Issuer / Certifying Body</label>
                <input type="text" name="issuer" class="form-control @error('issuer') is-invalid @enderror"
                       value="{{ old('issuer') }}" placeholder="e.g. MUI, BSN, BPOM">
                @error('issuer')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
