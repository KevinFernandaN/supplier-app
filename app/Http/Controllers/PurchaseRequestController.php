<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $prs = PurchaseRequest::with(['kitchen', 'menu'])
            ->latest()
            ->paginate(15);

        return view('purchase_requests.index', compact('prs'));
    }

    public function create()
    {
        $kitchens = Kitchen::where('is_active', true)->orderBy('name')->get();
        $menus    = Menu::orderBy('name')->get();

        return view('purchase_requests.create', compact('kitchens', 'menus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kitchen_id'    => ['required', 'exists:kitchens,id'],
            'menu_id'       => ['required', 'exists:menus,id'],
            'total_portion' => ['required', 'numeric', 'min:1'],
            'notes'         => ['nullable', 'string'],
        ]);

        $menu = Menu::with('recipes.product', 'recipes.unit')->findOrFail($validated['menu_id']);

        if ($menu->recipes->isEmpty()) {
            return back()->withInput()
                ->withErrors(['menu_id' => 'This menu has no recipe ingredients defined yet.']);
        }

        $pr = PurchaseRequest::create($validated);

        foreach ($menu->recipes as $recipe) {
            PurchaseRequestItem::create([
                'purchase_request_id' => $pr->id,
                'product_id'          => $recipe->product_id,
                'unit_id'             => $recipe->unit_id,
                'required_qty'        => $recipe->qty * $validated['total_portion'],
                'buffer_pct'          => 0,
            ]);
        }

        return redirect()->route('purchase-requests.show', $pr)
            ->with('success', 'Purchase Request created. Review and adjust buffers below.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['kitchen', 'menu', 'items.product', 'items.unit']);

        return view('purchase_requests.show', compact('purchaseRequest'));
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status === 'ordered') {
            return back()->withErrors(['error' => 'Cannot delete a PR that has already been ordered.']);
        }

        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')
            ->with('success', 'Purchase Request deleted.');
    }

    public function confirm(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'draft') {
            return back()->with('error', 'Only draft PRs can be confirmed.');
        }

        $purchaseRequest->update(['status' => 'confirmed']);

        return back()->with('success', 'Purchase Request confirmed and ready for ordering.');
    }

    public function reopen(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'confirmed') {
            return back()->with('error' , 'Only confirmed PRs can be re-opened to draft.');
        }

        $purchaseRequest->update(['status' => 'draft']);

        return back()->with('success', 'Purchase Request re-opened as draft.');
    }
}
