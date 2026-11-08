<?php

namespace App\Console\Commands;

use App\Services\BatchGenerationService;
use Illuminate\Console\Command;

class GenerateBatches extends Command
{
    protected $signature = 'batches:generate
                            {--limit= : Maximum number of batches to create}
                            {--dry-run : Show what would be created without creating}';

    protected $description = 'Automatically group pending orders into shopper batches';

    public function __construct(
        private BatchGenerationService $batchService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting batch generation...');

        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No batches will be created');
        }

        // Get pending orders not yet in a batch
        $pendingOrders = \App\Models\Order::whereNull('batch_id')
            ->where('status', 'pending')
            ->with(['merchant', 'address', 'items'])
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending orders found for batching.');
            return self::SUCCESS;
        }

        $this->info("Found {$pendingOrders->count()} pending orders");

        if ($dryRun) {
            $grouped = $pendingOrders->groupBy('merchant_id');
            foreach ($grouped as $merchantId => $orders) {
                $merchant = $orders->first()->merchant;
                $chunks = $orders->chunk(3);
                $this->info("Merchant: {$merchant->name} - Would create {$chunks->count()} batch(es) from {$orders->count()} order(s)");
            }
            return self::SUCCESS;
        }

        $batchesCreated = $this->batchService->autoAssignOrdersToBatches($limit);

        if ($limit && $batchesCreated > $limit) {
            $this->warn("Created {$batchesCreated} batches, but limit was {$limit}");
        }

        $this->info("Successfully created {$batchesCreated} batch(es)");

        return self::SUCCESS;
    }
}
