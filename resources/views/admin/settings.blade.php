@extends('layouts.app')

@section('title', 'System Settings - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}">
@endpush

@section('content')
<div class="settings-header-intro">
    <h1 data-lang-key="settings_header">System Settings</h1>
    <p data-lang-key="settings_subtitle">Configure store profile, taxation, receipts, security access, and app appearance.</p>
</div>

@if(session('success'))
    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 0.85rem 1.15rem; border-radius: 12px; margin-bottom: 1.25rem; font-weight: 600;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.85rem 1.15rem; border-radius: 12px; margin-bottom: 1.25rem; font-weight: 600;">
        {{ $errors->first() }}
    </div>
@endif

<!-- 6 Glass Card Settings Grid -->
<div class="settings-glass-grid">

    <!-- CARD 1: SHOP INFORMATION -->
    <div class="settings-glass-card">
        <div>
            <div class="card-title-bar">
                <div class="card-title-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div class="card-title-text">
                    <h3 data-lang-key="shop_info">Shop Information</h3>
                    <p data-lang-key="shop_info_sub">Bakery profile details and branding logo</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form" id="form_shop_info">
                @csrf
                <div class="form-field-group">
                    <label class="form-label" data-lang-key="shop_name">Shop Name</label>
                    <input type="text" name="shop_name" class="ios-input-field" value="{{ old('shop_name', $settings['shop_name'] ?? 'Sweet Delights Bakery') }}" required placeholder="Enter bakery name">
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="phone_number">Phone Number</label>
                    <input type="text" name="shop_phone" class="ios-input-field" value="{{ old('shop_phone', $settings['shop_phone'] ?? '+855 23 123 456') }}" required placeholder="+855 ...">
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="address">Address</label>
                    <input type="text" name="shop_address" class="ios-input-field" value="{{ old('shop_address', $settings['shop_address'] ?? 'Monivong Blvd, Phnom Penh, Cambodia') }}" required placeholder="Full store address">
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="shop_logo">Shop Logo</label>
                    <div class="logo-upload-box">
                        <div class="logo-preview-img" id="logo_preview_box">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-brown)" stroke-width="2"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/></svg>
                        </div>
                        <div class="logo-upload-info">
                            <label for="shop_logo_input" class="btn-upload-trigger">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span data-lang-key="upload_new_logo">Upload New Logo</span>
                            </label>
                            <input type="file" id="shop_logo_input" accept="image/*" style="display: none;">
                            <span style="font-size: 0.7rem; color: #9ca3af;">PNG, JPG or SVG (Max 2MB)</span>
                        </div>
                    </div>
                </div>

                <div class="card-save-footer">
                    <button type="submit" class="btn-ios-save" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span data-lang-key="save_changes">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD 2: TAX & DISCOUNT -->
    <div class="settings-glass-card">
        <div>
            <div class="card-title-bar">
                <div class="card-title-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="card-title-text">
                    <h3 data-lang-key="tax_discount">Tax & Discount</h3>
                    <p data-lang-key="tax_discount_sub">POS tax rates and default promotional discounts</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form" id="form_tax_discount">
                @csrf
                <div class="form-field-group">
                    <label class="form-label" data-lang-key="tax_rate">Tax Rate (%)</label>
                    <input type="number" name="tax_rate" step="0.1" class="ios-input-field" value="{{ old('tax_rate', $settings['tax_rate'] ?? '10.0') }}" required placeholder="0.0">
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="discount">Discount (%)</label>
                    <input type="number" name="discount" step="0.1" class="ios-input-field" value="{{ old('discount', $settings['discount'] ?? '5.0') }}" required placeholder="0.0">
                </div>

                <div class="card-save-footer" style="margin-top: auto; padding-top: 2rem;">
                    <button type="submit" class="btn-ios-save" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span data-lang-key="save_changes">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD 3: RECEIPT -->
    <div class="settings-glass-card">
        <div>
            <div class="card-title-bar">
                <div class="card-title-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"/><path d="M16 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/></svg>
                </div>
                <div class="card-title-text">
                    <h3 data-lang-key="receipt_config">Receipt Configuration</h3>
                    <p data-lang-key="receipt_config_sub">Custom receipt header and footer messages</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form" id="form_receipt">
                @csrf
                <div class="form-field-group">
                    <label class="form-label" data-lang-key="receipt_header">Receipt Header</label>
                    <textarea name="receipt_header" class="ios-textarea-field" placeholder="Header text printed at top of receipts">{{ old('receipt_header', $settings['receipt_header'] ?? "Sweet Delights Bakery & Cafe\nFresh Daily Artisan Breads") }}</textarea>
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="receipt_footer">Receipt Footer</label>
                    <textarea name="receipt_footer" class="ios-textarea-field" placeholder="Footer text printed at bottom of receipts">{{ old('receipt_footer', $settings['receipt_footer'] ?? "Thank you for visiting!\nFollow us @sweetdelights.bakery") }}</textarea>
                </div>

                <div class="card-save-footer">
                    <button type="submit" class="btn-ios-save" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span data-lang-key="save_changes">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD 4: USER & SECURITY -->
    <div class="settings-glass-card">
        <div>
            <div class="card-title-bar">
                <div class="card-title-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="card-title-text">
                    <h3 data-lang-key="user_security">User & Security</h3>
                    <p data-lang-key="user_security_sub">User access controls and account password update</p>
                </div>
            </div>

            <div style="margin-bottom: 1.15rem;">
                <a href="{{ route('admin.users.index') }}" class="btn-ios-secondary" style="width: 100%; justify-content: center; text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-3-3.87"/><path d="M9 21v-2a4 4 0 0 0-4-4H3a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span data-lang-key="manage_users">Manage Users & Staff Roles</span>
                </a>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form" id="form_security">
                @csrf
                <div class="form-field-group">
                    <label class="form-label" data-lang-key="current_password">Current Password</label>
                    <input type="password" name="current_password" class="ios-input-field" placeholder="••••••••••••">
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="new_password">New Password</label>
                    <input type="password" name="new_password" class="ios-input-field" placeholder="••••••••••••">
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="confirm_password">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" class="ios-input-field" placeholder="••••••••••••">
                </div>

                <div class="card-save-footer">
                    <button type="submit" class="btn-ios-save" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span data-lang-key="update_password">Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD 5: GENERAL PREFERENCES -->
    <div class="settings-glass-card">
        <div>
            <div class="card-title-bar">
                <div class="card-title-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div class="card-title-text">
                    <h3 data-lang-key="general_preferences">General Preferences</h3>
                    <p data-lang-key="general_preferences_sub">Currency, date formats, and language settings</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form" id="form_general">
                @csrf
                <div class="form-field-group">
                    <label class="form-label" data-lang-key="currency">Currency</label>
                    <select name="currency" class="ios-select-field" id="currency_select">
                        <option value="USD" {{ ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                        <option value="KHR" {{ ($settings['currency'] ?? '') === 'KHR' ? 'selected' : '' }}>KHR (៛) - Cambodian Riel</option>
                    </select>
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="date_format">Date Format</label>
                    <select name="date_format" class="ios-select-field" id="date_format_select">
                        <option value="DD/MM/YYYY" {{ ($settings['date_format'] ?? 'DD/MM/YYYY') === 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY (16/08/2026)</option>
                        <option value="MM/DD/YYYY" {{ ($settings['date_format'] ?? '') === 'MM/DD/YYYY' ? 'selected' : '' }}>MM/DD/YYYY (08/16/2026)</option>
                        <option value="YYYY-MM-DD" {{ ($settings['date_format'] ?? '') === 'YYYY-MM-DD' ? 'selected' : '' }}>YYYY-MM-DD (2026-08-16)</option>
                    </select>
                </div>

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="language">Language</label>
                    <select name="language" class="ios-select-field" id="language_select">
                        <option value="EN" {{ strtoupper($settings['language'] ?? 'EN') === 'EN' ? 'selected' : '' }}>English</option>
                        <option value="KM" {{ strtoupper($settings['language'] ?? '') === 'KM' ? 'selected' : '' }}>Khmer (ភាសាខ្មែរ)</option>
                    </select>
                </div>

                <div class="card-save-footer">
                    <button type="submit" class="btn-ios-save" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span data-lang-key="save_preferences">Save Preferences</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD 6: APPEARANCE & NOTIFICATIONS -->
    <div class="settings-glass-card">
        <div>
            <div class="card-title-bar">
                <div class="card-title-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.92 0 1.69-.74 1.69-1.67 0-.44-.18-.84-.46-1.14-.27-.29-.44-.69-.44-1.14 0-.93.75-1.69 1.69-1.69h1.75c3.74 0 6.77-3.03 6.77-6.77 0-4.32-3.7-7.59-8-7.59z"/></svg>
                </div>
                <div class="card-title-text">
                    <h3 data-lang-key="appearance_notifications">Appearance & Notifications</h3>
                    <p data-lang-key="appearance_notifications_sub">Application theme and system notifications</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="settings-form" id="form_appearance">
                @csrf
                <input type="hidden" name="theme_mode" id="theme_mode_hidden_input" value="{{ $settings['theme_mode'] ?? 'light' }}">

                <div class="form-field-group">
                    <label class="form-label" data-lang-key="theme_mode">Theme Mode</label>
                    <div class="ios-segmented-control">
                        <button type="button" class="segmented-option {{ ($settings['theme_mode'] ?? 'light') === 'light' ? 'active' : '' }}" data-theme="light">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                            <span data-lang-key="light">Light</span>
                        </button>
                        <button type="button" class="segmented-option {{ ($settings['theme_mode'] ?? '') === 'dark' ? 'active' : '' }}" data-theme="dark">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                            <span data-lang-key="dark">Dark</span>
                        </button>
                        <button type="button" class="segmented-option {{ ($settings['theme_mode'] ?? '') === 'glass' ? 'active' : '' }}" data-theme="glass">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                            <span data-lang-key="glass">Glass</span>
                        </button>
                    </div>
                </div>

                <div class="ios-switch-row" style="margin-top: 1rem;">
                    <div>
                        <div class="ios-switch-label" data-lang-key="system_notifications">System Notifications</div>
                        <div class="ios-switch-subtitle" data-lang-key="notifications_sub">Enable low stock & new order alert popups</div>
                    </div>
                    <label class="ios-switch">
                        <input type="checkbox" name="notifications_enabled" value="1" id="notifications_toggle" {{ ($settings['notifications_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                        <span class="switch-slider"></span>
                    </label>
                </div>

                <div class="card-save-footer" style="margin-top: auto; padding-top: 1.5rem;">
                    <button type="submit" class="btn-ios-save" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span data-lang-key="save_preferences">Save Preferences</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/settings.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeBtns = document.querySelectorAll('.segmented-option');
            const hiddenThemeInput = document.getElementById('theme_mode_hidden_input');

            themeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    themeBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const theme = this.getAttribute('data-theme');
                    if (hiddenThemeInput) hiddenThemeInput.value = theme;
                    document.documentElement.setAttribute('data-theme', theme);
                });
            });
        });
    </script>
@endpush
