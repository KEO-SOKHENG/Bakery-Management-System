<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Production;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductShelfLifeAndExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_expiry_test'],
            [
                'name' => 'Admin Expiry',
                'email' => 'admin_expiry@test.com',
                'password' => Hash::make('pass123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $this->category = Category::firstOrCreate(
            ['name' => 'Pastries'],
            ['description' => 'Delicious pastries', 'status' => 'active']
        );
    }

    public function test_product_has_shelf_life_and_expiry_date_attributes()
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Croissant',
            'sku' => 'CRO-001',
            'price' => 3.50,
            'cost' => 1.20,
            'stock' => 15,
            'minimum_stock' => 5,
            'shelf_life' => 7,
            'expiry_date' => Carbon::now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        $this->assertEquals(7, $product->shelf_life);
        $this->assertEquals(Carbon::now()->addDays(7)->toDateString(), $product->expiry_date->toDateString());
    }

    public function test_product_expiry_helper_methods()
    {
        // Expired product
        $expiredProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Old Eclair',
            'sku' => 'ECL-OLD',
            'price' => 2.50,
            'cost' => 1.00,
            'stock' => 5,
            'shelf_life' => 3,
            'expiry_date' => Carbon::now()->subDay()->toDateString(),
            'status' => 'active',
        ]);

        $this->assertTrue($expiredProduct->isExpired());
        $this->assertFalse($expiredProduct->isExpiringSoon(3));

        // Expiring soon product (2 days left)
        $expiringSoonProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Fresh Eclair',
            'sku' => 'ECL-SOON',
            'price' => 2.50,
            'cost' => 1.00,
            'stock' => 10,
            'shelf_life' => 3,
            'expiry_date' => Carbon::now()->addDays(2)->toDateString(),
            'status' => 'active',
        ]);

        $this->assertFalse($expiringSoonProduct->isExpired());
        $this->assertTrue($expiringSoonProduct->isExpiringSoon(3));

        // Fresh product (10 days left)
        $freshProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Durable Cookie',
            'sku' => 'COO-FRESH',
            'price' => 1.50,
            'cost' => 0.50,
            'stock' => 25,
            'shelf_life' => 14,
            'expiry_date' => Carbon::now()->addDays(10)->toDateString(),
            'status' => 'active',
        ]);

        $this->assertFalse($freshProduct->isExpired());
        $this->assertFalse($freshProduct->isExpiringSoon(3));
    }

    public function test_admin_can_create_product_with_shelf_life_and_expiry()
    {
        $expiry = Carbon::now()->addDays(5)->toDateString();

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Croissant Supreme',
            'category_id' => $this->category->id,
            'price' => 3.50,
            'cost' => 1.20,
            'stock' => 20,
            'minimum_stock' => 5,
            'shelf_life' => 5,
            'expiry_date' => $expiry,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.products'));

        $product = Product::where('name', 'Croissant Supreme')->first();
        $this->assertNotNull($product);
        $this->assertEquals(5, $product->shelf_life);
        $this->assertEquals($expiry, $product->expiry_date->toDateString());
    }

    public function test_completing_production_batch_auto_calculates_expiry_date()
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Batch Bread',
            'sku' => 'BAT-001',
            'price' => 2.00,
            'cost' => 0.80,
            'stock' => 10,
            'shelf_life' => 4,
            'expiry_date' => null,
            'status' => 'active',
        ]);

        $production = Production::create([
            'batch_number' => 'BATCH-EXP-001',
            'product_id' => $product->id,
            'quantity' => 10,
            'production_date' => now(),
            'status' => 'in_progress',
            'user_id' => $this->admin->id,
            'baker_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.production.updateStatus', $production), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();

        $product->refresh();
        $expectedExpiry = Carbon::now()->addDays(4)->toDateString();
        $this->assertEquals($expectedExpiry, $product->expiry_date?->toDateString());
    }
}
