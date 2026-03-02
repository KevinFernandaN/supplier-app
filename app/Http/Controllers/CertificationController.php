<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    public function index()
    {
        $certifications = Certification::orderBy('name')->paginate(20);
        return view('certifications.index', compact('certifications'));
    }

    public function create()
    {
        return view('certifications.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
        ]);

        Certification::create($validated);

        return redirect()->route('certifications.index')
            ->with('success', 'Certification type added.');
    }

    public function edit(Certification $certification)
    {
        return view('certifications.edit', compact('certification'));
    }

    public function update(Request $request, Certification $certification)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
        ]);

        $certification->update($validated);

        return redirect()->route('certifications.index')
            ->with('success', 'Certification type updated.');
    }

    public function destroy(Certification $certification)
    {
        if ($certification->supplierCertifications()->exists()) {
            return back()->with('error', 'Cannot delete: suppliers are using this certification.');
        }

        $certification->delete();

        return redirect()->route('certifications.index')
            ->with('success', 'Certification type deleted.');
    }
}
