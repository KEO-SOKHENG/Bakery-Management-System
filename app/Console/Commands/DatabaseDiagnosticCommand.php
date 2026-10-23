<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:diagnostic')]
class DatabaseDiagnosticCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:diagnostic {--database= : The database connection to check} {--json : Output information as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely check database connectivity and report connection details without logging credentials';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connectionName = $this->option('database') ?: config('database.default');
        $connectionConfig = config("database.connections.{$connectionName}") ?? [];

        $host = $connectionConfig['host'] ?? 'N/A';
        $database = $connectionConfig['database'] ?? 'N/A';

        $diagnostic = [
            'connection' => (string) $connectionName,
            'host' => (string) $host,
            'database' => (string) $database,
            'connected' => false,
            'status' => 'Pending',
        ];

        try {
            $connection = DB::connection($connectionName);
            // Verify connection via PDO
            $pdo = $connection->getPdo();
            $driver = $connection->getDriverTitle();
            $serverVersion = $connection->getServerVersion();

            $diagnostic['connected'] = true;
            $diagnostic['status'] = "Connection successful ({$driver} {$serverVersion})";

            if ($this->option('json')) {
                $this->output->writeln(json_encode($diagnostic));
                return self::SUCCESS;
            }

            $this->newLine();
            $this->components->info('Database Diagnostic Check');
            $this->components->twoColumnDetail('Connection', $diagnostic['connection']);
            $this->components->twoColumnDetail('Host', $diagnostic['host']);
            $this->components->twoColumnDetail('Database', $diagnostic['database']);
            $this->components->twoColumnDetail('Status', "<fg=green>{$diagnostic['status']}</>");
            $this->newLine();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $sanitizedError = $this->sanitizeErrorMessage(
                $e->getMessage(),
                $connectionConfig['password'] ?? null,
                env('DATABASE_URL') ?: env('DB_URL')
            );
            $diagnostic['connected'] = false;
            $diagnostic['status'] = "Connection failed: {$sanitizedError}";

            if ($this->option('json')) {
                $this->output->writeln(json_encode($diagnostic));
                return self::FAILURE;
            }

            $this->newLine();
            $this->components->error('Database Diagnostic Check Failed');
            $this->components->twoColumnDetail('Connection', $diagnostic['connection']);
            $this->components->twoColumnDetail('Host', $diagnostic['host']);
            $this->components->twoColumnDetail('Database', $diagnostic['database']);
            $this->components->twoColumnDetail('Status', "<fg=red>{$diagnostic['status']}</>");
            $this->newLine();

            return self::FAILURE;
        }
    }

    /**
     * Ensure no credentials or connection string secrets are leaked in error output.
     */
    protected function sanitizeErrorMessage(string $message, ?string $knownPassword = null, ?string $knownUrl = null): string
    {
        $sanitized = $message;

        if (! empty($knownUrl) && is_string($knownUrl)) {
            $sanitized = str_replace($knownUrl, '[REDACTED_DATABASE_URL]', $sanitized);
        }

        if (! empty($knownPassword) && is_string($knownPassword)) {
            $sanitized = str_replace($knownPassword, '***', $sanitized);
        }

        // Strip URLs with user:password (postgres://user:password@host, http://..., etc.)
        $sanitized = (string) preg_replace('/(:\/\/[^:]+:)[^@\s]+(@)/', '$1***$2', $sanitized);
        // Also catch :password@ without protocol prefix
        $sanitized = (string) preg_replace('/:[^:@\s]+@/', ':***@', $sanitized);
        // Strip password / pwd assignments (e.g. password=secret, password => secret, 'password' => 'secret')
        $sanitized = (string) preg_replace('/(password|pwd)(\s*[:=]\s*)(["\']?)[^\s;,\'"]+\3/i', '$1$2***', $sanitized);
        // Collapse whitespace
        return trim((string) preg_replace('/\s+/', ' ', $sanitized));
    }
}
