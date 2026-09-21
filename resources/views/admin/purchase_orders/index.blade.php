@extends('layouts.app')

@section('title', 'Purchase Orders - Bakery Management System')

@section('content')
<!-- Header Bar -->
<x-page-header title="Raw Material Purchase Orders" subtitle="Procure baking ingredients, manage vendor orders, and receive replenishment stock">
    <x-slot:actions>
        <x-button variant="secondary" :href="route('admin.suppliers')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/></svg>
            <span>Suppliers</span>
        </x-button>
        <x-button variant="primary" :href="route('admin.purchase-orders.create')" id="btn_create_po">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>New Purchase Order</span>
        </x-button>
    </x-slot:actions>
</x-page-header>

<!-- KPI Metrics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(77, 44, 32, 0.08); display: flex; align-items: center; justify-content: center; color: var(--accent-brown, #4d2c20); flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Purchase Orders</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary, #0f172a);">{{ $stats['total'] }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(148, 163, 184, 0.15); display: flex; align-items: center; justify-content: center; color: #475569; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Draft Orders</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #475569;">{{ $stats['draft'] }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #b45309; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Ordered (In Transit)</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #b45309;">{{ $stats['ordered'] }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #059669; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Received & Stocked In</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #059669;">{{ $stats['received'] }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #2563eb; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Procured Value</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #2563eb;">${{ number_format($stats['total_spend'], 2) }}</div>
        </div>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="orders-filter-bar" id="po_status_tabs" style="margin-bottom: 1.25rem;">
    <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
       class="filter-btn {{ (!request()->filled('status') || request('status') === 'all') ? 'active' : '' }}">
        All Orders ({{ $stats['total'] }})
    </a>
    <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status', 'page'), ['status' => 'draft'])) }}"
       class="filter-btn {{ request('status') === 'draft' ? 'active' : '' }}">
        Drafts ({{ $stats['draft'] }})
    </a>
    <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status', 'page'), ['status' => 'ordered'])) }}"
       class="filter-btn {{ request('status') === 'ordered' ? 'active' : '' }}">
        Ordered ({{ $stats['ordered'] }})
    </a>
    <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status', 'page'), ['status' => 'received'])) }}"
       class="filter-btn {{ request('status') === 'received' ? 'active' : '' }}">
        Received ({{ $stats['received'] }})
    </a>
    <a href="{{ route('admin.purchase-orders.index', array_merge(request()->except('status', 'page'), ['status' => 'cancelled'])) }}"
       class="filter-btn {{ request('status') === 'cancelled' ? 'active' : '' }}">
        Cancelled ({{ $stats['cancelled'] }})
    </a>
</div>

<!-- Live Search & Detailed Filters Form -->
<form method="GET" action="{{ route('admin.purchase-orders.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; align-items: center;">
    @if(request('status') && request('status') !== 'all')
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif

    <div style="flex: 1; min-width: 260px; position: relative;">
        <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" id="po_search_input" value="{{ request('search') }}"
               placeholder="Search PO #, supplier, or notes..."
               class="form-control-input" style="padding-left: 2.6rem;">
    </div>

    <div style="min-width: 180px;">
        <select name="supplier_id" class="form-control-select" onchange="this.form.submit()">
            <option value="all">All Suppliers</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>

    <div style="min-width: 150px;">
        <input type="date" name="date" value="{{ request('date') }}" class="form-control-input" onchange="this.form.submit()">
    </div>

    @if(request()->hasAny(['search', 'supplier_id', 'date']) && (request('search') || (request('supplier_id') && request('supplier_id') !== 'all') || request('date')))
        <x-button variant="secondary" size="sm" :href="route('admin.purchase-orders.index', request('status') ? ['status' => request('status')] : [])">
            Clear Filters
        </x-button>
    @endif
</form>

<!-- Purchase Orders List Table -->
<div class="card" style="border-radius: 1rem; overflow: hidden; padding: 0;">
    <div class="table-responsive-wrapper" style="margin-bottom: 0;">
        <table class="bakery-table" id="purchase_orders_table" style="width: 100%;">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier / Vendor</th>
                    <th>Order Date</th>
                    <th>Expected Delivery</th>
                    <th>Items Ordered</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                    <tr>
                        <td>
                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" style="font-family: monospace; font-weight: 800; color: var(--accent-brown, #d97736); text-decoration: none; font-size: 0.95rem;">
                                #{{ $po->po_number }}
                            </a>
                            <div style="font-size: 0.725rem; color: var(--text-muted, #94a3b8);">
                                By {{ $po->user->name ?? 'System' }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary, #1e293b);">
                                {{ $po->supplier->name ?? 'Unassigned Supplier' }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">
                                {{ $po->supplier->phone ?? ($po->supplier->email ?? '—') }}
                            </div>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-primary, #334155); font-weight: 600;">
                            {{ $po->order_date ? $po->order_date->format('M d, Y') : '—' }}
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-muted, #64748b);">
                            {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : '—' }}
                        </td>
                        <td>
                            <span class="type-pill" style="background: rgba(77, 44, 32, 0.08); color: var(--accent-brown, #4d2c20); font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px;">
                                {{ $po->items->count() }} items
                            </span>
                        </td>
                        <td style="font-weight: 800; font-size: 1rem; color: var(--text-primary, #0f172a);">
                            ${{ number_format($po->total_cost, 2) }}
                        </td>
                        <td>
                            <x-badge :status="$po->status" />
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;">
                                <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="table-icon-btn" title="View Purchase Order Details">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                @if($po->isDraft())
                                    <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="table-icon-btn" title="Edit Draft PO">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    </a>
                                @elseif($po->isOrdered())
                                    <form action="{{ route('admin.purchase-orders.receive', $po->id) }}" method="POST" onsubmit="return confirm('Confirm receipt of PO #{{ $po->po_number }}? This will instantly increase raw ingredient inventory stock.');" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="table-icon-btn" title="Receive Stock" style="color: #059669;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
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
                            <div style="font-weight: 700; font-size: 1.05rem;">No purchase orders found</div>
                            <div style="font-size: 0.825rem; margin-top: 0.25rem;">Click "New Purchase Order" to procure baking ingredients.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchaseOrders->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color, #e2e8f0);">
            {{ $purchaseOrders->links() }}
        </div>
    @endif
</div>
@endsection
