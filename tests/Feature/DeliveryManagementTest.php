<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeliveryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $cashier;
    private User $baker;
    private User $deliveryStaff1;
    private User $deliveryStaff2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'test_admin_del'],
            ['name' => 'Admin Del', 'email' => 'admin_del@test.com', 'password' => Hash::make('pass123'), 'role' => 'admin', 'status' => 'active']
        );

        $this->manager = User::firstOrCreate(
            ['username' => 'test_mgr_del'],
            ['name' => 'Mgr Del', 'email' => 'mgr_del@test.com', 'password' => Hash::make('pass123'), 'role' => 'manager', 'status' => 'active']
        );

        $this->cashier = User::firstOrCreate(
            ['username' => 'test_cashier_del'],
            ['name' => 'Cashier Del', 'email' => 'cashier_del@test.com', 'password' => Hash::make('pass123'), 'role' => 'cashier', 'status' => 'active']
        );

        $this->baker = User::firstOrCreate(
            ['username' => 'test_baker_del'],
            ['name' => 'Baker Del', 'email' => 'baker_del@test.com', 'password' => Hash::make('pass123'), 'role' => 'baker', 'status' => 'active']
        );

        $this->deliveryStaff1 = User::firstOrCreate(
            ['username' => 'test_driver_1'],
            ['name' => 'Driver 1', 'email' => 'driver1@test.com', 'password' => Hash::make('pass123'), 'role' => 'delivery_staff', 'status' => 'active']
        );

        $this->deliveryStaff2 = User::firstOrCreate(
            ['username' => 'test_driver_2'],
            ['name' => 'Driver 2', 'email' => 'driver2@test.com', 'password' => Hash::make('pass123'), 'role' => 'delivery_staff', 'status' => 'active']
        );
    }

    private function createTestOrder(string $status = 'ready_for_pickup'): Order
    {
        $customer = Customer::firstOrCreate(
            ['name' => 'Delivery Test Customer'],
            ['phone' => '+855 12 999 888', 'email' => 'deltest@bakery.com', 'address' => 'St 2004, Phnom Penh', 'status' => 'active']
        );

        $order = Order::create([
            'order_number' => 'DEL-ORD-' . strtoupper(uniqid()),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'subtotal' => 25.00,
            'discount' => 0.00,
            'tax' => 2.50,
            'total' => 27.50,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'order_status' => $status,
        ]);

        $product = Product::first();
        if ($product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 12.50,
                'subtotal' => 25.00,
            ]);
        }

        return $order;
    }

    public function test_admin_and_manager_can_view_deliveries_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.deliveries.index'));
        $response->assertStatus(200);
        $response->assertSee('Delivery Management');

        $responseMgr = $this->actingAs($this->manager)->get(route('admin.deliveries.index'));
        $responseMgr->assertStatus(200);
    }

    public function test_delivery_staff_can_view_deliveries_index()
    {
        $response = $this->actingAs($this->deliveryStaff1)->get(route('admin.deliveries.index'));
        $response->assertStatus(200);
    }

    public function test_baker_is_blocked_from_delivery_management()
    {
        $response = $this->actingAs($this->baker)->get(route('admin.deliveries.index'));
        $response->assertStatus(302); // redirected away by CheckRole
    }

    public function test_can_schedule_and_store_new_delivery()
    {
        $order = $this->createTestOrder('ready_for_pickup');

        $response = $this->actingAs($this->admin)->post(route('admin.deliveries.store'), [
            'order_id' => $order->id,
            'recipient_name' => 'Sopheap Meas',
            'recipient_phone' => '+855 12 777 666',
            'delivery_address' => 'House 42, St 63, BKK1, Phnom Penh',
            'scheduled_at' => now()->addHours(3)->toDateTimeString(),
            'delivery_fee' => 2.00,
            'notes' => 'Ring doorbell upon arrival',
            'user_id' => $this->deliveryStaff1->id,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('deliveries', [
            'order_id' => $order->id,
            'recipient_name' => 'Sopheap Meas',
            'delivery_status' => 'assigned',
            'user_id' => $this->deliveryStaff1->id,
        ]);
    }

    public function test_delivery_status_transitions_and_synchronizes_with_order()
    {
        $order = $this->createTestOrder('ready_for_pickup');

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'user_id' => $this->deliveryStaff1->id,
            'recipient_name' => 'Test Recipient',
            'recipient_phone' => '+855 12 345 678',
            'delivery_address' => 'Russian Market, Phnom Penh',
            'delivery_status' => 'assigned',
            'scheduled_at' => now()->addHour(),
            'delivery_fee' => 1.50,
            'tracking_number' => 'DEL-SYNC-01',
        ]);

        // 1. Transition to out_for_delivery
        $responseTransit = $this->actingAs($this->deliveryStaff1)
            ->post(route('admin.deliveries.updateStatus', $delivery), [
                'delivery_status' => 'out_for_delivery',
            ]);

        $responseTransit->assertStatus(302);
        $this->assertEquals('out_for_delivery', $delivery->fresh()->delivery_status);
        $this->assertEquals(Order::STATUS_OUT_FOR_DELIVERY, $order->fresh()->order_status);

        // 2. Transition to delivered -> Order completes
        $responseDelivered = $this->actingAs($this->deliveryStaff1)
            ->post(route('admin.deliveries.updateStatus', $delivery), [
                'delivery_status' => 'delivered',
            ]);

        $responseDelivered->assertStatus(302);
        $freshDelivery = $delivery->fresh();
        $this->assertEquals('delivered', $freshDelivery->delivery_status);
        $this->assertNotNull($freshDelivery->delivered_at);

        $freshOrder = $order->fresh();
        $this->assertEquals(Order::STATUS_COMPLETED, $freshOrder->order_status);
        $this->assertEquals('paid', $freshOrder->payment_status);
        $this->assertNotNull($freshOrder->sale);
        $this->assertTrue($freshOrder->payments()->count() > 0);
    }

    public function test_delivery_staff_cannot_view_or_update_other_drivers_deliveries()
    {
        $order = $this->createTestOrder('ready_for_pickup');

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'user_id' => $this->deliveryStaff1->id,
            'recipient_name' => 'Exclusive Driver 1 Package',
            'delivery_address' => 'Driver 1 Route Only',
            'delivery_status' => 'assigned',
            'tracking_number' => 'DEL-ISO-01',
        ]);

        // Driver 2 attempts to view Driver 1's delivery -> 403 Forbidden
        $responseView = $this->actingAs($this->deliveryStaff2)->get(route('admin.deliveries.show', $delivery));
        $responseView->assertStatus(403);

        // Driver 2 attempts to update Driver 1's delivery -> 403 Forbidden
        $responseUpdate = $this->actingAs($this->deliveryStaff2)
            ->post(route('admin.deliveries.updateStatus', $delivery), [
                'delivery_status' => 'delivered',
            ]);
        $responseUpdate->assertStatus(403);
    }

    public function test_admin_can_reassign_driver()
    {
        $order = $this->createTestOrder('ready_for_pickup');

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'user_id' => $this->deliveryStaff1->id,
            'recipient_name' => 'Reassign Package',
            'delivery_address' => 'BKK 2, Phnom Penh',
            'delivery_status' => 'assigned',
            'tracking_number' => 'DEL-REASSIGN-01',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.deliveries.assign', $delivery), [
                'user_id' => $this->deliveryStaff2->id,
            ]);

        $response->assertStatus(302);
        $this->assertEquals($this->deliveryStaff2->id, $delivery->fresh()->user_id);
    }
}
