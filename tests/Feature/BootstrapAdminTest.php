<?php

namespace Tests\Feature;

use App\Console\Commands\BootstrapAdminCommand;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BootstrapAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    protected function tearDown(): void
    {
        $this->clearBootstrapEnv();
        parent::tearDown();
    }

    protected function setBootstrapEnv(array $vars): void
    {
        foreach ($vars as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    protected function clearBootstrapEnv(): void
    {
        $keys = [
            'BOOTSTRAP_ADMIN',
            'BOOTSTRAP_ADMIN_NAME',
            'BOOTSTRAP_ADMIN_USERNAME',
            'BOOTSTRAP_ADMIN_EMAIL',
            'BOOTSTRAP_ADMIN_PASSWORD',
        ];

        foreach ($keys as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }

    /**
     * Test bootstrap command fails when BOOTSTRAP_ADMIN is disabled and --force is not provided.
     */
    public function test_bootstrap_fails_when_disabled(): void
    {
        $this->setBootstrapEnv(['BOOTSTRAP_ADMIN' => 'false']);

        $exitCode = Artisan::call('admin:bootstrap');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Admin bootstrap is disabled', $output);
    }

    /**
     * Test validation failure when required environment variables are missing.
     */
    public function test_bootstrap_fails_on_missing_required_variables(): void
    {
        $this->setBootstrapEnv([
            'BOOTSTRAP_ADMIN' => 'true',
            'BOOTSTRAP_ADMIN_NAME' => 'Admin User',
            // Missing username, email, password
        ]);

        $exitCode = Artisan::call('admin:bootstrap');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Admin bootstrap validation failed', $output);
    }

    /**
     * Test validation failure when password is shorter than 8 characters.
     */
    public function test_bootstrap_fails_on_short_password(): void
    {
        $this->setBootstrapEnv([
            'BOOTSTRAP_ADMIN'          => 'true',
            'BOOTSTRAP_ADMIN_NAME'     => 'Admin User',
            'BOOTSTRAP_ADMIN_USERNAME' => 'secure_admin',
            'BOOTSTRAP_ADMIN_EMAIL'    => 'admin@bakery.com',
            'BOOTSTRAP_ADMIN_PASSWORD' => 'short',
        ]);

        $exitCode = Artisan::call('admin:bootstrap');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('password field must be at least 8 characters', $output);
    }

    /**
     * Test successful administrator creation from environment variables.
     */
    public function test_bootstrap_creates_admin_successfully(): void
    {
        $plainPassword = 'ProductionSecurePassword#2026!';

        $this->setBootstrapEnv([
            'BOOTSTRAP_ADMIN'          => 'true',
            'BOOTSTRAP_ADMIN_NAME'     => 'Root Administrator',
            'BOOTSTRAP_ADMIN_USERNAME' => 'root_admin',
            'BOOTSTRAP_ADMIN_EMAIL'    => 'root_admin@bakery.com',
            'BOOTSTRAP_ADMIN_PASSWORD' => $plainPassword,
        ]);

        $exitCode = Artisan::call('admin:bootstrap');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('created successfully', $output);
        $this->assertStringContainsString('root_admin', $output);
        $this->assertStringContainsString('root_admin@bakery.com', $output);
        $this->assertStringNotContainsString($plainPassword, $output);

        $user = User::where('username', 'root_admin')->first();
        $this->assertNotNull($user);
        $this->assertSame('Root Administrator', $user->name);
        $this->assertSame('root_admin@bakery.com', $user->email);
        $this->assertSame('admin', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertFalse((bool) $user->must_change_password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    /**
     * Test idempotency: re-running bootstrap with the same credentials succeeds without changes.
     */
    public function test_bootstrap_is_idempotent_for_existing_admin(): void
    {
        $plainPassword = 'InitialSecurePassword#2026!';

        $this->setBootstrapEnv([
            'BOOTSTRAP_ADMIN'          => 'true',
            'BOOTSTRAP_ADMIN_NAME'     => 'Idempotent Admin',
            'BOOTSTRAP_ADMIN_USERNAME' => 'idem_admin',
            'BOOTSTRAP_ADMIN_EMAIL'    => 'idem_admin@bakery.com',
            'BOOTSTRAP_ADMIN_PASSWORD' => $plainPassword,
        ]);

        // First run: creation
        $this->assertSame(0, Artisan::call('admin:bootstrap'));
        $firstUser = User::where('username', 'idem_admin')->first();
        $originalPasswordHash = $firstUser->password;

        // Second run: idempotent check
        $exitCode = Artisan::call('admin:bootstrap');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('already exists. No account was modified', $output);

        $reloadedUser = User::where('username', 'idem_admin')->first();
        $this->assertSame($firstUser->id, $reloadedUser->id);
        $this->assertSame($originalPasswordHash, $reloadedUser->password);
    }

    /**
     * Test collision fails safely when an account with matching username exists with a different email.
     */
    public function test_bootstrap_fails_safely_on_conflicting_username(): void
    {
        User::create([
            'name'                 => 'Existing User',
            'username'             => 'conflict_user',
            'email'                => 'existing_email@bakery.com',
            'password'             => Hash::make('ExistingPassword#123'),
            'role'                 => 'cashier',
            'status'               => 'active',
            'must_change_password' => false,
        ]);

        $this->setBootstrapEnv([
            'BOOTSTRAP_ADMIN'          => 'true',
            'BOOTSTRAP_ADMIN_NAME'     => 'Bootstrap Attempt',
            'BOOTSTRAP_ADMIN_USERNAME' => 'conflict_user',
            'BOOTSTRAP_ADMIN_EMAIL'    => 'different_email@bakery.com',
            'BOOTSTRAP_ADMIN_PASSWORD' => 'NewPassword#9999',
        ]);

        $exitCode = Artisan::call('admin:bootstrap');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Account collision', $output);

        // Verify existing user was untouched
        $existing = User::where('username', 'conflict_user')->first();
        $this->assertSame('existing_email@bakery.com', $existing->email);
        $this->assertSame('cashier', $existing->role);
        $this->assertTrue(Hash::check('ExistingPassword#123', $existing->password));
    }

    /**
     * Test collision fails safely when an account with matching email exists with a different username.
     */
    public function test_bootstrap_fails_safely_on_conflicting_email(): void
    {
        User::create([
            'name'                 => 'Email Owner',
            'username'             => 'original_username',
            'email'                => 'shared_email@bakery.com',
            'password'             => Hash::make('OriginalPassword#123'),
            'role'                 => 'baker',
            'status'               => 'active',
            'must_change_password' => false,
        ]);

        $this->setBootstrapEnv([
            'BOOTSTRAP_ADMIN'          => 'true',
            'BOOTSTRAP_ADMIN_NAME'     => 'Bootstrap Attempt',
            'BOOTSTRAP_ADMIN_USERNAME' => 'different_username',
            'BOOTSTRAP_ADMIN_EMAIL'    => 'shared_email@bakery.com',
            'BOOTSTRAP_ADMIN_PASSWORD' => 'NewPassword#9999',
        ]);

        $exitCode = Artisan::call('admin:bootstrap');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Account collision', $output);

        // Verify existing user was untouched
        $existing = User::where('email', 'shared_email@bakery.com')->first();
        $this->assertSame('original_username', $existing->username);
        $this->assertSame('baker', $existing->role);
        $this->assertTrue(Hash::check('OriginalPassword#123', $existing->password));
    }

    /**
     * Test collision fails safely and never promotes an existing non-admin user with exact username and email.
     */
    public function test_bootstrap_never_promotes_existing_non_admin_account(): void
    {
        User::create([
            'name'                 => 'Staff Member',
            'username'             => 'staff_member',
            'email'                => 'staff@bakery.com',
            'password'             => Hash::make('StaffPassword#123'),
            'role'                 => 'cashier',
            'status'               => 'active',
            'must_change_password' => false,
        ]);

        $this->setBootstrapEnv([
            'BOOTSTRAP_ADMIN'          => 'true',
            'BOOTSTRAP_ADMIN_NAME'     => 'Staff Member',
            'BOOTSTRAP_ADMIN_USERNAME' => 'staff_member',
            'BOOTSTRAP_ADMIN_EMAIL'    => 'staff@bakery.com',
            'BOOTSTRAP_ADMIN_PASSWORD' => 'NewStaffAdminPass#123',
        ]);

        $exitCode = Artisan::call('admin:bootstrap');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('exists with non-admin role', $output);
        $this->assertStringContainsString('cannot be modified or promoted', $output);

        $staff = User::where('username', 'staff_member')->first();
        $this->assertSame('cashier', $staff->role);
        $this->assertTrue(Hash::check('StaffPassword#123', $staff->password));
    }

    /**
     * Test error message sanitization redacts sensitive passwords and connection URLs.
     */
    public function test_error_message_sanitization_redacts_credentials(): void
    {
        $command = new BootstrapAdminCommand;
        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('sanitizeErrorMessage');
        $method->setAccessible(true);

        $sensitivePassword = 'SuperSecretBootstrapPassword#2026';
        $rawMessage = "QueryException: postgresql://admin:{$sensitivePassword}@db.render.com/bakery password={$sensitivePassword}";

        $sanitized = $method->invoke($command, $rawMessage, $sensitivePassword);

        $this->assertStringNotContainsString($sensitivePassword, $sanitized);
        $this->assertStringContainsString(':***@', $sanitized);
        $this->assertStringContainsString('password=***', $sanitized);
    }

    /**
     * Test docker/entrypoint.sh contains the admin:bootstrap integration.
     */
    public function test_entrypoint_contains_admin_bootstrap_call(): void
    {
        $entrypointPath = base_path('docker/entrypoint.sh');
        $this->assertFileExists($entrypointPath);

        $content = file_get_contents($entrypointPath);
        $this->assertStringContainsString('[ "${BOOTSTRAP_ADMIN}" = "true" ]', $content);
        $this->assertStringContainsString('php artisan admin:bootstrap', $content);
        $this->assertStringContainsString('exit 1', $content);
    }
}
