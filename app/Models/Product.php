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
    ];

    protected $casts = [
        'meta_json' => 'array',
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
    public function getSummaryAttribute()
    {
        $cleanedText = strip_tags(html_entity_decode($this->body_html));
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

        // If already an absolute URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Prefer serving from Backend's storage if configured
        $backend = rtrim((string) env('BACKEND_ASSET_URL', env('BACKEND_URL', '')), '/');
        $clean = ltrim($path, '/');
        if ($backend) {
            return $backend . '/storage/' . $clean;
        }

        // Fallback to local storage URL (requires public/storage symlink)
        return asset('storage/' . $clean);
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

}
