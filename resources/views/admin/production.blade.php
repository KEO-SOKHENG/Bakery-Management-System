@extends('layouts.app')

@section('title', __('messages.production_management') . ' - Bakery System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/production.css') }}?v={{ @filemtime(public_path('css/production.css')) ?: '1.0' }}">
@endpush

@section('content')
<div class="production-dashboard-wrapper">
    <!-- 1. Hero Header Bar -->
    <div class="production-hero-bar page-header">
        <div class="production-hero-title page-header-info">
            <h1 class="page-header-title">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary-brown, #5D4037);"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                <span data-lang-key="production_management">{{ __('messages.production_management') }}</span>
            </h1>
            <p class="page-header-subtitle">{{ __('messages.deduct_stock_notice') }}</p>
        </div>

        @if(in_array(auth()->user()->role, ['admin', 'manager']))
            <div class="page-header-actions">
                <x-button variant="primary" id="btn_open_prod_modal" class="btn-schedule-batch">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span data-lang-key="schedule_production_batch">{{ __('messages.schedule_production_batch') }}</span>
                </x-button>
            </div>
        @endif
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif

    @if(session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <!-- 2. KPI Summary Cards -->
    <div class="prod-stats-grid">
        <x-card class="prod-stat-card">
            <div class="prod-stat-icon total">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </div>
            <div class="prod-stat-info">
                <span class="prod-stat-val">{{ number_format($stats['total']) }}</span>
                <span class="prod-stat-lbl" data-lang-key="all_batches">{{ __('messages.all_batches') }}</span>
            </div>
        </x-card>

        <x-card class="prod-stat-card">
            <div class="prod-stat-icon scheduled">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="prod-stat-info">
                <span class="prod-stat-val">{{ number_format($stats['scheduled']) }}</span>
                <span class="prod-stat-lbl" data-lang-key="scheduled">{{ __('messages.scheduled') }}</span>
            </div>
        </x-card>

        <x-card class="prod-stat-card">
            <div class="prod-stat-icon in-progress">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            </div>
            <div class="prod-stat-info">
                <span class="prod-stat-val">{{ number_format($stats['in_progress']) }}</span>
                <span class="prod-stat-lbl" data-lang-key="in_progress">{{ __('messages.in_progress') }}</span>
            </div>
        </x-card>

        <x-card class="prod-stat-card">
            <div class="prod-stat-icon completed">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="prod-stat-info">
                <span class="prod-stat-val">{{ number_format($stats['completed']) }}</span>
                <span class="prod-stat-lbl" data-lang-key="completed">{{ __('messages.completed') }}</span>
            </div>
        </x-card>
    </div>

    <!-- 3. Status Filter Tabs -->
    <div class="orders-filter-bar" id="prod_status_tabs" style="margin-bottom: 1.25rem;">
        <a href="{{ route('admin.production', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}"
           class="filter-btn {{ $tab === 'all' ? 'active' : '' }}">
            <span data-lang-key="all_batches">{{ __('messages.all_batches') }}</span> ({{ $stats['total'] }})
        </a>
        <a href="{{ route('admin.production', array_merge(request()->except('tab', 'page'), ['tab' => 'active'])) }}"
           class="filter-btn {{ $tab === 'active' ? 'active' : '' }}">
            <span data-lang-key="active_batches">{{ __('messages.active_batches') }}</span> ({{ $stats['scheduled'] + $stats['in_progress'] }})
        </a>
        <a href="{{ route('admin.production', array_merge(request()->except('tab', 'page'), ['tab' => 'history'])) }}"
           class="filter-btn {{ $tab === 'history' ? 'active' : '' }}">
            <span data-lang-key="completed_history">{{ __('messages.completed_history') }}</span> ({{ $stats['completed'] + $stats['cancelled'] }})
        </a>
        @if(auth()->user()->role === 'baker')
            <a href="{{ route('admin.production', array_merge(request()->except('tab', 'page'), ['tab' => 'mine'])) }}"
               class="filter-btn {{ $tab === 'mine' ? 'active' : '' }}">
                <span data-lang-key="assigned_to_me">{{ __('messages.assigned_to_me') }}</span> ({{ $stats['my_batches'] }})
            </a>
        @endif
    </div>

    <!-- 4. Filter Toolbar -->
    <div class="card" style="padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
        <form method="GET" action="{{ route('admin.production') }}" class="prod-filters-grid" id="prod_filter_form">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <!-- Search -->
            <div class="prod-search-wrap">
                <svg class="prod-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" id="prod_search_input" class="form-control-input" style="padding-left: 2.4rem;"
                       value="{{ request('search') }}" placeholder="Search batch #, product, baker...">
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="form-control-select" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>{{ __('messages.scheduled') }}</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('messages.cancelled') }}</option>
                </select>
            </div>

            <!-- Product Filter -->
            <div>
                <select name="product_id" class="form-control-select" onchange="this.form.submit()">
                    <option value="all">All Products</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                            {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Baker Filter -->
            <div>
                <select name="baker_id" class="form-control-select" onchange="this.form.submit()">
                    <option value="all">All Bakers</option>
                    @foreach($bakers as $baker)
                        <option value="{{ $baker->id }}" {{ request('baker_id') == $baker->id ? 'selected' : '' }}>
                            {{ $baker->name }} ({{ ucfirst($baker->role) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Reset -->
            <div>
                <x-button variant="secondary" size="sm" :href="route('admin.production', ['tab' => $tab])">
                    Reset
                </x-button>
            </div>
        </form>
    </div>

    <!-- 4. Production Batches Table -->
    <x-card class="prod-table-card">
        <div style="overflow-x: auto;">
            <table class="prod-table" id="production_batches_table">
                <thead>
                    <tr>
                        <th>{{ __('messages.batch_number') }}</th>
                        <th>Product & Recipe</th>
                        <th>Qty</th>
                        <th>{{ __('messages.baker') }}</th>
                        <th>Schedule & Timeline</th>
                        <th>{{ __('messages.status') }}</th>
                        <th style="text-align: right;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productions as $batch)
                        @php
                            $statusClass = strtolower($batch->status);
                            $canStart = $batch->isScheduled();
                            $canComplete = $batch->isInProgress();
                            $canCancel = in_array($statusClass, ['scheduled', 'in_progress']);
                            $isAssignedToUser = auth()->check() && (auth()->user()->role !== 'baker' || auth()->id() === $batch->baker_id);
                        @endphp
                        <tr class="prod-batch-row" data-batch-id="{{ $batch->id }}" data-status="{{ $statusClass }}">
                            <!-- Batch Number -->
                            <td>
                                <a href="{{ route('admin.production.show', $batch->id) }}" class="batch-code-pill" title="View Batch Details">
                                    {{ $batch->batch_number ?? ('PROD-' . str_pad($batch->id, 4, '0', STR_PAD_LEFT)) }}
                                </a>
                                <div style="font-size: 0.725rem; color: var(--text-muted, #71717a); margin-top: 0.2rem;">
                                    Created {{ $batch->created_at ? $batch->created_at->format('M d, H:i') : '' }}
                                </div>
                            </td>

                            <!-- Product -->
                            <td>
                                <div class="prod-product-cell">
                                    <span class="prod-product-emoji">{{ $batch->product->image_emoji ?? '🥖' }}</span>
                                    <div>
                                        <div class="prod-product-name">{{ $batch->product->name ?? 'Unknown Product' }}</div>
                                        <div class="prod-recipe-sub">
                                            @if($batch->recipe)
                                                <span>{{ $batch->recipe->name }}</span>
                                            @else
                                                <span style="color: #a1a1aa; font-style: italic;">No Recipe</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Quantity -->
                            <td>
                                <span style="font-weight: 800; font-size: 1rem; color: var(--text-dark, #27272a);">
                                    {{ $batch->quantity }}
                                </span>
                                <span style="font-size: 0.75rem; color: var(--text-muted, #71717a);"> units</span>
                            </td>

                            <!-- Assigned Baker -->
                            <td>
                                @if($batch->baker)
                                    <div class="baker-pill">
                                        <span class="baker-avatar">
                                            {{ strtoupper(substr($batch->baker->name, 0, 2)) }}
                                        </span>
                                        <span>{{ $batch->baker->name }}</span>
                                    </div>
                                @else
                                    <span style="color: #a1a1aa; font-style: italic; font-size: 0.8rem;">Unassigned</span>
                                @endif
                            </td>

                            <!-- Schedule & Timeline -->
                            <td>
                                <div style="font-weight: 600; font-size: 0.825rem; color: var(--text-dark, #27272a);">
                                    {{ $batch->scheduled_at ? $batch->scheduled_at->format('M d, Y h:i A') : ($batch->production_date ? $batch->production_date->format('M d, Y') : '-') }}
                                </div>
                                @if($batch->duration)
                                    <div style="font-size: 0.725rem; color: #16a34a; font-weight: 700;">
                                        Duration: {{ $batch->duration }}
                                    </div>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <x-badge :status="$batch->status" class="badge-status {{ $statusClass }}" />
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div class="prod-actions-row" style="justify-content: flex-end;">
                                    <!-- Start Production Button (Scheduled -> In Progress) -->
                                    @if($canStart && $isAssignedToUser)
                                        <form action="{{ route('admin.production.updateStatus', $batch->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="btn-action-start" title="Start Baking & Deduct Ingredients">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                                <span>{{ __('messages.start_production') }}</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Complete Production Button (In Progress -> Completed) -->
                                    @if($canComplete && $isAssignedToUser)
                                        <form action="{{ route('admin.production.updateStatus', $batch->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn-action-complete" title="Mark Completed & Increment Product Stock">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span>{{ __('messages.complete_production') }}</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Cancel Button -->
                                    @if($canCancel && $isAssignedToUser)
                                        <form action="{{ route('admin.production.updateStatus', $batch->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this production batch?');" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn-action-cancel" title="Cancel Production">
                                                &times; Cancel
                                            </button>
                                        </form>
                                    @endif

                                    <!-- View Details Link -->
                                    <a href="{{ route('admin.production.show', $batch->id) }}" class="btn-icon-action" title="View Batch Details">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>

                                    <!-- Delete Button (Only Scheduled or Cancelled, Admin/Manager Only) -->
                                    @if(in_array(auth()->user()->role, ['admin', 'manager']) && ($batch->isScheduled() || $batch->isCancelled()))
                                        <form action="{{ route('admin.production.destroy', $batch->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete production batch #{{ $batch->batch_number }}?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon-action delete" title="Delete Batch">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted, #71717a);">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🥖</div>
                                <div style="font-weight: 700; font-size: 1rem; color: var(--text-dark, #27272a);">No production batches found</div>
                                <div style="font-size: 0.825rem; margin-top: 0.25rem;">Try adjusting your search criteria or schedule a new production batch above.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($productions->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color, #e4e4e7);">
                {{ $productions->links() }}
            </div>
        @endif
    </x-card>
</div>

<!-- ========================================================================
     CREATE / SCHEDULE PRODUCTION BATCH MODAL
     ======================================================================== -->
<div class="prod-modal-overlay" id="modal_schedule_batch" style="display: none;">
    <div class="prod-modal-content">
        <div class="prod-modal-header">
            <h3 class="prod-modal-title" data-lang-key="schedule_production_batch">
                {{ __('messages.schedule_production_batch') }}
            </h3>
            <button type="button" class="btn-close-modal" id="btn_close_schedule_modal">&times;</button>
        </div>

        <form action="{{ route('admin.production.store') }}" method="POST" id="form_create_production">
            @csrf

            <div class="prod-form-grid">
                <!-- Product Selection -->
                <div class="prod-form-group full-width">
                    <label class="prod-form-label" for="prod_select_product">
                        {{ __('messages.product') }} *
                    </label>
                    <select name="product_id" id="prod_select_product" class="prod-form-select" required>
                        <option value="">-- Choose Product to Bake --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-has-recipe="{{ $p->recipe ? 'true' : 'false' }}">
                                {{ $p->image_emoji ?? '🥖' }} {{ $p->name }} (Current Stock: {{ $p->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantity to Produce -->
                <div class="prod-form-group">
                    <label class="prod-form-label" for="prod_input_qty">
                        {{ __('messages.quantity') }} *
                    </label>
                    <input type="number" name="quantity" id="prod_input_qty" class="prod-form-input" value="10" min="1" required>
                </div>

                <!-- Assign Baker -->
                <div class="prod-form-group">
                    <label class="prod-form-label" for="prod_select_baker">
                        {{ __('messages.assign_baker') }}
                    </label>
                    <select name="baker_id" id="prod_select_baker" class="prod-form-select">
                        @foreach($bakers as $baker)
                            <option value="{{ $baker->id }}" {{ $baker->role === 'baker' ? 'selected' : '' }}>
                                {{ $baker->name }} ({{ ucfirst($baker->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Scheduled Date & Time -->
                <div class="prod-form-group">
                    <label class="prod-form-label" for="prod_input_scheduled_at">
                        {{ __('messages.scheduled_at') }}
                    </label>
                    <input type="datetime-local" name="scheduled_at" id="prod_input_scheduled_at" class="prod-form-input"
                           value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <!-- Initial Status -->
                <div class="prod-form-group">
                    <label class="prod-form-label" for="prod_select_status">
                        Initial {{ __('messages.status') }}
                    </label>
                    <select name="status" id="prod_select_status" class="prod-form-select">
                        <option value="scheduled" selected>{{ __('messages.scheduled') }} (Plan ahead, no stock change)</option>
                        <option value="in_progress">{{ __('messages.in_progress') }} (Deduct ingredients now)</option>
                        <option value="completed">{{ __('messages.completed') }} (Deduct ingredients & increase product stock)</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="prod-form-group full-width">
                    <label class="prod-form-label" for="prod_textarea_notes">
                        {{ __('messages.notes') }}
                    </label>
                    <textarea name="notes" id="prod_textarea_notes" class="prod-form-textarea" rows="2" placeholder="e.g. Special order for morning catering, double proofed..."></textarea>
                </div>
            </div>

            <!-- LIVE INGREDIENT REQUIREMENT PREVIEW -->
            <div class="prod-ingredients-preview-box" id="preview_ingredients_container" style="display: none; margin-top: 1rem;">
                <div class="preview-box-header">
                    <span class="preview-box-title" data-lang-key="ingredient_requirements">{{ __('messages.ingredient_requirements') }}</span>
                    <span class="preview-recipe-badge" id="preview_recipe_badge">Recipe</span>
                </div>

                <div style="overflow-x: auto;">
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th>Ingredient</th>
                                <th>Unit Need</th>
                                <th>Batch Total</th>
                                <th>In Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="preview_ingredients_tbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <div id="preview_shortage_alert" style="display: none; color: #dc2626; font-size: 0.775rem; font-weight: 700; background: #fef2f2; padding: 0.5rem; border-radius: 6px; margin-top: 0.25rem;">
                    ⚠ Warning: One or more ingredients have insufficient inventory stock!
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; border-top: 1px solid var(--border-color, #e4e4e7); padding-top: 1rem;">
                <x-button variant="secondary" id="btn_cancel_schedule">Cancel</x-button>
                <x-button variant="primary" type="submit" class="btn-schedule-batch" id="btn_submit_production">
                    Save Production Batch
                </x-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    window.PRODUCTS_CATALOG = @json($productsJson);

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modal_schedule_batch');
        const openBtn = document.getElementById('btn_open_prod_modal');
        const closeBtn = document.getElementById('btn_close_schedule_modal');
        const cancelBtn = document.getElementById('btn_cancel_schedule');

        const productSelect = document.getElementById('prod_select_product');
        const qtyInput = document.getElementById('prod_input_qty');
        const previewContainer = document.getElementById('preview_ingredients_container');
        const previewBadge = document.getElementById('preview_recipe_badge');
        const previewTbody = document.getElementById('preview_ingredients_tbody');
        const shortageAlert = document.getElementById('preview_shortage_alert');

        // Modal open/close
        if (openBtn && modal) {
            openBtn.addEventListener('click', () => {
                modal.style.display = 'flex';
                updateIngredientPreview();
            });
        }

        [closeBtn, cancelBtn].forEach(btn => {
            if (btn && modal) {
                btn.addEventListener('click', () => modal.style.display = 'none');
            }
        });

        // Close on backdrop click
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });
        }

        // Live calculation of ingredients on product / quantity change
        function updateIngredientPreview() {
            if (!productSelect || !qtyInput) return;
            const productId = productSelect.value;
            const batchQty = parseInt(qtyInput.value) || 0;

            if (!productId || !window.PRODUCTS_CATALOG || !window.PRODUCTS_CATALOG[productId]) {
                previewContainer.style.display = 'none';
                return;
            }

            const prodData = window.PRODUCTS_CATALOG[productId];
            if (!prodData.recipe || !prodData.recipe.ingredients || prodData.recipe.ingredients.length === 0) {
                previewContainer.style.display = 'block';
                previewBadge.textContent = 'No Recipe Configured';
                previewTbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #a1a1aa; padding: 0.5rem;">No linked recipe. No ingredients will be deducted.</td></tr>`;
                shortageAlert.style.display = 'none';
                return;
            }

            previewContainer.style.display = 'block';
            previewBadge.textContent = prodData.recipe.name;
            previewTbody.innerHTML = '';
            let hasShortage = false;

            prodData.recipe.ingredients.forEach(ing => {
                const totalRequired = (ing.unit_quantity * batchQty).toFixed(2);
                const currentStock = parseFloat(ing.current_stock).toFixed(2);
                const isSufficient = parseFloat(currentStock) >= parseFloat(totalRequired);

                if (!isSufficient) hasShortage = true;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight: 600;">${ing.name}</td>
                    <td>${ing.unit_quantity} ${ing.unit}</td>
                    <td style="font-weight: 700;">${totalRequired} ${ing.unit}</td>
                    <td>${currentStock} ${ing.unit}</td>
                    <td>
                        <span class="${isSufficient ? 'tag-in-stock' : 'tag-shortage'}">
                            ${isSufficient ? '✓ In Stock' : '⚠ Short'}
                        </span>
                    </td>
                `;
                previewTbody.appendChild(tr);
            });

            shortageAlert.style.display = hasShortage ? 'block' : 'none';
        }

        if (productSelect) productSelect.addEventListener('change', updateIngredientPreview);
        if (qtyInput) qtyInput.addEventListener('input', updateIngredientPreview);
    });
</script>
@endpush
@endsection
