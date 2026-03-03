<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class CleanProductCopy extends Command
{
    protected $signature = 'products:clean-copy {--dry-run : Preview changes without saving}';

    protected $description = 'Decode customer-facing HTML fields (description, what to expect, included, etc.) so they display properly.';

    private int $updated = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        Product::query()
            ->select(['id','title','summary','body_html','what_to_expect','included'])
            ->chunkById(200, function ($chunk) use ($dryRun) {
                foreach ($chunk as $product) {
                    $changes = $this->cleanProduct($product);

                    if (empty($changes)) {
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("[DRY-RUN] Would clean fields on #{$product->id} '{$product->title}'");
                        continue;
                    }

                    $product->update($changes);
                    $this->info("Updated #{$product->id} '{$product->title}'");
                    $this->updated++;
                }
            });

        if (!$dryRun) {
            $this->info("Completed. {$this->updated} products updated.");
        }

        return self::SUCCESS;
    }

    private function cleanProduct(Product $product): array
    {
        $fields = ['summary','body_html','what_to_expect','included'];
        $changes = [];

        foreach ($fields as $field) {
            $original = (string) ($product->{$field} ?? '');
            $cleaned = $this->cleanField($original);

            if ($cleaned !== $original) {
                $changes[$field] = $cleaned;
            }
        }

        return $changes;
    }

    private function cleanField(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = str_replace(['&nbsp;', '\u00a0'], ' ', $decoded);
        $decoded = preg_replace('/\s*data-mce-[^\s=>]+="[^"]*"/i', '', $decoded);
        $decoded = preg_replace('/\s*data-mce-[^\s>]+/i', '', $decoded);
        $decoded = preg_replace('/<span[^>]*>(.*?)<\/span>/is', '$1', $decoded);
        $decoded = preg_replace('/\s{2,}/', ' ', $decoded);

        return trim($decoded);
    }
}
