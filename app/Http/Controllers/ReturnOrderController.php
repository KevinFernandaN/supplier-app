<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\ReturnOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ReturnOrderController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index()
    {
        $regionId = $this->currentRegionId();

        $returns = ReturnOrder::with('supplier')
            ->where('region_id', $regionId)
            ->orderBy('return_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('returns.index', compact('returns'));
    }

    public function create()
    {
        $regionId = $this->currentRegionId();

        $suppliers = Supplier::where('region_id', $regionId)->orderBy('name')->get();

        return view('returns.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $regionId = $this->currentRegionId();

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'return_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        // Ensure supplier is same region (safety)
        $supplier = Supplier::where('id', $validated['supplier_id'])->where('region_id', $regionId)->firstOrFail();

        $return = ReturnOrder::create([
            'region_id' => $regionId,
            'supplier_id' => $supplier->id,
            'return_date' => $validated['return_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('returns.show', $return)->with('success', 'Return created.');
    }

    public function show(ReturnOrder $return)
    {
        $regionId = $this->currentRegionId();
        abort_if($return->region_id !== $regionId, 404);

        $return->load('supplier', 'items.product', 'items.unit');

        return view('returns.show', ['returnOrder' => $return]);
    }

    public function edit(ReturnOrder $return)
    {
        $regionId = $this->currentRegionId();
        abort_if($return->region_id !== $regionId, 404);

        $suppliers = Supplier::where('region_id', $regionId)->orderBy('name')->get();

        return view('returns.edit', ['returnOrder' => $return, 'suppliers' => $suppliers]);
    }

    public function update(Request $request, ReturnOrder $return)
    {
        $regionId = $this->currentRegionId();
        abort_if($return->region_id !== $regionId, 404);

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'return_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier = Supplier::where('id', $validated['supplier_id'])->where('region_id', $regionId)->firstOrFail();

        $return->update([
            'supplier_id' => $supplier->id,
            'return_date' => $validated['return_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('returns.show', $return)->with('success', 'Return updated.');
    }

    public function destroy(ReturnOrder $return)
    {
        $regionId = $this->currentRegionId();
        abort_if($return->region_id !== $regionId, 404);

        $return->delete();

        return redirect()->route('returns.index')->with('success', 'Return deleted.');
    }
}
