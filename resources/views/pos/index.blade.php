@extends('layouts.app')

@section('title', 'POS Sales Terminal - Bakery Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
@endpush

@section('content')
<div class="pos-screen-layout">
    <!-- ======================================================================
         LEFT: CATALOG PANEL (Search, Categories, Product Cards)
         ====================================================================== -->
    <div class="pos-catalog-panel">
        <!-- Toolbar: Search & Category Pills -->
        <div class="pos-toolbar">
            <div class="pos-search-wrapper">
                <span class="pos-search-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"/>
                    </svg>
                </span>
                <input
                    type="text"
                    id="pos_search_input"
                    class="pos-search-input"
                    placeholder="Search bakery items by name, SKU, or code..."
                    autocomplete="off"
                    data-lang-key="search_products"
                >
            </div>

            <div class="pos-category-tabs" id="pos_category_tabs">
                <button type="button" class="pos-cat-pill active" data-category-id="all">
                    🥐 <span data-lang-key="all_categories">All Items</span>
                </button>
                @foreach($categories as $category)
                    <button type="button" class="pos-cat-pill" data-category-id="{{ $category->id }}">
                        {{ $category->icon ?? '🍰' }} <span>{{ $category->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Grid -->
        <div class="pos-grid-container" id="pos_product_grid">
            @forelse($products as $product)
                @php
                    $isOutOfStock = $product->stock <= 0;
                    $isLowStock = $product->stock > 0 && $product->stock <= 5;
                @endphp
                <div
                    class="pos-item-card {{ $isOutOfStock ? 'is-out-of-stock' : '' }}"
                    id="pos_prod_{{ $product->id }}"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ (float) $product->price }}"
                    data-stock="{{ (int) $product->stock }}"
                    data-category-id="{{ $product->category_id }}"
                    data-code="{{ strtolower($product->code ?? $product->sku ?? '') }}"
                >
                    <div class="pos-item-visual">
                        @if($product->image_emoji)
                            <span>{{ $product->image_emoji }}</span>
                        @elseif($product->image && file_exists(public_path('storage/' . $product->image)))
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 18px;">
                        @else
                            <span>🍞</span>
                        @endif
                    </div>

                    <div class="pos-item-name" title="{{ $product->name }}">{{ $product->name }}</div>
                    <div class="pos-item-price">{{ $currency }}{{ number_format($product->price, 2) }}</div>

                    <div class="pos-stock-badge {{ $isOutOfStock ? 'out-of-stock' : ($isLowStock ? 'low-stock' : 'in-stock') }}">
                        @if($isOutOfStock)
                            <span data-lang-key="out_of_stock">Out of Stock</span>
                        @elseif($isLowStock)
                            <span class="stock-text">Low stock: {{ $product->stock }}</span>
                        @else
                            <span class="stock-text">{{ $product->stock }} in stock</span>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
                    <p style="font-size: 1.1rem; font-weight: 700;">No active bakery products available.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ======================================================================
         RIGHT: CART & CHECKOUT PANEL
         ====================================================================== -->
    <div class="pos-cart-panel" id="pos_cart_panel">
        <!-- Mobile Drawer Close & Drag Bar -->
        <div class="pos-cart-mobile-drawer-top" id="pos_cart_mobile_drawer_top">
            <div class="pos-cart-drag-handle"></div>
            <div class="pos-cart-mobile-bar-title">
                <span data-lang-key="current_sale">Current Sale</span>
                <button type="button" class="btn-close-cart-drawer" id="btn_close_cart_drawer" aria-label="Close Cart">&times;</button>
            </div>
        </div>

        <div class="pos-cart-header">
            <div class="pos-cart-title">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span data-lang-key="current_sale">Current Sale</span>
                <span class="pos-cart-count-pill" id="pos_cart_count_pill">0</span>
            </div>
            <button type="button" class="btn-clear-cart" id="btn_clear_cart" title="Clear All Items" data-lang-key="clear_cart">
                Clear Cart
            </button>
        </div>

        <!-- Customer Selection & Loyalty Area -->
        <div class="pos-cart-form-group" id="pos_customer_section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                <label class="pos-cart-label" for="pos_customer_search" style="margin-bottom: 0;" data-lang-key="customer">Customer</label>
                <button type="button" class="btn-quick-add-cust" id="btn_open_quick_cust_modal" title="Quick Register Customer" style="background: none; border: none; font-size: 0.775rem; font-weight: 800; color: #563020; cursor: pointer; display: flex; align-items: center; gap: 3px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span data-lang-key="quick_add_customer">+ New</span>
                </button>
            </div>

            <!-- Search Box (Active when no customer selected) -->
            <div class="pos-cust-search-container" id="pos_cust_search_container" style="position: relative;">
                <input
                    type="text"
                    id="pos_customer_search"
                    class="pos-cart-input"
                    placeholder="Search name / phone (or Walk-in)..."
                    autocomplete="off"
                    data-lang-key="search_customer"
                >
                <!-- Live Search Dropdown -->
                <div class="pos-cust-dropdown" id="pos_cust_dropdown" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: var(--bg-card, #ffffff); border: 1px solid var(--border-color, #e2e8f0); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); max-height: 220px; overflow-y: auto; z-index: 1000; padding: 4px;">
                </div>
            </div>

            <!-- Selected Customer Card (Active when customer is selected) -->
            <div class="pos-selected-cust-card" id="pos_selected_cust_card" style="display: none; background: rgba(86, 48, 32, 0.06); border: 1px solid rgba(86, 48, 32, 0.18); border-radius: 12px; padding: 0.55rem 0.85rem; align-items: center; justify-content: space-between; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #563020; color: #ffffff; font-weight: 800; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" id="pos_cust_initials">
                        CU
                    </div>
                    <div style="min-width: 0;">
                        <div style="font-weight: 800; font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-title, #29150d);" id="pos_cust_selected_name">
                            Customer Name
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-muted, #71717a);">
                            <span id="pos_cust_selected_phone">000-000</span>
                            <span class="pos-cust-tier-badge" id="pos_cust_selected_tier" style="font-weight: 800; color: #d97706; background: rgba(217, 119, 6, 0.12); padding: 1px 6px; border-radius: 9999px;">
                                ⭐ 0 pts
                            </span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-remove-cust" id="btn_remove_selected_cust" title="Remove Customer (Revert to Walk-in)" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;" data-lang-key="remove_customer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <!-- Hidden Inputs for Form Reading & Backward Compatibility -->
            <input type="hidden" id="pos_customer_id" value="">
            <input type="hidden" id="pos_customer_name" value="Walk-in Customer">
        </div>

        <!-- Cart Items Scroll List -->
        <div class="pos-cart-items-container" id="pos_cart_items_container">
            <div class="pos-cart-empty-state" id="pos_cart_empty_state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.25rem;" data-lang-key="cart_empty">Your cart is empty</div>
                <div style="font-size: 0.8rem;" data-lang-key="cart_empty_sub">Click products on the left to add items</div>
            </div>
        </div>

        <!-- Payment Method Toggle Selector -->
        <div class="pos-cart-form-group">
            <label class="pos-cart-label" data-lang-key="payment_method">Payment Method</label>
            <div class="pos-payment-selector" id="pos_payment_selector">
                <button type="button" class="pos-pay-btn active" data-method="cash">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                        <circle cx="12" cy="12" r="2"/>
                        <path d="M6 12h.01M18 12h.01"/>
                    </svg>
                    <span data-lang-key="cash">Cash</span>
                </button>
                <button type="button" class="pos-pay-btn" data-method="qr_code">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span data-lang-key="qr_code">KHQR</span>
                </button>
                <button type="button" class="pos-pay-btn" data-method="card">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                    <span data-lang-key="card">Card</span>
                </button>
            </div>
        </div>

        <!-- Totals Breakdown -->
        <div class="pos-totals-table">
            <div class="pos-summary-row">
                <span data-lang-key="subtotal">Subtotal</span>
                <span id="pos_subtotal_display">{{ $currency }}0.00</span>
            </div>
            <div class="pos-summary-row" style="align-items: center;">
                <span data-lang-key="discount_label">Discount</span>
                <div style="display: flex; align-items: center; gap: 4px;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $currency }}</span>
                    <input
                        type="number"
                        id="pos_discount_input"
                        step="0.1"
                        min="0"
                        value="0.00"
                        class="pos-cart-input"
                        style="width: 76px; height: 28px; padding: 2px 6px; text-align: right; font-size: 0.85rem;"
                    >
                </div>
            </div>
            <div class="pos-summary-row">
                <span><span data-lang-key="tax_label">Tax</span> ({{ $taxPercentage }}%)</span>
                <span id="pos_tax_display">{{ $currency }}0.00</span>
            </div>
            <div class="pos-summary-row grand-total">
                <span data-lang-key="grand_total">Grand Total</span>
                <span class="pos-grand-amount" id="pos_grand_total_display">{{ $currency }}0.00</span>
            </div>
        </div>

        <!-- Complete Sale Button -->
        <button type="button" class="btn-complete-sale" id="btn_complete_sale" disabled>
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span id="btn_complete_sale_text" data-lang-key="complete_sale">Complete Sale</span>
    </div>
</div>

<!-- ======================================================================
     MOBILE STICKY BOTTOM CART TRIGGER BAR (<= 767px)
     ====================================================================== -->
<div class="pos-mobile-cart-bar" id="pos_mobile_cart_bar">
    <div class="pos-mobile-cart-summary">
        <div class="pos-mobile-cart-count-badge">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span><strong id="pos_mobile_cart_count">0</strong> items</span>
        </div>
        <div class="pos-mobile-cart-total-val" id="pos_mobile_cart_total">{{ $currency }}0.00</div>
    </div>
    <button type="button" class="btn-pos-mobile-view-cart" id="btn_pos_mobile_view_cart">
        <span data-lang-key="view_cart">View Cart</span>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
</div>

<!-- Mobile Cart Bottom Sheet Backdrop -->
<div class="pos-cart-backdrop" id="pos_cart_backdrop"></div>

<!-- ==========================================================================
     RECEIPT CONFIRMATION MODAL & THERMAL SLIP
     ========================================================================== -->
<div class="pos-modal-backdrop" id="pos_receipt_modal">
    <div class="pos-receipt-modal">
        <!-- Printable Area -->
        <div id="pos_receipt_print_area">
            <div class="pos-thermal-slip">
                <div class="slip-center">
                    <div class="slip-title" id="slip_bakery_name">{{ $bakeryName }}</div>
                    <div class="slip-meta" id="slip_bakery_address">{{ $bakeryAddress }}</div>
                    <div class="slip-meta" id="slip_bakery_phone">Tel: {{ $bakeryPhone }}</div>
                    <div class="slip-meta" style="margin-top: 4px; font-style: italic;" id="slip_receipt_header">{{ $receiptHeader }}</div>
                </div>

                <div class="slip-divider"></div>

                <div class="slip-meta" style="display: flex; justify-content: space-between;">
                    <span>Order: <strong id="slip_order_num">ORD-0000</strong></span>
                    <span id="slip_payment_ref">PAY-0000</span>
                </div>
                <div class="slip-meta" style="display: flex; justify-content: space-between;">
                    <span>Date: <span id="slip_datetime">2026-09-06 12:00</span></span>
                    <span>Cashier: <strong id="slip_cashier">Cashier</strong></span>
                </div>
                <div class="slip-meta" id="slip_customer_row">
                    <span>Customer: <span id="slip_customer">Walk-in</span></span>
                </div>
                <div class="slip-meta" id="slip_phone_row" style="display: none;">
                    <span>Phone: <span id="slip_customer_phone"></span></span>
                </div>
                <div class="slip-meta" id="slip_loyalty_row" style="display: none;">
                    <span>Loyalty: <strong id="slip_loyalty_info" style="color: #16a34a;"></strong></span>
                </div>
                <div class="slip-meta">
                    <span>Payment: <strong id="slip_method">CASH</strong></span>
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
                    <tbody id="slip_items_body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>

                <div class="slip-divider"></div>

                <div class="slip-totals-row">
                    <span>Subtotal:</span>
                    <span id="slip_subtotal">{{ $currency }}0.00</span>
                </div>
                <div class="slip-totals-row">
                    <span>Discount:</span>
                    <span id="slip_discount">{{ $currency }}0.00</span>
                </div>
                <div class="slip-totals-row">
                    <span>Tax (<span id="slip_tax_percent">{{ $taxPercentage }}</span>%):</span>
                    <span id="slip_tax">{{ $currency }}0.00</span>
                </div>
                <div class="slip-totals-row grand">
                    <span>TOTAL:</span>
                    <span id="slip_grand_total">{{ $currency }}0.00</span>
                </div>

                <div class="slip-divider"></div>

                <div class="slip-footer" id="slip_receipt_footer">
                    {{ $receiptFooter }}
                </div>
                <div class="slip-center slip-meta" style="margin-top: 8px; font-size: 0.7rem;">
                    *** Thank You & Come Again! ***
                </div>
            </div>
        </div>

        <!-- Modal Actions -->
        <div class="pos-modal-actions">
            <button type="button" class="btn-receipt-print" id="btn_receipt_print">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                <span data-lang-key="print_receipt">Print Receipt</span>
            </button>
            <button type="button" class="btn-receipt-close" id="btn_receipt_close" data-lang-key="new_sale">
                New Sale
            </button>
        </div>
    </div>
</div>

<!-- ==========================================================================
     QUICK REGISTER CUSTOMER MODAL (In POS Terminal)
     ========================================================================== -->
<div class="pos-modal-backdrop" id="pos_quick_customer_modal">
    <div class="pos-receipt-modal" style="max-width: 400px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--text-title, #29150d); display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                <span data-lang-key="quick_add_customer">Quick Register Customer</span>
            </h3>
            <button type="button" class="btn-modal-close" id="btn_close_quick_cust_modal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="form_quick_customer">
            <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="pos-cart-label" for="quick_cust_name" data-lang-key="customer_name">Full Name *</label>
                    <input type="text" id="quick_cust_name" class="pos-cart-input" required placeholder="e.g. Sokha Chan">
                </div>
                <div>
                    <label class="pos-cart-label" for="quick_cust_phone" data-lang-key="phone">Phone Number</label>
                    <input type="text" id="quick_cust_phone" class="pos-cart-input" placeholder="e.g. 098 765 432">
                </div>
                <div>
                    <label class="pos-cart-label" for="quick_cust_email" data-lang-key="email">Email Address (Optional)</label>
                    <input type="email" id="quick_cust_email" class="pos-cart-input" placeholder="e.g. customer@example.com">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn-clear-cart" id="btn_cancel_quick_cust" style="padding: 0.5rem 1rem;" data-lang-key="cancel">Cancel</button>
                <button type="submit" class="btn-complete-sale" style="width: auto; padding: 0.55rem 1.35rem; font-size: 0.875rem;" id="btn_save_quick_cust">
                    <span data-lang-key="save">Register & Select</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    // State
    const TAX_PERCENTAGE = {{ (float) $taxPercentage }};
    const CURRENCY = "{{ $currency }}";
    const CHECKOUT_URL = "{{ route('pos.checkout') }}";
    const CUSTOMER_SEARCH_URL = "{{ route('pos.customers.search') }}";
    const CUSTOMER_QUICK_STORE_URL = "{{ route('pos.customers.quickStore') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    let cart = []; // Array of { id, name, price, stock, qty }
    let selectedPaymentMethod = 'cash';
    let selectedCustomer = null;

    // DOM Elements
    const searchInput = document.getElementById('pos_search_input');
    const categoryTabs = document.getElementById('pos_category_tabs');
    const productGrid = document.getElementById('pos_product_grid');
    const cartContainer = document.getElementById('pos_cart_items_container');
    const cartEmptyState = document.getElementById('pos_cart_empty_state');
    const cartCountPill = document.getElementById('pos_cart_count_pill');
    const clearCartBtn = document.getElementById('btn_clear_cart');
    const customerInput = document.getElementById('pos_customer_name');
    const customerIdInput = document.getElementById('pos_customer_id');
    const customerSearchInput = document.getElementById('pos_customer_search');
    const customerDropdown = document.getElementById('pos_cust_dropdown');
    const customerSearchContainer = document.getElementById('pos_cust_search_container');
    const selectedCustCard = document.getElementById('pos_selected_cust_card');
    const custInitials = document.getElementById('pos_cust_initials');
    const custSelectedName = document.getElementById('pos_cust_selected_name');
    const custSelectedPhone = document.getElementById('pos_cust_selected_phone');
    const custSelectedTier = document.getElementById('pos_cust_selected_tier');
    const btnRemoveCust = document.getElementById('btn_remove_selected_cust');

    // Quick Add Modal Elements
    const quickCustModal = document.getElementById('pos_quick_customer_modal');
    const btnOpenQuickCust = document.getElementById('btn_open_quick_cust_modal');
    const btnCloseQuickCust = document.getElementById('btn_close_quick_cust_modal');
    const btnCancelQuickCust = document.getElementById('btn_cancel_quick_cust');
    const formQuickCustomer = document.getElementById('form_quick_customer');
    const quickCustName = document.getElementById('quick_cust_name');
    const quickCustPhone = document.getElementById('quick_cust_phone');
    const quickCustEmail = document.getElementById('quick_cust_email');

    const paymentSelector = document.getElementById('pos_payment_selector');
    const discountInput = document.getElementById('pos_discount_input');
    const subtotalDisplay = document.getElementById('pos_subtotal_display');
    const taxDisplay = document.getElementById('pos_tax_display');
    const grandTotalDisplay = document.getElementById('pos_grand_total_display');
    const completeSaleBtn = document.getElementById('btn_complete_sale');
    const completeSaleText = document.getElementById('btn_complete_sale_text');

    // Receipt Modal Elements
    const receiptModal = document.getElementById('pos_receipt_modal');
    const btnReceiptPrint = document.getElementById('btn_receipt_print');
    const btnReceiptClose = document.getElementById('btn_receipt_close');

    // Thermal Slip Elements
    const slipOrderNum = document.getElementById('slip_order_num');
    const slipPaymentRef = document.getElementById('slip_payment_ref');
    const slipDatetime = document.getElementById('slip_datetime');
    const slipCashier = document.getElementById('slip_cashier');
    const slipCustomer = document.getElementById('slip_customer');
    const slipPhoneRow = document.getElementById('slip_phone_row');
    const slipCustomerPhone = document.getElementById('slip_customer_phone');
    const slipLoyaltyRow = document.getElementById('slip_loyalty_row');
    const slipLoyaltyInfo = document.getElementById('slip_loyalty_info');
    const slipMethod = document.getElementById('slip_method');
    const slipItemsBody = document.getElementById('slip_items_body');
    const slipSubtotal = document.getElementById('slip_subtotal');
    const slipDiscount = document.getElementById('slip_discount');
    const slipTax = document.getElementById('slip_tax');
    const slipGrandTotal = document.getElementById('slip_grand_total');

    // -------------------------------------------------------------------------
    // 1. Live Product Search & Category Filtering
    // -------------------------------------------------------------------------
    let activeCategory = 'all';
    let searchQuery = '';

    function filterProducts() {
        const query = searchQuery.toLowerCase().trim();
        const cards = productGrid.querySelectorAll('.pos-item-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = (card.getAttribute('data-name') || '').toLowerCase();
            const code = (card.getAttribute('data-code') || '').toLowerCase();
            const catId = card.getAttribute('data-category-id') || '';

            const matchesSearch = !query || name.includes(query) || code.includes(query);
            const matchesCategory = (activeCategory === 'all') || (catId === activeCategory);

            if (matchesSearch && matchesCategory) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value;
            filterProducts();
        });
    }

    if (categoryTabs) {
        categoryTabs.addEventListener('click', function(e) {
            const pill = e.target.closest('.pos-cat-pill');
            if (!pill) return;

            categoryTabs.querySelectorAll('.pos-cat-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');

            activeCategory = pill.getAttribute('data-category-id');
            filterProducts();
        });
    }

    // -------------------------------------------------------------------------
    // 2. Product Click -> Add to Cart
    // -------------------------------------------------------------------------
    productGrid.addEventListener('click', function(e) {
        const card = e.target.closest('.pos-item-card');
        if (!card) return;

        const id = parseInt(card.getAttribute('data-id'), 10);
        const name = card.getAttribute('data-name');
        const price = parseFloat(card.getAttribute('data-price'));
        const stock = parseInt(card.getAttribute('data-stock'), 10);

        if (stock <= 0) {
            showToast(`"${name}" is out of stock!`, 'warning');
            return;
        }

        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.qty < stock) {
                existing.qty++;
            } else {
                showToast(`Cannot add more. Available stock for "${name}" is ${stock}.`, 'warning');
                return;
            }
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                stock: stock,
                qty: 1
            });
        }

        renderCart();
    });

    // -------------------------------------------------------------------------
    // 3. Cart Render & Calculations
    // -------------------------------------------------------------------------
    const mobileCartCount = document.getElementById('pos_mobile_cart_count');
    const mobileCartTotal = document.getElementById('pos_mobile_cart_total');
    const posCartPanel = document.querySelector('.pos-cart-panel');
    const btnViewCartMobile = document.getElementById('btn_pos_mobile_view_cart');
    const btnCloseCartDrawer = document.getElementById('btn_close_cart_drawer');
    const posCartBackdrop = document.getElementById('pos_cart_backdrop');

    function openMobileCartDrawer() {
        if (posCartPanel) posCartPanel.classList.add('mobile-drawer-open', 'mobile-open');
        if (posCartBackdrop) posCartBackdrop.classList.add('is-active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileCartDrawer() {
        if (posCartPanel) posCartPanel.classList.remove('mobile-drawer-open', 'mobile-open');
        if (posCartBackdrop) posCartBackdrop.classList.remove('is-active');
        document.body.style.overflow = '';
    }

    const mobileCartBar = document.getElementById('pos_mobile_cart_bar');
    if (mobileCartBar) {
        mobileCartBar.addEventListener('click', openMobileCartDrawer);
    }
    if (btnViewCartMobile) {
        btnViewCartMobile.addEventListener('click', function(e) {
            e.stopPropagation();
            openMobileCartDrawer();
        });
    }
    if (btnCloseCartDrawer) {
        btnCloseCartDrawer.addEventListener('click', function(e) {
            e.stopPropagation();
            closeMobileCartDrawer();
        });
    }
    if (posCartBackdrop) {
        posCartBackdrop.addEventListener('click', closeMobileCartDrawer);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && posCartPanel && posCartPanel.classList.contains('mobile-drawer-open')) {
            closeMobileCartDrawer();
        }
    });

    function renderCart() {
        // Clear items container (except empty state placeholder if needed)
        cartContainer.innerHTML = '';

        if (cart.length === 0) {
            cartContainer.appendChild(cartEmptyState);
            cartEmptyState.style.display = 'flex';
            cartCountPill.textContent = '0';
            if (mobileCartCount) mobileCartCount.textContent = '0';
            if (mobileCartTotal) mobileCartTotal.textContent = `${CURRENCY}0.00`;
            subtotalDisplay.textContent = `${CURRENCY}0.00`;
            taxDisplay.textContent = `${CURRENCY}0.00`;
            grandTotalDisplay.textContent = `${CURRENCY}0.00`;
            completeSaleBtn.disabled = true;
            return;
        }

        cartEmptyState.style.display = 'none';

        let subtotal = 0;
        let totalItemsCount = 0;

        cart.forEach((item, index) => {
            const lineTotal = item.price * item.qty;
            subtotal += lineTotal;
            totalItemsCount += item.qty;

            const itemEl = document.createElement('div');
            itemEl.className = 'pos-cart-item';
            itemEl.innerHTML = `
                <div class="pos-item-details">
                    <div class="pos-item-row-title" title="${item.name}">${item.name}</div>
                    <div class="pos-item-row-unit">${CURRENCY}${item.price.toFixed(2)} each</div>
                </div>
                <div class="pos-qty-capsule">
                    <button type="button" class="btn-qty btn-minus" data-index="${index}">-</button>
                    <span class="pos-qty-value">${item.qty}</span>
                    <button type="button" class="btn-qty btn-plus" data-index="${index}">+</button>
                </div>
                <div class="pos-item-row-total">${CURRENCY}${lineTotal.toFixed(2)}</div>
                <button type="button" class="btn-item-remove" data-index="${index}" title="Remove item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            `;

            cartContainer.appendChild(itemEl);
        });

        cartCountPill.textContent = totalItemsCount.toString();

        // Calculations
        const rawDiscount = parseFloat(discountInput.value) || 0;
        const discount = Math.min(Math.max(0, rawDiscount), subtotal);
        const taxable = Math.max(0, subtotal - discount);
        const tax = (taxable * (TAX_PERCENTAGE / 100));
        const grandTotal = taxable + tax;

        subtotalDisplay.textContent = `${CURRENCY}${subtotal.toFixed(2)}`;
        taxDisplay.textContent = `${CURRENCY}${tax.toFixed(2)}`;
        grandTotalDisplay.textContent = `${CURRENCY}${grandTotal.toFixed(2)}`;

        if (mobileCartCount) mobileCartCount.textContent = totalItemsCount.toString();
        if (mobileCartTotal) mobileCartTotal.textContent = `${CURRENCY}${grandTotal.toFixed(2)}`;

        completeSaleBtn.disabled = false;
        completeSaleText.textContent = `Charge ${CURRENCY}${grandTotal.toFixed(2)}`;
    }

    // Cart Items Interaction (+, -, remove)
    cartContainer.addEventListener('click', function(e) {
        const plusBtn = e.target.closest('.btn-plus');
        const minusBtn = e.target.closest('.btn-minus');
        const removeBtn = e.target.closest('.btn-item-remove');

        if (plusBtn) {
            const index = parseInt(plusBtn.getAttribute('data-index'), 10);
            const item = cart[index];
            if (item.qty < item.stock) {
                item.qty++;
                renderCart();
            } else {
                showToast(`Max available stock for "${item.name}" is ${item.stock}.`, 'warning');
            }
            return;
        }

        if (minusBtn) {
            const index = parseInt(minusBtn.getAttribute('data-index'), 10);
            const item = cart[index];
            if (item.qty > 1) {
                item.qty--;
            } else {
                cart.splice(index, 1);
            }
            renderCart();
            return;
        }

        if (removeBtn) {
            const index = parseInt(removeBtn.getAttribute('data-index'), 10);
            cart.splice(index, 1);
            renderCart();
            return;
        }
    });

    // Discount change listener
    discountInput.addEventListener('input', function() {
        renderCart();
    });

    // Clear Cart button
    clearCartBtn.addEventListener('click', function() {
        if (cart.length === 0) return;
        if (confirm('Are you sure you want to clear the current cart?')) {
            cart = [];
            discountInput.value = '0.00';
            renderCart();
        }
    });

    // -------------------------------------------------------------------------
    // 4. Payment Method Selection
    // -------------------------------------------------------------------------
    paymentSelector.addEventListener('click', function(e) {
        const btn = e.target.closest('.pos-pay-btn');
        if (!btn) return;

        paymentSelector.querySelectorAll('.pos-pay-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedPaymentMethod = btn.getAttribute('data-method');
    });

    // -------------------------------------------------------------------------
    // 4.1 Customer Search, Selection & Quick Registration
    // -------------------------------------------------------------------------
    function selectCustomer(cust) {
        selectedCustomer = cust;
        customerIdInput.value = cust.id;
        customerInput.value = cust.name;

        const initials = (cust.name || 'CU').trim().substring(0, 2).toUpperCase();
        custInitials.textContent = initials;
        custSelectedName.textContent = cust.name;
        custSelectedPhone.textContent = cust.phone || 'No phone number';
        custSelectedTier.textContent = `⭐ ${cust.loyalty_points || 0} pts (${cust.loyalty_tier || 'Standard'})`;

        customerSearchContainer.style.display = 'none';
        selectedCustCard.style.display = 'flex';
        customerDropdown.style.display = 'none';
        customerDropdown.innerHTML = '';
    }

    function clearCustomer() {
        selectedCustomer = null;
        customerIdInput.value = '';
        customerInput.value = 'Walk-in Customer';
        customerSearchInput.value = '';

        selectedCustCard.style.display = 'none';
        customerSearchContainer.style.display = 'block';
        customerDropdown.style.display = 'none';
        customerDropdown.innerHTML = '';
    }

    let searchCustTimeout = null;
    customerSearchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        clearTimeout(searchCustTimeout);

        if (query.length === 0) {
            customerDropdown.style.display = 'none';
            customerDropdown.innerHTML = '';
            customerInput.value = 'Walk-in Customer';
            return;
        }

        // If user typed a manual customer name, set it
        customerInput.value = query;

        searchCustTimeout = setTimeout(async () => {
            try {
                const res = await fetch(`${CUSTOMER_SEARCH_URL}?q=${encodeURIComponent(query)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                customerDropdown.innerHTML = '';
                if (data.customers && data.customers.length > 0) {
                    data.customers.forEach(cust => {
                        const item = document.createElement('div');
                        item.className = 'pos-cust-dropdown-item';
                        item.style.cssText = 'padding: 0.65rem 0.85rem; border-radius: 8px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color, #f1f5f9); transition: background 0.15s ease;';
                        item.innerHTML = `
                            <div>
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-title, #29150d);">${cust.name}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted, #71717a);">${cust.phone || cust.email || 'No phone'}</div>
                            </div>
                            <span style="font-size: 0.75rem; font-weight: 800; color: #d97706; background: rgba(217, 119, 6, 0.1); padding: 2px 7px; border-radius: 9999px;">
                                ⭐ ${cust.loyalty_points} pts
                            </span>
                        `;
                        item.addEventListener('mouseenter', () => item.style.background = 'rgba(86, 48, 32, 0.06)');
                        item.addEventListener('mouseleave', () => item.style.background = 'transparent');
                        item.addEventListener('click', () => selectCustomer(cust));
                        customerDropdown.appendChild(item);
                    });
                    customerDropdown.style.display = 'block';
                } else {
                    customerDropdown.innerHTML = `
                        <div style="padding: 0.75rem; text-align: center; color: var(--text-muted, #71717a); font-size: 0.8rem;">
                            No customers found. Click <strong>+ New</strong> to register.
                        </div>
                    `;
                    customerDropdown.style.display = 'block';
                }
            } catch (err) {
                console.error('Customer search error:', err);
            }
        }, 200);
    });

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        if (!customerSearchContainer.contains(e.target)) {
            customerDropdown.style.display = 'none';
        }
    });

    // Remove customer
    btnRemoveCust.addEventListener('click', function() {
        clearCustomer();
    });

    // Quick Add Customer modal handlers
    if (btnOpenQuickCust) {
        btnOpenQuickCust.addEventListener('click', () => quickCustModal.classList.add('is-active'));
    }
    if (btnCloseQuickCust) {
        btnCloseQuickCust.addEventListener('click', () => quickCustModal.classList.remove('is-active'));
    }
    if (btnCancelQuickCust) {
        btnCancelQuickCust.addEventListener('click', () => quickCustModal.classList.remove('is-active'));
    }

    formQuickCustomer.addEventListener('submit', async function(e) {
        e.preventDefault();
        const payload = {
            name: quickCustName.value.trim(),
            phone: quickCustPhone.value.trim() || null,
            email: quickCustEmail.value.trim() || null
        };

        try {
            const res = await fetch(CUSTOMER_QUICK_STORE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to register customer');
            }

            selectCustomer(data.customer);
            quickCustModal.classList.remove('is-active');
            formQuickCustomer.reset();
            showToast(`Customer "${data.customer.name}" registered & selected!`, 'success');
        } catch (err) {
            showToast(err.message, 'error');
        }
    });

    // -------------------------------------------------------------------------
    // 5. Checkout / Process Sale
    // -------------------------------------------------------------------------
    completeSaleBtn.addEventListener('click', async function() {
        if (cart.length === 0) return;

        const customerName = selectedCustomer ? selectedCustomer.name : (customerInput.value.trim() || 'Walk-in Customer');
        const customerId = selectedCustomer ? selectedCustomer.id : (customerIdInput.value ? parseInt(customerIdInput.value, 10) : null);
        const discountVal = parseFloat(discountInput.value) || 0;

        const payload = {
            customer_id: customerId,
            customer_name: customerName,
            payment_method: selectedPaymentMethod,
            discount: discountVal,
            items: cart.map(item => ({
                product_id: item.id,
                quantity: item.qty
            }))
        };

        // Loading state
        completeSaleBtn.disabled = true;
        const originalText = completeSaleText.textContent;
        completeSaleText.textContent = 'Processing Sale...';

        try {
            const response = await fetch(CHECKOUT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Checkout failed. Please try again.');
            }

            // Success!
            showToast(data.message || 'Sale completed successfully!', 'success');

            // 1. Update Product Card Stocks on the DOM
            if (data.receipt && data.receipt.items) {
                data.receipt.items.forEach(soldItem => {
                    const card = productGrid.querySelector(`.pos-item-card[data-name="${soldItem.name}"]`);
                    if (card) {
                        const newStock = soldItem.remaining_stock;
                        card.setAttribute('data-stock', newStock);

                        const badge = card.querySelector('.pos-stock-badge');
                        if (badge) {
                            if (newStock <= 0) {
                                card.classList.add('is-out-of-stock');
                                badge.className = 'pos-stock-badge out-of-stock';
                                badge.innerHTML = '<span data-lang-key="out_of_stock">Out of Stock</span>';
                            } else if (newStock <= 5) {
                                badge.className = 'pos-stock-badge low-stock';
                                badge.innerHTML = `<span class="stock-text">Low stock: ${newStock}</span>`;
                            } else {
                                badge.className = 'pos-stock-badge in-stock';
                                badge.innerHTML = `<span class="stock-text">${newStock} in stock</span>`;
                            }
                        }
                    }
                });
            }

            // 2. Populate and Open Receipt Modal
            closeMobileCartDrawer();
            populateReceiptModal(data.receipt);
            receiptModal.classList.add('is-active');

            // 3. Reset Cart & Customer
            cart = [];
            discountInput.value = '0.00';
            clearCustomer();
            renderCart();

        } catch (error) {
            console.error('POS Checkout Error:', error);
            showToast(error.message || 'An unexpected error occurred.', 'error');
            completeSaleBtn.disabled = false;
            completeSaleText.textContent = originalText;
        }
    });

    // -------------------------------------------------------------------------
    // 6. Receipt Modal & Printing
    // -------------------------------------------------------------------------
    function populateReceiptModal(receipt) {
        slipOrderNum.textContent = receipt.order_number;
        slipPaymentRef.textContent = receipt.payment_ref;
        slipDatetime.textContent = receipt.date_time;
        slipCashier.textContent = receipt.cashier_name;
        slipCustomer.textContent = receipt.customer_name;
        slipMethod.textContent = receipt.payment_method;

        // Populate customer phone & loyalty details if customer present
        if (receipt.customer_phone) {
            slipPhoneRow.style.display = 'block';
            slipCustomerPhone.textContent = receipt.customer_phone;
        } else {
            slipPhoneRow.style.display = 'none';
        }

        if (receipt.customer_id && receipt.points_earned !== undefined) {
            slipLoyaltyRow.style.display = 'block';
            slipLoyaltyInfo.textContent = `+${receipt.points_earned} pts (Bal: ${receipt.loyalty_balance} pts, ${receipt.loyalty_tier})`;
        } else {
            slipLoyaltyRow.style.display = 'none';
        }

        slipSubtotal.textContent = `${receipt.currency}${receipt.subtotal}`;
        slipDiscount.textContent = `${receipt.currency}${receipt.discount}`;
        slipTax.textContent = `${receipt.currency}${receipt.tax}`;
        slipGrandTotal.textContent = `${receipt.currency}${receipt.grand_total}`;

        // Populate Items
        slipItemsBody.innerHTML = '';
        receipt.items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.name}</td>
                <td class="align-right">${item.quantity} x ${receipt.currency}${parseFloat(item.price).toFixed(2)}</td>
                <td class="align-right">${receipt.currency}${parseFloat(item.subtotal).toFixed(2)}</td>
            `;
            slipItemsBody.appendChild(tr);
        });
    }

    // Print Receipt button
    btnReceiptPrint.addEventListener('click', function() {
        window.print();
    });

    // New Sale / Close Modal button
    btnReceiptClose.addEventListener('click', function() {
        receiptModal.classList.remove('is-active');
        clearCustomer();
    });

    // Close on backdrop click
    receiptModal.addEventListener('click', function(e) {
        if (e.target === receiptModal) {
            receiptModal.classList.remove('is-active');
            clearCustomer();
        }
    });

    // -------------------------------------------------------------------------
    // 7. Toast Notification Utility
    // -------------------------------------------------------------------------
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `pos-toast pos-toast-${type}`;

        let bg = '#1e293b';
        if (type === 'success') bg = '#16a34a';
        if (type === 'warning') bg = '#d97706';
        if (type === 'error') bg = '#dc2626';

        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: ${bg};
            color: #ffffff;
            padding: 0.85rem 1.35rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            z-index: 100000;
            animation: slideInToast 0.3s ease;
            max-width: 380px;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3200);
    }

    // Initialize
    renderCart();

})();
</script>
@endpush
