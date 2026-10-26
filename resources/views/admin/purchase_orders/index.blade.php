@extends('layouts.app')

@section('title', 'Purchase Orders - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/orders.css') }}">
@endpush

@section('header_toolbar')
    <div class="period-pill-group">
        <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
           class="period-pill {{ (!request()->filled('status') || request('status') === 'all') ? 'active' : '' }}">
            <span>All Orders ({{ $stats['total'] }})</span>
        </a>
        <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status'), ['status' => 'draft'])) }}"
           class="period-pill {{ request('status') === 'draft' ? 'active' : '' }}">
            <span>Drafts ({{ $stats['draft'] }})</span>
        </a>
        <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status'), ['status' => 'ordered'])) }}"
           class="period-pill {{ request('status') === 'ordered' ? 'active' : '' }}">
            <span>Ordered ({{ $stats['ordered'] }})</span>
        </a>
        <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status'), ['status' => 'received'])) }}"
           class="period-pill {{ request('status') === 'received' ? 'active' : '' }}">
            <span>Received ({{ $stats['received'] }})</span>
        </a>
        <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status'), ['status' => 'cancelled'])) }}"
           class="period-pill {{ request('status') === 'cancelled' ? 'active' : '' }}">
            <span>Cancelled ({{ $stats['cancelled'] }})</span>
        </a>
    </div>
@endsection

@section('content')
<div class="orders-container">

    <h1 class="sr-only">Raw Material Purchase Orders</h1>

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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Purchase Orders</span>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Procurement
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Pending Orders</span>
                <div class="stat-value">{{ $stats['draft'] + $stats['ordered'] }}</div>
                <span class="stat-trend {{ ($stats['draft'] + $stats['ordered']) > 0 ? 'warning' : 'up' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    {{ $stats['ordered'] }} ordered
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Received & Stocked In</span>
                <div class="stat-value">{{ $stats['received'] }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Stock Replenished
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Procured Value</span>
                <div class="stat-value">${{ number_format($stats['total_spend'], 2) }}</div>
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    Spend Track
                </span>
            </div>
        </div>
    </div>

    <!-- 2. Action & Filter Toolbar Row (Exact Orders & Ingredients Style) -->
    <div class="ingredients-toolbar-row" id="po_toolbar" style="margin-bottom: 0.75rem; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <!-- Left: Filters Form -->
        <form method="GET" action="{{ route('admin.purchase-orders.index') }}" id="po_filter_form" style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; margin: 0;">
            @if(request('status') && request('status') !== 'all')
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            @if(request('search'))
                <input type="hidden" name="search" id="po_search_input" value="{{ request('search') }}">
            @endif

            <!-- Supplier Pill Filter -->
            <div class="supplier-filter-pill-card">
                <select name="supplier_id" id="po_supplier_select" onchange="this.form.submit()">
                    <option value="all">All Suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>

                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" style="border: none; background: transparent; color: inherit; font-size: 0.825rem; padding: 0 0.5rem; height: 32px; border-left: 1px solid var(--border-color, #333);">

                <button type="submit" class="btn" style="border-radius: 50px; padding: 0.35rem 1rem; font-weight: 700; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: #ea580c; border: 1px solid #ea580c; color: #ffffff; cursor: pointer; font-size: 0.825rem;">Filter</button>

                @if(request()->hasAny(['search', 'supplier_id', 'date']) && (request('search') || (request('supplier_id') && request('supplier_id') !== 'all') || request('date')))
                    <a href="{{ route('admin.purchase-orders.index', request('status') ? ['status' => request('status')] : []) }}" class="btn btn-secondary" style="border-radius: 50px; padding: 0.35rem 0.85rem; font-weight: 600; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">Clear</a>
                @endif
            </div>
        </form>

        <!-- Right: Action Buttons -->
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('admin.suppliers') }}" class="btn btn-secondary" style="border-radius: 50px; height: 36px; padding: 0 1rem; display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.825rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/></svg>
                <span>Suppliers</span>
            </a>
            <a href="{{ route('admin.purchase-orders.create') }}" class="btn" id="btn_create_po" style="border-radius: 50px; height: 36px; padding: 0 1.15rem; display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; background: #ea580c; border: 1px solid #ea580c; color: #ffffff; cursor: pointer; font-size: 0.875rem; box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25); text-decoration: none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>New Purchase Order</span>
            </a>
        </div>
    </div>

    <!-- 3. Purchase Orders List Table Card (Full Viewport Height with internal scrolling) -->
    <div class="orders-card" id="purchase_orders_table_card">
        <div class="orders-table-wrapper" id="purchase_orders_table_wrapper">
            <table class="orders-table" id="purchase_orders_table">
                <thead>
                    <tr>
                        <th style="width: 14%;">PO Number</th>
                        <th style="width: 20%;">Supplier / Vendor</th>
                        <th style="width: 13%;">Order Date</th>
                        <th style="width: 13%;">Expected Delivery</th>
                        <th style="width: 12%;">Items Ordered</th>
                        <th style="width: 11%;">Total Cost</th>
                        <th style="width: 9%;">Status</th>
                        <th style="width: 8%; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <!-- PO Number -->
                            <td>
                                <a href="{{ route('admin.purchase-orders.show', $po->id) }}" style="font-family: monospace; font-weight: 800; color: #ea580c; text-decoration: none; font-size: 0.95rem;">
                                    #{{ $po->po_number }}
                                </a>
                                <div style="font-size: 0.725rem; color: var(--text-muted, #71717a); margin-top: 0.2rem;">
                                    By {{ $po->user->name ?? 'System' }}
                                </div>
                            </td>

                            <!-- Supplier / Vendor -->
                            <td>
                                <div style="font-weight: 700; color: inherit; font-size: 0.9rem;">
                                    {{ $po->supplier->name ?? 'Unassigned Supplier' }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted, #71717a);">
                                    {{ $po->supplier->phone ?? ($po->supplier->email ?? '—') }}
                                </div>
                            </td>

                            <!-- Order Date -->
                            <td style="font-size: 0.85rem; font-weight: 600; color: inherit;">
                                {{ $po->order_date ? $po->order_date->format('M d, Y') : '—' }}
                            </td>

                            <!-- Expected Delivery -->
                            <td style="font-size: 0.85rem; color: var(--text-muted, #a1a1aa);">
                                {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : '—' }}
                            </td>

                            <!-- Items Ordered -->
                            <td>
                                <span class="type-pill" style="background: rgba(234, 88, 12, 0.08); color: #ea580c; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px;">
                                    {{ $po->items->count() }} items
                                </span>
                            </td>

                            <!-- Total Cost -->
                            <td style="font-weight: 800; font-size: 1rem; color: inherit;">
                                ${{ number_format($po->total_cost, 2) }}
                            </td>

                            <!-- Status -->
                            <td>
                                <x-badge :status="$po->status" />
                            </td>

                            <!-- Action -->
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;">
                                    <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="table-icon-btn" title="View Purchase Order Details">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    @if($po->isDraft())
                                        <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="table-icon-btn" title="Edit Draft PO">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                        </a>
                                    @elseif($po->isOrdered())
                                        <form action="{{ route('admin.purchase-orders.receive', $po->id) }}" method="POST" onsubmit="return confirm('Confirm receipt of PO #{{ $po->po_number }}? This will instantly increase raw ingredient inventory stock.');" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="table-icon-btn" title="Receive Stock" style="color: #16a34a; background: none; border: none; cursor: pointer; padding: 4px; display: inline-flex; align-items: center;">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted, #71717a); padding: 3rem;">
                                <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">📋</div>
                                <div style="font-weight: 700; font-size: 1.05rem; color: inherit;">No purchase orders found</div>
                                <div style="font-size: 0.825rem; margin-top: 0.25rem;">Click "New Purchase Order" to procure baking ingredients.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
