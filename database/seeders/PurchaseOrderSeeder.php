<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\User;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $adminId = $admin ? $admin->id : 1;

        // Ensure realistic suppliers exist
        $supplier1 = Supplier::firstOrCreate(
            ['name' => 'ABC Food Supplier'],
            [
                'contact_person' => 'Sophea Pich',
                'phone'          => '+855 12 345 678',
                'email'          => 'contact@abcfood.com',
                'address'        => 'Phnom Penh Industrial Zone, Cambodia',
                'status'         => 'active',
                'notes'          => 'Preferred raw flour and sugar supplier. Net 30 payment terms.',
            ]
        );

        $supplier2 = Supplier::firstOrCreate(
            ['name' => 'Royal Dairy & Butter Imports'],
            [
                'contact_person' => 'Jean-Luc Moreau',
                'phone'          => '+855 98 765 432',
                'email'          => 'orders@royaldairy.com',
                'address'        => 'St. 271, Phnom Penh, Cambodia',
                'status'         => 'active',
                'notes'          => 'Imported French butter, heavy whipping cream, and whole dairy.',
            ]
        );

        $supplier3 = Supplier::firstOrCreate(
            ['name' => 'Mekong Packaging & Supplies'],
            [
                'contact_person' => 'Vannak Heng',
                'phone'          => '+855 11 223 344',
                'email'          => 'sales@mekongpack.com',
                'address'        => 'Sen Sok, Phnom Penh, Cambodia',
                'status'         => 'active',
                'notes'          => 'Bakery pastry boxes, bread paper bags, and parchment rolls.',
            ]
        );

        $flour = Ingredient::firstOrCreate(
            ['name' => 'Wheat Flour'],
            ['unit' => 'kg', 'quantity' => 120.00, 'cost' => 1.25, 'minimum_quantity' => 20.00, 'supplier_id' => $supplier1->id]
        );

        $sugar = Ingredient::firstOrCreate(
            ['name' => 'Refined Sugar'],
            ['unit' => 'kg', 'quantity' => 60.00, 'cost' => 0.95, 'minimum_quantity' => 15.00, 'supplier_id' => $supplier1->id]
        );

        $butter = Ingredient::firstOrCreate(
            ['name' => 'Unsalted Butter'],
            ['unit' => 'kg', 'quantity' => 35.00, 'cost' => 5.50, 'minimum_quantity' => 10.00, 'supplier_id' => $supplier2->id]
        );

        $yeast = Ingredient::firstOrCreate(
            ['name' => 'Instant Dry Yeast'],
            ['unit' => 'kg', 'quantity' => 8.00, 'cost' => 8.00, 'minimum_quantity' => 5.00, 'supplier_id' => $supplier1->id]
        );

        // 1. Seed Received Purchase Order (with StockMovements)
        if (!PurchaseOrder::where('po_number', 'PO-20260901-0001')->exists()) {
            $poReceived = PurchaseOrder::create([
                'po_number'              => 'PO-20260901-0001',
                'supplier_id'            => $supplier1->id,
                'order_date'             => now()->subDays(7),
                'expected_delivery_date' => now()->subDays(5),
                'received_date'          => now()->subDays(5),
                'status'                 => 'received',
                'notes'                  => 'Monthly flour and sugar replenishment. Delivered in good condition.',
                'user_id'                => $adminId,
                'total_cost'             => 255.00,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $poReceived->id,
                'ingredient_id'     => $flour->id,
                'quantity'          => 100.00,
                'unit'              => 'kg',
                'purchase_price'    => 1.25,
                'subtotal'          => 125.00,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $poReceived->id,
                'ingredient_id'     => $sugar->id,
                'quantity'          => 100.00,
                'unit'              => 'kg',
                'purchase_price'    => 0.95,
                'subtotal'          => 95.00,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $poReceived->id,
                'ingredient_id'     => $yeast->id,
                'quantity'          => 4.00,
                'unit'              => 'kg',
                'purchase_price'    => 8.75,
                'subtotal'          => 35.00,
            ]);

            // Audit movements for received PO
            StockMovement::create([
                'ingredient_id'    => $flour->id,
                'type'             => 'in',
                'quantity'         => 100.00,
                'unit'             => 'kg',
                'unit_cost'        => 1.25,
                'reference_type'   => 'purchase_order',
                'reference_id'     => $poReceived->id,
                'reference_number' => $poReceived->po_number,
                'notes'            => "Stock IN from received PO #{$poReceived->po_number}",
                'user_id'          => $adminId,
                'created_at'       => now()->subDays(5),
            ]);

            StockMovement::create([
                'ingredient_id'    => $sugar->id,
                'type'             => 'in',
                'quantity'         => 100.00,
                'unit'             => 'kg',
                'unit_cost'        => 0.95,
                'reference_type'   => 'purchase_order',
                'reference_id'     => $poReceived->id,
                'reference_number' => $poReceived->po_number,
                'notes'            => "Stock IN from received PO #{$poReceived->po_number}",
                'user_id'          => $adminId,
                'created_at'       => now()->subDays(5),
            ]);
        }

        // 2. Seed Ordered Purchase Order (In Transit)
        if (!PurchaseOrder::where('po_number', 'PO-20260905-0002')->exists()) {
            $poOrdered = PurchaseOrder::create([
                'po_number'              => 'PO-20260905-0002',
                'supplier_id'            => $supplier2->id,
                'order_date'             => now()->subDays(3),
                'expected_delivery_date' => now()->addDays(2),
                'status'                 => 'ordered',
                'notes'                  => 'French butter order for croissant production batches.',
                'user_id'                => $adminId,
                'total_cost'             => 275.00,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $poOrdered->id,
                'ingredient_id'     => $butter->id,
                'quantity'          => 50.00,
                'unit'              => 'kg',
                'purchase_price'    => 5.50,
                'subtotal'          => 275.00,
            ]);
        }

        // 3. Seed Draft Purchase Order
        if (!PurchaseOrder::where('po_number', 'PO-20260908-0003')->exists()) {
            $poDraft = PurchaseOrder::create([
                'po_number'              => 'PO-20260908-0003',
                'supplier_id'            => $supplier1->id,
                'order_date'             => today(),
                'expected_delivery_date' => today()->addDays(5),
                'status'                 => 'draft',
                'notes'                  => 'Weekly dry ingredients replenishment draft.',
                'user_id'                => $adminId,
                'total_cost'             => 145.00,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $poDraft->id,
                'ingredient_id'     => $flour->id,
                'quantity'          => 80.00,
                'unit'              => 'kg',
                'purchase_price'    => 1.25,
                'subtotal'          => 100.00,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $poDraft->id,
                'ingredient_id'     => $sugar->id,
                'quantity'          => 45.00,
                'unit'              => 'kg',
                'purchase_price'    => 1.00,
                'subtotal'          => 45.00,
            ]);
        }

        // 4. Seed Cancelled Purchase Order
        if (!PurchaseOrder::where('po_number', 'PO-20260902-0004')->exists()) {
            $poCancelled = PurchaseOrder::create([
                'po_number'              => 'PO-20260902-0004',
                'supplier_id'            => $supplier3->id,
                'order_date'             => now()->subDays(6),
                'status'                 => 'cancelled',
                'notes'                  => 'Cancelled due to change in pastry box specifications.',
                'user_id'                => $adminId,
                'total_cost'             => 80.00,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $poCancelled->id,
                'ingredient_id'     => $yeast->id,
                'quantity'          => 10.00,
                'unit'              => 'kg',
                'purchase_price'    => 8.00,
                'subtotal'          => 80.00,
            ]);
        }
    }
}
