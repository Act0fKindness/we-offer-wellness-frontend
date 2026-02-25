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
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SafetyContraindicationsController;
use App\Http\Controllers\HelpCentreController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\NeedsController;
use App\Http\Controllers\TherapiesController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\OnlineController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\OnlineNearMeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Api\ProductCardsController;
use App\Http\Controllers\MindfulTimesController;
use App\Http\Controllers\ProvidersController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CorporateController;
use App\Http\Controllers\StaticPagesController;

// Online & Near Me hub
Route::get('/online-near-me', [OnlineNearMeController::class, 'index'])->name('onlineNearMe.index');

/** By Need */
Route::get('/needs', [NeedsController::class, 'index'])->name('needs.index');
Route::get('/needs/{slug}', [NeedsController::class, 'show'])
    ->where('slug', '[A-Za-z][A-Za-z0-9\-]*')
    ->name('needs.show');

/** Therapies */
Route::get('/therapies', [TherapiesController::class, 'index'])->name('therapies.index');
Route::get('/therapies/{slug}', [TherapiesController::class, 'show'])
    ->where('slug', '[A-Za-z][A-Za-z0-9\-]*')
    ->name('therapies.show');

/** Events & Workshops */
Route::get('/events-workshops', [EventsController::class, 'index'])->name('events.index');
Route::get('/events-workshops/{slug}', [EventsController::class, 'show'])
    ->where('slug', '[A-Za-z][A-Za-z0-9\-]*')
    ->name('events.show');

/** Online */
Route::get('/online', [OnlineController::class, 'index'])->name('online.index');

/** Locations + Near Me */
Route::get('/locations', [LocationsController::class, 'index'])->name('locations.index');
Route::get('/locations/{slug}', [LocationsController::class, 'show'])
    ->where('slug', '[A-Za-z][A-Za-z0-9\-]*')
    ->name('locations.show');
Route::get('/near-me', [LocationsController::class, 'nearMe'])->name('nearMe');

// Misc redirects for broken/legacy links
Route::redirect('/help/which-therapy', '/plan', 301);

Route::get('/', [HomeController::class, 'index']);

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

Route::get('/search', [SearchController::class, 'index'])->name('search');

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
// HTML fragments: render product cards via Blade for dynamic sections (e.g., comfort rail)
Route::get('/api/product-cards', [ProductCardsController::class, 'index']);
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

// Redirect legacy hubs to Blade counterparts where applicable
Route::redirect('/events', '/events-workshops', 301);
Route::redirect('/workshops', '/events-workshops', 301);
Route::redirect('/classes', '/events-workshops', 301);
// Pain-point landing pages
Route::get('/need/{need}', fn(\Illuminate\Http\Request $r, string $need) => app(LandingController::class)->need($r, $need));
// Quiz plan results page
Route::get('/plan', fn(\Illuminate\Http\Request $r) => app(LandingController::class)->plan($r));

// Singular → plural 301 redirects for hubs
Route::get('/therapy', fn() => redirect('/therapies', 301));
Route::get('/event', fn() => redirect('/events-workshops', 301));
Route::get('/workshop', fn() => redirect('/events-workshops', 301));
Route::get('/class', fn() => redirect('/events-workshops', 301));
Route::get('/retreat', fn() => redirect('/retreats', 301));
Route::get('/gift', fn() => redirect('/gifts', 301));

// Singular → plural 301 redirects for legacy category paths
Route::get('/therapy/{category}', fn(string $category) => redirect('/therapies/'.$category, 301));
Route::get('/event/{category}', fn(string $category) => redirect('/events-workshops/'.$category, 301));
Route::get('/workshop/{category}', fn(string $category) => redirect('/events-workshops/'.$category, 301));
Route::get('/class/{category}', fn(string $category) => redirect('/events-workshops/'.$category, 301));
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

// Removed universal category catch-all to avoid conflicts with Blade routes

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

// Consolidate events/workshops hub to Blade route
Route::redirect('/events-and-workshops', '/events-workshops', 301);

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
    // Restore cart from cookie if session empty
    $items = session('cart.items', []);
    if (empty($items)) {
        $cookie = request()->cookie('wow_cart');
        if ($cookie) { $restored = json_decode($cookie, true) ?: []; if (is_array($restored)) { session(['cart.items' => $restored]); } }
    }
    return view('cart.index');
});

// Checkout routes
Route::post('/checkout', [CheckoutController::class, 'create']);
Route::get('/checkout/success', [CheckoutController::class, 'success']);
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel']);

// Cart code persistence in session
Route::post('/api/cart/promo', function (Request $request) {
    $code = strtoupper(trim((string)$request->input('code', '')));
    if ($code !== '') session(['cart_promo_code' => $code]); else session()->forget('cart_promo_code');
    return response()->json(['ok' => true, 'code' => $code]);
});
// Cart session store
Route::get('/api/cart/count', function(){
    $items = session('cart.items', []);
    if (empty($items)) {
        $cookie = request()->cookie('wow_cart');
        if ($cookie) { $restored = json_decode($cookie, true) ?: []; if (is_array($restored)) { $items = $restored; session(['cart.items' => $items]); } }
    }
    $count = array_sum(array_map(fn($it)=> (int)($it['qty'] ?? 0), $items));
    return response()->json(['count'=>$count])->header('Cache-Control','no-store, no-cache, must-revalidate')->header('Pragma','no-cache')->header('Vary','Cookie');
});
Route::get('/api/cart/mini', function(){
    $items = session('cart.items', []);
    if (empty($items)) {
        $cookie = request()->cookie('wow_cart');
        if ($cookie) { $restored = json_decode($cookie, true) ?: []; if (is_array($restored)) { session(['cart.items' => $restored]); } }
    }
    return response(view('partials.mini_cart')->render(), 200)
        ->header('Content-Type','text/html')
        ->header('Cache-Control','no-store, no-cache, must-revalidate')
        ->header('Pragma','no-cache')
        ->header('Vary','Cookie');
});
Route::post('/api/cart/add', function (Request $request) {
    $id = (int) $request->input('id'); $qty = max(1, (int)$request->input('qty', 1));
    if ($id <= 0) return response()->json(['ok'=>false,'error'=>'invalid'], 400);
    $p = \App\Models\Product::query()->withMin('variants','price')->find($id);
    if (!$p) return response()->json(['ok'=>false,'error'=>'not_found'], 404);
    $price = $p->variants_min_price ?? $p->price ?? 0; $price = (float)$price;
    $slug = \Illuminate\Support\Str::slug($p->title ?: (string)$p->id);
    // Determine segment similar to product_card
    $t = strtolower((string)($p->product_type ?? '')); $tags = strtolower((string)($p->tags_list ?? '')); $seg='therapies';
    if (str_contains($t,'workshop')) $seg='workshops'; elseif (str_contains($t,'event')) $seg='events'; elseif (str_contains($t,'class')) $seg='classes'; elseif (str_contains($t,'retreat')) $seg='retreats'; elseif (str_contains($t,'gift')||str_contains($tags,'gift')) $seg='gifts';
    $url = url('/'.$seg.'/'.$p->id.'-'.$slug);
    $image = method_exists($p,'getFirstImageUrl') ? $p->getFirstImageUrl() : null;
    $cart = session('cart', ['items'=>[]]);
    $items = $cart['items'] ?? [];
    $key = (string)$id;
    if(isset($items[$key])){ $items[$key]['qty'] = (int)($items[$key]['qty'] ?? 1) + $qty; }
    else { $items[$key] = ['id'=>$id, 'title'=>$p->title, 'price'=>$price, 'qty'=>$qty, 'image'=>$image, 'url'=>$url]; }
    session(['cart.items' => $items]);
    $cookie = cookie('wow_cart', json_encode($items), 60*24*30); // 30 days
    $count = array_sum(array_map(fn($it)=> (int)($it['qty'] ?? 0), $items));
    return response()->json(['ok'=>true,'count'=>$count])->withCookie($cookie);
});
Route::post('/api/cart/remove', function (Request $request) {
    $id = (string) $request->input('id'); $items = session('cart.items', []); unset($items[$id]); session(['cart.items'=>$items]);
    $cookie = cookie('wow_cart', json_encode($items), 60*24*30);
    return response()->json(['ok'=>true])->withCookie($cookie);
});
Route::post('/api/cart/update', function (Request $request) {
    $id = (string)$request->input('id'); $qty = max(0,(int)$request->input('qty',1));
    $items = session('cart.items', []); if(!isset($items[$id])) return response()->json(['ok'=>false],404);
    if($qty===0){ unset($items[$id]); } else { $items[$id]['qty']=$qty; }
    session(['cart.items'=>$items]);
    $cookie = cookie('wow_cart', json_encode($items), 60*24*30);
    return response()->json(['ok'=>true])->withCookie($cookie);
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
Route::get('/partners', [StaticPagesController::class, 'partners']);
}

// XML sitemap
Route::get('/sitemap.xml', function () {
    $base = url('');
    $now = now()->toAtomString();
    $urls = [];

    // Top-level hubs & key pages
    foreach (['/','/therapies','/events-workshops','/retreats','/gifts','/gift-cards','/corporate','/corporate-wellness','/search'] as $p) {
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

// General content pages (always available)
Route::get('/privacy', [StaticPagesController::class, 'privacy']);
Route::get('/terms', [StaticPagesController::class, 'terms']);
Route::get('/cookies', [StaticPagesController::class, 'cookies']);
Route::get('/refunds-and-cancellations', [StaticPagesController::class, 'refunds']);

Route::get('/safety-and-contraindications', [SafetyContraindicationsController::class, 'index'])
    ->name('safety-and-contraindications');

Route::get('/help', [HelpCentreController::class, 'index'])->name('help');

Route::get('/about', [AboutController::class, 'index'])
    ->name('about');

// Mindful Times (simple hub)
Route::get('/mindful-times', [MindfulTimesController::class, 'index']);

// Providers directory
Route::get('/providers', [ProvidersController::class, 'index']);
Route::get('/provider/{slug}', [ProvidersController::class, 'show']);

// Contact
Route::get('/contact', [ContactController::class, 'index']);

// Corporate
Route::get('/corporate', [CorporateController::class, 'hub']);
Route::get('/corporate-wellness', [CorporateController::class, 'comingSoon']);

// Gift cards
Route::get('/gift-cards', [StaticPagesController::class, 'giftCards']);

// Dynamic CMS-like pages stored in shared DB (from Backend admin)
Route::fallback([\App\Http\Controllers\PageController::class, 'show']);
