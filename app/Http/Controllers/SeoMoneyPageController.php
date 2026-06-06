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

    public function showNearMe(Request $request, string $category, ?string $country = null, ?string $county = null, ?string $town = null)
    {
        $isFiltered = $request->hasAny(['place', 'postcode', 'city', 'region', 'county', 'country', 'lat', 'lng']);
        $routeLocationContext = $this->routeLocationContext($country, $county, $town);
        $locationContext = $this->activeLocationContext($request, $routeLocationContext);
        $hasExplicitLocation = $routeLocationContext !== []
            || trim((string) data_get($locationContext, 'path', '')) !== ''
            || trim((string) data_get($locationContext, 'label', '')) !== ''
            || $isFiltered;
        $specialPages = $this->specialNearMePages();
        $page = $specialPages[$category] ?? $this->genericNearMePage($category);
        $strictLocation = $routeLocationContext !== []
            || trim((string) data_get($locationContext, 'path', '')) !== ''
            || $isFiltered;

        $products = $this->queryListings($page, $locationContext, $strictLocation)
            ->take(12)
            ->values()
            ->map(function ($product) {
                $this->decorateListing($product);
                return $product;
            });

        $products = ProductRanking::sortCollection($products)->values();

        if ($products->isEmpty() && !$hasExplicitLocation) {
            $products = $this->queryListings($page)
                ->take(12)
                ->values()
                ->map(function ($product) {
                    $this->decorateListing($product);
                    return $product;
                });

            $products = ProductRanking::sortCollection($products)->values();
        }

        if ($products->isEmpty()) {
            abort(404);
        }

        $canonicalPath = $this->buildNearMePath($category, $locationContext);
        $queryKeys = array_values(array_diff(array_keys($request->query()), ['place', 'postcode', 'city', 'region', 'county', 'country', 'lat', 'lng']));

        if ($routeLocationContext === [] && !empty($locationContext['path'] ?? '') && $queryKeys === []) {
            return redirect()->to(url($canonicalPath), 301);
        }

        return $this->renderPage($request, $category . '-near-me', $page, $products, $locationContext, $isFiltered, $canonicalPath);
    }

    private function renderPage(Request $request, string $slug, array $page, Collection $products, array $locationContext = [], bool $isFiltered = false, ?string $canonicalPath = null)
    {
        $locationLabel = trim((string) data_get($locationContext, 'label', ''));
        if ($locationLabel !== '') {
            $page = $this->applyLocationToPage($page, $locationLabel);
        }

        $popularLocations = $this->popularLocations($page, $locationContext, $slug);
        $catalog = app(LocationCatalogService::class)->load();
        $canonicalPath = $canonicalPath ?: $this->buildNearMePath($slug, $locationContext);

        return view('seo-money.show', [
            'seo' => [
                'title' => $page['title'],
                'description' => $page['description'],
                'robots' => $isFiltered ? 'noindex,follow' : 'index,follow',
                'canonical' => url($canonicalPath),
            ],
            'page' => $page,
            'products' => $products,
            'popularLocations' => $popularLocations,
            'catalogSuggestions' => data_get($catalog, 'flat', []),
            'savedLocation' => $locationContext,
            'searchBreadcrumbUrl' => $this->buildSearchBreadcrumbUrl($page, $locationContext),
            'searchAction' => url('/' . $slug),
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
                'breadcrumb_search_query' => 'Reiki Healing',
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
                'breadcrumb_search_query' => 'Sound Healing',
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
                'breadcrumb_search_query' => 'Holistic Therapy',
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
                'breadcrumb_search_query' => 'Wellness Classes',
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
            'breadcrumb_search_query' => 'Holistic Therapies',
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

    private function queryListings(array $page, array $locationContext = [], bool $strictLocation = false): Collection
    {
        $keywords = array_values(array_filter(array_map('trim', (array) ($page['query_terms'] ?? []))));
        $mode = (string) ($page['mode'] ?? 'therapy');
        $categorySlug = trim((string) ($page['category_slug'] ?? ''));
        $fallbackMode = filter_var($page['fallback_mode'] ?? false, FILTER_VALIDATE_BOOL);
        $locationTerms = $this->locationContextTerms($locationContext);
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
            ->when($locationTerms !== [], function ($query) use ($locationTerms, $strictLocation): void {
                if ($strictLocation) {
                    $this->applyStrictLocationTermsToProducts($query, $locationTerms);
                    return;
                }

                $this->applyLocationTermsToProducts($query, $locationTerms);
            })
            ->get()
            ->map(function (Product $product) use ($locationContext): Product {
                $product->setAttribute('vendor_name', $product->vendor?->vendor_name ?? null);
                $product->setAttribute('matched_location_label', $this->preferredLocationLabel($product, $locationContext));
                return $product;
            });

        $offeringItems = OfferingV3::query()
            ->with(['media', 'category', 'type', 'coverMedia', 'vendor.locations', 'vendor.tiers', 'vendor.user.settings'])
            ->whereIn('status', ['live', 'approved'])
            ->where(function ($query) use ($offeringQuery): void {
                $offeringQuery($query);
            })
            ->when($locationTerms !== [], function ($query) use ($locationTerms, $strictLocation): void {
                if ($strictLocation) {
                    $this->applyStrictLocationTermsToOfferings($query, $locationTerms);
                    return;
                }

                $this->applyLocationTermsToOfferings($query, $locationTerms);
            })
            ->get()
            ->map(function (OfferingV3 $offering) use ($locationContext): OfferingV3 {
                $offering->setAttribute('vendor_name', $offering->vendor?->vendor_name ?? null);
                $offering->setAttribute('product_type', (string) ($offering->type?->name ?? $offering->category?->name ?? 'experience'));
                $offering->setAttribute('tags_list', trim(implode(',', array_filter([
                    (string) ($offering->category?->name ?? ''),
                    (string) ($offering->type?->name ?? ''),
                ]))));
                $offering->setAttribute('matched_location_label', $this->preferredLocationLabel($offering, $locationContext));

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
                $slugNeedle = '%' . Str::slug($categorySlug) . '%';
                $inner->orWhereRaw("LOWER(COALESCE(title,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(product_type,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.therapy_slug')), '')) like ?", [$slugNeedle])
                    ->orWhereHas('category', function ($categoryQuery) use ($needle): void {
                        $categoryQuery->whereRaw("LOWER(COALESCE(name,'')) like ?", [$needle]);
                    });
            }

            foreach ($keywords as $keyword) {
                $needle = '%' . mb_strtolower($keyword) . '%';
                $slugNeedle = '%' . Str::slug($keyword) . '%';
                $inner->orWhereRaw("LOWER(COALESCE(title,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(product_type,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like ?", [$needle])
                    ->orWhereRaw("LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.therapy_slug')), '')) like ?", [$slugNeedle])
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

    private function popularLocations(array $page, array $locationContext = [], string $slug = ''): array
    {
        $catalog = app(LocationCatalogService::class)->load();
        $paths = array_values(array_filter((array) ($page['popular_location_paths'] ?? [])));
        $flat = collect((array) data_get($catalog, 'flat', []));
        $savedLocation = $this->locationContextMatch($flat, $locationContext);

        if ($paths !== []) {
            $found = [];
            foreach ($paths as $path) {
                $node = $flat->first(fn (array $location): bool => (string) ($location['path'] ?? '') === $path);
                if ($node !== null) {
                    $found[] = $node;
                }
            }

            if ($found !== []) {
                if ($savedLocation !== null) {
                    array_unshift($found, $savedLocation);
                    $found = collect($found)
                        ->map(fn (array $location): array => $this->locationSearchLink($location, $slug))
                        ->unique(fn (array $location): string => (string) ($location['path'] ?? Str::slug((string) ($location['title'] ?? ''))))
                        ->values()
                        ->all();
                }

                return collect($found)->map(fn (array $location): array => $this->locationSearchLink($location, $slug))->values()->all();
            }
        }

        $results = $flat
            ->filter(fn (array $location): bool => !empty($location['path']) && empty($location['online']) && (int) data_get($location, 'counts.total', 0) > 0)
            ->sortByDesc(fn (array $location): int => (int) data_get($location, 'counts.total', 0))
            ->take(6)
            ->values()
            ->all();

        if ($savedLocation !== null) {
            array_unshift($results, $savedLocation);
            $results = collect($results)
                ->unique(fn (array $location): string => (string) ($location['path'] ?? Str::slug((string) ($location['title'] ?? ''))))
                ->map(fn (array $location): array => $this->locationSearchLink($location, $slug))
                ->values()
                ->all();
        } else {
            $results = collect($results)
                ->map(fn (array $location): array => $this->locationSearchLink($location, $slug))
                ->values()
                ->all();
        }

        return $results;
    }

    private function locationSearchLink(array $location, string $slug): array
    {
        $base = url('/' . ltrim($slug, '/'));
        $path = trim((string) ($location['path'] ?? ''));
        $location['search_url'] = $path !== ''
            ? $base . Str::after($path, '/locations')
            : $base;

        return $location;
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

    private function savedLocationContext(Request $request): array
    {
        $city = trim((string) $request->cookie('wow_city', ''));
        $region = trim((string) $request->cookie('wow_region', ''));
        $country = trim((string) $request->cookie('wow_country', ''));
        $lat = $request->cookie('wow_lat');
        $lng = $request->cookie('wow_lng');

        $locationContext = [
            'city' => $city,
            'region' => $region,
            'country' => $country,
        ];

        $label = trim(implode(', ', array_filter([$city, $region, $country])));
        $terms = $this->locationContextTerms($locationContext);
        $catalog = app(LocationCatalogService::class)->load();
        $flat = collect((array) data_get($catalog, 'flat', []));
        $matched = $this->locationContextMatch($flat, $locationContext);

        if ($matched !== null) {
            $label = trim((string) ($matched['title'] ?? $matched['label'] ?? $label));
            $city = trim((string) ($matched['town'] ?? $matched['city'] ?? $city));
            $region = trim((string) ($matched['county'] ?? $matched['district'] ?? $matched['region'] ?? $region));
            $country = trim((string) ($matched['country'] ?? $country));
            $lat = $matched['lat'] ?? $lat;
            $lng = $matched['lng'] ?? $lng;
        }

        return [
            'label' => $label,
            'city' => $city,
            'region' => $region,
            'country' => $country,
            'path' => (string) ($matched['path'] ?? ''),
            'lat' => is_numeric($lat) ? (float) $lat : null,
            'lng' => is_numeric($lng) ? (float) $lng : null,
            'terms' => $terms,
        ];
    }

    private function routeLocationContext(?string $country, ?string $county = null, ?string $town = null): array
    {
        $segments = array_values(array_filter([
            trim((string) $country),
            trim((string) $county),
            trim((string) $town),
        ], static fn (string $segment): bool => $segment !== ''));

        if ($segments === []) {
            return [];
        }

        $path = '/locations/' . implode('/', $segments);
        $catalog = app(LocationCatalogService::class)->load();
        $node = $this->locationContextByPath($catalog, $path);

        if ($node === null) {
            return [
                'label' => trim(implode(', ', array_map(fn (string $segment): string => Str::headline($segment), $segments))),
                'city' => $town ? Str::headline($town) : '',
                'region' => $county ? Str::headline($county) : '',
                'country' => Str::headline($country ?: ''),
                'path' => $path,
            ];
        }

        return [
            'label' => (string) ($node['title'] ?? $node['label'] ?? ''),
            'city' => (string) ($node['town'] ?? $node['city'] ?? ''),
            'region' => (string) ($node['county'] ?? $node['district'] ?? $node['region'] ?? ''),
            'country' => (string) ($node['country'] ?? ''),
            'lat' => is_numeric($node['lat'] ?? null) ? (float) $node['lat'] : null,
            'lng' => is_numeric($node['lng'] ?? null) ? (float) $node['lng'] : null,
            'path' => (string) ($node['path'] ?? $path),
            'terms' => $this->locationContextTerms([
                'city' => (string) ($node['town'] ?? $node['city'] ?? ''),
                'region' => (string) ($node['county'] ?? $node['district'] ?? $node['region'] ?? ''),
                'country' => (string) ($node['country'] ?? ''),
            ]),
        ];
    }

    private function activeLocationContext(Request $request, array $routeLocationContext = []): array
    {
        $city = trim((string) $request->query('city', ''));
        $region = trim((string) $request->query('region', $request->query('county', '')));
        $country = trim((string) $request->query('country', ''));
        $place = trim((string) $request->query('place', $request->query('postcode', '')));
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if ($city === '' && $place !== '') {
            $city = $place;
        }

        if ($routeLocationContext !== []) {
            $locationContext = array_merge([
                'label' => trim(implode(', ', array_filter([$city, $region, $country]))),
                'city' => $city,
                'region' => $region,
                'country' => $country,
                'lat' => is_numeric($lat) ? (float) $lat : null,
                'lng' => is_numeric($lng) ? (float) $lng : null,
            ], array_filter($routeLocationContext, static fn ($value): bool => $value !== null && $value !== ''));

            $locationContext['terms'] = $this->locationContextTerms($locationContext);

            return $locationContext;
        }

        $locationContext = [
            'label' => trim(implode(', ', array_filter([$city, $region, $country]))),
            'city' => $city,
            'region' => $region,
            'country' => $country,
            'lat' => is_numeric($lat) ? (float) $lat : null,
            'lng' => is_numeric($lng) ? (float) $lng : null,
        ];

        $catalog = app(LocationCatalogService::class)->load();
        $flat = collect((array) data_get($catalog, 'flat', []));
        $matched = $this->locationContextMatch($flat, $locationContext);

        if ($matched !== null) {
            $locationContext['label'] = (string) ($matched['title'] ?? $locationContext['label']);
            $locationContext['city'] = (string) ($matched['town'] ?? $matched['city'] ?? $locationContext['city']);
            $locationContext['region'] = (string) ($matched['county'] ?? $matched['district'] ?? $matched['region'] ?? $locationContext['region']);
            $locationContext['country'] = (string) ($matched['country'] ?? $locationContext['country']);
            $locationContext['lat'] = is_numeric($matched['lat'] ?? null) ? (float) $matched['lat'] : $locationContext['lat'];
            $locationContext['lng'] = is_numeric($matched['lng'] ?? null) ? (float) $matched['lng'] : $locationContext['lng'];
            $locationContext['path'] = (string) ($matched['path'] ?? '');
        } else {
            $savedLocation = $this->savedLocationContext($request);
            if (($locationContext['label'] ?? '') === '' && ($savedLocation['label'] ?? '') !== '') {
                return $savedLocation;
            }
        }

        $locationContext['terms'] = $this->locationContextTerms($locationContext);

        return $locationContext;
    }

    private function buildNearMePath(string $slug, array $locationContext = []): string
    {
        $base = '/' . ltrim($slug, '/');
        $path = trim((string) data_get($locationContext, 'path', ''));
        if ($path === '') {
            return $base;
        }

        return $base . Str::after($path, '/locations');
    }

    private function buildSearchBreadcrumbUrl(array $page, array $locationContext = []): string
    {
        $query = [];
        $what = $this->breadcrumbSearchQuery($page);
        if ($what !== '') {
            $query['what'] = $what;
        }

        $where = trim((string) data_get($locationContext, 'label', ''));
        if ($where !== '') {
            $query['where'] = $where;
        }

        if ($query === []) {
            return url('/search');
        }

        return url('/search') . '?' . http_build_query($query);
    }

    private function breadcrumbSearchQuery(array $page): string
    {
        $explicit = trim((string) data_get($page, 'breadcrumb_search_query', ''));
        if ($explicit !== '') {
            return $explicit;
        }

        $categorySlug = trim((string) data_get($page, 'category_slug', ''));
        if ($categorySlug !== '') {
            return $this->humanizeSearchTerm($categorySlug);
        }

        $terms = array_values(array_filter(array_map('trim', (array) data_get($page, 'query_terms', []))));
        if ($terms !== []) {
            return $this->humanizeSearchTerm($terms[0]);
        }

        $h1 = trim((string) data_get($page, 'h1', ''));
        if ($h1 !== '') {
            return $this->humanizeSearchTerm($h1);
        }

        return '';
    }

    private function humanizeSearchTerm(string $term): string
    {
        $term = trim($term);
        if ($term === '') {
            return '';
        }

        $term = str_replace(['-', '_'], ' ', $term);
        $term = preg_replace('/\bnear me\b/i', '', $term) ?? $term;
        $term = preg_replace('/\bnear you\b/i', '', $term) ?? $term;
        $term = preg_replace('/\buk\b/i', '', $term) ?? $term;
        $term = preg_replace('/\s+/', ' ', $term) ?? $term;

        return trim(Str::title($term));
    }

    private function applyLocationToPage(array $page, string $locationLabel): array
    {
        $locationLabel = trim($locationLabel);
        if ($locationLabel === '') {
            return $page;
        }

        $title = trim((string) ($page['title'] ?? ''));
        if ($title !== '' && str_contains($title, ' | ')) {
            [$lead, $tail] = array_pad(explode(' | ', $title, 2), 2, '');
            if ($lead !== '' && $tail !== '') {
                $page['title'] = trim($lead . ' in ' . $locationLabel . ' | ' . $tail);
            }
        }

        $h1 = trim((string) ($page['h1'] ?? ''));
        if ($h1 !== '' && !str_contains(strtolower($h1), strtolower($locationLabel))) {
            if (str_contains($h1, 'Near You')) {
                $page['h1'] = str_replace('Near You', 'Near You in ' . $locationLabel, $h1);
            } elseif (str_contains($h1, 'Near Me')) {
                $page['h1'] = str_replace('Near Me', 'Near Me in ' . $locationLabel, $h1);
            } else {
                $page['h1'] = trim($h1 . ' in ' . $locationLabel);
            }
        }

        return $page;
    }

    private function locationContextByPath(array $catalog, string $path): ?array
    {
        foreach ((array) data_get($catalog, 'countries', []) as $country) {
            if ((string) ($country['path'] ?? '') === $path) {
                return $country;
            }

            foreach ((array) data_get($country, 'counties', []) as $county) {
                if ((string) ($county['path'] ?? '') === $path) {
                    return $county;
                }

                foreach ((array) data_get($county, 'towns', []) as $town) {
                    if ((string) ($town['path'] ?? '') === $path) {
                        return $town;
                    }
                }
            }
        }

        return null;
    }

    private function locationContextTerms(array $locationContext): array
    {
        $city = $this->normalizeLocationSearchTerm((string) ($locationContext['city'] ?? ''));
        $region = $this->normalizeLocationSearchTerm((string) ($locationContext['region'] ?? ''));
        $country = $this->normalizeLocationSearchTerm((string) ($locationContext['country'] ?? ''));
        $isGenericCountry = in_array(strtolower($country), ['united kingdom', 'uk', 'gb', 'great britain', 'britain'], true);

        $terms = [];

        if ($city !== '') {
            $terms[] = $city;
        }

        if ($region !== '' && !in_array(mb_strtolower($region), array_map('mb_strtolower', $terms), true)) {
            $terms[] = $region;
        }

        if ($country !== '' && (!$isGenericCountry || $terms === [])) {
            $terms[] = $country;
        }

        return array_values(array_unique(array_filter($terms, static fn (string $term): bool => $term !== '')));
    }

    private function normalizeLocationSearchTerm(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = str_replace(['&', '/'], ' ', $value);
        $value = preg_replace('/\b(and|of|the)\b/i', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function locationContextMatch(Collection $flat, array $locationContext): ?array
    {
        $terms = $this->locationContextTerms($locationContext);

        if ($terms === []) {
            return null;
        }

        foreach ($terms as $term) {
            $needle = strtolower(trim((string) $term));
            if ($needle === '') {
                continue;
            }

            foreach ($flat as $location) {
                $haystack = strtolower(implode(' ', array_filter([
                    (string) ($location['title'] ?? ''),
                    (string) ($location['label'] ?? ''),
                    (string) ($location['county'] ?? ''),
                    (string) ($location['district'] ?? ''),
                    (string) ($location['region'] ?? ''),
                    (string) ($location['country'] ?? ''),
                    (string) ($location['slug'] ?? ''),
                ])));

                if (str_contains($haystack, $needle)) {
                    return $location;
                }
            }
        }

        return null;
    }

    private function applyLocationTermsToProducts($query, array $terms): void
    {
        $query->where(function ($q) use ($terms): void {
            foreach ($terms as $index => $term) {
                $needle = '%' . strtolower(trim($term)) . '%';
                $branch = function ($branch) use ($needle): void {
                    $branch->whereHas('options', function ($options) use ($needle): void {
                        $options->where('meta_name', 'locations')
                            ->whereHas('values', function ($values) use ($needle): void {
                                $values->whereRaw("LOWER(COALESCE(value,'')) LIKE ?", [$needle]);
                            });
                    })->orWhereHas('vendor.locations', function ($locations) use ($needle): void {
                        $locations->where(function ($locationQuery) use ($needle): void {
                            $first = true;
                            foreach (['label', 'line1', 'line2', 'city', 'county', 'postcode', 'formatted_address', 'country'] as $column) {
                                $condition = "LOWER(COALESCE({$column}, '')) LIKE ?";
                                if ($first) {
                                    $locationQuery->whereRaw($condition, [$needle]);
                                    $first = false;
                                } else {
                                    $locationQuery->orWhereRaw($condition, [$needle]);
                                }
                            }
                        });
                    });
                };

                if ($index === 0) {
                    $q->where($branch);
                } else {
                    $q->orWhere($branch);
                }
            }
        });
    }

    private function applyStrictLocationTermsToProducts($query, array $terms): void
    {
        $query->where(function ($q) use ($terms): void {
            foreach ($terms as $index => $term) {
                $needle = strtolower(trim($term));
                if ($needle === '') {
                    continue;
                }

                $branch = function ($branch) use ($needle): void {
                    $branch->whereHas('options', function ($options) use ($needle): void {
                        $options->where('meta_name', 'locations')
                            ->whereHas('values', function ($values) use ($needle): void {
                                $values->whereRaw("LOWER(TRIM(COALESCE(value,''))) = ?", [$needle]);
                            });
                    })->orWhereHas('vendor.locations', function ($locations) use ($needle): void {
                        $locations->where(function ($locationQuery) use ($needle): void {
                            $first = true;
                            foreach (['city', 'county', 'country'] as $column) {
                                $condition = "LOWER(TRIM(COALESCE({$column}, ''))) = ?";
                                if ($first) {
                                    $locationQuery->whereRaw($condition, [$needle]);
                                    $first = false;
                                } else {
                                    $locationQuery->orWhereRaw($condition, [$needle]);
                                }
                            }
                        });
                    });
                };

                if ($index === 0) {
                    $q->where($branch);
                } else {
                    $q->orWhere($branch);
                }
            }
        });
    }

    private function applyLocationTermsToOfferings($query, array $terms): void
    {
        $query->where(function ($q) use ($terms): void {
            foreach ($terms as $index => $term) {
                $needle = '%' . strtolower(trim($term)) . '%';
                $branch = function ($branch) use ($needle): void {
                    $branch->whereHas('vendor.locations', function ($locations) use ($needle): void {
                        $locations->where(function ($locationQuery) use ($needle): void {
                            $first = true;
                            foreach (['label', 'line1', 'line2', 'city', 'county', 'postcode', 'formatted_address', 'country'] as $column) {
                                $condition = "LOWER(COALESCE({$column}, '')) LIKE ?";
                                if ($first) {
                                    $locationQuery->whereRaw($condition, [$needle]);
                                    $first = false;
                                } else {
                                    $locationQuery->orWhereRaw($condition, [$needle]);
                                }
                            }
                        });
                    });
                };

                if ($index === 0) {
                    $q->where($branch);
                } else {
                    $q->orWhere($branch);
                }
            }
        });
    }

    private function applyStrictLocationTermsToOfferings($query, array $terms): void
    {
        $query->where(function ($q) use ($terms): void {
            foreach ($terms as $index => $term) {
                $needle = strtolower(trim($term));
                if ($needle === '') {
                    continue;
                }

                $branch = function ($branch) use ($needle): void {
                    $branch->whereHas('vendor.locations', function ($locations) use ($needle): void {
                        $locations->where(function ($locationQuery) use ($needle): void {
                            $first = true;
                            foreach (['city', 'county', 'country'] as $column) {
                                $condition = "LOWER(TRIM(COALESCE({$column}, ''))) = ?";
                                if ($first) {
                                    $locationQuery->whereRaw($condition, [$needle]);
                                    $first = false;
                                } else {
                                    $locationQuery->orWhereRaw($condition, [$needle]);
                                }
                            }
                        });
                    });
                };

                if ($index === 0) {
                    $q->where($branch);
                } else {
                    $q->orWhere($branch);
                }
            }
        });
    }

    private function preferredLocationLabel($listing, array $locationContext): ?string
    {
        if (!method_exists($listing, 'getLocations')) {
            return null;
        }

        $locations = array_values(array_filter(array_map('trim', (array) $listing->getLocations())));
        if ($locations === []) {
            return null;
        }

        $terms = $this->locationContextTerms($locationContext);
        if ($terms === []) {
            return $locations[0] ?? null;
        }

        $normalizedTerms = array_map(fn (string $term): string => strtolower($this->normalizeLocationSearchTerm($term)), $terms);

        foreach ($locations as $location) {
            $normalizedLocation = strtolower($this->normalizeLocationSearchTerm($location));
            if ($normalizedLocation === '') {
                continue;
            }

            foreach ($normalizedTerms as $term) {
                if ($term === '') {
                    continue;
                }

                if ($normalizedLocation === $term || str_contains($normalizedLocation, $term) || str_contains($term, $normalizedLocation)) {
                    return $location;
                }
            }
        }

        return $locations[0] ?? null;
    }
}
