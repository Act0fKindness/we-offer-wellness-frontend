<?php

namespace App\Http\Controllers;

use App\Services\GuideRegistryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GuideController extends Controller
{
    public function index(GuideRegistryService $guides): View
    {
        return view('guides.hub', [
            'page' => $guides->guidesHub(),
        ]);
    }

    public function format(GuideRegistryService $guides, string $format): View
    {
        $page = $guides->formatHub($format);
        abort_if($page === null, 404);

        return view('guides.hub', ['page' => $page]);
    }

    public function modality(GuideRegistryService $guides, string $format, string $modality): View
    {
        $page = $guides->modalityHub($format, $modality);
        abort_if($page === null, 404);

        return view('guides.hub', ['page' => $page]);
    }

    public function show(GuideRegistryService $guides, string $format, string $modality, string $guide): View
    {
        $page = $guides->guidePage($format, $modality, $guide);
        abort_if($page === null, 404);

        return view('guides.show', ['page' => $page]);
    }

    public function legacy(GuideRegistryService $guides, string $legacySlug): RedirectResponse
    {
        $target = $guides->legacyRedirects()['/' . trim($legacySlug, '/')] ?? null;
        abort_if($target === null, 404);

        return redirect($target, 301);
    }
}
