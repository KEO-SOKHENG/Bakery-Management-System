<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategorySafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_cat_test'],
            [
                'name' => 'Admin Cat',
                'email' => 'admin_cat@test.com',
                'password' => Hash::make('pass123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }

    public function test_empty_category_can_be_deleted()
    {
        $category = Category::create([
            'name' => 'Empty Breads',
            'description' => 'No products in this category',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_products_cannot_be_deleted()
    {
        $category = Category::create([
            'name' => 'Cakes & Pies',
            'description' => 'Category with products',
            'status' => 'active',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Strawberry Tart',
            'sku' => 'TART-001',
            'price' => 4.50,
            'cost' => 1.50,
            'stock' => 10,
            'minimum_stock' => 2,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
