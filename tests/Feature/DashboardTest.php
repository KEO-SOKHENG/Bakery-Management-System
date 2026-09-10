<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Production;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $cashier;
    private User $baker;
    private Category $category;
    private Product $croissant;
    private Product $baguette;
    private Customer $customer;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_dash_test'],
            ['name' => 'Admin Dash', 'email' => 'admin_dash@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'admin', 'status' => 'active']
        );

        $this->manager = User::firstOrCreate(
            ['username' => 'manager_dash_test'],
            ['name' => 'Manager Dash', 'email' => 'manager_dash@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'manager', 'status' => 'active']
        );

        $this->cashier = User::firstOrCreate(
            ['username' => 'cashier_dash_test'],
            ['name' => 'Cashier Dash', 'email' => 'cashier_dash@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'cashier', 'status' => 'active']
        );

        $this->baker = User::firstOrCreate(
            ['username' => 'baker_dash_test'],
            ['name' => 'Baker Dash', 'email' => 'baker_dash@bakery.com', 'password' => bcrypt('pass123'), 'role' => 'baker', 'status' => 'active']
        );

        $this->category = Category::create([
            'name' => 'Bakery Delights',
            'description' => 'Fresh baked items',
            'status' => 'active'
        ]);

        $this->croissant = Product::create([
            'name' => 'Artisan Croissant',
            'price' => 3.50,
            'cost' => 1.20,
            'stock' => 45,
            'category_id' => $this->category->id,
            'status' => 'active'
        ]);

        $this->baguette = Product::create([
            'name' => 'French Baguette',
            'price' => 2.50,
            'cost' => 0.80,
            'stock' => 30,
            'category_id' => $this->category->id,
            'status' => 'active'
        ]);

        $this->customer = Customer::create([
            'name' => 'Sarah Connor',
            'phone' => '012999888',
            'email' => 'sarah@resistance.com',
            'loyalty_points' => 150,
            'loyalty_tier' => 'vip',
            'status' => 'active'
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Golden Grain Mills',
            'contact_person' => 'Robert Miller',
            'phone' => '012345678',
            'status' => 'active'
        ]);
    }

    public function test_admin_dashboard_renders_with_8_real_kpis(): void
    {
        // 1. Create order & completed sale for today
        $order = Order::create([
            'order_number' => 'ORD-DASH-001',
            'user_id' => $this->admin->id,
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'order_type' => 'walkin',
            'subtotal' => 20.00,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 20.00,
            'payment_type' => 'cash',
            'order_status' => 'completed',
            'created_at' => now(),
        ]);

        Sale::create([
            'sale_number' => 'SALE-DASH-001',
            'user_id' => $this->admin->id,
            'customer_id' => $this->customer->id,
            'order_id' => $order->id,
            'subtotal' => 20.00,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 20.00,
            'payment_method' => 'cash',
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        // 2. Create an ingredient with low stock
        Ingredient::create([
            'name' => 'All-Purpose Flour',
            'unit' => 'kg',
            'quantity' => 4.0,
            'minimum_quantity' => 10.0,
            'cost_per_unit' => 1.50,
            'status' => 'active'
        ]);

        // 3. Create active production batch
        Production::create([
            'batch_number' => 'BATCH-DASH-001',
            'product_id' => $this->croissant->id,
            'baker_id' => $this->baker->id,
            'quantity' => 50,
            'production_date' => today(),
            'status' => 'in_progress',
        ]);

        // 4. Create pending purchase order
        PurchaseOrder::create([
            'po_number' => 'PO-DASH-001',
            'supplier_id' => $this->supplier->id,
            'user_id' => $this->admin->id,
            'order_date' => today(),
            'status' => 'ordered',
            'total_cost' => 125.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('todayOrdersCount', 1);
        $response->assertViewHas('todayRevenueSum', 20.00);
        $response->assertViewHas('totalCustomersCount', 1);
        $response->assertViewHas('lowStockCount', 1);
        $response->assertViewHas('activeProductsCount', 2);
        $response->assertViewHas('activeProductionsCount', 1);
        $response->assertViewHas('pendingPosCount', 1);
        $response->assertSeeText("Today's Orders");
        $response->assertSeeText("Today's Revenue");
        $response->assertSeeText("Pending Orders");
        $response->assertSeeText("Low Stock Items");
    }

    public function test_admin_dashboard_date_filtering(): void
    {
        $orderYesterday = Order::create([
            'order_number' => 'ORD-YEST-001',
            'user_id' => $this->admin->id,
            'customer_name' => 'Yesterday Customer',
            'order_type' => 'walkin',
            'subtotal' => 50.00,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 50.00,
            'payment_type' => 'cash',
            'order_status' => 'completed',
            'created_at' => Carbon::yesterday(),
        ]);

        // Sale yesterday
        Sale::create([
            'sale_number' => 'SALE-YEST-001',
            'order_id' => $orderYesterday->id,
            'user_id' => $this->admin->id,
            'subtotal' => 50.00,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 50.00,
            'payment_method' => 'cash',
            'status' => 'completed',
            'sold_at' => Carbon::yesterday(),
        ]);

        $orderToday = Order::create([
            'order_number' => 'ORD-TOD-001',
            'user_id' => $this->admin->id,
            'customer_name' => 'Today Customer',
            'order_type' => 'walkin',
            'subtotal' => 30.00,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 30.00,
            'payment_type' => 'cash',
            'order_status' => 'completed',
            'created_at' => Carbon::today(),
        ]);

        // Sale today
        Sale::create([
            'sale_number' => 'SALE-TOD-001',
            'order_id' => $orderToday->id,
            'user_id' => $this->admin->id,
            'subtotal' => 30.00,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 30.00,
            'payment_method' => 'cash',
            'status' => 'completed',
            'sold_at' => Carbon::today(),
        ]);

        // Filter: yesterday
        $resYesterday = $this->actingAs($this->admin)->get(route('admin.dashboard', ['period' => 'yesterday']));
        $resYesterday->assertStatus(200);
        $resYesterday->assertViewHas('periodTotalRevenue', 50.00);

        // Filter: today
        $resToday = $this->actingAs($this->admin)->get(route('admin.dashboard', ['period' => 'today']));
        $resToday->assertStatus(200);
        $resToday->assertViewHas('periodTotalRevenue', 30.00);

        // Filter: 7days
        $res7Days = $this->actingAs($this->admin)->get(route('admin.dashboard', ['period' => '7days']));
        $res7Days->assertStatus(200);
        $res7Days->assertViewHas('periodTotalRevenue', 80.00);

        // Filter: custom
        $resCustom = $this->actingAs($this->admin)->get(route('admin.dashboard', [
            'period' => 'custom',
            'start_date' => Carbon::yesterday()->format('Y-m-d'),
            'end_date' => Carbon::yesterday()->format('Y-m-d'),
        ]));
        $resCustom->assertStatus(200);
        $resCustom->assertViewHas('periodTotalRevenue', 50.00);
    }

    public function test_best_selling_products_counts_completed_orders_only(): void
    {
        // Create an uncompleted order with 100 croissants
        $pendingOrder = Order::create([
            'order_number' => 'ORD-PEND-001',
            'user_id' => $this->admin->id,
            'customer_name' => 'Pending Customer',
            'order_type' => 'walkin',
            'subtotal' => 350.00,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 350.00,
            'payment_type' => 'cash',
            'order_status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $pendingOrder->id,
            'product_id' => $this->croissant->id,
            'unit_price' => 3.50,
            'quantity' => 100,
            'subtotal' => 350.00,
        ]);

        // Create a completed order with 15 baguettes
        $completedOrder = Order::create([
            'order_number' => 'ORD-COMP-001',
            'user_id' => $this->admin->id,
            'customer_name' => 'Completed Customer',
            'order_type' => 'walkin',
            'subtotal' => 37.50,
            'tax' => 0.00,
            'discount' => 0.00,
            'total' => 37.50,
            'payment_type' => 'cash',
            'order_status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $completedOrder->id,
            'product_id' => $this->baguette->id,
            'unit_price' => 2.50,
            'quantity' => 15,
            'subtotal' => 37.50,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $topProducts = $response->viewData('topProducts');
        $this->assertCount(1, $topProducts);
        $this->assertEquals($this->baguette->id, $topProducts->first()->id);
        $this->assertEquals(15, $topProducts->first()->total_sold);
    }

    public function test_stock_alerts_and_expiry_detection(): void
    {
        // 1. Low stock ingredient
        Ingredient::create([
            'name' => 'Vanilla Extract',
            'unit' => 'ml',
            'quantity' => 10,
            'minimum_quantity' => 100,
            'cost_per_unit' => 0.20,
            'status' => 'active'
        ]);

        // 2. Expiring soon ingredient (in 3 days)
        Ingredient::create([
            'name' => 'Fresh Milk',
            'unit' => 'liters',
            'quantity' => 20,
            'minimum_quantity' => 5,
            'cost_per_unit' => 1.80,
            'expiry_date' => Carbon::today()->addDays(3),
            'status' => 'active'
        ]);

        // 3. Already expired ingredient (yesterday)
        Ingredient::create([
            'name' => 'Dry Yeast Batch 1',
            'unit' => 'grams',
            'quantity' => 500,
            'minimum_quantity' => 100,
            'cost_per_unit' => 0.05,
            'expiry_date' => Carbon::yesterday(),
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response->assertViewHas('expiredIngredientsCount', 1);
        $expiringSoon = $response->viewData('expiringSoonIngredients');
        $this->assertTrue($expiringSoon->contains('name', 'Fresh Milk'));
        $this->assertFalse($expiringSoon->contains('name', 'Dry Yeast Batch 1'));
    }

    public function test_manager_dashboard_renders_with_real_operational_metrics(): void
    {
        // Create a scheduled production for today
        Production::create([
            'batch_number' => 'BATCH-MGR-001',
            'product_id' => $this->croissant->id,
            'baker_id' => $this->baker->id,
            'quantity' => 40,
            'production_date' => today(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('activeProductionsCount', 1);
        $todayProductions = $response->viewData('todayProductions');
        $this->assertCount(1, $todayProductions);
        $this->assertEquals(40, $todayProductions->first()->quantity);
    }

    public function test_rbac_access_control(): void
    {
        // Baker cannot access admin dashboard
        $bakerRes = $this->actingAs($this->baker)->get(route('admin.dashboard'));
        $this->assertTrue(in_array($bakerRes->status(), [403, 302]));

        // Cashier cannot access admin dashboard
        $cashierRes = $this->actingAs($this->cashier)->get(route('admin.dashboard'));
        $this->assertTrue(in_array($cashierRes->status(), [403, 302]));

        // Admin can access admin dashboard
        $adminRes = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $adminRes->assertStatus(200);

        // Manager can access manager dashboard
        $managerRes = $this->actingAs($this->manager)->get(route('manager.dashboard'));
        $managerRes->assertStatus(200);
    }
}
