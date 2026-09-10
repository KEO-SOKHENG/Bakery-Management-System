<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::firstOrCreate(
            ['username' => 'admin_test'],
            ['name' => 'Admin Test', 'email' => 'admintest@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'admin', 'status' => 'active']
        );
        $this->manager = User::firstOrCreate(
            ['username' => 'mgr_test'],
            ['name' => 'Mgr Test', 'email' => 'mgrtest@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'manager', 'status' => 'active']
        );
    }

    public function test_admin_can_view_suppliers_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.suppliers'));
        $response->assertStatus(200);
        $response->assertSee('Raw Material Suppliers');
    }

    public function test_manager_can_view_suppliers_list(): void
    {
        $response = $this->actingAs($this->manager)->get(route('admin.suppliers'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_supplier(): void
    {
        $supplierName = 'Vendor Testing ' . rand(1000, 9999);
        $response = $this->actingAs($this->admin)->post(route('admin.suppliers.store'), [
            'name'           => $supplierName,
            'contact_person' => 'Dara Sam',
            'phone'          => '+855 12 999 888',
            'email'          => 'dara@vendortest.com',
            'address'        => 'Toul Kork, Phnom Penh',
            'status'         => 'active',
            'notes'          => 'Test payment terms',
        ]);

        $response->assertRedirect(route('admin.suppliers'));
        $this->assertDatabaseHas('suppliers', ['name' => $supplierName]);
    }

    public function test_can_view_supplier_detail_with_orders_and_ingredients(): void
    {
        $supplier = Supplier::create([
            'name'           => 'Detail Test Vendor ' . rand(1000, 9999),
            'contact_person' => 'Chanrithy',
            'status'         => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.suppliers.show', $supplier->id));
        $response->assertStatus(200);
        $response->assertSee($supplier->name);
        $response->assertSee('Chanrithy');
    }

    public function test_supplier_with_historical_purchase_orders_cannot_be_deleted(): void
    {
        $supplier = Supplier::create([
            'name'   => 'PO Supplier ' . rand(1000, 9999),
            'status' => 'active',
        ]);

        PurchaseOrder::create([
            'po_number'   => 'PO-SAFE-' . rand(1000, 9999),
            'supplier_id' => $supplier->id,
            'order_date'  => today(),
            'status'      => 'received',
            'total_cost'  => 100.00,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.suppliers.destroy', $supplier->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_supplier_with_linked_ingredients_cannot_be_deleted(): void
    {
        $supplier = Supplier::create([
            'name'   => 'Ing Supplier ' . rand(1000, 9999),
            'status' => 'active',
        ]);

        Ingredient::create([
            'name'             => 'Test Sugar ' . rand(1000, 9999),
            'supplier_id'      => $supplier->id,
            'unit'             => 'kg',
            'quantity'         => 10,
            'cost'             => 1.0,
            'minimum_quantity' => 5,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.suppliers.destroy', $supplier->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_unreferenced_supplier_can_be_safely_deleted(): void
    {
        $supplier = Supplier::create([
            'name'   => 'Orphan Vendor ' . rand(1000, 9999),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.suppliers.destroy', $supplier->id));
        $response->assertRedirect(route('admin.suppliers'));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
