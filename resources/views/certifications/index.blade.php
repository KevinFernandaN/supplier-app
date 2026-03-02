@extends('layouts.app')

@section('title', 'Certification Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Certification Types</h1>
    <a href="{{ route('certifications.create') }}" class="btn btn-primary btn-sm">+ New Type</a>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Issuer / Body</th>
                    <th>Suppliers Using</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($certifications as $cert)
                    <tr>
                        <td>{{ $cert->name }}</td>
                        <td>{{ $cert->issuer ?? '-' }}</td>
                        <td>{{ $cert->supplierCertifications->count() }}</td>
                        <td>
                            <a href="{{ route('certifications.edit', $cert) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('certifications.destroy', $cert) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this certification type?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">✕</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-3 text-muted">No certification types yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $certifications->links() }}</div>
@endsection
