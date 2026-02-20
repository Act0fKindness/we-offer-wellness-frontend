<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ProductTypeController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Http\Controllers\Api\V3SubscriberController;

Route::get('/', function () {
    // Fetch a few curated product slices for the home page
    try {
        $base = \App\Models\Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->with(['media', 'options.values', 'category'])
            // Only visible products: include live/approved OR legacy rows without status set
            ->where(function($q){
                $q->whereHas('status', function($qs){ $qs->whereIn('status', ['live','approved']); })
                  ->orWhereNull('product_status_id');
            });

        // Gifts under £50 (consider variant prices too)
        $giftsUnder50 = (clone $base)
            ->where(function($q){
                $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                  ->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%gift%'");
            })
            ->where(function($q){
                // Support pennies or pounds on product price OR any variant price
                $q->where(function($qp){
                      $qp->where('price', '<=', 50)->orWhere('price', '<=', 50 * 100);
                  })
                  ->orWhereHas('variants', function($qv){
                      $qv->where('price', '<=', 50)->orWhere('price', '<=', 50 * 100);
                  });
            })
            ->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
            ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
            ->orderByRaw('COALESCE(reviews_count, 0) DESC')
            ->limit(12)
            ->get();

        // Online options under £50 (consider variant prices too)
        $onlineUnder50 = (clone $base)
            ->whereHas('options', function ($q) {
                $q->where('meta_name', 'locations')
                  ->whereHas('values', function ($q2) {
                      $q2->whereRaw('LOWER(value) = ?', ['online']);
                  });
            })
            ->where(function($q){
                $q->where(function($qp){
                      $qp->where('price', '<=', 50)->orWhere('price', '<=', 50 * 100);
                  })
                  ->orWhereHas('variants', function($qv){
                      $qv->where('price', '<=', 50)->orWhere('price', '<=', 50 * 100);
                  });
            })
            ->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
            ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
            ->orderByRaw('COALESCE(reviews_count, 0) DESC')
            ->limit(12)
            ->get();
    } catch (\Throwable $e) {
        $giftsUnder50 = collect();
        $onlineUnder50 = collect();
    }

    return view('home.index', [
        'giftsUnder50' => $giftsUnder50,
        'onlineUnder50' => $onlineUnder50,
    ]);
});

// V3 holding page
Route::get('/v3', function () {
    return Inertia::render('V3/Holding', [
        'meta' => [
            'title' => 'We Offer Wellness v3 — Coming Soon',
            'description' => 'We’re at the tail end of v3. Join for giveaways, discounts, and launch access.',
            'canonical' => url('/v3'),
        ],
    ]);
})->name('v3.holding');

Route::get('/search', function (\Illuminate\Http\Request $request) {
    // Normalise legacy/alternate params without touching the SPA code
    $format = strtolower((string) $request->query('format', ''));
    if ($format === 'online') {
        // For the "Explore Anytime Options" CTA: route to classes hub, online only, anytime
        return redirect('/classes?mode=online&anytime=1', 302);
    }
    // Support category landing via /search?category=Yoga[&type=classes]
    if ($request->has('category')) {
        $raw = trim((string) $request->query('category'));
        if ($raw !== '') {
            $slug = Str::slug($raw);
            $type = strtolower((string) $request->query('type', 'therapies'));
            $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
            if (!in_array($type, $allowed, true)) { $type = 'therapies'; }
            return redirect('/'.$type.'/'.$slug, 302);
        }
    }
    if ($format && !$request->has('mode')) {
        // Map other format values to mode for search, e.g., format=in-person
        $qs = $request->query();
        $qs['mode'] = $format;
        unset($qs['format']);
        $to = url('/search') . '?' . http_build_query($qs);
        return redirect($to, 302);
    }
    return Inertia::render('Search/Index', [
        'mapsKey' => env('GOOGLE_MAPS_API_KEY'),
    ]);
})->name('search');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin utilities: backup and clear Pages with confirmation token
    Route::get('/admin/pages/backup', [\App\Http\Controllers\Admin\PagesAdminController::class, 'backup'])
        ->name('admin.pages.backup');
    Route::post('/admin/pages/clear', [\App\Http\Controllers\Admin\PagesAdminController::class, 'clear'])
        ->name('admin.pages.clear');
});

require __DIR__.'/auth.php';

// Lightweight JSON endpoints for frontend
Route::get('/api/products', [ProductController::class, 'index']);
Route::get('/api/articles', [ArticleController::class, 'index']);
Route::get('/api/catalog', [CatalogController::class, 'index']);
Route::get('/api/product-types', [ProductTypeController::class, 'index']);
Route::get('/api/locations', [LocationController::class, 'index']);
Route::get('/api/review-stats', [\App\Http\Controllers\Api\ReviewStatsController::class, 'index']);
// Lightweight reservation hold/release endpoints (require auth)
Route::post('/api/reservations/hold', [ReservationController::class, 'hold'])->name('api.reservations.hold');
Route::post('/api/reservations/release', [ReservationController::class, 'release'])->name('api.reservations.release');

// Geolocation persistence (cookies)
Route::post('/api/geo', [\App\Http\Controllers\GeoController::class, 'update']);

// ------------------------------------------------------------------
// Landing/Hubs and Universal Pattern pages
// ------------------------------------------------------------------

// Hubs (top-level)
Route::get('/therapies', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->hub($r, 'therapies'));
Route::get('/events', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->hub($r, 'events'));
Route::get('/workshops', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->hub($r, 'workshops'));
Route::get('/classes', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->hub($r, 'classes'));
Route::get('/retreats', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->hub($r, 'retreats'));
Route::get('/gifts', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->hub($r, 'gifts'));
Route::get('/near-me', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->hub($r, 'near-me'));
// Pain-point landing pages
Route::get('/need/{need}', fn(\Illuminate\Http\Request $r, string $need) => app(LandingController::class)->need($r, $need));
// Quiz plan results page
Route::get('/plan', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->plan($r));

// Singular → plural 301 redirects for hubs
Route::get('/therapy', fn() => redirect('/therapies', 301));
Route::get('/event', fn() => redirect('/events', 301));
Route::get('/workshop', fn() => redirect('/workshops', 301));
Route::get('/class', fn() => redirect('/classes', 301));
Route::get('/retreat', fn() => redirect('/retreats', 301));
Route::get('/gift', fn() => redirect('/gifts', 301));

// Singular → plural 301 redirects for universal pattern
Route::get('/therapy/{category}', fn(string $category) => redirect('/therapies/'.$category, 301));
Route::get('/event/{category}', fn(string $category) => redirect('/events/'.$category, 301));
Route::get('/workshop/{category}', fn(string $category) => redirect('/workshops/'.$category, 301));
Route::get('/class/{category}', fn(string $category) => redirect('/classes/'.$category, 301));
Route::get('/retreat/{category}', fn(string $category) => redirect('/retreats/'.$category, 301));
Route::get('/gift/{category}', fn(string $category) => redirect('/gifts/'.$category, 301));

// Offering detail pages: /{type}/{id}-{slug}
Route::get('/{type}/{offering}', function (\Illuminate\Http\Request $r, string $type, string $offering) {
    $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
    if (!in_array(strtolower($type), $allowed, true)) { abort(404); }
    return app(\App\Http\Controllers\LandingController::class)->offering($r, $type, $offering);
})->where(['type' => 'therapies|events|workshops|classes|retreats|gifts', 'offering' => '\\d+-[A-Za-z0-9\-]+' ]);

// Legacy offering route support: /{type}/o/{handle} → 301 to /{type}/{id}-{slug}
Route::get('/{type}/o/{handle}', function (\Illuminate\Http\Request $r, string $type, string $handle) {
    $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
    if (!in_array(strtolower($type), $allowed, true)) { abort(404); }
    $p = \App\Models\Product::query()->where('handle', $handle)
        ->orWhere(fn($q)=>$q->where('id', is_numeric($handle) ? (int)$handle : 0))
        ->first();
    if (!$p) abort(404);
    $slug = \Illuminate\Support\Str::slug($p->title ?: (string)$p->id);
    return redirect('/'.strtolower($type).'/'.$p->id.'-'.$slug, 301);
})->where('type', 'therapies|events|workshops|classes|retreats|gifts');

// Universal pattern: /{type}/{category} (category slug may not start with digits to avoid clashing with offering route)
Route::get('/{type}/{category}', function (\Illuminate\Http\Request $r, string $type, string $category) {
    $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
    if (!in_array(strtolower($type), $allowed, true)) { abort(404); }
    return app(LandingController::class)->category($r, $type, $category);
})->where(['type' => 'therapies|events|workshops|classes|retreats|gifts', 'category' => '[A-Za-z][A-Za-z0-9\-]*' ]);

// City pages (limited whitelist to avoid conflicting with known routes)
$cities = implode('|', [
    'london','manchester','birmingham','leeds','bristol','brighton','liverpool','glasgow','edinburgh','cardiff','kent',
]);
Route::get('/{city}', [LandingController::class, 'city'])->where('city', $cities);
Route::get('/{city}/{type}/{category}', [LandingController::class, 'cityCategory'])
    ->where(['city' => $cities, 'type' => 'therapies|events|workshops|classes']);

// Legacy experiences → 301 redirects
Route::get('/experiences', function () {
    // Vanity landing: route to Gifts hub for now
    return redirect('/gifts', 301);
});
Route::get('/experience', function () {
    return redirect('/gifts', 301);
});
Route::get('/experience/{slug}', function (string $slug) {
    return redirect('/experiences/'.ltrim($slug, '/'), 301);
});
Route::get('/experiences/{slug}', function (string $slug) {
    $s = strtolower($slug);
    // quick mapping heuristics
    if (str_contains($s, 'sound-bath')) return redirect("/events/sound-bath", 301);
    if ($s === 'reiki') return redirect('/therapies/reiki', 301);
    if (str_contains($s, 'breathwork')) return redirect('/events/breathwork-workshops', 301);
    if (str_contains($s, 'retreat')) return redirect('/retreats', 301);
    // default guess: therapy
    return redirect('/therapies/'.$s, 301);
});

// Static placeholders (trust/corporate/legal). Guarded by ENABLE_STATIC_PAGES
if (config('wow.enable_static_pages')) {
Route::get('/corporate', function () {
    return Inertia::render('Corporate/Hub');
});
Route::get('/corporate-wellness', function () {
    return Inertia::render('Corporate/ComingSoon');
});
Route::redirect('/corporate-wellbeing', '/corporate-wellness', 301);

Route::get('/events-and-workshops', function () {
    return Inertia::render('EventsAndWorkshops');
});
Route::redirect('/events-workshops', '/events-and-workshops', 301);

Route::get('/gift-cards', function () {
    return Inertia::render('GiftCards');
});
Route::redirect('/gift-vouchers', '/gift-cards', 301);
}

Route::get('/reviews', function(){
    $reviews = Review::with(['user:id,first_name,last_name,name,location', 'product:id,title,slug', 'vendor:id,vendor_name'])
        ->whereNotNull('review_text')
        ->orderByDesc('created_at')
        ->get()
        ->map(function(Review $review) {
            $user = $review->user;
            $customerName = $user ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : null;
            if (!$customerName) {
                $customerName = $user?->name ?: 'Verified client';
            }
            return [
                'id' => $review->id,
                'quote' => trim($review->review_text),
                'rating' => $review->rating ? (int) $review->rating : null,
                'customer' => $customerName,
                'location' => $user->location ?? null,
                'product' => $review->product?->title,
                'product_slug' => $review->product?->slug,
                'vendor' => $review->vendor?->vendor_name,
                'created_at' => optional($review->created_at)->toIso8601String(),
            ];
        });

    return Inertia::render('Reviews/Index', [
        'reviews' => $reviews,
        'meta' => [
            'title' => 'Client Reviews | We Offer Wellness',
            'description' => 'Read real stories from people who booked therapies, classes, workshops and retreats with We Offer Wellness.',
        ],
    ]);
})->name('reviews.index');
if (config('wow.enable_static_pages')) {
Route::get('/about', function(){
    return Inertia::render('General/Page', [
        'title' => 'About We Offer Wellness',
        'metaDescription' => 'Our mission: wellness that actually helps, done safely and simply.',
        'bodyHtml' => '<p>Your story goes here. Why you started, who you help, and what makes you different.</p>',
        'canonical' => url('/about'),
    ]);
});
Route::get('/contact', function(){
    return Inertia::render('General/Page', [
        'title' => 'Contact',
        'metaDescription' => 'Get in touch with We Offer Wellness.',
        'bodyHtml' => '<p>Email us at <a href="mailto:hello@weofferwellness.co.uk">hello@weofferwellness.co.uk</a> or use the form below.</p>',
        'canonical' => url('/contact'),
    ]);
});
Route::get('/corporate/{slug}', function (string $slug) {
    $slug = strtolower($slug);
    $pages = [
        'wellbeing-workshops' => [
            'title' => 'Wellbeing Workshops',
            'desc' => 'Hands‑on sessions for stress, sleep and energy — tailored to your team.',
            'features' => [
                ['title' => 'Stress & burnout', 'text' => 'Breath, nervous‑system resets and micro‑mobility.'],
                ['title' => 'Energy & focus', 'text' => 'Cold exposure education, breath and recovery.'],
                ['title' => 'Sleep & recovery', 'text' => 'Wind‑down routines and restorative practices.'],
            ],
        ],
        'meditation' => [
            'title' => 'Meditation for Teams',
            'desc' => 'Guided meditations to reduce stress and improve focus — online or on‑site.',
            'features' => [
                ['title' => 'Beginner‑friendly', 'text' => 'No experience required.'],
                ['title' => 'Repeatable', 'text' => 'Weekly or monthly cadence.'],
                ['title' => 'Measurable', 'text' => 'Attendance and feedback tracking.'],
            ],
        ],
        'breathwork' => [
            'title' => 'Breathwork for Teams',
            'desc' => 'Science‑backed breathing to regulate stress and improve resilience.',
            'features' => [
                ['title' => 'Downshift fast', 'text' => 'Short parasympathetic techniques.'],
                ['title' => 'Focus boosts', 'text' => 'CO₂ tolerance and cadence work.'],
                ['title' => 'Hybrid delivery', 'text' => 'Online live or in‑person.'],
            ],
        ],
        'sound-bath' => [
            'title' => 'Workplace Sound Baths',
            'desc' => 'Immersive relaxation sessions to reduce stress and improve sleep. On‑site. ',
            'features' => [
                ['title' => 'All equipment', 'text' => 'We bring mats and instruments.'],
                ['title' => '30–60 minutes', 'text' => 'Fits lunch‑and‑learn or after‑work slots.'],
                ['title' => 'Group sizes', 'text' => 'From 10 to 60 attendees.'],
            ],
        ],
        'gift-vouchers' => [
            'title' => 'Staff Gifting',
            'desc' => 'Digital gift cards and curated experiences for employees and clients.',
            'features' => [
                ['title' => 'Instant delivery', 'text' => 'Email or bulk CSV.'],
                ['title' => 'Custom amounts', 'text' => '£25 – £200+.'],
                ['title' => 'Brandable', 'text' => 'Your logo and message.'],
            ],
        ],
        'employee-rewards' => [
            'title' => 'Employee Rewards',
            'desc' => 'Meaningful perks that support mental health and recovery.',
            'features' => [
                ['title' => 'Perk portals', 'text' => 'HRIS and benefit‑platform friendly.'],
                ['title' => 'Usage tracking', 'text' => 'Redemption and feedback.'],
                ['title' => 'Flexible billing', 'text' => 'Invoice or card.'],
            ],
        ],
    ];
    if (!isset($pages[$slug])) { abort(404); }
    return Inertia::render('Corporate/Service', [
        'slug' => $slug,
        'page' => $pages[$slug],
    ]);
});
// Back-compat redirects for old legal paths
Route::redirect('/legal/privacy', '/privacy', 301);
Route::redirect('/legal/terms', '/terms', 301);
Route::view('/404', 'app');
}

// Cart page
Route::get('/cart', function () {
    return Inertia::render('Cart/Index', [
        'sessionPromo' => session('cart_promo_code'),
        'sessionGift'  => session('cart_gift_code'),
    ]);
});

// Cart code persistence in session
Route::post('/api/cart/promo', function (Request $request) {
    $code = strtoupper(trim((string)$request->input('code', '')));
    if ($code !== '') session(['cart_promo_code' => $code]); else session()->forget('cart_promo_code');
    return response()->json(['ok' => true, 'code' => $code]);
});
// V3 subscriber opt-in API (CSRF-protected; same-domain)
Route::post('/api/v3-subscribers', [V3SubscriberController::class, 'store'])->name('api.v3-subscribers.store');
Route::post('/api/v3-subscribers/track', [V3SubscriberController::class, 'track'])->name('api.v3-subscribers.track');
Route::post('/api/cart/gift', function (Request $request) {
    $code = strtoupper(trim((string)$request->input('code', '')));
    if ($code !== '') session(['cart_gift_code' => $code]); else session()->forget('cart_gift_code');
    return response()->json(['ok' => true, 'code' => $code]);
});

// Providers (directory/profile)
Route::get('/providers', function(){
    return Inertia::render('Providers/Index', [
        'title' => 'Practitioners',
        'metaDescription' => 'Find trusted wellness practitioners across therapies and modalities.',
    ]);
});
Route::get('/provider/{slug}', function(string $slug){
    return Inertia::render('Providers/Show', [
        'slug' => $slug,
    ]);
});

// Help / Partners (guarded)
if (config('wow.enable_static_pages')) {
Route::get('/help', function(){
    $body = <<<'HTML'
<p>Need a hand with bookings, accounts or vouchers? Start with our FAQ, then reach out if you still need support.</p>
<ul>
  <li><a href="/help/faq">Read the FAQ</a> for booking, payment and cancellation answers.</li>
  <li><a href="/contact?topic=support">Contact support</a> if you need personalised help.</li>
  <li><a href="/help/gift-cards">Gift card help</a> covers delivery, redemption and balances.</li>
</ul>
HTML;
    return Inertia::render('General/Page', [
        'title' => 'Help Centre',
        'metaDescription' => 'FAQs and support for bookings and account.',
        'bodyHtml' => $body,
        'canonical' => url('/help'),
    ]);
});
Route::get('/help/faq', function(){
    $body = <<<'HTML'
<h2>Frequently asked questions</h2>
<p><strong>How do I manage a booking?</strong><br>Visit your confirmation email to reschedule or cancel, or message the practitioner directly from your account.</p>
<p><strong>What if I need to cancel?</strong><br>Each listing includes a cancellation window. If you cannot find it, <a href="/contact?topic=support">contact support</a>.</p>
<p><strong>Do I need any equipment?</strong><br>Most therapies only require comfortable clothing and a quiet space. Classes will note props if needed.</p>
HTML;
    return Inertia::render('General/Page', [
        'title' => 'FAQ',
        'metaDescription' => 'Common booking, payment and account questions.',
        'bodyHtml' => $body,
        'canonical' => url('/help/faq'),
    ]);
});
Route::get('/help/gift-cards', function(){
    $body = <<<'HTML'
<p>WOW gift cards arrive instantly by email with your personalised message. Gift cards never expire and can be redeemed on therapies, classes, events and workshops.</p>
<ul>
  <li><strong>How to send:</strong> Choose an amount, enter the recipient’s name and email, and schedule delivery or send immediately.</li>
  <li><strong>How to redeem:</strong> Recipients enter the unique code at checkout. Balances can be used across multiple bookings.</li>
  <li><strong>Need a custom amount?</strong> <a href="/contact?topic=gifting">Contact the team</a> for bulk or corporate gifting.</li>
</ul>
HTML;
    return Inertia::render('General/Page', [
        'title' => 'Gift Card Help',
        'metaDescription' => 'How WOW gift vouchers work and how to redeem them.',
        'bodyHtml' => $body,
        'canonical' => url('/help/gift-cards'),
    ]);
});
Route::get('/partners', function(){
    return Inertia::render('General/Page', [
        'title' => 'Partners',
        'metaDescription' => 'Partner with We Offer Wellness for referrals and corporate programmes.',
        'bodyHtml' => '<p>Tell partners how to work with you. Replace with full program details.</p>',
        'canonical' => url('/partners'),
    ]);
});
}

// XML sitemap
Route::get('/sitemap.xml', function () {
    $base = url('');
    $now = now()->toAtomString();
    $urls = [];

    // Top-level hubs & key pages
    foreach (['/','/therapies','/events','/workshops','/classes','/retreats','/gifts','/gift-cards','/events-and-workshops','/corporate','/corporate-wellness','/search'] as $p) {
        $urls[] = [ 'loc' => $base.$p, 'lastmod' => $now ];
    }

    // City pages (limited whitelist)
    foreach (['london','manchester','birmingham','leeds','bristol','brighton','liverpool','glasgow','edinburgh','cardiff','kent'] as $city) {
        $urls[] = [ 'loc' => $base.'/'.rawurlencode($city), 'lastmod' => $now ];
    }

    // Therapy categories (top N by product count)
    try {
        $cats = ProductCategory::query()
            ->withCount('products')
            ->orderByDesc('products_count')->orderBy('name')
            ->limit(120)->get();
        foreach ($cats as $c) {
            $slug = Str::slug($c->name ?? '');
            if ($slug) $urls[] = [ 'loc' => $base.'/therapies/'.$slug, 'lastmod' => $now ];
        }
    } catch (\Throwable $e) {}

    // Offerings (cap to keep sitemap lean)
    try {
        $items = Product::query()->select(['id','title','product_type','tags_list','updated_at'])->latest('updated_at')->limit(1000)->get();
        foreach ($items as $p) {
            $t = strtolower((string) $p->product_type);
            $tags = strtolower((string) $p->tags_list);
            if (str_contains($t, 'workshop')) $seg = 'workshops';
            elseif (str_contains($t, 'event')) $seg = 'events';
            elseif (str_contains($t, 'class')) $seg = 'classes';
            elseif (str_contains($t, 'retreat')) $seg = 'retreats';
            elseif (str_contains($t, 'gift') || str_contains($tags, 'gift')) $seg = 'gifts';
            else $seg = 'therapies';
            $slug = Str::slug($p->title ?: (string)$p->id);
            $urls[] = [ 'loc' => $base.'/'.$seg.'/'.$p->id.'-'.$slug, 'lastmod' => optional($p->updated_at)->toAtomString() ?: $now ];
        }
    } catch (\Throwable $e) {}

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
           '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $u) {
        $xml .= '<url>'
              . '<loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>'
              . (isset($u['lastmod']) ? '<lastmod>'.htmlspecialchars($u['lastmod'], ENT_XML1).'</lastmod>' : '')
              . '</url>';
    }
    $xml .= '</urlset>';
    return response($xml, 200)->header('Content-Type', 'application/xml');
});
Route::get('/sitemap', fn() => redirect('/sitemap.xml', 301));

// General content pages (privacy, terms, cookies) guarded
if (config('wow.enable_static_pages')) {
Route::get('/privacy', function(){
    return Inertia::render('General/Page', [
        'title' => 'Privacy Policy',
        'metaDescription' => 'How We Offer Wellness collects, uses and protects your information.',
        'bodyHtml' => '<p>This page describes how we handle your data. Replace with your full policy.</p>',
        'canonical' => url('/privacy'),
    ]);
});
Route::get('/terms', function(){
    return Inertia::render('General/Page', [
        'title' => 'Terms & Conditions',
        'metaDescription' => 'Terms for using We Offer Wellness services and bookings.',
        'bodyHtml' => '<p>These are your terms. Replace with your full terms of service.</p>',
        'canonical' => url('/terms'),
    ]);
});
Route::get('/cookies', function(){
    return Inertia::render('General/Page', [
        'title' => 'Cookies',
        'metaDescription' => 'Information about cookies and controls.',
        'bodyHtml' => '<p>We use cookies to improve your experience. Replace with your cookie policy and consent details.</p>',
        'canonical' => url('/cookies'),
    ]);
});
}

// Dynamic CMS-like pages stored in shared DB (from Backend admin)
Route::fallback([\App\Http\Controllers\PageController::class, 'show']);
