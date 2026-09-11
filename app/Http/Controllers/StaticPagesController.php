<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Models\Platform;
use Illuminate\Http\Request;

class StaticPagesController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $document = $this->findLegalDocument($slug);

        if ($document) {
            return $this->renderLegalDocument($document, $slug);
        }

        return app(PageController::class)->show($request, $slug);
    }

    public function partners() { return view('general.partners'); }
    public function giftCards() { return redirect('/giftcards', 301); }

    private function findLegalDocument(string $slug): ?LegalDocument
    {
        $slug = strtolower(trim($slug));
        $slug = match ($slug) {
            'terms-of-service' => 'terms',
            'privacy-policy' => 'privacy',
            default => $slug,
        };

        $platformName = match (true) {
            str_contains(request()->getHost(), 'studio.weofferwellness.co.uk') => 'WOW Studio',
            default => 'WOW Store',
        };

        $platform = Platform::query()->firstOrCreate(['name' => $platformName]);

        $document = LegalDocument::query()
            ->where('platform_id', $platform->id)
            ->where('slug', $slug)
            ->first();

        if ($document) {
            return $document;
        }

        // Fallback: if the document was created on the wrong platform, still render it.
        return LegalDocument::query()
            ->where('slug', $slug)
            ->orderByRaw('CASE WHEN platform_id = ? THEN 0 ELSE 1 END', [$platform->id])
            ->first();
    }

    private function renderLegalDocument(LegalDocument $document, string $slug)
    {
        $platform = $document->platform ?: $this->resolvePlatform();
        return view('legal.document', compact('document', 'platform', 'slug'));
    }

    private function resolvePlatform(): Platform
    {
        $platformName = match (true) {
            str_contains(request()->getHost(), 'studio.weofferwellness.co.uk') => 'WOW Studio',
            default => 'WOW Store',
        };

        return Platform::query()->firstOrCreate(['name' => $platformName]);
    }
}
