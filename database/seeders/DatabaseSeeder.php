<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\Production;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users (password: pass123 for Playwright tests & local dev)
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'System Admin',
                'email' => 'admin@bakery.com',
                'password' => Hash::make('pass123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['username' => 'manager'],
            [
                'name' => 'Bakery Manager',
                'email' => 'manager@bakery.com',
                'password' => Hash::make('pass123'),
                'role' => 'manager',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['username' => 'cashier'],
            [
                'name' => 'Store Cashier',
                'email' => 'cashier@bakery.com',
                'password' => Hash::make('pass123'),
                'role' => 'cashier',
                'status' => 'active',
            ]
        );

        $baker1 = User::updateOrCreate(
            ['username' => 'baker'],
            [
                'name' => 'Pierre Dubois (Head Baker)',
                'email' => 'baker@bakery.com',
                'password' => Hash::make('pass123'),
                'role' => 'baker',
                'status' => 'active',
            ]
        );

        $baker2 = User::updateOrCreate(
            ['username' => 'baker2'],
            [
                'name' => 'Alice Smith (Pastry Baker)',
                'email' => 'baker2@bakery.com',
                'password' => Hash::make('pass123'),
                'role' => 'baker',
                'status' => 'active',
            ]
        );

        // 2. Seed Categories (using firstOrCreate to prevent duplicates)
        $cakes = Category::firstOrCreate(['name' => 'Cakes'], ['description' => 'Freshly baked cakes and pastries', 'status' => 'active']);
        $bread = Category::firstOrCreate(['name' => 'Bread'], ['description' => 'Artisanal breads and loaves', 'status' => 'active']);
        $pastries = Category::firstOrCreate(['name' => 'Pastries'], ['description' => 'Flaky French pastries', 'status' => 'active']);
        $cookies = Category::firstOrCreate(['name' => 'Cookies'], ['description' => 'Sweet baked cookies', 'status' => 'active']);
        $drinks = Category::firstOrCreate(['name' => 'Drinks'], ['description' => 'Coffee, tea and beverage options', 'status' => 'active']);

        // 3. Seed Suppliers
        $sup1 = Supplier::create([
            'name' => 'ABC Food Supplier',
            'phone' => '+855 12 345 678',
            'email' => 'contact@abcfood.com',
            'address' => 'Phnom Penh Industrial Zone, Cambodia',
            'status' => 'active',
        ]);

        $sup2 = Supplier::create([
            'name' => 'Bakery Ingredients Co.',
            'phone' => '+855 98 765 432',
            'email' => 'orders@bakeryingredients.com',
            'address' => 'St. 271, Phnom Penh, Cambodia',
            'status' => 'active',
        ]);

        // 4. Seed Ingredients
        $flour = Ingredient::create([
            'supplier_id' => $sup1->id,
            'name' => 'Wheat Flour',
            'unit' => 'kg',
            'quantity' => 100.00,
            'cost' => 1.20,
            'minimum_quantity' => 20.00,
            'status' => 'active',
        ]);

        $sugar = Ingredient::create([
            'supplier_id' => $sup1->id,
            'name' => 'Refined Sugar',
            'unit' => 'kg',
            'quantity' => 50.00,
            'cost' => 0.90,
            'minimum_quantity' => 15.00,
            'status' => 'active',
        ]);

        $eggs = Ingredient::create([
            'supplier_id' => $sup2->id,
            'name' => 'Fresh Eggs',
            'unit' => 'pcs',
            'quantity' => 300.00,
            'cost' => 0.15,
            'minimum_quantity' => 50.00,
            'status' => 'active',
        ]);

        $milk = Ingredient::create([
            'supplier_id' => $sup2->id,
            'name' => 'Whole Milk',
            'unit' => 'liter',
            'quantity' => 40.00,
            'cost' => 1.50,
            'minimum_quantity' => 10.00,
            'status' => 'active',
        ]);

        $butter = Ingredient::create([
            'supplier_id' => $sup2->id,
            'name' => 'Unsalted Butter',
            'unit' => 'kg',
            'quantity' => 25.00,
            'cost' => 4.50,
            'minimum_quantity' => 5.00,
            'status' => 'active',
        ]);

        $chocolate = Ingredient::create([
            'supplier_id' => $sup1->id,
            'name' => 'Dark Chocolate Chips',
            'unit' => 'kg',
            'quantity' => 15.00,
            'cost' => 6.00,
            'minimum_quantity' => 3.00,
            'status' => 'active',
        ]);

        // 5. Seed Products
        $p1 = Product::create([
            'category_id' => $cakes->id,
            'name' => 'Chocolate Cake',
            'sku' => 'CAKE-CHOC-01',
            'description' => 'Rich chocolate cake with dark chocolate ganache',
            'price' => 15.00,
            'cost' => 7.50,
            'stock' => 20,
            'minimum_stock' => 5,
            'status' => 'active',
            'image_emoji' => '🎂',
        ]);

        $p2 = Product::create([
            'category_id' => $cakes->id,
            'name' => 'Vanilla Cake',
            'sku' => 'CAKE-VAN-01',
            'description' => 'Classic vanilla sponge cake with buttercream',
            'price' => 12.00,
            'cost' => 6.00,
            'stock' => 15,
            'minimum_stock' => 5,
            'status' => 'active',
            'image_emoji' => '🍰',
        ]);

        $p3 = Product::create([
            'category_id' => $pastries->id,
            'name' => 'Butter Croissant',
            'sku' => 'PAST-CROI-01',
            'description' => 'Flaky French butter croissant',
            'price' => 2.50,
            'cost' => 1.00,
            'stock' => 40,
            'minimum_stock' => 10,
            'status' => 'active',
            'image_emoji' => '🥐',
        ]);

        $p4 = Product::create([
            'category_id' => $cookies->id,
            'name' => 'Chocolate Donut',
            'sku' => 'DON-CHOC-01',
            'description' => 'Glazed chocolate donut',
            'price' => 1.80,
            'cost' => 0.70,
            'stock' => 30,
            'minimum_stock' => 8,
            'status' => 'active',
            'image_emoji' => '🍩',
        ]);

        $p5 = Product::create([
            'category_id' => $cookies->id,
            'name' => 'Choco Chip Cookies',
            'sku' => 'COOK-CHOC-01',
            'description' => 'Freshly baked chocolate chip cookie',
            'price' => 1.50,
            'cost' => 0.50,
            'stock' => 50,
            'minimum_stock' => 15,
            'status' => 'active',
            'image_emoji' => '🍪',
        ]);

        // 6. Seed Recipes & Recipe Ingredients
        $r1 = Recipe::create([
            'product_id' => $p1->id,
            'name' => 'Chocolate Cake Recipe',
            'description' => 'Standard recipe for 1 Chocolate Cake',
            'instructions' => 'Mix flour, sugar, eggs, milk, and melted chocolate. Bake at 180C for 35 mins.',
            'status' => 'active',
        ]);

        RecipeIngredient::create(['recipe_id' => $r1->id, 'ingredient_id' => $flour->id, 'quantity' => 0.50, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r1->id, 'ingredient_id' => $sugar->id, 'quantity' => 0.30, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r1->id, 'ingredient_id' => $eggs->id, 'quantity' => 4.00, 'unit' => 'pcs']);
        RecipeIngredient::create(['recipe_id' => $r1->id, 'ingredient_id' => $milk->id, 'quantity' => 0.25, 'unit' => 'liter']);
        RecipeIngredient::create(['recipe_id' => $r1->id, 'ingredient_id' => $chocolate->id, 'quantity' => 0.20, 'unit' => 'kg']);

        $r2 = Recipe::create([
            'product_id' => $p3->id,
            'name' => 'Butter Croissant Recipe',
            'description' => 'Recipe for 1 Butter Croissant',
            'instructions' => 'Fold butter into laminated yeast dough. Bake at 200C for 18 mins.',
            'status' => 'active',
        ]);

        RecipeIngredient::create(['recipe_id' => $r2->id, 'ingredient_id' => $flour->id, 'quantity' => 0.10, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r2->id, 'ingredient_id' => $butter->id, 'quantity' => 0.05, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r2->id, 'ingredient_id' => $milk->id, 'quantity' => 0.05, 'unit' => 'liter']);

        $r3 = Recipe::create([
            'product_id' => $p2->id,
            'name' => 'Vanilla Sponge Cake Recipe',
            'description' => 'Light and airy vanilla sponge cake',
            'instructions' => 'Whip sugar and eggs, fold in flour and milk. Bake at 175C for 30 mins.',
            'status' => 'active',
        ]);

        RecipeIngredient::create(['recipe_id' => $r3->id, 'ingredient_id' => $flour->id, 'quantity' => 0.40, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r3->id, 'ingredient_id' => $sugar->id, 'quantity' => 0.25, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r3->id, 'ingredient_id' => $eggs->id, 'quantity' => 3.00, 'unit' => 'pcs']);
        RecipeIngredient::create(['recipe_id' => $r3->id, 'ingredient_id' => $milk->id, 'quantity' => 0.20, 'unit' => 'liter']);

        $r4 = Recipe::create([
            'product_id' => $p4->id,
            'name' => 'Chocolate Glazed Donut Recipe',
            'description' => 'Fluffy yeast donuts with rich chocolate glaze',
            'instructions' => 'Proof dough, shape rings, deep fry at 180C for 2 mins each side, dip in chocolate.',
            'status' => 'active',
        ]);

        RecipeIngredient::create(['recipe_id' => $r4->id, 'ingredient_id' => $flour->id, 'quantity' => 0.08, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r4->id, 'ingredient_id' => $sugar->id, 'quantity' => 0.03, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r4->id, 'ingredient_id' => $butter->id, 'quantity' => 0.02, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r4->id, 'ingredient_id' => $chocolate->id, 'quantity' => 0.03, 'unit' => 'kg']);

        $r5 = Recipe::create([
            'product_id' => $p5->id,
            'name' => 'Choco Chip Cookie Recipe',
            'description' => 'Crispy on edges, chewy center cookies',
            'instructions' => 'Cream butter and sugar, mix dry ingredients and chocolate chips. Bake at 190C for 10 mins.',
            'status' => 'active',
        ]);

        RecipeIngredient::create(['recipe_id' => $r5->id, 'ingredient_id' => $flour->id, 'quantity' => 0.05, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r5->id, 'ingredient_id' => $sugar->id, 'quantity' => 0.04, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r5->id, 'ingredient_id' => $butter->id, 'quantity' => 0.03, 'unit' => 'kg']);
        RecipeIngredient::create(['recipe_id' => $r5->id, 'ingredient_id' => $chocolate->id, 'quantity' => 0.04, 'unit' => 'kg']);

        // 7. Seed Settings
        Setting::set('bakery_name', 'Sweet Delights Bakery');
        Setting::set('bakery_phone', '+855 23 123 456');
        Setting::set('bakery_email', 'info@sweetdelights.com');
        Setting::set('bakery_address', 'Monivong Blvd, Phnom Penh, Cambodia');
        Setting::set('currency', '$');
        Setting::set('tax_percentage', '10');
        Setting::set('language', 'en');

        // 8. Seed Customers
        Customer::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'name' => 'John Doe',
                'phone' => '012 345 678',
                'address' => 'Street 51, BKK1, Phnom Penh',
                'loyalty_points' => 150,
                'loyalty_tier' => 'silver',
                'status' => 'active',
            ]
        );

        Customer::firstOrCreate(
            ['email' => 'sokha.chan@example.com'],
            [
                'name' => 'Sokha Chan',
                'phone' => '098 765 432',
                'address' => 'Street 315, Toul Kork, Phnom Penh',
                'loyalty_points' => 340,
                'loyalty_tier' => 'gold',
                'status' => 'active',
            ]
        );

        Customer::firstOrCreate(
            ['email' => 'maria.santos@example.com'],
            [
                'name' => 'Maria Santos',
                'phone' => '011 223 344',
                'address' => 'Preah Sisowath Quay, Daun Penh, Phnom Penh',
                'loyalty_points' => 520,
                'loyalty_tier' => 'vip',
                'status' => 'active',
            ]
        );

        Customer::firstOrCreate(
            ['email' => 'regular@example.com'],
            [
                'name' => 'Vannak Kem',
                'phone' => '085 999 888',
                'address' => 'Street 271, Chamkarmon, Phnom Penh',
                'loyalty_points' => 45,
                'loyalty_tier' => 'standard',
                'status' => 'active',
            ]
        );

        // 9. Seed Initial Production Batches
        Production::create([
            'batch_number'    => 'BATCH-' . date('Ymd') . '-0001',
            'recipe_id'       => $r1->id,
            'product_id'      => $p1->id,
            'quantity'        => 10,
            'production_date' => now()->subDay(),
            'scheduled_at'    => now()->subDay(),
            'started_at'      => now()->subDay()->addHours(1),
            'completed_at'    => now()->subDay()->addHours(3),
            'status'          => 'completed',
            'notes'           => 'Morning chocolate cake batch. Baked to perfection.',
            'user_id'         => 1, // Admin
            'baker_id'        => $baker1->id,
        ]);

        Production::create([
            'batch_number'    => 'BATCH-' . date('Ymd') . '-0002',
            'recipe_id'       => $r2->id,
            'product_id'      => $p3->id,
            'quantity'        => 20,
            'production_date' => now(),
            'scheduled_at'    => now(),
            'started_at'      => now()->subMinutes(35),
            'completed_at'    => null,
            'status'          => 'in_progress',
            'notes'           => 'Fresh breakfast butter croissants currently in proofing/oven.',
            'user_id'         => 2, // Manager
            'baker_id'        => $baker1->id,
        ]);

        Production::create([
            'batch_number'    => 'BATCH-' . date('Ymd') . '-0003',
            'recipe_id'       => $r3->id,
            'product_id'      => $p2->id,
            'quantity'        => 15,
            'production_date' => now()->addHours(2),
            'scheduled_at'    => now()->addHours(2),
            'started_at'      => null,
            'completed_at'    => null,
            'status'          => 'scheduled',
            'notes'           => 'Afternoon vanilla sponge cakes for showcase display.',
            'user_id'         => 1, // Admin
            'baker_id'        => $baker2->id,
        ]);

        // 13. Seed Purchase Orders & Stock Movements
        $this->call(PurchaseOrderSeeder::class);
    }
}
