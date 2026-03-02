<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuRecipe;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class MenuRecipeController extends Controller
{
    public function index(Menu $menu)
    {
        $recipes = $menu->recipes()->with('product','unit')->get();
        return view('menu_recipes.index', compact('menu','recipes'));
    }

    public function create(Menu $menu)
    {
        $products = Product::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        return view('menu_recipes.create', compact('menu','products','units'));
    }

    public function store(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'product_id' => ['required','exists:products,id'],
            'unit_id' => ['required','exists:units,id'],
            'qty' => ['required','numeric','min:0'],
        ]);

        $validated['menu_id'] = $menu->id;
        MenuRecipe::create($validated);

        return redirect()->route('menus.recipes.index', $menu)->with('success', 'Recipe item added.');
    }

    public function edit(Menu $menu, MenuRecipe $recipe)
    {
        $products = Product::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('menu_recipes.edit', compact('menu', 'recipe', 'products', 'units'));
    }

    public function update(Request $request, Menu $menu, MenuRecipe $recipe)
    {
        $validated = $request->validate([
            'product_id' => ['required','exists:products,id'],
            'unit_id' => ['required','exists:units,id'],
            'qty' => ['required','numeric','min:0'],
        ]);

        $recipe->update($validated);

        return redirect()
            ->route('menus.recipes.index', $menu)
            ->with('success', 'Ingredient updated.');
    }

    public function destroy(Menu $menu, MenuRecipe $recipe)
    {
        $recipe->delete();
        return redirect()->route('menus.recipes.index', $menu)->with('success', 'Recipe item deleted.');
    }
}
