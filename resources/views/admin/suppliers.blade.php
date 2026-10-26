@extends('layouts.app')

@section('title', 'Suppliers & Vendors - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/orders.css') }}">
@endpush

@section('header_toolbar')
    <div class="period-pill-group">
        <a href="{{ route('admin.suppliers', array_merge(request()->except('status'), ['status' => 'all'])) }}"
           class="period-pill {{ (!request()->filled('status') || request('status') === 'all') ? 'active' : '' }}">
            <span>All Suppliers ({{ $stats['total'] }})</span>
        </a>
        <a href="{{ route('admin.suppliers', array_merge(request()->except('status'), ['status' => 'active'])) }}"
           class="period-pill {{ request('status') === 'active' ? 'active' : '' }}">
            <span>Active Partners ({{ $stats['active'] }})</span>
        </a>
        <a href="{{ route('admin.suppliers', array_merge(request()->except('status'), ['status' => 'inactive'])) }}"
           class="period-pill {{ request('status') === 'inactive' ? 'active' : '' }}">
            <span>Inactive Vendors ({{ $stats['inactive'] }})</span>
        </a>
    </div>
@endsection

@section('content')
<div class="orders-container">

    <h1 class="sr-only">Raw Material Suppliers</h1>

    <!-- Alert Notifications -->
    @if(session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <!-- 1. 4 Core KPI Stat Cards Grid (Exact Orders / Dashboard Page Style) -->
    <div class="dash-stat-row">
        <div class="stat-card">
            <div class="stat-icon-box orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Suppliers</span>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Vendor Base
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Active Partners</span>
                <div class="stat-value">{{ $stats['active'] }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Verified Active
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Inactive Vendors</span>
                <div class="stat-value">{{ $stats['inactive'] }}</div>
                <span class="stat-trend {{ $stats['inactive'] > 0 ? 'warning' : 'up' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    {{ $stats['inactive'] > 0 ? 'Dormant' : 'All Active' }}
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Procured Spend</span>
                <div class="stat-value">${{ number_format($stats['total_spend'], 2) }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Procurement
                </span>
            </div>
        </div>
    </div>

    <!-- 2. Action & Filter Toolbar Row (Exact Orders & Ingredients Style) -->
    <div class="ingredients-toolbar-row" id="suppliers_toolbar" style="margin-bottom: 0.75rem; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <!-- Left: Search and Status Filters -->
        <form method="GET" action="{{ route('admin.suppliers') }}" id="suppliers_filter_form" style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; margin: 0;">
            @if(request('search'))
                <input type="hidden" name="search" id="suppliers_search_input" value="{{ request('search') }}">
            @endif

            <!-- Status Pill Filter -->
            <div class="supplier-filter-pill-card">
                <select name="status" id="supplier_status_select" onchange="this.form.submit()">
                    <option value="all" {{ (!request()->filled('status') || request('status') === 'all') ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="btn" style="border-radius: 50px; padding: 0.35rem 1rem; font-weight: 700; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: #ea580c; border: 1px solid #ea580c; color: #ffffff; cursor: pointer; font-size: 0.825rem;">Filter</button>

                @if(request()->hasAny(['search', 'status']) && (request('search') || (request('status') && request('status') !== 'all')))
                    <a href="{{ route('admin.suppliers') }}" class="btn btn-secondary" style="border-radius: 50px; padding: 0.35rem 0.85rem; font-weight: 600; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">Clear</a>
                @endif
            </div>
        </form>

        <!-- Right: Purchase Orders & Add Supplier Buttons -->
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary" style="border-radius: 50px; height: 36px; padding: 0 1rem; display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.825rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>Purchase Orders</span>
            </a>
            <button type="button" class="btn" id="btn_open_supplier_modal" style="border-radius: 50px; height: 36px; padding: 0 1.15rem; display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; background: #ea580c; border: 1px solid #ea580c; color: #ffffff; cursor: pointer; font-size: 0.875rem; box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Add Supplier</span>
            </button>
        </div>
    </div>

    <!-- 3. Suppliers Table Card (Full Viewport Height with internal scrolling) -->
    <div class="orders-card" id="suppliers_table_card">
        <div class="orders-table-wrapper" id="suppliers_table_wrapper">
            <table class="orders-table" id="suppliers_table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Vendor / Company</th>
                        <th style="width: 14%;">Contact Person</th>
                        <th style="width: 12%;">Phone</th>
                        <th style="width: 15%;">Email</th>
                        <th style="width: 11%;">Supplied Items</th>
                        <th style="width: 10%;">Orders</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 8%; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <!-- Vendor / Company -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(234, 88, 12, 0.1); display: flex; align-items: center; justify-content: center; color: #ea580c; font-weight: 800; font-size: 0.95rem; flex-shrink: 0;">
                                        {{ strtoupper(substr($supplier->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.suppliers.show', $supplier->id) }}" style="font-weight: 700; font-size: 0.9rem; color: #ea580c; text-decoration: none;">
                                            {{ $supplier->name }}
                                        </a>
                                        <div style="font-size: 0.725rem; color: var(--text-muted, #71717a); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $supplier->address ?? 'No address registered' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact Person -->
                            <td style="font-weight: 600; color: inherit; font-size: 0.85rem;">
                                {{ $supplier->contact_person ?? '—' }}
                            </td>

                            <!-- Phone -->
                            <td style="font-size: 0.825rem; font-weight: 600; color: var(--text-muted, #a1a1aa);">
                                {{ $supplier->phone ?? '—' }}
                            </td>

                            <!-- Email -->
                            <td style="font-size: 0.825rem;">
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" style="color: #3b82f6; text-decoration: none; font-weight: 600;">
                                        {{ $supplier->email }}
                                    </a>
                                @else
                                    <span style="color: #71717a;">—</span>
                                @endif
                            </td>

                            <!-- Supplied Ingredients -->
                            <td>
                                <span style="background: rgba(234, 88, 12, 0.08); color: #ea580c; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 6px;">
                                    {{ $supplier->ingredients_count }} items
                                </span>
                            </td>

                            <!-- Purchase Orders -->
                            <td>
                                <span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 6px;">
                                    {{ $supplier->purchase_orders_count }} orders
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="status-badge {{ $supplier->status === 'active' ? 'status-completed' : 'status-cancelled' }}">
                                    {{ ucfirst($supplier->status) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 0.4rem;">
                                    <a href="{{ route('admin.suppliers.show', $supplier->id) }}" class="table-icon-btn" title="View Supplier Profile">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <button type="button" class="table-icon-btn btn-edit-supplier" title="Edit Supplier" 
                                        data-id="{{ $supplier->id }}"
                                        data-name="{{ $supplier->name }}"
                                        data-contact="{{ $supplier->contact_person }}"
                                        data-phone="{{ $supplier->phone }}"
                                        data-email="{{ $supplier->email }}"
                                        data-address="{{ $supplier->address }}"
                                        data-status="{{ $supplier->status }}"
                                        data-notes="{{ $supplier->notes }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete supplier \'{{ $supplier->name }}\'?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-action delete" title="Delete Supplier" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 4px; display: inline-flex; align-items: center;">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted, #71717a); padding: 3rem;">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏭</div>
                                <div style="font-weight: 700; font-size: 1rem; color: inherit;">No suppliers found</div>
                                <div style="font-size: 0.825rem; margin-top: 0.25rem;">Try adjusting search terms or click "Add Supplier" to register a new vendor.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD SUPPLIER MODAL -->
<x-modal id="add_supplier_modal" title="Add Supplier" maxWidth="540px" class="dash-modal-overlay">
    <form action="{{ route('admin.suppliers.store') }}" method="POST">
        @csrf
        <x-input name="name" label="Company / Vendor Name" placeholder="e.g. Royal Flour Mills Ltd." :required="true" />
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input name="contact_person" label="Contact Person" placeholder="e.g. John Doe" />
            <x-select name="status" label="Status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-select>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input name="phone" label="Phone Number" placeholder="+855 12 345 678" />
            <x-input type="email" name="email" label="Email Address" placeholder="orders@vendor.com" />
        </div>
        <x-input name="address" label="Physical Address / Facility" placeholder="e.g. St. 2004, Phnom Penh, Cambodia" />
        <div class="form-group">
            <label class="form-label">Notes & Procurement Terms</label>
            <textarea name="notes" rows="2" class="form-control-textarea" placeholder="e.g. Net 30 payment terms, weekly delivery on Mondays"></textarea>
        </div>
        <div class="modal-footer" style="padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <x-button variant="secondary" type="button" id="btn_cancel_supplier">Cancel</x-button>
            <x-button variant="primary" type="submit">Create Supplier</x-button>
        </div>
    </form>
</x-modal>

<!-- EDIT SUPPLIER MODAL -->
<x-modal id="edit_supplier_modal" title="Edit Supplier" maxWidth="540px" class="dash-modal-overlay">
    <form id="edit_supplier_form" method="POST">
        @csrf
        @method('PUT')
        <x-input name="name" id="edit_supplier_name" label="Company / Vendor Name" :required="true" />
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input name="contact_person" id="edit_supplier_contact" label="Contact Person" />
            <x-select name="status" id="edit_supplier_status" label="Status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-select>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input name="phone" id="edit_supplier_phone" label="Phone Number" />
            <x-input type="email" name="email" id="edit_supplier_email" label="Email Address" />
        </div>
        <x-input name="address" id="edit_supplier_address" label="Physical Address / Facility" />
        <div class="form-group">
            <label class="form-label">Notes & Procurement Terms</label>
            <textarea name="notes" id="edit_supplier_notes" rows="2" class="form-control-textarea"></textarea>
        </div>
        <div class="modal-footer" style="padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <x-button variant="secondary" type="button" id="btn_cancel_edit_supplier">Cancel</x-button>
            <x-button variant="primary" type="submit">Update Supplier</x-button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add Modal
        const addModal = document.getElementById('add_supplier_modal');
        const openAddBtn = document.getElementById('btn_open_supplier_modal');
        const closeAddBtn = document.getElementById('btn_close_supplier_modal');
        const cancelAddBtn = document.getElementById('btn_cancel_supplier');

        function openAdd() {
            if (addModal) {
                addModal.classList.add('active', 'is-active');
                addModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        function closeAdd() {
            if (addModal) {
                addModal.classList.remove('active', 'is-active');
                addModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        if (openAddBtn) openAddBtn.addEventListener('click', openAdd);
        if (closeAddBtn) closeAddBtn.addEventListener('click', closeAdd);
        if (cancelAddBtn) cancelAddBtn.addEventListener('click', closeAdd);
        if (addModal) {
            addModal.addEventListener('click', (e) => { if (e.target === addModal) closeAdd(); });
        }

        // Edit Modal
        const editModal = document.getElementById('edit_supplier_modal');
        const editForm = document.getElementById('edit_supplier_form');
        const closeEditBtn = document.getElementById('btn_close_edit_modal');
        const cancelEditBtn = document.getElementById('btn_cancel_edit_supplier');

        function closeEdit() {
            if (editModal) {
                editModal.classList.remove('active', 'is-active');
                editModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        if (closeEditBtn) closeEditBtn.addEventListener('click', closeEdit);
        if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEdit);
        if (editModal) {
            editModal.addEventListener('click', (e) => { if (e.target === editModal) closeEdit(); });
        }

        document.querySelectorAll('.btn-edit-supplier').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                editForm.action = `/admin/suppliers/${id}`;
                document.getElementById('edit_supplier_name').value = this.getAttribute('data-name') || '';
                document.getElementById('edit_supplier_contact').value = this.getAttribute('data-contact') || '';
                document.getElementById('edit_supplier_phone').value = this.getAttribute('data-phone') || '';
                document.getElementById('edit_supplier_email').value = this.getAttribute('data-email') || '';
                document.getElementById('edit_supplier_address').value = this.getAttribute('data-address') || '';
                document.getElementById('edit_supplier_status').value = this.getAttribute('data-status') || 'active';
                document.getElementById('edit_supplier_notes').value = this.getAttribute('data-notes') || '';

                editModal.classList.add('active', 'is-active');
                editModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });
    });
</script>
@endpush
@endsection
