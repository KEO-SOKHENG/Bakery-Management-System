@extends('layouts.app')

@section('title', 'Suppliers & Vendors - Bakery Management System')

@section('content')
<div class="suppliers-header-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0; color: var(--text-primary, #1e293b);">
            Raw Material Suppliers & Vendors
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted, #64748b); margin-top: 0.25rem;">
            Manage ingredient vendors, purchasing contacts, and procurement history
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 9999px; font-weight: 700; text-decoration: none; border: 1px solid var(--border-color, #e2e8f0); color: var(--text-primary, #1e293b); background: var(--bg-card, #ffffff);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <span>View Purchase Orders</span>
        </a>
        <button class="btn btn-primary" id="btn_open_supplier_modal" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(77, 44, 32, 0.25);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Add Supplier</span>
        </button>
    </div>
</div>

<!-- KPI Metrics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(77, 44, 32, 0.08); display: flex; align-items: center; justify-content: center; color: #4d2c20; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Suppliers</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary, #0f172a);">{{ $stats['total'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Active Partners</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #10b981;">{{ $stats['active'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Inactive Vendors</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #f59e0b;">{{ $stats['inactive'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #3b82f6; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Procured Spend</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #3b82f6;">${{ number_format($stats['total_spend'], 2) }}</div>
        </div>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="bakery-card" style="padding: 1rem 1.25rem; border-radius: 1rem; margin-bottom: 1.25rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
    <form method="GET" action="{{ route('admin.suppliers') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 0.75rem; flex: 1; min-width: 280px;">
            <div style="position: relative; flex: 1;">
                <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier name, contact, phone, or email..." class="ios-input-field" style="padding-left: 2.35rem; width: 100%; border-radius: 0.6rem; border: 1px solid var(--border-color, #d4d4d8); height: 40px;">
            </div>
            <select name="status" class="ios-select-field" style="width: 140px; border-radius: 0.6rem; border: 1px solid var(--border-color, #d4d4d8); height: 40px;" onchange="this.form.submit()">
                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; border-radius: 0.6rem; background: #4d2c20; color: #fff; font-weight: 700; border: none; cursor: pointer;">Search</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.suppliers') }}" class="btn btn-secondary" style="padding: 0.5rem 0.85rem; border-radius: 0.6rem; border: 1px solid #d4d4d8; text-decoration: none; color: #64748b;">Clear</a>
            @endif
        </div>
    </form>
</div>

<!-- Suppliers Table -->
<div class="bakery-card" style="border-radius: 1rem; overflow: hidden; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
    <div class="table-responsive-wrapper">
        <table class="recent-orders-table" id="suppliers_table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Vendor / Company</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Supplied Ingredients</th>
                    <th>Purchase Orders</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(77, 44, 32, 0.08); display: flex; align-items: center; justify-content: center; color: #4d2c20; font-weight: 800; font-size: 1rem; flex-shrink: 0;">
                                    {{ strtoupper(substr($supplier->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.suppliers.show', $supplier->id) }}" style="font-weight: 800; font-size: 0.95rem; color: #4d2c20; text-decoration: none;">
                                        {{ $supplier->name }}
                                    </a>
                                    <div style="font-size: 0.75rem; color: var(--text-muted, #71717a); max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $supplier->address ?? 'No address registered' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight: 600; color: var(--text-primary, #1e293b);">
                            {{ $supplier->contact_person ?? '—' }}
                        </td>
                        <td style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b);">
                            {{ $supplier->phone ?? '—' }}
                        </td>
                        <td>
                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}" style="color: #2563eb; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                                    {{ $supplier->email }}
                                </a>
                            @else
                                <span style="color: #a1a1aa;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="type-pill" style="background-color: rgba(77, 44, 32, 0.08); color: #4d2c20; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px;">
                                {{ $supplier->ingredients_count }} items
                            </span>
                        </td>
                        <td>
                            <span class="type-pill" style="background-color: rgba(59, 130, 246, 0.1); color: #2563eb; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px;">
                                {{ $supplier->purchase_orders_count }} orders
                            </span>
                        </td>
                        <td>
                            @if($supplier->status === 'active')
                                <span class="status-pill completed" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">Active</span>
                            @else
                                <span class="status-pill danger" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                <a href="{{ route('admin.suppliers.show', $supplier->id) }}" class="table-icon-btn" title="View Supplier Profile" style="color: #4d2c20; background: rgba(77, 44, 32, 0.08); border-radius: 6px; padding: 6px; display: inline-flex;">
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
                                    data-notes="{{ $supplier->notes }}"
                                    style="color: #2563eb; background: rgba(37, 99, 235, 0.08); border: none; border-radius: 6px; padding: 6px; cursor: pointer; display: inline-flex;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>
                                <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete supplier \'{{ $supplier->name }}\'?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-icon-btn" title="Delete Supplier" style="color: #ef4444; background: rgba(239, 68, 68, 0.08); border: none; border-radius: 6px; padding: 6px; cursor: pointer; display: inline-flex;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted, #71717a); padding: 3rem;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏭</div>
                            <div style="font-weight: 700; font-size: 1rem;">No suppliers found</div>
                            <div style="font-size: 0.825rem; margin-top: 0.25rem;">Try adjusting search terms or click "Add Supplier" to register a new vendor.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color, #e2e8f0);">
            {{ $suppliers->links() }}
        </div>
    @endif
</div>

<!-- ADD SUPPLIER MODAL -->
<div class="dash-modal-overlay" id="add_supplier_modal">
    <div class="dash-modal-content" style="max-width: 540px; border-radius: 1.25rem; padding: 1.5rem;">
        <div class="dash-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #4d2c20;">Add New Supplier / Vendor</h3>
            <button type="button" class="btn-close-modal" id="btn_close_supplier_modal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #71717a;">&times;</button>
        </div>
        <form action="{{ route('admin.suppliers.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Company / Vendor Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="name" class="ios-input-field" placeholder="e.g. Mekong Flour Mills Ltd" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Contact Person</label>
                    <input type="text" name="contact_person" class="ios-input-field" placeholder="e.g. Sovan Srun" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Status</label>
                    <select name="status" class="ios-select-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Phone Number</label>
                    <input type="text" name="phone" class="ios-input-field" placeholder="+855 12 345 678" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Email Address</label>
                    <input type="email" name="email" class="ios-input-field" placeholder="orders@vendor.com" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
            </div>
            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Physical Address / Facility</label>
                <input type="text" name="address" class="ios-input-field" placeholder="e.g. St. 2004, Phnom Penh, Cambodia" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
            </div>
            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Notes & Procurement Terms</label>
                <textarea name="notes" rows="2" class="ios-input-field" placeholder="e.g. Net 30 payment terms, weekly delivery on Mondays" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" id="btn_cancel_supplier" style="padding: 0.6rem 1.25rem; border-radius: 9999px; border: 1px solid #d4d4d8; background: #fff; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">Create Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT SUPPLIER MODAL -->
<div class="dash-modal-overlay" id="edit_supplier_modal">
    <div class="dash-modal-content" style="max-width: 540px; border-radius: 1.25rem; padding: 1.5rem;">
        <div class="dash-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #4d2c20;">Edit Supplier</h3>
            <button type="button" class="btn-close-modal" id="btn_close_edit_modal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #71717a;">&times;</button>
        </div>
        <form id="edit_supplier_form" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Company / Vendor Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="name" id="edit_supplier_name" class="ios-input-field" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Contact Person</label>
                    <input type="text" name="contact_person" id="edit_supplier_contact" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Status</label>
                    <select name="status" id="edit_supplier_status" class="ios-select-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Phone Number</label>
                    <input type="text" name="phone" id="edit_supplier_phone" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Email Address</label>
                    <input type="email" name="email" id="edit_supplier_email" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
            </div>
            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Physical Address / Facility</label>
                <input type="text" name="address" id="edit_supplier_address" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
            </div>
            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Notes & Procurement Terms</label>
                <textarea name="notes" id="edit_supplier_notes" rows="2" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" id="btn_cancel_edit_supplier" style="padding: 0.6rem 1.25rem; border-radius: 9999px; border: 1px solid #d4d4d8; background: #fff; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">Update Supplier</button>
            </div>
        </form>
    </div>
</div>

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
                addModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
        function closeAdd() {
            if (addModal) {
                addModal.classList.remove('active');
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
                editModal.classList.remove('active');
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

                editModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });
    });
</script>
@endpush
@endsection
