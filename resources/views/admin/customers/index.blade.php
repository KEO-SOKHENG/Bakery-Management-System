@extends('layouts.app')

@section('title', 'Customer Management - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/customers.css') }}">
    <link rel="stylesheet" href="{{ asset('css/orders.css') }}">
@endpush

@section('header_toolbar')
    <div class="period-pill-group">
        <a href="{{ route('admin.customers.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
           class="period-pill {{ (!request()->filled('status') || request('status') === 'all') ? 'active' : '' }}">
            <span>All Customers ({{ number_format($totalCustomers) }})</span>
        </a>
        <a href="{{ route('admin.customers.index', array_merge(request()->except('status'), ['status' => 'active'])) }}"
           class="period-pill {{ request('status') === 'active' ? 'active' : '' }}">
            <span>Active Accounts ({{ number_format($activeCustomers) }})</span>
        </a>
        <a href="{{ route('admin.customers.index', array_merge(request()->except('status'), ['status' => 'inactive'])) }}"
           class="period-pill {{ request('status') === 'inactive' ? 'active' : '' }}">
            <span>Inactive ({{ number_format(max(0, $totalCustomers - $activeCustomers)) }})</span>
        </a>
    </div>
@endsection

@section('content')
<div class="orders-container">

    <h1 class="sr-only">Customer Management</h1>

    <!-- Flash Notifications -->
    @if(session('success'))
        <x-alert type="success" :message="session('success')" />
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

    <!-- 1. 4 Core KPI Stat Cards Grid (Exact Orders / Dashboard Page Style) -->
    <div class="dash-stat-row customer-stats-grid">
        <div class="stat-card">
            <div class="stat-icon-box blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Customers</span>
                <div class="stat-value">{{ number_format($totalCustomers) }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Registered
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Active Accounts</span>
                <div class="stat-value">{{ number_format($activeCustomers) }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Active
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box yellow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Loyalty Members</span>
                <div class="stat-value">{{ number_format($loyaltyMembers) }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Points Active
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Customer Revenue</span>
                <div class="stat-value">${{ number_format($totalCustomerRevenue, 2) }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Total Spend
                </span>
            </div>
        </div>
    </div>

    <!-- 2. Action & Filter Toolbar Row (Exact Orders & Ingredients Style) -->
    <div class="ingredients-toolbar-row" id="customers_toolbar" style="margin-bottom: 0.75rem; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <!-- Left: Filters without toolbar search input -->
        <form method="GET" action="{{ route('admin.customers.index') }}" id="customers_filter_form" style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; margin: 0;">
            @if(request('search'))
                <input type="hidden" name="search" id="customer_search_input" value="{{ request('search') }}">
            @endif

            <!-- Pill Filter Card for Tier & Status -->
            <div class="supplier-filter-pill-card">
                <select name="tier" id="customer_tier_select" onchange="this.form.submit()">
                    <option value="all" {{ (!request()->filled('tier') || request('tier') === 'all') ? 'selected' : '' }}>All Loyalty Tiers</option>
                    <option value="standard" {{ request('tier') == 'standard' ? 'selected' : '' }}>Standard Tier</option>
                    <option value="silver" {{ request('tier') == 'silver' ? 'selected' : '' }}>Silver Tier (100+ pts)</option>
                    <option value="gold" {{ request('tier') == 'gold' ? 'selected' : '' }}>Gold Tier (300+ pts)</option>
                    <option value="vip" {{ request('tier') == 'vip' ? 'selected' : '' }}>VIP Tier (500+ pts)</option>
                </select>

                <select name="status" id="customer_status_select" onchange="this.form.submit()">
                    <option value="all" {{ (!request()->filled('status') || request('status') === 'all') ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="btn" style="border-radius: 50px; padding: 0.35rem 1rem; font-weight: 700; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: #ea580c; border: 1px solid #ea580c; color: #ffffff; cursor: pointer; font-size: 0.825rem;">Filter</button>

                @if(request()->hasAny(['search', 'tier', 'status']) && (request('search') || (request('tier') && request('tier') !== 'all') || (request('status') && request('status') !== 'all')))
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary" style="border-radius: 50px; padding: 0.35rem 0.85rem; font-weight: 600; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">Clear</a>
                @endif
            </div>
        </form>

        <!-- Right: Add Customer Button -->
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button type="button" class="btn" id="btn_open_add_customer_modal" style="border-radius: 50px; height: 36px; padding: 0 1.15rem; display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; background: #ea580c; border: 1px solid #ea580c; color: #ffffff; cursor: pointer; font-size: 0.875rem; box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span data-lang-key="add_customer">{{ __('messages.add_customer') }}</span>
            </button>
        </div>
    </div>

    <!-- 3. Customers Table Card (Full Viewport Height with internal scrolling) -->
    <div class="orders-card" id="customers_table_card">
        <div class="orders-table-wrapper" id="customers_table_wrapper">
            <table class="orders-table" id="customers_table">
                <thead>
                    <tr>
                        <th style="width: 26%;">Customer Name</th>
                        <th style="width: 18%;">Contact Info</th>
                        <th style="width: 18%;">Loyalty Points & Tier</th>
                        <th style="width: 12%;">Orders</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 14%; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        @php
                            $initials = strtoupper(substr($customer->name, 0, 2));
                            $tier = strtolower($customer->loyalty_tier ?? 'standard');
                            $tierIcon = match($tier) {
                                'vip' => '👑',
                                'gold' => '⭐',
                                'silver' => '🥈',
                                default => '🥉',
                            };
                        @endphp
                        <tr id="customer_row_{{ $customer->id }}">
                            <!-- Customer Name & Address -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #563020 0%, #ea580c 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; flex-shrink: 0; box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25);">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.customers.show', $customer->id) }}" style="font-weight: 700; font-size: 0.9rem; color: #ea580c; text-decoration: none;">
                                            {{ $customer->name }}
                                        </a>
                                        <div style="font-size: 0.725rem; color: var(--text-muted, #71717a); max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $customer->address ?? 'No address registered' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact Info -->
                            <td>
                                <div style="font-weight: 700; font-size: 0.875rem; color: var(--text-primary, #0f172a);">{{ $customer->phone ?? '—' }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted, #71717a);">{{ $customer->email ?? '—' }}</div>
                            </td>

                            <!-- Loyalty Points & Tier -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <span class="tier-pill tier-{{ $tier }}" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 2px 8px; border-radius: 50px; font-size: 0.75rem; font-weight: 700;">
                                        {{ $tierIcon }} {{ ucfirst($tier) }}
                                    </span>
                                    <span style="font-weight: 800; font-size: 0.85rem; color: var(--text-primary, #0f172a);">
                                        {{ number_format($customer->loyalty_points) }} pts
                                    </span>
                                </div>
                            </td>

                            <!-- Orders -->
                            <td>
                                <span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 6px;">
                                    {{ $customer->orders_count }} Orders
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="status-badge {{ $customer->status === 'active' ? 'status-completed' : 'status-cancelled' }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 0.4rem;">
                                    <!-- View Purchase History Details -->
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn-icon-action" title="View Purchase History & Profile">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>

                                    <!-- Edit Customer Button -->
                                    <button
                                        type="button"
                                        class="btn-icon-action btn-edit-customer"
                                        title="Edit Customer"
                                        data-id="{{ $customer->id }}"
                                        data-name="{{ $customer->name }}"
                                        data-phone="{{ $customer->phone }}"
                                        data-email="{{ $customer->email }}"
                                        data-address="{{ $customer->address }}"
                                        data-points="{{ $customer->loyalty_points }}"
                                        data-status="{{ $customer->status }}"
                                    >
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    </button>

                                    <!-- Delete Customer Form -->
                                    <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Delete customer \'{{ $customer->name }}\'? Their past order records will remain safely preserved with customer detached.');" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-action delete" title="Delete Customer">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted, #71717a);">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🥖</div>
                                <div style="font-size: 1rem; font-weight: 700; color: var(--text-primary, #0f172a);">No Customers Found</div>
                                <p style="font-size: 0.85rem; margin-top: 0.25rem;">Try adjusting your search criteria or register a new customer above.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================================================
     ADD CUSTOMER MODAL
     ========================================================================== -->
<div class="cust-modal-backdrop" id="add_customer_modal">
    <div class="cust-modal-card">
        <div class="cust-modal-header">
            <h3 data-lang-key="add_customer">Add Customer</h3>
            <button type="button" class="btn-modal-close" id="btn_close_add_customer">&times;</button>
        </div>
        <form action="{{ route('admin.customers.store') }}" method="POST">
            @csrf
            <div class="cust-modal-body">
                <div class="form-field-group">
                    <label for="add_cust_name" data-lang-key="customer_name">Full Name *</label>
                    <input type="text" id="add_cust_name" name="name" required placeholder="e.g. John Doe">
                </div>

                <div class="form-field-group">
                    <label for="add_cust_phone" data-lang-key="phone">Phone Number</label>
                    <input type="text" id="add_cust_phone" name="phone" placeholder="e.g. 012 345 678">
                </div>

                <div class="form-field-group">
                    <label for="add_cust_email" data-lang-key="email">Email Address</label>
                    <input type="email" id="add_cust_email" name="email" placeholder="e.g. customer@example.com">
                </div>

                <div class="form-field-group">
                    <label for="add_cust_address" data-lang-key="address">Address</label>
                    <textarea id="add_cust_address" name="address" rows="2" placeholder="Street, Khan / District, City"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-field-group">
                        <label for="add_cust_points" data-lang-key="loyalty_points">Initial Loyalty Points</label>
                        <input type="number" id="add_cust_points" name="loyalty_points" min="0" value="0">
                    </div>

                    <div class="form-field-group">
                        <label for="add_cust_status" data-lang-key="status">Status</label>
                        <select id="add_cust_status" name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="cust-modal-footer">
                <x-button variant="secondary" id="btn_cancel_add_customer" data-lang-key="cancel">Cancel</x-button>
                <x-button variant="primary" type="submit" class="btn-cust-primary" data-lang-key="save">Save Customer</x-button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     EDIT CUSTOMER MODAL
     ========================================================================== -->
<div class="cust-modal-backdrop" id="edit_customer_modal">
    <div class="cust-modal-card">
        <div class="cust-modal-header">
            <h3 data-lang-key="edit_customer">Edit Customer Profile</h3>
            <button type="button" class="btn-modal-close" id="btn_close_edit_customer">&times;</button>
        </div>
        <form id="form_edit_customer" method="POST">
            @csrf
            @method('PUT')
            <div class="cust-modal-body">
                <div class="form-field-group">
                    <label for="edit_cust_name" data-lang-key="customer_name">Full Name *</label>
                    <input type="text" id="edit_cust_name" name="name" required>
                </div>

                <div class="form-field-group">
                    <label for="edit_cust_phone" data-lang-key="phone">Phone Number</label>
                    <input type="text" id="edit_cust_phone" name="phone">
                </div>

                <div class="form-field-group">
                    <label for="edit_cust_email" data-lang-key="email">Email Address</label>
                    <input type="email" id="edit_cust_email" name="email">
                </div>

                <div class="form-field-group">
                    <label for="edit_cust_address" data-lang-key="address">Address</label>
                    <textarea id="edit_cust_address" name="address" rows="2"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-field-group">
                        <label for="edit_cust_points" data-lang-key="loyalty_points">Loyalty Points</label>
                        <input type="number" id="edit_cust_points" name="loyalty_points" min="0">
                    </div>

                    <div class="form-field-group">
                        <label for="edit_cust_status" data-lang-key="status">Status</label>
                        <select id="edit_cust_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="cust-modal-footer">
                <x-button variant="secondary" id="btn_cancel_edit_customer" data-lang-key="cancel">Cancel</x-button>
                <x-button variant="primary" type="submit" class="btn-cust-primary" data-lang-key="save">Update Customer</x-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Modal handlers
    const addModal = document.getElementById('add_customer_modal');
    const btnOpenAdd = document.getElementById('btn_open_add_customer_modal');
    const btnCloseAdd = document.getElementById('btn_close_add_customer');
    const btnCancelAdd = document.getElementById('btn_cancel_add_customer');

    if (btnOpenAdd) {
        btnOpenAdd.addEventListener('click', () => addModal.classList.add('is-active'));
    }
    if (btnCloseAdd) {
        btnCloseAdd.addEventListener('click', () => addModal.classList.remove('is-active'));
    }
    if (btnCancelAdd) {
        btnCancelAdd.addEventListener('click', () => addModal.classList.remove('is-active'));
    }

    // Edit Modal handlers
    const editModal = document.getElementById('edit_customer_modal');
    const editForm = document.getElementById('form_edit_customer');
    const btnCloseEdit = document.getElementById('btn_close_edit_customer');
    const btnCancelEdit = document.getElementById('btn_cancel_edit_customer');

    document.querySelectorAll('.btn-edit-customer').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const phone = this.getAttribute('data-phone');
            const email = this.getAttribute('data-email');
            const address = this.getAttribute('data-address');
            const points = this.getAttribute('data-points');
            const status = this.getAttribute('data-status');

            editForm.action = `/admin/customers/${id}`;
            document.getElementById('edit_cust_name').value = name || '';
            document.getElementById('edit_cust_phone').value = phone || '';
            document.getElementById('edit_cust_email').value = email || '';
            document.getElementById('edit_cust_address').value = address || '';
            document.getElementById('edit_cust_points').value = points || 0;
            document.getElementById('edit_cust_status').value = status || 'active';

            editModal.classList.add('is-active');
        });
    });

    if (btnCloseEdit) {
        btnCloseEdit.addEventListener('click', () => editModal.classList.remove('is-active'));
    }
    if (btnCancelEdit) {
        btnCancelEdit.addEventListener('click', () => editModal.classList.remove('is-active'));
    }

    // Close on backdrop click
    [addModal, editModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('is-active');
            }
        });
    });
});
</script>
@endpush
@endsection
