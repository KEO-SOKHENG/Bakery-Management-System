<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure baseline users exist for the 4 core roles
        User::factory()->create([
            'username' => 'test_admin',
            'name' => 'Test Admin',
            'email' => 'test_admin@bakery.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::factory()->create([
            'username' => 'test_manager',
            'name' => 'Test Manager',
            'email' => 'test_manager@bakery.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'status' => 'active',
        ]);

        User::factory()->create([
            'username' => 'test_baker',
            'name' => 'Test Baker',
            'email' => 'test_baker@bakery.com',
            'password' => Hash::make('password123'),
            'role' => 'baker',
            'status' => 'active',
        ]);

        User::factory()->create([
            'username' => 'test_cashier',
            'name' => 'Test Cashier',
            'email' => 'test_cashier@bakery.com',
            'password' => Hash::make('password123'),
            'role' => 'cashier',
            'status' => 'active',
        ]);
    }

    /**
     * Test that the login page renders without the role selector tabs or hidden role input.
     */
    public function test_login_page_renders_without_role_selection_ui(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);

        // Required elements
        $response->assertSee('Username / Email');
        $response->assertSee('Password');
        $response->assertSee('Remember Me');
        $response->assertSee('Forgot Password?');
        $response->assertSee('Sign In');

        // Verify role selection UI is completely absent
        $response->assertDontSee('role-selector-tabs');
        $response->assertDontSee('role-tab-btn');
        $response->assertDontSee('role-tab-pointer-pill');
        $response->assertDontSee('id="selected_role"', false);
        $response->assertDontSee('Sign In as Admin');
        $response->assertDontSee('Sign In as Manager');
        $response->assertDontSee('Sign In as Baker');
        $response->assertDontSee('Sign In as Cashier');
    }

    /**
     * Test Admin login redirects to Admin Dashboard without any role parameter.
     */
    public function test_admin_account_redirects_to_admin_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'username' => 'test_admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);
    }

    /**
     * Test Manager login redirects to Manager Dashboard without any role parameter.
     */
    public function test_manager_account_redirects_to_manager_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'username' => 'test_manager@bakery.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('manager.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('manager', auth()->user()->role);
    }

    /**
     * Test Baker login redirects to Production page without any role parameter.
     */
    public function test_baker_account_redirects_to_baker_production(): void
    {
        $response = $this->post(route('login.post'), [
            'username' => 'test_baker',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.production'));
        $this->assertAuthenticated();
        $this->assertEquals('baker', auth()->user()->role);
    }

    /**
     * Test Cashier login redirects to Cashier Dashboard without any role parameter.
     */
    public function test_cashier_account_redirects_to_cashier_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'username' => 'test_cashier@bakery.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('cashier.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('cashier', auth()->user()->role);
    }

    /**
     * Security test: passing a spoofed role parameter does NOT affect the user's role or redirect.
     * The role MUST strictly come from the authenticated user's database record.
     */
    public function test_tampered_role_parameter_is_ignored(): void
    {
        // Cashier attempts to pass role='admin' in payload
        $response = $this->post(route('login.post'), [
            'username' => 'test_cashier',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        // Must still redirect to cashier dashboard, NEVER admin dashboard
        $response->assertRedirect(route('cashier.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('cashier', auth()->user()->role);

        // Verify that cashier is denied access to admin-only dashboard and redirected to cashier dashboard
        $adminDashboardResponse = $this->get(route('admin.dashboard'));
        $adminDashboardResponse->assertRedirect(route('cashier.dashboard'));
        $adminDashboardResponse->assertSessionHas('error');
    }

    /**
     * Test invalid login credentials return validation error.
     */
    public function test_invalid_credentials_fails_with_errors(): void
    {
        $response = $this->post(route('login.post'), [
            'username' => 'test_admin',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * Test authenticated user accessing /login is automatically redirected to their role dashboard.
     */
    public function test_authenticated_user_redirected_from_login_page(): void
    {
        $admin = User::where('username', 'test_admin')->first();
        $this->actingAs($admin);
        $this->get(route('login'))->assertRedirect(route('admin.dashboard'));

        $manager = User::where('username', 'test_manager')->first();
        $this->actingAs($manager);
        $this->get(route('login'))->assertRedirect(route('manager.dashboard'));

        $baker = User::where('username', 'test_baker')->first();
        $this->actingAs($baker);
        $this->get(route('login'))->assertRedirect(route('admin.production'));

        $cashier = User::where('username', 'test_cashier')->first();
        $this->actingAs($cashier);
        $this->get(route('login'))->assertRedirect(route('cashier.dashboard'));
    }
}
