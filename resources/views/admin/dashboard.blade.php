@extends('layouts.app')

@section('title', 'Admin Analytics & Dashboard - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ time() }}">
@endpush

@section('content')
<!-- Analytics Date Range Filter Toolbar -->
<div class="dash-filter-bar">
    <div class="filter-bar-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 4h18v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V19l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4z"/>
        </svg>
        <span data-lang-key="analytics_timeframe">{{ __('messages.analytics_timeframe') }}</span>
        @if($startDate && $endDate)
            <span class="filter-date-badge">{{ $startDate->format('M d, Y') }} &mdash; {{ $endDate->format('M d, Y') }}</span>
        @endif
    </div>
    <form method="GET" action="{{ route('admin.dashboard') }}" class="filter-form">
        <div class="period-pill-group">
            <a href="{{ route('admin.dashboard', ['period' => 'today']) }}" class="period-pill {{ $period === 'today' ? 'active' : '' }}" data-lang-key="today">{{ __('messages.today') }}</a>
            <a href="{{ route('admin.dashboard', ['period' => 'yesterday']) }}" class="period-pill {{ $period === 'yesterday' ? 'active' : '' }}" data-lang-key="yesterday">{{ __('messages.yesterday') }}</a>
            <a href="{{ route('admin.dashboard', ['period' => '7days']) }}" class="period-pill {{ $period === '7days' ? 'active' : '' }}" data-lang-key="last_7_days">{{ __('messages.last_7_days') }}</a>
            <a href="{{ route('admin.dashboard', ['period' => '30days']) }}" class="period-pill {{ $period === '30days' ? 'active' : '' }}" data-lang-key="last_30_days">{{ __('messages.last_30_days') }}</a>
            <a href="{{ route('admin.dashboard', ['period' => 'this_month']) }}" class="period-pill {{ $period === 'this_month' ? 'active' : '' }}" data-lang-key="this_month">{{ __('messages.this_month') }}</a>
        </div>
        <div class="custom-date-group">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="start_date" class="custom-date-input" value="{{ request('start_date', $startDate ? $startDate->format('Y-m-d') : '') }}" aria-label="Start Date">
            <span style="color: var(--text-muted);">&mdash;</span>
            <input type="date" name="end_date" class="custom-date-input" value="{{ request('end_date', $endDate ? $endDate->format('Y-m-d') : '') }}" aria-label="End Date">
            <button type="submit" class="btn-filter-apply" data-lang-key="apply">{{ __('messages.apply') }}</button>
        </div>
    </form>
</div>

<!-- 8 Core KPI Stat Cards Grid (Real Database Metrics Only) -->
<div class="dash-stat-row">
    <!-- 1. Today's Orders (Primary Card 0) -->
    <div class="stat-card">
        <div class="stat-icon-box orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label" data-lang-key="todays_orders">{{ __('messages.todays_orders') }}</span>
            <div class="stat-value">{{ number_format($todayOrdersCount) }}</div>
            @if($ordersGrowthPercent !== null)
                <span class="stat-trend {{ $ordersGrowthPercent >= 0 ? 'up' : 'down' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="{{ $ordersGrowthPercent >= 0 ? '18 15 12 9 6 15' : '6 9 12 15 18 9' }}"/>
                    </svg>
                    {{ $ordersGrowthPercent >= 0 ? '+' : '' }}{{ $ordersGrowthPercent }}% {{ __('messages.vs_yesterday') }}
                </span>
            @else
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    {{ __('messages.live_sync') }}
                </span>
            @endif
        </div>
    </div>

    <!-- 2. Today's Revenue (Primary Card 1) -->
    <div class="stat-card">
        <div class="stat-icon-box green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 6v12"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label" data-lang-key="todays_revenue">{{ __('messages.todays_revenue') }}</span>
            <div class="stat-value">{{ $currency }}{{ number_format($todayRevenueSum, 2) }}</div>
            @if($salesGrowthPercent !== null)
                <span class="stat-trend {{ $salesGrowthPercent >= 0 ? 'up' : 'down' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="{{ $salesGrowthPercent >= 0 ? '18 15 12 9 6 15' : '6 9 12 15 18 9' }}"/>
                    </svg>
                    {{ $salesGrowthPercent >= 0 ? '+' : '' }}{{ $salesGrowthPercent }}% {{ __('messages.vs_yesterday') }}
                </span>
            @else
                <span class="stat-trend up">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    {{ __('messages.live_today') }}
                </span>
            @endif
        </div>
    </div>

    <!-- 3. Pending Orders (Primary Card 2) -->
    <div class="stat-card">
        <div class="stat-icon-box yellow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label" data-lang-key="pending_orders">{{ __('messages.pending_orders') }}</span>
            <div class="stat-value">{{ number_format($pendingOrdersCount) }}</div>
            @if($pendingOrdersCount > 0)
                <span class="stat-trend warning">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                    {{ __('messages.requires_attention') }}
                </span>
            @else
                <span class="stat-trend neutral">All clear</span>
            @endif
        </div>
    </div>

    <!-- 4. Low Stock Items (Primary Card 3) -->
    <div class="stat-card">
        <div class="stat-icon-box red">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label" data-lang-key="low_stock_items">{{ __('messages.low_stock_items') }}</span>
            <div class="stat-value">{{ number_format($lowStockCount) }}</div>
            <a href="{{ route('admin.ingredients') }}" class="stat-trend danger">
                {!! $outOfStockIngredientsCount > 0 ? $outOfStockIngredientsCount . ' out of stock &rarr;' : 'View details &rarr;' !!}
            </a>
        </div>
    </div>

    <!-- 5. Total Customers -->
    <div class="stat-card">
        <div class="stat-icon-box blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label" data-lang-key="total_customers">{{ __('messages.total_customers') }}</span>
            <div class="stat-value">{{ number_format($totalCustomersCount) }}</div>
            <span class="stat-trend neutral">+{{ $newCustomersThisMonth }} this month</span>
        </div>
    </div>

    <!-- 6. Active Products -->
    <div class="stat-card">
        <div class="stat-icon-box purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label" data-lang-key="active_products">{{ __('messages.active_products') }}</span>
            <div class="stat-value">{{ number_format($activeProductsCount) }}</div>
            <span class="stat-trend neutral">{{ $totalProductsCount }} in catalog</span>
        </div>
    </div>

    <!-- 7. Active Productions -->
    <div class="stat-card">
        <div class="stat-icon-box amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label" data-lang-key="active_productions">{{ __('messages.active_productions') }}</span>
            <div class="stat-value">{{ number_format($activeProductionsCount) }}</div>
            <span class="stat-trend neutral">{{ number_format($totalUnitsProduced) }} units baked</span>
        </div>
    </div>

    <!-- 8. Pending Purchase Orders -->
    <div class="stat-card">
        <div class="stat-icon-box teal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <div class="stat-info">
            <span class="stat-label" data-lang-key="pending_purchase_orders">{{ __('messages.pending_purchase_orders') }}</span>
            <div class="stat-value">{{ number_format($pendingPosCount) }}</div>
            <span class="stat-trend {{ $pendingPosCount > 0 ? 'warning' : 'neutral' }}">{{ $orderedPosCount }} ordered</span>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="quick-actions-section">
    <h2 class="section-title">Quick Actions</h2>
    <div class="quick-actions-grid">
        <!-- Add Product -->
        <a href="{{ route('admin.products') }}" class="quick-action-btn cream">
            <div class="quick-action-icon-circle">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <span>Add Product</span>
        </a>

        <!-- New Walk-in Order -->
        <a href="{{ route('cashier.dashboard') }}" class="quick-action-btn blue">
            <div class="quick-action-icon-circle">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </div>
            <span>New Walk-in Order</span>
        </a>

        <!-- New Booking Order -->
        <a href="{{ route('admin.orders') }}" class="quick-action-btn purple">
            <div class="quick-action-icon-circle">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <span>New Booking Order</span>
        </a>
    </div>
</div>

<!-- Sales & Revenue Performance Visual Analytics -->
<div class="chart-card" style="margin-bottom: 1.85rem;">
    <div class="card-header-flex" style="flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-title); margin: 0;" data-lang-key="sales_revenue_performance">
                {{ __('messages.sales_revenue_performance') }}
            </h2>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                Continuous timeline aggregation based on completed POS sales & orders
            </p>
        </div>
        <div style="display: flex; gap: 1.25rem; align-items: center;">
            <div style="text-align: right;">
                <span style="font-size: 0.725rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('messages.total_revenue') }}</span>
                <div style="font-size: 1.25rem; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format($periodTotalRevenue, 2) }}</div>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 0.725rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('messages.orders') }}</span>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-title);">{{ number_format($periodTotalOrders) }}</div>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 0.725rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('messages.average_order_value') }}</span>
                <div style="font-size: 1.25rem; font-weight: 800; color: #ca8a04;">
                    {{ $currency }}{{ $periodTotalOrders > 0 ? number_format($periodTotalRevenue / $periodTotalOrders, 2) : '0.00' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Native SVG Revenue Chart -->
    @php
        $maxRev = max(max($chartRevenues ?: [0]), 10);
        $pointCount = count($chartLabels);
        $svgPoints = [];
        $svgAreaPoints = [];
        $barWidth = $pointCount > 0 ? min(38, max(12, intval(680 / $pointCount))) : 20;

        foreach($chartRevenues as $idx => $r) {
            $x = $pointCount > 1 ? 40 + ($idx / ($pointCount - 1)) * 720 : 400;
            $y = 190 - ($r / $maxRev) * 150;
            $svgPoints[] = "$x,$y";
        }
        $pointsStr = implode(' ', $svgPoints);
        $areaPointsStr = "40,190 " . $pointsStr . " 760,190";
    @endphp
    <div style="width: 100%; height: 230px; position: relative; margin-top: 1rem; overflow: hidden;">
        <svg viewBox="0 0 800 220" preserveAspectRatio="none" style="width: 100%; height: 100%; overflow: visible;">
            <defs>
                <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#d97706" stop-opacity="0.35" />
                    <stop offset="100%" stop-color="#d97706" stop-opacity="0.01" />
                </linearGradient>
            </defs>

            <!-- Horizontal Background Guide Lines -->
            <line x1="40" y1="40" x2="760" y2="40" stroke="rgba(200, 190, 180, 0.25)" stroke-dasharray="4" />
            <line x1="40" y1="90" x2="760" y2="90" stroke="rgba(200, 190, 180, 0.25)" stroke-dasharray="4" />
            <line x1="40" y1="140" x2="760" y2="140" stroke="rgba(200, 190, 180, 0.25)" stroke-dasharray="4" />
            <line x1="40" y1="190" x2="760" y2="190" stroke="rgba(200, 190, 180, 0.45)" stroke-width="1.5" />

            <!-- Y Axis Reference Markers -->
            <text x="32" y="44" font-size="10" fill="#9ca3af" text-anchor="end">${{ number_format($maxRev, 0) }}</text>
            <text x="32" y="119" font-size="10" fill="#9ca3af" text-anchor="end">${{ number_format($maxRev / 2, 0) }}</text>
            <text x="32" y="194" font-size="10" fill="#9ca3af" text-anchor="end">$0</text>

            <!-- Chart Filled Area & Polyline -->
            @if($pointCount > 1)
                <polygon points="{{ $areaPointsStr }}" fill="url(#chartGradient)" />
                <polyline points="{{ $pointsStr }}" fill="none" stroke="#d97706" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
            @endif

            <!-- Data Point Nodes with Tooltips -->
            @foreach($chartRevenues as $idx => $r)
                @php
                    $cx = $pointCount > 1 ? 40 + ($idx / ($pointCount - 1)) * 720 : 400;
                    $cy = 190 - ($r / $maxRev) * 150;
                    $lbl = $chartLabels[$idx] ?? '';
                    $cnt = $chartOrderCounts[$idx] ?? 0;
                @endphp
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="5" fill="#d97706" stroke="var(--card-bg, #ffffff)" stroke-width="2.5" style="cursor: pointer;">
                    <title>{{ $lbl }}: ${{ number_format($r, 2) }} ({{ $cnt }} orders)</title>
                </circle>
                <!-- Date Axis Labels (Thinned if many dates) -->
                @if($pointCount <= 14 || $idx % intval(ceil($pointCount / 10)) === 0 || $idx === $pointCount - 1)
                    <text x="{{ $cx }}" y="212" font-size="10.5" fill="#71717a" text-anchor="middle" font-weight="600">{{ $lbl }}</text>
                @endif
            @endforeach
        </svg>
    </div>
</div>

<!-- Two-Column Grid: Order Status Distribution & Best Selling Products -->
<div class="dash-two-col-grid" style="margin-bottom: 1.85rem;">
    <!-- 1. Order Status Distribution -->
    <div class="bakery-card">
        <div class="card-header-flex">
            <div>
                <h2 data-lang-key="order_status_distribution">{{ __('messages.order_status_distribution') }}</h2>
                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ number_format($totalAllOrders) }} total recorded orders</span>
            </div>
            <a href="{{ route('admin.orders') }}" class="link-action">View all &rarr;</a>
        </div>

        <!-- Proportional Multi-Segment Status Bar -->
        @if($totalAllOrders > 0)
            <div class="dash-status-progress-track" style="height: 12px; display: flex; width: 100%; border-radius: 8px; overflow: hidden; margin: 1rem 0 1.25rem 0; background: #e5e7eb;">
                @if($statusPending > 0)
                    <div style="width: {{ ($statusPending / $totalAllOrders) * 100 }}%; background: #ca8a04;" title="Pending: {{ $statusPending }}"></div>
                @endif
                @if($statusPreparing > 0)
                    <div style="width: {{ ($statusPreparing / $totalAllOrders) * 100 }}%; background: #2563eb;" title="Baking: {{ $statusPreparing }}"></div>
                @endif
                @if($statusReady > 0)
                    <div style="width: {{ ($statusReady / $totalAllOrders) * 100 }}%; background: #9333ea;" title="Ready: {{ $statusReady }}"></div>
                @endif
                @if($statusDelivery > 0)
                    <div style="width: {{ ($statusDelivery / $totalAllOrders) * 100 }}%; background: #4f46e5;" title="Delivery: {{ $statusDelivery }}"></div>
                @endif
                @if($statusCompleted > 0)
                    <div style="width: {{ ($statusCompleted / $totalAllOrders) * 100 }}%; background: #16a34a;" title="Completed: {{ $statusCompleted }}"></div>
                @endif
                @if($statusCancelled > 0)
                    <div style="width: {{ ($statusCancelled / $totalAllOrders) * 100 }}%; background: #dc2626;" title="Cancelled: {{ $statusCancelled }}"></div>
                @endif
            </div>
        @endif

        <div class="status-dist-grid">
            <div class="status-dist-box">
                <span class="status-dist-label">Pending</span>
                <span class="status-dist-val" style="color: #ca8a04;">{{ number_format($statusPending) }}</span>
            </div>
            <div class="status-dist-box">
                <span class="status-dist-label">Baking</span>
                <span class="status-dist-val" style="color: #2563eb;">{{ number_format($statusPreparing) }}</span>
            </div>
            <div class="status-dist-box">
                <span class="status-dist-label">Ready</span>
                <span class="status-dist-val" style="color: #9333ea;">{{ number_format($statusReady) }}</span>
            </div>
            <div class="status-dist-box">
                <span class="status-dist-label">Out for Delivery</span>
                <span class="status-dist-val" style="color: #4f46e5;">{{ number_format($statusDelivery) }}</span>
            </div>
            <div class="status-dist-box">
                <span class="status-dist-label">Completed</span>
                <span class="status-dist-val" style="color: #16a34a;">{{ number_format($statusCompleted) }}</span>
            </div>
            <div class="status-dist-box">
                <span class="status-dist-label">Cancelled</span>
                <span class="status-dist-val" style="color: #dc2626;">{{ number_format($statusCancelled) }}</span>
            </div>
        </div>
    </div>

    <!-- 2. Best Selling Products (Completed Orders Only) -->
    <div class="bakery-card">
        <div class="card-header-flex">
            <div>
                <h2 data-lang-key="best_selling_products">{{ __('messages.best_selling_products') }}</h2>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Top items ranked by units sold from completed sales</span>
            </div>
            <a href="{{ route('admin.products') }}" class="link-action">Catalog &rarr;</a>
        </div>

        <div class="bestseller-list">
            @forelse($topProducts as $index => $prod)
                @php
                    $rankClass = match($index) {
                        0 => 'gold',
                        1 => 'silver',
                        2 => 'bronze',
                        default => ''
                    };
                    $percent = $maxSold > 0 ? round(($prod->total_sold / $maxSold) * 100) : 0;
                @endphp
                <div class="bestseller-item">
                    <div class="bestseller-info-row">
                        <div style="display: flex; align-items: center; min-width: 0; gap: 0.4rem;">
                            <span class="bestseller-rank {{ $rankClass }}">{{ $index + 1 }}</span>
                            <span style="font-weight: 700; color: var(--text-title); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                {{ $prod->name }}
                            </span>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <span style="font-weight: 800; color: var(--text-title);">{{ number_format($prod->total_sold) }} {{ __('messages.units_sold') }}</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">{{ $currency }}{{ number_format($prod->total_revenue, 2) }}</span>
                        </div>
                    </div>
                    <div class="bestseller-progress-track">
                        <div class="bestseller-progress-fill" style="width: {{ $percent }}%;"></div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 2.5rem 1rem; font-size: 0.9rem;">
                    {{ __('messages.no_bestsellers') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Two-Column Grid: Stock & Expiry Alerts and Operations Overview -->
<div class="dash-two-col-grid" style="margin-bottom: 1.85rem;">
    <!-- Stock & Expiry Alerts -->
    <div class="bakery-card">
        <div class="card-header-flex">
            <div>
                <h2 data-lang-key="stock_expiry_alerts">{{ __('messages.stock_expiry_alerts') }}</h2>
                <span style="font-size: 0.8rem; color: var(--text-muted);">
                    @if($expiredIngredientsCount > 0)
                        <span style="color: #dc2626; font-weight: 700;">{{ $expiredIngredientsCount }} expired!</span> &bull;
                    @endif
                    Critical inventory requiring replenishment
                </span>
            </div>
            <a href="{{ route('admin.ingredients') }}" class="link-action">Manage &rarr;</a>
        </div>

        <div class="stock-alerts-list">
            <!-- Low Stock Ingredients -->
            @forelse($lowStockIngredients as $ing)
                @php
                    $isCritical = $ing->quantity <= 0 || $ing->quantity <= ($ing->minimum_quantity / 2);
                    $tagClass = $isCritical ? 'critical' : 'low';
                    $tagLabel = $ing->quantity <= 0 ? 'Out of Stock' : ($isCritical ? 'Critical' : 'Low Stock');
                @endphp
                <div class="stock-alert-item">
                    <div class="stock-item-left">
                        <div class="stock-item-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="stock-item-info">
                            <div class="item-name">{{ $ing->name }}</div>
                            <div class="item-qty">{{ $ing->quantity }} {{ $ing->unit }} remaining (Min: {{ $ing->minimum_quantity }})</div>
                        </div>
                    </div>
                    <span class="alert-tag {{ $tagClass }}">{{ $tagLabel }}</span>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 1.5rem; font-size: 0.85rem;">
                    {{ __('messages.no_low_stock') }}
                </div>
            @endforelse

            <!-- Expiring Soon Ingredients -->
            @if($expiringSoonIngredients->count() > 0)
                <div style="margin-top: 1rem; border-top: 1px dashed var(--border-color); padding-top: 0.75rem;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.04em;">Expiring Soon (&le; 7 Days)</span>
                    @foreach($expiringSoonIngredients as $exp)
                        <div class="stock-alert-item" style="margin-top: 0.5rem;">
                            <div class="stock-item-left">
                                <div class="stock-item-icon" style="background: #fef3c7; color: #b45309;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <div class="stock-item-info">
                                    <div class="item-name">{{ $exp->name }}</div>
                                    <div class="item-qty">Expires {{ \Carbon\Carbon::parse($exp->expiry_date)->format('M d, Y') }} ({{ $exp->quantity }} {{ $exp->unit }})</div>
                                </div>
                            </div>
                            <span class="expiry-tag soon">Expiring Soon</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card-bottom-action-container">
            <a href="{{ route('admin.ingredients') }}" class="btn-card-outline" data-lang-key="view_all_stock">{{ __('messages.view_all_stock') }}</a>
        </div>
    </div>

    <!-- Procurement & Operations Summary -->
    <div class="bakery-card">
        <div class="card-header-flex">
            <div>
                <h2 data-lang-key="procurement_production_overview">{{ __('messages.procurement_production_overview') }}</h2>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Suppliers, Purchase Orders & Production KPIs</span>
            </div>
            <a href="{{ route('admin.purchase-orders.index') }}" class="link-action">Procurement &rarr;</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.25rem;">
            <div class="status-dist-box">
                <span class="status-dist-label">Active Suppliers</span>
                <span class="status-dist-val" style="color: var(--text-title);">{{ number_format($suppliersCount) }}</span>
                <span style="font-size: 0.75rem; color: var(--text-muted);">Partners in supply chain</span>
            </div>
            <div class="status-dist-box">
                <span class="status-dist-label">Total Procurement Spend</span>
                <span class="status-dist-val" style="color: #16a34a;">{{ $currency }}{{ number_format($totalProcurementSpend, 2) }}</span>
                <span style="font-size: 0.75rem; color: var(--text-muted);">From {{ $receivedPosCount }} received POs</span>
            </div>
            <div class="status-dist-box">
                <span class="status-dist-label">Active Productions</span>
                <span class="status-dist-val" style="color: #d97706;">{{ number_format($activeProductionsCount) }}</span>
                <span style="font-size: 0.75rem; color: var(--text-muted);">Batches in bakery oven</span>
            </div>
            <div class="status-dist-box">
                <span class="status-dist-label">Total Units Produced</span>
                <span class="status-dist-val" style="color: #9333ea;">{{ number_format($totalUnitsProduced) }}</span>
                <span style="font-size: 0.75rem; color: var(--text-muted);">From completed batches</span>
            </div>
        </div>

        <!-- VIP Customers & Loyalty -->
        <div class="dash-loyalty-box" style="padding: 1rem; background: #faf6f0; border-radius: 12px; border: 1px solid rgba(238, 233, 224, 0.8); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-weight: 700; color: var(--text-title); font-size: 0.9rem;">Customer Loyalty Program</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">{{ number_format($vipCustomersCount) }} VIP Members &bull; {{ number_format($totalLoyaltyPoints) }} Reward Points Issued</div>
            </div>
            <a href="{{ route('admin.customers.index') }}" class="btn-card-outline" style="padding: 0.4rem 0.85rem; font-size: 0.8rem;">View Customers</a>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="bakery-card">
    <div class="card-header-flex">
        <h2>Recent Orders</h2>
        <a href="{{ route('admin.orders') }}" class="link-action">View all orders &rarr;</a>
    </div>

    <div class="table-responsive-wrapper">
        <table class="recent-orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Type</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    @php
                        $statusClass = strtolower($order->order_status ?? 'completed');
                        $statusBadge = match($statusClass) {
                            'completed' => '<span class="status-pill completed">Completed</span>',
                            'preparing', 'baking', 'in progress', 'processing' => '<span class="status-pill inprogress">In Baking</span>',
                            'ready_for_pickup', 'ready' => '<span class="status-pill completed">Ready</span>',
                            'out_for_delivery' => '<span class="status-pill inprogress">Delivering</span>',
                            'cancelled' => '<span class="status-pill danger" style="background:#fee2e2; color:#dc2626;">Cancelled</span>',
                            default => '<span class="status-pill pending">Pending</span>',
                        };
                        $custName = $order->customer ? $order->customer->name : ($order->customer_name ?: 'Walk-in Customer');
                    @endphp
                    <tr>
                        <td style="font-weight: 800;">#{{ $order->id }}</td>
                        <td><span class="type-pill walkin">{{ strtoupper($order->payment_type ?? $order->order_type ?? 'POS') }}</span></td>
                        <td>{{ $custName }}</td>
                        <td style="font-weight: 700;">{{ $currency }}{{ number_format($order->total, 2) }}</td>
                        <td>{!! $statusBadge !!}</td>
                        <td>
                            <div class="action-btn-group">
                                <button type="button" class="table-icon-btn btn-view-order" 
                                    data-order='{"id":{{ $order->id }},"order_number":"#ORD-{{ $order->id }}","customer_name":"{{ addslashes($custName) }}","order_type":"{{ strtoupper($order->order_type ?? "STORE POS") }}","items_summary":"Assorted Bakery Items","total_amount":"{{ $order->total }}","status":"{{ strtoupper($order->order_status ?? "COMPLETED") }}"}' 
                                    title="View Order Details">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;" data-lang-key="no_recent_orders">
                            {{ __('messages.no_recent_orders') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-bottom-action-container">
        <a href="{{ route('admin.orders') }}" class="btn-card-outline" data-lang-key="view_all_orders">{{ __('messages.view_all_orders') }}</a>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal-backdrop" id="view_order_modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Order Details</h3>
            <button type="button" class="modal-close" id="btn_close_view_modal">&times;</button>
        </div>
        <div class="modal-body" style="padding: 1.5rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Order Code</label>
                    <div id="view_order_id" style="font-weight: 800; font-size: 1.1rem; color: var(--text-title);">-</div>
                </div>
                <div>
                    <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Customer</label>
                    <div id="view_order_customer" style="font-weight: 700; color: var(--text-title);">-</div>
                </div>
                <div>
                    <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Order Channel</label>
                    <div id="view_order_type" style="font-weight: 600;">-</div>
                </div>
                <div>
                    <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Status</label>
                    <div id="view_order_status" style="font-weight: 700; color: var(--primary);">-</div>
                </div>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Items Summary</label>
                <div id="view_order_items" class="modal-item-summary-box" style="padding: 0.75rem; background: #faf6f0; border-radius: 8px; font-size: 0.875rem;">-</div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <span style="font-weight: 700; font-size: 1rem;">Total Amount:</span>
                <span id="view_order_total" style="font-weight: 800; font-size: 1.35rem; color: #16a34a;">-</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endpush
