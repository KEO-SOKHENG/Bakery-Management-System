<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

class RbacPurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $baker;
    private User $cashier;

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
        $this->baker = User::firstOrCreate(
            ['username' => 'baker_test'],
            ['name' => 'Baker Test', 'email' => 'bakertest@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'baker', 'status' => 'active']
        );
        $this->cashier = User::firstOrCreate(
            ['username' => 'cashier_test'],
            ['name' => 'Cashier Test', 'email' => 'cashiertest@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'cashier', 'status' => 'active']
        );
    }

    public function test_admin_and_manager_have_access_to_purchase_orders_and_suppliers(): void
    {
        // Admin
        $this->actingAs($this->admin)->get(route('admin.purchase-orders.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.suppliers'))->assertStatus(200);

        // Manager
        $this->actingAs($this->manager)->get(route('admin.purchase-orders.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('admin.suppliers'))->assertStatus(200);
    }

    public function test_baker_and_cashier_are_restricted_from_purchase_orders_and_suppliers(): void
    {
        // Baker restricted
        $resBakerPO = $this->actingAs($this->baker)->get(route('admin.purchase-orders.index'));
        $this->assertTrue(in_array($resBakerPO->status(), [403, 302]));

        $resBakerSup = $this->actingAs($this->baker)->get(route('admin.suppliers'));
        $this->assertTrue(in_array($resBakerSup->status(), [403, 302]));

        // Cashier restricted
        $resCashierPO = $this->actingAs($this->cashier)->get(route('admin.purchase-orders.index'));
        $this->assertTrue(in_array($resCashierPO->status(), [403, 302]));

        $resCashierSup = $this->actingAs($this->cashier)->get(route('admin.suppliers'));
        $this->assertTrue(in_array($resCashierSup->status(), [403, 302]));
    }
}
