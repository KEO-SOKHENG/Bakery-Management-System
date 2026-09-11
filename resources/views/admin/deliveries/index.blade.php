@extends('layouts.app')

@section('title', 'Delivery Management - Bakery System')
@section('header_title', 'Delivery & Dispatch')
@section('header_subtitle', 'Coordinate bakery delivery orders, dispatch drivers, and monitor delivery statuses')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/deliveries.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="deliveries-container">

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

    <!-- Header bar -->
    <div class="deliveries-header">
        <div class="deliveries-title-group">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <span data-lang-key="delivery_management">{{ __('messages.delivery_management') ?? 'Delivery Management' }}</span>
                <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted);">({{ $totalDeliveries }})</span>
            </h2>
            <p data-lang-key="delivery_management_subtitle">{{ __('messages.delivery_management_subtitle') ?? 'Live driver assignments and fulfillment tracking' }}</p>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isCashier())
            <button type="button" class="btn-delivery-primary" id="btn_open_schedule_modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span data-lang-key="schedule_delivery">{{ __('messages.schedule_delivery') ?? 'Schedule Delivery' }}</span>
            </button>
        @endif
    </div>

    <!-- KPI Summary Cards Grid -->
    <div class="deliveries-metrics-grid">
        <div class="delivery-metric-card">
            <div class="delivery-metric-icon" style="background: rgba(86, 48, 32, 0.1); color: #563020;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <div class="delivery-metric-info">
                <span class="delivery-metric-label" data-lang-key="total_deliveries">{{ __('messages.total_deliveries') ?? 'Total Deliveries' }}</span>
                <span class="delivery-metric-value" id="kpi_total_deliveries">{{ number_format($totalDeliveries) }}</span>
            </div>
        </div>

        <div class="delivery-metric-card">
            <div class="delivery-metric-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="delivery-metric-info">
                <span class="delivery-metric-label" data-lang-key="pending_assigned">{{ __('messages.pending_assigned') ?? 'Pending / Assigned' }}</span>
                <span class="delivery-metric-value" id="kpi_pending_deliveries">{{ number_format($pendingCount) }}</span>
            </div>
        </div>

        <div class="delivery-metric-card">
            <div class="delivery-metric-icon" style="background: rgba(147, 51, 234, 0.12); color: #7e22ce;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="delivery-metric-info">
                <span class="delivery-metric-label" data-lang-key="out_for_delivery">{{ __('messages.out_for_delivery') ?? 'Out for Delivery' }}</span>
                <span class="delivery-metric-value" id="kpi_in_transit_deliveries">{{ number_format($outForDeliveryCount) }}</span>
            </div>
        </div>

        <div class="delivery-metric-card">
            <div class="delivery-metric-icon" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="delivery-metric-info">
                <span class="delivery-metric-label" data-lang-key="delivered">{{ __('messages.delivered') ?? 'Delivered' }}</span>
                <span class="delivery-metric-value" id="kpi_delivered_deliveries">{{ number_format($deliveredCount) }}</span>
            </div>
        </div>
    </div>

    <!-- Toolbar: Filter Tabs & Search Form -->
    <div class="deliveries-toolbar">
        <div class="deliveries-filter-tabs" id="delivery_status_tabs">
            <a href="{{ route('admin.deliveries.index', ['tab' => 'all']) }}" class="deliveries-tab-btn {{ $tab === 'all' ? 'active' : '' }}" data-lang-key="all">
                {{ __('messages.all') ?? 'All' }}
            </a>
            <a href="{{ route('admin.deliveries.index', ['tab' => 'pending']) }}" class="deliveries-tab-btn {{ $tab === 'pending' ? 'active' : '' }}" data-lang-key="pending">
                {{ __('messages.pending') ?? 'Pending / Assigned' }}
            </a>
            <a href="{{ route('admin.deliveries.index', ['tab' => 'in_transit']) }}" class="deliveries-tab-btn {{ $tab === 'in_transit' ? 'active' : '' }}" data-lang-key="out_for_delivery">
                {{ __('messages.out_for_delivery') ?? 'In Transit' }}
            </a>
            <a href="{{ route('admin.deliveries.index', ['tab' => 'completed']) }}" class="deliveries-tab-btn {{ $tab === 'completed' ? 'active' : '' }}" data-lang-key="completed">
                {{ __('messages.completed') ?? 'Delivered' }}
            </a>
        </div>

        <form action="{{ route('admin.deliveries.index') }}" method="GET" class="deliveries-search-form">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input
                type="text"
                name="search"
                id="delivery_search_input"
                class="deliveries-search-input"
                placeholder="{{ __('messages.search_delivery_placeholder') ?? 'Search tracking, order, recipient...' }}"
                value="{{ request('search') }}"
            >
            <button type="submit" class="deliveries-tab-btn" style="border: 1px solid #cbd5e1; background: #ffffff;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span data-lang-key="search">{{ __('messages.search') ?? 'Search' }}</span>
            </button>
            @if(request()->hasAny(['search', 'status', 'user_id', 'date']))
                <a href="{{ route('admin.deliveries.index', ['tab' => $tab]) }}" class="deliveries-tab-btn" style="color: #ef4444;" title="Reset filters">
                    ✕
                </a>
            @endif
        </form>
    </div>

    <!-- Deliveries Table Card -->
    <div class="deliveries-table-card">
        <div style="overflow-x: auto;">
            <table class="deliveries-table" id="deliveries_table">
                <thead>
                    <tr>
                        <th>Tracking / Order</th>
                        <th>Recipient & Customer</th>
                        <th>Delivery Address</th>
                        <th>Scheduled For</th>
                        <th>Assigned Staff</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                        @php
                            $status = $delivery->delivery_status;
                            $badgeClass = match($status) {
                                'assigned' => 'badge-assigned',
                                'out_for_delivery' => 'badge-out-for-delivery',
                                'delivered' => 'badge-delivered',
                                'cancelled', 'failed' => 'badge-cancelled',
                                default => 'badge-pending',
                            };
                            $statusLabel = ucwords(str_replace('_', ' ', $status));
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: #563020;">{{ $delivery->tracking_number }}</div>
                                @if($delivery->order)
                                    <a href="{{ route('admin.orders.show', $delivery->order) }}" style="font-size: 0.8rem; color: #64748b; text-decoration: underline;">
                                        Order #{{ $delivery->order->order_number }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 700;">{{ $delivery->recipient_name }}</div>
                                @if($delivery->recipient_phone)
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">📞 {{ $delivery->recipient_phone }}</div>
                                @endif
                            </td>
                            <td style="max-width: 250px;">
                                <div style="font-size: 0.85rem; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="{{ $delivery->delivery_address }}">
                                    📍 {{ $delivery->delivery_address }}
                                </div>
                            </td>
                            <td>
                                @if($delivery->scheduled_at)
                                    <div style="font-weight: 600;">{{ $delivery->scheduled_at->format('M d, Y') }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $delivery->scheduled_at->format('h:i A') }}</div>
                                @else
                                    <span style="color: var(--text-muted); font-style: italic;">Immediate</span>
                                @endif
                            </td>
                            <td>
                                @if($delivery->deliveryStaff)
                                    <span style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #0369a1;">
                                        👤 {{ $delivery->deliveryStaff->name }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="delivery-status-badge {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="deliveries-tab-btn" style="padding: 0.35rem 0.75rem; border: 1px solid #cbd5e1; font-size: 0.775rem;" title="View Details">
                                        View
                                    </a>

                                    <!-- Quick Status Update Dropdown -->
                                    <form action="{{ route('admin.deliveries.updateStatus', $delivery) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        <select name="delivery_status" onchange="this.form.submit()" style="padding: 0.35rem 0.6rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.775rem; font-weight: 700; background: #ffffff; cursor: pointer;">
                                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="assigned" {{ $status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                            <option value="out_for_delivery" {{ $status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                            <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 0.5rem; opacity: 0.5;"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                <p style="font-weight: 700; margin: 0;">No delivery orders found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveries->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid rgba(238, 233, 224, 0.7);">
                {{ $deliveries->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Schedule Delivery Modal -->
<div class="delivery-modal-backdrop" id="schedule_delivery_modal">
    <div class="delivery-modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--text-color);">
                🚚 Schedule Order Delivery
            </h3>
            <button type="button" id="btn_close_schedule_modal" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="{{ route('admin.deliveries.store') }}" method="POST">
            @csrf

            <!-- Select Eligible Order -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-color);">
                    Select Order <span style="color: #ef4444;">*</span>
                </label>
                <select name="order_id" id="modal_order_id" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.875rem;" onchange="handleOrderSelection(this)">
                    <option value="">-- Choose Order to Deliver --</option>
                    @foreach($eligibleOrders as $elOrder)
                        <option
                            value="{{ $elOrder->id }}"
                            data-customer="{{ $elOrder->customer_name }}"
                            data-phone="{{ $elOrder->customer->phone ?? '' }}"
                            data-address="{{ $elOrder->customer->address ?? '' }}"
                        >
                            #{{ $elOrder->order_number }} - {{ $elOrder->customer_name }} ({{ $currency }}{{ number_format($elOrder->total, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Recipient Name -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-color);">
                    Recipient Name
                </label>
                <input type="text" name="recipient_name" id="modal_recipient_name" placeholder="Recipient customer name" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.875rem;">
            </div>

            <!-- Recipient Phone -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-color);">
                    Recipient Phone
                </label>
                <input type="text" name="recipient_phone" id="modal_recipient_phone" placeholder="e.g. +855 12 345 678" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.875rem;">
            </div>

            <!-- Delivery Address -->
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-color);">
                    Delivery Address <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="delivery_address" id="modal_delivery_address" required rows="2" placeholder="Full street address, building, district, Phnom Penh" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.875rem;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <!-- Scheduled Date/Time -->
                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-color);">
                        Scheduled Time
                    </label>
                    <input type="datetime-local" name="scheduled_at" id="modal_scheduled_at" value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.875rem;">
                </div>

                <!-- Delivery Fee -->
                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-color);">
                        Delivery Fee ({{ $currency }})
                    </label>
                    <input type="number" step="0.01" name="delivery_fee" value="0.00" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.875rem;">
                </div>
            </div>

            <!-- Assign Staff -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-color);">
                    Assign Delivery Driver / Staff
                </label>
                <select name="user_id" id="modal_user_id" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.875rem;">
                    <option value="">-- Assign Later (Pending) --</option>
                    @foreach($deliveryStaff as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ ucfirst(str_replace('_', ' ', $staff->role)) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Notes -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-color);">
                    Special Delivery Instructions
                </label>
                <input type="text" name="notes" placeholder="e.g. Call upon arrival, fragile cake box" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.875rem;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" id="btn_cancel_schedule_modal" class="deliveries-tab-btn" style="border: 1px solid #cbd5e1;">Cancel</button>
                <button type="submit" class="btn-delivery-primary" id="btn_submit_schedule">Confirm & Schedule</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('schedule_delivery_modal');
        const openBtn = document.getElementById('btn_open_schedule_modal');
        const closeBtn = document.getElementById('btn_close_schedule_modal');
        const cancelBtn = document.getElementById('btn_cancel_schedule_modal');

        if (openBtn && modal) {
            openBtn.addEventListener('click', () => modal.classList.add('is-active'));
        }
        if (closeBtn && modal) {
            closeBtn.addEventListener('click', () => modal.classList.remove('is-active'));
        }
        if (cancelBtn && modal) {
            cancelBtn.addEventListener('click', () => modal.classList.remove('is-active'));
        }
    });

    function handleOrderSelection(select) {
        const option = select.options[select.selectedIndex];
        if (!option || !option.value) return;

        const custName = option.getAttribute('data-customer') || '';
        const phone = option.getAttribute('data-phone') || '';
        const address = option.getAttribute('data-address') || '';

        const nameInput = document.getElementById('modal_recipient_name');
        const phoneInput = document.getElementById('modal_recipient_phone');
        const addrInput = document.getElementById('modal_delivery_address');

        if (nameInput && !nameInput.value) nameInput.value = custName;
        if (phoneInput && !phoneInput.value) phoneInput.value = phone;
        if (addrInput && !addrInput.value) addrInput.value = address;
    }
</script>
@endsection
