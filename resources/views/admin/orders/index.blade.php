@extends('layouts.app')

@section('title', 'Orders Management - Bakery System')
@section('header_title', 'Customer & Wholesale Orders')
@section('header_subtitle', 'Monitor live order fulfillment, custom cakes, and POS sales')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/orders.css') }}">
@endpush

@section('content')
<div class="orders-container">

    <!-- Flash Alerts -->
    @if(session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <!-- KPI Summary Metrics Grid -->
    <div class="orders-metrics-grid">
        <div class="stat-card">
            <div class="stat-icon-box orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Orders</span>
                <div class="stat-value" id="kpi_total_orders">{{ number_format($totalOrdersCount) }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Pending / Baking</span>
                <div class="stat-value" id="kpi_pending_orders">{{ number_format($pendingCount + $preparingCount) }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box teal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Ready for Pickup</span>
                <div class="stat-value" id="kpi_ready_orders">{{ number_format($readyCount) }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-box green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 6v12"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Completed Sales</span>
                <div class="stat-value" id="kpi_total_revenue">{{ $currency }}{{ number_format($totalRevenue, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Header Actions & Search / Filter Controls -->
    <div class="orders-header">
        <!-- Status Filter Tabs -->
        <div class="orders-filter-bar" id="order_status_tabs">
            <a href="{{ route('admin.orders', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
               class="filter-btn {{ (!request()->filled('status') || request('status') === 'all') ? 'active' : '' }}">
                All Orders ({{ $totalOrdersCount }})
            </a>
            <a href="{{ route('admin.orders', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
               class="filter-btn {{ request('status') === 'pending' ? 'active' : '' }}">
                Pending ({{ $pendingCount }})
            </a>
            <a href="{{ route('admin.orders', array_merge(request()->except('status', 'page'), ['status' => 'preparing'])) }}"
               class="filter-btn {{ request('status') === 'preparing' ? 'active' : '' }}">
                Preparing ({{ $preparingCount }})
            </a>
            <a href="{{ route('admin.orders', array_merge(request()->except('status', 'page'), ['status' => 'ready_for_pickup'])) }}"
               class="filter-btn {{ request('status') === 'ready_for_pickup' ? 'active' : '' }}">
                Ready for Pickup ({{ $readyCount }})
            </a>
            <a href="{{ route('admin.orders', array_merge(request()->except('status', 'page'), ['status' => 'completed'])) }}"
               class="filter-btn {{ request('status') === 'completed' ? 'active' : '' }}">
                Completed ({{ $completedCount }})
            </a>
            <a href="{{ route('admin.orders', array_merge(request()->except('status', 'page'), ['status' => 'cancelled'])) }}"
               class="filter-btn {{ request('status') === 'cancelled' ? 'active' : '' }}">
                Cancelled ({{ $cancelledCount }})
            </a>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            @if(Auth::user() && Auth::user()->isAdmin())
                <x-button variant="outline-danger" id="btn_open_purge_modal" onclick="openPurgeModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    <span>Clear All Orders</span>
                </x-button>
            @endif

            <!-- Create Order Button -->
            <x-button variant="primary" :href="route('admin.orders.create')" id="btn_create_order">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                <span>Create Order</span>
            </x-button>
        </div>
    </div>

    <!-- Live Search & Detailed Filters Form -->
    <form action="{{ route('admin.orders') }}" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; align-items: center;">
        @if(request('status') && request('status') !== 'all')
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <div style="flex: 1; min-width: 260px; position: relative;">
            <input type="text" name="search" id="order_search_input" value="{{ request('search') }}"
                   placeholder="Search by order #, customer name, or phone..."
                   class="form-control-input" style="padding-left: 2.6rem;">
            <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>

        <div style="min-width: 150px;">
            <select name="type" class="form-control-select" onchange="this.form.submit()">
                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Order Types</option>
                <option value="pos" {{ request('type') == 'pos' ? 'selected' : '' }}>Store POS</option>
                <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom Cake ({{ $customOrdersCount }})</option>
            </select>
        </div>

        <div style="min-width: 140px;">
            <select name="payment_method" class="form-control-select" onchange="this.form.submit()">
                <option value="all" {{ request('payment_method') == 'all' ? 'selected' : '' }}>All Payments</option>
                <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="qr_code" {{ request('payment_method') == 'qr_code' ? 'selected' : '' }}>KHQR</option>
                <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
            </select>
        </div>

        <div style="min-width: 140px;">
            <select name="date" class="form-control-select" onchange="this.form.submit()">
                <option value="" {{ !request('date') ? 'selected' : '' }}>All Dates</option>
                <option value="today" {{ request('date') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ request('date') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="this_week" {{ request('date') == 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="this_month" {{ request('date') == 'this_month' ? 'selected' : '' }}>This Month</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary" style="border-radius: 14px; padding: 0.75rem 1.25rem; font-weight: 700; cursor: pointer;">
            Filter
        </button>

        @if(request()->anyFilled(['search', 'status', 'type', 'payment_method', 'date']))
            <a href="{{ route('admin.orders') }}" class="btn btn-outline" style="border-radius: 14px; padding: 0.75rem 1rem; color: #ef4444; border: 1px solid #ef4444; text-decoration: none; font-weight: 700;">
                Reset
            </a>
        @endif
    </form>

    <!-- Orders Table Card -->
    <x-card class="orders-card">
        <table class="orders-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Order #</th>
                    <th style="width: 18%;">Customer</th>
                    <th style="width: 25%;">Items</th>
                    <th style="width: 15%;">Total & Payment</th>
                    <th style="width: 13%;">Status</th>
                    <th style="width: 11%;" class="col-date">Date</th>
                    <th style="width: 4%; min-width: 48px; text-align: center;">View</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $normStatus = $order->normalized_status;
                        $statusBadge = match($normStatus) {
                            'completed' => '<span class="status-badge status-completed"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Completed</span>',
                            'preparing' => '<span class="status-badge status-preparing"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="m4.93 19.07 2.83-2.83"/></svg> Preparing</span>',
                            'ready_for_pickup' => '<span class="status-badge status-ready"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg> Ready</span>',
                            'out_for_delivery' => '<span class="status-badge status-delivery"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Delivery</span>',
                            'cancelled' => '<span class="status-badge status-cancelled"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Cancelled</span>',
                            default => '<span class="status-badge status-pending"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Pending</span>',
                        };

                        $paymentMethodLabel = match($order->payment_method) {
                            'qr_code' => 'KHQR',
                            'card' => 'Card',
                            default => 'Cash',
                        };
                    @endphp
                    <tr id="order_row_{{ $order->id }}" data-status="{{ $normStatus }}" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" style="font-weight: 800; color: var(--accent-brown, #d97736); text-decoration: none;">
                                {{ $order->order_number }}
                            </a>
                            @if($order->is_custom)
                                <div style="margin-top: 2px;">
                                    <span class="type-pill type-custom" style="font-size: 0.68rem; padding: 2px 7px;">🎂 Custom Cake</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-title); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $order->customer_name }}">
                                {{ $order->customer_name }}
                            </div>
                            @if($order->customer && $order->customer->phone)
                                <div style="font-size: 0.75rem; color: #64748b;">{{ $order->customer->phone }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $order->items_summary }}">
                                {{ $order->items_summary }}
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 800; font-size: 0.925rem; color: var(--text-title);">
                                {{ $currency }}{{ number_format($order->total, 2) }}
                            </div>
                            <div style="margin-top: 2px;">
                                <span class="payment-badge {{ $order->payment_status === 'paid' ? 'paid' : 'unpaid' }}" style="font-size: 0.68rem; padding: 2px 6px;">
                                    {{ $paymentMethodLabel }} • {{ ucfirst($order->payment_status ?? 'unpaid') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {!! $statusBadge !!}
                        </td>
                        <td class="col-date" style="font-size: 0.8rem; color: #64748b; white-space: nowrap;">
                            <div>{{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}</div>
                            <div style="font-size: 0.725rem;">{{ $order->created_at ? $order->created_at->format('h:i A') : '' }}</div>
                        </td>
                        <td style="text-align: center;" onclick="event.stopPropagation();">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn-icon-action" title="View Order #{{ $order->order_number }} Details">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted, #9ca3af); padding: 3rem;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                <span style="font-weight: 700; font-size: 0.95rem;">No orders match your selected filters.</span>
                                <span style="font-size: 0.825rem; color: #94a3b8;">Try clearing filters or click "Create Order" to record an order.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="orders-pagination-wrapper">
                {{ $orders->links() }}
            </div>
        @endif
    </x-card>

    <!-- Easy 1-Click Delete / Clear Orders Modal (Admin Only) -->
    @if(Auth::user() && Auth::user()->isAdmin())
    <div id="purgeModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div style="background: #ffffff; border-radius: 20px; max-width: 480px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;">
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 1.15rem 1.35rem; border-bottom: 1px solid #fecaca; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: #fee2e2; border: 1.5px solid #f87171; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #991b1b;">Delete Orders</h3>
                        <p style="margin: 0; font-size: 0.8rem; color: #b91c1c;">Select what you want to delete</p>
                    </div>
                </div>
                <button type="button" onclick="closePurgeModal()" style="background: none; border: none; font-size: 1.6rem; color: #991b1b; cursor: pointer; line-height: 1; padding: 0.25rem;">&times;</button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('admin.orders.purgeOld') }}" method="POST" id="purgeForm" style="padding: 1.25rem 1.35rem;">
                @csrf
                <!-- Hidden inputs for seamless backend compatibility -->
                <input type="hidden" name="scope" id="form_purge_scope" value="all">
                <input type="hidden" name="days" id="form_purge_days" value="30">
                <input type="hidden" name="status" id="form_purge_status" value="all">
                <input type="hidden" name="restore_stock" value="1">
                <input type="hidden" name="backup" value="1">
                <input type="hidden" name="confirm_phrase" id="confirm_phrase_input" value="CONFIRM">

                <!-- Fast 1-Click Scope Buttons -->
                <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.6rem;">
                    Select Delete Option
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; margin-bottom: 1rem;">
                    <button type="button" class="scope-quick-btn active" id="btn_scope_all" onclick="setScopeMode('all', 0, 'all', this)"
                            style="padding: 0.75rem 0.65rem; border-radius: 12px; border: 2px solid #ef4444; background: #fef2f2; color: #dc2626; font-weight: 800; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: all 0.15s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>
                        <span>All Orders</span>
                    </button>

                    <button type="button" class="scope-quick-btn" id="btn_scope_30" onclick="setScopeMode('days', 30, 'all', this)"
                            style="padding: 0.75rem 0.65rem; border-radius: 12px; border: 2px solid #e2e8f0; background: #ffffff; color: #475569; font-weight: 700; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: all 0.15s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Older than 30d</span>
                    </button>

                    <button type="button" class="scope-quick-btn" id="btn_scope_90" onclick="setScopeMode('days', 90, 'all', this)"
                            style="padding: 0.75rem 0.65rem; border-radius: 12px; border: 2px solid #e2e8f0; background: #ffffff; color: #475569; font-weight: 700; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: all 0.15s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Older than 90d</span>
                    </button>

                    <button type="button" class="scope-quick-btn" id="btn_scope_cancelled" onclick="setScopeMode('all', 0, 'cancelled', this)"
                            style="padding: 0.75rem 0.65rem; border-radius: 12px; border: 2px solid #e2e8f0; background: #ffffff; color: #475569; font-weight: 700; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: all 0.15s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        <span>Cancelled Only</span>
                    </button>
                </div>

                <!-- Live Impact Preview Box -->
                <div id="purge_preview_box" style="background: #fafaf9; border: 1.5px solid #e7e5e4; border-radius: 14px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: #44403c;">Matching orders to delete:</span>
                        <strong id="preview_orders_count" style="font-size: 1.15rem; color: #dc2626; font-weight: 800;">Loading...</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.775rem; color: #78716c;">
                        <span>Includes linked items & sales records</span>
                        <span id="preview_sub_counts">...</span>
                    </div>
                </div>

                <!-- Action Buttons: Cancel vs 1-Click Delete -->
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center;">
                    <button type="button" onclick="closePurgeModal()" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; border-radius: 50px; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-weight: 700; cursor: pointer; transition: all 0.15s ease;">
                        Cancel
                    </button>
                    <button type="submit" id="btn_submit_purge" class="btn btn-danger" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; border-radius: 50px; background: #dc2626; color: #ffffff; border: none; font-weight: 800; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        <span>Delete Orders Now</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setScopeMode(scope, days, status, btnEl) {
            document.querySelectorAll('.scope-quick-btn').forEach(btn => {
                btn.style.border = '2px solid #e2e8f0';
                btn.style.background = '#ffffff';
                btn.style.color = '#475569';
                btn.style.fontWeight = '700';
            });
            btnEl.style.border = '2px solid #ef4444';
            btnEl.style.background = '#fef2f2';
            btnEl.style.color = '#dc2626';
            btnEl.style.fontWeight = '800';

            const scopeInput = document.getElementById('form_purge_scope');
            const daysInput = document.getElementById('form_purge_days');
            const statusInput = document.getElementById('form_purge_status');

            if (scopeInput) scopeInput.value = scope;
            if (daysInput) daysInput.value = days;
            if (statusInput) statusInput.value = status;

            fetchPurgePreview();
        }

        function openPurgeModal() {
            const modal = document.getElementById('purgeModal');
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.style.display = 'flex';
                fetchPurgePreview();
            }
        }

        function closePurgeModal() {
            const modal = document.getElementById('purgeModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        let previewDebounceTimer = null;
        function fetchPurgePreview() {
            clearTimeout(previewDebounceTimer);
            const countEl = document.getElementById('preview_orders_count');
            const subCountEl = document.getElementById('preview_sub_counts');
            if (countEl) countEl.innerText = 'Calculating...';

            previewDebounceTimer = setTimeout(() => {
                const scope = document.getElementById('form_purge_scope')?.value || 'all';
                const days = document.getElementById('form_purge_days')?.value || '30';
                const status = document.getElementById('form_purge_status')?.value || 'all';

                const url = `{{ route('admin.orders.purgePreview') }}?scope=${encodeURIComponent(scope)}&days=${encodeURIComponent(days)}&status=${encodeURIComponent(status)}`;

                fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (countEl) countEl.innerText = `${data.orders_count} orders`;
                        if (subCountEl) subCountEl.innerText = `${data.items_count} items, ${data.sales_count} sales, ${data.payments_count} payments`;
                        const submitBtn = document.getElementById('btn_submit_purge');
                        if (submitBtn) {
                            if (data.orders_count === 0) {
                                submitBtn.disabled = true;
                                submitBtn.style.opacity = '0.6';
                                submitBtn.style.cursor = 'not-allowed';
                            } else {
                                submitBtn.disabled = false;
                                submitBtn.style.opacity = '1';
                                submitBtn.style.cursor = 'pointer';
                            }
                        }
                    }
                })
                .catch(err => {
                    console.error('Failed to preview purge count', err);
                    if (countEl) countEl.innerText = '0 orders';
                });
            }, 150);
        }
    </script>
    @endif
</div>
@endsection
