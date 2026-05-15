<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RabDay;
use App\Models\RabDayMenu;
use App\Models\RabDayMenuItem;
use App\Models\RabPeriod;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RabDayMenuItemController extends Controller
{
    public function create(RabPeriod $rabPeriod, RabDay $day, RabDayMenu $menu)
    {
        $menu->load('menu');
        $products  = Product::orderBy('name')->get();
        $units     = Unit::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('rab-day-menu-items.create', compact(
            'rabPeriod', 'day', 'menu', 'products', 'units', 'suppliers'
        ));
    }

    public function store(Request $request, RabPeriod $rabPeriod, RabDay $day, RabDayMenu $menu)
    {
        $data = $request->validate([
            'product_id'     => 'required|exists:products,id',
            'unit_id'        => 'required|exists:units,id',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'pk_gramasi'     => 'required|numeric|min:0',
            'pb_gramasi'     => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        $data['rab_day_menu_id'] = $menu->id;
        RabDayMenuItem::create($data);

        return redirect()->route('rab-periods.days.show', [$rabPeriod, $day])
            ->with('success', 'Ingredient added.');
    }

    public function edit(RabPeriod $rabPeriod, RabDay $day, RabDayMenu $menu, RabDayMenuItem $item)
    {
        $menu->load('menu');
        $item->load(['product', 'unit']);
        $suppliers = Supplier::orderBy('name')->get();

        $conversions = DB::table('unit_conversions')
            ->get()
            ->keyBy(function ($uc) { return $uc->from_unit_id . '_' . $uc->to_unit_id; });

        $supplierPrices = [];
        foreach ($suppliers as $supplier) {
            $lppRow = DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->where('po.supplier_id', $supplier->id)
                ->where('poi.product_id', $item->product_id)
                ->where('po.order_date', '<=', $day->day_date)
                ->orderByDesc('po.order_date')
                ->orderByDesc('poi.id')
                ->select('poi.purchase_price', 'poi.unit_id')
                ->first();

            if ($lppRow) {
                if ($lppRow->unit_id == $item->unit_id) {
                    $supplierPrices[$supplier->id] = round((float) $lppRow->purchase_price, 4);
                } else {
                    $key = $item->unit_id . '_' . $lppRow->unit_id;
                    if (isset($conversions[$key])) {
                        $supplierPrices[$supplier->id] = round(
                            (float) $lppRow->purchase_price * (float) $conversions[$key]->multiplier,
                            4
                        );
                    }
                }
            }
        }

        return view('rab-day-menu-items.edit', compact(
            'rabPeriod', 'day', 'menu', 'item', 'suppliers', 'supplierPrices'
        ));
    }

    public function update(Request $request, RabPeriod $rabPeriod, RabDay $day, RabDayMenu $menu, RabDayMenuItem $item)
    {
        $data = $request->validate([
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'pk_gramasi'     => 'required|numeric|min:0',
            'pb_gramasi'     => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        $item->update($data);

        return redirect()->route('rab-periods.days.show', [$rabPeriod, $day])
            ->with('success', 'Ingredient updated.');
    }

    public function destroy(RabPeriod $rabPeriod, RabDay $day, RabDayMenu $menu, RabDayMenuItem $item)
    {
        $item->delete();
        return redirect()->route('rab-periods.days.show', [$rabPeriod, $day])
            ->with('success', 'Ingredient removed.');
    }
}
