<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Region;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceivingController extends Controller
{
    public function index()
    {
        $receivings = Receiving::with('purchaseOrder.supplier', 'kitchen')
            ->orderByDesc('id')
            ->paginate(15);

        return view('receivings.index', compact('receivings'));
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'kitchen_id' => ['required', 'exists:kitchens,id'],
        ]);

        // Prevent the same kitchen from receiving the same PO twice
        $alreadyExists = Receiving::where('purchase_order_id', $purchaseOrder->id)
            ->where('kitchen_id', $request->kitchen_id)
            ->exists();

        if ($alreadyExists) {
            return back()->with('error', 'This kitchen already has a receiving record for this PO.');
        }

        $purchaseOrder->load('items');

        $receiving = Receiving::create([
            'purchase_order_id' => $purchaseOrder->id,
            'kitchen_id'        => $request->kitchen_id,
            'received_at'       => null,
        ]);

        foreach ($purchaseOrder->items as $item) {
            ReceivingItem::create([
                'receiving_id'           => $receiving->id,
                'purchase_order_item_id' => $item->id,
                'product_id'             => $item->product_id,
                'unit_id'                => $item->unit_id,
                'ordered_qty'            => $item->qty,
                'received_qty'           => null,
            ]);
        }

        return redirect()
            ->route('receivings.show', $receiving)
            ->with('success', 'Receiving created.');
    }

    public function show(Receiving $receiving)
    {
        $receiving->load([
            'purchaseOrder.supplier',
            'kitchen',
            'items.product',
            'items.unit',
            'items.purchaseOrderItem.complaints',
        ]);

        return view('receivings.show', compact('receiving'));
    }

    public function update(Request $request, Receiving $receiving)
    {
        if ($receiving->received_at) {
            return back()->with('error', 'This receiving has already been completed.');
        }

        $rows = $request->input('items', []);

        foreach ($receiving->items as $item) {
            $data = $rows[$item->id] ?? [];
            $item->update([
                'received_qty' => isset($data['received_qty']) && $data['received_qty'] !== '' ? (float) $data['received_qty'] : null,
                'notes'        => $data['notes'] ?? null,
            ]);
        }

        return back()->with('success', 'Quantities saved.');
    }

    public function receive(Receiving $receiving)
    {
        if ($receiving->received_at) {
            return back()->with('error', 'Already marked as received.');
        }

        $receiving->load('items.purchaseOrderItem');

        $anyNull = $receiving->items->contains(fn($i) => $i->received_qty === null);
        if ($anyNull) {
            return back()->with('error', 'Please fill in all received quantities before marking as received.');
        }

        $regionId = (int) Region::where('is_active', true)->value('id');
        $now = now();

        DB::transaction(function () use ($receiving, $regionId, $now) {
            $receiving->update(['received_at' => $now]);

            foreach ($receiving->items as $item) {
                StockMovement::create([
                    'product_id'   => $item->product_id,
                    'unit_id'      => $item->unit_id,
                    'qty'          => $item->received_qty,
                    'type'         => 'receiving_in',
                    'reference_id' => $receiving->id,
                    'moved_at'     => $now,
                ]);

                $purchasePrice = $item->purchaseOrderItem->purchase_price;

                DB::table('rab_items')
                    ->whereIn('rab_id', function ($q) use ($regionId) {
                        $q->select('id')
                            ->from('rabs')
                            ->where('region_id', $regionId)
                            ->whereDate('rab_date', '>=', now()->toDateString());
                    })
                    ->where('product_id', $item->product_id)
                    ->update(['purchase_price' => $purchasePrice]);
            }
        });

        return redirect()
            ->route('receivings.show', $receiving)
            ->with('success', 'Receiving completed. Stock updated and RAB prices synced.');
    }

    public function uploadProof(Request $request, ReceivingItem $receivingItem)
    {
        $request->validate([
            'proof_image' => ['required', 'image', 'max:4096'],
        ]);

        if ($receivingItem->proof_image) {
            Storage::disk('public')->delete($receivingItem->proof_image);
        }

        $path = $request->file('proof_image')->store(
            'receiving-proofs/' . $receivingItem->receiving_id,
            'public'
        );

        $receivingItem->update(['proof_image' => $path]);

        return back()->with('success', 'Proof image uploaded.');
    }

    public function deleteProof(ReceivingItem $receivingItem)
    {
        if ($receivingItem->proof_image) {
            Storage::disk('public')->delete($receivingItem->proof_image);
            $receivingItem->update(['proof_image' => null]);
        }

        return back()->with('success', 'Proof image removed.');
    }
}