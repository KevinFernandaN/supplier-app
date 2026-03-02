<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Region;
use App\Models\SupplierReview;
use Illuminate\Http\Request;

class SupplierReviewController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function create(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier');

        // Prevent duplicates
        $existing = SupplierReview::where('purchase_order_id', $purchaseOrder->id)->first();
        if ($existing) {
            return redirect()->route('purchase-orders.reviews.show', [$purchaseOrder, $existing]);
        }

        return view('supplier_reviews.create', compact('purchaseOrder'));
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier');

        // Prevent duplicates again (race-safe)
        if (SupplierReview::where('purchase_order_id', $purchaseOrder->id)->exists()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Review already exists for this PO.');
        }

        $validated = $request->validate([
            'review_date' => ['required', 'date'],

            // 1-5 scoring
            'goods_correct' => ['required','integer','min:1','max:5'],
            'weight_correct' => ['required','integer','min:1','max:5'],
            'on_time' => ['required','integer','min:1','max:5'],
            'price_correct' => ['required','integer','min:1','max:5'],

            'notes' => ['nullable', 'string'],
        ]);

        $overall = (
            $validated['goods_correct'] +
            $validated['weight_correct'] +
            $validated['on_time'] +
            $validated['price_correct']
        ) / 4;

        $review = SupplierReview::create([
            'region_id' => $this->currentRegionId(),
            'supplier_id' => $purchaseOrder->supplier_id,
            'purchase_order_id' => $purchaseOrder->id,
            'review_date' => $validated['review_date'],
            'goods_correct' => $validated['goods_correct'],
            'weight_correct' => $validated['weight_correct'],
            'on_time' => $validated['on_time'],
            'price_correct' => $validated['price_correct'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('purchase-orders.reviews.show', [$purchaseOrder, $review])
            ->with('success', 'Supplier review saved.');
    }

    public function show(PurchaseOrder $purchaseOrder, SupplierReview $review)
    {
        $purchaseOrder->load('supplier');

        // Safety: ensure review belongs to PO
        abort_unless($review->purchase_order_id === $purchaseOrder->id, 404);

        return view('supplier_reviews.show', compact('purchaseOrder', 'review'));
    }
}
