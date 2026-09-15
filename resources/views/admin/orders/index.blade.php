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
        <div class="alert alert-success" style="display: flex; align-items: center; gap: 0.75rem; background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 0.85rem 1.25rem; border-radius: 14px; margin-bottom: 1.5rem; font-weight: 600;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="display: flex; align-items: center; gap: 0.75rem; background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 0.85rem 1.25rem; border-radius: 14px; margin-bottom: 1.5rem; font-weight: 600;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- KPI Summary Metrics Grid -->
    <div class="orders-metrics-grid">
        <div class="order-metric-card">
            <div class="order-metric-icon" style="background: rgba(86, 48, 32, 0.1); color: #563020;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div class="order-metric-info">
                <span class="order-metric-label">Total Orders</span>
                <span class="order-metric-value" id="kpi_total_orders">{{ number_format($totalOrdersCount) }}</span>
            </div>
        </div>

        <div class="order-metric-card">
            <div class="order-metric-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="order-metric-info">
                <span class="order-metric-label">Pending / Baking</span>
                <span class="order-metric-value" id="kpi_pending_orders">{{ number_format($pendingCount + $preparingCount) }}</span>
            </div>
        </div>

        <div class="order-metric-card">
            <div class="order-metric-icon" style="background: rgba(13, 148, 136, 0.12); color: #0d9488;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <div class="order-metric-info">
                <span class="order-metric-label">Ready for Pickup</span>
                <span class="order-metric-value" id="kpi_ready_orders">{{ number_format($readyCount) }}</span>
            </div>
        </div>

        <div class="order-metric-card">
            <div class="order-metric-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="order-metric-info">
                <span class="order-metric-label">Completed Sales</span>
                <span class="order-metric-value" id="kpi_total_revenue">{{ $currency }}{{ number_format($totalRevenue, 2) }}</span>
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
            <a href="{{ route('admin.orders', array_merge(request()->except('status', 'page'), ['status' => 'out_for_delivery'])) }}"
               class="filter-btn {{ request('status') === 'out_for_delivery' ? 'active' : '' }}">
                Out for Delivery ({{ $outForDeliveryCount }})
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
                <button type="button" id="btn_open_purge_modal" onclick="openPurgeModal()" class="btn btn-outline-danger" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 50px; padding: 0.65rem 1.25rem; font-weight: 700; border: 1.5px solid #ef4444; color: #dc2626; background: #ffffff; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.1);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    <span>Clear Old Orders</span>
                </button>
            @endif

            <!-- Create Order Button -->
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary" id="btn_create_order" style="display: inline-flex; align-items: center; gap: 0.55rem; background: linear-gradient(135deg, #563020 0%, #3b2118 100%); border-radius: 50px; padding: 0.65rem 1.45rem; color: #ffffff; border: none; font-weight: 800; box-shadow: 0 4px 16px rgba(86, 48, 32, 0.25); text-decoration: none; cursor: pointer; transition: all 0.25s ease;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                <span>Create New Order</span>
            </a>
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
    <div class="orders-card">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Items Summary</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th>Date / Time</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $normStatus = $order->normalized_status;
                        $statusBadge = match($normStatus) {
                            'completed' => '<span class="status-badge status-completed"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Completed</span>',
                            'preparing' => '<span class="status-badge status-preparing"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="m4.93 19.07 2.83-2.83"/></svg> Preparing</span>',
                            'ready_for_pickup' => '<span class="status-badge status-ready"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg> Ready for Pickup</span>',
                            'out_for_delivery' => '<span class="status-badge status-delivery"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Out for Delivery</span>',
                            'cancelled' => '<span class="status-badge status-cancelled"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Cancelled</span>',
                            default => '<span class="status-badge status-pending"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Pending</span>',
                        };

                        $paymentMethodLabel = match($order->payment_method) {
                            'qr_code' => 'KHQR',
                            'card' => 'Card',
                            default => 'Cash',
                        };
                    @endphp
                    <tr id="order_row_{{ $order->id }}" data-status="{{ $normStatus }}">
                        <td style="font-weight: 800; color: #563020;">
                            <a href="{{ route('admin.orders.show', $order) }}" style="color: inherit; text-decoration: none;">
                                {{ $order->order_number }}
                            </a>
                            @if($order->is_custom)
                                <div style="margin-top: 2px;">
                                    <span class="type-pill type-custom" title="Custom Cake Order">🎂 Custom Cake</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #29150d;">{{ $order->customer_name }}</div>
                            @if($order->customer && $order->customer->phone)
                                <div style="font-size: 0.775rem; color: #64748b;">{{ $order->customer->phone }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="type-pill {{ $order->is_custom ? 'type-custom' : 'type-pos' }}">
                                {{ $order->order_type }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 600; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $order->items_summary }}">
                                {{ $order->items_summary }}
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                            </div>
                        </td>
                        <td style="font-weight: 800; font-size: 0.95rem; color: #29150d;">
                            {{ $currency }}{{ number_format($order->total, 2) }}
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-weight: 700; font-size: 0.8rem;">{{ $paymentMethodLabel }}</span>
                                <span class="payment-badge {{ $order->payment_status === 'paid' ? 'paid' : 'unpaid' }}">
                                    {{ ucfirst($order->payment_status ?? 'unpaid') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {!! $statusBadge !!}
                        </td>
                        <td style="font-size: 0.8rem; color: #64748b; white-space: nowrap;">
                            <div>{{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}</div>
                            <div style="font-size: 0.75rem;">{{ $order->created_at ? $order->created_at->format('h:i A') : '' }}</div>
                        </td>
                        <td style="text-align: right;">
                            <div class="order-action-btns" style="justify-content: flex-end;">
                                <!-- View Details -->
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn-icon-action" title="View Order Details">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>

                                <!-- Edit (Pending Only) -->
                                @if($order->canBeEdited())
                                    <a href="{{ route('admin.orders.edit', $order) }}" class="btn-icon-action" title="Edit Order">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    </a>
                                @endif

                                <!-- Status Quick Action Transitions -->
                                @if($normStatus === 'pending')
                                    <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="preparing">
                                        <button type="submit" class="btn-icon-action" title="Start Preparing" style="color: #2563eb;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        </button>
                                    </form>
                                @elseif($normStatus === 'preparing')
                                    <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="ready_for_pickup">
                                        <button type="submit" class="btn-icon-action" title="Mark Ready for Pickup" style="color: #0d9488;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
                                        </button>
                                    </form>
                                @elseif($normStatus === 'ready_for_pickup')
                                    <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn-icon-action" title="Complete Order" style="color: #16a34a;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        </button>
                                    </form>
                                @elseif($normStatus === 'out_for_delivery')
                                    <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn-icon-action" title="Mark Delivered & Completed" style="color: #16a34a;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        </button>
                                    </form>
                                @endif

                                <!-- Cancel Action (Pending & Preparing only) -->
                                @if($order->canBeCancelled())
                                    <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" style="display: inline;" onsubmit="return confirm('Cancel Order #{{ $order->order_number }} and restore items to stock?');">
                                        @csrf
                                        <button type="submit" class="btn-icon-action" title="Cancel Order" style="color: #ef4444;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted, #9ca3af); padding: 3rem;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                <span style="font-weight: 700; font-size: 1rem;">No orders match your selected filters.</span>
                                <span style="font-size: 0.85rem; color: #94a3b8;">Try clearing filters or click "Create New Order" to record an order.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Purge Old Orders Modal (Admin Only) -->
    @if(Auth::user() && Auth::user()->isAdmin())
    <div id="purgeModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div style="background: #ffffff; border-radius: 20px; max-width: 540px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;">
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 1.25rem 1.5rem; border-bottom: 1px solid #fecaca; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: #fee2e2; border: 1px solid #f87171; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #991b1b;">Clear Old Order Data</h3>
                        <p style="margin: 0; font-size: 0.8rem; color: #b91c1c;">Safely purge historical orders and linked financial records</p>
                    </div>
                </div>
                <button type="button" onclick="closePurgeModal()" style="background: none; border: none; font-size: 1.5rem; color: #991b1b; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('admin.orders.purgeOld') }}" method="POST" id="purgeForm" style="padding: 1.5rem;">
                @csrf

                <!-- Warning Alert -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #92400e; display: flex; gap: 0.65rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span><strong>Permanent Action:</strong> Deleting orders will cascade delete corresponding order line items, sales records, payment transactions, and deliveries.</span>
                </div>

                <!-- Scope Selection -->
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-weight: 700; font-size: 0.875rem; color: #334155; margin-bottom: 0.5rem;">Purge Scope</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; font-size: 0.875rem; font-weight: 600;" id="scope_days_label">
                            <input type="radio" name="scope" value="days" checked onchange="toggleScope(this.value)">
                            <span>By Order Age</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; font-size: 0.875rem; font-weight: 600;" id="scope_all_label">
                            <input type="radio" name="scope" value="all" onchange="toggleScope(this.value)">
                            <span style="color: #dc2626;">Reset All Orders</span>
                        </label>
                    </div>
                </div>

                <!-- Days Input (shown when scope is days) -->
                <div id="days_container" style="margin-bottom: 1.25rem;">
                    <label for="purge_days_input" style="display: block; font-weight: 700; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">Orders Older Than (Days)</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="number" name="days" id="purge_days_input" value="30" min="0" max="3650" class="form-control-input" style="width: 120px; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px;" oninput="fetchPurgePreview()">
                        <span style="font-size: 0.85rem; color: #64748b;">days (e.g. 7, 30, 60, 90)</span>
                    </div>
                </div>

                <!-- Status Filter -->
                <div style="margin-bottom: 1.25rem;">
                    <label for="purge_status_select" style="display: block; font-weight: 700; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">Filter by Order Status</label>
                    <select name="status" id="purge_status_select" class="form-control-select" style="width: 100%; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px;" onchange="fetchPurgePreview()">
                        <option value="all">All Statuses (Completed, Pending, etc.)</option>
                        <option value="completed">Completed Orders Only</option>
                        <option value="cancelled">Cancelled Orders Only</option>
                        <option value="pending">Pending Orders Only</option>
                    </select>
                </div>

                <!-- Stock & Backup Options -->
                <div style="margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="restore_stock" value="1" checked>
                        <span>Restore inventory stock for any deleted pending/preparing orders</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="backup" value="1" checked>
                        <span>Export a JSON backup to server before clearing</span>
                    </label>
                </div>

                <!-- Live Impact Preview Badge -->
                <div id="purge_preview_box" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #475569;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span>Matching Orders to Delete:</span>
                        <strong id="preview_orders_count" style="color: #dc2626;">Loading...</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #64748b;">
                        <span>Related Items / Sales / Payments:</span>
                        <span id="preview_sub_counts">...</span>
                    </div>
                </div>

                <!-- Confirmation Input -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="confirm_phrase_input" style="display: block; font-weight: 700; font-size: 0.875rem; color: #991b1b; margin-bottom: 0.35rem;">
                        Type <span style="background: #fee2e2; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace;">CONFIRM</span> to proceed:
                    </label>
                    <input type="text" name="confirm_phrase" id="confirm_phrase_input" placeholder="CONFIRM" required autocomplete="off"
                           style="width: 100%; padding: 0.65rem 0.85rem; border: 2px solid #f87171; border-radius: 8px; font-size: 0.95rem; font-weight: 700;"
                           oninput="checkConfirmPhrase(this.value)">
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" onclick="closePurgeModal()" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; border-radius: 50px; background: #e2e8f0; color: #334155; border: none; font-weight: 700; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" id="btn_submit_purge" disabled class="btn btn-danger" style="padding: 0.65rem 1.5rem; border-radius: 50px; background: #dc2626; color: #ffffff; border: none; font-weight: 800; cursor: not-allowed; opacity: 0.6; transition: all 0.2s ease;">
                        Permanently Clear Orders
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPurgeModal() {
            const modal = document.getElementById('purgeModal');
            if (modal) {
                modal.style.display = 'flex';
                fetchPurgePreview();
            }
        }

        function closePurgeModal() {
            const modal = document.getElementById('purgeModal');
            if (modal) {
                modal.style.display = 'none';
                document.getElementById('confirm_phrase_input').value = '';
                checkConfirmPhrase('');
            }
        }

        function toggleScope(val) {
            const container = document.getElementById('days_container');
            if (container) {
                container.style.display = val === 'all' ? 'none' : 'block';
            }
            fetchPurgePreview();
        }

        function checkConfirmPhrase(val) {
            const btn = document.getElementById('btn_submit_purge');
            if (val.trim().toUpperCase() === 'CONFIRM') {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            } else {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                btn.style.cursor = 'not-allowed';
            }
        }

        let previewDebounceTimer = null;
        function fetchPurgePreview() {
            clearTimeout(previewDebounceTimer);
            previewDebounceTimer = setTimeout(() => {
                const scope = document.querySelector('input[name="scope"]:checked')?.value || 'days';
                const days = document.getElementById('purge_days_input')?.value || '30';
                const status = document.getElementById('purge_status_select')?.value || 'all';

                const url = `{{ route('admin.orders.purgePreview') }}?scope=${encodeURIComponent(scope)}&days=${encodeURIComponent(days)}&status=${encodeURIComponent(status)}`;

                fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('preview_orders_count').innerText = `${data.orders_count} orders`;
                        document.getElementById('preview_sub_counts').innerText = `${data.items_count} items, ${data.sales_count} sales, ${data.payments_count} payments`;
                    }
                })
                .catch(err => {
                    console.error('Failed to preview purge count', err);
                });
            }, 250);
        }
    </script>
    @endif
</div>
@endsection
