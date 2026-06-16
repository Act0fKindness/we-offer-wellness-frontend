<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemaps extends Command
{
    protected $signature = 'sitemaps:generate
        {--output-dir= : Optional output directory override}';

    protected $description = 'Generate the sitemap index, child sitemap files, and Search Console manifest.';

    public function handle(SitemapService $service): int
    {
        @set_time_limit(0);
        if (function_exists('ini_set')) {
            @ini_set('max_execution_time', '0');
        }

        $previousTotal = 0;
        $previousFileCount = 0;
        $manifestPath = $service->manifestPath();
        if (File::isFile($manifestPath)) {
            $decoded = json_decode((string) File::get($manifestPath), true);
            if (is_array($decoded)) {
                $previousTotal = (int) ($decoded['total_urls'] ?? 0);
                $previousFileCount = count((array) ($decoded['sitemaps'] ?? []));
            }
        }

        $outputDir = trim((string) $this->option('output-dir'));
        $result = $service->buildAndWriteAll($outputDir !== '' ? $outputDir : null);

        $files = (array) ($result['files'] ?? []);
        $manifest = (array) ($result['manifest'] ?? []);
        $submissionUrls = (array) data_get($manifest, 'submission_urls', []);
        $totalUrls = (int) data_get($manifest, 'total_urls', 0);
        $fileCount = (int) data_get($manifest, 'file_count', count($files));

        $this->info(sprintf('Generated %d sitemap segment file(s).', count($files)));
        $this->line(sprintf('Total sitemap URLs: %d', $totalUrls));
        $this->line(sprintf('Search Console submission URLs: %d', count($submissionUrls)));

        foreach ($files as $file) {
            $this->line(sprintf(
                '- %s (%d URLs) -> %s',
                (string) ($file['filename'] ?? ''),
                (int) ($file['count'] ?? 0),
                (string) ($file['url'] ?? '')
            ));
        }

        if ($fileCount < 10) {
            $this->warn(sprintf('Critical: only %d sitemap file(s) were generated; expected at least 10.', $fileCount));
        }

        if ($previousTotal > 0 && $totalUrls > 0 && $totalUrls < (int) floor($previousTotal * 0.7)) {
            $this->warn(sprintf(
                'Critical: sitemap URL count dropped by more than 30%% from %d to %d.',
                $previousTotal,
                $totalUrls
            ));
        }

        if ($previousFileCount > 0 && $fileCount < (int) floor($previousFileCount * 0.7)) {
            $this->warn(sprintf(
                'Critical: sitemap file count dropped by more than 30%% from %d to %d.',
                $previousFileCount,
                $fileCount
            ));
        }

        return self::SUCCESS;
    }
}
