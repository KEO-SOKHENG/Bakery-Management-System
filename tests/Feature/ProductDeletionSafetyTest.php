<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Production;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductDeletionSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_safety_test'],
            [
                'name'     => 'Admin Safety',
                'email'    => 'admin_safety@test.com',
                'password' => Hash::make('pass123'),
                'role'     => 'admin',
                'status'   => 'active',
            ]
        );

        $this->category = Category::create([
            'name'   => 'Artisan Breads',
            'status' => 'active',
        ]);
    }

    public function test_cannot_delete_product_with_order_items(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Sourdough Loaf',
            'sku'         => 'SOUR-01',
            'price'       => 5.00,
            'cost'        => 2.00,
            'stock'       => 20,
            'status'      => 'active',
        ]);

        $order = Order::create([
            'user_id'        => $this->admin->id,
            'order_number'   => 'ORD-SAFETY-01',
            'customer_name'  => 'Walk-in Customer',
            'subtotal'       => 5.00,
            'total'          => 5.50,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status'   => 'completed',
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => 5.00,
            'subtotal'   => 5.00,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_cannot_delete_product_with_recipe(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Baguette Tradition',
            'sku'         => 'BAG-01',
            'price'       => 2.00,
            'cost'        => 0.80,
            'stock'       => 15,
            'status'      => 'active',
        ]);

        Recipe::create([
            'product_id' => $product->id,
            'name'       => 'Baguette Recipe',
            'status'     => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_cannot_delete_recipe_with_production_records(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Brioche Bun',
            'sku'         => 'BRIO-01',
            'price'       => 1.50,
            'cost'        => 0.60,
            'stock'       => 30,
            'status'      => 'active',
        ]);

        $recipe = Recipe::create([
            'product_id' => $product->id,
            'name'       => 'Brioche Bun Recipe',
            'status'     => 'active',
        ]);

        Production::create([
            'recipe_id'       => $recipe->id,
            'product_id'      => $product->id,
            'quantity'        => 50,
            'production_date' => now(),
            'status'          => 'completed',
            'user_id'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.recipes.destroy', $recipe));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id]);
    }

    public function test_can_delete_unused_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name'        => 'Discontinued Pastry',
            'sku'         => 'DISC-01',
            'price'       => 3.00,
            'cost'        => 1.00,
            'stock'       => 0,
            'status'      => 'inactive',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
