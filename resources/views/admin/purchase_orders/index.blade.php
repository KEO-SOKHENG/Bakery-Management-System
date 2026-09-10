@extends('layouts.app')

@section('title', 'Purchase Orders - Bakery Management System')

@section('content')
<!-- Header Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0; color: var(--text-primary, #0f172a);">
            Raw Material Purchase Orders
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted, #64748b); margin-top: 0.25rem;">
            Procure baking ingredients, manage vendor orders, and receive replenishment stock
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <a href="{{ route('admin.suppliers') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 9999px; border: 1px solid var(--border-color, #e2e8f0); text-decoration: none; color: var(--text-primary, #1e293b); background: var(--bg-card, #ffffff); font-weight: 700;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/></svg>
            <span>Suppliers</span>
        </a>
        <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary" id="btn_create_po" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #4d2c20; color: #fff; border-radius: 9999px; text-decoration: none; font-weight: 700; box-shadow: 0 4px 12px rgba(77, 44, 32, 0.25);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>New Purchase Order</span>
        </a>
    </div>
</div>

<!-- KPI Metrics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(77, 44, 32, 0.08); display: flex; align-items: center; justify-content: center; color: #4d2c20; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Purchase Orders</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary, #0f172a);">{{ $stats['total'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(148, 163, 184, 0.15); display: flex; align-items: center; justify-content: center; color: #475569; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Draft Orders</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #475569;">{{ $stats['draft'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #b45309; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Ordered (In Transit)</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #b45309;">{{ $stats['ordered'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #059669; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Received & Stocked In</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #059669;">{{ $stats['received'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #2563eb; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Procured Value</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #2563eb;">${{ number_format($stats['total_spend'], 2) }}</div>
        </div>
    </div>
</div>

<!-- Filter Tabs & Controls -->
<div class="bakery-card" style="padding: 1rem 1.25rem; border-radius: 1rem; margin-bottom: 1.25rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
    <!-- Status Tabs -->
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color, #e2e8f0); padding-bottom: 0.75rem;">
        @php
            $currentStatus = request('status', 'all');
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="btn" style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.825rem; font-weight: 700; text-decoration: none; {{ $currentStatus === 'all' ? 'background: #4d2c20; color: #fff;' : 'background: #f1f5f9; color: #475569;' }}">
            All Orders ({{ $stats['total'] }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'draft']) }}" class="btn" style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.825rem; font-weight: 700; text-decoration: none; {{ $currentStatus === 'draft' ? 'background: #4d2c20; color: #fff;' : 'background: #f1f5f9; color: #475569;' }}">
            Drafts ({{ $stats['draft'] }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'ordered']) }}" class="btn" style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.825rem; font-weight: 700; text-decoration: none; {{ $currentStatus === 'ordered' ? 'background: #4d2c20; color: #fff;' : 'background: #f1f5f9; color: #475569;' }}">
            Ordered ({{ $stats['ordered'] }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'received']) }}" class="btn" style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.825rem; font-weight: 700; text-decoration: none; {{ $currentStatus === 'received' ? 'background: #4d2c20; color: #fff;' : 'background: #f1f5f9; color: #475569;' }}">
            Received ({{ $stats['received'] }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled']) }}" class="btn" style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.825rem; font-weight: 700; text-decoration: none; {{ $currentStatus === 'cancelled' ? 'background: #4d2c20; color: #fff;' : 'background: #f1f5f9; color: #475569;' }}">
            Cancelled ({{ $stats['cancelled'] }})
        </a>
    </div>

    <!-- Filter Inputs -->
    <form method="GET" action="{{ route('admin.purchase-orders.index') }}" style="display: flex; gap: 0.85rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <input type="hidden" name="status" value="{{ request('status', 'all') }}">

        <div style="display: flex; gap: 0.75rem; flex: 1; min-width: 280px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 220px;">
                <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO #, supplier, or notes..." class="ios-input-field" style="padding-left: 2.35rem; width: 100%; border-radius: 0.6rem; border: 1px solid var(--border-color, #d4d4d8); height: 40px;">
            </div>

            <select name="supplier_id" class="ios-select-field" style="min-width: 180px; border-radius: 0.6rem; border: 1px solid var(--border-color, #d4d4d8); height: 40px;" onchange="this.form.submit()">
                <option value="all">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ request('date') }}" class="ios-input-field" style="border-radius: 0.6rem; border: 1px solid var(--border-color, #d4d4d8); height: 40px;" onchange="this.form.submit()">
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.1rem; border-radius: 0.6rem; background: #4d2c20; color: #fff; font-weight: 700; border: none; cursor: pointer;">Filter</button>
            @if(request()->hasAny(['search', 'supplier_id', 'date']) || request('status') !== 'all')
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary" style="padding: 0.5rem 0.85rem; border-radius: 0.6rem; border: 1px solid #d4d4d8; text-decoration: none; color: #64748b;">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Purchase Orders List Table -->
<div class="bakery-card" style="border-radius: 1rem; overflow: hidden; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
    <div class="table-responsive-wrapper">
        <table class="recent-orders-table" id="purchase_orders_table" style="width: 100%;">
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
                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" style="font-family: monospace; font-weight: 800; color: #4d2c20; text-decoration: none; font-size: 0.95rem;">
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
                            <span class="type-pill" style="background: rgba(77, 44, 32, 0.08); color: #4d2c20; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px;">
                                {{ $po->items->count() }} items
                            </span>
                        </td>
                        <td style="font-weight: 800; font-size: 1rem; color: var(--text-primary, #0f172a);">
                            ${{ number_format($po->total_cost, 2) }}
                        </td>
                        <td>
                            @if($po->status === 'draft')
                                <span class="status-pill pending" style="background: rgba(148, 163, 184, 0.15); color: #475569; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">Draft</span>
                            @elseif($po->status === 'ordered')
                                <span class="status-pill inprogress" style="background: rgba(245, 158, 11, 0.15); color: #b45309; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">Ordered</span>
                            @elseif($po->status === 'received')
                                <span class="status-pill completed" style="background: rgba(16, 185, 129, 0.15); color: #047857; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">Received</span>
                            @else
                                <span class="status-pill danger" style="background: rgba(239, 68, 68, 0.15); color: #b91c1c; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">Cancelled</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-decoration: none; border: 1px solid #e2e8f0; color: #4d2c20;">
                                    View
                                </a>
                                @if($po->isDraft())
                                    <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="btn btn-secondary" style="padding: 0.35rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-decoration: none; border: 1px solid #e2e8f0; color: #2563eb;" title="Edit Draft">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    </a>
                                @elseif($po->isOrdered())
                                    <form action="{{ route('admin.purchase-orders.receive', $po->id) }}" method="POST" onsubmit="return confirm('Confirm receipt of PO #{{ $po->po_number }}? This will instantly increase raw ingredient inventory stock.');" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary" style="padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; background: #059669; color: #fff; border: none; cursor: pointer;">
                                            Receive
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
