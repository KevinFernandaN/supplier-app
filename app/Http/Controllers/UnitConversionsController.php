<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Http\Request;

class UnitConversionsController extends Controller
{
    public function index()
    {
        $conversions = UnitConversion::with(['fromUnit', 'toUnit'])
            ->orderBy('from_unit_id')
            ->orderBy('to_unit_id')
            ->get();

        return view('unit_conversions.index', compact('conversions'));
    }

    public function create()
    {
        $units = Unit::orderBy('name')->get();
        return view('unit_conversions.create', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_unit_id' => 'required|exists:units,id',
            'to_unit_id'   => 'required|exists:units,id|different:from_unit_id',
            'multiplier'   => 'required|numeric|gt:0',
        ]);

        UnitConversion::create($data);

        return redirect()->route('unit-conversions.index')
            ->with('success', 'Conversion added.');
    }

    public function edit(UnitConversion $unitConversion)
    {
        $units = Unit::orderBy('name')->get();
        return view('unit_conversions.edit', compact('unitConversion', 'units'));
    }

    public function update(Request $request, UnitConversion $unitConversion)
    {
        $data = $request->validate([
            'from_unit_id' => 'required|exists:units,id',
            'to_unit_id'   => 'required|exists:units,id|different:from_unit_id',
            'multiplier'   => 'required|numeric|gt:0',
        ]);

        $unitConversion->update($data);

        return redirect()->route('unit-conversions.index')
            ->with('success', 'Conversion updated.');
    }

    public function destroy(UnitConversion $unitConversion)
    {
        $unitConversion->delete();

        return redirect()->route('unit-conversions.index')
            ->with('success', 'Conversion deleted.');
    }
}
