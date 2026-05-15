<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\RabDay;
use App\Models\RabDayMenu;
use App\Models\RabDayMenuItem;
use App\Models\RabPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RabDayMenuController extends Controller
{
    public function create(RabPeriod $rabPeriod, RabDay $day)
    {
        $day->load('menus.menu');
        $menus      = Menu::orderBy('name')->get();
        $categories = RabDayMenu::$categories;

        return view('rab-day-menus.create', compact('rabPeriod', 'day', 'menus', 'categories'));
    }

    public function store(Request $request, RabPeriod $rabPeriod, RabDay $day)
    {
        $data = $request->validate([
            'menu_id'          => 'required|exists:menus,id',
            'category'         => 'required|in:' . implode(',', RabDayMenu::$categories),
            'is_replacement'   => 'boolean',
            'replaces_id'      => 'nullable|exists:rab_day_menus,id',
            'allergy_pk_count' => 'nullable|integer|min:0',
            'allergy_pb_count' => 'nullable|integer|min:0',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $data['rab_day_id']     = $day->id;
        $data['is_replacement'] = $request->boolean('is_replacement');

        // Clear allergy counts if not a replacement
        if (!$data['is_replacement']) {
            $data['replaces_id']      = null;
            $data['allergy_pk_count'] = null;
            $data['allergy_pb_count'] = null;
        }

        $dayMenu = RabDayMenu::create($data);

        $this->populateItemsFromRecipes($dayMenu, $rabPeriod, $day);

        return redirect()->route('rab-periods.days.show', [$rabPeriod, $day])
            ->with('success', 'Menu added. Review and adjust ingredients below.');
    }

    public function edit(RabPeriod $rabPeriod, RabDay $day, RabDayMenu $menu)
    {
        $day->load('menus.menu');
        $menus      = Menu::orderBy('name')->get();
        $categories = RabDayMenu::$categories;

        return view('rab-day-menus.edit', compact('rabPeriod', 'day', 'menu', 'menus', 'categories'));
    }

    public function update(Request $request, RabPeriod $rabPeriod, RabDay $day, RabDayMenu $menu)
    {
        $data = $request->validate([
            'menu_id'          => 'required|exists:menus,id',
            'category'         => 'required|in:' . implode(',', RabDayMenu::$categories),
            'is_replacement'   => 'boolean',
            'replaces_id'      => 'nullable|exists:rab_day_menus,id',
            'allergy_pk_count' => 'nullable|integer|min:0',
            'allergy_pb_count' => 'nullable|integer|min:0',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $data['is_replacement'] = $request->boolean('is_replacement');

        if (!$data['is_replacement']) {
            $data['replaces_id']      = null;
            $data['allergy_pk_count'] = null;
            $data['allergy_pb_count'] = null;
        }

        $menu->update($data);

        return redirect()->route('rab-periods.days.show', [$rabPeriod, $day])
            ->with('success', 'Menu updated.');
    }

    public function destroy(RabPeriod $rabPeriod, RabDay $day, RabDayMenu $menu)
    {
        $menu->delete();
        return redirect()->route('rab-periods.days.show', [$rabPeriod, $day])
            ->with('success', 'Menu removed.');
    }

    // -------------------------------------------------------------------------
    // Auto-populate items from menu_recipes using LPP + unit conversion
    // -------------------------------------------------------------------------
    private function populateItemsFromRecipes(RabDayMenu $dayMenu, RabPeriod $period, RabDay $day): void
    {
        $recipes = DB::table('menu_recipes')->where('menu_id', $dayMenu->menu_id)->get();
        if ($recipes->isEmpty()) {
            return;
        }

        $conversions = DB::table('unit_conversions')
            ->get()
            ->keyBy(fn($uc) => $uc->from_unit_id . '_' . $uc->to_unit_id);

        foreach ($recipes as $recipe) {
            $lppRow = DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->where('po.region_id', $period->region_id)
                ->where('poi.product_id', $recipe->product_id)
                ->where('po.order_date', '<=', $day->day_date)
                ->orderByDesc('po.order_date')
                ->orderByDesc('poi.id')
                ->select('poi.purchase_price', 'poi.unit_id', 'po.supplier_id')
                ->first();

            $purchasePrice = 0;
            $supplierId    = null;

            if ($lppRow) {
                $supplierId = $lppRow->supplier_id;

                if ($lppRow->unit_id == $recipe->unit_id) {
                    $purchasePrice = (float) $lppRow->purchase_price;
                } else {
                    $key = $recipe->unit_id . '_' . $lppRow->unit_id;
                    if (isset($conversions[$key])) {
                        $purchasePrice = round(
                            (float) $lppRow->purchase_price * (float) $conversions[$key]->multiplier,
                            4
                        );
                    }
                }
            }

            RabDayMenuItem::create([
                'rab_day_menu_id' => $dayMenu->id,
                'product_id'      => $recipe->product_id,
                'unit_id'         => $recipe->unit_id,
                'supplier_id'     => $supplierId,
                'pk_gramasi'      => $recipe->qty,
                'pb_gramasi'      => $recipe->qty,
                'purchase_price'  => $purchasePrice,
            ]);
        }
    }
}
