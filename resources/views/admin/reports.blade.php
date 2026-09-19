@extends('layouts.app')

@section('title', 'Report Management - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/reports.css') }}?v={{ time() }}">
@endpush

@section('content')
<!-- Print Only Official Header -->
<div class="print-only-header">
    <div class="print-bakery-name">{{ $bakeryName }}</div>
    <div class="print-report-title">{{ strtoupper(str_replace('-', ' ', $type)) }} AUDIT REPORT</div>
    <div class="print-meta-row">
        <span>Period: {{ $startDate ? $startDate->format('M d, Y') : 'Start' }} &mdash; {{ $endDate ? $endDate->format('M d, Y') : 'End' }}</span>
        <span>Generated: {{ now()->format('M d, Y H:i:s') }}</span>
        <span>Status: Official Transactional Record</span>
    </div>
</div>

<!-- Report Dashboard Header -->
<div class="reports-header">
    <div>
        <h2 style="font-size: 1.45rem; font-weight: 800; margin: 0; color: var(--text-title); letter-spacing: -0.02em;" data-lang-key="reports_title">
            {{ __('messages.reports_title') }}
        </h2>
        <p class="reports-subtitle" data-lang-key="reports_subtitle">
            {{ __('messages.reports_subtitle') }}
        </p>
    </div>
    <div class="report-export-group">
        <span class="report-period-badge">
            <span class="live-dot"></span>
            <span>Live System Sync</span>
        </span>
        <x-button variant="secondary" :href="route('admin.reports.export.pdf', request()->all())" class="btn-export-pdf" id="btn_export_pdf" title="Export PDF">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <span data-lang-key="export_pdf">{{ __('messages.export_pdf') }}</span>
        </x-button>
        <x-button variant="secondary" :href="route('admin.reports.export.excel', request()->all())" class="btn-export-excel" id="btn_export_excel" title="Export Excel / CSV">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
            <span data-lang-key="export_excel">{{ __('messages.export_excel') }}</span>
        </x-button>
        <x-button variant="secondary" class="btn-print-report" id="btn_print_report" onclick="window.print();" title="Print Layout">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            <span data-lang-key="print_report">{{ __('messages.print_report') }}</span>
        </x-button>
    </div>
</div>

<!-- Global Timeframe Filter Bar -->
<div class="dash-filter-bar" style="margin-bottom: 1.25rem;">
    <div class="filter-bar-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V19l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4z"/></svg>
        <span data-lang-key="analytics_timeframe">{{ __('messages.analytics_timeframe') }}</span>
        @if($startDate && $endDate)
            <span class="filter-date-badge">{{ $startDate->format('M d, Y') }} &mdash; {{ $endDate->format('M d, Y') }}</span>
        @endif
    </div>
    <form method="GET" action="{{ route('admin.reports') }}" class="filter-form">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="period-pill-group">
            <a href="{{ route('admin.reports', array_merge(request()->except('page'), ['type' => $type, 'period' => 'today'])) }}" class="period-pill {{ $period === 'today' ? 'active' : '' }}" data-lang-key="today">{{ __('messages.today') }}</a>
            <a href="{{ route('admin.reports', array_merge(request()->except('page'), ['type' => $type, 'period' => 'yesterday'])) }}" class="period-pill {{ $period === 'yesterday' ? 'active' : '' }}" data-lang-key="yesterday">{{ __('messages.yesterday') }}</a>
            <a href="{{ route('admin.reports', array_merge(request()->except('page'), ['type' => $type, 'period' => '7days'])) }}" class="period-pill {{ $period === '7days' ? 'active' : '' }}" data-lang-key="last_7_days">{{ __('messages.last_7_days') }}</a>
            <a href="{{ route('admin.reports', array_merge(request()->except('page'), ['type' => $type, 'period' => '30days'])) }}" class="period-pill {{ $period === '30days' ? 'active' : '' }}" data-lang-key="last_30_days">{{ __('messages.last_30_days') }}</a>
            <a href="{{ route('admin.reports', array_merge(request()->except('page'), ['type' => $type, 'period' => 'this_month'])) }}" class="period-pill {{ $period === 'this_month' ? 'active' : '' }}" data-lang-key="this_month">{{ __('messages.this_month') }}</a>
            <a href="{{ route('admin.reports', array_merge(request()->except('page'), ['type' => $type, 'period' => 'last_month'])) }}" class="period-pill {{ $period === 'last_month' ? 'active' : '' }}" data-lang-key="last_month">{{ __('messages.last_month') }}</a>
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

<!-- 10 Report Type Segmented Navigation Tabs -->
<div class="report-nav-tabs-container">
    <div class="report-nav-tabs">
        @php
            $tabParams = request()->except(['page', 'type']);
            $tabs = [
                'sales'       => ['icon' => '<circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 6v12"/>', 'label' => 'report_sales'],
                'revenue'     => ['icon' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>', 'label' => 'report_revenue'],
                'orders'      => ['icon' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>', 'label' => 'report_orders'],
                'products'    => ['icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>', 'label' => 'report_products'],
                'inventory'   => ['icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>', 'label' => 'report_inventory'],
                'low-stock'   => ['icon' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 'label' => 'report_low_stock'],
                'production'  => ['icon' => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>', 'label' => 'report_production'],
                'customers'   => ['icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', 'label' => 'report_customers'],
                'purchases'   => ['icon' => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>', 'label' => 'report_purchases'],
                'profit-loss' => ['icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>', 'label' => 'report_profit_loss'],
            ];
        @endphp

        @foreach($tabs as $tabKey => $tabMeta)
            <a href="{{ route('admin.reports', array_merge($tabParams, ['type' => $tabKey])) }}" 
               class="report-tab-btn {{ $type === $tabKey ? 'active' : '' }}" 
               id="tab_{{ str_replace('-', '_', $tabKey) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">{!! $tabMeta['icon'] !!}</svg>
                <span data-lang-key="{{ $tabMeta['label'] }}">{{ __('messages.' . $tabMeta['label']) }}</span>
            </a>
        @endforeach
    </div>
</div>

<!-- =========================================================================
     DYNAMIC 4 KPI STAT CARDS (TAILORED TO ACTIVE REPORT)
     ========================================================================= -->
<div class="reports-summary-grid">
    @if($type === 'sales')
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="total_sales">{{ __('messages.total_sales') }}</div>
            <div class="report-stat-val val-green">{{ $currency }}{{ number_format($reportData['totalSalesRevenue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Net: {{ $currency }}{{ number_format($reportData['netSales'], 2) }}</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="number_of_sales">{{ __('messages.number_of_sales') }}</div>
            <div class="report-stat-val val-default">{{ number_format($reportData['totalSalesCount']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Completed sales</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="average_sale_value">{{ __('messages.average_sale_value') }}</div>
            <div class="report-stat-val val-amber">{{ $currency }}{{ number_format($reportData['avgSaleValue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Per transaction</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="total_discount">{{ __('messages.total_discount') }}</div>
            <div class="report-stat-val val-blue">{{ $currency }}{{ number_format($reportData['totalDiscount'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Tax: {{ $currency }}{{ number_format($reportData['totalTax'], 2) }}</span>
        </div>
    @elseif($type === 'revenue')
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="total_revenue">{{ __('messages.total_revenue') }}</div>
            <div class="report-stat-val val-green">{{ $currency }}{{ number_format($reportData['totalRevenue'], 2) }}</div>
            @if($reportData['revenueGrowthPercent'] !== null)
                <span style="font-size: 0.75rem; font-weight: 700; color: {{ $reportData['revenueGrowthPercent'] >= 0 ? '#16a34a' : '#dc2626' }};">
                    {{ $reportData['revenueGrowthPercent'] >= 0 ? '+' : '' }}{{ $reportData['revenueGrowthPercent'] }}% vs prev period
                </span>
            @else
                <span style="font-size: 0.75rem; color: #71717a;">Selected timeframe</span>
            @endif
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="average_order_value">{{ __('messages.average_order_value') }}</div>
            <div class="report-stat-val val-amber">{{ $currency }}{{ number_format($reportData['avgOrderValue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">{{ number_format($reportData['totalOrders']) }} orders</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Store POS Cash</div>
            <div class="report-stat-val val-default">{{ $currency }}{{ number_format($reportData['cashRevenue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">In-store cash sales</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Card & Digital KHQR</div>
            <div class="report-stat-val val-blue">{{ $currency }}{{ number_format($reportData['cardRevenue'] + $reportData['qrRevenue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Electronic payments</span>
        </div>
    @elseif($type === 'orders')
        <div class="report-stat-card">
            <div class="report-stat-title">Total Orders Recorded</div>
            <div class="report-stat-val val-default">{{ number_format($reportData['totalOrders']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Gross: {{ $currency }}{{ number_format($reportData['totalOrderAmount'], 2) }}</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Completed Orders</div>
            <div class="report-stat-val val-green">{{ number_format($reportData['completedOrders']) }}</div>
            <span style="font-size: 0.75rem; color: #16a34a; font-weight: 700;">Rate: {{ $reportData['completionRate'] }}%</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">In Progress / Baking</div>
            <div class="report-stat-val val-amber">{{ number_format($reportData['pendingOrders'] + $reportData['preparingOrders']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Pending / Baking</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Cancelled Orders</div>
            <div class="report-stat-val val-blue" style="color: #dc2626;">{{ number_format($reportData['cancelledOrders']) }}</div>
            <span style="font-size: 0.75rem; color: #dc2626; font-weight: 700;">Rate: {{ $reportData['cancellationRate'] }}%</span>
        </div>
    @elseif($type === 'products')
        <div class="report-stat-card">
            <div class="report-stat-title">Units Sold (Completed)</div>
            <div class="report-stat-val val-green">{{ number_format($reportData['totalUnitsSold']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Delivered bakery units</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Product Sales Revenue</div>
            <div class="report-stat-val val-default">{{ $currency }}{{ number_format($reportData['totalRevenue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Across all categories</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Active Products Sold</div>
            <div class="report-stat-val val-amber">{{ number_format($reportData['totalDistinctProducts']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">With completed sales</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Top Product Leader</div>
            <div class="report-stat-val val-blue" style="font-size: 1.15rem; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                {{ count($reportData['top5']) > 0 ? $reportData['top5'][0]->product_name : 'No sales' }}
            </div>
            <span style="font-size: 0.75rem; color: #71717a;">{{ count($reportData['top5']) > 0 ? $reportData['top5'][0]->total_units_sold . ' units sold' : 'N/A' }}</span>
        </div>
    @elseif($type === 'inventory')
        <div class="report-stat-card">
            <div class="report-stat-title">Total Ingredients</div>
            <div class="report-stat-val val-default">{{ number_format($reportData['totalIngredients']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Physical inventory items</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Total Inventory Value</div>
            <div class="report-stat-val val-green">{{ $currency }}{{ number_format($reportData['totalInventoryValue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">SUM(quantity &times; unit cost)</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Low Stock Alert</div>
            <div class="report-stat-val val-amber">{{ number_format($reportData['lowStockCount']) }}</div>
            <span style="font-size: 0.75rem; color: #dc2626;">Out of stock: {{ number_format($reportData['outOfStockCount']) }}</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Expired / Expiring Soon</div>
            <div class="report-stat-val val-blue" style="color: #dc2626;">{{ number_format($reportData['expiredCount']) }} / {{ number_format($reportData['expiringSoonCount']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Expired / Next 7 days</span>
        </div>
    @elseif($type === 'low-stock')
        <div class="report-stat-card">
            <div class="report-stat-title">Items Requiring Reorder</div>
            <div class="report-stat-val val-amber">{{ number_format($reportData['totalNeedingReorder']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">At or below minimum level</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Out of Stock (Zero)</div>
            <div class="report-stat-val val-blue" style="color: #dc2626;">{{ number_format($reportData['outOfStockCount']) }}</div>
            <span style="font-size: 0.75rem; color: #dc2626; font-weight: 700;">Critical replenishment</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Low Stock Remaining</div>
            <div class="report-stat-val val-default">{{ number_format($reportData['lowStockCount']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Above zero, below threshold</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="estimated_restock_cost">{{ __('messages.estimated_restock_cost') }}</div>
            <div class="report-stat-val val-green">{{ $currency }}{{ number_format($reportData['estimatedRestockCost'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">To reach minimum thresholds</span>
        </div>
    @elseif($type === 'production')
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="total_batches">{{ __('messages.total_batches') }}</div>
            <div class="report-stat-val val-default">{{ number_format($reportData['totalBatches']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Production runs scheduled</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Units Produced</div>
            <div class="report-stat-val val-green">{{ number_format($reportData['totalUnitsProduced']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">From completed batches</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Completed Batches</div>
            <div class="report-stat-val val-amber">{{ number_format($reportData['completedBatches']) }}</div>
            <span style="font-size: 0.75rem; color: #16a34a; font-weight: 700;">Rate: {{ $reportData['completionRate'] }}%</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">In Progress / Kitchen</div>
            <div class="report-stat-val val-blue">{{ number_format($reportData['inProgressBatches'] + $reportData['scheduledBatches']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Cancelled: {{ number_format($reportData['cancelledBatches']) }}</span>
        </div>
    @elseif($type === 'customers')
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="total_customers">{{ __('messages.total_customers') }}</div>
            <div class="report-stat-val val-default">{{ number_format($reportData['totalCustomers']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Total customer accounts</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">New in Selected Period</div>
            <div class="report-stat-val val-blue">+{{ number_format($reportData['newCustomersPeriod']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Customer acquisition</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="total_spent">{{ __('messages.total_spent') }}</div>
            <div class="report-stat-val val-green">{{ $currency }}{{ number_format($reportData['totalCustomerSpend'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Completed customer purchases</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Average Customer Spend</div>
            <div class="report-stat-val val-amber">{{ $currency }}{{ number_format($reportData['avgCustomerSpend'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Active purchasing customers</span>
        </div>
    @elseif($type === 'purchases')
        <div class="report-stat-card">
            <div class="report-stat-title">Active Suppliers</div>
            <div class="report-stat-val val-default">{{ number_format($reportData['totalSuppliers']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Procurement partners</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Purchase Orders</div>
            <div class="report-stat-val val-blue">{{ number_format($reportData['totalPos']) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Draft: {{ number_format($reportData['draftPos']) }} | Ordered: {{ number_format($reportData['orderedPos']) }}</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="total_procurement_spend">{{ __('messages.total_procurement_spend') }}</div>
            <div class="report-stat-val val-green">{{ $currency }}{{ number_format($reportData['totalPurchaseSpend'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #16a34a; font-weight: 700;">Received POs only (Actual Spend)</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title">Average Received PO</div>
            <div class="report-stat-val val-amber">{{ $currency }}{{ number_format($reportData['avgPoValue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">{{ number_format($reportData['receivedPos']) }} received orders</span>
        </div>
    @elseif($type === 'profit-loss')
        <div class="report-stat-card">
            <div class="report-stat-title">Sales Revenue</div>
            <div class="report-stat-val val-green">{{ $currency }}{{ number_format($reportData['salesRevenue'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">From completed orders</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="cost_of_goods_sold">{{ __('messages.cost_of_goods_sold') }}</div>
            <div class="report-stat-val val-blue" style="color: #dc2626;">{{ $currency }}{{ number_format($reportData['productCogs'], 2) }}</div>
            <span style="font-size: 0.75rem; color: #71717a;">Product costs &times; units sold</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="gross_profit">{{ __('messages.gross_profit') }}</div>
            <div class="report-stat-val {{ $reportData['grossProfit'] >= 0 ? 'val-green' : 'val-blue' }}" style="{{ $reportData['grossProfit'] < 0 ? 'color: #dc2626;' : '' }}">
                {{ $currency }}{{ number_format($reportData['grossProfit'], 2) }}
            </div>
            <span style="font-size: 0.75rem; color: #71717a;" data-lang-key="gross_profit_note">{{ __('messages.gross_profit_note') }}</span>
        </div>
        <div class="report-stat-card">
            <div class="report-stat-title" data-lang-key="profit_margin">{{ __('messages.profit_margin') }}</div>
            <div class="report-stat-val val-amber">{{ $reportData['profitMargin'] }}%</div>
            <span style="font-size: 0.75rem; color: #71717a;">(Gross Profit / Revenue) &times; 100</span>
        </div>
    @endif
</div>

<!-- =========================================================================
     CHARTS / VISUAL ANALYTICS COMPONENT
     ========================================================================= -->
@if($type === 'sales')
    <div class="chart-card" style="margin-bottom: 1.85rem;" id="sales_payment_distribution_card">
        <div class="card-header-flex" style="flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-title); margin: 0;" data-lang-key="payment_method_breakdown">
                    {{ __('messages.payment_method_breakdown') }}
                </h2>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                    Completed sales volume & transaction counts grouped by payment channel
                </p>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            @php
                $pmMap = $reportData['paymentMethods']->keyBy('payment_method');
                $channels = [
                    'cash'    => ['label' => 'Cash (In-Store)', 'color' => '#16a34a'],
                    'card'    => ['label' => 'Credit / Debit Card', 'color' => '#2563eb'],
                    'qr_code' => ['label' => 'KHQR / Digital Pay', 'color' => '#d97706'],
                ];
            @endphp
            @foreach($channels as $chanKey => $chanMeta)
                @php
                    $rec = $pmMap->get($chanKey);
                    $chanTotal = $rec ? (float) $rec->total_amount : 0.0;
                    $chanCount = $rec ? (int) $rec->count : 0;
                    $chanPercent = $reportData['totalSalesRevenue'] > 0 ? round(($chanTotal / $reportData['totalSalesRevenue']) * 100, 1) : 0;
                @endphp
                <div class="report-inner-card" style="padding: 1rem 1.25rem; border-left: 4px solid {{ $chanMeta['color'] }};">
                    <div style="font-size: 0.775rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ $chanMeta['label'] }}</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: {{ $chanMeta['color'] }}; margin: 0.35rem 0 0.2rem;">
                        {{ $currency }}{{ number_format($chanTotal, 2) }}
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted);">
                        <span>{{ number_format($chanCount) }} transactions</span>
                        <span style="font-weight: 700; color: var(--text-title);">{{ $chanPercent }}%</span>
                    </div>
                    <div class="report-progress-track" style="width: 100%; height: 6px; margin-top: 0.5rem;">
                        <div style="width: {{ $chanPercent }}%; height: 100%; background: {{ $chanMeta['color'] }}; border-radius: 3px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@elseif($type === 'revenue')
    <div class="chart-card" style="margin-bottom: 1.85rem;">
        <div class="card-header-flex" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-title); margin: 0;" data-lang-key="revenue_by_day">
                    {{ __('messages.revenue_by_day') }}
                </h2>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                    Daily revenue trends from completed sales
                </p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 0.725rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Period Revenue</span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format($reportData['totalRevenue'], 2) }}</div>
            </div>
        </div>

        @php
            $revValues = $reportData['chartRevenues'];
            $maxRev = max(max($revValues ?: [0]), 10);
            $pointCount = count($reportData['chartLabels']);
            $svgPoints = [];
            $svgAreaPoints = [];

            foreach($revValues as $idx => $r) {
                $x = $pointCount > 1 ? 40 + ($idx / ($pointCount - 1)) * 720 : 400;
                $y = 190 - ($r / $maxRev) * 150;
                $svgPoints[] = "$x,$y";
            }
            $pointsStr = implode(' ', $svgPoints);
            $areaPointsStr = "40,190 " . $pointsStr . " 760,190";
        @endphp

        <div style="width: 100%; height: 230px; position: relative; margin-top: 1rem; overflow: hidden;">
            <svg viewBox="0 0 800 220" preserveAspectRatio="none" style="width: 100%; height: 100%; overflow: visible;" id="revenue_svg_chart">
                <defs>
                    <linearGradient id="reportChartGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#d97706" stop-opacity="0.35" />
                        <stop offset="100%" stop-color="#d97706" stop-opacity="0.01" />
                    </linearGradient>
                </defs>

                <line x1="40" y1="40" x2="760" y2="40" stroke="rgba(200, 190, 180, 0.25)" stroke-dasharray="4" />
                <line x1="40" y1="90" x2="760" y2="90" stroke="rgba(200, 190, 180, 0.25)" stroke-dasharray="4" />
                <line x1="40" y1="140" x2="760" y2="140" stroke="rgba(200, 190, 180, 0.25)" stroke-dasharray="4" />
                <line x1="40" y1="190" x2="760" y2="190" stroke="rgba(200, 190, 180, 0.45)" stroke-width="1.5" />

                <text x="32" y="44" font-size="10" fill="#9ca3af" text-anchor="end">{{ $currency }}{{ number_format($maxRev, 0) }}</text>
                <text x="32" y="119" font-size="10" fill="#9ca3af" text-anchor="end">{{ $currency }}{{ number_format($maxRev / 2, 0) }}</text>
                <text x="32" y="194" font-size="10" fill="#9ca3af" text-anchor="end">{{ $currency }}0</text>

                @if($pointCount > 1)
                    <polygon points="{{ $areaPointsStr }}" fill="url(#reportChartGradient)" />
                    <polyline points="{{ $pointsStr }}" fill="none" stroke="#d97706" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                @endif

                @foreach($revValues as $idx => $r)
                    @php
                        $cx = $pointCount > 1 ? 40 + ($idx / ($pointCount - 1)) * 720 : 400;
                        $cy = 190 - ($r / $maxRev) * 150;
                        $lbl = $reportData['chartLabels'][$idx] ?? '';
                        $cnt = $reportData['chartOrderCounts'][$idx] ?? 0;
                    @endphp
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="4.5" fill="#d97706" stroke="var(--card-bg, #ffffff)" stroke-width="2" style="cursor: pointer;">
                        <title>{{ $lbl }}: {{ $currency }}{{ number_format($r, 2) }} ({{ $cnt }} orders)</title>
                    </circle>
                    @if($pointCount <= 14 || $idx % intval(ceil($pointCount / 10)) === 0 || $idx === $pointCount - 1)
                        <text x="{{ $cx }}" y="212" font-size="10" fill="#71717a" text-anchor="middle" font-weight="600">{{ $lbl }}</text>
                    @endif
                @endforeach
            </svg>
        </div>
    </div>
@elseif($type === 'orders')
    <div class="chart-card" style="margin-bottom: 1.85rem;" id="orders_status_distribution_card">
        <div class="card-header-flex" style="flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-title); margin: 0;">Order Status Distribution</h2>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Pipeline status across all orders placed in the selected timeframe</p>
            </div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-secondary);">
                Completion Rate: <span style="color: #16a34a;">{{ $reportData['completionRate'] }}%</span> | Cancellation: <span style="color: #dc2626;">{{ $reportData['cancellationRate'] }}%</span>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem;">
            @php
                $orderStatuses = [
                    ['label' => 'Completed', 'count' => $reportData['completedOrders'], 'color' => '#16a34a'],
                    ['label' => 'Preparing / Baking', 'count' => $reportData['preparingOrders'], 'color' => '#f59e0b'],
                    ['label' => 'Ready for Pickup', 'count' => $reportData['readyOrders'], 'color' => '#0ea5e9'],
                    ['label' => 'Pending', 'count' => $reportData['pendingOrders'], 'color' => '#64748b'],
                    ['label' => 'Cancelled', 'count' => $reportData['cancelledOrders'], 'color' => '#dc2626'],
                ];
            @endphp
            @foreach($orderStatuses as $st)
                @php $stPct = $reportData['totalOrders'] > 0 ? round(($st['count'] / $reportData['totalOrders']) * 100, 1) : 0; @endphp
                <div class="report-inner-card" style="padding: 1rem; border-top: 3px solid {{ $st['color'] }};">
                    <div style="font-size: 0.775rem; font-weight: 700; color: var(--text-muted);">{{ $st['label'] }}</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-title); margin: 0.25rem 0;">{{ number_format($st['count']) }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                        <span>{{ $stPct }}% of total</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@elseif($type === 'products')
    @if(count($reportData['top5']) > 0)
        <div class="chart-card" style="margin-bottom: 1.85rem;" id="products_top5_card">
            <div class="card-header-flex" style="flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-title); margin: 0;">Top 5 Best Selling Bakery Items</h2>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Highest volume bakery items delivered in completed customer orders</p>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                @foreach($reportData['top5'] as $rank => $prod)
                    <div class="report-inner-card" style="padding: 1rem; position: relative;">
                        <span style="position: absolute; top: 10px; right: 12px; font-size: 0.75rem; font-weight: 800; color: var(--accent-brown, #d97736); background: rgba(217,119,54,0.15); border-radius: 50%; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center;">#{{ $rank + 1 }}</span>
                        <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">{{ $prod->category_name ?? 'Bakery Item' }}</div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-title); margin: 0.2rem 0 0.4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $prod->product_name }}">
                            {{ $prod->product_name }}
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.825rem; font-weight: 700;">
                            <span style="color: #16a34a;">{{ number_format($prod->total_units_sold) }} units</span>
                            <span style="color: var(--text-title);">{{ $currency }}{{ number_format($prod->total_revenue, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@elseif($type === 'profit-loss')
    <div class="bakery-card" style="margin-bottom: 1.85rem; border-left: 4px solid #b45309;">
        <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-title); margin: 0 0 0.5rem 0;">Financial Notes</h3>
        <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; margin: 0;">
            General operating expenses such as rent and store utilities are not tracked here. The calculations above reflect <strong>Gross Profit Based on Recorded Costs</strong> (Total Sales minus recipe ingredient costs). Purchase order spend is displayed as a separate cash outflow metric.
        </p>
    </div>
    @if(isset($reportData['categoryBreakdown']) && count($reportData['categoryBreakdown']) > 0)
        <div class="chart-card" style="margin-bottom: 1.85rem;" id="profit_loss_categories_card">
            <div class="card-header-flex" style="flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-title); margin: 0;">Category Profitability Analysis</h2>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Revenue and estimated direct recipe cost by bakery product category</p>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                @foreach($reportData['categoryBreakdown'] as $cat)
                    <div class="report-inner-card" style="padding: 1rem;">
                        <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-title);">{{ $cat->category_name }}</div>
                        <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-secondary);">
                            <span>Revenue:</span>
                            <span style="font-weight: 700; color: #16a34a;">{{ $currency }}{{ number_format($cat->cat_revenue, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-secondary);">
                            <span>Recipe COGS:</span>
                            <span style="font-weight: 700; color: #dc2626;">-{{ $currency }}{{ number_format($cat->cat_cogs, 2) }}</span>
                        </div>
                        <div style="border-top: 1px dashed var(--border-color, #eee9e0); margin-top: 0.5rem; padding-top: 0.4rem; display: flex; justify-content: space-between; font-size: 0.875rem; font-weight: 800;">
                            <span>Gross Margin:</span>
                            <span style="color: {{ $cat->cat_profit >= 0 ? '#16a34a' : '#dc2626' }};">{{ $currency }}{{ number_format($cat->cat_profit, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif

<!-- =========================================================================
     REPORT CONTROLS & TABLE FILTERS
     ========================================================================= -->
<div class="report-controls-card">
    <div class="report-filter-flex">
        <div style="font-weight: 800; font-size: 1rem; color: var(--text-title);">
            {{ ucfirst(str_replace('-', ' ', $type)) }} Data Audit
        </div>
        <div style="font-size: 0.8rem; color: var(--text-secondary);">
            Showing {{ is_object($reportData['records'] ?? null) && method_exists($reportData['records'], 'total') ? $reportData['records']->total() : count($reportData['records'] ?? ($reportData['dailyRecords'] ?? [])) }} records
        </div>
    </div>

    <!-- Inline Filter & Search Form -->
    <form method="GET" action="{{ route('admin.reports') }}" class="data-filter-row">
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="period" value="{{ $period }}">
        @if(request()->filled('start_date'))
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        @endif
        @if(request()->filled('end_date'))
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        @endif

        <input type="text" name="search" class="report-search-input" placeholder="Search records by ID, name, code..." value="{{ request('search') }}" id="report_search_input">

        @if($type === 'sales')
            <select name="payment_method" class="report-select-filter" onchange="this.form.submit();">
                <option value="">All Payment Methods</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                <option value="qr_code" {{ request('payment_method') === 'qr_code' ? 'selected' : '' }}>KHQR / Digital</option>
            </select>
        @elseif($type === 'orders')
            <select name="order_status" class="report-select-filter" onchange="this.form.submit();">
                <option value="">All Order Statuses</option>
                <option value="pending" {{ request('order_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="preparing" {{ request('order_status') === 'preparing' ? 'selected' : '' }}>Preparing / Baking</option>
                <option value="ready_for_pickup" {{ request('order_status') === 'ready_for_pickup' ? 'selected' : '' }}>Ready for Pickup</option>
                <option value="completed" {{ request('order_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('order_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        @elseif($type === 'inventory')
            <select name="status" class="report-select-filter" onchange="this.form.submit();">
                <option value="">All Inventory Statuses</option>
                <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>Expiring in 7 Days</option>
            </select>
        @elseif($type === 'purchases')
            <select name="status" class="report-select-filter" onchange="this.form.submit();">
                <option value="">All PO Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>Ordered</option>
                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        @endif

        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.25rem; border-radius: 12px; font-weight: 700; font-size: 0.825rem;">
            Search & Filter
        </button>
        @if(request()->hasAny(['search', 'payment_method', 'order_status', 'status']))
            <a href="{{ route('admin.reports', ['type' => $type, 'period' => $period]) }}" class="btn btn-secondary" style="padding: 0.5rem 1rem; border-radius: 12px; font-size: 0.825rem;">
                Reset
            </a>
        @endif
    </form>
</div>

<!-- =========================================================================
     DATA TABLE COMPONENT
     ========================================================================= -->
<div class="report-table-wrapper">
    @if($type === 'sales')
        <table class="report-table" id="sales_report_table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date & Time</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th style="text-align: right;">Subtotal</th>
                    <th style="text-align: right;">Discount</th>
                    <th style="text-align: right;">Tax</th>
                    <th style="text-align: right;">Total</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $sale)
                    @php $order = $sale->order; @endphp
                    <tr>
                        <td style="font-weight: 800;">#SALE-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $sale->sold_at ? $sale->sold_at->format('M d, Y H:i') : 'N/A' }}</td>
                        <td style="font-weight: 700;">{{ $order ? $order->order_number : 'N/A' }}</td>
                        <td>{{ $order ? ($order->customer_name ?? ($order->customer ? $order->customer->name : 'Walk-in')) : 'Walk-in' }}</td>
                        <td>{{ $order && $order->items ? $order->items->sum('quantity') : 1 }}x</td>
                        <td style="text-align: right;">{{ $currency }}{{ $order ? number_format((float) $order->subtotal, 2) : '0.00' }}</td>
                        <td style="text-align: right; color: #dc2626;">-{{ $currency }}{{ $order ? number_format((float) $order->discount, 2) : '0.00' }}</td>
                        <td style="text-align: right;">{{ $currency }}{{ $order ? number_format((float) $order->tax, 2) : '0.00' }}</td>
                        <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format((float) $sale->total, 2) }}</td>
                        <td><span class="type-pill walkin">{{ strtoupper($sale->payment_method) }}</span></td>
                        <td><span class="status-badge {{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="report-empty-state">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <h4>No Sales Records Found</h4>
                                <p data-lang-key="no_data_period">{{ __('messages.no_data_period') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'revenue')
        <table class="report-table" id="revenue_report_table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th style="text-align: center;">Orders Completed</th>
                    <th style="text-align: right;">Daily Revenue</th>
                    <th style="text-align: right;">Average Order Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['dailyRecords'] as $row)
                    <tr>
                        <td style="font-weight: 800;">{{ $row['date'] }}</td>
                        <td>{{ $row['formatted'] }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $row['orders'] }}</td>
                        <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format((float) $row['revenue'], 2) }}</td>
                        <td style="text-align: right; font-weight: 700; color: #b45309;">{{ $currency }}{{ number_format((float) $row['avg_value'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="report-empty-state">
                                <h4>No Revenue Data</h4>
                                <p data-lang-key="no_data_period">{{ __('messages.no_data_period') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'orders')
        <table class="report-table" id="orders_report_table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date & Time</th>
                    <th>Customer</th>
                    <th>Order Type</th>
                    <th style="text-align: right;">Subtotal</th>
                    <th style="text-align: right;">Discount</th>
                    <th style="text-align: right;">Tax</th>
                    <th style="text-align: right;">Total</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $order)
                    <tr>
                        <td style="font-weight: 800;">{{ $order->order_number }}</td>
                        <td>{{ $order->created_at ? $order->created_at->format('M d, Y H:i') : '' }}</td>
                        <td>{{ $order->customer_name ?? ($order->customer ? $order->customer->name : 'Walk-in') }}</td>
                        <td><span class="type-pill {{ $order->is_custom ? 'custom' : 'walkin' }}">{{ $order->is_custom ? 'Custom Cake' : 'Standard' }}</span></td>
                        <td style="text-align: right;">{{ $currency }}{{ number_format((float) $order->subtotal, 2) }}</td>
                        <td style="text-align: right; color: #dc2626;">-{{ $currency }}{{ number_format((float) $order->discount, 2) }}</td>
                        <td style="text-align: right;">{{ $currency }}{{ number_format((float) $order->tax, 2) }}</td>
                        <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format((float) $order->total, 2) }}</td>
                        <td><span class="status-badge {{ $order->payment_status ?? 'paid' }}">{{ ucfirst($order->payment_status ?? 'paid') }}</span></td>
                        <td><span class="status-badge {{ $order->order_status }}">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="report-empty-state">
                                <h4>No Orders Recorded</h4>
                                <p data-lang-key="no_data_period">{{ __('messages.no_data_period') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'products')
        <table class="report-table" id="products_report_table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>SKU</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Unit Cost</th>
                    <th style="text-align: center;">Units Sold</th>
                    <th style="text-align: right;">Total Revenue</th>
                    <th style="text-align: center;">Orders Count</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $index => $prod)
                    <tr>
                        <td style="font-weight: 800; color: var(--accent-brown, #d97736);">#{{ $index + 1 }}</td>
                        <td style="font-weight: 800;">{{ $prod->product_name }}</td>
                        <td>{{ $prod->category_name ?? 'Uncategorized' }}</td>
                        <td style="font-family: monospace;">{{ $prod->sku ?? 'N/A' }}</td>
                        <td style="text-align: right;">{{ $currency }}{{ number_format((float) $prod->unit_price, 2) }}</td>
                        <td style="text-align: right;">{{ $currency }}{{ number_format((float) $prod->unit_cost, 2) }}</td>
                        <td style="text-align: center; font-weight: 800; color: var(--text-title);">{{ number_format((float) $prod->total_units_sold) }}</td>
                        <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format((float) $prod->total_revenue, 2) }}</td>
                        <td style="text-align: center;">{{ number_format($prod->total_orders_count) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="report-empty-state">
                                <h4>No Product Sales Found</h4>
                                <p data-lang-key="no_data_period">{{ __('messages.no_data_period') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'inventory')
        <table class="report-table" id="inventory_report_table">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Supplier</th>
                    <th style="text-align: right;">Current Stock</th>
                    <th>Unit</th>
                    <th style="text-align: right;">Reorder Level</th>
                    <th style="text-align: right;">Purchase Cost</th>
                    <th style="text-align: right;">Total Valuation</th>
                    <th>Expiry Date</th>
                    <th>Stock Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $item)
                    @php 
                        $statusClass = $item->quantity <= 0 ? 'out_of_stock' : ($item->quantity <= $item->minimum_quantity ? 'low_stock' : 'in_stock');
                        $statusLabel = $item->quantity <= 0 ? 'Out of Stock' : ($item->quantity <= $item->minimum_quantity ? 'Low Stock' : 'In Stock');
                    @endphp
                    <tr>
                        <td style="font-weight: 800;">{{ $item->name }}</td>
                        <td>{{ $item->supplier ? $item->supplier->name : 'N/A' }}</td>
                        <td style="text-align: right; font-weight: 800; color: {{ $item->quantity <= 0 ? '#dc2626' : 'inherit' }};">
                            {{ number_format((float) $item->quantity, 2) }}
                        </td>
                        <td>{{ $item->unit }}</td>
                        <td style="text-align: right;">{{ number_format((float) $item->minimum_quantity, 2) }}</td>
                        <td style="text-align: right;">{{ $currency }}{{ number_format((float) $item->cost, 2) }}</td>
                        <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format((float) ($item->quantity * $item->cost), 2) }}</td>
                        <td>{{ $item->expiry_date ? $item->expiry_date->format('M d, Y') : 'N/A' }}</td>
                        <td><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="report-empty-state">
                                <h4>No Ingredient Records</h4>
                                <p data-lang-key="no_data_period">{{ __('messages.no_data_period') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'low-stock')
        <table class="report-table" id="low_stock_report_table">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Supplier</th>
                    <th style="text-align: right;">Current Stock</th>
                    <th>Unit</th>
                    <th style="text-align: right;">Reorder Level</th>
                    <th style="text-align: right;">Shortage Deficit</th>
                    <th style="text-align: right;">Unit Cost</th>
                    <th style="text-align: right;">Est. Restock Cost</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $item)
                    @php 
                        $deficit = max(0.0, (float) $item->minimum_quantity - (float) $item->quantity);
                        $restockCost = $deficit * (float) $item->cost;
                    @endphp
                    <tr>
                        <td style="font-weight: 800;">{{ $item->name }}</td>
                        <td>{{ $item->supplier ? $item->supplier->name : 'N/A' }}</td>
                        <td style="text-align: right; font-weight: 800; color: #dc2626;">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td style="text-align: right;">{{ number_format((float) $item->minimum_quantity, 2) }}</td>
                        <td style="text-align: right; font-weight: 800; color: #b45309;">{{ number_format($deficit, 2) }}</td>
                        <td style="text-align: right;">{{ $currency }}{{ number_format((float) $item->cost, 2) }}</td>
                        <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format($restockCost, 2) }}</td>
                        <td>
                            <span class="status-badge {{ $item->quantity <= 0 ? 'out_of_stock' : 'low_stock' }}">
                                {{ $item->quantity <= 0 ? 'Out of Stock' : 'Low Stock' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="report-empty-state">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <h4 style="color: #15803d;">All Stock Levels Optimal</h4>
                                <p>No ingredients are currently below their designated minimum threshold.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'production')
        <table class="report-table" id="production_report_table">
            <thead>
                <tr>
                    <th>Batch #</th>
                    <th>Product</th>
                    <th>Recipe</th>
                    <th>Baker</th>
                    <th>Production Date</th>
                    <th style="text-align: center;">Quantity</th>
                    <th>Status</th>
                    <th>Started</th>
                    <th>Completed</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $batch)
                    <tr>
                        <td style="font-weight: 800;">{{ $batch->batch_number }}</td>
                        <td style="font-weight: 700;">{{ $batch->product ? $batch->product->name : 'N/A' }}</td>
                        <td>{{ $batch->recipe ? $batch->recipe->name : 'N/A' }}</td>
                        <td>{{ $batch->baker ? $batch->baker->name : ($batch->creator ? $batch->creator->name : 'Unassigned') }}</td>
                        <td>{{ $batch->production_date ? $batch->production_date->format('M d, Y') : '' }}</td>
                        <td style="text-align: center; font-weight: 800;">{{ number_format($batch->quantity) }}</td>
                        <td><span class="status-badge {{ $batch->status }}">{{ ucfirst($batch->status) }}</span></td>
                        <td>{{ $batch->started_at ? $batch->started_at->format('H:i') : '-' }}</td>
                        <td>{{ $batch->completed_at ? $batch->completed_at->format('H:i') : '-' }}</td>
                        <td style="font-weight: 600;">{{ $batch->duration ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="report-empty-state">
                                <h4>No Production Batches Found</h4>
                                <p data-lang-key="no_data_period">{{ __('messages.no_data_period') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'customers')
        <table class="report-table" id="customers_report_table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th style="text-align: center;">Completed Orders</th>
                    <th style="text-align: right;">Total Spent</th>
                    <th style="text-align: center;">Loyalty Points</th>
                    <th>Tier</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $cust)
                    <tr>
                        <td style="font-weight: 800;">{{ $cust->name }}</td>
                        <td>{{ $cust->phone ?? 'N/A' }}</td>
                        <td>{{ $cust->email ?? 'N/A' }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $cust->orders_count ?? 0 }}</td>
                        <td style="text-align: right; font-weight: 800; color: #16a34a;">
                            {{ $currency }}{{ number_format((float) ($cust->orders_sum_total ?? 0), 2) }}
                        </td>
                        <td style="text-align: center; font-weight: 800; color: #b45309;">{{ number_format($cust->loyalty_points ?? 0) }}</td>
                        <td><span class="status-badge {{ strtolower($cust->loyalty_tier ?? 'standard') }}">{{ strtoupper($cust->loyalty_tier ?? 'standard') }}</span></td>
                        <td><span class="status-badge {{ $cust->status ?? 'active' }}">{{ ucfirst($cust->status ?? 'active') }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="report-empty-state">
                                <h4>No Customer Records</h4>
                                <p data-lang-key="no_data_period">{{ __('messages.no_data_period') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'purchases')
        <table class="report-table" id="purchases_report_table">
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Expected Date</th>
                    <th>Received Date</th>
                    <th>Status</th>
                    <th style="text-align: right;">Total Cost</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $po)
                    <tr>
                        <td style="font-weight: 800;">{{ $po->po_number }}</td>
                        <td style="font-weight: 700;">{{ $po->supplier ? $po->supplier->name : 'N/A' }}</td>
                        <td>{{ $po->order_date ? $po->order_date->format('M d, Y') : '' }}</td>
                        <td>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $po->received_date ? $po->received_date->format('M d, Y H:i') : 'N/A' }}</td>
                        <td><span class="status-badge {{ $po->status }}">{{ strtoupper($po->status) }}</span></td>
                        <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format((float) $po->total_cost, 2) }}</td>
                        <td>{{ $po->user ? $po->user->name : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="report-empty-state">
                                <h4>No Purchase Orders Found</h4>
                                <p data-lang-key="no_data_period">{{ __('messages.no_data_period') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'profit-loss')
        <table class="report-table" id="profit_loss_report_table">
            <thead>
                <tr>
                    <th>Financial Performance Metric</th>
                    <th style="text-align: right;">Amount ({{ $currency }})</th>
                    <th>Calculation Scope Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: 800;">1. Total Sales Revenue</td>
                    <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format($reportData['salesRevenue'], 2) }}</td>
                    <td>Sum of completed orders in selected period</td>
                </tr>
                <tr>
                    <td style="font-weight: 800;">2. Product Cost of Goods Sold (COGS)</td>
                    <td style="text-align: right; font-weight: 800; color: #dc2626;">-{{ $currency }}{{ number_format($reportData['productCogs'], 2) }}</td>
                    <td>Sum of (units sold &times; product recipe cost) for completed orders</td>
                </tr>
                <tr style="background: var(--bg-surface-subtle, rgba(217, 119, 54, 0.08));">
                    <td style="font-weight: 800; font-size: 0.95rem;">3. GROSS PROFIT (Based on Recorded Costs)</td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.05rem; color: {{ $reportData['grossProfit'] >= 0 ? '#16a34a' : '#dc2626' }};">
                        {{ $currency }}{{ number_format($reportData['grossProfit'], 2) }}
                    </td>
                    <td>Sales Revenue &minus; Product COGS</td>
                </tr>
                <tr>
                    <td style="font-weight: 800;">4. Gross Profit Margin</td>
                    <td style="text-align: right; font-weight: 800; color: #b45309;">{{ $reportData['profitMargin'] }}%</td>
                    <td>(Gross Profit / Sales Revenue) &times; 100</td>
                </tr>
                <tr>
                    <td style="font-weight: 800;">5. Total Procurement Spend</td>
                    <td style="text-align: right; font-weight: 800;">{{ $currency }}{{ number_format($reportData['procurementSpend'], 2) }}</td>
                    <td>Total cost of purchase orders marked as Received in period</td>
                </tr>
                <tr>
                    <td style="font-weight: 800;">6. Physical Product Units Sold</td>
                    <td style="text-align: right; font-weight: 800;">{{ number_format($reportData['totalUnitsSold']) }}</td>
                    <td>Total units delivered to customers</td>
                </tr>
                <tr>
                    <td style="font-weight: 800;">7. Average Gross Profit Per Unit</td>
                    <td style="text-align: right; font-weight: 800; color: #16a34a;">{{ $currency }}{{ number_format($reportData['avgProfitPerUnit'], 2) }}</td>
                    <td>Gross Profit / Units Sold</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>

<!-- Pagination Links (When available) -->
@if(isset($reportData['records']) && is_object($reportData['records']) && method_exists($reportData['records'], 'links'))
    <div style="margin-top: 1rem;">
        {{ $reportData['records']->links() }}
    </div>
@endif

@endsection