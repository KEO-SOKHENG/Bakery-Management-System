@extends('layouts.app')

@section('title', "Order #{$order->order_number} - Bakery System")
@section('header_title', "Order #{$order->order_number}")
@section('header_subtitle', "Order details, fulfillment tracking, and thermal receipt")

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/orders.css') }}">
@endpush

@section('content')
<div class="orders-container">

    <!-- Top Action Bar / Back Navigation -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('admin.orders') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Back to Orders</span>
        </a>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <!-- Print Thermal Receipt Button -->
            <button type="button" class="btn btn-secondary" id="btn_open_receipt_modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Print Receipt</span>
            </button>

            <!-- Edit Button if Pending -->
            @if($order->canBeEdited())
                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    <span>Edit Order</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <!-- STATUS TIMELINE / STEPPER TRACKING -->
    @php
        $normStatus = $order->normalized_status;
        $isCancelled = $normStatus === 'cancelled';
        $isDeliveryFlow = ($normStatus === 'out_for_delivery');

        // Steps definition
        if ($isDeliveryFlow) {
            $steps = [
                'pending'          => ['label' => 'Pending', 'icon' => '1'],
                'preparing'        => ['label' => 'Preparing', 'icon' => '2'],
                'ready_for_pickup' => ['label' => 'Ready for Pickup', 'icon' => '3'],
                'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => '4'],
                'completed'        => ['label' => 'Completed', 'icon' => '5'],
            ];
        } else {
            $steps = [
                'pending'          => ['label' => 'Pending', 'icon' => '1'],
                'preparing'        => ['label' => 'Preparing', 'icon' => '2'],
                'ready_for_pickup' => ['label' => 'Ready for Pickup', 'icon' => '3'],
                'completed'        => ['label' => 'Completed', 'icon' => '4'],
            ];
        }

        $stepKeys = array_keys($steps);
        $currentIndex = array_search($normStatus, $stepKeys);
        if ($currentIndex === false && $normStatus === 'completed') {
            $currentIndex = count($stepKeys) - 1;
        }

        $progressPercent = 0;
        if ($isCancelled) {
            $progressPercent = 0;
        } elseif ($currentIndex !== false && count($stepKeys) > 1) {
            $progressPercent = ($currentIndex / (count($stepKeys) - 1)) * 100;
        }
    @endphp

    <x-card class="detail-card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #29150d; margin: 0;">Order Status Timeline</h3>
                <span style="font-size: 0.8rem; color: #64748b;">Real-time fulfillment tracking</span>
            </div>
            <div>
                @if($isCancelled)
                    <span class="status-badge status-cancelled">Cancelled</span>
                @else
                    <span class="status-badge status-{{ $normStatus }}">
                        {{ ucwords(str_replace('_', ' ', $normStatus)) }}
                    </span>
                @endif
            </div>
        </div>

        @if($isCancelled)
            <div style="background: #fef2f2; border: 1px dashed #ef4444; border-radius: 16px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; color: #991b1b;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <div>
                    <div style="font-weight: 800; font-size: 1rem;">This order has been cancelled</div>
                    <div style="font-size: 0.85rem; color: #b91c1c;">Reserved bakery inventory has been safely restored to stock. Cancellation is final.</div>
                </div>
            </div>
        @else
            <div class="order-stepper">
                <div class="stepper-track-bg">
                    <div class="stepper-track-fill" style="width: {{ $progressPercent }}%;"></div>
                </div>

                @foreach($steps as $key => $data)
                    @php
                        $index = array_search($key, $stepKeys);
                        $isPast = $currentIndex !== false && $index < $currentIndex;
                        $isCurrent = $currentIndex !== false && $index === $currentIndex;
                    @endphp
                    <div class="stepper-step {{ $isPast ? 'completed' : ($isCurrent ? 'current' : 'pending') }}">
                        <div class="stepper-circle">
                            @if($isPast)
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <span class="stepper-label">{{ $data['label'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    <!-- MAIN TWO-COLUMN DETAILS -->
    <div class="order-detail-grid">

        <!-- LEFT COLUMN: Items & Custom Cake Instructions -->
        <div>
            <!-- Order Items Card -->
            <x-card class="detail-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span>Ordered Items ({{ $order->items->count() }})</span>
                    </span>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #64748b;">
                        {{ $order->items->sum('quantity') }} total units
                    </span>
                </div>

                <table class="orders-table" style="box-shadow: none; border-radius: 0;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th style="text-align: right;">Line Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div style="font-weight: 800; color: #29150d;">{{ $item->product ? $item->product->name : 'Item #' . $item->product_id }}</div>
                                    @if($item->product && $item->product->category)
                                        <div style="font-size: 0.75rem; color: #64748b;">{{ $item->product->category->name }}</div>
                                    @endif
                                </td>
                                <td style="font-weight: 700;">
                                    {{ $currency }}{{ number_format($item->price, 2) }}
                                </td>
                                <td>
                                    <span style="font-weight: 800; background: var(--bg-main); color: var(--text-title); padding: 0.25rem 0.65rem; border-radius: 8px; border: 1px solid var(--border-color);">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 800; color: var(--text-title);">
                                    {{ $currency }}{{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-card>

            <!-- Custom Cake / Special Instructions Card -->
            @if($order->is_custom || !empty($order->special_instructions) || !empty($order->pickup_date))
                <x-card class="detail-card" style="border-left: 4px solid #f472b6;">
                    <div class="detail-card-header">
                        <span class="detail-card-title">
                            <span style="font-size: 1.25rem;">🎂</span>
                            <span>Custom Cake & Special Instructions</span>
                        </span>
                        @if($order->is_custom)
                            <span class="type-pill type-custom">Custom Cake Order</span>
                        @endif
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.9rem;">
                        @if(!empty($order->pickup_date))
                            <div style="display: flex; gap: 0.75rem; align-items: center; background: #fff1f2; padding: 0.75rem 1rem; border-radius: 12px; color: #9f1239;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <div>
                                    <span style="font-weight: 800;">Target Pickup / Delivery Schedule:</span>
                                    <strong style="margin-left: 0.35rem;">{{ $order->pickup_date->format('M d, Y h:i A') }}</strong>
                                </div>
                            </div>
                        @endif

                        @if(!empty($order->special_instructions))
                            <div>
                                <label style="display: block; font-weight: 800; color: #64748b; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.35rem;">
                                    Custom Specifications / Inscription / Notes:
                                </label>
                                <div style="background: #faf6f0; border: 1px solid rgba(238, 233, 224, 0.9); border-radius: 14px; padding: 1rem; color: #29150d; font-weight: 600; line-height: 1.5; white-space: pre-line;">
                                    {{ $order->special_instructions }}
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif

        </div>

        <!-- RIGHT COLUMN: Customer, Payment Summary & Contextual Actions -->
        <div>
            <!-- Customer Information Card -->
            <x-card class="detail-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Customer Profile</span>
                    </span>
                    @if($order->customer)
                        <a href="{{ route('admin.customers.show', $order->customer) }}" style="font-size: 0.75rem; font-weight: 700; color: #563020; text-decoration: underline;">
                            View Customer
                        </a>
                    @endif
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.875rem;">
                    <div>
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Customer Name</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #29150d; margin-top: 2px;">
                            {{ $order->customer_name }}
                        </div>
                    </div>

                    @if($order->customer)
                        @if($order->customer->phone)
                            <div>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Phone Number</div>
                                <div style="font-weight: 700; color: #29150d;">{{ $order->customer->phone }}</div>
                            </div>
                        @endif

                        @if($order->customer->email)
                            <div>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Email Address</div>
                                <div style="font-weight: 600; color: #29150d;">{{ $order->customer->email }}</div>
                            </div>
                        @endif

                        @if($order->customer->address)
                            <div>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Delivery / Physical Address</div>
                                <div style="font-weight: 600; color: #29150d;">{{ $order->customer->address }}</div>
                            </div>
                        @endif

                        <div style="display: flex; justify-content: space-between; align-items: center; background: #faf6f0; padding: 0.65rem 0.9rem; border-radius: 12px; margin-top: 0.35rem;">
                            <div>
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Loyalty Tier:</span>
                                <strong style="color: #d97706; text-transform: uppercase;">{{ $order->customer->loyalty_tier }}</strong>
                            </div>
                            <div>
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Points:</span>
                                <strong style="color: #10b981;">{{ $order->customer->loyalty_points }} pts</strong>
                            </div>
                        </div>
                    @else
                        <div style="background: #faf6f0; padding: 0.75rem 1rem; border-radius: 12px; color: #64748b; font-weight: 600; font-size: 0.825rem;">
                            Walk-in Customer (No customer account attached)
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Financial Summary & Payment Card -->
            <x-card class="detail-card">
                <div class="detail-card-header">
                    <span class="detail-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <span>Payment & Summary</span>
                    </span>
                    <span class="payment-badge {{ $order->payment_status === 'paid' ? 'paid' : 'unpaid' }}">
                        {{ strtoupper($order->payment_status ?? 'unpaid') }}
                    </span>
                </div>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong style="color: #29150d;">{{ $currency }}{{ number_format($order->subtotal, 2) }}</strong>
                </div>

                <div class="summary-row">
                    <span>Discount Applied:</span>
                    <span style="color: #10b981; font-weight: 700;">-{{ $currency }}{{ number_format($order->discount, 2) }}</span>
                </div>

                <div class="summary-row">
                    <span>Tax ({{ $taxPercentage }}%):</span>
                    <span style="color: #64748b; font-weight: 700;">+{{ $currency }}{{ number_format($order->tax, 2) }}</span>
                </div>

                <div class="summary-row grand-total">
                    <span>Grand Total:</span>
                    <span style="color: var(--text-title);">{{ $currency }}{{ number_format($order->total, 2) }}</span>
                </div>

                <div style="border-top: 1px solid rgba(238, 233, 224, 0.8); margin-top: 1rem; padding-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Payment Method:</span>
                        <strong style="text-transform: uppercase;">{{ $order->payment_method === 'qr_code' ? 'KHQR' : $order->payment_method }}</strong>
                    </div>

                    @if($order->sale)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b;">Sale ID:</span>
                            <span>#SALE-{{ $order->sale->id }}</span>
                        </div>
                    @endif

                    @if($order->payments->isNotEmpty())
                        @php $lastPay = $order->payments->last(); @endphp
                        @if($lastPay && $lastPay->payment_reference)
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Payment Ref:</span>
                                <span style="font-family: monospace; font-weight: 700;">{{ $lastPay->payment_reference }}</span>
                            </div>
                        @endif
                    @endif
                </div>
            </x-card>

            <!-- CONTEXTUAL STATUS WORKFLOW ACTIONS -->
            <x-card class="detail-card" style="background: linear-gradient(145deg, #faf6f0 0%, #f4ede4 100%);">
                <div class="detail-card-header" style="border-bottom: 1px solid rgba(86, 48, 32, 0.15);">
                    <span class="detail-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="M12 22v-4"/><path d="m19.07 19.07-2.83-2.83"/><path d="M22 12h-4"/><path d="m19.07 4.93-2.83 2.83"/></svg>
                        <span>Workflow Actions</span>
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @if($normStatus === 'pending')
                        <!-- Start Preparing -->
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="preparing">
                            <button type="submit" class="btn btn-primary btn-block" id="btn_status_preparing">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                <span>Start Preparing</span>
                            </button>
                        </form>

                        <!-- Cancel Order -->
                        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Cancel Order #{{ $order->order_number }} and restore stock?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-block" id="btn_cancel_order">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                <span>Cancel Order</span>
                            </button>
                        </form>

                    @elseif($normStatus === 'preparing')
                        <!-- Ready for Pickup -->
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="ready_for_pickup">
                            <button type="submit" class="btn btn-success btn-block" id="btn_status_ready">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                <span>Mark Ready for Pickup</span>
                            </button>
                        </form>

                        <!-- Cancel Order -->
                        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Cancel Order #{{ $order->order_number }} and restore stock?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-block" id="btn_cancel_order">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                <span>Cancel Order</span>
                            </button>
                        </form>

                    @elseif($normStatus === 'ready_for_pickup')
                        <!-- Complete Order (In-Store Pickup) -->
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-success btn-block" id="btn_status_complete">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Complete Order</span>
                            </button>
                        </form>

                        <!-- Send Out for Delivery -->
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="out_for_delivery">
                            <button type="submit" class="btn btn-secondary btn-block" id="btn_status_delivery">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                <span>Send Out for Delivery</span>
                            </button>
                        </form>

                    @elseif($normStatus === 'out_for_delivery')
                        <!-- Complete Delivery -->
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-success btn-block" id="btn_status_complete">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Mark Delivered & Completed</span>
                            </button>
                        </form>

                    @elseif($normStatus === 'completed')
                        <div style="text-align: center; color: #16a34a; font-weight: 800; padding: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Order is Fully Completed</span>
                        </div>
                    @else
                        <div style="text-align: center; color: #dc2626; font-weight: 800; padding: 0.5rem;">
                            Order is Cancelled
                        </div>
                    @endif
                </div>
            </x-card>

        </div>

    </div>

</div>

<!-- ==========================================================================
     THERMAL RECEIPT MODAL (Matching POS Thermal Slip Format)
     ========================================================================== -->
<div class="receipt-modal-backdrop" id="order_receipt_modal">
    <div class="receipt-modal-dialog">
        <!-- Printable Slip Area -->
        <div id="thermal_slip_print_area">
            <div class="thermal-slip">
                <div class="slip-center">
                    <div class="slip-title">{{ $bakeryName }}</div>
                    <div class="slip-meta">{{ $bakeryAddress }}</div>
                    <div class="slip-meta">Tel: {{ $bakeryPhone }}</div>
                    <div class="slip-meta" style="margin-top: 4px; font-style: italic;">{{ $receiptHeader }}</div>
                </div>

                <div class="slip-divider"></div>

                <div class="slip-meta" style="display: flex; justify-content: space-between;">
                    <span>Order: <strong>{{ $order->order_number }}</strong></span>
                    <span>{{ $order->order_type }}</span>
                </div>
                <div class="slip-meta" style="display: flex; justify-content: space-between;">
                    <span>Date: {{ $order->created_at ? $order->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i') }}</span>
                    <span>Cashier: <strong>{{ $order->user ? $order->user->name : 'Staff' }}</strong></span>
                </div>
                <div class="slip-meta">
                    <span>Customer: <strong>{{ $order->customer_name }}</strong></span>
                </div>
                @if($order->customer && $order->customer->phone)
                    <div class="slip-meta">
                        <span>Phone: {{ $order->customer->phone }}</span>
                    </div>
                @endif
                @if($order->customer && $order->customer->loyalty_tier)
                    <div class="slip-meta">
                        <span>Loyalty: <strong style="color: #15803d;">{{ ucfirst($order->customer->loyalty_tier) }} ({{ $order->customer->loyalty_points }} pts)</strong></span>
                    </div>
                @endif
                <div class="slip-meta">
                    <span>Payment: <strong style="text-transform: uppercase;">{{ $order->payment_method === 'qr_code' ? 'KHQR' : $order->payment_method }} ({{ ucfirst($order->payment_status ?? 'paid') }})</strong></span>
                </div>

                <div class="slip-divider"></div>

                <table class="slip-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="align-right">Qty x Price</th>
                            <th class="align-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product ? $item->product->name : 'Item #' . $item->product_id }}</td>
                                <td class="align-right">{{ $item->quantity }} x {{ $currency }}{{ number_format($item->price, 2) }}</td>
                                <td class="align-right">{{ $currency }}{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="slip-divider"></div>

                <div class="slip-totals-row">
                    <span>Subtotal:</span>
                    <span>{{ $currency }}{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="slip-totals-row">
                    <span>Discount:</span>
                    <span>{{ $currency }}{{ number_format($order->discount, 2) }}</span>
                </div>
                <div class="slip-totals-row">
                    <span>Tax ({{ $taxPercentage }}%):</span>
                    <span>{{ $currency }}{{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="slip-totals-row grand">
                    <span>TOTAL:</span>
                    <span>{{ $currency }}{{ number_format($order->total, 2) }}</span>
                </div>

                <div class="slip-divider"></div>

                <div class="slip-center slip-meta" style="margin-top: 6px;">
                    {{ $receiptFooter }}
                </div>
                <div class="slip-center slip-meta" style="margin-top: 6px; font-size: 0.7rem;">
                    *** Thank You & Come Again! ***
                </div>
            </div>
        </div>

        <!-- Modal Actions (Hidden in Print) -->
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.25rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
            <button type="button" class="btn btn-secondary" id="btn_close_receipt_modal" style="border-radius: 50px; padding: 0.5rem 1.25rem; cursor: pointer; font-weight: 700;">
                Close
            </button>
            <button type="button" class="btn btn-primary" id="btn_trigger_print" style="border-radius: 50px; padding: 0.5rem 1.4rem; background: #563020; color: #fff; border: none; cursor: pointer; font-weight: 800; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Print</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('order_receipt_modal');
    const openBtn = document.getElementById('btn_open_receipt_modal');
    const closeBtn = document.getElementById('btn_close_receipt_modal');
    const printBtn = document.getElementById('btn_trigger_print');

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => {
            modal.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('is-active');
            document.body.style.overflow = '';
        });
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('is-active');
                document.body.style.overflow = '';
            }
        });
    }

    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }
});
</script>
@endpush
@endsection
