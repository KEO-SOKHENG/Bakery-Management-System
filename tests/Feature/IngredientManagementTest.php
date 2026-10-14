<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Product;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IngredientManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_ing_test'],
            ['name' => 'Admin Ing Test', 'email' => 'admining@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'admin', 'status' => 'active']
        );

        $this->supplier = Supplier::create([
            'name'           => 'Golden Grain Millers',
            'contact_person' => 'John Miller',
            'phone'          => '012345678',
            'email'          => 'supplier@grain.com',
            'status'         => 'active',
        ]);
    }

    public function test_can_list_ingredients_and_search_case_insensitively(): void
    {
        Ingredient::create([
            'supplier_id'      => $this->supplier->id,
            'name'             => 'Organic Whole Wheat Flour',
            'unit'             => 'kg',
            'quantity'         => 100.00,
            'cost'             => 1.50,
            'minimum_quantity' => 20.00,
            'status'           => 'active',
        ]);

        Ingredient::create([
            'supplier_id'      => $this->supplier->id,
            'name'             => 'Cane Sugar Extra Fine',
            'unit'             => 'kg',
            'quantity'         => 5.00,
            'cost'             => 0.90,
            'minimum_quantity' => 15.00,
            'status'           => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.ingredients'));
        $response->assertStatus(200);
        $this->assertCount(2, $response->viewData('ingredients'));

        // Test lowercase search for uppercase text
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.ingredients', ['search' => 'wheat flour']));
        $searchResponse->assertStatus(200);
        $this->assertCount(1, $searchResponse->viewData('ingredients'));
        $this->assertEquals('Organic Whole Wheat Flour', $searchResponse->viewData('ingredients')->first()->name);

        // Test filter by stock status (low stock)
        $filterResponse = $this->actingAs($this->admin)->get(route('admin.ingredients', ['stock_status' => 'low']));
        $filterResponse->assertStatus(200);
        $this->assertCount(1, $filterResponse->viewData('ingredients'));
        $this->assertEquals('Cane Sugar Extra Fine', $filterResponse->viewData('ingredients')->first()->name);
    }

    public function test_can_create_ingredient_with_initial_stock_movement(): void
    {
        $payload = [
            'supplier_id'      => $this->supplier->id,
            'name'             => 'Dry Yeast Premium',
            'unit'             => 'kg',
            'quantity'         => 25.50,
            'cost'             => 3.20,
            'minimum_quantity' => 5.00,
            'expiry_date'      => '2026-12-31',
            'status'           => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.ingredients.store'), $payload);
        $response->assertRedirect(route('admin.ingredients'));
        $response->assertSessionHas('success');

        $ingredient = Ingredient::where('name', 'Dry Yeast Premium')->first();
        $this->assertNotNull($ingredient);
        $this->assertEquals(25.50, (float) $ingredient->quantity);

        // Verify initial StockMovement was automatically created
        $movement = StockMovement::where('ingredient_id', $ingredient->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('in', $movement->type);
        $this->assertEquals(25.50, (float) $movement->quantity);
        $this->assertEquals('manual_adjustment', $movement->reference_type);
        $this->assertEquals($this->admin->id, $movement->user_id);
    }

    public function test_can_update_ingredient_and_logs_stock_adjustment(): void
    {
        $ingredient = Ingredient::create([
            'supplier_id'      => $this->supplier->id,
            'name'             => 'Pure Vanilla Extract',
            'unit'             => 'liter',
            'quantity'         => 10.00,
            'cost'             => 18.00,
            'minimum_quantity' => 2.00,
            'status'           => 'active',
        ]);

        $updatePayload = [
            'supplier_id'      => $this->supplier->id,
            'name'             => 'Pure Vanilla Extract (Bourbon)',
            'unit'             => 'liter',
            'quantity'         => 15.00, // Stock increased by 5
            'cost'             => 19.50,
            'minimum_quantity' => null, // Omitted or cleared - should fallback safely
            'status'           => 'active',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.ingredients.update', $ingredient), $updatePayload);
        $response->assertRedirect(route('admin.ingredients'));
        $response->assertSessionHas('success');

        $ingredient->refresh();
        $this->assertEquals('Pure Vanilla Extract (Bourbon)', $ingredient->name);
        $this->assertEquals(15.00, (float) $ingredient->quantity);
        $this->assertEquals(19.50, (float) $ingredient->cost);

        // Verify StockMovement for the difference (+5 liters)
        $movement = StockMovement::where('ingredient_id', $ingredient->id)->latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals('in', $movement->type);
        $this->assertEquals(5.00, (float) $movement->quantity);
        $this->assertEquals('manual_adjustment', $movement->reference_type);
    }

    public function test_cannot_delete_ingredient_used_in_recipe(): void
    {
        $category = Category::create(['name' => 'Pastries', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id,
            'name'        => 'Croissant',
            'sku'         => 'CROIS-01',
            'price'       => 2.50,
            'cost'        => 1.00,
            'stock'       => 10,
            'status'      => 'active',
        ]);

        $ingredient = Ingredient::create([
            'name'             => 'Unsalted French Butter',
            'unit'             => 'kg',
            'quantity'         => 40.00,
            'cost'             => 8.00,
            'minimum_quantity' => 10.00,
            'status'           => 'active',
        ]);

        $recipe = Recipe::create([
            'product_id' => $product->id,
            'name'       => 'Butter Croissant Recipe',
            'status'     => 'active',
        ]);

        $recipe->ingredients()->attach($ingredient->id, ['quantity' => 0.25, 'unit' => 'kg']);

        $response = $this->actingAs($this->admin)->delete(route('admin.ingredients.destroy', $ingredient));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id]);
    }

    public function test_can_delete_unused_ingredient(): void
    {
        $ingredient = Ingredient::create([
            'name'             => 'Temporary Test Spice',
            'unit'             => 'g',
            'quantity'         => 0.00,
            'cost'             => 0.50,
            'minimum_quantity' => 10.00,
            'status'           => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.ingredients.destroy', $ingredient));
        $response->assertRedirect(route('admin.ingredients'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('ingredients', ['id' => $ingredient->id]);
    }
}
