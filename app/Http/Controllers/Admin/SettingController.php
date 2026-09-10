<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'shop_name' => Setting::get('shop_name', Setting::get('bakery_name', 'Sweet Delights Bakery')),
            'shop_phone' => Setting::get('shop_phone', Setting::get('bakery_phone', '+855 23 123 456')),
            'shop_email' => Setting::get('shop_email', Setting::get('bakery_email', 'info@sweetdelights.com')),
            'shop_address' => Setting::get('shop_address', Setting::get('bakery_address', 'Monivong Blvd, Phnom Penh')),
            'tax_rate' => Setting::get('tax_rate', Setting::get('tax_percentage', '10.0')),
            'discount' => Setting::get('discount', '5.0'),
            'receipt_header' => Setting::get('receipt_header', "Sweet Delights Bakery & Cafe\nFresh Daily Artisan Breads"),
            'receipt_footer' => Setting::get('receipt_footer', "Thank you for visiting!\nFollow us @sweetdelights.bakery"),
            'currency' => Setting::get('currency', 'USD'),
            'date_format' => Setting::get('date_format', 'DD/MM/YYYY'),
            'language' => Setting::get('language', 'EN'),
            'theme_mode' => Setting::get('theme_mode', 'light'),
            'notifications_enabled' => Setting::get('notifications_enabled', '1'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        // 1. Password update check
        if ($request->filled('current_password') || $request->filled('new_password')) {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6|confirmed',
            ]);

            $user = Auth::user();
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password does not match.']);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->back()->with('success', 'Account password updated successfully!');
        }

        // 2. Save settings fields
        $fields = [
            'shop_name', 'shop_phone', 'shop_email', 'shop_address',
            'tax_rate', 'discount', 'receipt_header', 'receipt_footer',
            'currency', 'date_format', 'language', 'theme_mode', 'notifications_enabled'
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field));
            }
        }

        // Sync legacy keys
        if ($request->filled('shop_name')) Setting::set('bakery_name', $request->shop_name);
        if ($request->filled('shop_phone')) Setting::set('bakery_phone', $request->shop_phone);
        if ($request->filled('shop_address')) Setting::set('bakery_address', $request->shop_address);
        if ($request->filled('tax_rate')) Setting::set('tax_percentage', $request->tax_rate);

        if ($request->filled('language')) {
            Session::put('locale', strtolower($request->language));
        }

        return redirect()->back()->with('success', 'System settings saved successfully!');
    }
}
