@extends('layouts.app')

@section('title', 'Attach Certification')

@section('content')
<h1 class="h3 mb-3">Attach Certification – {{ $supplier->name }}</h1>

<div class="card" style="max-width:560px">
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.certifications.store', $supplier) }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Certification Type <span class="text-danger">*</span></label>
                <select name="certification_id" class="form-select @error('certification_id') is-invalid @enderror" required>
                    <option value="">-- Select --</option>
                    @foreach($certifications as $c)
                        <option value="{{ $c->id }}" @selected(old('certification_id') == $c->id)>
                            {{ $c->name }}{{ $c->issuer ? ' (' . $c->issuer . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('certification_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">
                    <a href="{{ route('certifications.create') }}" target="_blank">+ Add new certification type</a>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Certificate No.</label>
                <input type="text" name="certificate_no" class="form-control @error('certificate_no') is-invalid @enderror"
                       value="{{ old('certificate_no') }}" placeholder="e.g. HLT-20240001">
                @error('certificate_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Issued Date</label>
                    <input type="date" name="issued_at" class="form-control @error('issued_at') is-invalid @enderror"
                           value="{{ old('issued_at') }}">
                    @error('issued_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expired_at" class="form-control @error('expired_at') is-invalid @enderror"
                           value="{{ old('expired_at') }}">
                    @error('expired_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Certificate File</label>
                <input type="file" name="cert_file" accept=".pdf,.jpg,.jpeg,.png"
                       class="form-control @error('cert_file') is-invalid @enderror">
                <div class="form-text">PDF, JPEG or PNG · Max 4 MB</div>
                @error('cert_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Attach</button>
                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
