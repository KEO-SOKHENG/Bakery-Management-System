@extends('layouts.app')

@section('title', 'User & Staff Management - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')
<x-page-header title="User & Staff Management" :subtitle="__('messages.users_management_subtitle')">
    <x-slot:actions>
        <x-button variant="primary" id="btn_open_create_user_modal" class="btn-user-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span data-lang-key="create_user">{{ __('messages.create_user') }}</span>
        </x-button>
    </x-slot:actions>
</x-page-header>

<!-- Flash Notifications -->
@if(session('success'))
    <x-alert type="success" :message="session('success')" />
@endif

@if(session('error'))
    <x-alert type="danger" :message="session('error')" />
@endif

@if(session('info'))
    <x-alert type="info" :message="session('info')" />
@endif

@if($errors->any())
    <x-alert type="danger">
        <ul style="margin: 0; padding-left: 1.2rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

<!-- Navigation Tabs -->
<div class="hr-nav-tabs">
    <a href="{{ route('admin.users.index') }}" class="hr-nav-tab active">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span data-lang-key="users_management">{{ __('messages.users_management') }}</span>
    </a>
    <a href="{{ route('admin.hr.salaries') }}" class="hr-nav-tab">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <span data-lang-key="salary_management">{{ __('messages.salary_management') }}</span>
    </a>
    <a href="{{ route('admin.hr.attendance') }}" class="hr-nav-tab">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span data-lang-key="attendance_management">{{ __('messages.attendance_management') }}</span>
    </a>
    <a href="{{ route('admin.hr.schedules') }}" class="hr-nav-tab">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span data-lang-key="work_schedules">{{ __('messages.work_schedules') }}</span>
    </a>
</div>

<!-- Summary Metric Cards -->
<div class="user-stats-grid">
    <x-card class="user-stat-card">
        <div class="stat-icon-wrapper total">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" data-lang-key="total_staff">{{ __('messages.total_staff') }}</div>
            <div class="stat-number">{{ number_format($totalUsers) }}</div>
        </div>
    </x-card>

    <x-card class="user-stat-card">
        <div class="stat-icon-wrapper active">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" data-lang-key="active_accounts">{{ __('messages.active_accounts') }}</div>
            <div class="stat-number">{{ number_format($activeUsers) }}</div>
        </div>
    </x-card>

    <x-card class="user-stat-card">
        <div class="stat-icon-wrapper inactive">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" data-lang-key="inactive_accounts">{{ __('messages.inactive_accounts') }}</div>
            <div class="stat-number">{{ number_format($inactiveUsers) }}</div>
        </div>
    </x-card>

    <x-card class="user-stat-card">
        <div class="stat-icon-wrapper suspended">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" data-lang-key="suspended_accounts">{{ __('messages.suspended_accounts') }}</div>
            <div class="stat-number">{{ number_format($suspendedUsers) }}</div>
        </div>
    </x-card>
</div>

<!-- Search & Filtering Toolbar -->
<div class="user-toolbar">
    <form action="{{ route('admin.users.index') }}" method="GET" class="user-search-form">
        <div class="user-search-input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
                type="text"
                name="search"
                id="user_search_input"
                class="user-input"
                value="{{ request('search') }}"
                placeholder="{{ __('messages.search_users_placeholder') }}"
                data-lang-key="search_users_placeholder"
            >
        </div>

        <select name="role" class="user-select" onchange="this.form.submit()">
            <option value="" data-lang-key="all_roles">{{ __('messages.all_roles') }}</option>
            @foreach($roles as $key => $label)
                <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <select name="status" class="user-select" onchange="this.form.submit()">
            <option value="" data-lang-key="all_statuses">{{ __('messages.all_statuses') }}</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        @if(request()->hasAny(['search', 'role', 'status']))
            <a href="{{ route('admin.users.index') }}" style="font-size: 0.85rem; font-weight: 700; color: #ef4444; text-decoration: none; padding: 0.5rem;">Clear Filters</a>
        @endif
    </form>
</div>

<!-- Users Table Card -->
<x-card class="users-card">
    <div style="overflow-x: auto;">
        <table class="users-table" id="users_table">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Contact Info</th>
                    <th>Role</th>
                    <th>Account Status</th>
                    <th>Last Active</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    @php
                        $initials = strtoupper(substr($user->name, 0, 2));
                        $roleClass = 'role-' . strtolower($user->role);
                        $statusClass = 'status-' . strtolower($user->status);
                    @endphp
                    <tr id="user_row_{{ $user->id }}">
                        <td>
                            <div class="user-identity">
                                <div class="user-avatar">{{ $initials }}</div>
                                <div>
                                    <div class="user-name">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span style="font-size: 0.675rem; background: rgba(86, 48, 32, 0.1); color: #563020; padding: 0.15rem 0.45rem; border-radius: 9999px; font-weight: 800; margin-left: 0.35rem;">YOU</span>
                                        @endif
                                        @if($user->must_change_password)
                                            <span style="font-size: 0.675rem; background: rgba(217, 119, 6, 0.12); color: #d97706; padding: 0.15rem 0.45rem; border-radius: 9999px; font-weight: 800; margin-left: 0.35rem;" title="Temporary Password Pending Change">TEMP PWD</span>
                                        @endif
                                    </div>
                                    <div class="user-username">{{ '@' . $user->username }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; font-size: 0.875rem;">{{ $user->email }}</div>
                            <div style="font-size: 0.775rem; color: var(--user-muted);">{{ $user->phone ?? '—' }}</div>
                        </td>
                        <td>
                            <span class="role-pill {{ $roleClass }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            <x-badge :variant="$user->status === 'active' ? 'success' : ($user->status === 'suspended' ? 'danger' : 'warning')">
                                {{ ucfirst($user->status) }}
                            </x-badge>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; font-weight: 600;">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--user-muted);">
                                Joined {{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}
                            </div>
                        </td>
                        <td>
                            <div class="user-action-btns" style="justify-content: flex-end;">
                                <!-- View Details & Audit Button -->
                                <button
                                    type="button"
                                    class="btn-icon-action btn-view-user"
                                    title="View Profile & Audit Trail"
                                    data-id="{{ $user->id }}"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>

                                <!-- Edit Profile Button -->
                                <button
                                    type="button"
                                    class="btn-icon-action btn-edit-user"
                                    title="Edit User Profile"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-username="{{ $user->username }}"
                                    data-email="{{ $user->email }}"
                                    data-phone="{{ $user->phone }}"
                                    data-role="{{ $user->role }}"
                                    data-status="{{ $user->status }}"
                                    data-mustchange="{{ $user->must_change_password ? '1' : '0' }}"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>

                                <!-- Manage Custom Permissions Button -->
                                <button
                                    type="button"
                                    class="btn-icon-action btn-permissions-user"
                                    title="Custom Permissions Override"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-role="{{ $user->role }}"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </button>

                                <!-- Reset Password Button -->
                                <button
                                    type="button"
                                    class="btn-icon-action warn btn-reset-pwd-user"
                                    title="Reset Password & Issue Temporary Password"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-username="{{ $user->username }}"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                                </button>

                                <!-- Status Quick Toggle Dropdown / Modal Trigger -->
                                @if($user->id !== auth()->id())
                                    <button
                                        type="button"
                                        class="btn-icon-action btn-status-user"
                                        title="Change Status (Active / Inactive / Suspended)"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-status="{{ $user->status }}"
                                    >
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4"/><path d="M12 16V8"/></svg>
                                    </button>

                                    <!-- Delete Button (checks historical record protection) -->
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete user \'{{ $user->username }}\'? If they have sales or order records, consider deactivating them instead.');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-action delete" title="Delete User">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--user-muted);">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👥</div>
                            <div style="font-size: 1rem; font-weight: 700; color: var(--user-text);" data-lang-key="no_users_found">{{ __('messages.no_users_found') }}</div>
                            <p style="font-size: 0.85rem; margin-top: 0.25rem;">Try adjusting your search criteria or register a new staff account above.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 1.5rem;">
        {{ $users->links() }}
    </div>
</x-card>

<!-- ==========================================================================
     CREATE USER MODAL
     ========================================================================== -->
<div class="user-modal-backdrop" id="create_user_modal">
    <div class="user-modal-card">
        <div class="user-modal-header">
            <h3 data-lang-key="create_user">{{ __('messages.create_user') }}</h3>
            <button type="button" class="btn-modal-close" id="btn_close_create_user">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="user-modal-body">
                <div class="form-field-group">
                    <label for="create_user_name">Full Name *</label>
                    <input type="text" id="create_user_name" name="name" required placeholder="e.g. Sareth Chan">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-field-group">
                        <label for="create_user_username">Username *</label>
                        <input type="text" id="create_user_username" name="username" required placeholder="e.g. sareth.c">
                    </div>
                    <div class="form-field-group">
                        <label for="create_user_phone">Phone Number</label>
                        <input type="text" id="create_user_phone" name="phone" placeholder="e.g. 012 345 678">
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="create_user_email">Email Address *</label>
                    <input type="email" id="create_user_email" name="email" required placeholder="e.g. staff@bakery.com">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-field-group">
                        <label for="create_user_role">Staff Role *</label>
                        <select id="create_user_role" name="role" required>
                            <option value="cashier" selected>Cashier</option>
                            <option value="baker">Baker</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label for="create_user_status">Initial Status *</label>
                        <select id="create_user_status" name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="create_user_password">Password / Temporary Password * (min 6 chars)</label>
                    <input type="password" id="create_user_password" name="password" required minlength="6" placeholder="••••••••••••">
                </div>

                <div style="margin-top: 0.5rem; background: rgba(86, 48, 32, 0.05); padding: 0.75rem; border-radius: 10px; border: 1px dashed var(--user-border);">
                    <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: var(--user-text);">
                        <input type="checkbox" name="must_change_password" value="1" checked style="width: 18px; height: 18px; accent-color: #563020;">
                        <span data-lang-key="force_password_change">{{ __('messages.force_password_change') }}</span>
                    </label>
                    <div style="font-size: 0.75rem; color: var(--user-muted); margin-top: 0.25rem; margin-left: 1.8rem;">
                        If checked, the account will be forced to set a permanent password upon first login.
                    </div>
                </div>
            </div>
            <div class="user-modal-footer">
                <x-button variant="secondary" id="btn_cancel_create_user" class="btn-modal-cancel" data-lang-key="cancel">{{ __('messages.cancel') }}</x-button>
                <x-button variant="primary" type="submit" class="btn-user-primary" data-lang-key="save">{{ __('messages.save') }}</x-button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     EDIT USER MODAL
     ========================================================================== -->
<div class="user-modal-backdrop" id="edit_user_modal">
    <div class="user-modal-card">
        <div class="user-modal-header">
            <h3 data-lang-key="edit_user">{{ __('messages.edit_user') }}</h3>
            <button type="button" class="btn-modal-close" id="btn_close_edit_user">&times;</button>
        </div>
        <form id="form_edit_user" method="POST">
            @csrf
            @method('PUT')
            <div class="user-modal-body">
                <div class="form-field-group">
                    <label for="edit_user_name">Full Name *</label>
                    <input type="text" id="edit_user_name" name="name" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-field-group">
                        <label for="edit_user_username">Username *</label>
                        <input type="text" id="edit_user_username" name="username" required>
                    </div>
                    <div class="form-field-group">
                        <label for="edit_user_phone">Phone Number</label>
                        <input type="text" id="edit_user_phone" name="phone">
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="edit_user_email">Email Address *</label>
                    <input type="email" id="edit_user_email" name="email" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-field-group">
                        <label for="edit_user_role">Staff Role *</label>
                        <select id="edit_user_role" name="role" required>
                            <option value="cashier">Cashier</option>
                            <option value="baker">Baker</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label for="edit_user_status">Account Status *</label>
                        <select id="edit_user_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="edit_user_password">New Password (leave empty to keep current)</label>
                    <input type="password" id="edit_user_password" name="password" minlength="6" placeholder="Optional new password">
                </div>

                <div style="margin-top: 0.5rem; background: rgba(86, 48, 32, 0.05); padding: 0.75rem; border-radius: 10px; border: 1px dashed var(--user-border);">
                    <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: var(--user-text);">
                        <input type="checkbox" id="edit_must_change_password" name="must_change_password" value="1" style="width: 18px; height: 18px; accent-color: #563020;">
                        <span data-lang-key="force_password_change">{{ __('messages.force_password_change') }}</span>
                    </label>
                </div>
            </div>
            <div class="user-modal-footer">
                <x-button variant="secondary" id="btn_cancel_edit_user" class="btn-modal-cancel" data-lang-key="cancel">{{ __('messages.cancel') }}</x-button>
                <x-button variant="primary" type="submit" class="btn-user-primary" data-lang-key="save">{{ __('messages.save') }}</x-button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     RESET PASSWORD MODAL
     ========================================================================== -->
<div class="user-modal-backdrop" id="reset_password_modal">
    <div class="user-modal-card">
        <div class="user-modal-header">
            <h3 data-lang-key="reset_password">{{ __('messages.reset_password') }}</h3>
            <button type="button" class="btn-modal-close" id="btn_close_reset_pwd">&times;</button>
        </div>
        <form id="form_reset_password" method="POST">
            @csrf
            <div class="user-modal-body">
                <div style="padding: 0.75rem; background: rgba(217, 119, 6, 0.1); border: 1px solid rgba(217, 119, 6, 0.25); border-radius: 12px; font-size: 0.85rem; font-weight: 600; color: #92400e;">
                    Resetting password for: <strong id="reset_pwd_user_title">User</strong>
                </div>

                <div class="form-field-group">
                    <label for="reset_pwd_input">New Password * (min 6 characters)</label>
                    <input type="password" id="reset_pwd_input" name="password" required minlength="6" placeholder="Enter new password">
                </div>

                <div style="background: rgba(86, 48, 32, 0.05); padding: 0.75rem; border-radius: 10px; border: 1px dashed var(--user-border);">
                    <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: var(--user-text);">
                        <input type="checkbox" name="must_change_password" value="1" checked style="width: 18px; height: 18px; accent-color: #563020;">
                        <span data-lang-key="force_password_change">{{ __('messages.force_password_change') }}</span>
                    </label>
                    <div style="font-size: 0.75rem; color: var(--user-muted); margin-top: 0.25rem; margin-left: 1.8rem;">
                        User will be required to change this password before accessing any bakery features upon their next login.
                    </div>
                </div>
            </div>
            <div class="user-modal-footer">
                <x-button variant="secondary" id="btn_cancel_reset_pwd" class="btn-modal-cancel" data-lang-key="cancel">{{ __('messages.cancel') }}</x-button>
                <x-button variant="warning" type="submit" class="btn-user-primary">Save New Password</x-button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     STATUS QUICK-UPDATE MODAL
     ========================================================================== -->
<div class="user-modal-backdrop" id="status_user_modal">
    <div class="user-modal-card">
        <div class="user-modal-header">
            <h3>Update Account Status</h3>
            <button type="button" class="btn-modal-close" id="btn_close_status_modal">&times;</button>
        </div>
        <form id="form_status_user" method="POST">
            @csrf
            <div class="user-modal-body">
                <div style="padding: 0.75rem; background: rgba(86, 48, 32, 0.05); border: 1px solid var(--user-border); border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                    Target User: <strong id="status_user_name"></strong>
                </div>

                <div class="form-field-group" style="margin-top: 1rem;">
                    <label for="modal_status_select">Set Account Status *</label>
                    <select id="modal_status_select" name="status" required>
                        <option value="active">Active (Full operational access)</option>
                        <option value="inactive">Inactive (Deactivated, blocked from logging in)</option>
                        <option value="suspended">Suspended (Temporarily locked due to policy)</option>
                    </select>
                </div>

                <div style="font-size: 0.8rem; color: var(--user-muted); line-height: 1.4;">
                    <strong>Historical Integrity Rule:</strong> Deactivating or suspending a staff member preserves all historical orders, sales, payments, and batch productions created by them.
                </div>
            </div>
            <div class="user-modal-footer">
                <x-button variant="secondary" id="btn_cancel_status_modal" class="btn-modal-cancel" data-lang-key="cancel">{{ __('messages.cancel') }}</x-button>
                <x-button variant="primary" type="submit" class="btn-user-primary" data-lang-key="save">{{ __('messages.save') }}</x-button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     PERMISSIONS MANAGEMENT MODAL
     ========================================================================== -->
<div class="user-modal-backdrop" id="permissions_user_modal">
    <div class="user-modal-card wide-modal">
        <div class="user-modal-header">
            <div>
                <h3 data-lang-key="manage_permissions">{{ __('messages.manage_permissions') }}</h3>
                <div style="font-size: 0.8rem; color: var(--user-muted);" id="perm_user_subtitle">
                    Configuring permissions for Staff Member
                </div>
            </div>
            <button type="button" class="btn-modal-close" id="btn_close_perm_modal">&times;</button>
        </div>
        <form id="form_update_permissions" method="POST">
            @csrf
            @method('PUT')
            <div class="user-modal-body" style="max-height: 65vh; overflow-y: auto;">
                <div id="perm_admin_notice" style="display: none; padding: 0.85rem; background: rgba(91, 33, 182, 0.1); border: 1px solid rgba(91, 33, 182, 0.3); border-radius: 12px; font-size: 0.85rem; font-weight: 700; color: #5b21b6; margin-bottom: 0.5rem;">
                    👑 <strong>Administrator Account:</strong> Admins inherently possess all module permissions unconditionally.
                </div>

                <div class="permission-modules-list" id="permission_modules_container">
                    @foreach($modules as $moduleKey => $module)
                        <div class="permission-module-card">
                            <div class="permission-module-header">
                                <div>
                                    <div class="permission-module-title">{{ $module['label'] }}</div>
                                    <div class="permission-module-desc">{{ $module['description'] }}</div>
                                </div>
                            </div>
                            <div class="permission-checkboxes-grid">
                                @foreach($module['permissions'] as $permKey => $permLabel)
                                    <label class="perm-checkbox-item">
                                        <input
                                            type="checkbox"
                                            name="permissions[{{ $permKey }}]"
                                            value="1"
                                            id="perm_check_{{ str_replace('.', '_', $permKey) }}"
                                            class="perm-checkbox"
                                            data-perm-key="{{ $permKey }}"
                                        >
                                        <div>
                                            <span>{{ $permLabel }}</span>
                                            <span class="perm-role-default-tag" id="tag_default_{{ str_replace('.', '_', $permKey) }}" style="display: none;">Default</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="user-modal-footer">
                <button type="button" class="btn-modal-cancel" id="btn_cancel_perm_modal" data-lang-key="cancel">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn-user-primary" id="btn_save_permissions" data-lang-key="save_permissions">{{ __('messages.save_permissions') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     USER DETAILS & AUDIT TRAIL MODAL
     ========================================================================== -->
<div class="user-modal-backdrop" id="view_user_modal">
    <div class="user-modal-card">
        <div class="user-modal-header">
            <h3 data-lang-key="user_details">{{ __('messages.user_details') }}</h3>
            <button type="button" class="btn-modal-close" id="btn_close_view_modal">&times;</button>
        </div>
        <div class="user-modal-body">
            <div style="display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid var(--user-border); padding-bottom: 1rem;">
                <div class="user-avatar" id="view_user_avatar" style="width: 52px; height: 52px; font-size: 1.25rem;">SC</div>
                <div>
                    <div style="font-weight: 800; font-size: 1.15rem; color: var(--user-text);" id="view_user_name">—</div>
                    <div style="font-size: 0.825rem; color: var(--user-muted);" id="view_user_username">@username</div>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.35rem;">
                        <span class="role-pill" id="view_user_role_pill">Role</span>
                        <span class="status-badge" id="view_user_status_badge">Status</span>
                    </div>
                </div>
            </div>

            <!-- Activity / Operational Stats -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; text-align: center; margin: 0.5rem 0;">
                <div style="background: var(--bg-primary, #f8fafc); border: 1px solid var(--user-border); border-radius: 12px; padding: 0.65rem;">
                    <div style="font-size: 0.7rem; font-weight: 700; color: var(--user-muted);">ORDERS</div>
                    <div style="font-size: 1.2rem; font-weight: 800; color: var(--user-text);" id="view_orders_count">0</div>
                </div>
                <div style="background: var(--bg-primary, #f8fafc); border: 1px solid var(--user-border); border-radius: 12px; padding: 0.65rem;">
                    <div style="font-size: 0.7rem; font-weight: 700; color: var(--user-muted);">SALES</div>
                    <div style="font-size: 1.2rem; font-weight: 800; color: var(--user-text);" id="view_sales_count">0</div>
                </div>
                <div style="background: var(--bg-primary, #f8fafc); border: 1px solid var(--user-border); border-radius: 12px; padding: 0.65rem;">
                    <div style="font-size: 0.7rem; font-weight: 700; color: var(--user-muted);">BAKING</div>
                    <div style="font-size: 1.2rem; font-weight: 800; color: var(--user-text);" id="view_prod_count">0</div>
                </div>
            </div>

            <div>
                <h4 style="font-size: 0.85rem; font-weight: 800; text-transform: uppercase; color: var(--user-muted); margin: 0 0 0.5rem 0;" data-lang-key="audit_trail">
                    {{ __('messages.audit_trail') }}
                </h4>
                <div class="audit-timeline" id="view_user_audit_timeline">
                    <div style="font-size: 0.8rem; color: var(--user-muted); padding: 0.5rem 0;">Loading activity trail...</div>
                </div>
            </div>
        </div>
        <div class="user-modal-footer">
            <button type="button" class="btn-modal-cancel" id="btn_close_view_footer" style="width: 100%; text-align: center;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal Helpers
    function openModal(modal) {
        if (modal) modal.classList.add('is-active');
    }
    function closeModal(modal) {
        if (modal) modal.classList.remove('is-active');
    }

    // 1. Create User Modal
    const createModal = document.getElementById('create_user_modal');
    const btnOpenCreate = document.getElementById('btn_open_create_user_modal');
    const btnCloseCreate = document.getElementById('btn_close_create_user');
    const btnCancelCreate = document.getElementById('btn_cancel_create_user');

    if (btnOpenCreate) btnOpenCreate.addEventListener('click', () => openModal(createModal));
    if (btnCloseCreate) btnCloseCreate.addEventListener('click', () => closeModal(createModal));
    if (btnCancelCreate) btnCancelCreate.addEventListener('click', () => closeModal(createModal));

    // 2. Edit User Modal
    const editModal = document.getElementById('edit_user_modal');
    const btnCloseEdit = document.getElementById('btn_close_edit_user');
    const btnCancelEdit = document.getElementById('btn_cancel_edit_user');
    const formEdit = document.getElementById('form_edit_user');

    document.querySelectorAll('.btn-edit-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            formEdit.action = `/admin/users/${id}`;
            document.getElementById('edit_user_name').value = this.dataset.name || '';
            document.getElementById('edit_user_username').value = this.dataset.username || '';
            document.getElementById('edit_user_email').value = this.dataset.email || '';
            document.getElementById('edit_user_phone').value = this.dataset.phone || '';
            document.getElementById('edit_user_role').value = this.dataset.role || 'cashier';
            document.getElementById('edit_user_status').value = this.dataset.status || 'active';
            document.getElementById('edit_must_change_password').checked = (this.dataset.mustchange === '1');
            document.getElementById('edit_user_password').value = '';
            openModal(editModal);
        });
    });

    if (btnCloseEdit) btnCloseEdit.addEventListener('click', () => closeModal(editModal));
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', () => closeModal(editModal));

    // 3. Reset Password Modal
    const resetModal = document.getElementById('reset_password_modal');
    const btnCloseReset = document.getElementById('btn_close_reset_pwd');
    const btnCancelReset = document.getElementById('btn_cancel_reset_pwd');
    const formReset = document.getElementById('form_reset_password');
    const resetTitle = document.getElementById('reset_pwd_user_title');

    document.querySelectorAll('.btn-reset-pwd-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const username = this.dataset.username;
            formReset.action = `/admin/users/${id}/reset-password`;
            resetTitle.textContent = `${name} (@${username})`;
            document.getElementById('reset_pwd_input').value = '';
            openModal(resetModal);
        });
    });

    if (btnCloseReset) btnCloseReset.addEventListener('click', () => closeModal(resetModal));
    if (btnCancelReset) btnCancelReset.addEventListener('click', () => closeModal(resetModal));

    // 4. Status Quick Update Modal
    const statusModal = document.getElementById('status_user_modal');
    const btnCloseStatus = document.getElementById('btn_close_status_modal');
    const btnCancelStatus = document.getElementById('btn_cancel_status_modal');
    const formStatus = document.getElementById('form_status_user');
    const statusUserName = document.getElementById('status_user_name');
    const statusSelect = document.getElementById('modal_status_select');

    document.querySelectorAll('.btn-status-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const currentStatus = this.dataset.status;
            formStatus.action = `/admin/users/${id}/status`;
            statusUserName.textContent = name;
            statusSelect.value = currentStatus;
            openModal(statusModal);
        });
    });

    if (btnCloseStatus) btnCloseStatus.addEventListener('click', () => closeModal(statusModal));
    if (btnCancelStatus) btnCancelStatus.addEventListener('click', () => closeModal(statusModal));

    // 5. Permissions Management Modal
    const permModal = document.getElementById('permissions_user_modal');
    const btnClosePerm = document.getElementById('btn_close_perm_modal');
    const btnCancelPerm = document.getElementById('btn_cancel_perm_modal');
    const formPerm = document.getElementById('form_update_permissions');
    const permSubtitle = document.getElementById('perm_user_subtitle');
    const permAdminNotice = document.getElementById('perm_admin_notice');
    const btnSavePerm = document.getElementById('btn_save_permissions');

    document.querySelectorAll('.btn-permissions-user').forEach(btn => {
        btn.addEventListener('click', async function() {
            const userId = this.dataset.id;
            const userName = this.dataset.name;
            const userRole = this.dataset.role;

            formPerm.action = `/admin/users/${userId}/permissions`;
            permSubtitle.textContent = `Configuring permissions for ${userName} (${userRole.toUpperCase()})`;

            // Fetch current permissions state
            try {
                const response = await fetch(`/admin/users/${userId}/permissions`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                const isAdmin = (userRole.toLowerCase() === 'admin');
                if (isAdmin) {
                    permAdminNotice.style.display = 'block';
                    btnSavePerm.disabled = true;
                } else {
                    permAdminNotice.style.display = 'none';
                    btnSavePerm.disabled = false;
                }

                const defaults = data.role_defaults || [];
                const overrides = data.custom_overrides || {};
                const effective = data.effective_permissions || [];

                document.querySelectorAll('.perm-checkbox').forEach(cb => {
                    const key = cb.dataset.permKey;
                    const safeKey = key.replace(/\./g, '_');
                    const defaultTag = document.getElementById(`tag_default_${safeKey}`);

                    // Show or hide default badge
                    if (defaultTag) {
                        defaultTag.style.display = defaults.includes(key) ? 'inline-block' : 'none';
                    }

                    // Checked state
                    if (isAdmin) {
                        cb.checked = true;
                        cb.disabled = true;
                    } else {
                        cb.disabled = false;
                        cb.checked = effective.includes(key);
                    }
                });

                openModal(permModal);
            } catch (err) {
                console.error('Error fetching permissions:', err);
                alert('Could not load user permissions. Please try again.');
            }
        });
    });

    if (btnClosePerm) btnClosePerm.addEventListener('click', () => closeModal(permModal));
    if (btnCancelPerm) btnCancelPerm.addEventListener('click', () => closeModal(permModal));

    // 6. View User Details & Audit Trail Modal
    const viewModal = document.getElementById('view_user_modal');
    const btnCloseView = document.getElementById('btn_close_view_modal');
    const btnCloseViewFooter = document.getElementById('btn_close_view_footer');
    const viewName = document.getElementById('view_user_name');
    const viewUsername = document.getElementById('view_user_username');
    const viewAvatar = document.getElementById('view_user_avatar');
    const viewRolePill = document.getElementById('view_user_role_pill');
    const viewStatusBadge = document.getElementById('view_user_status_badge');
    const viewOrders = document.getElementById('view_orders_count');
    const viewSales = document.getElementById('view_sales_count');
    const viewProd = document.getElementById('view_prod_count');
    const viewTimeline = document.getElementById('view_user_audit_timeline');

    document.querySelectorAll('.btn-view-user').forEach(btn => {
        btn.addEventListener('click', async function() {
            const userId = this.dataset.id;
            viewTimeline.innerHTML = '<div style="font-size: 0.8rem; color: var(--user-muted); padding: 0.5rem 0;">Loading activity history...</div>';
            openModal(viewModal);

            try {
                const response = await fetch(`/admin/users/${userId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                const u = data.user;

                viewName.textContent = u.name;
                viewUsername.textContent = `@${u.username}`;
                viewAvatar.textContent = (u.name || '').substring(0, 2).toUpperCase();

                viewRolePill.className = `role-pill role-${u.role}`;
                viewRolePill.textContent = u.role.toUpperCase();

                viewStatusBadge.className = `status-badge status-${u.status}`;
                viewStatusBadge.innerHTML = `<span class="status-dot"></span> ${u.status.toUpperCase()}`;

                viewOrders.textContent = u.orders_count || 0;
                viewSales.textContent = u.sales_count || 0;
                viewProd.textContent = u.productions_count || 0;

                const audits = data.recent_audits || [];
                if (audits.length === 0) {
                    viewTimeline.innerHTML = '<div style="font-size: 0.8rem; color: var(--user-muted); padding: 0.5rem 0;">No recorded audit events yet.</div>';
                } else {
                    viewTimeline.innerHTML = audits.map(a => {
                        const dateStr = new Date(a.created_at).toLocaleString();
                        return `
                            <div class="audit-timeline-item">
                                <div>
                                    <div class="audit-action-title">${escapeHtml(a.description || a.action)}</div>
                                    <div class="audit-meta-time">${dateStr} • IP: ${a.ip_address || '—'}</div>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            } catch (err) {
                console.error('Error fetching user details:', err);
                viewTimeline.innerHTML = '<div style="font-size: 0.8rem; color: #ef4444; padding: 0.5rem 0;">Failed to load user details.</div>';
            }
        });
    });

    if (btnCloseView) btnCloseView.addEventListener('click', () => closeModal(viewModal));
    if (btnCloseViewFooter) btnCloseViewFooter.addEventListener('click', () => closeModal(viewModal));

    // Close on backdrop click
    document.querySelectorAll('.user-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this);
        });
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endpush
@endsection
