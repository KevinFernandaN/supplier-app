<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Supplier;
use App\Models\SupplierCertification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierCertificationController extends Controller
{
    public function create(Supplier $supplier)
    {
        $certifications = Certification::orderBy('name')->get();
        return view('supplier_certifications.create', compact('supplier', 'certifications'));
    }

    public function store(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'certification_id' => ['required', 'exists:certifications,id'],
            'certificate_no'   => ['nullable', 'string', 'max:100'],
            'issued_at'        => ['nullable', 'date'],
            'expired_at'       => ['nullable', 'date', 'after_or_equal:issued_at'],
            'cert_file'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $filePath = null;
        if ($request->hasFile('cert_file')) {
            $filePath = $request->file('cert_file')->store('supplier-certs/' . $supplier->id, 'public');
        }

        $supplier->certifications()->create([
            'certification_id' => $validated['certification_id'],
            'certificate_no'   => $validated['certificate_no'] ?? null,
            'issued_at'        => $validated['issued_at'] ?? null,
            'expired_at'       => $validated['expired_at'] ?? null,
            'file_path'        => $filePath,
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Certification attached.');
    }

    public function edit(Supplier $supplier, SupplierCertification $certification)
    {
        abort_if($certification->supplier_id !== $supplier->id, 404);
        $certifications = Certification::orderBy('name')->get();
        return view('supplier_certifications.edit', compact('supplier', 'certification', 'certifications'));
    }

    public function update(Request $request, Supplier $supplier, SupplierCertification $certification)
    {
        abort_if($certification->supplier_id !== $supplier->id, 404);

        $validated = $request->validate([
            'certification_id' => ['required', 'exists:certifications,id'],
            'certificate_no'   => ['nullable', 'string', 'max:100'],
            'issued_at'        => ['nullable', 'date'],
            'expired_at'       => ['nullable', 'date', 'after_or_equal:issued_at'],
            'cert_file'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $filePath = $certification->file_path;
        if ($request->hasFile('cert_file')) {
            // Delete old file if exists
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('cert_file')->store('supplier-certs/' . $supplier->id, 'public');
        }

        $certification->update([
            'certification_id' => $validated['certification_id'],
            'certificate_no'   => $validated['certificate_no'] ?? null,
            'issued_at'        => $validated['issued_at'] ?? null,
            'expired_at'       => $validated['expired_at'] ?? null,
            'file_path'        => $filePath,
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Certification updated.');
    }

    public function destroy(Supplier $supplier, SupplierCertification $certification)
    {
        abort_if($certification->supplier_id !== $supplier->id, 404);

        if ($certification->file_path) {
            Storage::disk('public')->delete($certification->file_path);
        }
        $certification->delete();

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Certification removed.');
    }
}
