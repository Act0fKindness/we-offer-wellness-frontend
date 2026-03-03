<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ReviewProductStatuses extends Command
{
    protected $signature = 'products:review-live {--dry-run : Only report the changes without persisting them}';

    protected $description = 'Audit product content and price data, promoting safe listings to live and demoting incomplete ones.';

    private ?int $liveStatusId = null;

    private ?int $draftStatusId = null;

    private int $promoted = 0;

    private int $demoted = 0;

    private int $unchanged = 0;

    private array $bannedTitleFragments = [
        'test', 'dummy', 'sample', 'lorem', 'ipsum', 'placeholder', 'tbd', 'zzz', 'asdf', 'testing'
    ];

    public function handle(): int
    {
        $this->liveStatusId = ProductStatus::where('status', 'live')->value('id');
        $this->draftStatusId = ProductStatus::where('status', 'draft')->value('id');

        if (!$this->liveStatusId || !$this->draftStatusId) {
            $this->error('Missing required product status records (live/draft).');
            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');

        Product::with(['variants', 'options.values', 'status'])
            ->chunkById(100, function ($chunk) use ($dryRun) {
                foreach ($chunk as $product) {
                    $issues = $this->detectIssues($product);

                    if (empty($issues)) {
                        $this->promoteIfNeeded($product, $dryRun);
                    } else {
                        $this->demoteIfNeeded($product, $issues, $dryRun);
                    }
                }
            });

        $this->info('Product review complete.');
        $this->line("Promoted to live: {$this->promoted}");
        $this->line("Demoted to draft: {$this->demoted}");
        $this->line("No change: {$this->unchanged}");

        return self::SUCCESS;
    }

    private function detectIssues(Product $product): array
    {
        $issues = [];

        if (!$this->hasValidTitle($product->title)) {
            $issues[] = 'title looks suspicious or too short';
        }

        if (!$this->hasValidDescription($product)) {
            $issues[] = 'insufficient description/summary content';
        }

        if (!$this->hasValidPrice($product)) {
            $issues[] = 'no non-zero price found';
        }

        if (!$this->hasValidVariants($product)) {
            $issues[] = 'missing or invalid variants/options';
        }

        return $issues;
    }

    private function hasValidTitle(?string $title): bool
    {
        $title = trim((string) $title);
        if ($title === '' || Str::length($title) < 6) {
            return false;
        }

        $lower = Str::lower($title);
        foreach ($this->bannedTitleFragments as $fragment) {
            if (Str::contains($lower, $fragment)) {
                return false;
            }
        }

        if (preg_match('/[?]{3,}/', $title)) {
            return false;
        }

        return true;
    }

    private function hasValidDescription(Product $product): bool
    {
        $fields = [
            $product->summary,
            $product->body_html,
            $product->what_to_expect,
            $product->included,
        ];

        $combined = '';
        foreach ($fields as $field) {
            $combined .= ' ' . strip_tags((string) $field);
        }

        return Str::length(trim($combined)) >= 60;
    }

    private function hasValidPrice(Product $product): bool
    {
        $basePrice = $this->toMinorUnits($product->price);
        if ($basePrice > 0) {
            return true;
        }

        foreach ($product->variants as $variant) {
            if ($this->toMinorUnits($variant->price) > 0) {
                return true;
            }
        }

        return false;
    }

    private function hasValidVariants(Product $product): bool
    {
        if ($product->variants->isEmpty()) {
            return $this->toMinorUnits($product->price) > 0;
        }

        foreach ($product->variants as $variant) {
            if ($this->toMinorUnits($variant->price) <= 0) {
                return false;
            }
        }

        return true;
    }

    private function toMinorUnits($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $numeric = (float) $value;

        return (int) round($numeric * 100);
    }

    private function promoteIfNeeded(Product $product, bool $dryRun): void
    {
        if ($product->product_status_id === $this->liveStatusId) {
            $this->unchanged++;
            return;
        }

        if ($dryRun) {
            $this->info("[DRY-RUN] Would promote #{$product->id} '{$product->title}' to live");
        } else {
            $product->update(['product_status_id' => $this->liveStatusId]);
            $this->info("Promoted #{$product->id} '{$product->title}' to live");
        }

        $this->promoted++;
    }

    private function demoteIfNeeded(Product $product, array $issues, bool $dryRun): void
    {
        $message = sprintf(
            "%s #%d '%s' due to: %s",
            $dryRun ? '[DRY-RUN] Would demote' : 'Demoted',
            $product->id,
            $product->title,
            implode('; ', $issues)
        );

        if ($product->product_status_id === $this->draftStatusId) {
            $this->line($message);
            $this->unchanged++;
            return;
        }

        if (!$dryRun) {
            $product->update(['product_status_id' => $this->draftStatusId]);
        }

        $this->line($message);
        $this->demoted++;
    }
}
