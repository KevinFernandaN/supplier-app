@extends('layouts.app')

@section('title', 'Upload Photo')

@section('content')
<h1 class="h3 mb-3">Upload Photo – {{ $supplier->name }}</h1>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.photos.store', $supplier) }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Photo <span class="text-danger">*</span></label>
                <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/webp"
                       class="form-control @error('photo') is-invalid @enderror" required>
                <div class="form-text">JPEG, PNG or WebP · Max 2 MB</div>
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Caption</label>
                <input type="text" name="caption" class="form-control @error('caption') is-invalid @enderror"
                       value="{{ old('caption') }}" placeholder="Optional description">
                @error('caption')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Upload</button>
                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
