@extends('layouts.app')

@section('title', $customer->name . ' - Purchase History & Loyalty')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/customers.css') }}">
@endpush

@section('content')
<!-- Back & Breadcrumb Bar -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <a href="{{ route('admin.customers.index') }}" class="btn-icon-action" style="width: 38px; height: 38px; border-radius: 10px;" title="Back to Customers">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0; color: var(--cust-text);">
                {{ $customer->name }}
            </h2>
            <div style="font-size: 0.825rem; color: var(--cust-muted); margin-top: 0.2rem;">
                Member since {{ $customer->created_at->format('M d, Y') }}
            </div>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <button type="button" class="btn-icon-action btn-edit-customer" style="width: auto; height: auto; padding: 0.55rem 1.15rem; border-radius: 9999px; gap: 0.5rem; font-weight: 700; font-size: 0.85rem;"
            data-id="{{ $customer->id }}"
            data-name="{{ $customer->name }}"
            data-phone="{{ $customer->phone }}"
            data-email="{{ $customer->email }}"
            data-address="{{ $customer->address }}"
            data-points="{{ $customer->loyalty_points }}"
            data-status="{{ $customer->status }}"
        >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
            <span data-lang-key="edit_customer">Edit Profile</span>
        </button>
    </div>
</div>

@php
    $initials = strtoupper(substr($customer->name, 0, 2));
    $tier = strtolower($customer->loyalty_tier ?? 'standard');
    $tierIcon = match($tier) {
        'vip' => '👑',
        'gold' => '⭐',
        'silver' => '🥈',
        default => '🥉',
    };

    // Loyalty Next Tier Progress
    $points = (int) $customer->loyalty_points;
    $nextTierTarget = 100;
    $nextTierName = 'Silver';
    if ($points >= 500) {
        $nextTierTarget = 500;
        $nextTierName = 'Max VIP';
        $progressPercent = 100;
    } elseif ($points >= 300) {
        $nextTierTarget = 500;
        $nextTierName = 'VIP (500 pts)';
        $progressPercent = min(100, round((($points - 300) / 200) * 100));
    } elseif ($points >= 100) {
        $nextTierTarget = 300;
        $nextTierName = 'Gold (300 pts)';
        $progressPercent = min(100, round((($points - 100) / 200) * 100));
    } else {
        $nextTierTarget = 100;
        $nextTierName = 'Silver (100 pts)';
        $progressPercent = min(100, round(($points / 100) * 100));
    }
@endphp

<!-- Detail Layout: Left Customer Profile / Right Purchase History -->
<div class="cust-detail-grid">
    <!-- LEFT: Profile & Loyalty Card -->
    <div class="cust-profile-card">
        <div class="cust-profile-avatar-large">{{ $initials }}</div>
        <h3 class="cust-profile-name">{{ $customer->name }}</h3>
        <div>
            <span class="tier-pill tier-{{ $tier }}" style="font-size: 0.85rem; padding: 0.35rem 0.85rem;">
                {{ $tierIcon }} {{ ucfirst($tier) }} Tier
            </span>
        </div>

        <!-- Loyalty Progress Bar -->
        <div class="loyalty-progress-container">
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700;">
                <span data-lang-key="loyalty_points">Current Balance:</span>
                <span style="color: #d97706;">{{ number_format($points) }} pts</span>
            </div>
            <div class="loyalty-progress-bar">
                <div class="loyalty-progress-fill" style="width: {{ $progressPercent }}%;"></div>
            </div>
            <div style="font-size: 0.725rem; color: var(--cust-muted); text-align: right;">
                Target: {{ $nextTierName }}
            </div>
        </div>

        <!-- Contact & Meta Information -->
        <div class="cust-meta-list">
            <div class="cust-meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <span><strong>Phone:</strong> {{ $customer->phone ?? 'Not provided' }}</span>
            </div>
            <div class="cust-meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                <span><strong>Email:</strong> {{ $customer->email ?? 'Not provided' }}</span>
            </div>
            <div class="cust-meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                <span><strong>Address:</strong> {{ $customer->address ?? 'No physical address' }}</span>
            </div>
            <div class="cust-meta-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span><strong>Status:</strong>
                    @if($customer->status === 'active')
                        <span style="color: #16a34a; font-weight: 800;">Active Member</span>
                    @else
                        <span style="color: #ef4444; font-weight: 800;">Inactive</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- RIGHT: Lifetime Stats & Detailed Purchase History -->
    <div class="cust-history-area">
        <!-- 4 Quick Stats Boxes -->
        <div class="cust-history-stats-grid">
            <div class="cust-history-stat-box">
                <div class="box-label" data-lang-key="total_spent">Total Lifetime Spend</div>
                <div class="box-value">${{ number_format($totalSpent, 2) }}</div>
            </div>
            <div class="cust-history-stat-box">
                <div class="box-label" data-lang-key="total_purchases">Total Orders Placed</div>
                <div class="box-value">{{ number_format($totalOrders) }}</div>
            </div>
            <div class="cust-history-stat-box">
                <div class="box-label">Avg. Order Value</div>
                <div class="box-value">${{ number_format($avgOrderValue, 2) }}</div>
            </div>
            <div class="cust-history-stat-box">
                <div class="box-label" data-lang-key="last_visit">Last Purchase</div>
                <div class="box-value" style="font-size: 1.05rem;">
                    {{ $lastVisit ? $lastVisit->format('M d, Y') : 'Never' }}
                </div>
            </div>
        </div>

        <!-- Purchase History Table Card -->
        <div class="customers-card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: var(--cust-text); display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span data-lang-key="purchase_history">Customer Purchase History</span>
                    <span style="font-size: 0.85rem; color: var(--cust-muted); font-weight: 600;">({{ $orders->total() }} records)</span>
                </h3>
            </div>

            <div style="overflow-x: auto;">
                <table class="customers-table">
                    <thead>
                        <tr>
                            <th>Order Code</th>
                            <th>Date & Time</th>
                            <th>Items Purchased</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th style="text-align: right;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $statusClass = strtolower($order->order_status ?? 'completed');
                                $statusBadge = match($statusClass) {
                                    'completed' => '<span style="background: rgba(22, 163, 74, 0.12); color: #16a34a; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem;">Completed</span>',
                                    'pending' => '<span style="background: rgba(239, 68, 68, 0.12); color: #ef4444; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem;">Pending</span>',
                                    default => '<span style="background: rgba(217, 119, 6, 0.12); color: #d97706; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem;">' . ucfirst($statusClass) . '</span>',
                                };

                                $methodPill = match(strtolower($order->payment_method ?? 'cash')) {
                                    'card' => '<span style="background: #f3f4f6; color: #1f2937; padding: 0.2rem 0.5rem; border-radius: 6px; font-weight: 700; font-size: 0.75rem;">💳 Card</span>',
                                    'qr_code' => '<span style="background: #fee2e2; color: #b91c1c; padding: 0.2rem 0.5rem; border-radius: 6px; font-weight: 700; font-size: 0.75rem;">📱 KHQR</span>',
                                    default => '<span style="background: #dcfce7; color: #15803d; padding: 0.2rem 0.5rem; border-radius: 6px; font-weight: 700; font-size: 0.75rem;">💵 Cash</span>',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight: 800; color: var(--cust-text);">{{ $order->order_number }}</div>
                                    @if($order->sale)
                                        <div style="font-size: 0.75rem; color: var(--cust-muted);">Sale #{{ $order->sale->id }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div style="font-size: 0.75rem; color: var(--cust-muted);">{{ $order->created_at->format('H:i A') }}</div>
                                </td>
                                <td>
                                    @if($order->items && $order->items->isNotEmpty())
                                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                            @foreach($order->items as $item)
                                                <div style="font-size: 0.825rem;">
                                                    <strong>{{ $item->quantity }}x</strong> {{ $item->product ? $item->product->name : 'Bakery Item' }}
                                                    <span style="color: var(--cust-muted); font-size: 0.75rem;">(${{ number_format($item->price, 2) }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color: var(--cust-muted); font-style: italic;">Standard Items</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $methodPill !!}
                                </td>
                                <td>
                                    {!! $statusBadge !!}
                                </td>
                                <td style="text-align: right;">
                                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--cust-text);">
                                        ${{ number_format($order->total, 2) }}
                                    </div>
                                    @if($order->discount > 0)
                                        <div style="font-size: 0.725rem; color: #16a34a;">-${{ number_format($order->discount, 2) }} disc</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: var(--cust-muted);">
                                    <div style="font-size: 2rem; margin-bottom: 0.35rem;">🛍️</div>
                                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--cust-text);">No Purchase History Yet</div>
                                    <p style="font-size: 0.8rem; margin-top: 0.2rem;">When this customer completes sales at the POS terminal, their itemized orders will appear here automatically.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div style="margin-top: 1.25rem;">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     EDIT CUSTOMER MODAL (In Detail View)
     ========================================================================== -->
<div class="cust-modal-backdrop" id="edit_customer_modal">
    <div class="cust-modal-card">
        <div class="cust-modal-header">
            <h3 data-lang-key="edit_customer">Edit Customer Profile</h3>
            <button type="button" class="btn-modal-close" id="btn_close_edit_customer">&times;</button>
        </div>
        <form id="form_edit_customer" action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="cust-modal-body">
                <div class="form-field-group">
                    <label for="edit_cust_name" data-lang-key="customer_name">Full Name *</label>
                    <input type="text" id="edit_cust_name" name="name" value="{{ $customer->name }}" required>
                </div>

                <div class="form-field-group">
                    <label for="edit_cust_phone" data-lang-key="phone">Phone Number</label>
                    <input type="text" id="edit_cust_phone" name="phone" value="{{ $customer->phone }}">
                </div>

                <div class="form-field-group">
                    <label for="edit_cust_email" data-lang-key="email">Email Address</label>
                    <input type="email" id="edit_cust_email" name="email" value="{{ $customer->email }}">
                </div>

                <div class="form-field-group">
                    <label for="edit_cust_address" data-lang-key="address">Address</label>
                    <textarea id="edit_cust_address" name="address" rows="2">{{ $customer->address }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-field-group">
                        <label for="edit_cust_points" data-lang-key="loyalty_points">Loyalty Points</label>
                        <input type="number" id="edit_cust_points" name="loyalty_points" min="0" value="{{ $customer->loyalty_points }}">
                    </div>

                    <div class="form-field-group">
                        <label for="edit_cust_status" data-lang-key="status">Status</label>
                        <select id="edit_cust_status" name="status" required>
                            <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="cust-modal-footer">
                <button type="button" class="btn-modal-cancel" id="btn_cancel_edit_customer" data-lang-key="cancel">Cancel</button>
                <button type="submit" class="btn-cust-primary" data-lang-key="save">Update Profile</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('edit_customer_modal');
    const btnOpenEdit = document.querySelector('.btn-edit-customer');
    const btnCloseEdit = document.getElementById('btn_close_edit_customer');
    const btnCancelEdit = document.getElementById('btn_cancel_edit_customer');

    if (btnOpenEdit) {
        btnOpenEdit.addEventListener('click', () => editModal.classList.add('is-active'));
    }
    if (btnCloseEdit) {
        btnCloseEdit.addEventListener('click', () => editModal.classList.remove('is-active'));
    }
    if (btnCancelEdit) {
        btnCancelEdit.addEventListener('click', () => editModal.classList.remove('is-active'));
    }

    editModal.addEventListener('click', function(e) {
        if (e.target === editModal) {
            editModal.classList.remove('is-active');
        }
    });
});
</script>
@endpush
@endsection
