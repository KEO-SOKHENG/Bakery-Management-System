<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'admin:reset-password')]
class ResetAdminPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password
                            {--username= : Administrator username}
                            {--email= : Administrator email address}
                            {--password= : New administrator password}
                            {--require-change= : Force user to change password on login (default: true)}
                            {--force : Bypass RESET_ADMIN_PASSWORD environment check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely reset an existing administrator account password from environment variables without creating accounts or modifying non-admin users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enabled = filter_var($this->getEnvValue('RESET_ADMIN_PASSWORD'), FILTER_VALIDATE_BOOLEAN) || $this->option('force');
        if (! $enabled) {
            $this->error('Admin password reset is disabled. Set RESET_ADMIN_PASSWORD=true in environment to execute.');
            return self::FAILURE;
        }

        $username = trim((string) (
            $this->option('username')
            ?: $this->getEnvValue('RESET_ADMIN_USERNAME')
            ?: $this->getEnvValue('BOOTSTRAP_ADMIN_USERNAME')
        ));

        $email = trim((string) (
            $this->option('email')
            ?: $this->getEnvValue('RESET_ADMIN_EMAIL')
            ?: $this->getEnvValue('BOOTSTRAP_ADMIN_EMAIL')
        ));

        $password = (string) (
            $this->option('password')
            ?: $this->getEnvValue('RESET_ADMIN_NEW_PASSWORD')
            ?: $this->getEnvValue('RESET_ADMIN_PASSWORD_VALUE')
            ?: $this->getEnvValue('BOOTSTRAP_ADMIN_PASSWORD')
        );

        $requireChangeOpt = $this->option('require-change');
        if ($requireChangeOpt !== null && $requireChangeOpt !== '') {
            $mustChange = filter_var($requireChangeOpt, FILTER_VALIDATE_BOOLEAN);
        } else {
            $envMustChange = $this->getEnvValue('RESET_ADMIN_MUST_CHANGE_PASSWORD');
            $mustChange = $envMustChange !== null
                ? filter_var($envMustChange, FILTER_VALIDATE_BOOLEAN)
                : true;
        }

        $validator = Validator::make([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
        ], [
            'username' => 'required|string|min:3|max:50',
            'email'    => 'required|email|max:100',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Admin password reset validation failed: ' . implode(' ', $validator->errors()->all()));
            return self::FAILURE;
        }

        $connectionName = config('database.default');
        $databaseName = config("database.connections.{$connectionName}.database");

        try {
            $userByUsername = User::where('username', $username)->first();
            $userByEmail = User::where('email', $email)->first();

            // Safety Check 1: Account does not exist
            if (! $userByUsername && ! $userByEmail) {
                $this->error("Account not found: No account matches username '{$username}' and email '{$email}'. This command only resets existing administrators and will not create new accounts.");
                return self::FAILURE;
            }

            // Safety Check 2: Username and Email must resolve to the EXACT SAME account
            if (! $userByUsername || ! $userByEmail || $userByUsername->id !== $userByEmail->id) {
                $this->error("Account mismatch: The specified username '{$username}' and email '{$email}' do not match the exact same account. Aborting for safety.");
                return self::FAILURE;
            }

            $adminUser = $userByUsername;

            // Safety Check 3: Strictly restricted to existing administrator accounts
            if ($adminUser->role !== 'admin') {
                $this->error("Access denied: Account '{$username}' ({$email}) has role '{$adminUser->role}'. This command is strictly restricted to administrator accounts and cannot modify or promote non-admin users.");
                return self::FAILURE;
            }

            // Safety Check 4: Update within database transaction
            DB::transaction(function () use ($adminUser, $password, $mustChange) {
                $adminUser->password = Hash::make($password);
                $adminUser->must_change_password = $mustChange;
                $adminUser->status = 'active';
                $adminUser->save();
            });

            // Safety Check 5: Audit logging
            try {
                AuditLogService::log(
                    'admin_password_reset',
                    "Administrator account '{$username}' password was reset via secure environment mechanism",
                    $adminUser,
                    [
                        'must_change_password' => $mustChange,
                        'source' => 'console_env',
                    ],
                    $adminUser->id
                );
            } catch (\Throwable) {
                // Non-fatal if audit logging encounters an issue
            }

            $this->components->info("Administrator account '{$username}' ({$email}) password reset successfully.");
            $this->components->twoColumnDetail('Connection', (string) $connectionName);
            $this->components->twoColumnDetail('Database', (string) $databaseName);
            $this->components->twoColumnDetail('Username', (string) $username);
            $this->components->twoColumnDetail('Email', (string) $email);
            $this->components->twoColumnDetail('Role', 'admin');
            $this->components->twoColumnDetail('Must Change Password', $mustChange ? '<fg=yellow>true (temporary password)</>' : '<fg=green>false (permanent)</>');
            $this->components->twoColumnDetail('Status', '<fg=green>active</>');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $sanitizedMessage = $this->sanitizeErrorMessage($e->getMessage(), $password);
            $this->error("Admin password reset failed: {$sanitizedMessage}");
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
