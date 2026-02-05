<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductStatus;

class SetDraftProductsLive extends Command
{
    protected $signature = 'products:set-drafts-live {--dry-run : Show counts only, do not update}';
    protected $description = 'Update any products with status=draft to status=live';

    public function handle()
    {
        $draft = ProductStatus::where('status', 'draft')->first();
        $live = ProductStatus::where('status', 'live')->first();
        if (!$live) {
            $this->error('Live status not found in product_status table');
            return Command::FAILURE;
        }
        if (!$draft) {
            $this->info('No draft status found; nothing to update.');
            return Command::SUCCESS;
        }

        $q = Product::query()->where('product_status_id', $draft->id);
        $count = (int) $q->count();
        if ($count === 0) {
            $this->info('No draft products found.');
            return Command::SUCCESS;
        }
        $this->info("Found {$count} draft products.");
        if ($this->option('dry-run')) {
            $this->info('Dry run: no changes made.');
            return Command::SUCCESS;
        }
        $updated = Product::where('product_status_id', $draft->id)
            ->update(['product_status_id' => $live->id]);
        $this->info("Updated {$updated} products to live.");
        return Command::SUCCESS;
    }
}

