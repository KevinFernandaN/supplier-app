<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;

class PurchaseRequestItemController extends Controller
{
    public function update(Request $request, PurchaseRequest $purchaseRequest, PurchaseRequestItem $item)
    {
        if ($purchaseRequest->status !== 'draft') {
            return back()->with('error', 'Buffers can only be edited on draft PRs.');
        }

        $validated = $request->validate([
            'buffer_pct' => ['required', 'numeric', 'min:0', 'max:20'],
        ]);

        $item->update($validated);

        return back()->with('success', 'Buffer updated.');
    }
}
