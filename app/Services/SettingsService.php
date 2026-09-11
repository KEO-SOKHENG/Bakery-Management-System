<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public const CACHE_KEY = 'app_system_settings';

    /**
     * Legacy & convenience aliases between dual-named setting keys.
     */
    protected const ALIASES = [
        'shop_name'     => 'bakery_name',
        'bakery_name'   => 'shop_name',
        'shop_phone'    => 'bakery_phone',
        'bakery_phone'  => 'shop_phone',
        'shop_email'    => 'bakery_email',
        'bakery_email'  => 'shop_email',
        'shop_address'  => 'bakery_address',
        'bakery_address'=> 'shop_address',
        'tax_rate'      => 'tax_percentage',
        'tax_percentage'=> 'tax_rate',
    ];

    /**
     * Default values for system settings when not found in database.
     */
    protected const DEFAULTS = [
        'shop_name'                 => 'Sweet Delights Bakery',
        'bakery_name'               => 'Sweet Delights Bakery',
        'shop_phone'                => '+855 23 123 456',
        'bakery_phone'              => '+855 23 123 456',
        'shop_email'                => 'info@sweetdelights.com',
        'bakery_email'              => 'info@sweetdelights.com',
        'shop_address'              => 'Monivong Blvd, Phnom Penh, Cambodia',
        'bakery_address'            => 'Monivong Blvd, Phnom Penh, Cambodia',
        'tax_rate'                  => '10.0',
        'tax_percentage'            => '10.0',
        'discount'                  => '5.0',
        'receipt_header'            => "Sweet Delights Bakery & Cafe\nFresh Daily Artisan Breads",
        'receipt_footer'            => "Thank you for visiting!\nFollow us @sweetdelights.bakery",
        'currency'                  => '$',
        'date_format'               => 'DD/MM/YYYY',
        'language'                  => 'en',
        'theme_mode'                => 'light',
        'notifications_enabled'     => '1',
        'low_stock_alerts_enabled'  => '1',
        'expiry_alerts_enabled'     => '1',
        'opening_time'              => '07:00',
        'closing_time'              => '20:00',
        'low_stock_threshold'       => '5',
        'expiry_warning_days'       => '3',
    ];

    /**
     * Retrieve a setting value with fallback to defaults or passed default.
     */
    public static function get(string $key, $default = null)
    {
        $all = static::all();

        if (array_key_exists($key, $all) && $all[$key] !== null) {
            return $all[$key];
        }

        if (isset(static::ALIASES[$key])) {
            $alias = static::ALIASES[$key];
            if (array_key_exists($alias, $all) && $all[$alias] !== null) {
                return $all[$alias];
            }
        }

        if ($default !== null) {
            return $default;
        }

        return static::DEFAULTS[$key] ?? (isset(static::ALIASES[$key]) ? (static::DEFAULTS[static::ALIASES[$key]] ?? null) : null);
    }

    /**
     * Retrieve setting as string.
     */
    public static function getString(string $key, string $default = ''): string
    {
        $val = static::get($key, $default);
        return (string) ($val ?? $default);
    }

    /**
     * Retrieve setting as integer.
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $val = static::get($key, $default);
        return is_numeric($val) ? (int) $val : $default;
    }

    /**
     * Retrieve setting as float.
     */
    public static function getFloat(string $key, float $default = 0.0): float
    {
        $val = static::get($key, $default);
        return is_numeric($val) ? (float) $val : $default;
    }

    /**
     * Retrieve setting as boolean.
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $val = static::get($key, $default);
        if ($val === null) {
            return $default;
        }

        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Resolve currency symbol from currency code or symbol.
     */
    public static function getCurrencySymbol(): string
    {
        $raw = strtoupper(trim(static::getString('currency', '$')));
        if ($raw === 'USD' || $raw === '$') {
            return '$';
        }
        if ($raw === 'KHR' || $raw === '៛') {
            return '៛';
        }
        return $raw ?: '$';
    }

    /**
     * Persist or update a single setting and invalidate cache.
     */
    public static function set(string $key, $value): Setting
    {
        $stringVal = $value === null ? null : (string) $value;

        $record = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $stringVal]
        );

        // Keep known aliases in sync
        if (isset(static::ALIASES[$key])) {
            $aliasKey = static::ALIASES[$key];
            Setting::updateOrCreate(
                ['key' => $aliasKey],
                ['value' => $stringVal]
            );
        }

        static::clearCache();

        return $record;
    }

    /**
     * Persist multiple settings key-value pairs at once.
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $stringVal = $value === null ? null : (string) $value;
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $stringVal]
            );

            if (isset(static::ALIASES[$key])) {
                Setting::updateOrCreate(
                    ['key' => static::ALIASES[$key]],
                    ['value' => $stringVal]
                );
            }
        }

        static::clearCache();
    }

    /**
     * Retrieve all settings key-value pairs with caching.
     */
    public static function all(): array
    {
        return Cache::rememberForever(static::CACHE_KEY, function () {
            return Setting::query()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear settings cache immediately.
     */
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
