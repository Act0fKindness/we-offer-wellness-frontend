<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Services\LocationCatalogService;
use App\Support\ProductRanking;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SeoMoneyPageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = $this->nationalPage();
        $products = $this->queryListings($page)
            ->take(12)
            ->values()
            ->map(function ($product) {
                $this->decorateListing($product);
                return $product;
            });

        $products = ProductRanking::sortCollection($products)->values();

        return $this->renderPage($request, 'holistic-therapies-uk', $page, $products);
    }

    public function showNearMe(Request $request, string $category)
    {
        $specialPages = $this->specialNearMePages();
        $page = $specialPages[$category] ?? $this->genericNearMePage($category);
        $products = $this->queryListings($page)
            ->take(12)
            ->values()
            ->map(function ($product) {
                $this->decorateListing($product);
                return $product;
            });

        $products = ProductRanking::sortCollection($products)->values();

        if ($products->isEmpty()) {
            abort(404);
        }

        return $this->renderPage($request, $category . '-near-me', $page, $products);
    }

    private function renderPage(Request $request, string $slug, array $page, Collection $products)
    {
        $popularLocations = $this->popularLocations($page);

        return view('seo-money.show', [
            'seo' => [
                'title' => $page['title'],
                'description' => $page['description'],
                'robots' => 'index,follow',
                'canonical' => url('/' . $slug),
            ],
            'page' => $page,
            'products' => $products,
            'popularLocations' => $popularLocations,
            'request' => $request,
        ]);
    }

    private function specialNearMePages(): array
    {
        return [
            'reiki' => [
                'title' => 'Reiki Near Me | Find Trusted Reiki Practitioners',
                'description' => 'Find Reiki near you with trusted practitioners. Browse online and in-person Reiki healing sessions across the UK through We Offer Wellness.',
                'h1' => 'Find Reiki Near You',
                'kicker' => 'High-intent search',
                'intro' => 'Use the search below to find Reiki practitioners near you, then browse live Reiki sessions, distance Reiki and online options.',
                'highlights' => [
                    'Online and in-person Reiki',
                    'Trusted practitioners',
                    'Search by town or postcode',
                ],
                'search_placeholder' => 'e.g. Maidstone or ME14',
                'search_helper' => 'Enter a town, county or postcode to see what is available nearby.',
                'query_terms' => ['reiki', 'distance reiki', 'reiki healing'],
                'category_slug' => 'reiki',
                'mode' => 'therapy',
                'fallback_mode' => true,
                'result_label' => 'Reiki sessions',
                'result_intro' => 'Browse the strongest Reiki matches available now.',
                'popular_location_paths' => [
                    '/locations/united-kingdom/kent',
                    '/locations/united-kingdom/london',
                    '/locations/united-kingdom/east-sussex/brighton-and-hove',
                    '/locations/united-kingdom/manchester',
                    '/locations/united-kingdom/west-midlands/birmingham',
                ],
                'related_links' => [
                    ['label' => 'Reiki sessions', 'href' => '/therapies/reiki'],
                    ['label' => 'Holistic therapy near me', 'href' => '/holistic-therapy-near-me'],
                    ['label' => 'Holistic therapies UK', 'href' => '/holistic-therapies-uk'],
                ],
                'faqs' => [
                    [
                        'q' => 'What does Reiki near me mean?',
                        'a' => 'It means you can browse Reiki sessions close to your location or choose online Reiki if you prefer to book remotely.',
                    ],
                    [
                        'q' => 'Can I book Reiki online?',
                        'a' => 'Yes. Many practitioners offer online or distance Reiki, so you can book from anywhere in the UK.',
                    ],
                    [
                        'q' => 'Do I need to know my exact postcode?',
                        'a' => 'No. A town, county or postcode is enough to start browsing local results.',
                    ],
                ],
            ],
            'sound-healing' => [
                'title' => 'Sound Healing Near Me | Sound Baths, Sessions & Workshops',
                'description' => 'Find sound healing near you. Browse sound baths, sound healing sessions, workshops and holistic events online or in person.',
                'h1' => 'Find Sound Healing Near You',
                'kicker' => 'Sound, frequency and calm',
                'intro' => 'Explore sound healing sessions and sound baths near you, then compare live listings, online options and workshops.',
                'highlights' => [
                    'Sound baths and live sessions',
                    'Online and in-person options',
                    'Workshops and event formats',
                ],
                'search_placeholder' => 'e.g. Leeds or BN1',
                'search_helper' => 'Search by town, county or postcode to find sound healing nearby.',
                'query_terms' => ['sound healing', 'sound bath', 'gong', 'vibrational therapy'],
                'category_slug' => 'sound-healing',
                'mode' => 'therapy',
                'fallback_mode' => true,
                'result_label' => 'Sound healing sessions',
                'result_intro' => 'Browse live sound healing and sound bath listings.',
                'popular_location_paths' => [
                    '/locations/united-kingdom/kent',
                    '/locations/united-kingdom/london',
                    '/locations/united-kingdom/surrey',
                    '/locations/united-kingdom/devon',
                    '/locations/united-kingdom/greater-manchester',
                ],
                'related_links' => [
                    ['label' => 'Sound healing sessions', 'href' => '/therapies/sound-healing'],
                    ['label' => 'Wellness classes near me', 'href' => '/wellness-classes-near-me'],
                    ['label' => 'Holistic therapies UK', 'href' => '/holistic-therapies-uk'],
                ],
                'faqs' => [
                    [
                        'q' => 'What is a sound bath?',
                        'a' => 'A sound bath is a guided relaxation experience using instruments such as gongs, bowls, chimes or tuning forks.',
                    ],
                    [
                        'q' => 'Can sound healing be online?',
                        'a' => 'Some practitioners offer online sound healing or recorded experiences, while others work in person.',
                    ],
                    [
                        'q' => 'Is sound healing a class or a session?',
                        'a' => 'It can be either. Some practitioners run one-to-one sessions, others run group classes, workshops or events.',
                    ],
                ],
            ],
            'holistic-therapy' => [
                'title' => 'Holistic Therapy Near Me | Find Trusted Holistic Therapists',
                'description' => 'Find trusted holistic therapy near you. Browse Reiki, reflexology, sound healing, breathwork, meditation and more online or in person.',
                'h1' => 'Find Holistic Therapy Near You',
                'kicker' => 'Broad wellness search',
                'intro' => 'Start with the broadest holistic therapy search, then narrow by location, format and therapy type.',
                'highlights' => [
                    'Reiki, reflexology and more',
                    'Online and in-person listings',
                    'Therapies, classes and workshops',
                ],
                'search_placeholder' => 'e.g. Kent or SW1',
                'search_helper' => 'Search by town, county or postcode for holistic therapy nearby.',
                'query_terms' => ['reiki', 'sound healing', 'reflexology', 'breathwork', 'massage', 'hypnotherapy', 'meditation', 'acupuncture', 'somatic experiencing'],
                'category_slug' => 'holistic-therapy',
                'mode' => 'broad',
                'fallback_mode' => true,
                'result_label' => 'Holistic therapy sessions',
                'result_intro' => 'Browse a wider mix of trusted holistic therapies now.',
                'popular_location_paths' => [
                    '/locations/united-kingdom/kent',
                    '/locations/united-kingdom/london',
                    '/locations/united-kingdom/greater-manchester',
                    '/locations/united-kingdom/east-sussex',
                    '/locations/united-kingdom/shire',
                ],
                'related_links' => [
                    ['label' => 'Reiki near me', 'href' => '/reiki-near-me'],
                    ['label' => 'Sound healing near me', 'href' => '/sound-healing-near-me'],
                    ['label' => 'Holistic therapies UK', 'href' => '/holistic-therapies-uk'],
                ],
                'faqs' => [
                    [
                        'q' => 'What counts as holistic therapy?',
                        'a' => 'Holistic therapy includes wellbeing approaches that support the whole person, such as Reiki, reflexology, massage, breathwork and sound healing.',
                    ],
                    [
                        'q' => 'Can I filter by online or in-person?',
                        'a' => 'Yes. Use the live listings and the location search to narrow your results.',
                    ],
                    [
                        'q' => 'What if nothing is showing for my town?',
                        'a' => 'Try a nearby town, county page or the UK-wide hub to keep moving toward a live booking.',
                    ],
                ],
            ],
            'wellness-classes' => [
                'title' => 'Wellness Classes Near Me | Yoga, Meditation, Breathwork & Sound Healing',
                'description' => 'Find wellness classes near you. Browse yoga, meditation, breathwork, sound healing, workshops and wellbeing classes online or in person.',
                'h1' => 'Find Wellness Classes Near You',
                'kicker' => 'Classes and workshops',
                'intro' => 'Explore classes and group sessions near you, from yoga and meditation to breathwork, sound healing and workshops.',
                'highlights' => [
                    'Yoga and meditation classes',
                    'Breathwork and sound healing',
                    'Online and in-person listings',
                ],
                'search_placeholder' => 'e.g. Brighton or LS1',
                'search_helper' => 'Search by town, county or postcode for a class near you.',
                'query_terms' => ['yoga', 'meditation', 'breathwork', 'sound healing', 'workshop', 'class'],
                'category_slug' => 'wellness-classes',
                'mode' => 'class',
                'fallback_mode' => true,
                'result_label' => 'Wellness classes',
                'result_intro' => 'Browse live classes, workshops and group wellbeing sessions.',
                'popular_location_paths' => [
                    '/locations/united-kingdom/london',
                    '/locations/united-kingdom/east-sussex/brighton-and-hove',
                    '/locations/united-kingdom/kent',
                    '/locations/united-kingdom/greater-manchester',
                    '/locations/united-kingdom/greater-london',
                ],
                'related_links' => [
                    ['label' => 'Sound healing near me', 'href' => '/sound-healing-near-me'],
                    ['label' => 'Holistic therapy near me', 'href' => '/holistic-therapy-near-me'],
                    ['label' => 'Therapies', 'href' => '/therapies'],
                ],
                'faqs' => [
                    [
                        'q' => 'What kinds of classes are included?',
                        'a' => 'Yoga, meditation, breathwork, sound healing sessions and wider wellbeing workshops can all appear here.',
                    ],
                    [
                        'q' => 'Are online classes included?',
                        'a' => 'Yes. Online classes and workshops can appear alongside in-person options.',
                    ],
                    [
                        'q' => 'Can I search by location?',
                        'a' => 'Yes. Enter a town, county or postcode to focus on classes near you.',
                    ],
                ],
            ],
        ];
    }

    private function nationalPage(): array
    {
        return [
            'title' => 'Holistic Therapies UK | Book Trusted Therapists Online & In Person',
            'description' => 'Explore holistic therapies across the UK. Find Reiki, sound healing, reflexology, breathwork, meditation, coaching and more with trusted practitioners.',
            'h1' => 'Holistic Therapies Across the UK',
            'kicker' => 'UK-wide discovery',
            'intro' => 'Browse the UK-wide hub for holistic therapy listings, then narrow by location, therapy type or format.',
            'highlights' => [
                'UK-wide listings',
                'Online and in-person options',
                'Trusted practitioners and venues',
            ],
            'search_placeholder' => 'e.g. Kent, London or M1',
            'search_helper' => 'Use a location or postcode to narrow the UK-wide results.',
            'query_terms' => ['reiki', 'sound healing', 'reflexology', 'breathwork', 'massage', 'hypnotherapy', 'meditation', 'acupuncture', 'somatic experiencing'],
            'category_slug' => 'holistic-therapies-uk',
            'mode' => 'broad',
            'fallback_mode' => true,
            'result_label' => 'UK holistic therapy listings',
            'result_intro' => 'Browse live holistic therapy listings across the UK.',
            'popular_location_paths' => [
                '/locations/united-kingdom',
                '/locations/united-kingdom/kent',
                '/locations/united-kingdom/london',
                '/locations/united-kingdom/east-sussex',
                '/locations/united-kingdom/greater-manchester',
            ],
            'related_links' => [
                ['label' => 'Reiki near me', 'href' => '/reiki-near-me'],
                ['label' => 'Sound healing near me', 'href' => '/sound-healing-near-me'],
                ['label' => 'Wellness classes near me', 'href' => '/wellness-classes-near-me'],
            ],
            'faqs' => [
                [
                    'q' => 'Is this the main UK-wide landing page?',
                    'a' => 'Yes. It is the national entry point for holistic therapy discovery on We Offer Wellness.',
                ],
                [
                    'q' => 'Can I find online sessions here?',
                    'a' => 'Yes. The listings can include online and in-person experiences.',
                ],
                [
                    'q' => 'Should I still use location pages?',
                    'a' => 'Yes. The UK-wide page is a hub, while location pages are best when you want something specific to a county or town.',
                ],
            ],
        ];
    }

    private function genericNearMePage(string $slug): array
    {
        $human = $this->humanizeSlug($slug);
        $mode = $this->inferModeFromSlug($slug);

        $page = [
            'title' => $human . ' Near Me | Find Live Listings',
            'description' => 'Browse ' . $human . ' near you with live listings, trusted providers and nearby locations across We Offer Wellness.',
            'h1' => 'Find ' . $human . ' Near You',
            'kicker' => 'Category search',
            'intro' => 'Use the search below to find ' . $human . ' near you, then compare live listings and nearby locations.',
            'highlights' => [
                'Online and in-person options',
                'Search by town or postcode',
                'Live listings across the UK',
            ],
            'search_placeholder' => 'e.g. Maidstone or M14',
            'search_helper' => 'Enter a town, county or postcode to see what is available nearby.',
            'query_terms' => [$human, $slug],
            'category_slug' => $slug,
            'mode' => $mode,
            'fallback_mode' => false,
            'result_label' => $human,
            'result_intro' => 'Browse live ' . $human . ' listings now.',
            'popular_location_paths' => [
                '/locations/united-kingdom/kent',
                '/locations/united-kingdom/london',
                '/locations/united-kingdom/east-sussex',
                '/locations/united-kingdom/greater-manchester',
                '/locations/united-kingdom/manchester',
            ],
            'related_links' => [
                ['label' => 'Holistic therapy near me', 'href' => '/holistic-therapy-near-me'],
                ['label' => 'Holistic therapies UK', 'href' => '/holistic-therapies-uk'],
                ['label' => 'Therapies', 'href' => '/therapies'],
            ],
            'faqs' => [
                [
                    'q' => 'What does this near me page do?',
                    'a' => 'It gives you a canonical entry point for the category search so people can browse live results without creating duplicate URL families.',
                ],
                [
                    'q' => 'Can I still search by location?',
                    'a' => 'Yes. Use the location search box to narrow the results by town, county or postcode.',
                ],
                [
                    'q' => 'What happens if there are no results?',
                    'a' => 'If there are no live matches for this category yet, the page is not created as an indexable public page.',
                ],
            ],
        ];

        return $page;
    }

    private function queryListings(array $page): Collection
    {
        $keywords = array_values(array_filter(array_map('trim', (array) ($page['query_terms'] ?? []))));
        $mode = (string) ($page['mode'] ?? 'therapy');
        $categorySlug = trim((string) ($page['category_slug'] ?? ''));
        $fallbackMode = filter_var($page['fallback_mode'] ?? false, FILTER_VALIDATE_BOOL);
        $productBuilder = Product::query()
            ->with(['media', 'category', 'options.values', 'vendor.locations', 'vendor.tiers', 'vendor.user.settings'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->withMax('variants', 'price')
            ->whereHas('status', function ($query): void {
                $query->whereIn('status', ['live', 'approved']);
            });

        $productQuery = function ($query) use ($keywords, $categorySlug, $mode): void {
            $this->applyKeywordFilters($query, $keywords, $categorySlug);

            if ($mode === 'class') {
                $query->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%class%'")
                    ->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%workshop%'")
                    ->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%event%'");
            }
        };

        $offeringQuery = function ($query) use ($keywords, $categorySlug, $mode): void {
            $this->applyOfferingKeywordFilters($query, $keywords, $categorySlug);

            if ($mode === 'class') {
                $query->orWhereRaw("LOWER(COALESCE(title,'')) like '%class%'")
                    ->orWhereRaw("LOWER(COALESCE(title,'')) like '%workshop%'")
                    ->orWhereRaw("LOWER(COALESCE(title,'')) like '%event%'")
                    ->orWhereHas('type', function ($typeQuery): void {
                        $typeQuery->whereRaw("LOWER(COALESCE(name,'')) like '%class%'")
                            ->orWhereRaw("LOWER(COALESCE(name,'')) like '%workshop%'")
                            ->orWhereRaw("LOWER(COALESCE(name,'')) like '%event%'");
                    });
            }
        };

        $productItems = $productBuilder
            ->where(function ($query) use ($productQuery): void {
                $productQuery($query);
            })
            ->get()
            ->map(function (Product $product): Product {
                $product->setAttribute('vendor_name', $product->vendor?->vendor_name ?? null);
                return $product;
            });

        $offeringItems = OfferingV3::query()
            ->with(['media', 'category', 'type', 'coverMedia', 'vendor.locations', 'vendor.tiers', 'vendor.user.settings'])
            ->whereIn('status', ['live', 'approved'])
            ->where(function ($query) use ($offeringQuery): void {
                $offeringQuery($query);
            })
            ->get()
            ->map(function (OfferingV3 $offering): OfferingV3 {
                $offering->setAttribute('vendor_name', $offering->vendor?->vendor_name ?? null);
                $offering->setAttribute('product_type', (string) ($offering->type?->name ?? $offering->category?->name ?? 'experience'));
                $offering->setAttribute('tags_list', trim(implode(',', array_filter([
                    (string) ($offering->category?->name ?? ''),
                    (string) ($offering->type?->name ?? ''),
                ]))));

                return $offering;
            });

        $items = ProductRanking::sortCollection($productItems->concat($offeringItems));

        if ($items->isNotEmpty()) {
            return $items->values();
        }

        if (!$fallbackMode) {
            return collect();
        }

        $fallbackProducts = Product::query()
                ->with(['media', 'category', 'options.values', 'vendor.locations', 'vendor.tiers', 'vendor.user.settings'])
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->withMin('variants', 'price')
                ->withMax('variants', 'price')
                ->whereHas('status', function ($query): void {
                    $query->whereIn('status', ['live', 'approved']);
                })
                ->whereRaw("LOWER(COALESCE(product_type,'')) like '%therap%'")
                ->get()
                ->map(function (Product $product): Product {
                    $product->setAttribute('vendor_name', $product->vendor?->vendor_name ?? null);
                    return $product;
                });

        $fallbackOfferings = OfferingV3::query()
            ->with(['media', 'category', 'type', 'coverMedia', 'vendor.locations', 'vendor.tiers', 'vendor.user.settings'])
            ->whereIn('status', ['live', 'approved'])
            ->where(function ($query): void {
                $query->whereRaw("LOWER(COALESCE(title,'')) like '%therap%'")
                    ->orWhereHas('category', function ($categoryQuery): void {
                        $categoryQuery->whereRaw("LOWER(COALESCE(name,'')) like '%therap%'");
                    })
                    ->orWhereHas('type', function ($typeQuery): void {
                        $typeQuery->whereRaw("LOWER(COALESCE(name,'')) like '%therap%'");
                    });
            })
            ->get()
            ->map(function (OfferingV3 $offering): OfferingV3 {
                $offering->setAttribute('vendor_name', $offering->vendor?->vendor_name ?? null);
                $offering->setAttribute('product_type', (string) ($offering->type?->name ?? $offering->category?->name ?? 'experience'));
                $offering->setAttribute('tags_list', trim(implode(',', array_filter([
                    (string) ($offering->category?->name ?? ''),
                    (string) ($offering->type?->name ?? ''),
                ]))));

                return $offering;
            });

        return ProductRanking::sortCollection($fallbackProducts->concat($fallbackOfferings))->values();
    }

    private function applyKeywordFilters($query, array $keywords, string $categorySlug): void
    {
        $query->where(function ($inner) use ($keywords, $categorySlug): void {
            if ($categorySlug !== '') {
                $needle = '%' . str_replace('-', ' ', mb_strtolower($categorySlug)) . '%';
                $inner->orWhereRaw("LOWER(COALESCE(title,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(product_type,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(meta_json->therapy_slug, '')) like ?", [Str::slug($categorySlug)])
                    ->orWhereHas('category', function ($categoryQuery) use ($needle): void {
                        $categoryQuery->whereRaw("LOWER(COALESCE(name,'')) like ?", [$needle]);
                    });
            }

            foreach ($keywords as $keyword) {
                $needle = '%' . mb_strtolower($keyword) . '%';
                $inner->orWhereRaw("LOWER(COALESCE(title,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(product_type,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(meta_json->therapy_slug, '')) like ?", [Str::slug($keyword)])
                    ->orWhereHas('category', function ($categoryQuery) use ($needle): void {
                        $categoryQuery->whereRaw("LOWER(COALESCE(name,'')) like ?", [$needle]);
                    });
            }
        });
    }

    private function applyOfferingKeywordFilters($query, array $keywords, string $categorySlug): void
    {
        $query->where(function ($inner) use ($keywords, $categorySlug): void {
            if ($categorySlug !== '') {
                $needle = '%' . str_replace('-', ' ', mb_strtolower($categorySlug)) . '%';
                $inner->orWhereRaw("LOWER(COALESCE(title,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(summary,'')) like ?", [$needle])
                    ->orWhereHas('category', function ($categoryQuery) use ($needle): void {
                        $categoryQuery->whereRaw("LOWER(COALESCE(name,'')) like ?", [$needle]);
                    })
                    ->orWhereHas('type', function ($typeQuery) use ($needle): void {
                        $typeQuery->whereRaw("LOWER(COALESCE(name,'')) like ?", [$needle]);
                    });
            }

            foreach ($keywords as $keyword) {
                $needle = '%' . mb_strtolower($keyword) . '%';
                $inner->orWhereRaw("LOWER(COALESCE(title,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(summary,'')) like ?", [$needle])
                    ->orWhereHas('category', function ($categoryQuery) use ($needle): void {
                        $categoryQuery->whereRaw("LOWER(COALESCE(name,'')) like ?", [$needle]);
                    })
                    ->orWhereHas('type', function ($typeQuery) use ($needle): void {
                        $typeQuery->whereRaw("LOWER(COALESCE(name,'')) like ?", [$needle]);
                    });
            }
        });
    }

    private function decorateListing($listing): void
    {
        if ($listing instanceof Product) {
            $listing->setAttribute('vendor_name', $listing->vendor?->vendor_name ?? null);
            return;
        }

        if ($listing instanceof OfferingV3) {
            $listing->setAttribute('vendor_name', $listing->vendor?->vendor_name ?? null);
            $listing->setAttribute('product_type', (string) ($listing->type?->name ?? $listing->category?->name ?? 'experience'));
            $listing->setAttribute('tags_list', trim(implode(',', array_filter([
                (string) ($listing->category?->name ?? ''),
                (string) ($listing->type?->name ?? ''),
            ]))));
        }
    }

    private function popularLocations(array $page): array
    {
        $catalog = app(LocationCatalogService::class)->load();
        $paths = array_values(array_filter((array) ($page['popular_location_paths'] ?? [])));
        $flat = collect((array) data_get($catalog, 'flat', []));

        if ($paths !== []) {
            $found = [];
            foreach ($paths as $path) {
                $node = $flat->first(fn (array $location): bool => (string) ($location['path'] ?? '') === $path);
                if ($node !== null) {
                    $found[] = $node;
                }
            }

            if ($found !== []) {
                return $found;
            }
        }

        return $flat
            ->filter(fn (array $location): bool => !empty($location['path']) && empty($location['online']) && (int) data_get($location, 'counts.total', 0) > 0)
            ->sortByDesc(fn (array $location): int => (int) data_get($location, 'counts.total', 0))
            ->take(6)
            ->values()
            ->all();
    }

    private function humanizeSlug(string $slug): string
    {
        $slug = trim(str_replace(['_', '+'], '-', strtolower($slug)));

        return Str::of($slug)
            ->replace('-', ' ')
            ->headline()
            ->toString();
    }

    private function inferModeFromSlug(string $slug): string
    {
        $slug = strtolower($slug);

        if (str_contains($slug, 'class') || str_contains($slug, 'workshop') || str_contains($slug, 'event')) {
            return 'class';
        }

        return 'broad';
    }
}
