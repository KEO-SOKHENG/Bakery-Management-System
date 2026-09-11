<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\AuditLogService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SettingController extends Controller
{
    /**
     * Display the System Settings view.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->hasPermission('settings.view')) {
            abort(403, 'Unauthorized access to system settings.');
        }

        $settings = [
            'shop_name'                => SettingsService::getString('shop_name', 'Sweet Delights Bakery'),
            'shop_phone'               => SettingsService::getString('shop_phone', '+855 23 123 456'),
            'shop_email'               => SettingsService::getString('shop_email', 'info@sweetdelights.com'),
            'shop_address'             => SettingsService::getString('shop_address', 'Monivong Blvd, Phnom Penh, Cambodia'),
            'shop_logo'                => SettingsService::getString('shop_logo', ''),
            'tax_rate'                 => SettingsService::getString('tax_rate', '10.0'),
            'discount'                 => SettingsService::getString('discount', '5.0'),
            'receipt_header'           => SettingsService::getString('receipt_header', "Sweet Delights Bakery & Cafe\nFresh Daily Artisan Breads"),
            'receipt_footer'           => SettingsService::getString('receipt_footer', "Thank you for visiting!\nFollow us @sweetdelights.bakery"),
            'currency'                 => SettingsService::getString('currency', 'USD'),
            'date_format'              => SettingsService::getString('date_format', 'DD/MM/YYYY'),
            'language'                 => SettingsService::getString('language', 'EN'),
            'theme_mode'               => SettingsService::getString('theme_mode', 'light'),
            'notifications_enabled'    => SettingsService::getString('notifications_enabled', '1'),
            'low_stock_alerts_enabled' => SettingsService::getString('low_stock_alerts_enabled', '1'),
            'expiry_alerts_enabled'    => SettingsService::getString('expiry_alerts_enabled', '1'),
            'opening_time'             => SettingsService::getString('opening_time', '07:00'),
            'closing_time'             => SettingsService::getString('closing_time', '20:00'),
            'low_stock_threshold'      => SettingsService::getString('low_stock_threshold', '5'),
            'expiry_warning_days'      => SettingsService::getString('expiry_warning_days', '3'),
        ];

        $canManage = $user->hasPermission('settings.manage');
        $isAdmin = $user->isAdmin();

        return view('admin.settings', compact('settings', 'canManage', 'isAdmin'));
    }

    /**
     * Update settings and configuration.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        // 1. Password update check
        if ($request->filled('current_password') || $request->filled('new_password') || $request->filled('new_password_confirmation')) {
            $request->validate([
                'current_password'          => 'required|string',
                'new_password'              => 'required|string|min:6|confirmed',
                'new_password_confirmation' => 'required|string|min:6',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Current password does not match.'], 422);
                }
                return redirect()->back()->withErrors(['current_password' => 'Current password does not match.']);
            }

            $user->password = Hash::make($request->new_password);
            $user->must_change_password = false;
            $user->save();

            AuditLogService::log(
                'password_update',
                "User {$user->email} updated their account password via Settings.",
                $user
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Account password updated successfully!']);
            }
            return redirect()->back()->with('success', 'Account password updated successfully!');
        }

        // Check general settings management permission
        if (!$user->hasPermission('settings.manage')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized: You do not have permission to modify system settings.'], 403);
            }
            abort(403, 'Unauthorized: You do not have permission to modify system settings.');
        }

        // 2. Validate configuration input
        $validated = $request->validate([
            'shop_name'                => 'nullable|string|max:255',
            'shop_phone'               => 'nullable|string|max:50',
            'shop_email'               => 'nullable|email|max:255',
            'shop_address'             => 'nullable|string|max:500',
            'tax_rate'                 => 'nullable|numeric|min:0|max:100',
            'discount'                 => 'nullable|numeric|min:0|max:100',
            'receipt_header'           => 'nullable|string|max:1000',
            'receipt_footer'           => 'nullable|string|max:1000',
            'currency'                 => 'nullable|string|in:USD,KHR,$,៛',
            'opening_time'             => 'nullable|date_format:H:i',
            'closing_time'             => 'nullable|date_format:H:i',
            'low_stock_threshold'      => 'nullable|numeric|min:0|max:10000',
            'expiry_warning_days'      => 'nullable|integer|min:1|max:90',
            'notifications_enabled'    => 'nullable|in:0,1',
            'low_stock_alerts_enabled' => 'nullable|in:0,1',
            'expiry_alerts_enabled'    => 'nullable|in:0,1',
            'date_format'              => 'nullable|string|in:DD/MM/YYYY,MM/DD/YYYY,YYYY-MM-DD',
            'language'                 => 'nullable|string|in:EN,KM,en,km',
            'theme_mode'               => 'nullable|string|in:light,dark,glass',
            'shop_logo'                => 'nullable|image|max:2048',
        ]);

        // Handle shop logo upload if provided
        if ($request->hasFile('shop_logo')) {
            $logoFile = $request->file('shop_logo');
            $logoFilename = 'bakery_logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
            $destinationPath = public_path('images');
            File::ensureDirectoryExists($destinationPath);
            $logoFile->move($destinationPath, $logoFilename);
            SettingsService::set('shop_logo', '/images/' . $logoFilename);
        }

        $fieldsToUpdate = [
            'shop_name', 'shop_phone', 'shop_email', 'shop_address',
            'tax_rate', 'discount', 'receipt_header', 'receipt_footer',
            'currency', 'date_format', 'language', 'theme_mode',
            'notifications_enabled', 'low_stock_alerts_enabled', 'expiry_alerts_enabled',
            'opening_time', 'closing_time', 'low_stock_threshold', 'expiry_warning_days'
        ];

        $updatedKeys = [];
        foreach ($fieldsToUpdate as $field) {
            if ($request->has($field)) {
                $val = $request->input($field);
                SettingsService::set($field, $val);
                $updatedKeys[$field] = $val;
            }
        }

        if ($request->filled('language')) {
            Session::put('locale', strtolower($request->language));
        }

        AuditLogService::log(
            'settings_update',
            "System settings updated by {$user->name} ({$user->role}).",
            null,
            ['updated_fields' => array_keys($updatedKeys)]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'System settings saved successfully!',
                'settings' => $updatedKeys
            ]);
        }

        return redirect()->back()->with('success', 'System settings saved successfully!');
    }

    /**
     * Generate and download a safe system configuration and catalog backup.
     */
    public function createBackup(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized: Only system administrators can generate backups.');
        }

        $allSettings = Setting::query()->pluck('value', 'key')->toArray();
        $categories = Category::query()->select('id', 'name', 'description', 'status')->get()->toArray();
        $products = Product::query()->select('id', 'name', 'sku', 'price', 'cost', 'stock', 'status')->get()->toArray();

        $backupPayload = [
            'system'          => 'Bakery Management System',
            'version'         => '1.0',
            'created_at'      => now()->toIso8601String(),
            'generated_by'    => $user->name . ' (' . $user->email . ')',
            'settings'        => $allSettings,
            'categories'      => $categories,
            'products'        => $products,
        ];

        $filename = 'bakery_backup_' . date('Y_m_d_His') . '.json';
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($filepath, json_encode($backupPayload, JSON_PRETTY_PRINT));

        AuditLogService::log(
            'backup_created',
            "Administrator {$user->name} created and downloaded system backup {$filename}.",
            null,
            ['filename' => $filename, 'file_size' => filesize($filepath)]
        );

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Restore system configuration from an authenticated, validated backup file.
     */
    public function restoreBackup(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized: Only system administrators can restore backups.');
        }

        $request->validate([
            'backup_file'     => 'required|file|max:5120',
            'confirm_restore' => 'required',
        ], [
            'confirm_restore.required' => 'You must check the confirmation box to proceed with restoring settings.',
        ]);

        $file = $request->file('backup_file');
        $rawContent = file_get_contents($file->getRealPath());
        $payload = json_decode($rawContent, true);

        if (!is_array($payload) || !isset($payload['settings']) || !is_array($payload['settings'])) {
            return redirect()->back()->withErrors([
                'backup_file' => 'The uploaded file is not a valid system backup or contains corrupted data.'
            ]);
        }

        try {
            DB::transaction(function () use ($payload) {
                SettingsService::setMany($payload['settings']);
            });

            AuditLogService::log(
                'backup_restored',
                "Administrator {$user->name} restored system settings from backup file.",
                null,
                ['settings_count' => count($payload['settings'])]
            );

            return redirect()->back()->with('success', 'System settings restored successfully from backup!');
        } catch (\Throwable $e) {
            AuditLogService::log(
                'backup_restore_failed',
                "Restore attempt failed: " . $e->getMessage(),
                null
            );

            return redirect()->back()->withErrors([
                'backup_file' => 'Failed to restore configuration: ' . $e->getMessage()
            ]);
        }
    }
}
