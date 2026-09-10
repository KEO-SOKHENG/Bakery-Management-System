<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Production;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Bakers
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

        // 2. Fetch existing products & ingredients
        $flour = Ingredient::where('name', 'like', '%Flour%')->first();
        $sugar = Ingredient::where('name', 'like', '%Sugar%')->first();
        $eggs  = Ingredient::where('name', 'like', '%Egg%')->first();
        $milk  = Ingredient::where('name', 'like', '%Milk%')->first();
        $butter = Ingredient::where('name', 'like', '%Butter%')->first();
        $choc  = Ingredient::where('name', 'like', '%Chocolate%')->first();

        $p1 = Product::where('sku', 'CAKE-CHOC-01')->first();
        $p2 = Product::where('sku', 'CAKE-VAN-01')->first();
        $p3 = Product::where('sku', 'PAST-CROI-01')->first();
        $p4 = Product::where('sku', 'DON-CHOC-01')->first();
        $p5 = Product::where('sku', 'COOK-CHOC-01')->first();

        // 3. Ensure Recipes exist for Vanilla Cake, Donut, Cookies
        if ($p2 && !Recipe::where('product_id', $p2->id)->exists()) {
            $r3 = Recipe::create([
                'product_id'   => $p2->id,
                'name'         => 'Vanilla Sponge Cake Recipe',
                'description'  => 'Light and airy vanilla sponge cake',
                'instructions' => 'Whip sugar and eggs, fold in flour and milk. Bake at 175C for 30 mins.',
                'status'       => 'active',
            ]);
            if ($flour) RecipeIngredient::firstOrCreate(['recipe_id' => $r3->id, 'ingredient_id' => $flour->id], ['quantity' => 0.40, 'unit' => 'kg']);
            if ($sugar) RecipeIngredient::firstOrCreate(['recipe_id' => $r3->id, 'ingredient_id' => $sugar->id], ['quantity' => 0.25, 'unit' => 'kg']);
            if ($eggs)  RecipeIngredient::firstOrCreate(['recipe_id' => $r3->id, 'ingredient_id' => $eggs->id], ['quantity' => 3.00, 'unit' => 'pcs']);
            if ($milk)  RecipeIngredient::firstOrCreate(['recipe_id' => $r3->id, 'ingredient_id' => $milk->id], ['quantity' => 0.20, 'unit' => 'liter']);
        }

        if ($p4 && !Recipe::where('product_id', $p4->id)->exists()) {
            $r4 = Recipe::create([
                'product_id'   => $p4->id,
                'name'         => 'Chocolate Glazed Donut Recipe',
                'description'  => 'Fluffy yeast donuts with rich chocolate glaze',
                'instructions' => 'Proof dough, shape rings, deep fry at 180C for 2 mins each side, dip in chocolate.',
                'status'       => 'active',
            ]);
            if ($flour) RecipeIngredient::firstOrCreate(['recipe_id' => $r4->id, 'ingredient_id' => $flour->id], ['quantity' => 0.08, 'unit' => 'kg']);
            if ($sugar) RecipeIngredient::firstOrCreate(['recipe_id' => $r4->id, 'ingredient_id' => $sugar->id], ['quantity' => 0.03, 'unit' => 'kg']);
            if ($butter) RecipeIngredient::firstOrCreate(['recipe_id' => $r4->id, 'ingredient_id' => $butter->id], ['quantity' => 0.02, 'unit' => 'kg']);
            if ($choc)  RecipeIngredient::firstOrCreate(['recipe_id' => $r4->id, 'ingredient_id' => $choc->id], ['quantity' => 0.03, 'unit' => 'kg']);
        }

        if ($p5 && !Recipe::where('product_id', $p5->id)->exists()) {
            $r5 = Recipe::create([
                'product_id'   => $p5->id,
                'name'         => 'Choco Chip Cookie Recipe',
                'description'  => 'Crispy on edges, chewy center cookies',
                'instructions' => 'Cream butter and sugar, mix dry ingredients and chocolate chips. Bake at 190C for 10 mins.',
                'status'       => 'active',
            ]);
            if ($flour) RecipeIngredient::firstOrCreate(['recipe_id' => $r5->id, 'ingredient_id' => $flour->id], ['quantity' => 0.05, 'unit' => 'kg']);
            if ($sugar) RecipeIngredient::firstOrCreate(['recipe_id' => $r5->id, 'ingredient_id' => $sugar->id], ['quantity' => 0.04, 'unit' => 'kg']);
            if ($butter) RecipeIngredient::firstOrCreate(['recipe_id' => $r5->id, 'ingredient_id' => $butter->id], ['quantity' => 0.03, 'unit' => 'kg']);
            if ($choc)  RecipeIngredient::firstOrCreate(['recipe_id' => $r5->id, 'ingredient_id' => $choc->id], ['quantity' => 0.04, 'unit' => 'kg']);
        }

        // 4. Seed Initial Batches if none exist
        if (Production::count() === 0) {
            $admin = User::where('role', 'admin')->first();
            $manager = User::where('role', 'manager')->first();

            $r1 = Recipe::first();
            $r2 = Recipe::skip(1)->first();
            $r3 = Recipe::skip(2)->first();

            if ($p1 && $r1) {
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
                    'user_id'         => $admin ? $admin->id : 1,
                    'baker_id'        => $baker1->id,
                ]);
            }

            if ($p3 && $r2) {
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
                    'notes'           => 'Fresh breakfast butter croissants currently in oven.',
                    'user_id'         => $manager ? $manager->id : 2,
                    'baker_id'        => $baker1->id,
                ]);
            }

            if ($p2 && $r3) {
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
                    'user_id'         => $admin ? $admin->id : 1,
                    'baker_id'        => $baker2->id,
                ]);
            }
        }
    }
}
