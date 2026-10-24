<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'admin:bootstrap')]
class BootstrapAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:bootstrap
                            {--name= : Optional override for administrator full name}
                            {--username= : Optional override for administrator username}
                            {--email= : Optional override for administrator email address}
                            {--password= : Optional override for administrator password}
                            {--force : Bypass BOOTSTRAP_ADMIN environment check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely bootstrap the initial production administrator account from environment variables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enabled = filter_var($this->getEnvValue('BOOTSTRAP_ADMIN'), FILTER_VALIDATE_BOOLEAN) || $this->option('force');
        if (! $enabled) {
            $this->error('Admin bootstrap is disabled. Set BOOTSTRAP_ADMIN=true in environment to execute.');
            return self::FAILURE;
        }

        $name = trim((string) ($this->option('name') ?: $this->getEnvValue('BOOTSTRAP_ADMIN_NAME')));
        $username = trim((string) ($this->option('username') ?: $this->getEnvValue('BOOTSTRAP_ADMIN_USERNAME')));
        $email = trim((string) ($this->option('email') ?: $this->getEnvValue('BOOTSTRAP_ADMIN_EMAIL')));
        $password = (string) ($this->option('password') ?: $this->getEnvValue('BOOTSTRAP_ADMIN_PASSWORD'));

        $validator = Validator::make([
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
            'password' => $password,
        ], [
            'name'     => 'required|string|min:1|max:100',
            'username' => 'required|string|min:3|max:50',
            'email'    => 'required|email|max:100',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Admin bootstrap validation failed: ' . implode(' ', $validator->errors()->all()));
            return self::FAILURE;
        }

        $connectionName = config('database.default');
        $databaseName = config("database.connections.{$connectionName}.database");

        try {
            // Check for existing accounts
            $userByUsername = User::whereRaw('LOWER(username) = ?', [strtolower($username)])->first();
            $userByEmail = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

            // Case 1: Exact bootstrap admin already exists (Idempotent success)
            if ($userByUsername && $userByEmail && $userByUsername->id === $userByEmail->id) {
                if ($userByUsername->role === 'admin') {
                    $this->components->info("Bootstrap administrator account '{$username}' ({$email}) already exists. No account was modified.");
                    $this->components->twoColumnDetail('Connection', (string) $connectionName);
                    $this->components->twoColumnDetail('Database', (string) $databaseName);
                    $this->components->twoColumnDetail('Status', '<fg=yellow>unchanged (idempotent)</>');
                    return self::SUCCESS;
                }

                $this->error("Account collision: User '{$username}' ({$email}) exists with non-admin role '{$userByUsername->role}'. Existing accounts cannot be modified or promoted.");
                return self::FAILURE;
            }

            // Case 2: Username or email is already taken by another account (Safety conflict)
            if ($userByUsername || $userByEmail) {
                $conflictField = $userByUsername ? "username '{$username}'" : "email '{$email}'";
                $this->error("Account collision: An account with {$conflictField} already exists. Existing accounts cannot be modified or overwritten.");
                return self::FAILURE;
            }

            // Case 3: Create new administrator within a database transaction
            DB::transaction(function () use ($name, $username, $email, $password) {
                User::create([
                    'name'                 => $name,
                    'username'             => $username,
                    'email'                => $email,
                    'password'             => Hash::make($password),
                    'role'                 => 'admin',
                    'status'               => 'active',
                    'must_change_password' => false,
                ]);
            });

            $this->components->info("Bootstrap administrator account '{$username}' ({$email}) created successfully.");
            $this->components->twoColumnDetail('Connection', (string) $connectionName);
            $this->components->twoColumnDetail('Database', (string) $databaseName);
            $this->components->twoColumnDetail('Username', (string) $username);
            $this->components->twoColumnDetail('Email', (string) $email);
            $this->components->twoColumnDetail('Role', 'admin');
            $this->components->twoColumnDetail('Status', '<fg=green>active</>');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $sanitizedMessage = $this->sanitizeErrorMessage($e->getMessage(), $password);
            $this->error("Admin bootstrap failed: {$sanitizedMessage}");
            return self::FAILURE;
        }
    }

    /**
     * Retrieve environment variable safely across all environments and configurations.
     */
    protected function getEnvValue(string $key): ?string
    {
        $val = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);
        if ($val !== false && $val !== null && $val !== '') {
            return (string) $val;
        }

        $envVal = env($key);
        return $envVal !== null ? (string) $envVal : null;
    }

    /**
     * Ensure passwords, connection strings, or secrets are redacted from error output.
     */
    protected function sanitizeErrorMessage(string $message, ?string $knownPassword = null): string
    {
        $sanitized = $message;

        if (! empty($knownPassword) && is_string($knownPassword)) {
            $sanitized = str_replace($knownPassword, '***', $sanitized);
        }

        $sanitized = (string) preg_replace('/(:\/\/[^:]+:)[^@\s]+(@)/', '$1***$2', $sanitized);
        $sanitized = (string) preg_replace('/:[^:@\s]+@/', ':***@', $sanitized);
        $sanitized = (string) preg_replace('/(password|pwd)(\s*[:=]\s*)(["\']?)[^\s;,\'"]+\3/i', '$1$2***', $sanitized);

        return trim((string) preg_replace('/\s+/', ' ', $sanitized));
    }
}
