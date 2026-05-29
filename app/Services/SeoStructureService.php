<?php

namespace App\Services;

use App\Models\OfferingV3;
use App\Models\Product;
use Illuminate\Support\Str;

class SeoStructureService
{
    private const TYPE_DEFINITIONS = [
        'therapies' => [
            'singular' => 'therapy',
            'plural' => 'therapies',
            'page_label' => 'Therapies',
            'seo_label' => 'therapy sessions',
            'noun' => 'sessions',
            'entity_label' => 'practitioners',
            'cta_view' => 'View session',
            'cta_book' => 'Book session',
            'schema' => 'Service',
            'bookable' => true,
            'dated' => false,
            'use_event_schema' => false,
            'appear_in_filters' => true,
            'can_combine_locations' => true,
            'can_combine_categories' => true,
        ],
        'classes' => [
            'singular' => 'class',
            'plural' => 'classes',
            'page_label' => 'Classes',
            'seo_label' => 'classes',
            'noun' => 'classes',
            'entity_label' => 'instructors',
            'cta_view' => 'View class',
            'cta_book' => 'Book class',
            'schema' => 'Event',
            'bookable' => true,
            'dated' => true,
            'use_event_schema' => true,
            'appear_in_filters' => true,
            'can_combine_locations' => true,
            'can_combine_categories' => true,
        ],
        'events' => [
            'singular' => 'event',
            'plural' => 'events',
            'page_label' => 'Events',
            'seo_label' => 'events',
            'noun' => 'events',
            'entity_label' => 'facilitators',
            'cta_view' => 'View event',
            'cta_book' => 'Book event',
            'schema' => 'Event',
            'bookable' => true,
            'dated' => true,
            'use_event_schema' => true,
            'appear_in_filters' => true,
            'can_combine_locations' => true,
            'can_combine_categories' => true,
        ],
        'retreats' => [
            'singular' => 'retreat',
            'plural' => 'retreats',
            'page_label' => 'Retreats',
            'seo_label' => 'retreats',
            'noun' => 'retreats',
            'entity_label' => 'hosts',
            'cta_view' => 'View retreat',
            'cta_book' => 'Book retreat',
            'schema' => 'Event',
            'bookable' => true,
            'dated' => true,
            'use_event_schema' => true,
            'appear_in_filters' => true,
            'can_combine_locations' => true,
            'can_combine_categories' => true,
        ],
        'workshops' => [
            'singular' => 'workshop',
            'plural' => 'workshops',
            'page_label' => 'Workshops',
            'seo_label' => 'workshops',
            'noun' => 'workshops',
            'entity_label' => 'facilitators',
            'cta_view' => 'View workshop',
            'cta_book' => 'Book workshop',
            'schema' => 'Event',
            'bookable' => true,
            'dated' => true,
            'use_event_schema' => true,
            'appear_in_filters' => true,
            'can_combine_locations' => true,
            'can_combine_categories' => true,
        ],
        'courses' => [
            'singular' => 'course',
            'plural' => 'courses',
            'page_label' => 'Courses',
            'seo_label' => 'courses',
            'noun' => 'courses',
            'entity_label' => 'teachers',
            'cta_view' => 'View course',
            'cta_book' => 'Start course',
            'schema' => 'Course',
            'bookable' => true,
            'dated' => false,
            'use_event_schema' => false,
            'appear_in_filters' => true,
            'can_combine_locations' => true,
            'can_combine_categories' => true,
        ],
        'readings' => [
            'singular' => 'reading',
            'plural' => 'readings',
            'page_label' => 'Readings',
            'seo_label' => 'readings',
            'noun' => 'readings',
            'entity_label' => 'readers',
            'cta_view' => 'View reading',
            'cta_book' => 'Book reading',
            'schema' => 'Service',
            'bookable' => true,
            'dated' => false,
            'use_event_schema' => false,
            'appear_in_filters' => true,
            'can_combine_locations' => true,
            'can_combine_categories' => true,
        ],
        'gifts' => [
            'singular' => 'gift',
            'plural' => 'gifts',
            'page_label' => 'Gifts',
            'seo_label' => 'gifts',
            'noun' => 'gifts',
            'entity_label' => 'providers',
            'cta_view' => 'View gift',
            'cta_book' => 'Buy gift',
            'schema' => 'Product',
            'bookable' => true,
            'dated' => false,
            'use_event_schema' => false,
            'appear_in_filters' => true,
            'can_combine_locations' => false,
            'can_combine_categories' => false,
        ],
    ];

    private const CATEGORY_LABEL_OVERRIDES = [
        'reflexology-and-reiki' => 'Reflexology & Reiki',
        'yoga-and-meditation' => 'Yoga & Meditation',
        'sound-healing-and-meditation' => 'Sound Healing & Meditation',
        'reiki-and-breathwork' => 'Reiki & Breathwork',
        'birth-chart-reading-and-tarot' => 'Birth Chart Reading & Tarot',
    ];

    private const CATEGORY_NOUN_OVERRIDES = [
        'reiki' => [
            'therapies' => 'sessions',
            'classes' => 'classes',
            'events' => 'events',
            'workshops' => 'workshops',
            'retreats' => 'retreats',
        ],
        'yoga' => [
            'therapies' => 'therapy sessions',
            'classes' => 'classes',
            'events' => 'events',
            'workshops' => 'workshops',
            'retreats' => 'retreats',
        ],
        'sound-healing' => [
            'therapies' => 'sessions',
            'classes' => 'classes',
            'events' => 'events',
            'workshops' => 'workshops',
            'retreats' => 'retreats',
        ],
        'breathwork' => [
            'therapies' => 'sessions',
            'classes' => 'classes',
            'events' => 'events',
            'workshops' => 'workshops',
            'retreats' => 'retreats',
        ],
        'reflexology' => [
            'therapies' => 'treatments',
            'classes' => 'classes',
            'events' => 'events',
            'workshops' => 'workshops',
        ],
        'meditation' => [
            'therapies' => 'sessions',
            'classes' => 'classes',
            'events' => 'events',
            'workshops' => 'workshops',
            'retreats' => 'retreats',
        ],
        'crystal-healing' => [
            'therapies' => 'sessions',
            'classes' => 'classes',
            'events' => 'events',
            'workshops' => 'workshops',
            'retreats' => 'retreats',
        ],
        'massage' => [
            'therapies' => 'treatments',
            'classes' => 'classes',
        ],
        'acupuncture' => [
            'therapies' => 'treatments',
        ],
        'birth-chart-reading' => [
            'readings' => 'readings',
            'events' => 'events',
            'workshops' => 'workshops',
        ],
    ];

    private const CATEGORY_ENTITY_OVERRIDES = [
        'reiki' => [
            'therapies' => 'practitioners',
            'classes' => 'instructors',
            'events' => 'facilitators',
            'workshops' => 'facilitators',
        ],
        'yoga' => [
            'therapies' => 'practitioners',
            'classes' => 'instructors',
            'events' => 'facilitators',
            'retreats' => 'hosts',
        ],
        'sound-healing' => [
            'therapies' => 'practitioners',
            'classes' => 'instructors',
            'events' => 'facilitators',
            'workshops' => 'facilitators',
        ],
        'breathwork' => [
            'therapies' => 'practitioners',
            'classes' => 'instructors',
            'events' => 'facilitators',
            'workshops' => 'facilitators',
            'retreats' => 'hosts',
        ],
        'reflexology' => [
            'therapies' => 'practitioners',
            'classes' => 'instructors',
            'events' => 'facilitators',
            'workshops' => 'facilitators',
        ],
        'meditation' => [
            'therapies' => 'practitioners',
            'classes' => 'instructors',
            'events' => 'facilitators',
            'workshops' => 'facilitators',
            'retreats' => 'hosts',
        ],
        'birth-chart-reading' => [
            'readings' => 'readers',
            'events' => 'facilitators',
            'workshops' => 'facilitators',
        ],
        'massage' => [
            'therapies' => 'therapists',
            'classes' => 'instructors',
        ],
        'acupuncture' => [
            'therapies' => 'practitioners',
        ],
    ];

    public function typeDefinitions(): array
    {
        return self::TYPE_DEFINITIONS;
    }

    public function typeDefinition(string $type): array
    {
        $type = $this->canonicalTypeKey($type);

        return self::TYPE_DEFINITIONS[$type] ?? self::TYPE_DEFINITIONS['therapies'];
    }

    public function canonicalTypeKey(string $type): string
    {
        $type = strtolower(trim($type));
        $type = str_replace(['_', ' '], '-', $type);

        return match ($type) {
            'therapy', 'therapist', 'therapists' => 'therapies',
            'class', 'classes' => 'classes',
            'event', 'events' => 'events',
            'retreat', 'retreats' => 'retreats',
            'workshop', 'workshops' => 'workshops',
            'course', 'courses' => 'courses',
            'reading', 'readings' => 'readings',
            'gift', 'gifts', 'voucher', 'vouchers' => 'gifts',
            default => $type ?: 'therapies',
        };
    }

    public function typePageCopy(string $type): array
    {
        $definition = $this->typeDefinition($type);
        $title = $definition['page_label'];
        $entity = $definition['entity_label'];
        $seoLabel = $definition['seo_label'];

        return [
            'kicker' => Str::headline($definition['page_label']),
            'title' => $title,
            'description' => 'Explore ' . $seoLabel . ' from trusted ' . $entity . '. Browse live online and in-person options across We Offer Wellness.',
            'intro' => 'Browse live ' . $seoLabel . ' from trusted ' . $entity . '. Use the filters to find the right option by location, format or focus.',
            'points' => [
                'Live listings first',
                'Online and in-person options',
                'Trusted providers and clear discovery',
            ],
            'primary_cta' => ['label' => 'Browse ' . $definition['plural'], 'href' => '#landing-products'],
            'secondary_cta' => ['label' => 'Search all results', 'href' => '/search?type=' . $definition['plural']],
            'schema' => $definition['schema'],
            'entity_label' => $entity,
            'noun' => $definition['noun'],
        ];
    }

    public function categoryLabel(string $category): string
    {
        $slug = $this->categorySlug($category);

        if ($slug === '') {
            return '';
        }

        if (isset(self::CATEGORY_LABEL_OVERRIDES[$slug])) {
            return self::CATEGORY_LABEL_OVERRIDES[$slug];
        }

        $label = Str::headline(str_replace(['_', '+'], '-', $category));
        $label = preg_replace('/\bAnd\b/', '&', (string) $label) ?: (string) $label;
        $label = preg_replace('/\s+&\s+/', ' & ', (string) $label) ?: (string) $label;

        return trim((string) $label);
    }

    public function categorySlug(string $category): string
    {
        return Str::slug(str_replace(['_', '+'], '-', trim($category)));
    }

    public function categoryNoun(string $type, string $category): string
    {
        $type = $this->canonicalTypeKey($type);
        $slug = $this->categorySlug($category);

        return self::CATEGORY_NOUN_OVERRIDES[$slug][$type]
            ?? $this->typeDefinition($type)['noun']
            ?? 'sessions';
    }

    public function categoryEntityLabel(string $type, string $category): string
    {
        $type = $this->canonicalTypeKey($type);
        $slug = $this->categorySlug($category);

        return self::CATEGORY_ENTITY_OVERRIDES[$slug][$type]
            ?? ($this->typeDefinition($type)['entity_label'] ?? 'practitioners');
    }

    public function combinationCopy(string $type, string $category, ?string $location = null): array
    {
        $type = $this->canonicalTypeKey($type);
        $categoryLabel = $this->categoryLabel($category);
        $noun = $this->categoryNoun($type, $category);
        $entity = $this->categoryEntityLabel($type, $category);
        $locationLabel = $location !== null && trim($location) !== '' ? $this->locationLabel($location) : null;

        $baseTitle = trim($categoryLabel . ' ' . $noun);
        $title = $locationLabel ? $baseTitle . ' in ' . $locationLabel : $baseTitle;

        return [
            'title' => $title,
            'h1' => $title,
            'meta_title' => $locationLabel
                ? $title . ' | Find Trusted ' . $categoryLabel . ' ' . Str::headline($entity)
                : $title . ' | Find Trusted ' . $categoryLabel . ' ' . Str::headline($entity),
            'description' => $locationLabel
                ? 'Find ' . $baseTitle . ' in ' . $locationLabel . ' with trusted ' . $entity . '. Browse online and in-person options, prices and availability.'
                : 'Explore ' . $baseTitle . ' with trusted ' . $entity . '. Browse online and in-person options, prices and availability.',
            'intro' => $locationLabel
                ? 'Browse live ' . $baseTitle . ' in ' . $locationLabel . '. Compare online and in-person options from trusted ' . $entity . '.'
                : 'Browse live ' . $baseTitle . '. Compare online and in-person options from trusted ' . $entity . '.',
            'kicker' => Str::headline($this->typeDefinition($type)['page_label'] ?? ucfirst($type)),
            'entity_label' => $entity,
            'noun' => $noun,
            'type' => $type,
            'category' => $categoryLabel,
            'location' => $locationLabel,
        ];
    }

    public function locationLabel(string $location): string
    {
        $location = trim(str_replace(['_', '+'], '-', $location));
        $location = str_replace('-', ' ', $location);

        return Str::headline($location);
    }

    public function inferTypeKeyFromText(string $value): string
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return 'therapies';
        }

        if (str_contains($value, 'gift') || str_contains($value, 'voucher') || str_contains($value, 'card')) {
            return 'gifts';
        }

        if (str_contains($value, 'course')) {
            return 'courses';
        }

        if (str_contains($value, 'reading')) {
            return 'readings';
        }

        if (str_contains($value, 'retreat')) {
            return 'retreats';
        }

        if (str_contains($value, 'workshop')) {
            return 'workshops';
        }

        if (str_contains($value, 'event')) {
            return 'events';
        }

        if (str_contains($value, 'class')) {
            return 'classes';
        }

        return 'therapies';
    }

    public function inferTypeKeyFromProduct(Product $product): string
    {
        return $this->inferTypeKeyFromText(implode(' ', array_filter([
            (string) ($product->product_type ?? ''),
            (string) ($product->tags_list ?? ''),
            (string) optional($product->category)->name,
            (string) data_get($product, 'meta_json.therapy_slug', ''),
        ])));
    }

    public function inferTypeKeyFromOffering(OfferingV3 $offering): string
    {
        return $this->inferTypeKeyFromText(implode(' ', array_filter([
            (string) optional($offering->type)->name,
            (string) optional($offering->category)->name,
            (string) ($offering->title ?? ''),
            (string) ($offering->summary ?? ''),
        ])));
    }
}
