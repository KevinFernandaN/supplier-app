<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->get();
        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255|unique:units,name',
            'symbol' => 'required|string|max:20|unique:units,symbol',
        ]);

        Unit::create($data);

        return redirect()->route('units.index')->with('success', 'Unit added.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255|unique:units,name,' . $unit->id,
            'symbol' => 'required|string|max:20|unique:units,symbol,' . $unit->id,
        ]);

        $unit->update($data);

        return redirect()->route('units.index')->with('success', 'Unit updated.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted.');
    }
}
