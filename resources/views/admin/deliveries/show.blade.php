@extends('layouts.app')

@section('title', 'Delivery #' . $delivery->tracking_number . ' - Bakery System')
@section('header_title', 'Delivery Details')
@section('header_subtitle', 'Monitor package fulfillment, driver dispatch, and customer destination')

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

    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('admin.deliveries.index') }}" class="deliveries-tab-btn" style="border: 1px solid #cbd5e1; background: #fff;">
            ← Back to Deliveries
        </a>

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
        <span class="delivery-status-badge {{ $badgeClass }}" style="font-size: 0.9rem; padding: 0.4rem 1rem;">
            ● {{ $statusLabel }}
        </span>
    </div>

    <!-- Grid Layout -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">

        <!-- Delivery & Customer Info Card -->
        <div class="delivery-metric-card" style="display: block;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-color); margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid rgba(238, 233, 224, 0.8); padding-bottom: 0.6rem;">
                📦 Delivery Information
            </h3>
            <div style="display: grid; grid-template-columns: 140px 1fr; gap: 0.6rem; font-size: 0.875rem;">
                <span style="color: var(--text-muted); font-weight: 700;">Tracking Code:</span>
                <span style="font-weight: 800; color: #563020;">{{ $delivery->tracking_number }}</span>

                <span style="color: var(--text-muted); font-weight: 700;">Order Reference:</span>
                <span>
                    @if($delivery->order)
                        <a href="{{ route('admin.orders.show', $delivery->order) }}" style="color: #0369a1; font-weight: 700; text-decoration: underline;">
                            #{{ $delivery->order->order_number }}
                        </a>
                        <span style="font-size: 0.775rem; color: var(--text-muted);">({{ ucfirst($delivery->order->order_status) }})</span>
                    @else
                        N/A
                    @endif
                </span>

                <span style="color: var(--text-muted); font-weight: 700;">Recipient Name:</span>
                <span style="font-weight: 700;">{{ $delivery->recipient_name }}</span>

                <span style="color: var(--text-muted); font-weight: 700;">Contact Phone:</span>
                <span>{{ $delivery->recipient_phone ?: 'None provided' }}</span>

                <span style="color: var(--text-muted); font-weight: 700;">Scheduled For:</span>
                <span style="font-weight: 600;">
                    {{ $delivery->scheduled_at ? $delivery->scheduled_at->format('M d, Y - h:i A') : 'Immediate dispatch' }}
                </span>

                <span style="color: var(--text-muted); font-weight: 700;">Delivered At:</span>
                <span>
                    {{ $delivery->delivered_at ? $delivery->delivered_at->format('M d, Y - h:i A') : 'Not yet delivered' }}
                </span>

                <span style="color: var(--text-muted); font-weight: 700;">Delivery Fee:</span>
                <span style="font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format($delivery->delivery_fee, 2) }}</span>
            </div>

            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid rgba(238, 233, 224, 0.8);">
                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.35rem;">
                    📍 Destination Address
                </div>
                <div style="background: rgba(86, 48, 32, 0.04); border: 1px solid rgba(86, 48, 32, 0.1); border-radius: 12px; padding: 0.85rem; font-size: 0.9rem; line-height: 1.4;">
                    {{ $delivery->delivery_address }}
                </div>
            </div>

            @if($delivery->notes)
                <div style="margin-top: 1rem;">
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.35rem;">
                        📝 Special Instructions
                    </div>
                    <div style="font-style: italic; color: var(--text-color); font-size: 0.875rem;">
                        "{{ $delivery->notes }}"
                    </div>
                </div>
            @endif
        </div>

        <!-- Driver Assignment & Status Controls -->
        <div class="delivery-metric-card" style="display: block;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-color); margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid rgba(238, 233, 224, 0.8); padding-bottom: 0.6rem;">
                🛵 Driver & Workflow Actions
            </h3>

            <!-- Current Driver Display -->
            <div style="background: rgba(3, 105, 161, 0.06); border: 1px solid rgba(3, 105, 161, 0.18); border-radius: 14px; padding: 1rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.775rem; font-weight: 800; text-transform: uppercase; color: #0369a1; margin-bottom: 0.25rem;">
                    Assigned Driver
                </div>
                @if($delivery->deliveryStaff)
                    <div style="font-size: 1.1rem; font-weight: 800; color: #0c4a6e;">
                        👤 {{ $delivery->deliveryStaff->name }}
                    </div>
                    <div style="font-size: 0.8rem; color: #0369a1;">
                        ✉️ {{ $delivery->deliveryStaff->email }} | 📞 {{ $delivery->deliveryStaff->phone ?? 'No phone' }}
                    </div>
                @else
                    <div style="color: #94a3b8; font-weight: 700; font-style: italic;">
                        No delivery driver assigned yet.
                    </div>
                @endif
            </div>

            <!-- Driver Assignment Form (Admin & Manager) -->
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <form action="{{ route('admin.deliveries.assign', $delivery) }}" method="POST" style="margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid rgba(238, 233, 224, 0.8);">
                    @csrf
                    <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.35rem;">
                        Reassign Driver
                    </label>
                    <div style="display: flex; gap: 0.5rem;">
                        <select name="user_id" required style="flex: 1; padding: 0.55rem 0.75rem; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                            <option value="">-- Choose Driver --</option>
                            @foreach($deliveryStaff as $staff)
                                <option value="{{ $staff->id }}" {{ $delivery->user_id === $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ ucfirst(str_replace('_', ' ', $staff->role)) }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="deliveries-tab-btn" style="border: 1px solid #cbd5e1; background: #ffffff; white-space: nowrap;">
                            Assign
                        </button>
                    </div>
                </form>
            @endif

            <!-- Status Transition Buttons -->
            <div>
                <label style="display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.6rem;">
                    Update Fulfillment Status
                </label>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @if($delivery->delivery_status !== 'out_for_delivery' && $delivery->delivery_status !== 'delivered')
                        <form action="{{ route('admin.deliveries.updateStatus', $delivery) }}" method="POST">
                            @csrf
                            <input type="hidden" name="delivery_status" value="out_for_delivery">
                            <button type="submit" class="btn-delivery-primary" style="width: 100%; justify-content: center; background: #6d28d9;">
                                🚀 Mark Out for Delivery (In Transit)
                            </button>
                        </form>
                    @endif

                    @if($delivery->delivery_status !== 'delivered')
                        <form action="{{ route('admin.deliveries.updateStatus', $delivery) }}" method="POST">
                            @csrf
                            <input type="hidden" name="delivery_status" value="delivered">
                            <button type="submit" class="btn-delivery-primary" style="width: 100%; justify-content: center; background: #16a34a;">
                                ✅ Mark as Delivered (Complete Order)
                            </button>
                        </form>
                    @endif

                    @if($delivery->delivery_status !== 'cancelled' && $delivery->delivery_status !== 'delivered')
                        <form action="{{ route('admin.deliveries.updateStatus', $delivery) }}" method="POST" onsubmit="return confirm('Cancel this delivery?')">
                            @csrf
                            <input type="hidden" name="delivery_status" value="cancelled">
                            <button type="submit" class="deliveries-tab-btn" style="width: 100%; justify-content: center; color: #ef4444; border: 1px solid #fecaca; background: #fff;">
                                ✕ Cancel Delivery
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <!-- Order Items Card (if linked order exists) -->
    @if($delivery->order)
        <div class="deliveries-table-card">
            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(238, 233, 224, 0.8); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--text-color);">
                    🥐 Order Package Contents (#{{ $delivery->order->order_number }})
                </h3>
                <span style="font-weight: 800; color: #563020; font-size: 1rem;">
                    Total: {{ $currency }}{{ number_format($delivery->order->total, 2) }}
                </span>
            </div>
            <div style="overflow-x: auto;">
                <table class="deliveries-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($delivery->order->items as $item)
                            <tr>
                                <td style="font-weight: 700;">{{ $item->product ? $item->product->name : 'Custom Item' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
                                <td style="text-align: right; font-weight: 700;">{{ $currency }}{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
