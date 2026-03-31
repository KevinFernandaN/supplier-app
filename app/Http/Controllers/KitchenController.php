<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Region;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index()
    {
        $kitchens = Kitchen::with('region')
            ->orderBy('name')
            ->paginate(15);

        return view('kitchens.index', compact('kitchens'));
    }

    public function create()
    {
        $regions = Region::where('is_active', true)->orderBy('name')->get();
        return view('kitchens.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
            'name'      => ['required', 'string', 'max:255'],
            'type'      => ['required', 'in:open,assisted'],
            'address'   => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Kitchen::create($validated);

        return redirect()->route('kitchens.index')
            ->with('success', 'Kitchen created successfully.');
    }

    public function edit(Kitchen $kitchen)
    {
        $regions = Region::where('is_active', true)->orderBy('name')->get();
        return view('kitchens.edit', compact('kitchen', 'regions'));
    }

    public function update(Request $request, Kitchen $kitchen)
    {
        $validated = $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
            'name'      => ['required', 'string', 'max:255'],
            'type'      => ['required', 'in:open,assisted'],
            'address'   => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $kitchen->update($validated);

        return redirect()->route('kitchens.index')
            ->with('success', 'Kitchen updated successfully.');
    }

    public function destroy(Kitchen $kitchen)
    {
        $kitchen->delete();

        return redirect()->route('kitchens.index')
            ->with('success', 'Kitchen deleted.');
    }
}
