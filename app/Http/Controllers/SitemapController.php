<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Support\Facades\File;

class SitemapController extends Controller
{
    public function index(SitemapService $service)
    {
        return $this->serveXml(
            public_path('sitemap.xml'),
            fn (): string => $service->buildIndexXml(),
        );
    }

    public function indexFile(SitemapService $service)
    {
        return $this->index($service);
    }

    public function pages(SitemapService $service)
    {
        return $this->serveXml(
            public_path('sitemap-pages.xml'),
            fn (): string => $service->buildSegmentXml('static') ?? '',
        );
    }

    public function segment(SitemapService $service, string $segment)
    {
        $segment = trim($segment);

        if ($segment === '') {
            abort(404);
        }

        return $this->serveXml(
            public_path('sitemaps/' . $segment . '.xml'),
            fn (): string => $service->buildSegmentXml($segment) ?? '',
        );
    }

    /**
     * @param  callable():string  $fallback
     */
    private function serveXml(string $path, callable $fallback)
    {
        if (File::isFile($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ]);
        }

        $xml = trim($fallback());
        if ($xml !== '') {
            return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
        }

        abort(404);
    }
}
