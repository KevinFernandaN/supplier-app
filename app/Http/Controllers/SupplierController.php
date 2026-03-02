<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private function currentRegionId(): int
    {
        // Simple MVP: pick first active region (seeded JKT)
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index()
    {
        $regionId = $this->currentRegionId();

        $suppliers = Supplier::where('region_id', $regionId)
            ->orderBy('name')
            ->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $regionId = $this->currentRegionId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_wa' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['region_id'] = $regionId;
        $validated['is_active'] = $request->boolean('is_active', true);

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $regionId = $this->currentRegionId();
        abort_if($supplier->region_id !== $regionId, 404);

        $supplier->load(['photos', 'certifications.certification']);

        $latestKpi = $supplier->kpis()->orderBy('month', 'desc')->first();
        $kpiHistory = $supplier->kpis()->orderBy('month', 'desc')->limit(6)->get();

        return view('suppliers.show', compact('supplier', 'latestKpi', 'kpiHistory'));
    }

    public function edit(Supplier $supplier)
    {
        $regionId = $this->currentRegionId();
        abort_if($supplier->region_id !== $regionId, 404);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $regionId = $this->currentRegionId();
        abort_if($supplier->region_id !== $regionId, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_wa' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $regionId = $this->currentRegionId();
        abort_if($supplier->region_id !== $regionId, 404);

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
