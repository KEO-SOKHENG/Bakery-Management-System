@extends('layouts.app')

@section('title', 'Create Order - Bakery System')
@section('header_title', 'Create Customer / Custom Cake Order')
@section('header_subtitle', 'Record custom cakes, wholesale pre-orders, and in-store bookings')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/orders.css') }}">
@endpush

@section('content')
<div class="orders-container">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <a href="{{ route('admin.orders') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 50px; padding: 0.55rem 1.25rem; font-weight: 700; text-decoration: none; color: #563020; background: #faf6f0; border: 1px solid rgba(238, 233, 224, 0.9);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Back to Orders</span>
        </a>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 1rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem;">
            <div style="font-weight: 800; margin-bottom: 0.35rem;">Please correct the following errors:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.85rem;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.orders.store') }}" method="POST" id="create_order_form">
        @csrf

        <div class="order-form-grid">
            <!-- LEFT COLUMN: Customer, Type, Items -->
            <div>
                <!-- Customer Details Card -->
                <div class="form-card" style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #29150d; margin-top: 0; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Customer Information</span>
                    </h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="customer_id">Select Existing Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control-select">
                                <option value="">-- Walk-in / New Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" data-name="{{ $c->name }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }} [{{ ucfirst($c->loyalty_tier) }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="customer_name">Customer Name</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control-input"
                                   value="{{ old('customer_name', 'Walk-in Customer') }}" required>
                        </div>
                    </div>
                </div>

                <!-- Custom Cake / Order Type Card -->
                <div class="form-card" style="margin-bottom: 1.5rem; border-left: 4px solid #f472b6;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="font-size: 1.05rem; font-weight: 800; color: #29150d; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.2rem;">🎂</span>
                            <span>Custom Cake & Scheduling</span>
                        </h3>
                        <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 700; font-size: 0.85rem; color: #be185d;">
                            <input type="checkbox" name="is_custom" id="is_custom" value="1" {{ old('is_custom') ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #be185d;">
                            <span>Mark as Custom Cake Order</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="pickup_date">Pickup / Delivery Schedule</label>
                        <input type="datetime-local" name="pickup_date" id="pickup_date" class="form-control-input"
                               value="{{ old('pickup_date') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="special_instructions">Custom Instructions & Inscription</label>
                        <textarea name="special_instructions" id="special_instructions" class="form-control-textarea" rows="3"
                                  placeholder="e.g. 'Happy 25th Birthday Sarah!', 2 tiers vanilla-sponge with strawberry buttercream, allergy: no peanuts">{{ old('special_instructions') }}</textarea>
                    </div>
                </div>

                <!-- Order Items Card -->
                <div class="form-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="font-size: 1.05rem; font-weight: 800; color: #29150d; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span>Products / Items</span>
                        </h3>
                        <button type="button" class="btn btn-secondary" id="btn_add_item" style="border-radius: 50px; padding: 0.4rem 1rem; font-size: 0.8rem; font-weight: 700; background: #faf6f0; border: 1px solid rgba(86, 48, 32, 0.2); color: #563020; cursor: pointer;">
                            + Add Item
                        </button>
                    </div>

                    <table class="order-items-builder-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Product</th>
                                <th style="width: 20%;">Price</th>
                                <th style="width: 15%;">Qty</th>
                                <th style="width: 15%; text-align: right;">Total</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="order_items_tbody">
                            <tr class="item-row" data-index="0">
                                <td>
                                    <select name="items[0][product_id]" class="form-control-select item-product-select" required>
                                        <option value="">-- Choose Product --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-stock="{{ $p->stock }}">
                                                {{ $p->name }} (${{ number_format($p->price, 2) }}) [Stock: {{ $p->stock }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control-input item-price-display" value="$0.00" readonly style="background: #f1f5f9;">
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]" class="form-control-input item-qty-input" value="1" min="1" required>
                                </td>
                                <td style="text-align: right; font-weight: 800; vertical-align: middle;">
                                    <span class="item-line-total">$0.00</span>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-remove-row" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.1rem;" title="Remove">&times;</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT COLUMN: Payment & Order Totals Summary -->
            <div>
                <div class="form-card" style="position: sticky; top: 1.5rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #29150d; margin-top: 0; margin-bottom: 1.25rem;">
                        Payment & Fulfillment
                    </h3>

                    <div class="form-group">
                        <label class="form-label" for="order_status">Initial Order Status</label>
                        <select name="order_status" id="order_status" class="form-control-select">
                            <option value="pending" {{ old('order_status') == 'pending' ? 'selected' : '' }}>Pending (Fulfillment Queue)</option>
                            <option value="preparing" {{ old('order_status') == 'preparing' ? 'selected' : '' }}>Preparing (In Baking)</option>
                            <option value="ready_for_pickup" {{ old('order_status') == 'ready_for_pickup' ? 'selected' : '' }}>Ready for Pickup</option>
                            <option value="completed" {{ old('order_status') == 'completed' ? 'selected' : '' }}>Completed (Immediate Sale)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control-select" required>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="qr_code" {{ old('payment_method') == 'qr_code' ? 'selected' : '' }}>KHQR (Bakong Digital)</option>
                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Credit / Debit Card</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_status">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-control-select">
                            <option value="unpaid" {{ old('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid / Pay on Pickup</option>
                            <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="discount_input">Promotional Discount ($)</label>
                        <input type="number" step="0.01" name="discount" id="discount_input" class="form-control-input"
                               value="{{ old('discount', '0.00') }}" min="0">
                    </div>

                    <!-- Live Summary Calculation -->
                    <div style="border-top: 1px solid rgba(238, 233, 224, 0.9); margin-top: 1.25rem; padding-top: 1rem;">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <strong id="summary_subtotal">$0.00</strong>
                        </div>
                        <div class="summary-row">
                            <span>Discount:</span>
                            <span id="summary_discount" style="color: #10b981; font-weight: 700;">-$0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (<span id="tax_rate_span">{{ $taxPercentage }}</span>%):</span>
                            <span id="summary_tax" style="color: #64748b; font-weight: 700;">+$0.00</span>
                        </div>
                        <div class="summary-row grand-total">
                            <span>Grand Total:</span>
                            <span id="summary_grand_total" style="color: #563020;">$0.00</span>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary" id="btn_submit_order" style="width: 100%; border-radius: 50px; padding: 0.85rem; background: linear-gradient(135deg, #563020 0%, #3b2118 100%); color: #ffffff; border: none; font-weight: 800; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 16px rgba(86, 48, 32, 0.3);">
                            Submit & Save Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const productsData = @json($products);
    const taxRate = {{ $taxPercentage }} / 100.0;
    const tbody = document.getElementById('order_items_tbody');
    const addBtn = document.getElementById('btn_add_item');
    const discountInput = document.getElementById('discount_input');
    const customerSelect = document.getElementById('customer_id');
    const customerNameInput = document.getElementById('customer_name');
    let rowCount = 1;

    // Auto-update customer name on customer select
    if (customerSelect && customerNameInput) {
        customerSelect.addEventListener('change', () => {
            const opt = customerSelect.options[customerSelect.selectedIndex];
            if (opt && opt.value) {
                customerNameInput.value = opt.getAttribute('data-name') || opt.text;
            } else {
                customerNameInput.value = 'Walk-in Customer';
            }
        });
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.item-product-select');
            const qtyInput = row.querySelector('.item-qty-input');
            const lineTotalSpan = row.querySelector('.item-line-total');
            const priceDisplay = row.querySelector('.item-price-display');

            const opt = select.options[select.selectedIndex];
            const price = parseFloat(opt ? opt.getAttribute('data-price') || 0 : 0);
            const qty = parseInt(qtyInput ? qtyInput.value || 0 : 0);
            const lineTotal = price * qty;

            if (priceDisplay) priceDisplay.value = '$' + price.toFixed(2);
            if (lineTotalSpan) lineTotalSpan.textContent = '$' + lineTotal.toFixed(2);

            subtotal += lineTotal;
        });

        const enteredDiscount = parseFloat(discountInput ? discountInput.value || 0 : 0);
        const discount = Math.min(enteredDiscount, subtotal);
        const taxable = Math.max(0, subtotal - discount);
        const tax = taxable * taxRate;
        const grandTotal = taxable + tax;

        document.getElementById('summary_subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('summary_discount').textContent = '-$' + discount.toFixed(2);
        document.getElementById('summary_tax').textContent = '+$' + tax.toFixed(2);
        document.getElementById('summary_grand_total').textContent = '$' + grandTotal.toFixed(2);
    }

    // Event delegation on table body
    tbody.addEventListener('change', (e) => {
        if (e.target.classList.contains('item-product-select') || e.target.classList.contains('item-qty-input')) {
            calculateTotals();
        }
    });

    tbody.addEventListener('input', (e) => {
        if (e.target.classList.contains('item-qty-input')) {
            calculateTotals();
        }
    });

    tbody.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove-row') || e.target.closest('.btn-remove-row')) {
            if (document.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            } else {
                alert('An order must contain at least one item.');
            }
        }
    });

    if (discountInput) {
        discountInput.addEventListener('input', calculateTotals);
    }

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            const index = rowCount++;
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.setAttribute('data-index', index);

            let optionsHtml = '<option value="">-- Choose Product --</option>';
            productsData.forEach(p => {
                optionsHtml += `<option value="${p.id}" data-price="${p.price}" data-stock="${p.stock}">${p.name} ($${parseFloat(p.price).toFixed(2)}) [Stock: ${p.stock}]</option>`;
            });

            tr.innerHTML = `
                <td>
                    <select name="items[${index}][product_id]" class="form-control-select item-product-select" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control-input item-price-display" value="$0.00" readonly style="background: #f1f5f9;">
                </td>
                <td>
                    <input type="number" name="items[${index}][quantity]" class="form-control-input item-qty-input" value="1" min="1" required>
                </td>
                <td style="text-align: right; font-weight: 800; vertical-align: middle;">
                    <span class="item-line-total">$0.00</span>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn-remove-row" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.1rem;" title="Remove">&times;</button>
                </td>
            `;

            tbody.appendChild(tr);
        });
    }

    calculateTotals();
});
</script>
@endpush
@endsection
