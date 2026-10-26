<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::with(['product.category', 'ingredients', 'recipeIngredients.ingredient']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $like = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search, $like) {
                      $pq->where('name', $like, "%{$search}%");
                  });
            });
        }

        if ($request->filled('product_id') && $request->product_id !== 'all') {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $recipes = $query->orderBy('id', 'desc')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $ingredients = Ingredient::where('status', 'active')->orderBy('name', 'asc')->get();

        $allRecipes = Recipe::with('recipeIngredients.ingredient')->get();
        $avgTime = $allRecipes->where('production_time', '>', 0)->avg('production_time');
        $stats = [
            'total'           => $allRecipes->count(),
            'active'          => $allRecipes->where('status', 'active')->count(),
            'inactive'        => $allRecipes->where('status', 'inactive')->count(),
            'avg_time'        => (int) round($avgTime ?: 35),
            'linked_products' => $allRecipes->pluck('product_id')->unique()->filter()->count(),
            'avg_unit_cost'   => (float) ($allRecipes->isNotEmpty() ? $allRecipes->avg('cost_per_unit') : 0.0),
        ];

        return view('admin.recipes', compact('recipes', 'products', 'ingredients', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'production_time' => 'nullable|integer|min:1',
            'yield_quantity' => 'nullable|integer|min:1',
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
            'production_time' => $validated['production_time'] ?? null,
            'yield_quantity' => $validated['yield_quantity'] ?? 1,
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
            'production_time' => 'nullable|integer|min:1',
            'yield_quantity' => 'nullable|integer|min:1',
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
            'production_time' => $validated['production_time'] ?? null,
            'yield_quantity' => $validated['yield_quantity'] ?? 1,
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
        if ($recipe->productions()->exists()) {
            return back()->with('error', "Cannot delete recipe '{$recipe->name}': Historical production batches reference this recipe.");
        }

        $recipe->delete();
        return redirect()->route('admin.recipes')->with('success', 'Recipe deleted successfully!');
    }
}
