@extends('layouts.app')

@section('title', 'Ingredients Inventory - Bakery Management System')

@section('content')
<x-page-header title="Raw Material Ingredients Inventory" subtitle="Monitor baking stocks, minimum thresholds, purchase costs, and replenish via Purchase Orders">
    <x-slot:actions>
        <x-button variant="secondary" :href="route('admin.purchase-orders.create')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            <span>Purchase Order</span>
        </x-button>
        <x-button variant="primary" id="btn_open_ingredient_modal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Add Ingredient</span>
        </x-button>
    </x-slot:actions>
</x-page-header>

<!-- KPI Metrics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(77, 44, 32, 0.08); display: flex; align-items: center; justify-content: center; color: #4d2c20; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Total Ingredients</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary, #0f172a);">{{ $stats['total'] }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: #b45309; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Low Stock Warnings</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #b45309;">{{ $stats['low_stock'] }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 600;">Out of Stock</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #dc2626;">{{ $stats['out_of_stock'] }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
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
<x-card style="padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
    <form method="GET" action="{{ route('admin.ingredients') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 0.75rem; flex: 1; min-width: 280px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 220px;">
                <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ingredient name..." class="form-control-input" style="padding-left: 2.35rem;">
            </div>

            <div style="min-width: 170px;">
                <x-select name="supplier_id" onchange="this.form.submit()">
                    <option value="all">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div style="min-width: 150px;">
                <x-select name="stock_status" onchange="this.form.submit()">
                    <option value="">All Stock Levels</option>
                    <option value="normal" {{ request('stock_status') === 'normal' ? 'selected' : '' }}>In Stock</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock (≤ Threshold)</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
                </x-select>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <x-button variant="primary" type="submit">Filter</x-button>
            @if(request()->hasAny(['search', 'supplier_id', 'stock_status']))
                <x-button variant="secondary" :href="route('admin.ingredients')">Clear</x-button>
            @endif
        </div>
    </form>
</x-card>

<!-- Ingredients Table -->
<x-card style="overflow: hidden; padding: 0;">
    <div class="table-responsive-wrapper" style="margin-bottom: 0;">
        <table class="bakery-table" id="ingredients_table" style="width: 100%;">
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
                            <span style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem; color: #334155;">
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
                            <x-badge :variant="$isOut ? 'danger' : ($isLow ? 'warning' : 'success')">
                                {{ $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock' : 'In Stock') }}
                            </x-badge>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <a href="{{ route('admin.purchase-orders.create', ['ingredient_id' => $ing->id]) }}" class="table-icon-btn" title="Procure Ingredient via PO">
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
                                    data-status="{{ $ing->status }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>
                                <form action="{{ route('admin.ingredients.destroy', $ing->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete ingredient \'{{ $ing->name }}\'?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon-action delete" title="Delete Ingredient">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
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
</x-card>

<!-- ADD INGREDIENT MODAL -->
<x-modal id="add_ingredient_modal" title="Add Ingredient" maxWidth="540px" class="dash-modal-overlay">
    <form action="{{ route('admin.ingredients.store') }}" method="POST">
        @csrf
        <x-input name="name" label="Ingredient Name" placeholder="e.g. Unbleached Bread Flour" :required="true" />

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-select name="supplier_id" label="Supplier">
                <option value="">-- Optional Supplier --</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </x-select>
            <x-input name="unit" label="Unit (kg, g, L, pcs)" placeholder="kg" :required="true" value="kg" />
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input type="number" step="0.01" min="0" name="quantity" label="Initial Stock Quantity" placeholder="0.00" :required="true" value="0" />
            <x-input type="number" step="0.01" min="0" name="cost" label="Unit Purchase Cost ($)" placeholder="1.50" :required="true" value="0.00" />
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input type="number" step="0.01" min="0" name="minimum_quantity" label="Reorder Warning Threshold" placeholder="10.00" value="10.00" />
            <x-input type="date" name="expiry_date" label="Expiry Date" />
        </div>

        <div class="modal-footer" style="padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <x-button variant="secondary" type="button" id="btn_cancel_ing">Cancel</x-button>
            <x-button variant="primary" type="submit">Save Ingredient</x-button>
        </div>
    </form>
</x-modal>

<!-- EDIT INGREDIENT MODAL -->
<x-modal id="edit_ingredient_modal" title="Edit Ingredient" maxWidth="540px" class="dash-modal-overlay">
    <form id="edit_ingredient_form" method="POST">
        @csrf
        @method('PUT')
        <x-input name="name" id="edit_ing_name" label="Ingredient Name" :required="true" />

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-select name="supplier_id" id="edit_ing_supplier" label="Supplier">
                <option value="">-- Optional Supplier --</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </x-select>
            <x-input name="unit" id="edit_ing_unit" label="Unit" :required="true" />
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input type="number" step="0.01" min="0" name="quantity" id="edit_ing_quantity" label="Current Quantity" :required="true" />
            <x-input type="number" step="0.01" min="0" name="cost" id="edit_ing_cost" label="Purchase Cost ($)" :required="true" />
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input type="number" step="0.01" min="0" name="minimum_quantity" id="edit_ing_minimum" label="Reorder Threshold" />
            <x-input type="date" name="expiry_date" id="edit_ing_expiry" label="Expiry Date" />
        </div>

        <x-select name="status" id="edit_ing_status" label="Status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </x-select>

        <div class="modal-footer" style="padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <x-button variant="secondary" type="button" id="btn_cancel_edit_ing">Cancel</x-button>
            <x-button variant="primary" type="submit">Update Ingredient</x-button>
        </div>
    </form>
</x-modal>

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
                addModal.classList.add('active', 'is-active');
                addModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        function closeAdd() {
            if (addModal) {
                addModal.classList.remove('active', 'is-active');
                addModal.style.display = 'none';
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
                editModal.classList.remove('active', 'is-active');
                editModal.style.display = 'none';
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

                editModal.classList.add('active', 'is-active');
                editModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });
    });
</script>
@endpush
@endsection
