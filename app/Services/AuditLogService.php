<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * Log a user or system event safely.
     */
    public static function log(
        string $action,
        string $description,
        ?Model $target = null,
        array $metadata = [],
        ?int $userId = null
    ): AuditLog {
        // Strip sensitive keys from metadata
        $sanitizedMetadata = self::sanitizeMetadata($metadata);

        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'description' => $description,
            'target_type' => $target ? class_basename($target) : null,
            'target_id' => $target?->getKey(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'metadata' => !empty($sanitizedMetadata) ? $sanitizedMetadata : null,
        ]);
    }

    /**
     * Strip any passwords, secrets, or sensitive tokens.
     */
    protected static function sanitizeMetadata(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'secret', 'token', 'remember_token'];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = self::sanitizeMetadata($value);
            }
        }

        return $data;
    }
}
