<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Services\AuditLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ClearOldOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:clear-old
                            {--days= : Clear orders older than this number of days (default: 30)}
                            {--all : Clear all orders in the system regardless of creation date}
                            {--status= : Filter by order status (e.g. completed, cancelled, pending)}
                            {--restore-stock : Restore reserved stock for any deleted pending/preparing orders}
                            {--backup : Create a JSON archive of orders before deleting}
                            {--dry-run : Simulate deletion and display affected records without deleting}
                            {--force : Bypass confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely clear old or historical order records and associated data with stock restoration and cascade safety';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $all = (bool) $this->option('all');
        $daysOpt = $this->option('days');
        $statusOpt = $this->option('status');
        $restoreStock = (bool) $this->option('restore-stock');
        $backup = (bool) $this->option('backup');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if (!$all && $daysOpt === null && !$this->input->isInteractive()) {
            $days = 30;
        } elseif (!$all && $daysOpt !== null) {
            $days = max(0, (int) $daysOpt);
        } elseif ($all) {
            $days = null;
        } else {
            // Interactive mode prompt
            if ($this->confirm('Do you want to clear ALL orders regardless of date?', false)) {
                $all = true;
                $days = null;
            } else {
                $days = (int) $this->ask('Clear orders older than how many days?', '30');
            }
        }

        // Build Query
        $query = Order::query();

        if (!$all && $days !== null) {
            $cutoffDate = now()->subDays($days);
            $query->where('created_at', '<', $cutoffDate);
        }

        if (!empty($statusOpt)) {
            $statuses = array_filter(array_map('trim', explode(',', strtolower($statusOpt))));
            if (!empty($statuses)) {
                $query->whereIn('order_status', $statuses);
            }
        }

        $orderIds = (clone $query)->pluck('id')->toArray();
        $orderCount = count($orderIds);

        if ($orderCount === 0) {
            $this->info('No matching orders found to clear.');
            return Command::SUCCESS;
        }

        // Calculate related record impact
        $itemsCount = OrderItem::whereIn('order_id', $orderIds)->count();
        $salesCount = Sale::whereIn('order_id', $orderIds)->count();
        $paymentsCount = Payment::whereIn('order_id', $orderIds)->count();
        $pendingOrPreparingCount = Order::whereIn('id', $orderIds)
            ->whereIn('order_status', ['pending', 'preparing', 'baking'])
            ->count();

        $this->warn("Identified {$orderCount} order(s) matching purge criteria.");

        $this->table(
            ['Entity / Criterion', 'Count / Details'],
            [
                ['Filter Scope', $all ? 'ALL Orders (Entire System)' : "Older than {$days} days (Created before " . now()->subDays($days)->toDateTimeString() . ")"],
                ['Status Filter', !empty($statusOpt) ? $statusOpt : 'All statuses'],
                ['Orders to Delete', number_format($orderCount)],
                ['Order Items (Line Items)', number_format($itemsCount)],
                ['Sales Records', number_format($salesCount)],
                ['Payment Transactions', number_format($paymentsCount)],
                ['Pending / Preparing Orders', number_format($pendingOrPreparingCount)],
                ['Restore Reserved Stock?', $restoreStock ? 'Yes (Inventory will be restored)' : 'No (Stock stays as-is)'],
            ]
        );

        if ($dryRun) {
            $this->info('[DRY-RUN] Simulation completed. No records were deleted.');
            return Command::SUCCESS;
        }

        if (!$force) {
            $confirmed = $this->confirm("Are you SURE you want to permanently delete these {$orderCount} order(s) and all linked sales/payments?", false);
            if (!$confirmed) {
                $this->comment('Purge cancelled by user.');
                return Command::SUCCESS;
            }
        }

        // Create backup archive if requested
        if ($backup) {
            $this->createBackupArchive($orderIds);
        }

        // Execute Deletion
        $this->output->write('Purging order records and maintaining data consistency... ');

        $restoredItemsCount = 0;

        DB::transaction(function () use (
            $orderIds,
            $restoreStock,
            &$restoredItemsCount,
            $orderCount,
            $all,
            $days,
            $statusOpt
        ) {
            // Restore inventory if requested for pending/preparing orders
            if ($restoreStock) {
                $activeOrders = Order::with('items')
                    ->whereIn('id', $orderIds)
                    ->whereIn('order_status', ['pending', 'preparing', 'baking'])
                    ->lockForUpdate()
                    ->get();

                foreach ($activeOrders as $activeOrder) {
                    foreach ($activeOrder->items as $item) {
                        if ($item->product_id && $item->quantity > 0) {
                            $product = Product::lockForUpdate()->find($item->product_id);
                            if ($product) {
                                $product->increment('stock', $item->quantity);
                                $restoredItemsCount++;
                            }
                        }
                    }
                }
            }

            // Explicitly clean up related tables and orders
            Payment::whereIn('order_id', $orderIds)->delete();
            Sale::whereIn('order_id', $orderIds)->delete();
            OrderItem::whereIn('order_id', $orderIds)->delete();
            Order::whereIn('id', $orderIds)->delete();

            // Record Audit Log
            AuditLogService::log(
                'orders_purged',
                "Cleared {$orderCount} historical order(s) (scope: " . ($all ? 'all' : ">{$days}d") . ($statusOpt ? ", status: {$statusOpt}" : '') . ").",
                null,
                [
                    'orders_deleted' => $orderCount,
                    'restored_stock' => $restoreStock,
                    'restored_items_count' => $restoredItemsCount,
                    'days_threshold' => $days,
                    'all' => $all,
                    'status_filter' => $statusOpt,
                ]
            );
        });

        $this->output->writeln('<info>DONE</info>');
        $this->info("Successfully cleared {$orderCount} order(s) and their linked records.");
        if ($restoreStock && $restoredItemsCount > 0) {
            $this->info("Restored inventory for {$restoredItemsCount} product line item(s).");
        }

        return Command::SUCCESS;
    }

    /**
     * Create JSON backup file before purging orders.
     */
    protected function createBackupArchive(array $orderIds): void
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $backupFile = $backupDir . DIRECTORY_SEPARATOR . 'orders_purge_backup_' . date('Y_m_d_His') . '.json';

        $ordersData = Order::with(['items', 'sale', 'payments'])
            ->whereIn('id', $orderIds)
            ->get();

        File::put($backupFile, $ordersData->toJson(JSON_PRETTY_PRINT));
        $this->info("Backup archive saved to: {$backupFile}");
    }
}
