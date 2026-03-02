<?php

namespace App\Support\ExternalReviews;

use App\Models\Review;
use App\Models\VendorReview;
use Illuminate\Console\Command as ArtisanCommand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Throwable;

class VendorReviewPublisher
{
    public function run(ArtisanCommand $console, array $options = []): int
    {
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $limit = max(0, (int) ($options['limit'] ?? 0));
        $vendorIds = $this->parseVendorIds($options['vendor_id'] ?? null);
        $refresh = (bool) ($options['refresh'] ?? false);

        $query = VendorReview::query()
            ->with(['vendor.user', 'publishedReview'])
            ->when(!empty($vendorIds), fn ($q) => $q->whereIn('vendor_id', $vendorIds))
            ->when(!$refresh, fn ($q) => $q->whereNull('review_id'))
            ->orderBy('id');

        $processed = 0;
        $published = 0;
        $skipped = 0;

        $console->info('Publishing vendor reviews into reviews table...');

        $query->chunkById(50, function (Collection $chunk) use (
            $console,
            $dryRun,
            $limit,
            $refresh,
            &$processed,
            &$published,
            &$skipped
        ) {
            foreach ($chunk as $vendorReview) {
                if ($limit && $published >= $limit) {
                    return false;
                }

                $processed++;
                try {
                    $result = $this->publishSingle($console, $vendorReview, $dryRun, $refresh);
                    if ($result === true) {
                        $published++;
                    } else {
                        $skipped++;
                    }
                } catch (Throwable $e) {
                    $skipped++;
                    $console->error(sprintf('Failed vendor review #%d: %s', $vendorReview->id, $e->getMessage()));
                    report($e);
                }
            }
        });

        $console->newLine();
        $console->info(sprintf('Processed %d vendor review rows. Published %d, skipped %d.', $processed, $published, $skipped));

        return SymfonyCommand::SUCCESS;
    }

    private function publishSingle(ArtisanCommand $console, VendorReview $source, bool $dryRun, bool $refresh): bool
    {
        $vendor = $source->vendor;
        $user = $vendor?->user;
        $text = trim((string) $source->review_text);

        if (!$vendor || !$user) {
            $console->warn(sprintf('Skipping review #%d (vendor or vendor user missing)', $source->id));
            return false;
        }

        if ($text === '') {
            $console->warn(sprintf('Skipping review #%d (empty review text)', $source->id));
            return false;
        }

        if (!$refresh && $source->review_id) {
            // already linked, nothing to do
            return false;
        }

        $payload = [
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'product_id' => null,
            'review_text' => $text,
            'rating' => $this->normalizeRating($source->rating),
            'media_type' => null,
            'media_url' => null,
            'is_provider_added' => true,
        ];

        if ($dryRun) {
            $console->line(sprintf('DRY RUN → would publish review #%d for vendor #%d', $source->id, $vendor->id));
            return true;
        }

        $review = $source->publishedReview;
        if (!$review) {
            $review = new Review();
        }

        $review->fill($payload);
        $reviewedAt = $source->reviewed_at instanceof Carbon ? $source->reviewed_at : null;

        if (!$review->exists && $reviewedAt) {
            $review->created_at = $reviewedAt;
            $review->updated_at = $reviewedAt;
        }

        $review->save();

        if ($reviewedAt && $review->wasRecentlyCreated === false) {
            $review->timestamps = false;
            $review->update(['created_at' => $reviewedAt, 'updated_at' => $reviewedAt]);
            $review->timestamps = true;
        }

        $source->review_id = $review->id;
        $source->save();

        return true;
    }

    private function normalizeRating($rating): ?float
    {
        if ($rating === null || $rating === '') {
            return null;
        }
        $value = (float) $rating;
        if ($value <= 0) {
            return null;
        }

        return round(min($value, 5), 1);
    }

    private function parseVendorIds($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return collect($value)->map(fn ($id) => (int) $id)->filter()->values()->all();
        }

        if (is_numeric($value)) {
            return [(int) $value];
        }

        return collect(explode(',', (string) $value))
            ->map(fn ($chunk) => (int) trim($chunk))
            ->filter()
            ->values()
            ->all();
    }
}
