<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::orderBy('name')->get();
        return view('regions.index', compact('regions'));
    }

    public function create()
    {
        return view('regions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:20|unique:regions,code',
            'name'      => 'required|string|max:191',
            'timezone'  => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Region::create($data);

        return redirect()->route('regions.index')
            ->with('success', 'Region created.');
    }

    public function edit(Region $region)
    {
        return view('regions.edit', compact('region'));
    }

    public function update(Request $request, Region $region)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:20|unique:regions,code,' . $region->id,
            'name'      => 'required|string|max:191',
            'timezone'  => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $region->update($data);

        return redirect()->route('regions.index')
            ->with('success', 'Region updated.');
    }

    public function destroy(Region $region)
    {
        $region->delete();
        return redirect()->route('regions.index')
            ->with('success', 'Region deleted.');
    }
}
