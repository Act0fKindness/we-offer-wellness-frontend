<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;

class GenerateSitemaps extends Command
{
    protected $signature = 'sitemaps:generate
        {--output-dir= : Optional output directory override}';

    protected $description = 'Generate the sitemap index, child sitemap files, and Search Console manifest.';

    public function handle(SitemapService $service): int
    {
        $outputDir = trim((string) $this->option('output-dir'));
        $result = $service->buildAndWriteAll($outputDir !== '' ? $outputDir : null);

        $files = (array) ($result['files'] ?? []);
        $manifest = (array) ($result['manifest'] ?? []);
        $submissionUrls = (array) data_get($manifest, 'submission_urls', []);

        $this->info(sprintf('Generated %d sitemap segment file(s).', count($files)));
        $this->line(sprintf('Search Console submission URLs: %d', count($submissionUrls)));

        foreach ($files as $file) {
            $this->line(sprintf(
                '- %s (%d URLs) -> %s',
                (string) ($file['filename'] ?? ''),
                (int) ($file['count'] ?? 0),
                (string) ($file['url'] ?? '')
            ));
        }

        return self::SUCCESS;
    }
}
