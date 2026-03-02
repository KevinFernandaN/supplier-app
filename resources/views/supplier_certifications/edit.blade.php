@extends('layouts.app')

@section('title', 'Edit Certification')

@section('content')
<h1 class="h3 mb-3">Edit Certification – {{ $supplier->name }}</h1>

<div class="card" style="max-width:560px">
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.certifications.update', [$supplier, $certification]) }}"
              enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Certification Type <span class="text-danger">*</span></label>
                <select name="certification_id" class="form-select @error('certification_id') is-invalid @enderror" required>
                    @foreach($certifications as $c)
                        <option value="{{ $c->id }}" @selected(old('certification_id', $certification->certification_id) == $c->id)>
                            {{ $c->name }}{{ $c->issuer ? ' (' . $c->issuer . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('certification_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Certificate No.</label>
                <input type="text" name="certificate_no" class="form-control @error('certificate_no') is-invalid @enderror"
                       value="{{ old('certificate_no', $certification->certificate_no) }}">
                @error('certificate_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Issued Date</label>
                    <input type="date" name="issued_at" class="form-control @error('issued_at') is-invalid @enderror"
                           value="{{ old('issued_at', $certification->issued_at?->toDateString()) }}">
                    @error('issued_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expired_at" class="form-control @error('expired_at') is-invalid @enderror"
                           value="{{ old('expired_at', $certification->expired_at?->toDateString()) }}">
                    @error('expired_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Replace Certificate File</label>
                @if($certification->file_path)
                    <div class="mb-1 small text-muted">
                        Current file:
                        <a href="{{ Storage::url($certification->file_path) }}" target="_blank">View</a>
                        (upload a new file to replace it)
                    </div>
                @endif
                <input type="file" name="cert_file" accept=".pdf,.jpg,.jpeg,.png"
                       class="form-control @error('cert_file') is-invalid @enderror">
                <div class="form-text">PDF, JPEG or PNG · Max 4 MB</div>
                @error('cert_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
