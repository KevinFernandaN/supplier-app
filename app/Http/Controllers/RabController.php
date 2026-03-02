<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RabController extends Controller
{
    private function currentRegionId(): int
    {
        return (int) Region::where('is_active', true)->orderBy('id')->value('id');
    }

    public function index()
    {
        $regionId = $this->currentRegionId();

        $rabs = Rab::with('menu')
            ->where('region_id', $regionId)
            ->orderByDesc('rab_date')
            ->orderBy('id')
            ->paginate(20);

        return view('rabs.index', compact('rabs'));
    }

    public function create()
    {
        $menus = Menu::orderBy('name')->get();
        return view('rabs.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $regionId = $this->currentRegionId();

        $data = $request->validate([
            'menu_id'       => 'required|exists:menus,id',
            'rab_date'      => 'required|date',
            'selling_price' => 'required|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        $data['region_id'] = $regionId;

        $rab = Rab::create($data);

        // Auto-populate items from menu recipes with LPP + unit conversion
        $this->populateItemsFromRecipes($rab);

        return redirect()->route('rabs.show', $rab)
            ->with('success', 'RAB created. Review and adjust ingredient quantities/prices below.');
    }

    public function show(Rab $rab)
    {
        $rab->load(['menu', 'items.product', 'items.unit']);

        $totalCogs = $rab->items->sum(function ($item) {
            return (float)$item->qty * (float)$item->purchase_price;
        });

        $margin    = (float)$rab->selling_price - $totalCogs;
        $marginPct = (float)$rab->selling_price > 0
            ? ($margin / (float)$rab->selling_price) * 100
            : 0;

        return view('rabs.show', compact('rab', 'totalCogs', 'margin', 'marginPct'));
    }

    public function edit(Rab $rab)
    {
        $menus = Menu::orderBy('name')->get();
        return view('rabs.edit', compact('rab', 'menus'));
    }

    public function update(Request $request, Rab $rab)
    {
        $data = $request->validate([
            'menu_id'       => 'required|exists:menus,id',
            'rab_date'      => 'required|date',
            'selling_price' => 'required|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        $rab->update($data);

        return redirect()->route('rabs.show', $rab)
            ->with('success', 'RAB updated.');
    }

    public function destroy(Rab $rab)
    {
        $rab->delete(); // cascades to rab_items
        return redirect()->route('rabs.index')
            ->with('success', 'RAB deleted.');
    }

    // -------------------------------------------------------------------------
    // Private: auto-populate rab_items from menu_recipes with LPP lookup
    // -------------------------------------------------------------------------
    private function populateItemsFromRecipes(Rab $rab): void
    {
        $recipes = DB::table('menu_recipes')
            ->where('menu_id', $rab->menu_id)
            ->get();

        if ($recipes->isEmpty()) {
            return;
        }

        // Load unit conversions into a map [from_unit_id_to_unit_id => multiplier]
        $conversions = DB::table('unit_conversions')
            ->get()
            ->keyBy(function ($uc) {
                return $uc->from_unit_id . '_' . $uc->to_unit_id;
            });

        foreach ($recipes as $recipe) {
            // Find LPP for this product: last purchase price at or before rab_date
            $lppRow = DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->where('po.region_id', $rab->region_id)
                ->where('poi.product_id', $recipe->product_id)
                ->where('po.order_date', '<=', $rab->rab_date)
                ->orderByDesc('po.order_date')
                ->orderByDesc('poi.id')
                ->select('poi.purchase_price', 'poi.unit_id')
                ->first();

            $purchasePrice = 0;

            if ($lppRow) {
                if ($lppRow->unit_id == $recipe->unit_id) {
                    // Same unit — use price directly
                    $purchasePrice = (float)$lppRow->purchase_price;
                } else {
                    // Different units — convert price to recipe unit
                    // price_per_recipe_unit = price_per_purchase_unit × multiplier(recipe_unit → purchase_unit)
                    $key = $recipe->unit_id . '_' . $lppRow->unit_id;
                    if (isset($conversions[$key])) {
                        $purchasePrice = round((float)$lppRow->purchase_price * (float)$conversions[$key]->multiplier, 4);
                    }
                    // If no conversion defined: purchase_price stays 0 (user can fill it in)
                }
            }

            RabItem::create([
                'rab_id'         => $rab->id,
                'product_id'     => $recipe->product_id,
                'unit_id'        => $recipe->unit_id,
                'qty'            => $recipe->qty,
                'purchase_price' => $purchasePrice,
            ]);
        }
    }
}
