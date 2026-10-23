<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DatabaseConfigurationTest extends TestCase
{
    /**
     * Test that db:diagnostic runs cleanly and reports status without credentials.
     */
    public function test_db_diagnostic_command_succeeds_without_credentials(): void
    {
        $exitCode = Artisan::call('db:diagnostic');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Database Diagnostic Check', $output);
        $this->assertStringContainsString('Connection', $output);
        $this->assertStringContainsString('Host', $output);
        $this->assertStringContainsString('Database', $output);
        $this->assertStringContainsString('Status', $output);
        $this->assertStringContainsString('Connection successful', $output);

        // Verify that no passwords or secrets are output
        $this->assertStringNotContainsString('password', strtolower($output));
        $this->assertStringNotContainsString('secret', strtolower($output));
    }

    /**
     * Test that db:diagnostic --json outputs valid JSON and contains required keys without secrets.
     */
    public function test_db_diagnostic_json_output_structure(): void
    {
        $exitCode = Artisan::call('db:diagnostic', ['--json' => true]);
        $output = trim(Artisan::output());

        $this->assertSame(0, $exitCode);
        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('connection', $data);
        $this->assertArrayHasKey('host', $data);
        $this->assertArrayHasKey('database', $data);
        $this->assertArrayHasKey('connected', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertTrue($data['connected']);

        // Ensure no passwords or raw credentials are leaked
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('url', $data);
    }

    /**
     * Test that pgsql configuration parser extracts URL components and removes raw url key.
     */
    public function test_configuration_url_parser_extracts_components_without_raw_url(): void
    {
        $sampleUrl = 'postgres://render_user:masked_secret_123@dpg-abc123-a.oregon-postgres.render.com:5432/render_bakery?sslmode=require';
        $parser = new \Illuminate\Support\ConfigurationUrlParser;
        $parsed = $parser->parseConfiguration($sampleUrl);

        $this->assertSame('render_user', $parsed['username']);
        $this->assertSame('masked_secret_123', $parsed['password']);
        $this->assertSame('dpg-abc123-a.oregon-postgres.render.com', $parsed['host']);
        $this->assertSame(5432, $parsed['port']);
        $this->assertSame('render_bakery', $parsed['database']);
        $this->assertSame('require', $parsed['sslmode']);
        $this->assertArrayNotHasKey('url', $parsed);
    }

    /**
     * Test that error sanitization in diagnostic command scrubs credentials and passwords.
     */
    public function test_sanitization_removes_credentials_from_messages(): void
    {
        $command = new \App\Console\Commands\DatabaseDiagnosticCommand;
        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('sanitizeErrorMessage');
        $method->setAccessible(true);

        $rawError = 'SQLSTATE[08006] [7] connection failed: postgres://app_user:super_secret_pw@db.render.com:5432/my_db password=super_secret_pw';
        $sanitized = $method->invoke($command, $rawError);

        $this->assertStringNotContainsString('super_secret_pw', $sanitized);
        $this->assertStringContainsString(':***@', $sanitized);
        $this->assertStringContainsString('password=***', $sanitized);
    }

    /**
     * Test that error sanitization also scrubs known passwords and raw URLs.
     */
    public function test_sanitization_removes_known_password_and_raw_url(): void
    {
        $command = new \App\Console\Commands\DatabaseDiagnosticCommand;
        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('sanitizeErrorMessage');
        $method->setAccessible(true);

        $rawUrl = 'postgres://admin:top_secret_pass@db.internal:5432/my_bakery';
        $knownPassword = 'top_secret_pass';
        $message = "Failed to connect using {$rawUrl} with password {$knownPassword}";

        $sanitized = $method->invoke($command, $message, $knownPassword, $rawUrl);

        $this->assertStringNotContainsString('top_secret_pass', $sanitized);
        $this->assertStringNotContainsString($rawUrl, $sanitized);
        $this->assertStringContainsString('[REDACTED_DATABASE_URL]', $sanitized);
        $this->assertStringContainsString('***', $sanitized);
    }

    /**
     * Test that db:diagnostic handles non-existent or failing connections safely.
     */
    public function test_db_diagnostic_fails_safely_on_invalid_connection(): void
    {
        $exitCode = Artisan::call('db:diagnostic', ['--database' => 'invalid_connection_name']);
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Database Diagnostic Check Failed', $output);
        $this->assertStringContainsString('Connection', $output);
        $this->assertStringContainsString('invalid_connection_name', $output);
        $this->assertStringContainsString('Connection failed', $output);

        $this->assertStringNotContainsString('password', strtolower($output));
    }

    /**
     * Test that docker entrypoint uses safe db:diagnostic and does not call db:show.
     */
    public function test_docker_entrypoint_uses_db_diagnostic_and_not_db_show(): void
    {
        $entrypointPath = base_path('docker/entrypoint.sh');
        $this->assertFileExists($entrypointPath);

        $content = file_get_contents($entrypointPath);

        $this->assertStringContainsString('php artisan db:diagnostic', $content);
        $this->assertStringNotContainsString('php artisan db:show', $content);
    }

    /**
     * Test that config/database.php pgsql does not fallback to 127.0.0.1 or root in production.
     */
    public function test_database_config_does_not_fallback_to_root_or_laravel(): void
    {
        $originalAppEnv = $_ENV['APP_ENV'] ?? null;
        $originalDbHost = $_ENV['DB_HOST'] ?? null;
        $originalDbDatabase = $_ENV['DB_DATABASE'] ?? null;
        $originalDbUser = $_ENV['DB_USERNAME'] ?? null;
        $originalDbUrl = $_ENV['DATABASE_URL'] ?? null;

        try {
            putenv('APP_ENV=production');
            $_ENV['APP_ENV'] = 'production';
            $_SERVER['APP_ENV'] = 'production';

            putenv('DATABASE_URL');
            unset($_ENV['DATABASE_URL'], $_SERVER['DATABASE_URL']);

            putenv('DB_URL');
            unset($_ENV['DB_URL'], $_SERVER['DB_URL']);

            putenv('DB_HOST');
            unset($_ENV['DB_HOST'], $_SERVER['DB_HOST']);

            putenv('DB_DATABASE');
            unset($_ENV['DB_DATABASE'], $_SERVER['DB_DATABASE']);

            putenv('DB_USERNAME');
            unset($_ENV['DB_USERNAME'], $_SERVER['DB_USERNAME']);

            $config = require base_path('config/database.php');
            $pgsqlConfig = $config['connections']['pgsql'];

            $this->assertIsArray($pgsqlConfig);
            $this->assertNull($pgsqlConfig['host'], 'Host should be null in production when not configured');
            $this->assertNull($pgsqlConfig['database'], 'Database should be null in production when not configured');
            $this->assertNull($pgsqlConfig['username'], 'Username should be null in production when not configured');
            $this->assertNotSame('127.0.0.1', $pgsqlConfig['host']);
            $this->assertNotSame('laravel', $pgsqlConfig['database']);
            $this->assertNotSame('root', $pgsqlConfig['username']);
            $this->assertArrayNotHasKey('url', $pgsqlConfig);
        } finally {
            if ($originalAppEnv !== null) {
                putenv("APP_ENV={$originalAppEnv}");
                $_ENV['APP_ENV'] = $originalAppEnv;
                $_SERVER['APP_ENV'] = $originalAppEnv;
            }
            if ($originalDbHost !== null) {
                putenv("DB_HOST={$originalDbHost}");
                $_ENV['DB_HOST'] = $originalDbHost;
                $_SERVER['DB_HOST'] = $originalDbHost;
            }
            if ($originalDbDatabase !== null) {
                putenv("DB_DATABASE={$originalDbDatabase}");
                $_ENV['DB_DATABASE'] = $originalDbDatabase;
                $_SERVER['DB_DATABASE'] = $originalDbDatabase;
            }
            if ($originalDbUser !== null) {
                putenv("DB_USERNAME={$originalDbUser}");
                $_ENV['DB_USERNAME'] = $originalDbUser;
                $_SERVER['DB_USERNAME'] = $originalDbUser;
            }
            if ($originalDbUrl !== null) {
                putenv("DATABASE_URL={$originalDbUrl}");
                $_ENV['DATABASE_URL'] = $originalDbUrl;
                $_SERVER['DATABASE_URL'] = $originalDbUrl;
            }
        }
    }

    /**
     * Test that admin:create command safely provisions an administrator account without leaking passwords.
     */
    public function test_admin_create_command_provisions_account_safely(): void
    {
        Artisan::call('migrate');

        $exitCode = Artisan::call('admin:create', [
            '--name'     => 'Production Admin',
            '--username' => 'prod_admin',
            '--email'    => 'prod_admin@bakery.com',
            '--password' => 'SecurePass#2026',
        ]);

        $this->assertSame(0, $exitCode);
        $output = Artisan::output();

        $this->assertStringContainsString('Administrator Account Ready', $output);
        $this->assertStringContainsString('prod_admin', $output);
        $this->assertStringContainsString('prod_admin@bakery.com', $output);
        $this->assertStringNotContainsString('SecurePass#2026', $output);

        $this->assertDatabaseHas('users', [
            'username' => 'prod_admin',
            'email'    => 'prod_admin@bakery.com',
            'role'     => 'admin',
            'status'   => 'active',
        ]);
    }
}

