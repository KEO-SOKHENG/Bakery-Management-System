<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SyncConditionAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:sync-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan live inventory and orders to generate deduplicated condition alerts and resolve normalized conditions';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $service): int
    {
        $this->info('Scanning bakery inventory, expiry dates, and custom orders...');

        $counts = $service->syncConditionAlerts();

        $this->table(
            ['Condition Category', 'Alerts Generated / Active'],
            [
                ['Low Stock Alerts', $counts['low_stock']],
                ['Out of Stock Alerts', $counts['out_of_stock']],
                ['Expiring Ingredients', $counts['expiring']],
                ['Expired Ingredients', $counts['expired']],
                ['Custom Order Reminders', $counts['pickup_soon']],
                ['Auto-Resolved Alerts', $counts['resolved']],
            ]
        );

        $this->info('Alert synchronization complete. Zero duplicates created.');

        return Command::SUCCESS;
    }
}
