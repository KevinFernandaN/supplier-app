<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\PurchaseOrderItem;
use App\Models\Region;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index(Request $request)
    {
        $query = \App\Models\Complaint::with(['supplier', 'product'])
            ->latest('complaint_date');

        // Optional filter by purchase_order_item_id
        if ($request->filled('purchase_order_item_id')) {
            $query->where('purchase_order_item_id', $request->purchase_order_item_id);
        }

        $complaints = $query->paginate(15)->withQueryString();

        return view('complaints.index', compact('complaints'));
    }

    public function create(PurchaseOrderItem $purchaseOrderItem)
    {
        // Load relations for display
        $purchaseOrderItem->load('purchaseOrder.supplier', 'product', 'unit');

        $types = [
            'wrong_item' => 'Wrong item',
            'wrong_weight' => 'Wrong weight',
            'late_delivery' => 'Late delivery',
            'bad_quality' => 'Bad quality (rotten/damaged)',
            'price_mismatch' => 'Price mismatch',
            'other' => 'Other',
        ];

        return view('complaints.create', compact('purchaseOrderItem', 'types'));
    }

    public function store(Request $request, PurchaseOrderItem $purchaseOrderItem)
    {
        $purchaseOrderItem->load('purchaseOrder.supplier');

        $validated = $request->validate([
            'complaint_date' => ['required', 'date'],
            'complaint_type' => ['required', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:50'],
            'severity' => ['required', 'integer', 'min:1', 'max:5'],
            'qty' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
        ]);

        Complaint::create([
            'region_id' => $this->currentRegionId(),
            'purchase_order_item_id' => $purchaseOrderItem->id,
            'supplier_id' => $purchaseOrderItem->purchaseOrder->supplier_id,
            'product_id' => $purchaseOrderItem->product_id,
            'complaint_date' => $validated['complaint_date'],
            'complaint_type' => $validated['complaint_type'],
            'type' => $validated['type'],
            'severity' => $validated['severity'],
            'qty' => $validated['qty'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        // Redirect back to the PO show page
        return redirect()
            ->route('purchase-orders.show', $purchaseOrderItem->purchase_order_id)
            ->with('success', 'Complaint recorded.');
    }

    public function resolve(\App\Models\Complaint $complaint)
    {
        $complaint->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Complaint resolved.');
    }

    public function reopen(\App\Models\Complaint $complaint)
    {
        $complaint->update([
            'status' => 'open',
            'resolved_at' => null,
        ]);

        return back()->with('success', 'Complaint reopened.');
    }
}
