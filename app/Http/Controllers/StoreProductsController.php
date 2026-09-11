<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\StoreProductReview;

class StoreProductsController extends Controller
{
    public function index(Request $request)
    {
        $response = $this->backend()->get('/api/store/products', ['limit' => $request->integer('limit', 24)]);
        return Inertia::render('Store/Index', ['products' => $response->successful() ? ($response->json('data') ?: []) : []]);
    }

    public function show(Request $request, string $category, string $slug)
    {
        $response = $this->backend()->get('/api/store/products/'.rawurlencode($slug));
        if (!$response->successful()) abort(404);
        $product = $response->json('data');
        $this->attachReviews($product);
        $canonicalCategory = (string) data_get($product, 'category.slug', '');
        if ($canonicalCategory !== '' && $canonicalCategory !== $category) {
            return redirect('/product/'.rawurlencode($canonicalCategory).'/'.rawurlencode($slug), 301);
        }
        return view('store.product', ['product' => $product]);
    }

    public function storeReview(Request $request, string $category, string $slug)
    {
        $response = $this->backend()->get('/api/store/products/'.rawurlencode($slug));
        abort_unless($response->successful(), 404);
        $product = $response->json('data');
        abort_if((string) data_get($product, 'category.slug', '') !== $category, 404);
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_title' => ['nullable', 'string', 'max:120'],
            'review_text' => ['required', 'string', 'min:10', 'max:2000'],
        ]);
        StoreProductReview::updateOrCreate(
            ['store_product_id' => (int) data_get($product, 'id'), 'user_id' => $request->user()->id],
            ['rating' => (int) $validated['rating'], 'title' => trim((string) ($validated['review_title'] ?? '')) ?: null, 'body' => trim((string) $validated['review_text']), 'status' => 'published']
        );
        return redirect()->to(url('/product/'.$category.'/'.$slug).'#reviews')->with('status', 'Your review has been saved.');
    }

    public function legacyShow(string $slug)
    {
        $response = $this->backend()->get('/api/store/products/'.rawurlencode($slug));
        if (! $response->successful()) {
            abort(404);
        }

        $product = $response->json('data');
        $category = (string) data_get($product, 'category.slug', '');
        if ($category !== '') {
            return redirect('/product/'.rawurlencode($category).'/'.rawurlencode($slug), 301);
        }

        return redirect('/products/'.rawurlencode($slug), 301);
    }

    public function apiIndex(Request $request)
    {
        $response = $this->backend()->get('/api/store/products', $request->query());
        return response()->json($response->successful() ? $response->json() : ['data' => []], $response->status());
    }

    public function apiShow(string $slug)
    {
        $response = $this->backend()->get('/api/store/products/'.rawurlencode($slug));
        return response()->json($response->successful() ? $response->json() : ['message' => 'Product not found'], $response->status());
    }

    private function attachReviews(?array &$product): void
    {
        if (! is_array($product) || empty($product['id'])) return;
        $reviews = StoreProductReview::query()->with('user:id,name,public_display_name')->where('store_product_id', (int) $product['id'])->where('status', 'published')->latest()->get();
        $product['review_count'] = $reviews->count();
        $product['rating'] = $reviews->isNotEmpty() ? round((float) $reviews->avg('rating'), 1) : null;
        $product['reviews'] = $reviews->map(fn (StoreProductReview $review) => [
            'rating' => (int) $review->rating,
            'title' => (string) ($review->title ?? ''),
            'body' => (string) $review->body,
            'author' => (string) ($review->user?->public_display_name ?: $review->user?->name ?: 'Verified customer'),
            'date' => optional($review->created_at)->format('j M Y'),
        ])->values()->all();
    }

    private function backend()
    {
        return Http::acceptJson()
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; WeOfferWellness/1.0)',
            ])
            ->connectTimeout(5)
            ->timeout(20)
            ->baseUrl(rtrim((string) env('BACKEND_URL', 'https://studio.weofferwellness.co.uk'), '/'));
    }
}
