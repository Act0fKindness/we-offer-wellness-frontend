<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\LocationCatalogService;
use App\Services\SeoStructureService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LandingRedirectsController extends Controller
{
    public function shopifyProduct(Request $request, string $handle)
    {
        $target = $this->resolveLegacyProductTarget($handle);
        if ($target === null) {
            return redirect('/therapies', 301);
        }

        return redirect()->to($target, 301);
    }

    public function shopifyProductSandbox(Request $request, string $prefix, string $pixel, string $handle)
    {
        return $this->shopifyProduct($request, $handle);
    }

    public function shopifyCollection(Request $request, ?string $slug = null)
    {
        $slug = trim(rawurldecode((string) $slug));
        if ($slug === '') {
            return redirect('/therapies', 301);
        }

        $target = $this->resolveLegacyCollectionTarget($slug);
        if ($target !== null) {
            return redirect()->to($target, 301);
        }

        return redirect('/therapies', 301);
    }

    public function shopifyPage(Request $request, string $path)
    {
        $path = trim(rawurldecode($path), '/');
        if ($path === '') {
            return redirect('/therapies', 301);
        }

        $segments = array_values(array_filter(explode('/', $path), static fn ($segment) => trim((string) $segment) !== ''));
        $leaf = (string) array_pop($segments);

        if ($leaf !== '') {
            if ($target = $this->resolveLegacyLocationTarget($leaf)) {
                return redirect()->to($target, 301);
            }

            if ($target = $this->resolveLegacyNeedTarget($leaf)) {
                return redirect()->to($target, 301);
            }

            if ($target = $this->resolveLegacyTherapyTarget($leaf)) {
                return redirect()->to($target, 301);
            }
        }

        $normalized = $this->normalizeLegacyKey($path);
        if (str_contains($normalized, 'gift')) {
            return redirect('/gifts', 301);
        }

        if (str_contains($normalized, 'location')) {
            if ($target = $this->resolveLegacyLocationTarget($path)) {
                return redirect()->to($target, 301);
            }
        }

        return redirect('/therapies', 301);
    }

    public function shopifyAccountLogin(Request $request)
    {
        $redirectTo = trim((string) $request->query('return_url', '/account'));
        if ($redirectTo === '') {
            $redirectTo = '/account';
        }

        if (! str_starts_with($redirectTo, '/')) {
            $redirectTo = '/' . $redirectTo;
        }

        $query = http_build_query(['redirect' => $redirectTo], '', '&', PHP_QUERY_RFC3986);

        return redirect('/login?' . $query, 301);
    }

    public function offeringHandle(string $type, string $handle)
    {
        $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
        if (!in_array(strtolower($type), $allowed, true)) abort(404);
        $p = Product::query()->where('handle', $handle)
            ->orWhere(fn($q)=>$q->where('id', is_numeric($handle) ? (int)$handle : 0))
            ->first();
        if (!$p) abort(404);
        return redirect()->to(app(SeoStructureService::class)->canonicalProductUrl($p), 301);
    }

    public function experiencesIndex()
    {
        return redirect('/gifts', 301);
    }

    public function experienceIndex()
    {
        return redirect('/gifts', 301);
    }

    public function experienceSlug(string $slug)
    {
        return redirect('/experiences/'.ltrim($slug, '/'), 301);
    }

    public function experiencesSlug(string $slug)
    {
        $s = strtolower($slug);
        if (str_contains($s, 'sound-bath')) return redirect('/sound-healing/events', 301);
        if ($s === 'reiki') return redirect('/reiki/therapies', 301);
        if (str_contains($s, 'breathwork')) return redirect('/breathwork/workshops', 301);
        if (str_contains($s, 'retreat')) return redirect('/retreats', 301);
        return redirect('/'.Str::slug($s).'/therapies', 301);
    }

    private function resolveLegacyProductTarget(string $handle): ?string
    {
        $raw = trim(rawurldecode($handle));
        $normalized = $this->normalizeLegacyKey($raw);
        if ($normalized === '') {
            return null;
        }

        $product = Product::query()
            ->with(['variants'])
            ->where('handle', $raw)
            ->first();

        if ($product === null) {
            $product = Product::query()
                ->select(['id', 'title', 'handle'])
                ->get()
                ->first(function (Product $candidate) use ($normalized): bool {
                    $copyless = $this->stripLegacyCopySuffix($normalized);

                    return $this->normalizeLegacyKey((string) $candidate->handle) === $normalized
                        || $this->normalizeLegacyKey((string) $candidate->title) === $normalized
                        || $this->normalizeLegacyKey((string) $candidate->handle) === $copyless
                        || $this->normalizeLegacyKey((string) $candidate->title) === $copyless;
                });
        }

        if ($product !== null) {
            return $this->canonicalProductRedirect($product);
        }

        $offering = OfferingV3::query()
            ->with(['type', 'category'])
            ->where('slug', $raw)
            ->first();

        if ($offering === null) {
            $offering = OfferingV3::query()
                ->select(['id', 'title', 'slug', 'status', 'type_id', 'category_id'])
                ->with(['type', 'category'])
                ->get()
                ->first(function (OfferingV3 $candidate) use ($normalized): bool {
                    return $this->normalizeLegacyKey((string) $candidate->slug) === $normalized
                        || $this->normalizeLegacyKey((string) $candidate->title) === $normalized;
                });
        }

        if ($offering !== null && in_array((string) $offering->status, ['live', 'approved'], true)) {
            return $this->canonicalOfferingRedirect($offering);
        }

        return null;
    }

    private function resolveLegacyCollectionTarget(string $slug): ?string
    {
        $raw = trim(rawurldecode($slug));
        $normalized = $this->normalizeLegacyKey($raw);
        if ($normalized === '') {
            return null;
        }

        $special = [
            'all' => '/therapies',
            'experiences' => '/therapies',
            'discover-wellness' => '/therapies',
            'new-in' => '/therapies',
            'new-in-experiences' => '/therapies',
            'our-newest-experiences' => '/therapies',
            'gift-finder' => '/gifts',
            'gift-cards' => '/giftcards',
            'gifts-under-50' => '/gifts',
            'gifts-under-100' => '/gifts',
            'premium-gifts-from-100' => '/gifts',
            'gifts-for-her' => '/gifts',
            'upcoming-events' => '/events',
            'events-workshops' => '/events',
            'events-and-workshops' => '/events',
        ];

        if (isset($special[$normalized])) {
            return $special[$normalized];
        }

        if (($target = $this->resolveLegacyProviderTarget($raw)) !== null) {
            return $target;
        }

        if (($target = $this->resolveLegacyProductTarget($raw)) !== null) {
            return $target;
        }

        if (($target = $this->resolveLegacyLocationTarget($raw)) !== null) {
            return $target;
        }

        if (($target = $this->resolveLegacyNeedTarget($raw)) !== null) {
            return $target;
        }

        if (($target = $this->resolveLegacyTherapyTarget($raw)) !== null) {
            return $target;
        }

        if (($target = $this->resolveLegacyCategoryTarget($raw)) !== null) {
            return $target;
        }

        if (str_contains($normalized, 'gift')) {
            return '/gifts';
        }

        if (str_contains($normalized, 'event')) {
            return '/events';
        }

        if (str_contains($normalized, 'retreat')) {
            return '/retreats';
        }

        if (str_contains($normalized, 'workshop')) {
            return '/workshops';
        }

        if (str_contains($normalized, 'class') || str_contains($normalized, 'yoga')) {
            return '/classes';
        }

        return '/therapies';
    }

    private function resolveLegacyProviderTarget(string $slug): ?string
    {
        $raw = trim(rawurldecode($slug));
        $normalized = $this->normalizeLegacyKey($raw);
        if ($normalized === '') {
            return null;
        }

        $providerSlug = $normalized;
        if (str_starts_with($providerSlug, 'provider-')) {
            $providerSlug = substr($providerSlug, 9);
        }
        $providerSlug = preg_replace('/-atom$/', '', $providerSlug) ?? $providerSlug;

        $knownProviders = [
            'evi-constantinidou-512',
            'morgana-marie',
            'mike-fossett',
            'stacey-paige',
            'dara-flows',
            'jo-adams',
            'tremendoustre',
            'jen-deer',
        ];

        if (str_starts_with($normalized, 'provider-') || str_ends_with(strtolower($raw), '.atom') || in_array($providerSlug, $knownProviders, true)) {
            return '/practioner/' . trim($providerSlug, '-');
        }

        return null;
    }

    private function resolveLegacyLocationTarget(string $slug): ?string
    {
        $raw = trim(rawurldecode($slug));
        $normalized = $this->normalizeLegacyKey($raw);
        if ($normalized === '') {
            return null;
        }

        $catalog = app(LocationCatalogService::class)->load();
        $nodes = (array) ($catalog['flat'] ?? []);
        $needles = [];
        foreach (array_reverse(array_values(array_filter(explode('-', $normalized)))) as $segment) {
            if (strlen($segment) < 3 && ! in_array($segment, ['uk', 'gb'], true)) {
                continue;
            }

            $needles[] = $segment;
        }
        $needles[] = $normalized;
        $needles = array_values(array_unique($needles));

        foreach ($needles as $needle) {
            $best = null;
            $bestScore = 0;

            foreach ($nodes as $node) {
                if (! is_array($node)) {
                    continue;
                }

                $path = (string) ($node['path'] ?? '');
                $nodeSlug = $this->normalizeLegacyKey((string) ($node['slug'] ?? ''));
                $nodeTitle = $this->normalizeLegacyKey((string) ($node['title'] ?? ''));
                $nodePath = $this->normalizeLegacyKey(trim(str_replace('/locations/', '', $path), '/'));

                $score = 0;
                if ($needle === $nodeSlug || $needle === $nodeTitle || $needle === $nodePath) {
                    $score = 100;
                } elseif ($nodeSlug !== '' && (str_contains($nodeSlug, $needle) || str_contains($needle, $nodeSlug))) {
                    $score = 80;
                } elseif ($nodeTitle !== '' && (str_contains($nodeTitle, $needle) || str_contains($needle, $nodeTitle))) {
                    $score = 70;
                } elseif ($nodePath !== '' && (str_contains($nodePath, $needle) || str_contains($needle, $nodePath))) {
                    $score = 60;
                }

                if ($score > $bestScore && $path !== '') {
                    $bestScore = $score;
                    $best = $path;
                }
            }

            if ($best !== null) {
                return $best;
            }
        }

        return null;
    }

    private function resolveLegacyNeedTarget(string $slug): ?string
    {
        $normalized = $this->normalizeLegacyKey($slug);
        if ($normalized === '') {
            return null;
        }

        $map = [
            'stress-and-anxiety' => '/needs/stress-and-anxiety',
            'stress-anxiety' => '/needs/stress-and-anxiety',
            'sleep-issues' => '/needs/sleep-issues',
            'low-mood-burnout' => '/needs/low-mood-burnout',
            'overwhelm' => '/needs/overwhelm',
            'worry' => '/needs/worry',
            'pain-management' => '/needs/pain-management',
            'pain' => '/needs/pain-management',
            'mens-wellbeing' => '/needs/mens-wellbeing',
            'mens-wellbeing-support' => '/needs/mens-wellbeing',
            'digestive-health' => '/needs/digestive-health',
            'gut-health' => '/needs/digestive-health',
            'fertility-pregnancy' => '/needs/fertility-pregnancy',
            'pregnancy' => '/needs/fertility-pregnancy',
            'nervous-system' => '/needs/nervous-system',
            'trauma' => '/needs/nervous-system',
            'corporate-wellbeing' => '/needs/corporate-wellbeing',
        ];

        foreach ($map as $needle => $target) {
            if ($normalized === $needle || str_contains($normalized, $needle)) {
                return $target;
            }
        }

        return null;
    }

    private function resolveLegacyTherapyTarget(string $slug): ?string
    {
        $normalized = $this->normalizeLegacyKey($slug);
        if ($normalized === '') {
            return null;
        }

        $map = [
            'sound-healing' => '/therapies/sound-healing',
            'sound-bath' => '/therapies/sound-healing',
            'gong' => '/therapies/sound-healing',
            'reiki' => '/therapies/reiki',
            'reflexology' => '/therapies/reflexology',
            'acupuncture' => '/therapies/acupuncture',
            'breathwork' => '/therapies/breathwork',
            'massage' => '/therapies/massage',
            'hypnotherapy' => '/therapies/hypnotherapy',
            'somatic' => '/therapies/somatic-experiencing',
            'meditation' => '/therapies/meditation',
            'mindfulness' => '/therapies/meditation',
            'corporate-wellness' => '/therapies/corporate-wellness',
            'corporate' => '/therapies/corporate-wellness',
        ];

        foreach ($map as $needle => $target) {
            if ($normalized === $needle || str_contains($normalized, $needle)) {
                return $target;
            }
        }

        return null;
    }

    private function resolveLegacyCategoryTarget(string $slug): ?string
    {
        $normalized = $this->normalizeLegacyKey($slug);
        if ($normalized === '') {
            return null;
        }

        $category = ProductCategory::query()
            ->select(['id', 'name'])
            ->get()
            ->first(function (ProductCategory $candidate) use ($normalized): bool {
                $candidateSlug = $this->normalizeLegacyKey((string) $candidate->name);
                return $candidateSlug === $normalized;
            });

        if ($category === null) {
            return null;
        }

        return '/' . Str::slug((string) $category->name);
    }

    private function canonicalProductRedirect(Product $product): string
    {
        return app(SeoStructureService::class)->canonicalProductUrl($product);
    }

    private function canonicalOfferingRedirect(OfferingV3 $offering): string
    {
        return app(SeoStructureService::class)->canonicalOfferingUrl($offering);
    }

    private function normalizeLegacyKey(string $value): string
    {
        $value = rawurldecode(trim($value));
        $value = preg_replace('/\.atom$/i', '', $value) ?? $value;
        $value = str_replace(['®', '™'], '', $value);
        $value = Str::of($value)->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->toString();
        $value = preg_replace('/^(collections?|products?|pages?)-/', '', $value) ?? $value;
        $value = preg_replace('/^(location|locations)-/', '', $value) ?? $value;

        return trim($value, '-');
    }

    private function stripLegacyCopySuffix(string $value): string
    {
        return preg_replace('/-copy$/', '', $value) ?? $value;
    }
}
