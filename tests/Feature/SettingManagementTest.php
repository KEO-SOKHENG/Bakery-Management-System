<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $cashier;
    private User $baker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@bakery.com',
            'username' => 'admin',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->manager = User::factory()->create([
            'name'     => 'Manager User',
            'email'    => 'manager@bakery.com',
            'username' => 'manager',
            'role'     => 'manager',
            'status'   => 'active',
        ]);

        $this->cashier = User::factory()->create([
            'name'     => 'Cashier User',
            'email'    => 'cashier@bakery.com',
            'username' => 'cashier',
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        $this->baker = User::factory()->create([
            'name'     => 'Baker User',
            'email'    => 'baker@bakery.com',
            'username' => 'baker',
            'role'     => 'baker',
            'status'   => 'active',
        ]);

        SettingsService::clearCache();
    }

    /**
     * 1. Admin can access Settings.
     */
    public function test_admin_can_access_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings');
        $response->assertSee('System Settings');
        $response->assertSee('Shop Information');
        $response->assertSee('Tax & Discount', false);
        $response->assertSee('Business Hours & Operations', false);
        $response->assertSee('System Backup & Restore', false);
    }

    /**
     * 2. Unauthorized users cannot modify Settings (Cashier, Baker).
     */
    public function test_unauthorized_users_cannot_modify_settings(): void
    {
        // Web request: redirected to user's dashboard
        $response = $this->actingAs($this->cashier)->get(route('admin.settings'));
        $response->assertRedirect(route('cashier.dashboard'));

        $postResponse = $this->actingAs($this->cashier)->post(route('admin.settings.update'), [
            'shop_name' => 'Hacked Bakery',
        ]);
        $postResponse->assertRedirect(route('cashier.dashboard'));

        // JSON/AJAX request: returns 403 Forbidden
        $ajaxResponse = $this->actingAs($this->baker)->json('POST', route('admin.settings.update'), [
            'shop_name' => 'Hacked Bakery',
        ]);
        $ajaxResponse->assertStatus(403);
    }

    /**
     * 3. Manager without permission cannot modify settings.
     */
    public function test_manager_without_manage_permission_cannot_modify_settings(): void
    {
        // Manager by default has settings.view but not settings.manage
        $response = $this->actingAs($this->manager)->get(route('admin.settings'));
        $response->assertStatus(200);
        $response->assertSee('View-only mode');

        $updateResponse = $this->actingAs($this->manager)->json('POST', route('admin.settings.update'), [
            'tax_rate' => 15.0,
        ]);
        $updateResponse->assertStatus(403);
    }

    /**
     * 4. Admin can update bakery information.
     */
    public function test_admin_can_update_bakery_information(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'shop_name'    => 'Artisan Golden Loaf',
            'shop_phone'   => '+855 99 888 777',
            'shop_email'   => 'contact@goldenloaf.com',
            'shop_address' => 'Street 240, Phnom Penh, Cambodia',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Artisan Golden Loaf', SettingsService::get('shop_name'));
        $this->assertEquals('Artisan Golden Loaf', SettingsService::get('bakery_name'));
        $this->assertEquals('contact@goldenloaf.com', SettingsService::get('shop_email'));
    }

    /**
     * 5. Admin can update tax rate.
     */
    public function test_admin_can_update_tax_rate(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'tax_rate' => 12.5,
            'discount' => 8.0,
        ]);

        $response->assertRedirect();
        $this->assertEquals(12.5, SettingsService::getFloat('tax_rate'));
        $this->assertEquals(12.5, SettingsService::getFloat('tax_percentage'));
    }

    /**
     * 6. Admin can update currency.
     */
    public function test_admin_can_update_currency(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'currency' => 'KHR',
        ]);

        $response->assertRedirect();
        $this->assertEquals('KHR', SettingsService::getString('currency'));
        $this->assertEquals('៛', SettingsService::getCurrencySymbol());

        $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'currency' => 'USD',
        ]);
        $this->assertEquals('$', SettingsService::getCurrencySymbol());
    }

    /**
     * 7. Admin can update business hours.
     */
    public function test_admin_can_update_business_hours(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'opening_time' => '06:30',
            'closing_time' => '21:30',
        ]);

        $response->assertRedirect();
        $this->assertEquals('06:30', SettingsService::getString('opening_time'));
        $this->assertEquals('21:30', SettingsService::getString('closing_time'));
    }

    /**
     * 8. Invalid tax rate is rejected.
     */
    public function test_invalid_tax_rate_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'tax_rate' => -5.0, // Negative tax
        ]);

        $response->assertSessionHasErrors('tax_rate');

        $response2 = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'tax_rate' => 150.0, // Exceeds 100%
        ]);

        $response2->assertSessionHasErrors('tax_rate');
    }

    /**
     * 9. Invalid currency is rejected.
     */
    public function test_invalid_currency_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'currency' => 'BITCOIN', // Invalid currency
        ]);

        $response->assertSessionHasErrors('currency');
    }

    /**
     * 10. Invalid time is rejected.
     */
    public function test_invalid_time_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'opening_time' => '25:99', // Invalid time format
        ]);

        $response->assertSessionHasErrors('opening_time');
    }

    /**
     * 11. Settings persist correctly in database.
     */
    public function test_settings_persist_correctly_in_database(): void
    {
        SettingsService::set('shop_name', 'Baguette Haven');

        $this->assertDatabaseHas('settings', [
            'key'   => 'shop_name',
            'value' => 'Baguette Haven',
        ]);

        // Verify alias sync in database
        $this->assertDatabaseHas('settings', [
            'key'   => 'bakery_name',
            'value' => 'Baguette Haven',
        ]);
    }

    /**
     * 12. Settings service returns correct typed values.
     */
    public function test_settings_service_returns_correct_typed_values(): void
    {
        SettingsService::set('tax_rate', '12.5');
        SettingsService::set('expiry_warning_days', '5');
        SettingsService::set('notifications_enabled', '1');

        $this->assertSame(12.5, SettingsService::getFloat('tax_rate'));
        $this->assertSame(5, SettingsService::getInt('expiry_warning_days'));
        $this->assertTrue(SettingsService::getBool('notifications_enabled'));
        $this->assertSame('12.5', SettingsService::getString('tax_rate'));
    }

    /**
     * 13. Cache is refreshed/invalidated after update.
     */
    public function test_cache_is_refreshed_after_update(): void
    {
        SettingsService::set('shop_name', 'Initial Bakery');
        $this->assertEquals('Initial Bakery', SettingsService::get('shop_name'));

        // Update via controller
        $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'shop_name' => 'Updated Cached Bakery',
        ]);

        // Immediate subsequent call must return fresh value without reboot
        $this->assertEquals('Updated Cached Bakery', SettingsService::get('shop_name'));
    }

    /**
     * 14. POS uses configured tax rate.
     */
    public function test_pos_uses_configured_tax_rate(): void
    {
        SettingsService::set('tax_percentage', '15.0');

        $cat = Category::create(['name' => 'Breads', 'slug' => 'breads', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $cat->id,
            'name'        => 'French Baguette',
            'slug'        => 'french-baguette',
            'price'       => 10.00,
            'cost'        => 3.00,
            'stock'       => 50,
            'status'      => 'active',
        ]);

        $response = $this->actingAs($this->cashier)->json('POST', route('pos.checkout'), [
            'payment_method' => 'cash',
            'items'          => [
                ['product_id' => $product->id, 'quantity' => 2], // subtotal = 20.00
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // 20.00 * 15% = 3.00 tax -> total = 23.00
        $this->assertEquals('3.00', $data['receipt']['tax']);
        $this->assertEquals('23.00', $data['receipt']['grand_total']);
    }

    /**
     * 15. POS uses configured currency.
     */
    public function test_pos_uses_configured_currency(): void
    {
        SettingsService::set('currency', 'KHR');

        $response = $this->actingAs($this->cashier)->get(route('pos'));
        $response->assertStatus(200);
        $response->assertViewHas('currency', '៛');
    }

    /**
     * 16. Notification expiry threshold uses configured setting.
     */
    public function test_notification_expiry_threshold_uses_configured_setting(): void
    {
        // Set expiry warning window to 5 days
        SettingsService::set('expiry_warning_days', '5');

        $ingredient = Ingredient::create([
            'name'             => 'Fresh Cream',
            'unit'             => 'liters',
            'quantity'         => 10,
            'minimum_quantity' => 2,
            'cost'             => 4.00,
            'expiry_date'      => Carbon::today()->addDays(4)->format('Y-m-d'), // within 5 days
        ]);

        $service = app(NotificationService::class);
        $counts = $service->syncConditionAlerts();

        $this->assertGreaterThanOrEqual(1, $counts['expiring']);
        $this->assertDatabaseHas('notifications', [
            'type'      => 'expiring',
            'dedup_key' => "expiring_5days:ingredient:{$ingredient->id}",
        ]);
    }

    /**
     * 17. Notification settings do not create duplicate alerts.
     */
    public function test_notification_settings_do_not_create_duplicate_alerts(): void
    {
        SettingsService::set('expiry_warning_days', '3');

        $ingredient = Ingredient::create([
            'name'             => 'Butter',
            'unit'             => 'kg',
            'quantity'         => 15,
            'minimum_quantity' => 2,
            'cost'             => 5.00,
            'expiry_date'      => Carbon::today()->addDays(2)->format('Y-m-d'),
        ]);

        $service = app(NotificationService::class);

        // Run sync multiple times
        $service->syncConditionAlerts();
        $service->syncConditionAlerts();
        $service->syncConditionAlerts();

        $alertCount = Notification::where('dedup_key', "expiring_3days:ingredient:{$ingredient->id}")->count();
        $targetUserCount = User::whereIn('role', ['admin', 'manager', 'baker'])->count();

        $this->assertEquals($targetUserCount, $alertCount);
    }

    /**
     * 18. Historical sales are not recalculated when current tax changes.
     */
    public function test_historical_sales_are_not_recalculated_when_tax_changes(): void
    {
        // 1. Create order at 10% tax
        SettingsService::set('tax_percentage', '10.0');

        $cat = Category::create(['name' => 'Pastries', 'slug' => 'pastries', 'status' => 'active']);
        $prod = Product::create([
            'category_id' => $cat->id,
            'name'        => 'Croissant',
            'slug'        => 'croissant',
            'price'       => 10.00,
            'cost'        => 2.00,
            'stock'       => 20,
            'status'      => 'active',
        ]);

        $this->actingAs($this->cashier)->json('POST', route('pos.checkout'), [
            'payment_method' => 'cash',
            'items'          => [['product_id' => $prod->id, 'quantity' => 1]],
        ]);

        $order = Order::latest()->first();
        $this->assertEquals(1.00, (float) $order->tax);
        $this->assertEquals(11.00, (float) $order->total);

        // 2. Change tax rate to 20%
        SettingsService::set('tax_percentage', '20.0');

        // Verify historical order records remain unchanged
        $order->refresh();
        $this->assertEquals(1.00, (float) $order->tax);
        $this->assertEquals(11.00, (float) $order->total);
    }

    /**
     * 19. Audit logs are created for setting changes.
     */
    public function test_audit_logs_are_created_for_setting_changes(): void
    {
        $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'shop_name' => 'Audited Bakery Name',
            'tax_rate'  => 8.5,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'settings_update',
        ]);
    }

    /**
     * 20. Unauthorized backup/restore is blocked.
     */
    public function test_unauthorized_backup_and_restore_is_blocked(): void
    {
        // Manager blocked
        $resManager = $this->actingAs($this->manager)->post(route('admin.settings.backup'));
        $resManager->assertRedirect(); // CheckRole redirects non-admin

        // Cashier blocked
        $resCashier = $this->actingAs($this->cashier)->post(route('admin.settings.backup'));
        $resCashier->assertRedirect();

        // Cashier JSON blocked
        $resJson = $this->actingAs($this->cashier)->json('POST', route('admin.settings.backup'));
        $resJson->assertStatus(403);
    }

    /**
     * 21. Backup behavior works and safe restore succeeds.
     */
    public function test_backup_generation_and_safe_restore_works(): void
    {
        // 1. Admin generates backup
        $backupResponse = $this->actingAs($this->admin)->post(route('admin.settings.backup'));
        $backupResponse->assertStatus(200);
        $backupResponse->assertHeader('content-type', 'application/json');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'backup_created',
        ]);

        // 2. Prepare verified backup file payload
        $restorePayload = [
            'system'   => 'Bakery Management System',
            'version'  => '1.0',
            'settings' => [
                'shop_name'           => 'Restored Heritage Bakery',
                'currency'            => 'KHR',
                'tax_rate'            => '7.5',
                'expiry_warning_days' => '4',
            ],
        ];

        $uploadedFile = UploadedFile::fake()->createWithContent(
            'restore_test.json',
            json_encode($restorePayload)
        );

        // 3. Restore without confirmation checkbox is rejected
        $failedResponse = $this->actingAs($this->admin)->post(route('admin.settings.restore'), [
            'backup_file' => $uploadedFile,
        ]);
        $failedResponse->assertSessionHasErrors('confirm_restore');

        // 4. Restore with confirmation succeeds
        $successResponse = $this->actingAs($this->admin)->post(route('admin.settings.restore'), [
            'backup_file'     => $uploadedFile,
            'confirm_restore' => '1',
        ]);

        $successResponse->assertRedirect();
        $successResponse->assertSessionHas('success');

        $this->assertEquals('Restored Heritage Bakery', SettingsService::get('shop_name'));
        $this->assertEquals('KHR', SettingsService::get('currency'));
        $this->assertEquals(7.5, SettingsService::getFloat('tax_rate'));
        $this->assertEquals(4, SettingsService::getInt('expiry_warning_days'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'backup_restored',
        ]);
    }
}
