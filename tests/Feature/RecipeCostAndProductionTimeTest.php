<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RecipeCostAndProductionTimeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Product $product;
    private Ingredient $flour;
    private Ingredient $butter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_recipe_test'],
            ['name' => 'Admin Recipe', 'email' => 'admin_rec@test.com', 'password' => Hash::make('pass123'), 'role' => 'admin', 'status' => 'active']
        );

        $category = Category::firstOrCreate(
            ['name' => 'Bakery Test Cat'],
            ['description' => 'Test category', 'status' => 'active']
        );

        $this->product = Product::firstOrCreate(
            ['name' => 'Artisan Baguette'],
            ['category_id' => $category->id, 'sku' => 'BAG-001', 'price' => 4.50, 'cost' => 1.50, 'stock' => 20, 'minimum_stock' => 5, 'status' => 'active']
        );

        $supplier = Supplier::firstOrCreate(
            ['name' => 'Flour & Dairy Supplier'],
            ['phone' => '+855 12 333 444', 'status' => 'active']
        );

        // Flour: $1.20 per kg
        $this->flour = Ingredient::create([
            'supplier_id' => $supplier->id,
            'name' => 'Bread Flour T65',
            'unit' => 'kg',
            'quantity' => 100.00,
            'cost' => 1.20,
            'minimum_quantity' => 20.00,
            'status' => 'active',
        ]);

        // Butter: $6.00 per kg
        $this->butter = Ingredient::create([
            'supplier_id' => $supplier->id,
            'name' => 'Unsalted Butter',
            'unit' => 'kg',
            'quantity' => 50.00,
            'cost' => 6.00,
            'minimum_quantity' => 10.00,
            'status' => 'active',
        ]);
    }

    public function test_recipe_cost_formula_calculates_accurately()
    {
        $recipe = Recipe::create([
            'product_id' => $this->product->id,
            'name' => 'Baguette Formula',
            'instructions' => 'Knead and bake at 220C for 25 mins',
            'production_time' => 45,
            'yield_quantity' => 4,
            'status' => 'active',
        ]);

        // 2 kg flour * $1.20 = $2.40
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $this->flour->id,
            'quantity' => 2.00,
            'unit' => 'kg',
        ]);

        // 0.5 kg butter * $6.00 = $3.00
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $this->butter->id,
            'quantity' => 0.50,
            'unit' => 'kg',
        ]);

        // Total Recipe Cost = $2.40 + $3.00 = $5.40
        $this->assertEquals(5.40, $recipe->total_cost);

        // Cost Per Unit = $5.40 / 4 yield = $1.35
        $this->assertEquals(1.35, $recipe->cost_per_unit);

        // Formatted production time: 45 mins
        $this->assertEquals('45 mins', $recipe->formatted_production_time);
    }

    public function test_production_time_formatting_for_hours_and_minutes()
    {
        $recipeShort = Recipe::create([
            'product_id' => $this->product->id,
            'name' => 'Short Recipe',
            'production_time' => 30,
            'yield_quantity' => 1,
            'status' => 'active',
        ]);
        $this->assertEquals('30 mins', $recipeShort->formatted_production_time);

        $recipeLong = Recipe::create([
            'product_id' => $this->product->id,
            'name' => 'Long Recipe',
            'production_time' => 90,
            'yield_quantity' => 1,
            'status' => 'active',
        ]);
        $this->assertEquals('1h 30m', $recipeLong->formatted_production_time);

        $recipeHoursOnly = Recipe::create([
            'product_id' => $this->product->id,
            'name' => 'Hours Only Recipe',
            'production_time' => 120,
            'yield_quantity' => 1,
            'status' => 'active',
        ]);
        $this->assertEquals('2h', $recipeHoursOnly->formatted_production_time);
    }

    public function test_can_create_and_update_recipe_with_production_time_and_yield()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.recipes.store'), [
            'product_id' => $this->product->id,
            'name' => 'New Brioche Recipe',
            'description' => 'Rich egg and butter bread',
            'instructions' => 'Bake at 180C for 35 mins',
            'production_time' => 60,
            'yield_quantity' => 6,
            'status' => 'active',
            'ingredients' => [
                ['ingredient_id' => $this->flour->id, 'quantity' => 1.5, 'unit' => 'kg'],
                ['ingredient_id' => $this->butter->id, 'quantity' => 0.5, 'unit' => 'kg'],
            ],
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('recipes', [
            'name' => 'New Brioche Recipe',
            'production_time' => 60,
            'yield_quantity' => 6,
        ]);

        $recipe = Recipe::where('name', 'New Brioche Recipe')->first();
        $this->assertNotNull($recipe);
        $this->assertEquals(2, $recipe->ingredients()->count());

        // Update
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.recipes.update', $recipe), [
            'product_id' => $this->product->id,
            'name' => 'Updated Brioche Formula',
            'description' => 'Updated description',
            'instructions' => 'Bake at 190C for 40 mins',
            'production_time' => 75,
            'yield_quantity' => 8,
            'status' => 'active',
        ]);

        $responseUpdate->assertStatus(302);
        $this->assertEquals(75, $recipe->fresh()->production_time);
        $this->assertEquals(8, $recipe->fresh()->yield_quantity);
    }

    public function test_recipes_index_displays_costs_and_production_time()
    {
        $recipe = Recipe::create([
            'product_id' => $this->product->id,
            'name' => 'Display Test Recipe',
            'instructions' => 'Bake at 200C',
            'production_time' => 50,
            'yield_quantity' => 5,
            'status' => 'active',
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $this->flour->id,
            'quantity' => 1.00,
            'unit' => 'kg',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.recipes'));
        $response->assertStatus(200);
        $response->assertSee('Display Test Recipe');
        $response->assertSee('50 mins');
        $response->assertSee('Cost Per Unit');
        $response->assertSee('Total Cost');
    }
}
