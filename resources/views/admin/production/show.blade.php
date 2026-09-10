@extends('layouts.app')

@section('title', 'Batch #' . ($production->batch_number ?? $production->id) . ' - Production Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/production.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="prod-detail-container">
    <!-- Top Back & Actions Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('admin.production') }}" class="btn-filter-reset" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Back to Production Batches</span>
        </a>

        <!-- Quick Status Transitions -->
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            @php
                $status = strtolower($production->status);
                $isBaker = auth()->check() && auth()->user()->role === 'baker';
                $canAct = !$isBaker || auth()->id() === $production->baker_id;
            @endphp

            @if($production->isScheduled() && $canAct)
                <form action="{{ route('admin.production.updateStatus', $production->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" class="btn-action-start" style="padding: 0.55rem 1.15rem; font-size: 0.85rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        <span>{{ __('messages.start_production') }}</span>
                    </button>
                </form>

                <form action="{{ route('admin.production.updateStatus', $production->id) }}" method="POST" onsubmit="return confirm('Cancel this scheduled batch?');" style="display: inline;">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn-action-cancel" style="padding: 0.55rem 1.15rem; font-size: 0.85rem;">
                        &times; Cancel Batch
                    </button>
                </form>
            @elseif($production->isInProgress() && $canAct)
                <form action="{{ route('admin.production.updateStatus', $production->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn-action-complete" style="padding: 0.55rem 1.15rem; font-size: 0.85rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ __('messages.complete_production') }}</span>
                    </button>
                </form>

                <form action="{{ route('admin.production.updateStatus', $production->id) }}" method="POST" onsubmit="return confirm('Cancel this in-progress batch? Reserved ingredients will be restored to inventory.');" style="display: inline;">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn-action-cancel" style="padding: 0.55rem 1.15rem; font-size: 0.85rem;">
                        &times; Cancel Batch & Restore Stock
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 0.85rem 1.15rem; border-radius: 12px; font-weight: 700;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.85rem 1.15rem; border-radius: 12px; font-weight: 700;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Production Banner Card -->
    <div class="prod-detail-banner">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <span class="batch-code-pill" style="font-size: 1rem; padding: 0.35rem 0.75rem;">
                    {{ $production->batch_number ?? ('PROD-' . str_pad($production->id, 4, '0', STR_PAD_LEFT)) }}
                </span>
                <span class="badge-status {{ $status }}">
                    {{ ucfirst(str_replace('_', ' ', $production->status)) }}
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                <span style="font-size: 2.2rem; width: 54px; height: 54px; background: #f4f4f5; border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                    {{ $production->product->image_emoji ?? '🥖' }}
                </span>
                <div>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-dark, #27272a); margin: 0;">
                        {{ $production->product->name ?? 'Bakery Product' }}
                    </h2>
                    <div style="font-size: 0.85rem; color: var(--text-muted, #71717a); margin-top: 0.2rem;">
                        SKU: {{ $production->product->sku ?? 'N/A' }} &bull; Current Store Stock: <strong>{{ $production->product->stock }} units</strong>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 0.775rem; font-weight: 700; color: var(--text-muted, #71717a); text-transform: uppercase;">
                Quantity Produced
            </div>
            <div style="font-size: 2.5rem; font-weight: 900; color: var(--primary-brown, #5D4037); line-height: 1;">
                {{ $production->quantity }}
                <span style="font-size: 1rem; font-weight: 600; color: var(--text-muted, #71717a);">units</span>
            </div>
        </div>

        <!-- Production Lifecycle Timeline -->
        <div class="prod-timeline-row" style="width: 100%;">
            <div class="prod-timeline-step">
                <span class="timeline-dot active"></span>
                <div>
                    <div class="timeline-info-title">Scheduled</div>
                    <div class="timeline-info-val">
                        {{ $production->scheduled_at ? $production->scheduled_at->format('M d, Y h:i A') : ($production->production_date ? $production->production_date->format('M d, Y') : '-') }}
                    </div>
                </div>
            </div>

            <div class="prod-timeline-step">
                <span class="timeline-dot {{ $production->started_at ? 'active' : '' }}"></span>
                <div>
                    <div class="timeline-info-title">Started Baking</div>
                    <div class="timeline-info-val">
                        {{ $production->started_at ? $production->started_at->format('M d, Y h:i A') : 'Pending' }}
                    </div>
                </div>
            </div>

            <div class="prod-timeline-step">
                <span class="timeline-dot {{ ($production->completed_at || $production->isCancelled()) ? 'active' : '' }}" style="{{ $production->isCancelled() ? 'background: #dc2626;' : '' }}"></span>
                <div>
                    <div class="timeline-info-title">{{ $production->isCancelled() ? 'Cancelled' : 'Completed' }}</div>
                    <div class="timeline-info-val">
                        @if($production->completed_at)
                            {{ $production->completed_at->format('M d, Y h:i A') }}
                        @elseif($production->isCancelled())
                            Cancelled
                        @else
                            In Progress
                        @endif
                    </div>
                </div>
            </div>

            @if($production->duration)
                <div class="prod-timeline-step" style="margin-left: auto;">
                    <span class="badge-status completed" style="font-size: 0.8rem;">
                        ⏱ Total Duration: {{ $production->duration }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- 2 Column Details: Staff & Recipe -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
        <!-- Assigned Baker Card -->
        <div class="prod-stat-card" style="flex-direction: column; align-items: stretch; gap: 0.75rem;">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted, #71717a); text-transform: uppercase;">
                Production Personnel
            </div>
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div class="baker-avatar" style="width: 44px; height: 44px; font-size: 1.1rem;">
                    {{ $production->baker ? strtoupper(substr($production->baker->name, 0, 2)) : 'UB' }}
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-dark, #27272a);">
                        {{ $production->baker->name ?? 'Unassigned Baker' }}
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted, #71717a);">
                        {{ $production->baker ? ucfirst($production->baker->role) : 'N/A' }} &bull; {{ $production->baker->email ?? '' }}
                    </div>
                </div>
            </div>

            <div style="font-size: 0.8rem; color: var(--text-muted, #71717a); border-top: 1px solid var(--border-color, #f4f4f5); padding-top: 0.6rem; margin-top: 0.25rem;">
                Scheduled by: <strong>{{ $production->creator->name ?? 'System' }}</strong>
            </div>
        </div>

        <!-- Recipe Information Card -->
        <div class="prod-stat-card" style="flex-direction: column; align-items: stretch; gap: 0.75rem;">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted, #71717a); text-transform: uppercase;">
                Recipe & Instructions
            </div>
            @if($production->recipe)
                <div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--primary-brown, #5D4037);">
                        {{ $production->recipe->name }}
                    </div>
                    <div style="font-size: 0.825rem; color: var(--text-dark, #27272a); margin-top: 0.35rem; line-height: 1.4;">
                        {{ $production->recipe->instructions ?? $production->recipe->description ?? 'No detailed instructions provided.' }}
                    </div>
                </div>
            @else
                <div style="color: var(--text-muted, #71717a); font-style: italic; font-size: 0.85rem;">
                    No recipe linked to this batch.
                </div>
            @endif

            @if($production->notes)
                <div style="font-size: 0.8rem; color: var(--text-dark, #27272a); background: #f4f4f5; padding: 0.5rem 0.75rem; border-radius: 8px; margin-top: 0.25rem;">
                    <strong>Notes:</strong> {{ $production->notes }}
                </div>
            @endif
        </div>
    </div>

    <!-- 3. Itemized Ingredients Table -->
    <div class="prod-table-card">
        <div style="padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color, #e4e4e7); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-dark, #27272a);">
                    {{ __('messages.ingredient_requirements') }}
                </h3>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.8rem; color: var(--text-muted, #71717a);">
                    Required ingredients calculated based on batch quantity of {{ $production->quantity }} units
                </p>
            </div>
            <span class="preview-recipe-badge" style="font-size: 0.8rem;">
                {{ $production->recipe ? $production->recipe->name : 'No Recipe' }}
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table class="prod-table">
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Per Unit Need</th>
                        <th>Batch Requirement</th>
                        <th>Current Available Stock</th>
                        <th>Stock Inventory Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requiredIngredients as $ing)
                        <tr>
                            <td style="font-weight: 700;">{{ $ing->name }}</td>
                            <td>{{ number_format($ing->unit_quantity, 2) }} {{ $ing->unit }}</td>
                            <td style="font-weight: 800; color: var(--primary-brown, #5D4037);">
                                {{ number_format($ing->required_total, 2) }} {{ $ing->unit }}
                            </td>
                            <td>{{ number_format($ing->current_stock, 2) }} {{ $ing->unit }}</td>
                            <td>
                                @if($production->isCompleted())
                                    <span class="tag-in-stock">✓ Deducted from Inventory</span>
                                @elseif($production->isInProgress())
                                    <span class="tag-in-stock">✓ Deducted (Baking In Progress)</span>
                                @elseif($ing->is_sufficient)
                                    <span class="tag-in-stock">✓ Available in Inventory</span>
                                @else
                                    <span class="tag-shortage">
                                        ⚠ Short by {{ number_format($ing->shortage, 2) }} {{ $ing->unit }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted, #71717a);">
                                No recipe ingredients linked to this product.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
