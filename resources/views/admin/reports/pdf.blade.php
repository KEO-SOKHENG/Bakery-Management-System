<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $bakeryName }} - {{ ucfirst(str_replace('-', ' ', $type)) }} Report</title>
    <style>
        @page {
            margin: 12mm 15mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #27272a;
            font-size: 8.5pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #563020;
            padding-bottom: 8px;
        }
        .bakery-title {
            font-size: 16pt;
            font-weight: bold;
            color: #563020;
            margin: 0;
        }
        .bakery-subtitle {
            font-size: 8pt;
            color: #71717a;
            margin-top: 2px;
        }
        .report-title-cell {
            text-align: right;
        }
        .report-title {
            font-size: 13pt;
            font-weight: bold;
            color: #18181b;
            text-transform: uppercase;
            margin: 0;
        }
        .report-period {
            font-size: 8pt;
            color: #52525b;
            margin-top: 3px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 15px;
        }
        .kpi-cell {
            background-color: #faf7f3;
            border: 1px solid #e7dfd5;
            border-radius: 6px;
            padding: 8px 10px;
            width: 25%;
            vertical-align: top;
        }
        .kpi-title {
            font-size: 7pt;
            text-transform: uppercase;
            color: #71717a;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kpi-value {
            font-size: 12pt;
            font-weight: bold;
            color: #27272a;
            margin-top: 3px;
        }
        .kpi-sub {
            font-size: 6.5pt;
            color: #a1a1aa;
            margin-top: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .data-table th {
            background-color: #563020;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #563020;
        }
        .data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e4e4e7;
            font-size: 8pt;
        }
        .data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .totals-row td {
            background-color: #f4efe9 !important;
            font-weight: bold;
            border-top: 1.5px solid #563020;
            border-bottom: 1.5px solid #563020;
            color: #27272a;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer-table {
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid #e4e4e7;
            padding-top: 6px;
            font-size: 7pt;
            color: #a1a1aa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        .badge-red   { background: #fee2e2; color: #b91c1c; }
        .badge-blue  { background: #dbeafe; color: #1d4ed8; }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: bottom;">
                <div class="bakery-title">{{ $bakeryName }}</div>
                <div class="bakery-subtitle">
                    {{ $bakeryAddress ?: 'Official Bakery Management System' }} 
                    {{ $bakeryPhone ? ' | Tel: ' . $bakeryPhone : '' }}
                </div>
            </td>
            <td class="report-title-cell" style="width: 45%; vertical-align: bottom;">
                <div class="report-title">{{ strtoupper(str_replace('-', ' ', $type)) }} REPORT</div>
                <div class="report-period">
                    Period: 
                    @if($startDate && $endDate)
                        {{ $startDate->format('M d, Y') }} &mdash; {{ $endDate->format('M d, Y') }}
                    @else
                        Full Lifetime Audit
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- KPI Summary Row -->
    <table class="kpi-table">
        <tr>
            @if($type === 'sales')
                <td class="kpi-cell">
                    <div class="kpi-title">Total Sales Revenue</div>
                    <div class="kpi-value" style="color: #15803d;">{{ $currency }}{{ number_format($reportData['totalSalesRevenue'], 2) }}</div>
                    <div class="kpi-sub">Across all payment channels</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Sales Count</div>
                    <div class="kpi-value">{{ number_format($reportData['totalSalesCount']) }}</div>
                    <div class="kpi-sub">Completed transactions</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Average Sale Value</div>
                    <div class="kpi-value" style="color: #b45309;">{{ $currency }}{{ number_format($reportData['avgSaleValue'], 2) }}</div>
                    <div class="kpi-sub">Revenue / transactions</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Total Discounts Given</div>
                    <div class="kpi-value" style="color: #4f46e5;">{{ $currency }}{{ number_format($reportData['totalDiscount'], 2) }}</div>
                    <div class="kpi-sub">Tax: {{ $currency }}{{ number_format($reportData['totalTax'], 2) }}</div>
                </td>
            @elseif($type === 'revenue')
                <td class="kpi-cell">
                    <div class="kpi-title">Total Revenue</div>
                    <div class="kpi-value" style="color: #15803d;">{{ $currency }}{{ number_format($reportData['totalRevenue'], 2) }}</div>
                    <div class="kpi-sub">{{ number_format($reportData['totalOrders']) }} completed orders</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Avg Order Value</div>
                    <div class="kpi-value" style="color: #b45309;">{{ $currency }}{{ number_format($reportData['avgOrderValue'], 2) }}</div>
                    <div class="kpi-sub">Per transaction</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Cash vs Card Channel</div>
                    <div class="kpi-value" style="font-size: 10pt;">Cash: {{ $currency }}{{ number_format($reportData['cashRevenue'], 0) }}</div>
                    <div class="kpi-sub">Card: {{ $currency }}{{ number_format($reportData['cardRevenue'], 0) }}</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">KHQR / Digital</div>
                    <div class="kpi-value" style="color: #2563eb;">{{ $currency }}{{ number_format($reportData['qrRevenue'], 2) }}</div>
                    <div class="kpi-sub">Digital mobile payments</div>
                </td>
            @elseif($type === 'orders')
                <td class="kpi-cell">
                    <div class="kpi-title">Total Orders</div>
                    <div class="kpi-value">{{ number_format($reportData['totalOrders']) }}</div>
                    <div class="kpi-sub">Gross volume: {{ $currency }}{{ number_format($reportData['totalOrderAmount'], 2) }}</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Completed Orders</div>
                    <div class="kpi-value" style="color: #15803d;">{{ number_format($reportData['completedOrders']) }}</div>
                    <div class="kpi-sub">Success rate: {{ $reportData['completionRate'] }}%</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Pending / Baking</div>
                    <div class="kpi-value" style="color: #b45309;">{{ number_format($reportData['pendingOrders'] + $reportData['preparingOrders']) }}</div>
                    <div class="kpi-sub">In queue & kitchen</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Cancelled Rate</div>
                    <div class="kpi-value" style="color: #b91c1c;">{{ $reportData['cancellationRate'] }}%</div>
                    <div class="kpi-sub">{{ number_format($reportData['cancelledOrders']) }} orders cancelled</div>
                </td>
            @elseif($type === 'products')
                <td class="kpi-cell">
                    <div class="kpi-title">Total Units Sold</div>
                    <div class="kpi-value" style="color: #563020;">{{ number_format($reportData['totalUnitsSold']) }}</div>
                    <div class="kpi-sub">Completed items</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Total Sales Revenue</div>
                    <div class="kpi-value" style="color: #15803d;">{{ $currency }}{{ number_format($reportData['totalRevenue'], 2) }}</div>
                    <div class="kpi-sub">Product earnings</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Distinct Catalog Items Sold</div>
                    <div class="kpi-value">{{ number_format($reportData['totalDistinctProducts']) }}</div>
                    <div class="kpi-sub">Active items with sales</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Top Product</div>
                    <div class="kpi-value" style="font-size: 10pt; color: #b45309;">
                        {{ count($reportData['top5']) > 0 ? $reportData['top5'][0]->product_name : 'None' }}
                    </div>
                    <div class="kpi-sub">{{ count($reportData['top5']) > 0 ? $reportData['top5'][0]->total_units_sold . ' units' : '' }}</div>
                </td>
            @elseif($type === 'inventory')
                <td class="kpi-cell">
                    <div class="kpi-title">Total Ingredients</div>
                    <div class="kpi-value">{{ number_format($reportData['totalIngredients']) }}</div>
                    <div class="kpi-sub">Gross stock: {{ number_format($reportData['totalQuantitySum'], 1) }}</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Total Inventory Valuation</div>
                    <div class="kpi-value" style="color: #15803d;">{{ $currency }}{{ number_format($reportData['totalInventoryValue'], 2) }}</div>
                    <div class="kpi-sub">Based on purchase cost</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Low Stock Alert</div>
                    <div class="kpi-value" style="color: #b45309;">{{ number_format($reportData['lowStockCount']) }}</div>
                    <div class="kpi-sub">Out of stock: {{ number_format($reportData['outOfStockCount']) }}</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Expired / Soon</div>
                    <div class="kpi-value" style="color: #b91c1c;">{{ number_format($reportData['expiredCount']) }} / {{ number_format($reportData['expiringSoonCount']) }}</div>
                    <div class="kpi-sub">Expired / Expiring in 7 days</div>
                </td>
            @elseif($type === 'low-stock')
                <td class="kpi-cell">
                    <div class="kpi-title">Items Requiring Reorder</div>
                    <div class="kpi-value" style="color: #b45309;">{{ number_format($reportData['totalNeedingReorder']) }}</div>
                    <div class="kpi-sub">At or below reorder point</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Out of Stock Count</div>
                    <div class="kpi-value" style="color: #b91c1c;">{{ number_format($reportData['outOfStockCount']) }}</div>
                    <div class="kpi-sub">Stock = 0</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Low Stock Count</div>
                    <div class="kpi-value" style="color: #ca8a04;">{{ number_format($reportData['lowStockCount']) }}</div>
                    <div class="kpi-sub">0 &lt; Qty &le; Reorder Level</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Estimated Restock Cost</div>
                    <div class="kpi-value" style="color: #15803d;">{{ $currency }}{{ number_format($reportData['estimatedRestockCost'], 2) }}</div>
                    <div class="kpi-sub">To restore to minimum stock</div>
                </td>
            @elseif($type === 'production')
                <td class="kpi-cell">
                    <div class="kpi-title">Total Batches</div>
                    <div class="kpi-value">{{ number_format($reportData['totalBatches']) }}</div>
                    <div class="kpi-sub">Batches scheduled & run</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Units Produced</div>
                    <div class="kpi-value" style="color: #15803d;">{{ number_format($reportData['totalUnitsProduced']) }}</div>
                    <div class="kpi-sub">Completed products</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Completed Batches</div>
                    <div class="kpi-value" style="color: #2563eb;">{{ number_format($reportData['completedBatches']) }}</div>
                    <div class="kpi-sub">Rate: {{ $reportData['completionRate'] }}%</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Active in Kitchen</div>
                    <div class="kpi-value" style="color: #b45309;">{{ number_format($reportData['inProgressBatches'] + $reportData['scheduledBatches']) }}</div>
                    <div class="kpi-sub">In progress / Scheduled</div>
                </td>
            @elseif($type === 'customers')
                <td class="kpi-cell">
                    <div class="kpi-title">Total Customers</div>
                    <div class="kpi-value">{{ number_format($reportData['totalCustomers']) }}</div>
                    <div class="kpi-sub">Registered accounts</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">New in Selected Period</div>
                    <div class="kpi-value" style="color: #2563eb;">+{{ number_format($reportData['newCustomersPeriod']) }}</div>
                    <div class="kpi-sub">Acquisition</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Total Customer Spending</div>
                    <div class="kpi-value" style="color: #15803d;">{{ $currency }}{{ number_format($reportData['totalCustomerSpend'], 2) }}</div>
                    <div class="kpi-sub">From completed orders</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Average Customer Spend</div>
                    <div class="kpi-value" style="color: #b45309;">{{ $currency }}{{ number_format($reportData['avgCustomerSpend'], 2) }}</div>
                    <div class="kpi-sub">Per active customer</div>
                </td>
            @elseif($type === 'purchases')
                <td class="kpi-cell">
                    <div class="kpi-title">Total Suppliers</div>
                    <div class="kpi-value">{{ number_format($reportData['totalSuppliers']) }}</div>
                    <div class="kpi-sub">Active procurement partners</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Purchase Orders</div>
                    <div class="kpi-value">{{ number_format($reportData['totalPos']) }}</div>
                    <div class="kpi-sub">POs created in period</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Total Purchase Spend</div>
                    <div class="kpi-value" style="color: #15803d;">{{ $currency }}{{ number_format($reportData['totalPurchaseSpend'], 2) }}</div>
                    <div class="kpi-sub">Received POs only (Actual Spend)</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Avg Received PO Value</div>
                    <div class="kpi-value" style="color: #b45309;">{{ $currency }}{{ number_format($reportData['avgPoValue'], 2) }}</div>
                    <div class="kpi-sub">{{ number_format($reportData['receivedPos']) }} received POs</div>
                </td>
            @elseif($type === 'profit-loss')
                <td class="kpi-cell">
                    <div class="kpi-title">Sales Revenue</div>
                    <div class="kpi-value" style="color: #15803d;">{{ $currency }}{{ number_format($reportData['salesRevenue'], 2) }}</div>
                    <div class="kpi-sub">Completed sales</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Product Cost (COGS)</div>
                    <div class="kpi-value" style="color: #b91c1c;">{{ $currency }}{{ number_format($reportData['productCogs'], 2) }}</div>
                    <div class="kpi-sub">Units sold &times; product cost</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Gross Profit (Recorded Costs)</div>
                    <div class="kpi-value" style="color: {{ $reportData['grossProfit'] >= 0 ? '#15803d' : '#b91c1c' }};">
                        {{ $currency }}{{ number_format($reportData['grossProfit'], 2) }}
                    </div>
                    <div class="kpi-sub">Revenue &minus; Product COGS</div>
                </td>
                <td class="kpi-cell">
                    <div class="kpi-title">Gross Margin</div>
                    <div class="kpi-value" style="color: #b45309;">{{ $reportData['profitMargin'] }}%</div>
                    <div class="kpi-sub">Procurement Spend: {{ $currency }}{{ number_format($reportData['procurementSpend'], 2) }}</div>
                </td>
            @endif
        </tr>
    </table>

    <!-- Data Table -->
    @if($type === 'sales')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $sale)
                    @php $order = $sale->order; @endphp
                    <tr>
                        <td>#SALE-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $sale->sold_at ? $sale->sold_at->format('M d, Y H:i') : 'N/A' }}</td>
                        <td>{{ $order ? $order->order_number : 'N/A' }}</td>
                        <td>{{ $order ? ($order->customer_name ?? ($order->customer ? $order->customer->name : 'Walk-in')) : 'Walk-in' }}</td>
                        <td class="text-right">{{ $currency }}{{ $order ? number_format((float) $order->subtotal, 2) : '0.00' }}</td>
                        <td class="text-right">{{ $currency }}{{ $order ? number_format((float) $order->discount, 2) : '0.00' }}</td>
                        <td class="text-right">{{ $currency }}{{ $order ? number_format((float) $order->tax, 2) : '0.00' }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ $currency }}{{ number_format((float) $sale->total, 2) }}</td>
                        <td>{{ strtoupper($sale->payment_method) }}</td>
                        <td><span class="badge badge-green">{{ ucfirst($sale->payment_status) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 20px; color: #71717a;">No sales records found for this period.</td>
                    </tr>
                @endforelse
                @if(count($reportData['records']) > 0)
                    <tr class="totals-row">
                        <td colspan="4">TOTALS ({{ count($reportData['records']) }} records)</td>
                        <td class="text-right">{{ $currency }}{{ number_format($reportData['netSales'], 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format($reportData['totalDiscount'], 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format($reportData['totalTax'], 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format($reportData['totalSalesRevenue'], 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endif
            </tbody>
        </table>
    @elseif($type === 'revenue')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th class="text-center">Orders Count</th>
                    <th class="text-right">Daily Revenue</th>
                    <th class="text-right">Average Order Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['dailyRecords'] as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['formatted'] }}</td>
                        <td class="text-center">{{ $row['orders'] }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ $currency }}{{ number_format((float) $row['revenue'], 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format((float) $row['avg_value'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 20px; color: #71717a;">No revenue data recorded for this period.</td>
                    </tr>
                @endforelse
                <tr class="totals-row">
                    <td colspan="2">TOTAL PERIOD REVENUE</td>
                    <td class="text-center">{{ number_format($reportData['totalOrders']) }}</td>
                    <td class="text-right">{{ $currency }}{{ number_format($reportData['totalRevenue'], 2) }}</td>
                    <td class="text-right">{{ $currency }}{{ number_format($reportData['avgOrderValue'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @elseif($type === 'orders')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->created_at ? $order->created_at->format('M d, Y H:i') : '' }}</td>
                        <td>{{ $order->customer_name ?? ($order->customer ? $order->customer->name : 'Walk-in') }}</td>
                        <td>{{ $order->is_custom ? 'Custom Cake' : 'Standard' }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format((float) $order->subtotal, 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format((float) $order->discount, 2) }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ $currency }}{{ number_format((float) $order->total, 2) }}</td>
                        <td>{{ strtoupper($order->payment_method ?? 'cash') }}</td>
                        <td>
                            <span class="badge {{ $order->order_status === 'completed' ? 'badge-green' : ($order->order_status === 'cancelled' ? 'badge-red' : 'badge-amber') }}">
                                {{ ucfirst($order->order_status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px; color: #71717a;">No order records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'products')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>SKU</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-center">Units Sold</th>
                    <th class="text-right">Total Revenue</th>
                    <th class="text-center">Orders Count</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $prod)
                    <tr>
                        <td style="font-weight: bold;">{{ $prod->product_name }}</td>
                        <td>{{ $prod->category_name ?? 'Uncategorized' }}</td>
                        <td>{{ $prod->sku ?? 'N/A' }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format((float) $prod->unit_price, 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format((float) $prod->unit_cost, 2) }}</td>
                        <td class="text-center" style="font-weight: bold;">{{ number_format((float) $prod->total_units_sold) }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ $currency }}{{ number_format((float) $prod->total_revenue, 2) }}</td>
                        <td class="text-center">{{ number_format($prod->total_orders_count) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: #71717a;">No product sales recorded in this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'inventory')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Supplier</th>
                    <th class="text-right">Current Stock</th>
                    <th>Unit</th>
                    <th class="text-right">Reorder Level</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Total Value</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $item)
                    @php 
                        $statusClass = $item->quantity <= 0 ? 'badge-red' : ($item->quantity <= $item->minimum_quantity ? 'badge-amber' : 'badge-green');
                        $statusLabel = $item->quantity <= 0 ? 'Out of Stock' : ($item->quantity <= $item->minimum_quantity ? 'Low Stock' : 'In Stock');
                    @endphp
                    <tr>
                        <td style="font-weight: bold;">{{ $item->name }}</td>
                        <td>{{ $item->supplier ? $item->supplier->name : 'N/A' }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-right">{{ number_format((float) $item->minimum_quantity, 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format((float) $item->cost, 2) }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ $currency }}{{ number_format((float) ($item->quantity * $item->cost), 2) }}</td>
                        <td>{{ $item->expiry_date ? $item->expiry_date->format('M d, Y') : 'N/A' }}</td>
                        <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px; color: #71717a;">No ingredient records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'low-stock')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Supplier</th>
                    <th class="text-right">Current Stock</th>
                    <th>Unit</th>
                    <th class="text-right">Reorder Level</th>
                    <th class="text-right">Shortage Deficit</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Est. Restock Cost</th>
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
                        <td style="font-weight: bold;">{{ $item->name }}</td>
                        <td>{{ $item->supplier ? $item->supplier->name : 'N/A' }}</td>
                        <td class="text-right" style="color: #b91c1c; font-weight: bold;">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-right">{{ number_format((float) $item->minimum_quantity, 2) }}</td>
                        <td class="text-right" style="color: #b45309; font-weight: bold;">{{ number_format($deficit, 2) }}</td>
                        <td class="text-right">{{ $currency }}{{ number_format((float) $item->cost, 2) }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ $currency }}{{ number_format($restockCost, 2) }}</td>
                        <td>
                            <span class="badge {{ $item->quantity <= 0 ? 'badge-red' : 'badge-amber' }}">
                                {{ $item->quantity <= 0 ? 'Out of Stock' : 'Low Stock' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px; color: #15803d;">All ingredient stock levels are currently optimal! No low stock alerts.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'production')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Batch #</th>
                    <th>Product</th>
                    <th>Recipe</th>
                    <th>Baker</th>
                    <th>Production Date</th>
                    <th class="text-center">Quantity</th>
                    <th>Status</th>
                    <th>Started At</th>
                    <th>Completed At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $batch)
                    <tr>
                        <td style="font-weight: bold;">{{ $batch->batch_number }}</td>
                        <td>{{ $batch->product ? $batch->product->name : 'N/A' }}</td>
                        <td>{{ $batch->recipe ? $batch->recipe->name : 'N/A' }}</td>
                        <td>{{ $batch->baker ? $batch->baker->name : ($batch->creator ? $batch->creator->name : 'Unassigned') }}</td>
                        <td>{{ $batch->production_date ? $batch->production_date->format('M d, Y') : '' }}</td>
                        <td class="text-center" style="font-weight: bold;">{{ number_format($batch->quantity) }}</td>
                        <td>
                            <span class="badge {{ $batch->status === 'completed' ? 'badge-green' : ($batch->status === 'cancelled' ? 'badge-red' : 'badge-amber') }}">
                                {{ ucfirst($batch->status) }}
                            </span>
                        </td>
                        <td>{{ $batch->started_at ? $batch->started_at->format('H:i') : '-' }}</td>
                        <td>{{ $batch->completed_at ? $batch->completed_at->format('H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px; color: #71717a;">No production batches recorded in this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'customers')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th class="text-center">Orders Count</th>
                    <th class="text-right">Total Spent</th>
                    <th class="text-center">Loyalty Points</th>
                    <th>Tier</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $cust)
                    <tr>
                        <td style="font-weight: bold;">{{ $cust->name }}</td>
                        <td>{{ $cust->phone ?? 'N/A' }}</td>
                        <td>{{ $cust->email ?? 'N/A' }}</td>
                        <td class="text-center">{{ $cust->orders_count ?? 0 }}</td>
                        <td class="text-right" style="font-weight: bold; color: #15803d;">
                            {{ $currency }}{{ number_format((float) ($cust->orders_sum_total ?? 0), 2) }}
                        </td>
                        <td class="text-center">{{ number_format($cust->loyalty_points ?? 0) }}</td>
                        <td><span class="badge badge-amber">{{ strtoupper($cust->loyalty_tier ?? 'standard') }}</span></td>
                        <td><span class="badge badge-green">{{ ucfirst($cust->status ?? 'active') }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: #71717a;">No customer activity recorded in this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'purchases')
        <table class="data-table">
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Expected Date</th>
                    <th>Received Date</th>
                    <th>Status</th>
                    <th class="text-right">Total Cost</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['records'] as $po)
                    <tr>
                        <td style="font-weight: bold;">{{ $po->po_number }}</td>
                        <td>{{ $po->supplier ? $po->supplier->name : 'N/A' }}</td>
                        <td>{{ $po->order_date ? $po->order_date->format('M d, Y') : '' }}</td>
                        <td>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $po->received_date ? $po->received_date->format('M d, Y H:i') : 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $po->status === 'received' ? 'badge-green' : ($po->status === 'cancelled' ? 'badge-red' : 'badge-amber') }}">
                                {{ strtoupper($po->status) }}
                            </span>
                        </td>
                        <td class="text-right" style="font-weight: bold;">{{ $currency }}{{ number_format((float) $po->total_cost, 2) }}</td>
                        <td>{{ $po->user ? $po->user->name : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: #71717a;">No purchase orders found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type === 'profit-loss')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Financial Performance Metric</th>
                    <th class="text-right">Amount ({{ $currency }})</th>
                    <th>Calculation Formula & Scope Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: bold;">1. Total Sales Revenue</td>
                    <td class="text-right" style="font-weight: bold; color: #15803d;">{{ $currency }}{{ number_format($reportData['salesRevenue'], 2) }}</td>
                    <td>Sum of completed customer sales in period</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">2. Product Cost of Goods Sold (COGS)</td>
                    <td class="text-right" style="font-weight: bold; color: #b91c1c;">{{ $currency }}{{ number_format($reportData['productCogs'], 2) }}</td>
                    <td>Sum of (units sold &times; recorded unit cost) for completed orders</td>
                </tr>
                <tr class="totals-row">
                    <td>3. GROSS PROFIT (Recorded Costs)</td>
                    <td class="text-right" style="color: {{ $reportData['grossProfit'] >= 0 ? '#15803d' : '#b91c1c' }};">
                        {{ $currency }}{{ number_format($reportData['grossProfit'], 2) }}
                    </td>
                    <td>Sales Revenue &minus; Product COGS</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">4. Gross Profit Margin</td>
                    <td class="text-right" style="font-weight: bold;">{{ $reportData['profitMargin'] }}%</td>
                    <td>(Gross Profit / Sales Revenue) &times; 100</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">5. Total Procurement Spend</td>
                    <td class="text-right" style="font-weight: bold;">{{ $currency }}{{ number_format($reportData['procurementSpend'], 2) }}</td>
                    <td>Total cost of purchase orders received in period</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">6. Total Product Units Sold</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($reportData['totalUnitsSold']) }}</td>
                    <td>Physical bakery units delivered</td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 15px; font-size: 7.5pt; color: #71717a; border-left: 3px solid #b45309; padding-left: 8px;">
            <strong>Accounting Limitation Note:</strong> Operational overheads such as building rent, utilities, electricity, and staff labor wages are not tracked in the current database schema. This analysis transparently provides Gross Profit based on recorded direct product costs and procurement orders.
        </div>
    @endif

    <!-- Footer -->
    <table class="footer-table">
        <tr>
            <td>Generated on {{ now()->format('Y-m-d H:i:s T') }} &bull; Bakery Management System</td>
            <td style="text-align: right;">Confidential &bull; Page 1 of 1</td>
        </tr>
    </table>
</body>
</html>
