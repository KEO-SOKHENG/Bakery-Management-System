@extends('layouts.app')

@section('title', 'Create Purchase Order - Bakery Management System')

@section('content')
<!-- Header Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0; color: var(--text-primary, #0f172a);">
            Create Purchase Order
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted, #64748b); margin-top: 0.25rem;">
            Order raw ingredients from verified suppliers and track incoming stock
        </p>
    </div>
    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary" style="padding: 0.6rem 1.1rem; border-radius: 9999px; border: 1px solid var(--border-color, #e2e8f0); text-decoration: none; color: var(--text-primary, #1e293b); background: var(--bg-card, #ffffff); font-weight: 600;">
        &larr; Back to Orders
    </a>
</div>

<form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="po_form">
    @csrf

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
        <!-- Left Column: Order Details & Dynamic Items -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Order Header Card -->
            <div class="bakery-card" style="padding: 1.5rem; border-radius: 1.25rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
                <h3 style="margin: 0 0 1.25rem 0; font-size: 1.1rem; font-weight: 800; color: #4d2c20;">
                    1. Supplier & Delivery Information
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div>
                        <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.4rem; color: var(--text-primary, #1e293b);">
                            Select Supplier <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="supplier_id" id="supplier_select" class="ios-select-field" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.6rem; border: 1px solid #d4d4d8; height: 42px;">
                            <option value="">-- Choose Vendor / Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ (old('supplier_id', request('supplier_id')) == $supplier->id) ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->phone ?? $supplier->email ?? 'No contact' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.4rem; color: var(--text-primary, #1e293b);">
                            Order Date <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.6rem; border: 1px solid #d4d4d8; height: 42px;">
                    </div>

                    <div>
                        <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.4rem; color: var(--text-primary, #1e293b);">
                            Expected Delivery Date
                        </label>
                        <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.6rem; border: 1px solid #d4d4d8; height: 42px;">
                    </div>

                    <div>
                        <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.4rem; color: var(--text-primary, #1e293b);">
                            Internal Notes & Terms
                        </label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Bulk discount applied, Net 15" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.6rem; border: 1px solid #d4d4d8; height: 42px;">
                    </div>
                </div>
            </div>

            <!-- Items Table Card -->
            <div class="bakery-card" style="padding: 1.5rem; border-radius: 1.25rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #4d2c20;">
                            2. Purchase Order Items
                        </h3>
                        <p style="font-size: 0.8rem; color: var(--text-muted, #64748b); margin: 0.2rem 0 0 0;">
                            Add raw materials to order. Unit and current price will automatically populate.
                        </p>
                    </div>
                    <button type="button" id="btn_add_row" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 700; border: 1px solid #d4d4d8; background: #fff; color: #4d2c20; cursor: pointer;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Add Item</span>
                    </button>
                </div>

                <div class="table-responsive-wrapper">
                    <table class="recent-orders-table" id="items_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Ingredient</th>
                                <th style="width: 15%;">Unit</th>
                                <th style="width: 15%;">Quantity</th>
                                <th style="width: 15%;">Unit Price ($)</th>
                                <th style="width: 15%;">Subtotal ($)</th>
                                <th style="width: 5%; text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody id="items_tbody">
                            <!-- Dynamic Item Rows Injected Here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Order Summary Card -->
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div class="bakery-card" style="padding: 1.5rem; border-radius: 1.25rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); position: sticky; top: 1.5rem;">
                <h3 style="margin: 0 0 1rem 0; font-size: 1.1rem; font-weight: 800; color: #4d2c20;">
                    Order Summary
                </h3>

                <div style="display: flex; flex-direction: column; gap: 0.85rem; border-bottom: 1px solid var(--border-color, #e2e8f0); padding-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span style="color: var(--text-muted, #64748b);">Total Items</span>
                        <span style="font-weight: 700;" id="summary_total_items">0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span style="color: var(--text-muted, #64748b);">Initial Status</span>
                        <span class="status-pill pending" style="background: rgba(148, 163, 184, 0.15); color: #475569; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem;">Draft</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: baseline; margin: 1.25rem 0;">
                    <span style="font-size: 1rem; font-weight: 700; color: var(--text-primary, #0f172a);">Estimated Total:</span>
                    <span style="font-size: 1.6rem; font-weight: 800; color: #4d2c20;" id="summary_grand_total">$0.00</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; background: #4d2c20; color: #fff; border-radius: 0.75rem; border: none; font-weight: 700; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 12px rgba(77, 44, 32, 0.25);">
                        Save Purchase Order (Draft)
                    </button>
                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary" style="width: 100%; padding: 0.7rem; border-radius: 0.75rem; border: 1px solid #d4d4d8; text-decoration: none; text-align: center; color: #64748b; font-weight: 600;">
                        Cancel
                    </a>
                </div>

                <div style="margin-top: 1rem; padding: 0.75rem; border-radius: 0.6rem; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.775rem; color: #64748b; line-height: 1.4;">
                    ℹ️ Saving creates a <strong>Draft</strong> purchase order. When ready, click "Place Order" to change status to <strong>Ordered</strong>, and "Receive" when vendor delivers to replenish stock.
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const ingredientsCatalog = @json($ingredientsJson);
    let rowCount = 0;

    function createItemRow(ingredientId = '', quantity = 1, price = '') {
        const tbody = document.getElementById('items_tbody');
        const rowIndex = rowCount++;

        const tr = document.createElement('tr');
        tr.id = `row_${rowIndex}`;
        tr.className = 'item-row';

        let ingOptions = `<option value="">-- Select Ingredient --</option>`;
        for (const [id, ing] of Object.entries(ingredientsCatalog)) {
            const selected = (id == ingredientId) ? 'selected' : '';
            ingOptions += `<option value="${id}" ${selected}>${ing.name} (Stock: ${ing.stock} ${ing.unit})</option>`;
        }

        const initialUnit = ingredientId && ingredientsCatalog[ingredientId] ? ingredientsCatalog[ingredientId].unit : '—';
        const initialCost = ingredientId && ingredientsCatalog[ingredientId] ? ingredientsCatalog[ingredientId].cost : 0.00;
        const initialPrice = price !== '' ? price : initialCost;
        const initialSubtotal = (parseFloat(quantity) * parseFloat(initialPrice || 0)).toFixed(2);

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][ingredient_id]" class="ios-select-field ing-select" required style="width: 100%; padding: 0.5rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                    ${ingOptions}
                </select>
            </td>
            <td>
                <span class="unit-badge" id="unit_badge_${rowIndex}" style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.8rem; color: #334155;">
                    ${initialUnit}
                </span>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="items[${rowIndex}][quantity]" value="${quantity}" required class="ios-input-field qty-input" style="width: 100%; padding: 0.5rem; border-radius: 0.5rem; border: 1px solid #d4d4d8; font-weight: 700;">
            </td>
            <td>
                <input type="number" step="0.01" min="0.00" name="items[${rowIndex}][purchase_price]" value="${initialPrice}" required class="ios-input-field price-input" style="width: 100%; padding: 0.5rem; border-radius: 0.5rem; border: 1px solid #d4d4d8; font-weight: 700;">
            </td>
            <td>
                <span style="font-weight: 800; font-size: 0.95rem; color: #0f172a;" id="subtotal_${rowIndex}">
                    $${initialSubtotal}
                </span>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-remove-row" data-row="${rowIndex}" style="background: none; border: none; color: #ef4444; font-size: 1.25rem; cursor: pointer; padding: 0.25rem;">
                    &times;
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Event listeners for this row
        const select = tr.querySelector('.ing-select');
        const qtyInput = tr.querySelector('.qty-input');
        const priceInput = tr.querySelector('.price-input');
        const removeBtn = tr.querySelector('.btn-remove-row');

        select.addEventListener('change', function() {
            const ingId = this.value;
            if (ingId && ingredientsCatalog[ingId]) {
                const ing = ingredientsCatalog[ingId];
                document.getElementById(`unit_badge_${rowIndex}`).textContent = ing.unit.toUpperCase();
                priceInput.value = Number(ing.cost).toFixed(2);
            } else {
                document.getElementById(`unit_badge_${rowIndex}`).textContent = '—';
            }
            updateRowSubtotal(rowIndex);
        });

        qtyInput.addEventListener('input', () => updateRowSubtotal(rowIndex));
        priceInput.addEventListener('input', () => updateRowSubtotal(rowIndex));

        removeBtn.addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                tr.remove();
                recalculateGrandTotal();
            } else {
                alert('A purchase order must have at least one ingredient.');
            }
        });

        recalculateGrandTotal();
    }

    function updateRowSubtotal(rowIndex) {
        const tr = document.getElementById(`row_${rowIndex}`);
        if (!tr) return;
        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        const subtotal = (qty * price).toFixed(2);
        document.getElementById(`subtotal_${rowIndex}`).textContent = `$${subtotal}`;
        recalculateGrandTotal();
    }

    function recalculateGrandTotal() {
        let grandTotal = 0;
        let totalItems = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            grandTotal += (qty * price);
            totalItems++;
        });

        document.getElementById('summary_total_items').textContent = totalItems;
        document.getElementById('summary_grand_total').textContent = `$${grandTotal.toFixed(2)}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize with 1 default row
        createItemRow();

        document.getElementById('btn_add_row').addEventListener('click', function() {
            createItemRow();
        });
    });
</script>
@endpush
@endsection
