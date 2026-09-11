<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationDeliveryReminderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $driver;
    protected Order $order;
    protected NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(NotificationService::class);

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_notif_test'],
            [
                'name' => 'Admin Notif',
                'email' => 'admin_notif@test.com',
                'password' => Hash::make('pass123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $this->driver = User::firstOrCreate(
            ['username' => 'driver_notif_test'],
            [
                'name' => 'Driver Notif',
                'email' => 'driver_notif@test.com',
                'password' => Hash::make('pass123'),
                'role' => 'delivery_staff',
                'status' => 'active',
            ]
        );

        $this->order = Order::create([
            'order_number' => 'ORD-NOTIF-001',
            'customer_name' => 'John Recipient',
            'customer_phone' => '+855 12 999 888',
            'delivery_address' => 'St 240, Phnom Penh',
            'order_type' => 'delivery',
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 30.00,
            'tax_amount' => 0.00,
            'total_amount' => 30.00,
            'order_date' => now(),
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_notify_delivery_reminder_dispatches_notifications()
    {
        $delivery = Delivery::create([
            'order_id' => $this->order->id,
            'user_id' => $this->driver->id,
            'recipient_name' => 'John Recipient',
            'recipient_phone' => '+855 12 999 888',
            'delivery_address' => 'St 240, Phnom Penh',
            'scheduled_at' => Carbon::today()->toDateString(),
            'delivery_status' => Delivery::STATUS_ASSIGNED,
            'notes' => 'Ring the doorbell',
        ]);

        $this->service->notifyDeliveryReminder($delivery);

        // Should notify admin
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'delivery',
            'severity' => 'warning',
        ]);

        // Should notify driver directly
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->driver->id,
            'type' => 'delivery',
            'severity' => 'warning',
        ]);
    }

    public function test_sync_condition_alerts_includes_deliveries_and_deduplicates()
    {
        $delivery = Delivery::create([
            'order_id' => $this->order->id,
            'recipient_name' => 'Alice Recipient',
            'recipient_phone' => '+855 12 777 666',
            'delivery_address' => 'Russian Market, Phnom Penh',
            'scheduled_at' => Carbon::today()->toDateString(),
            'delivery_status' => Delivery::STATUS_PENDING,
        ]);

        $counts = $this->service->syncConditionAlerts();

        $this->assertArrayHasKey('deliveries', $counts);
        $this->assertGreaterThanOrEqual(1, $counts['deliveries']);

        $initialCount = Notification::where('type', 'delivery')->count();
        $this->assertGreaterThanOrEqual(1, $initialCount);

        // Running sync again on the same day should not produce duplicate unread rows
        $this->service->syncConditionAlerts();
        $afterCount = Notification::where('type', 'delivery')->count();

        $this->assertEquals($initialCount, $afterCount);
    }
}
