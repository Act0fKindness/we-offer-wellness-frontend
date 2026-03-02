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
use App\Http\Controllers\CheckoutResultController;
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
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\HelpPagesController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\LandingRedirectsController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\SubscriberController;

Route::get('/', [HomeController::class, 'index']);

Route::prefix('subscribe')->group(function () {
    Route::get('/confirm/{token}', [SubscriberController::class, 'confirm'])->name('subscribe.confirm');
    Route::get('/preferences/{token}', [SubscriberController::class, 'preferences'])->name('subscribe.preferences');
    Route::post('/preferences/{token}', [SubscriberController::class, 'updatePreferences'])->name('subscribe.preferences.update');
    Route::get('/unsubscribe/{token}', [SubscriberController::class, 'unsubscribe'])->name('subscribe.unsubscribe');
    Route::get('/resubscribe/{token}', [SubscriberController::class, 'resubscribe'])->name('subscribe.resubscribe');
});

// Online & Near Me hub
Route::get('/online-near-me', [OnlineNearMeController::class, 'index'])->name('onlineNearMe.index');

/** By Need */
Route::get('/needs', [NeedsController::class, 'index'])->name('needs.index');
Route::get('/needs/{slug}', [NeedsController::class, 'show'])
    ->where('slug', '[A-Za-z][A-Za-z0-9\-]*')
    ->name('needs.show');
// Back-compat: old slug variant without "and"
Route::redirect('/needs/stress-anxiety', '/needs/stress-and-anxiety', 301);

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
// Stripe Checkout session (web POST with CSRF)
Route::post('/checkout/session', [CheckoutController::class, 'createSession'])->name('checkout.session');

Route::redirect('/dashboard', '/account', 301)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('account')->group(function () {
        Route::get('/', [CustomerAccountController::class, 'dashboard'])->name('account.dashboard');
        Route::get('/orders', [CustomerAccountController::class, 'orders'])->name('account.orders');
        Route::get('/orders/{order}', [CustomerAccountController::class, 'showOrder'])
            ->whereNumber('order')
            ->name('account.orders.show');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.photo');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Legacy /profile URL support → redirect into /account/profile
    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    })->name('profile.legacy');

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
Route::get('/need/{need}', [LandingController::class, 'need']);
// Quiz plan results page
Route::get('/plan', [LandingController::class, 'plan']);

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
Route::get('/{type}/{offering}', [LandingController::class, 'offering'])
    ->where(['type' => 'therapies|events|workshops|classes|retreats|gifts', 'offering' => '\\d+-[A-Za-z0-9\-]+' ]);

// Legacy offering route support: /{type}/o/{handle} → 301 to /{type}/{id}-{slug}
Route::get('/{type}/o/{handle}', [LandingRedirectsController::class, 'offeringHandle'])
    ->where('type', 'therapies|events|workshops|classes|retreats|gifts');

// Removed universal category catch-all to avoid conflicts with Blade routes

// City pages (limited whitelist to avoid conflicting with known routes)
$cities = implode('|', [
    'london','manchester','birmingham','leeds','bristol','brighton','liverpool','glasgow','edinburgh','cardiff','kent',
]);
Route::get('/{city}', [LandingController::class, 'city'])->where('city', $cities);
Route::get('/{city}/{type}/{category}', [LandingController::class, 'cityCategory'])
    ->where(['city' => $cities, 'type' => 'therapies|events|workshops|classes']);

// Legacy experiences → 301 redirects
Route::get('/experiences', [LandingRedirectsController::class, 'experiencesIndex']);
Route::get('/experience', [LandingRedirectsController::class, 'experienceIndex']);
Route::get('/experience/{slug}', [LandingRedirectsController::class, 'experienceSlug']);
Route::get('/experiences/{slug}', [LandingRedirectsController::class, 'experiencesSlug']);

Route::redirect('/corporate-wellbeing', '/corporate-wellness', 301);
Route::redirect('/events-and-workshops', '/events-workshops', 301);
Route::redirect('/gift-vouchers', '/gift-cards', 301);

Route::get('/reviews', [ReviewsController::class, 'index'])->name('reviews.index');
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
Route::get('/cart', [CartController::class, 'page']);

// Checkout routes
Route::post('/checkout', [CheckoutController::class, 'create']);
Route::get('/checkout/success', [CheckoutResultController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [CheckoutResultController::class, 'cancel'])->name('checkout.cancel');

// API routes moved to routes/api.php

// Providers handled via ProvidersController below

// Help / Partners (guarded)
Route::get('/partners', [StaticPagesController::class, 'partners']);

// XML sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
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
Route::fallback([\App\Http\Controllers\PageController::class, 'show'])
    ->where('fallbackPlaceholder', '^(?!api\/).*$');
