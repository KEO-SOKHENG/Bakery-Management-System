@extends('layouts.app')

@section('title', 'Cashier POS Terminal - Bakery Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cashier-dashboard.css') }}">
@endpush

@section('content')
<div class="pos-container" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; padding: 1rem;">
    <!-- Catalog Section -->
    <div class="pos-catalog-section">
        <h2 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1rem; color: #5D4037;">Product Catalog</h2>

        <div class="pos-product-grid" id="pos_product_grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem;">
            @forelse($products as $product)
                <div class="pos-product-card" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}" style="cursor: pointer; background: #fff; padding: 1rem; border-radius: 12px; border: 1px solid #e4e4e7; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center; transition: transform 0.2s;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">{{ $product->image_emoji ?? '🥖' }}</div>
                    <div class="pos-product-name" style="font-weight: 700; font-size: 0.95rem; color: #27272a;">{{ $product->name }}</div>
                    <div style="font-size: 0.8rem; color: #71717a; margin-top: 0.25rem;">Stock: {{ $product->stock }}</div>
                    <div class="pos-product-price" style="font-weight: 800; color: #16a34a; margin-top: 0.5rem; font-size: 1.1rem;">${{ number_format($product->price, 2) }}</div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem;">
                    No available products found in stock.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Cart & Checkout Panel -->
    <div class="pos-cart-panel" style="background: #fff; padding: 1.25rem; border-radius: 12px; border: 1px solid #e4e4e7; box-shadow: 0 1px 3px rgba(0,0,0,0.05); height: fit-content;">
        <div class="cart-header" style="font-weight: 800; font-size: 1.1rem; border-bottom: 1px solid #f4f4f5; padding-bottom: 0.75rem; color: #5D4037;">Current Cart Order</div>

        @if(session('error'))
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 8px; font-size: 0.85rem; margin-top: 0.75rem;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.orders.store') }}" method="POST" id="pos_checkout_form">
            @csrf
            <div style="margin-top: 0.75rem;">
                <label style="font-size: 0.8rem; font-weight: 700; color: #52525b;">Customer Name</label>
                <input type="text" name="customer_name" value="Walk-in Customer" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #d4d4d8; font-size: 0.85rem; margin-top: 0.25rem;">
            </div>

            <div style="margin-top: 0.75rem;">
                <label style="font-size: 0.8rem; font-weight: 700; color: #52525b;">Payment Method</label>
                <select name="payment_method" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #d4d4d8; font-size: 0.85rem; margin-top: 0.25rem;">
                    <option value="cash">Cash</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="qr_code">KHQR / Code</option>
                </select>
            </div>

            <div class="cart-items-list" id="cart_items_container" style="min-height: 180px; max-height: 280px; overflow-y: auto; padding: 0.75rem 0; margin-top: 0.5rem;">
                <div id="cart_empty_msg" style="text-align: center; color: #a1a1aa; padding: 2.5rem 1rem; font-size: 0.85rem;">
                    Click products on the left to add them to this order cart.
                </div>
            </div>

            <div id="hidden_items_inputs"></div>

            <div class="cart-footer" style="border-top: 1px solid #f4f4f5; padding-top: 1rem; margin-top: 0.5rem;">
                <div class="cart-total-row" style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.2rem; margin-bottom: 1rem; color: #27272a;">
                    <span>Total Amount:</span>
                    <span id="pos_cart_total_display">$0.00</span>
                </div>

                <button type="submit" class="btn btn-primary" id="btn_pos_checkout" disabled style="width: 100%; padding: 0.85rem; background: #5D4037; color: #fff; border-radius: 9999px; border: none; font-weight: 800; font-size: 1rem; cursor: pointer; opacity: 0.6; transition: all 0.2s;">
                    Complete POS Order
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cart = [];
        const container = document.getElementById('cart_items_container');
        const hiddenInputs = document.getElementById('hidden_items_inputs');
        const totalDisplay = document.getElementById('pos_cart_total_display');
        const checkoutBtn = document.getElementById('btn_pos_checkout');

        function updateCartUI() {
            if (cart.length === 0) {
                container.innerHTML = '<div id="cart_empty_msg" style="text-align: center; color: #a1a1aa; padding: 2.5rem 1rem; font-size: 0.85rem;">Click products on the left to add them to this order cart.</div>';
                hiddenInputs.innerHTML = '';
                totalDisplay.textContent = '$0.00';
                checkoutBtn.disabled = true;
                checkoutBtn.style.opacity = '0.6';
                return;
            }

            let html = '';
            let inputsHtml = '';
            let total = 0;

            cart.forEach((item, index) => {
                const itemTotal = item.price * item.qty;
                total += itemTotal;

                html += `
                    <div class="cart-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #f4f4f5;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.875rem;">${item.name}</div>
                            <div style="font-size: 0.75rem; color: #71717a;">$${item.price.toFixed(2)} x ${item.qty}</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-weight: 800; font-size: 0.9rem;">$${itemTotal.toFixed(2)}</span>
                            <button type="button" class="btn-remove-cart-item" data-index="${index}" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.1rem;">&times;</button>
                        </div>
                    </div>
                `;

                inputsHtml += `
                    <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
                `;
            });

            container.innerHTML = html;
            hiddenInputs.innerHTML = inputsHtml;
            totalDisplay.textContent = `$${total.toFixed(2)}`;
            checkoutBtn.disabled = false;
            checkoutBtn.style.opacity = '1';
        }

        document.querySelectorAll('.pos-product-card').forEach(card => {
            card.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const price = parseFloat(this.getAttribute('data-price'));
                const maxStock = parseInt(this.getAttribute('data-stock'));

                const existing = cart.find(i => i.id === id);
                if (existing) {
                    if (existing.qty < maxStock) {
                        existing.qty += 1;
                    } else {
                        alert(`Cannot add more. Stock limit for ${name} is ${maxStock}.`);
                    }
                } else {
                    cart.push({ id, name, price, qty: 1 });
                }

                updateCartUI();
            });
        });

        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-cart-item')) {
                const index = parseInt(e.target.getAttribute('data-index'));
                cart.splice(index, 1);
                updateCartUI();
            }
        });
    });
</script>
@endpush
@endsection
