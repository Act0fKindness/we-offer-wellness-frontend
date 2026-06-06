<?php

namespace App\Console\Commands;

use App\Services\LocationCatalogService;
use Illuminate\Console\Command;

class ExportLocationCatalog extends Command
{
    protected $signature = 'locations:export-catalog {--path= : Optional output path override}';

    protected $description = 'Build the grouped locations JSON cache used by location search and landing pages.';

    public function handle(LocationCatalogService $service): int
    {
        $path = trim((string) $this->option('path'));
        $output = $service->export($path !== '' ? $path : null);
        $catalog = $service->load();

        $this->info('Location catalog exported to ' . $output);
        $this->line(sprintf(
            'Countries: %d | Counties: %d | Towns: %d | Products: %d | Offerings: %d',
            (int) data_get($catalog, 'stats.countries', 0),
            (int) data_get($catalog, 'stats.counties', 0),
            (int) data_get($catalog, 'stats.towns', 0),
            (int) data_get($catalog, 'stats.products', 0),
            (int) data_get($catalog, 'stats.offerings', 0),
        ));

        return self::SUCCESS;
    }
}
