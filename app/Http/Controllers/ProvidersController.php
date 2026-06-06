<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\VendorClient;
use App\Services\ProfilePageResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProvidersController extends Controller
{
    public function index()
    {
        return view('providers.index', [
            'seo' => [
                'title' => 'Practitioners | We Offer Wellness™',
                'description' => 'Discover trusted practitioners and facilitators across therapies and modalities.',
                'canonical' => url('/providers'),
            ],
        ]);
    }

    public function show(string $slug, ProfilePageResolver $resolver)
    {
        $user = $resolver->resolve($slug, 'practitioner');

        abort_if($user === null, 404);

        return view('providers.show', [
            'seo' => $resolver->buildSeo($user, 'practitioner', url('/practioner/'.$slug)),
            'slug' => $slug,
            'profileType' => 'practitioner',
            'user' => $user,
        ]);
    }

    public function storeReview(Request $request, string $slug, ProfilePageResolver $resolver): RedirectResponse
    {
        $user = $resolver->resolve($slug, 'practitioner');
        abort_if($user === null, 404);

        $vendor = $user->vendorDetail;
        abort_if($vendor === null, 404);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['required', 'string', 'min:10', 'max:2000'],
            'review_title' => ['nullable', 'string', 'max:120'],
        ]);

        $account = $request->user();
        $reviewText = trim((string) $validated['review_text']);
        $reviewTitle = trim((string) ($validated['review_title'] ?? ''));
        $existingReview = Review::query()
            ->where('user_id', $account->id)
            ->where('vendor_id', $vendor->id)
            ->whereNull('product_id')
            ->first();

        $review = Review::updateOrCreate(
            [
                'user_id' => $account->id,
                'vendor_id' => $vendor->id,
                'product_id' => null,
            ],
            [
                'rating' => (int) $validated['rating'],
                'review_text' => $reviewText,
                'reviewer' => trim((string) ($account->public_display_name ?? $account->name ?? 'Verified client')),
                'title' => $reviewTitle !== '' ? $reviewTitle : ($existingReview?->title ?? null),
                'source' => 'site',
                'source_url' => url('/practioner/' . $slug),
                'media_type' => null,
                'media_url' => null,
                'is_provider_added' => false,
            ]
        );

        if (Schema::hasTable('vendor_clients')) {
            VendorClient::firstOrCreate([
                'vendor_id' => $vendor->id,
                'client_user_id' => $account->id,
            ]);
        }

        $defaultRedirect = url('/practioner/' . $slug) . '?review=1#reviews';
        $redirectTo = trim((string) $request->input('redirect', $defaultRedirect));
        if ($redirectTo === '' || ! preg_match('#^(https?://|/)#i', $redirectTo)) {
            $redirectTo = $defaultRedirect;
        }

        return redirect()
            ->to($redirectTo)
            ->with('status', $review->wasRecentlyCreated ? 'Your review has been added.' : 'Your review has been updated.');
    }
}
