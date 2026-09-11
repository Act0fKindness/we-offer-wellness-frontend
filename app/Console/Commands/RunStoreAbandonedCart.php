<?php

namespace App\Console\Commands;

use App\Services\StoreAbandonedCartService;
use Illuminate\Console\Command;

class RunStoreAbandonedCart extends Command
{
    protected $signature = 'store:run-abandoned-cart';
    protected $description = 'Send due Store abandoned-cart reminders and stop after purchase.';
    public function handle(StoreAbandonedCartService $service): int
    {
        $result = $service->run();
        $this->info("Store abandoned cart processed: {$result['sent']} sent, {$result['skipped']} skipped.");
        return self::SUCCESS;
    }
}
