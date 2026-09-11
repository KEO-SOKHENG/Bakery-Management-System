@extends('layouts.app')

@section('title', 'Schedule Delivery - Bakery System')
@section('header_title', 'Schedule Delivery')
@section('header_subtitle', 'Dispatch order package to customer location with assigned driver')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/deliveries.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="deliveries-container" style="max-width: 720px; margin: 0 auto;">

    <div style="margin-bottom: 1.25rem;">
        <a href="{{ url()->previous() ?: route('admin.deliveries.index') }}" class="deliveries-tab-btn" style="border: 1px solid #cbd5e1; background: #fff;">
            ← Back
        </a>
    </div>

    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 1rem; border-radius: 14px; margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="delivery-metric-card" style="display: block; padding: 2rem;">
        <h2 style="margin-top: 0; font-size: 1.35rem; font-weight: 800; color: #563020; margin-bottom: 1.5rem;">
            🚚 Schedule Bakery Delivery
        </h2>

        <form action="{{ route('admin.deliveries.store') }}" method="POST">
            @csrf

            <!-- Select Order -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                    Select Order <span style="color: #ef4444;">*</span>
                </label>
                @if($order)
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <div style="background: #faf6f0; border: 1px solid rgba(86, 48, 32, 0.2); border-radius: 12px; padding: 0.85rem 1rem; font-weight: 700; color: #563020;">
                        Order #{{ $order->order_number }} - {{ $order->customer_name }} ({{ $currency }}{{ number_format($order->total, 2) }})
                    </div>
                @else
                    <select name="order_id" required style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.9rem;" onchange="handleOrderSelection(this)">
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
                @endif
            </div>

            <!-- Recipient Name -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                    Recipient Name
                </label>
                <input
                    type="text"
                    name="recipient_name"
                    id="recipient_name_field"
                    placeholder="Recipient name"
                    value="{{ old('recipient_name', $order ? $order->customer_name : '') }}"
                    style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.9rem;"
                >
            </div>

            <!-- Contact Phone -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                    Contact Phone
                </label>
                <input
                    type="text"
                    name="recipient_phone"
                    id="recipient_phone_field"
                    placeholder="e.g. +855 12 345 678"
                    value="{{ old('recipient_phone', $order && $order->customer ? $order->customer->phone : '') }}"
                    style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.9rem;"
                >
            </div>

            <!-- Delivery Address -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                    Delivery Address <span style="color: #ef4444;">*</span>
                </label>
                <textarea
                    name="delivery_address"
                    id="delivery_address_field"
                    required
                    rows="3"
                    placeholder="Street, building, district, city"
                    style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.9rem;"
                >{{ old('delivery_address', $order && $order->customer ? $order->customer->address : '') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <!-- Scheduled At -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                        Scheduled Time
                    </label>
                    <input
                        type="datetime-local"
                        name="scheduled_at"
                        value="{{ old('scheduled_at', now()->addHours(2)->format('Y-m-d\TH:i')) }}"
                        style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.9rem;"
                    >
                </div>

                <!-- Delivery Fee -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                        Delivery Fee ({{ $currency }})
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        name="delivery_fee"
                        value="{{ old('delivery_fee', '0.00') }}"
                        style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.9rem;"
                    >
                </div>
            </div>

            <!-- Assign Staff -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                    Assign Delivery Staff / Driver
                </label>
                <select name="user_id" style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.9rem;">
                    <option value="">-- Assign Later (Pending) --</option>
                    @foreach($deliveryStaff as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ ucfirst(str_replace('_', ' ', $staff->role)) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Special Instructions -->
            <div style="margin-bottom: 1.75rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem;">
                    Special Notes / Instructions
                </label>
                <input
                    type="text"
                    name="notes"
                    placeholder="e.g. Call before delivery, handle cake with care"
                    value="{{ old('notes') }}"
                    style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 0.9rem;"
                >
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <a href="{{ url()->previous() ?: route('admin.deliveries.index') }}" class="deliveries-tab-btn" style="border: 1px solid #cbd5e1; background: #fff;">
                    Cancel
                </a>
                <button type="submit" class="btn-delivery-primary" id="btn_submit_delivery">
                    Confirm & Schedule Delivery
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function handleOrderSelection(select) {
        const option = select.options[select.selectedIndex];
        if (!option || !option.value) return;

        const custName = option.getAttribute('data-customer') || '';
        const phone = option.getAttribute('data-phone') || '';
        const address = option.getAttribute('data-address') || '';

        const nameInput = document.getElementById('recipient_name_field');
        const phoneInput = document.getElementById('recipient_phone_field');
        const addrInput = document.getElementById('delivery_address_field');

        if (nameInput) nameInput.value = custName;
        if (phoneInput) phoneInput.value = phone;
        if (addrInput) addrInput.value = address;
    }
</script>
@endsection
