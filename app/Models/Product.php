<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopify_id',
        'title',
        'summary',
        'handle',
        'description',
        'body_html',
        'what_to_expect',  // Added field
        'included',        // Added field
        'product_type',
        'price',
        'vendor_id',
        'product_status_id',
        'category_id',
        'is_women_owned',  // Added field
        'is_lgbtq_friendly', // Added field
        'tags_list',
        'meta_json',
        'by_need',
    ];

    protected $casts = [
        'meta_json' => 'array',
        'by_need' => 'array',
    ];


    // Relationships

    /**
     * The vendor who owns the product.
     */
    public function vendor()
    {
        return $this->belongsTo(VendorDetail::class, 'vendor_id');
    }

    /**
     * Media associated with the product.
     */
    public function product_media()
    {
        return $this->hasMany(ProductMedia::class, 'product_id');
    }

    /**
     * The status of the product.
     */
    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'product_status_id');
    }

    /**
     * Options associated with the product.
     */
    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    /**
     * Variants associated with the product.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Reviews associated with the product.
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Inventory details associated with the product.
     */
    public function inventory()
    {
        return $this->hasOne(ProductInventory::class);
    }

    /**
     * The category the product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Media files associated with the product.
     */
    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    // Accessors and Utility Methods

    /**
     * Get a summary of the product description.
     *
     * @return string
     */
    public function getSummaryAttribute(?string $value): ?string
    {
        if (filled($value)) {
            return $value;
        }

        $source = $this->description ?? $this->body_html ?? '';
        $decoded = html_entity_decode($source, ENT_QUOTES | ENT_HTML5);
        $cleanedText = trim(preg_replace('/\s+/', ' ', strip_tags($decoded) ?? ''));

        if ($cleanedText === '') {
            return null;
        }

        return Str::limit(Str::words($cleanedText, 30, ''), 150, '...');
    }

    /**
     * Get all option names and values for the product.
     *
     * @return array
     */
    public function getOptionNamesAndValues()
    {
        return $this->options->mapWithKeys(function ($option) {
            return [
                $option->name => $option->values->pluck('value')->toArray(),
            ];
        })->toArray();
    }

    /**
     * Get locations formatted display.
     *
     * @return string
     */
    public function getFormattedLocations()
    {
        $locations = $this->getLocations();
        $isOnline = in_array('Online', $locations, true);
        $physicalLocations = array_diff($locations, ['Online']);
        $physicalCount = count($physicalLocations);

        if ($isOnline && $physicalCount === 0) {
            return 'Available only Online';
        } elseif ($isOnline && $physicalCount > 0) {
            return 'Available in ' . $physicalCount . ' locations & Online';
        } elseif ($physicalCount > 0) {
            return 'Available in ' . $physicalCount . ' locations';
        }

        return 'No locations available';
    }

    /**
     * Fetch all available locations for the product.
     *
     * @return array
     */
    public function getLocations()
    {
        $locationsOption = $this->relationLoaded('options')
            ? $this->options->firstWhere('meta_name', 'locations')
            : $this->options()->where('meta_name', 'locations')->with('values')->first();

        if (!$locationsOption) {
            return [];
        }

        return $locationsOption->values
            ->pluck('value')
            ->filter()
            ->map(fn($value) => is_string($value) ? trim($value) : $value)
            ->values()
            ->toArray();
    }

    /**
     * Get the first image URL of the product.
     *
     * @return string
     */
    public function getFirstImageUrl()
    {
        $firstImage = $this->media->first();
        if (!$firstImage) {
            return asset('assets/img/no-product-image.jpg');
        }

        $path = (string) ($firstImage->media_url ?? '');
        if ($path === '') {
            return asset('assets/img/no-product-image.jpg');
        }

        // Build a URL from known sources first
        $url = null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $url = $path; // absolute, will normalize host below
        } else {
            // Prefer serving from Backend's storage if configured
            $backend = rtrim((string) env('BACKEND_ASSET_URL', env('BACKEND_URL', '')), '/');
            $clean = ltrim($path, '/');
            if ($backend) {
                $url = $backend . '/storage/' . $clean;
            } else {
                // Fallback to local storage URL (requires public/storage symlink)
                $url = asset('storage/' . $clean);
            }
        }

        // Normalize all product image hosts to atease domain
        $atease = rtrim((string) env('ATEASE_BASE_URL', env('ALT_STORAGE_BASE', 'https://atease.weofferwellness.co.uk')), '/');
        try {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                $parts = parse_url($url) ?: [];
                $p = ($parts['path'] ?? '/') ?: '/';
                $q = isset($parts['query']) && $parts['query'] !== '' ? ('?'.$parts['query']) : '';
                return $atease . $p . $q;
            }
        } catch (\Throwable $e) {}

        // Relative or failed parse: ensure single leading slash
        return $atease . '/' . ltrim((string) $url, '/');
    }

    /**
     * Map variants with their respective options.
     *
     * @return array
     */
    public function getMappedVariants()
    {
        return $this->variants->map(function ($variant) {
            $decodedOptionIds = json_decode($variant->option_ids, true) ?? [];
            $optionValues = collect($decodedOptionIds)->map(function ($optionId) {
                $value = $this->options->flatMap(fn ($option) => $option->values)
                    ->firstWhere('id', $optionId);
                return $value ? $value->value : 'N/A';
            });

            return [
                'option_values' => $optionValues,
                'price' => $variant->price,
                'sku' => $variant->sku,
                'inventory_quantity' => $variant->inventory_quantity,
                'option_ids' => $variant->option_ids,
            ];
        });
    }

    /**
     * Generate structured tabular data for display.
     *
     * @return array
     */
    public function getTabularData()
    {
        $options = $this->options()->with('values')->get();
        $optionValues = $options->mapWithKeys(function ($option) {
            return [$option->meta_name => $option->values->pluck('value')->toArray()];
        })->toArray();

        $combinations = $this->generateCombinations($optionValues);

        $variants = $this->variants->map(function ($variant) {
            return [
                'price' => $variant->price ?? 'NULL',
                'sku' => $variant->sku ?? 'NULL',
                'inventory' => $variant->inventory_quantity ?? 'NULL',
            ];
        })->toArray();

        $tabularData = [];
        foreach ($combinations as $combination) {
            $variant = array_shift($variants) ?? ['price' => 'NULL', 'sku' => 'NULL', 'inventory' => 'NULL'];
            $tabularData[] = array_merge($combination, $variant);
        }

        return $tabularData;
    }

    /**
     * Generate combinations of option values.
     *
     * @param array $optionValues
     * @return array
     */
    private function generateCombinations($optionValues)
    {
        if (empty($optionValues)) {
            return [];
        }

        $keys = array_keys($optionValues);
        $values = array_values($optionValues);

        $combinations = [[]];

        foreach ($values as $valueSet) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($valueSet as $value) {
                    $newCombinations[] = array_merge($combination, [$value]);
                }
            }
            $combinations = $newCombinations;
        }

        return array_map(function ($combination) use ($keys) {
            return array_combine($keys, $combination);
        }, $combinations);
    }

    public function painpoints()
    {
        return $this->hasMany(ProductPainpoint::class);
    }

    /**
     * Accessor for tags as an array.
     *
     * @return array
     */
    public function getTagsArrayAttribute()
    {
        return explode(',', $this->tags_list);
    }

    /**
     * Mutator to set tags as a comma-separated string.
     *
     * @param array $value
     * @return void
     */
    public function setTagsArrayAttribute($value)
    {
        $this->attributes['tags_list'] = implode(',', array_filter($value));
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_product')
                    ->withPivot('sort_order');
    }

    /**
     * Return the ProductOption row for locations (with values), if present.
     */
    public function locationOption(): ?ProductOption
    {
        if ($this->relationLoaded('options')) {
            $opt = $this->options->firstWhere('meta_name', 'locations');
            if ($opt && !$opt->relationLoaded('values')) {
                $opt->load('values');
            }
            return $opt;
        }
        return $this->options()->where('meta_name', 'locations')->with('values')->first();
    }

    /**
     * Map each location label to the variant IDs that include that location in their options.
     *
     * @return array<string, array<int>> [ location_label => [variant_id, ...] ]
     */
    public function locationVariantMap(): array
    {
        $locOpt = $this->locationOption();
        if (!$locOpt) return [];

        $valueLookup = [];
        foreach (($this->relationLoaded('options') ? $this->options : $this->options()->with('values')->get()) as $o) {
            foreach ($o->values as $val) {
                $valueLookup[$val->id] = trim((string) $val->value);
            }
        }

        $normalize = function ($s) {
            $s = trim((string) $s);
            if ($s === '') return $s;
            if (function_exists('mb_strtolower')) return mb_strtolower($s, 'UTF-8');
            return strtolower($s);
        };

        $locationLabels = $locOpt->values->pluck('value')->map(fn($v) => trim((string)$v))->filter()->values()->all();
        $locationIndex = [];
        foreach ($locationLabels as $label) {
            $locationIndex[$normalize($label)] = $label; // normalized => original
        }

        $map = [];
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
        foreach ($variants as $v) {
            $labels = [];
            // Preferred: option_ids referencing ProductOptionValue IDs
            $optIdsRaw = $v->option_ids ?? null;
            if (is_string($optIdsRaw) && $optIdsRaw !== '') {
                $ids = json_decode($optIdsRaw, true) ?: [];
                foreach ((array)$ids as $id) {
                    if (isset($valueLookup[$id])) { $labels[] = $valueLookup[$id]; }
                }
            }
            // Fallback: textual options array/json on variant
            if (empty($labels)) {
                $opts = $v->options ?? null;
                if (is_string($opts) && $opts !== '') {
                    $opts = json_decode($opts, true) ?: [];
                }
                if (is_array($opts) && !empty($opts)) {
                    // Keep only string-ish values
                    foreach ($opts as $ov) {
                        if (is_string($ov)) { $labels[] = $ov; }
                        elseif (is_array($ov)) {
                            foreach ($ov as $sub) { if (is_string($sub)) $labels[] = $sub; }
                        }
                    }
                }
            }

            // Match labels to known location values
            $norms = array_map($normalize, $labels);
            foreach ($norms as $n) {
                if (isset($locationIndex[$n])) {
                    $orig = $locationIndex[$n];
                    $map[$orig] = $map[$orig] ?? [];
                    $map[$orig][] = (int) $v->id;
                }
            }
        }

        // Ensure deterministic order and unique IDs
        foreach ($map as $k => $ids) {
            $map[$k] = array_values(array_unique(array_map('intval', $ids)));
        }
        return $map;
    }

    /**
     * Convenience: retrieve variants available for a given location label.
     */
    public function variantsForLocation(string $location)
    {
        $map = $this->locationVariantMap();
        $ids = $map[trim($location)] ?? [];
        if (empty($ids)) return collect();
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
        return $variants->whereIn('id', $ids)->values();
    }

    /**
     * Reviews published in our system that are associated to this product's vendor.
     *
     * These originate from the internal `reviews` table and are tied to a
     * vendor via `vendor_id` (not only to a specific product).
     */
    public function vendorPublishedReviews()
    {
        return $this->hasMany(Review::class, 'vendor_id', 'vendor_id');
    }

    /**
     * External reviews we ingest for this product's vendor (e.g. Google, etc.).
     */
    public function vendorExternalReviews()
    {
        return $this->hasMany(VendorReview::class, 'vendor_id', 'vendor_id');
    }

    /**
     * Unified collection of all reviews for the vendor behind this product.
     * Combines internal published reviews and external vendor reviews into a
     * single, normalized collection, newest first.
     *
     * @return \Illuminate\Support\Collection
     */
    public function vendorAllReviews()
    {
        $published = $this->vendorPublishedReviews()
            ->with(['user:id,name,first_name,last_name'])
            ->get()
            ->map(function (Review $r) {
                $name = trim(($r->user->first_name ?? '').' '.($r->user->last_name ?? ''));
                if ($name === '') {
                    $name = $r->user->name ?? 'Verified customer';
                }
                return [
                    'source' => 'published',
                    'rating' => (float) ($r->rating ?? 0),
                    'text' => (string) ($r->review_text ?? ''),
                    'reviewer' => $name,
                    'date' => optional($r->created_at)->toImmutable(),
                ];
            });

        $external = $this->vendorExternalReviews()
            ->get()
            ->map(function (VendorReview $r) {
                return [
                    'source' => (string) ($r->source ?? 'external'),
                    'rating' => (float) ($r->rating ?? 0),
                    'text' => (string) ($r->review_text ?? ''),
                    'reviewer' => (string) ($r->reviewer_name ?? 'Customer'),
                    'date' => optional($r->reviewed_at ?: $r->created_at)->toImmutable(),
                ];
            });

        return $published->merge($external)
            ->filter(fn ($x) => ($x['rating'] ?? 0) > 0 || ($x['text'] ?? '') !== '')
            ->sortByDesc(fn ($x) => $x['date'] ?? now())
            ->values();
    }

    /**
     * Aggregate rating stats for this product's vendor across all sources.
     *
     * @return array{avg: float|null, count: int}
     */
    public function vendorReviewStats(): array
    {
        // Pull light-weight aggregates directly from DB to avoid loading bodies
        $pub = $this->vendorPublishedReviews()
            ->selectRaw('COUNT(*) as c, AVG(rating) as a')
            ->first();

        $ext = $this->vendorExternalReviews()
            ->selectRaw('COUNT(*) as c, AVG(rating) as a')
            ->first();

        $c1 = (int) ($pub->c ?? 0); $a1 = (float) ($pub->a ?? 0);
        $c2 = (int) ($ext->c ?? 0); $a2 = (float) ($ext->a ?? 0);

        $count = $c1 + $c2;
        if ($count === 0) {
            return ['avg' => null, 'count' => 0];
        }

        // Weighted average by count across the two sources
        $avg = 0.0;
        if ($c1 > 0) { $avg += $a1 * $c1; }
        if ($c2 > 0) { $avg += $a2 * $c2; }
        $avg = round($avg / max(1, $count), 1);

        return ['avg' => $avg > 0 ? $avg : null, 'count' => $count];
    }

}
