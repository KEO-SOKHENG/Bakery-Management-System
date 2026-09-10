<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with(['product', 'ingredients'])->orderBy('id', 'desc')->get();
        $products = Product::where('status', 'active')->get();
        $ingredients = Ingredient::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('admin.recipes', compact('recipes', 'products', 'ingredients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
            'ingredients.*.unit' => 'nullable|string',
        ]);

        $recipe = Recipe::create([
            'product_id' => $validated['product_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        if (!empty($validated['ingredients'])) {
            foreach ($validated['ingredients'] as $ingData) {
                $recipe->ingredients()->attach($ingData['ingredient_id'], [
                    'quantity' => $ingData['quantity'],
                    'unit' => $ingData['unit'] ?? 'kg',
                ]);
            }
        }

        return redirect()->route('admin.recipes')->with('success', 'Recipe created successfully!');
    }

    public function update(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
            'ingredients.*.unit' => 'nullable|string',
        ]);

        $recipe->update([
            'product_id' => $validated['product_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'status' => $validated['status'],
        ]);

        $syncData = [];
        if (!empty($validated['ingredients'])) {
            foreach ($validated['ingredients'] as $ingData) {
                $syncData[$ingData['ingredient_id']] = [
                    'quantity' => $ingData['quantity'],
                    'unit' => $ingData['unit'] ?? 'kg',
                ];
            }
        }
        $recipe->ingredients()->sync($syncData);

        return redirect()->route('admin.recipes')->with('success', 'Recipe updated successfully!');
    }

    public function destroy(Recipe $recipe)
    {
        $recipe->delete();
        return redirect()->route('admin.recipes')->with('success', 'Recipe deleted successfully!');
    }
}
