<?php

namespace App\Http\Controllers\Panel\Pos;

use App\Http\Controllers\Controller;
use App\Models\MenuRecipe;
use App\Models\PosMenuItem;
use App\Models\RecipeIngredient;
use App\Services\Pos\MenuEngineeringService;
use Illuminate\Http\Request;

class MenuEngineeringController extends Controller
{
    public function __construct(protected MenuEngineeringService $svc) {}

    public function matrix()
    {
        $property = app('current_property');
        $data = $this->svc->classifyMenuItems($property);

        return view('panel.pos.menu-matrix', $data);
    }

    public function recipes()
    {
        $propertyId = app('current_property')->id;
        $recipes = MenuRecipe::where('property_id', $propertyId)
            ->with(['ingredients', 'menuItem'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $menuItems = PosMenuItem::where('property_id', $propertyId)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('panel.pos.menu-recipes', compact('recipes', 'menuItems'));
    }

    public function storeRecipe(Request $request)
    {
        $data = $request->validate([
            'menu_item_id' => 'nullable|exists:pos_menu_items,id',
            'name' => 'required|string|max:150',
            'selling_price' => 'required|numeric|min:0',
            'portion_size' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
        ]);

        MenuRecipe::create($data + ['property_id' => app('current_property')->id]);
        return back()->with('success', 'Resep berhasil ditambahkan.');
    }

    public function updateRecipe(Request $request, $id)
    {
        $recipe = MenuRecipe::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'menu_item_id' => 'nullable|exists:pos_menu_items,id',
            'name' => 'required|string|max:150',
            'selling_price' => 'required|numeric|min:0',
            'portion_size' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
        ]);

        $recipe->update($data);
        return back()->with('success', 'Resep berhasil diperbarui.');
    }

    public function destroyRecipe($id)
    {
        $recipe = MenuRecipe::where('property_id', app('current_property')->id)->findOrFail($id);
        $recipe->ingredients()->delete();
        $recipe->delete();
        return back()->with('success', 'Resep berhasil dihapus.');
    }

    public function storeIngredient(Request $request, $recipeId)
    {
        $recipe = MenuRecipe::where('property_id', app('current_property')->id)->findOrFail($recipeId);

        $data = $request->validate([
            'ingredient_name' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0.0001',
            'unit' => 'required|string|max:20',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        $data['total_cost'] = $data['quantity'] * $data['cost_per_unit'];
        $data['menu_recipe_id'] = $recipe->id;
        $data['property_id'] = app('current_property')->id;

        RecipeIngredient::create($data);
        return back()->with('success', 'Bahan berhasil ditambahkan.');
    }

    public function destroyIngredient($id)
    {
        $ingredient = RecipeIngredient::where('property_id', app('current_property')->id)->findOrFail($id);
        $ingredient->delete();
        return back()->with('success', 'Bahan berhasil dihapus.');
    }

    public function recipeDetail($id)
    {
        $recipe = MenuRecipe::where('property_id', app('current_property')->id)
            ->with(['ingredients', 'menuItem', 'performances' => fn($q) => $q->orderByDesc('period_start')->limit(3)])
            ->findOrFail($id);

        $recommendedPrice = $recipe->food_cost > 0
            ? round($recipe->food_cost / 0.30, 0) // 30% food cost target
            : 0;

        return view('panel.pos.menu-recipe', compact('recipe', 'recommendedPrice'));
    }

    public function calculate()
    {
        $this->svc->calculatePerformance(app('current_property'));
        return back()->with('success', 'Data performa menu berhasil dihitung ulang.');
    }
}
