<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearOldOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $cashier;
    private Category $category;
    private Product $product;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_test_purge'],
            ['name' => 'Admin Purge Test', 'email' => 'admin_purge@bakery.com', 'password' => bcrypt('password123'), 'role' => 'admin', 'status' => 'active']
        );

        $this->cashier = User::firstOrCreate(
            ['username' => 'cashier_test_purge'],
            ['name' => 'Cashier Purge Test', 'email' => 'cashier_purge@bakery.com', 'password' => bcrypt('password123'), 'role' => 'cashier', 'status' => 'active']
        );

        $this->category = Category::firstOrCreate(
            ['name' => 'Test Pastries'],
            ['description' => 'Pastries category', 'status' => 'active']
        );

        $this->product = Product::firstOrCreate(
            ['name' => 'Sourdough Bread'],
            ['price' => 4.50, 'cost' => 1.50, 'stock' => 50, 'category_id' => $this->category->id, 'status' => 'active']
        );

        $this->customer = Customer::firstOrCreate(
            ['phone' => '099112233'],
            ['name' => 'John Doe', 'email' => 'john@test.com', 'status' => 'active']
        );
    }

    private function createFullOrder(string $status, int $daysAgo = 0, int $qty = 2): Order
    {
        $createdAt = now()->subDays($daysAgo);

        $order = Order::create([
            'user_id' => $this->admin->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-' . uniqid(),
            'customer_name' => $this->customer->name,
            'subtotal' => 4.50 * $qty,
            'discount' => 0,
            'tax' => 0.45 * $qty,
            'total' => 4.95 * $qty,
            'payment_method' => 'cash',
            'payment_status' => $status === 'completed' ? 'paid' : 'unpaid',
            'order_status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        // Manually update created_at via query to bypass timestamp overrides
        Order::where('id', $order->id)->update(['created_at' => $createdAt]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => $qty,
            'price' => 4.50,
            'subtotal' => 4.50 * $qty,
            'created_at' => $createdAt,
        ]);

        if ($status === 'completed') {
            Sale::create([
                'order_id' => $order->id,
                'user_id' => $this->admin->id,
                'total' => 4.95 * $qty,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'sold_at' => $createdAt,
            ]);

            Payment::create([
                'order_id' => $order->id,
                'user_id' => $this->admin->id,
                'payment_method' => 'cash',
                'amount' => 4.95 * $qty,
                'payment_status' => 'completed',
                'payment_reference' => 'PAY-' . uniqid(),
                'payment_date' => $createdAt,
            ]);
        }

        return $order->fresh();
    }

    public function test_artisan_dry_run_does_not_delete_orders(): void
    {
        $this->createFullOrder('completed', 45);
        $this->createFullOrder('completed', 10);

        $this->artisan('orders:clear-old', [
            '--days' => 30,
            '--dry-run' => true,
        ])
        ->expectsOutputToContain('[DRY-RUN] Simulation completed. No records were deleted.')
        ->assertSuccessful();

        $this->assertEquals(2, Order::count());
    }

    public function test_artisan_command_purges_old_orders_and_cascades_relations(): void
    {
        $oldOrder = $this->createFullOrder('completed', 40);
        $recentOrder = $this->createFullOrder('completed', 5);

        $this->artisan('orders:clear-old', [
            '--days' => 30,
            '--force' => true,
        ])
        ->expectsOutputToContain('Successfully cleared 1 order(s)')
        ->assertSuccessful();

        $this->assertEquals(1, Order::count());
        $this->assertEquals($recentOrder->id, Order::first()->id);
        $this->assertDatabaseMissing('orders', ['id' => $oldOrder->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $oldOrder->id]);
        $this->assertDatabaseMissing('sales', ['order_id' => $oldOrder->id]);
        $this->assertDatabaseMissing('payments', ['order_id' => $oldOrder->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'orders_purged',
        ]);
    }

    public function test_artisan_command_can_filter_by_status(): void
    {
        $oldCompleted = $this->createFullOrder('completed', 40);
        $oldPending = $this->createFullOrder('pending', 40);

        $this->artisan('orders:clear-old', [
            '--days' => 30,
            '--status' => 'completed',
            '--force' => true,
        ])
        ->assertSuccessful();

        $this->assertDatabaseMissing('orders', ['id' => $oldCompleted->id]);
        $this->assertDatabaseHas('orders', ['id' => $oldPending->id]);
    }

    public function test_artisan_command_restores_stock_for_pending_orders(): void
    {
        $initialStock = $this->product->stock; // 50
        $oldPending = $this->createFullOrder('pending', 40, 5); // 5 units

        $this->artisan('orders:clear-old', [
            '--days' => 30,
            '--restore-stock' => true,
            '--force' => true,
        ])
        ->assertSuccessful();

        $this->product->refresh();
        $this->assertEquals($initialStock + 5, $this->product->stock);
        $this->assertDatabaseMissing('orders', ['id' => $oldPending->id]);
    }

    public function test_artisan_command_can_clear_all_orders(): void
    {
        $this->createFullOrder('completed', 5);
        $this->createFullOrder('pending', 2);

        $this->artisan('orders:clear-old', [
            '--all' => true,
            '--force' => true,
        ])
        ->expectsOutputToContain('Successfully cleared 2 order(s)')
        ->assertSuccessful();

        $this->assertEquals(0, Order::count());
    }

    public function test_admin_can_preview_purge_counts_via_web_api(): void
    {
        $this->createFullOrder('completed', 45);
        $this->createFullOrder('completed', 10);

        $response = $this->actingAs($this->admin)->getJson(route('admin.orders.purgePreview', [
            'scope' => 'days',
            'days' => 30,
            'status' => 'all',
        ]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'orders_count' => 1,
            ]);
    }

    public function test_non_admin_cannot_preview_or_purge_orders(): void
    {
        $response = $this->actingAs($this->cashier)->getJson(route('admin.orders.purgePreview'));
        $response->assertStatus(403);

        $postResponse = $this->actingAs($this->cashier)->postJson(route('admin.orders.purgeOld'), [
            'scope' => 'all',
            'confirm_phrase' => 'CONFIRM',
        ]);
        $postResponse->assertStatus(403);

        $redirectResponse = $this->actingAs($this->cashier)->post(route('admin.orders.purgeOld'), [
            'scope' => 'all',
            'confirm_phrase' => 'CONFIRM',
        ]);
        $redirectResponse->assertRedirect(route('cashier.dashboard'));
    }

    public function test_admin_can_purge_orders_via_web_controller(): void
    {
        $this->createFullOrder('completed', 40);
        $this->createFullOrder('completed', 5);

        $response = $this->actingAs($this->admin)->post(route('admin.orders.purgeOld'), [
            'scope' => 'days',
            'days' => 30,
            'status' => 'all',
            'restore_stock' => 1,
            'confirm_phrase' => 'CONFIRM',
        ]);

        $response->assertRedirect(route('admin.orders'));
        $this->assertEquals(1, Order::count());
    }

    public function test_web_controller_rejects_without_confirm_phrase(): void
    {
        $this->createFullOrder('completed', 40);

        $response = $this->actingAs($this->admin)->post(route('admin.orders.purgeOld'), [
            'scope' => 'all',
            'confirm_phrase' => 'WRONG_WORD',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Order::count());
    }
}
