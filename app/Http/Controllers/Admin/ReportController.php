<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public const ALLOWED_TYPES = [
        'sales',
        'revenue',
        'orders',
        'products',
        'inventory',
        'low-stock',
        'production',
        'customers',
        'purchases',
        'profit-loss',
    ];

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the dynamic Report Management Dashboard.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'sales');
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            $type = 'sales';
        }

        $dateInfo = $this->reportService->parseDateRange($request);
        $period    = $dateInfo['period'];
        $startDate = $dateInfo['startDate'];
        $endDate   = $dateInfo['endDate'];

        $reportData = match ($type) {
            'sales'       => $this->reportService->getSalesReport($startDate, $endDate, $request, true),
            'revenue'     => $this->reportService->getRevenueReport($startDate, $endDate),
            'orders'      => $this->reportService->getOrderReport($startDate, $endDate, $request, true),
            'products'    => $this->reportService->getProductReport($startDate, $endDate, $request, true),
            'inventory'   => $this->reportService->getInventoryReport($request, true),
            'low-stock'   => $this->reportService->getLowStockReport($request, true),
            'production'  => $this->reportService->getProductionReport($startDate, $endDate, $request, true),
            'customers'   => $this->reportService->getCustomerReport($startDate, $endDate, $request, true),
            'purchases'   => $this->reportService->getSupplierPurchaseReport($startDate, $endDate, $request, true),
            'profit-loss' => $this->reportService->getProfitLossReport($startDate, $endDate),
        };

        $currency = Setting::get('currency', '$');
        $bakeryName = Setting::get('bakery_name', 'Sweet Delights Bakery');

        return view('admin.reports', compact(
            'type',
            'period',
            'startDate',
            'endDate',
            'reportData',
            'currency',
            'bakeryName'
        ));
    }

    /**
     * Export the active report as a styled PDF document.
     */
    public function exportPdf(Request $request)
    {
        $type = $request->get('type', 'sales');
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            $type = 'sales';
        }

        $dateInfo = $this->reportService->parseDateRange($request);
        $period    = $dateInfo['period'];
        $startDate = $dateInfo['startDate'];
        $endDate   = $dateInfo['endDate'];

        // Get unpaginated data for full export
        $reportData = match ($type) {
            'sales'       => $this->reportService->getSalesReport($startDate, $endDate, $request, false),
            'revenue'     => $this->reportService->getRevenueReport($startDate, $endDate),
            'orders'      => $this->reportService->getOrderReport($startDate, $endDate, $request, false),
            'products'    => $this->reportService->getProductReport($startDate, $endDate, $request, false),
            'inventory'   => $this->reportService->getInventoryReport($request, false),
            'low-stock'   => $this->reportService->getLowStockReport($request, false),
            'production'  => $this->reportService->getProductionReport($startDate, $endDate, $request, false),
            'customers'   => $this->reportService->getCustomerReport($startDate, $endDate, $request, false),
            'purchases'   => $this->reportService->getSupplierPurchaseReport($startDate, $endDate, $request, false),
            'profit-loss' => $this->reportService->getProfitLossReport($startDate, $endDate),
        };

        $currency = Setting::get('currency', '$');
        $bakeryName = Setting::get('bakery_name', 'Sweet Delights Bakery');
        $bakeryAddress = Setting::get('address', '');
        $bakeryPhone = Setting::get('phone_number', '');

        try {
            $pdf = Pdf::loadView('admin.reports.pdf', compact(
                'type',
                'period',
                'startDate',
                'endDate',
                'reportData',
                'currency',
                'bakeryName',
                'bakeryAddress',
                'bakeryPhone'
            ))->setPaper('a4', 'landscape');

            $startStr = $startDate ? $startDate->format('Ymd') : 'all';
            $endStr = $endDate ? $endDate->format('Ymd') : 'all';
            $filename = "bakery-{$type}-report-{$startStr}-{$endStr}.pdf";

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            return response()->view('admin.reports.pdf', compact(
                'type',
                'period',
                'startDate',
                'endDate',
                'reportData',
                'currency',
                'bakeryName',
                'bakeryAddress',
                'bakeryPhone'
            ));
        }
    }

    /**
     * Export the active report as a structured Excel/CSV file with UTF-8 BOM.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $type = $request->get('type', 'sales');
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            $type = 'sales';
        }

        $dateInfo = $this->reportService->parseDateRange($request);
        $startDate = $dateInfo['startDate'];
        $endDate   = $dateInfo['endDate'];

        // Get unpaginated data for full export
        $reportData = match ($type) {
            'sales'       => $this->reportService->getSalesReport($startDate, $endDate, $request, false),
            'revenue'     => $this->reportService->getRevenueReport($startDate, $endDate),
            'orders'      => $this->reportService->getOrderReport($startDate, $endDate, $request, false),
            'products'    => $this->reportService->getProductReport($startDate, $endDate, $request, false),
            'inventory'   => $this->reportService->getInventoryReport($request, false),
            'low-stock'   => $this->reportService->getLowStockReport($request, false),
            'production'  => $this->reportService->getProductionReport($startDate, $endDate, $request, false),
            'customers'   => $this->reportService->getCustomerReport($startDate, $endDate, $request, false),
            'purchases'   => $this->reportService->getSupplierPurchaseReport($startDate, $endDate, $request, false),
            'profit-loss' => $this->reportService->getProfitLossReport($startDate, $endDate),
        };

        $startStr = $startDate ? $startDate->format('Ymd') : 'all';
        $endStr = $endDate ? $endDate->format('Ymd') : 'all';
        $filename = "bakery-{$type}-report-{$startStr}-{$endStr}.csv";

        return response()->streamDownload(function () use ($type, $reportData) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            match ($type) {
                'sales' => $this->writeSalesCsv($handle, $reportData),
                'revenue' => $this->writeRevenueCsv($handle, $reportData),
                'orders' => $this->writeOrdersCsv($handle, $reportData),
                'products' => $this->writeProductsCsv($handle, $reportData),
                'inventory' => $this->writeInventoryCsv($handle, $reportData),
                'low-stock' => $this->writeLowStockCsv($handle, $reportData),
                'production' => $this->writeProductionCsv($handle, $reportData),
                'customers' => $this->writeCustomersCsv($handle, $reportData),
                'purchases' => $this->writePurchasesCsv($handle, $reportData),
                'profit-loss' => $this->writeProfitLossCsv($handle, $reportData),
            };

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function writeSalesCsv($handle, array $data): void
    {
        fputcsv($handle, ['Sale ID', 'Date', 'Order #', 'Customer', 'Items Count', 'Subtotal', 'Discount', 'Tax', 'Total', 'Payment Method', 'Payment Status']);
        foreach ($data['records'] as $sale) {
            $order = $sale->order;
            fputcsv($handle, [
                '#SALE-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT),
                $sale->sold_at ? $sale->sold_at->format('Y-m-d H:i') : '',
                $order ? $order->order_number : 'N/A',
                $order ? ($order->customer_name ?? ($order->customer ? $order->customer->name : 'Walk-in')) : 'Walk-in',
                $order && $order->items ? $order->items->sum('quantity') : 1,
                $order ? number_format((float) $order->subtotal, 2, '.', '') : '0.00',
                $order ? number_format((float) $order->discount, 2, '.', '') : '0.00',
                $order ? number_format((float) $order->tax, 2, '.', '') : '0.00',
                number_format((float) $sale->total, 2, '.', ''),
                strtoupper($sale->payment_method),
                ucfirst($sale->payment_status),
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['SUMMARY TOTALS', '', '', '', $data['totalSalesCount'], number_format($data['netSales'], 2, '.', ''), number_format($data['totalDiscount'], 2, '.', ''), number_format($data['totalTax'], 2, '.', ''), number_format($data['totalSalesRevenue'], 2, '.', ''), '', '']);
    }

    protected function writeRevenueCsv($handle, array $data): void
    {
        fputcsv($handle, ['Date', 'Day', 'Order Count', 'Total Revenue', 'Average Order Value']);
        foreach ($data['dailyRecords'] as $row) {
            fputcsv($handle, [
                $row['date'],
                $row['formatted'],
                $row['orders'],
                number_format((float) $row['revenue'], 2, '.', ''),
                number_format((float) $row['avg_value'], 2, '.', ''),
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['TOTALS', '', $data['totalOrders'], number_format((float) $data['totalRevenue'], 2, '.', ''), number_format((float) $data['avgOrderValue'], 2, '.', '')]);
        fputcsv($handle, ['Cash Revenue', number_format((float) $data['cashRevenue'], 2, '.', '')]);
        fputcsv($handle, ['Card Revenue', number_format((float) $data['cardRevenue'], 2, '.', '')]);
        fputcsv($handle, ['KHQR / Digital Revenue', number_format((float) $data['qrRevenue'], 2, '.', '')]);
    }

    protected function writeOrdersCsv($handle, array $data): void
    {
        fputcsv($handle, ['Order #', 'Date', 'Customer', 'Type', 'Subtotal', 'Discount', 'Tax', 'Total', 'Payment Method', 'Payment Status', 'Order Status']);
        foreach ($data['records'] as $order) {
            fputcsv($handle, [
                $order->order_number,
                $order->created_at ? $order->created_at->format('Y-m-d H:i') : '',
                $order->customer_name ?? ($order->customer ? $order->customer->name : 'Walk-in'),
                $order->is_custom ? 'Custom Cake' : 'Standard',
                number_format((float) $order->subtotal, 2, '.', ''),
                number_format((float) $order->discount, 2, '.', ''),
                number_format((float) $order->tax, 2, '.', ''),
                number_format((float) $order->total, 2, '.', ''),
                strtoupper($order->payment_method ?? 'cash'),
                ucfirst($order->payment_status ?? 'paid'),
                ucfirst($order->order_status ?? 'completed'),
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['TOTAL ORDERS', $data['totalOrders'], 'COMPLETED', $data['completedOrders'], 'CANCELLED', $data['cancelledOrders'], 'TOTAL AMOUNT', number_format((float) $data['totalOrderAmount'], 2, '.', '')]);
    }

    protected function writeProductsCsv($handle, array $data): void
    {
        fputcsv($handle, ['Product Name', 'Category', 'SKU', 'Unit Price', 'Unit Cost', 'Units Sold', 'Total Revenue', 'Order Count']);
        foreach ($data['records'] as $prod) {
            fputcsv($handle, [
                $prod->product_name,
                $prod->category_name ?? 'Uncategorized',
                $prod->sku ?? 'N/A',
                number_format((float) $prod->unit_price, 2, '.', ''),
                number_format((float) $prod->unit_cost, 2, '.', ''),
                $prod->total_units_sold,
                number_format((float) $prod->total_revenue, 2, '.', ''),
                $prod->total_orders_count,
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['TOTALS', '', '', '', '', $data['totalUnitsSold'], number_format((float) $data['totalRevenue'], 2, '.', ''), '']);
    }

    protected function writeInventoryCsv($handle, array $data): void
    {
        fputcsv($handle, ['Ingredient', 'Supplier', 'Current Stock', 'Unit', 'Reorder Level', 'Purchase Price', 'Inventory Value', 'Expiry Date', 'Status']);
        foreach ($data['records'] as $item) {
            $status = $item->quantity <= 0 ? 'Out of Stock' : ($item->quantity <= $item->minimum_quantity ? 'Low Stock' : 'In Stock');
            fputcsv($handle, [
                $item->name,
                $item->supplier ? $item->supplier->name : 'N/A',
                number_format((float) $item->quantity, 2, '.', ''),
                $item->unit,
                number_format((float) $item->minimum_quantity, 2, '.', ''),
                number_format((float) $item->cost, 2, '.', ''),
                number_format((float) ($item->quantity * $item->cost), 2, '.', ''),
                $item->expiry_date ? $item->expiry_date->format('Y-m-d') : 'N/A',
                $status,
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['TOTALS', 'Total Items: ' . $data['totalIngredients'], 'Low Stock: ' . $data['lowStockCount'], 'Out of Stock: ' . $data['outOfStockCount'], 'Expired: ' . $data['expiredCount'], '', 'Total Valuation: ' . number_format((float) $data['totalInventoryValue'], 2, '.', '')]);
    }

    protected function writeLowStockCsv($handle, array $data): void
    {
        fputcsv($handle, ['Ingredient', 'Supplier', 'Current Stock', 'Unit', 'Reorder Level', 'Shortage Deficit', 'Unit Cost', 'Est. Restock Cost', 'Status']);
        foreach ($data['records'] as $item) {
            $deficit = max(0.0, (float) $item->minimum_quantity - (float) $item->quantity);
            $restockCost = $deficit * (float) $item->cost;
            $status = $item->quantity <= 0 ? 'Out of Stock' : 'Low Stock';
            fputcsv($handle, [
                $item->name,
                $item->supplier ? $item->supplier->name : 'N/A',
                number_format((float) $item->quantity, 2, '.', ''),
                $item->unit,
                number_format((float) $item->minimum_quantity, 2, '.', ''),
                number_format($deficit, 2, '.', ''),
                number_format((float) $item->cost, 2, '.', ''),
                number_format($restockCost, 2, '.', ''),
                $status,
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['TOTAL DEFICIT ITEMS', $data['totalNeedingReorder'], 'OUT OF STOCK', $data['outOfStockCount'], 'LOW STOCK', $data['lowStockCount'], 'EST. TOTAL RESTOCK COST', number_format((float) $data['estimatedRestockCost'], 2, '.', '')]);
    }

    protected function writeProductionCsv($handle, array $data): void
    {
        fputcsv($handle, ['Batch #', 'Product', 'Recipe', 'Baker', 'Production Date', 'Quantity', 'Status', 'Started At', 'Completed At', 'Duration']);
        foreach ($data['records'] as $batch) {
            fputcsv($handle, [
                $batch->batch_number,
                $batch->product ? $batch->product->name : 'N/A',
                $batch->recipe ? $batch->recipe->name : 'N/A',
                $batch->baker ? $batch->baker->name : ($batch->creator ? $batch->creator->name : 'Unassigned'),
                $batch->production_date ? $batch->production_date->format('Y-m-d') : '',
                $batch->quantity,
                strtoupper($batch->status),
                $batch->started_at ? $batch->started_at->format('Y-m-d H:i') : '',
                $batch->completed_at ? $batch->completed_at->format('Y-m-d H:i') : '',
                $batch->duration ?? 'N/A',
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['TOTAL BATCHES', $data['totalBatches'], 'COMPLETED', $data['completedBatches'], 'TOTAL UNITS PRODUCED', $data['totalUnitsProduced'], 'COMPLETION RATE', $data['completionRate'] . '%']);
    }

    protected function writeCustomersCsv($handle, array $data): void
    {
        fputcsv($handle, ['Customer Name', 'Phone', 'Email', 'Completed Orders', 'Total Spent', 'Avg Order Spend', 'Loyalty Points', 'Loyalty Tier', 'Status']);
        foreach ($data['records'] as $cust) {
            $ordersCount = $cust->orders_count ?? 0;
            $spent = (float) ($cust->orders_sum_total ?? 0);
            $avg = $ordersCount > 0 ? ($spent / $ordersCount) : 0.0;
            fputcsv($handle, [
                $cust->name,
                $cust->phone ?? 'N/A',
                $cust->email ?? 'N/A',
                $ordersCount,
                number_format($spent, 2, '.', ''),
                number_format($avg, 2, '.', ''),
                $cust->loyalty_points ?? 0,
                strtoupper($cust->loyalty_tier ?? 'standard'),
                ucfirst($cust->status ?? 'active'),
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['TOTAL REGISTERED CUSTOMERS', $data['totalCustomers'], 'ACTIVE WITH ORDERS', $data['activeCustomersCount'], 'TOTAL CUSTOMER SPEND', number_format((float) $data['totalCustomerSpend'], 2, '.', '')]);
    }

    protected function writePurchasesCsv($handle, array $data): void
    {
        fputcsv($handle, ['PO #', 'Supplier', 'Order Date', 'Expected Date', 'Received Date', 'Status', 'Total Cost', 'Created By']);
        foreach ($data['records'] as $po) {
            fputcsv($handle, [
                $po->po_number,
                $po->supplier ? $po->supplier->name : 'N/A',
                $po->order_date ? $po->order_date->format('Y-m-d') : '',
                $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : 'N/A',
                $po->received_date ? $po->received_date->format('Y-m-d H:i') : 'N/A',
                strtoupper($po->status),
                number_format((float) $po->total_cost, 2, '.', ''),
                $po->user ? $po->user->name : 'N/A',
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['TOTAL PURCHASE ORDERS', $data['totalPos'], 'RECEIVED ORDERS', $data['receivedPos'], 'TOTAL PROCUREMENT SPEND (RECEIVED ONLY)', number_format((float) $data['totalPurchaseSpend'], 2, '.', '')]);
    }

    protected function writeProfitLossCsv($handle, array $data): void
    {
        fputcsv($handle, ['Financial Metric', 'Amount / Value', 'Calculation Formula & Scope']);
        fputcsv($handle, ['Total Sales Revenue', number_format((float) $data['salesRevenue'], 2, '.', ''), 'Sum of all completed orders in period']);
        fputcsv($handle, ['Cost of Goods Sold (COGS)', number_format((float) $data['productCogs'], 2, '.', ''), 'Sum of (order_items.quantity * products.cost) from completed sales']);
        fputcsv($handle, ['Gross Profit (Based on Recorded Costs)', number_format((float) $data['grossProfit'], 2, '.', ''), 'Sales Revenue - Product COGS']);
        fputcsv($handle, ['Gross Margin Percentage', $data['profitMargin'] . '%', '(Gross Profit / Sales Revenue) * 100']);
        fputcsv($handle, ['Total Procurement Spend', number_format((float) $data['procurementSpend'], 2, '.', ''), 'Sum of received purchase orders in period']);
        fputcsv($handle, ['Total Units Sold', $data['totalUnitsSold'], 'Total product units sold in completed orders']);
        fputcsv($handle, ['Average Profit Per Unit', number_format((float) $data['avgProfitPerUnit'], 2, '.', ''), 'Gross Profit / Total Units Sold']);
        fputcsv($handle, []);
        fputcsv($handle, ['CATEGORY BREAKDOWN', '', '']);
        fputcsv($handle, ['Category', 'Revenue', 'COGS', 'Gross Profit']);
        foreach ($data['categoryBreakdown'] as $cat) {
            fputcsv($handle, [
                $cat->category_name,
                number_format((float) $cat->cat_revenue, 2, '.', ''),
                number_format((float) $cat->cat_cogs, 2, '.', ''),
                number_format((float) $cat->cat_profit, 2, '.', ''),
            ]);
        }
    }
}
