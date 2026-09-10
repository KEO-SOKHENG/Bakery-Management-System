@extends('layouts.app')

@section('title', 'Ingredients Inventory - Bakery Management System')

@section('content')
<!-- Header Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0; color: var(--text-primary, #0f172a);">
            Raw Material Ingredients Inventory
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted, #64748b); margin-top: 0.25rem;">
            Monitor baking stocks, minimum thresholds, purchase costs, and replenish via Purchase Orders
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 9999px; border: 1px solid var(--border-color, #e2e8f0); text-decoration: none; color: var(--text-primary, #1e293b); background: var(--bg-card, #ffffff); font-weight: 700;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            <span>Procure via Purchase Order</span>
        </a>
        <button class="btn btn-primary" id="btn_open_ingredient_modal" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(77, 44, 32, 0.25);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Add Ingredient</span>
        </button>
    </div>
</div>

<!-- KPI Metrics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(77, 44, 32, 0.08); display: flex; align-items: center; justify-content: center; color: #4d2c20; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Ingredients</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary, #0f172a);">{{ $stats['total'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #b45309; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Low Stock Warnings</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #b45309;">{{ $stats['low_stock'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Out of Stock</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #dc2626;">{{ $stats['out_of_stock'] }}</div>
        </div>
    </div>

    <div class="bakery-card" style="padding: 1.1rem 1.25rem; border-radius: 1rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #2563eb; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Stock Value</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #2563eb;">${{ number_format($stats['total_value'], 2) }}</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="bakery-card" style="padding: 1rem 1.25rem; border-radius: 1rem; margin-bottom: 1.25rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
    <form method="GET" action="{{ route('admin.ingredients') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 0.75rem; flex: 1; min-width: 280px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 220px;">
                <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ingredient name..." class="ios-input-field" style="padding-left: 2.35rem; width: 100%; border-radius: 0.6rem; border: 1px solid var(--border-color, #d4d4d8); height: 40px;">
            </div>

            <select name="supplier_id" class="ios-select-field" style="min-width: 170px; border-radius: 0.6rem; border: 1px solid var(--border-color, #d4d4d8); height: 40px;" onchange="this.form.submit()">
                <option value="all">All Suppliers</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>

            <select name="stock_status" class="ios-select-field" style="min-width: 150px; border-radius: 0.6rem; border: 1px solid var(--border-color, #d4d4d8); height: 40px;" onchange="this.form.submit()">
                <option value="">All Stock Levels</option>
                <option value="normal" {{ request('stock_status') === 'normal' ? 'selected' : '' }}>In Stock</option>
                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock (≤ Threshold)</option>
                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.1rem; border-radius: 0.6rem; background: #4d2c20; color: #fff; font-weight: 700; border: none; cursor: pointer;">Filter</button>
            @if(request()->hasAny(['search', 'supplier_id', 'stock_status']))
                <a href="{{ route('admin.ingredients') }}" class="btn btn-secondary" style="padding: 0.5rem 0.85rem; border-radius: 0.6rem; border: 1px solid #d4d4d8; text-decoration: none; color: #64748b;">Clear</a>
            @endif
        </div>
    </form>
</div>

<!-- Ingredients Table -->
<div class="bakery-card" style="border-radius: 1rem; overflow: hidden; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
    <div class="table-responsive-wrapper">
        <table class="recent-orders-table" id="ingredients_table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Ingredient Name</th>
                    <th>Current Stock</th>
                    <th>Unit</th>
                    <th>Purchase Cost</th>
                    <th>Reorder Threshold</th>
                    <th>Primary Supplier</th>
                    <th>Expiry Date</th>
                    <th>Stock Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ingredients as $ing)
                    @php
                        $qty = (float) $ing->quantity;
                        $min = (float) $ing->minimum_quantity;
                        $isOut = $qty <= 0;
                        $isLow = $qty > 0 && $qty <= $min;
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-primary, #0f172a);">
                                {{ $ing->name }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted, #71717a);">
                                In {{ $ing->recipes_count }} recipes | {{ $ing->purchase_order_items_count }} PO items
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 800; font-size: 1.05rem; {{ $isOut ? 'color: #ef4444;' : ($isLow ? 'color: #d97706;' : 'color: #0f172a;') }}">
                                {{ number_format($ing->quantity, 2) }}
                            </span>
                        </td>
                        <td>
                            <span class="unit-badge" style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem; color: #334155;">
                                {{ strtoupper($ing->unit) }}
                            </span>
                        </td>
                        <td style="font-weight: 700; color: #0f172a;">
                            ${{ number_format($ing->cost, 2) }}
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-muted, #64748b);">
                            {{ number_format($ing->minimum_quantity, 2) }} {{ $ing->unit }}
                        </td>
                        <td>
                            @if($ing->supplier)
                                <a href="{{ route('admin.suppliers.show', $ing->supplier->id) }}" style="color: #4d2c20; font-weight: 700; text-decoration: none; font-size: 0.85rem;">
                                    {{ $ing->supplier->name }}
                                </a>
                            @else
                                <span style="color: #a1a1aa; font-size: 0.85rem;">Unassigned</span>
                            @endif
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-muted, #64748b);">
                            {{ $ing->expiry_date ? $ing->expiry_date->format('M d, Y') : '—' }}
                        </td>
                        <td>
                            @if($isOut)
                                <span class="status-pill danger" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">Out of Stock</span>
                            @elseif($isLow)
                                <span class="status-pill pending" style="background: rgba(245, 158, 11, 0.12); color: #d97706; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">Low Stock</span>
                            @else
                                <span class="status-pill completed" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem;">In Stock</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <a href="{{ route('admin.purchase-orders.create', ['ingredient_id' => $ing->id]) }}" class="table-icon-btn" title="Procure Ingredient via PO" style="color: #059669; background: rgba(5, 150, 105, 0.08); border-radius: 6px; padding: 6px; display: inline-flex;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </a>
                                <button type="button" class="table-icon-btn btn-edit-ing" title="Edit Ingredient"
                                    data-id="{{ $ing->id }}"
                                    data-name="{{ $ing->name }}"
                                    data-unit="{{ $ing->unit }}"
                                    data-quantity="{{ $ing->quantity }}"
                                    data-cost="{{ $ing->cost }}"
                                    data-min="{{ $ing->minimum_quantity }}"
                                    data-supplier="{{ $ing->supplier_id }}"
                                    data-expiry="{{ $ing->expiry_date ? $ing->expiry_date->format('Y-m-d') : '' }}"
                                    data-status="{{ $ing->status }}"
                                    style="color: #2563eb; background: rgba(37, 99, 235, 0.08); border: none; border-radius: 6px; padding: 6px; cursor: pointer; display: inline-flex;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>
                                <form action="{{ route('admin.ingredients.destroy', $ing->id) }}" method="POST" onsubmit="return confirm('Delete ingredient \'{{ $ing->name }}\'?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-icon-btn" title="Delete Ingredient" style="color: #ef4444; background: rgba(239, 68, 68, 0.08); border: none; border-radius: 6px; padding: 6px; cursor: pointer; display: inline-flex;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted, #71717a); padding: 3rem;">
                            <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">🌾</div>
                            <div style="font-weight: 700; font-size: 1.05rem;">No ingredients found</div>
                            <div style="font-size: 0.825rem; margin-top: 0.25rem;">Click "Add Ingredient" to register baking raw materials.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ingredients->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color, #e2e8f0);">
            {{ $ingredients->links() }}
        </div>
    @endif
</div>

<!-- ADD INGREDIENT MODAL -->
<div class="dash-modal-overlay" id="add_ingredient_modal">
    <div class="dash-modal-content" style="max-width: 540px; border-radius: 1.25rem; padding: 1.5rem;">
        <div class="dash-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #4d2c20;">Add New Raw Material</h3>
            <button type="button" class="btn-close-modal" id="btn_close_ing_modal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #71717a;">&times;</button>
        </div>
        <form action="{{ route('admin.ingredients.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Ingredient Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="name" class="ios-input-field" placeholder="e.g. Unbleached Bread Flour" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Supplier</label>
                    <select name="supplier_id" class="ios-select-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                        <option value="">-- Optional Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Unit (kg, g, L, pcs) <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="unit" class="ios-input-field" placeholder="kg" required value="kg" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Initial Stock Quantity <span style="color: #ef4444;">*</span></label>
                    <input type="number" step="0.01" min="0" name="quantity" class="ios-input-field" placeholder="0.00" required value="0" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Unit Purchase Cost ($) <span style="color: #ef4444;">*</span></label>
                    <input type="number" step="0.01" min="0" name="cost" class="ios-input-field" placeholder="1.50" required value="0.00" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Reorder Warning Threshold</label>
                    <input type="number" step="0.01" min="0" name="minimum_quantity" class="ios-input-field" placeholder="10.00" value="10.00" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Expiry Date</label>
                    <input type="date" name="expiry_date" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" id="btn_cancel_ing" style="padding: 0.6rem 1.25rem; border-radius: 9999px; border: 1px solid #d4d4d8; background: #fff; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">Save Ingredient</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT INGREDIENT MODAL -->
<div class="dash-modal-overlay" id="edit_ingredient_modal">
    <div class="dash-modal-content" style="max-width: 540px; border-radius: 1.25rem; padding: 1.5rem;">
        <div class="dash-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #4d2c20;">Edit Ingredient</h3>
            <button type="button" class="btn-close-modal" id="btn_close_edit_ing_modal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #71717a;">&times;</button>
        </div>
        <form id="edit_ingredient_form" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Ingredient Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="name" id="edit_ing_name" class="ios-input-field" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Supplier</label>
                    <select name="supplier_id" id="edit_ing_supplier" class="ios-select-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                        <option value="">-- Optional Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Unit <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="unit" id="edit_ing_unit" class="ios-input-field" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Current Stock <span style="color: #ef4444;">*</span></label>
                    <input type="number" step="0.01" min="0" name="quantity" id="edit_ing_quantity" class="ios-input-field" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Unit Cost ($) <span style="color: #ef4444;">*</span></label>
                    <input type="number" step="0.01" min="0" name="cost" id="edit_ing_cost" class="ios-input-field" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Reorder Threshold <span style="color: #ef4444;">*</span></label>
                    <input type="number" step="0.01" min="0" name="minimum_quantity" id="edit_ing_minimum" class="ios-input-field" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Expiry Date</label>
                    <input type="date" name="expiry_date" id="edit_ing_expiry" class="ios-input-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                </div>
            </div>

            <div>
                <label class="form-label" style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem;">Status</label>
                <select name="status" id="edit_ing_status" class="ios-select-field" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.5rem; border: 1px solid #d4d4d8;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" id="btn_cancel_edit_ing" style="padding: 0.6rem 1.25rem; border-radius: 9999px; border: 1px solid #d4d4d8; background: #fff; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">Update Ingredient</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add Modal
        const addModal = document.getElementById('add_ingredient_modal');
        const openAddBtn = document.getElementById('btn_open_ingredient_modal');
        const closeAddBtn = document.getElementById('btn_close_ing_modal');
        const cancelAddBtn = document.getElementById('btn_cancel_ing');

        function openAdd() {
            if (addModal) {
                addModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
        function closeAdd() {
            if (addModal) {
                addModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
        if (openAddBtn) openAddBtn.addEventListener('click', openAdd);
        if (closeAddBtn) closeAddBtn.addEventListener('click', closeAdd);
        if (cancelAddBtn) cancelAddBtn.addEventListener('click', closeAdd);
        if (addModal) {
            addModal.addEventListener('click', (e) => { if (e.target === addModal) closeAdd(); });
        }

        // Edit Modal
        const editModal = document.getElementById('edit_ingredient_modal');
        const editForm = document.getElementById('edit_ingredient_form');
        const closeEditBtn = document.getElementById('btn_close_edit_ing_modal');
        const cancelEditBtn = document.getElementById('btn_cancel_edit_ing');

        function closeEdit() {
            if (editModal) {
                editModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
        if (closeEditBtn) closeEditBtn.addEventListener('click', closeEdit);
        if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEdit);
        if (editModal) {
            editModal.addEventListener('click', (e) => { if (e.target === editModal) closeEdit(); });
        }

        document.querySelectorAll('.btn-edit-ing').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                editForm.action = `/admin/ingredients/${id}`;
                document.getElementById('edit_ing_name').value = this.getAttribute('data-name') || '';
                document.getElementById('edit_ing_unit').value = this.getAttribute('data-unit') || '';
                document.getElementById('edit_ing_quantity').value = this.getAttribute('data-quantity') || '';
                document.getElementById('edit_ing_cost').value = this.getAttribute('data-cost') || '';
                document.getElementById('edit_ing_minimum').value = this.getAttribute('data-min') || '';
                document.getElementById('edit_ing_supplier').value = this.getAttribute('data-supplier') || '';
                document.getElementById('edit_ing_expiry').value = this.getAttribute('data-expiry') || '';
                document.getElementById('edit_ing_status').value = this.getAttribute('data-status') || 'active';

                editModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });
    });
</script>
@endpush
@endsection
