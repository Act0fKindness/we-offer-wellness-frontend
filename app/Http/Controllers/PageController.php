<?php

namespace App\Http\Controllers;

use App\Models\LegacyPageVisit;
use App\Models\Page;
use App\Models\PageRedirect;
use App\Support\BotPathMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PageController extends Controller
{
    public function show(Request $request, string $slug = '')
    {
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            abort(404);
        }

        $slug = $slug !== '' ? $slug : (string) $request->path();
        $slug = ltrim($slug, '/');

        if (BotPathMatcher::shouldBlock($request)) {
            abort(410);
        }

        // Handle redirects first (exact path match)
        $path = '/'.$slug;
        if ($redir = PageRedirect::query()->where('from_path', $path)->first()) {
            return redirect()->to($redir->to_path, $redir->http_code ?? 301);
        }

        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$page) {
            $this->logLegacyMiss($request, $slug);
            return redirect('/');
        }

        $title = $page->meta_title ?: $page->title;
        $desc = $page->meta_description ?: '';
        $canon = $page->canonical_url ?: url('/'.$page->slug);
        $og = $page->og_image_url ?: '';

        // Render blocks (if any) and merge with rich editor HTML
        $blocksArr = (array) ($page->blocks ?? []);
        $hasBlocks = !empty($blocksArr);
        $blocksHtml = $this->renderBlocksToHtml($blocksArr);
        $richHtml = trim((string) ($page->body_html ?? ''));
        if ($hasBlocks) {
            // When blocks are present, append rich content as its own section for consistent spacing
            if ($richHtml !== '') {
                $blocksHtml .= '<section class="section"><div class="container-page prose">'.$richHtml.'</div></section>';
            }
            $bodyHtml = $blocksHtml;
        } else {
            // No blocks → pass raw rich HTML so Page.vue will wrap it and include the H1
            $bodyHtml = $richHtml;
        }

        // Append tracking pixel (Backend route) for page views
        try {
            $backend = rtrim((string) env('BACKEND_ASSET_URL', ''), '/');
            if ($backend !== '') {
                $pixel = $backend.'/t/pageview.gif?pid='.(int)$page->id.'&slug='.rawurlencode((string)$page->slug);
                $bodyHtml .= '<img src="'.e($pixel).'" width="1" height="1" style="display:none" alt="" />';
            }
        } catch (\Throwable $e) {}

        return Inertia::render('General/Page', [
            'title' => $title,
            'metaDescription' => $desc,
            'bodyHtml' => $bodyHtml,
            'canonical' => $canon,
            'ogImage' => $og,
        ]);
    }

    private function renderBlocksToHtml(array $blocks): string
    {
        $out = '';
        foreach ($blocks as $b) {
            if (!is_array($b)) { continue; }
            $type = (string) ($b['type'] ?? '');
            if ($type === 'hero') {
                $h = e((string) ($b['headline'] ?? ''));
                $sh = e((string) ($b['subheadline'] ?? ''));
                $img = (string) ($b['image'] ?? '');
                $alignRaw = (string) ($b['align'] ?? 'center');
                $align = in_array($alignRaw, ['left','center','right'], true) ? $alignRaw : 'center';
                $overlay = !empty($b['overlay']);
                $btn = (array) ($b['button'] ?? []);
                $btnText = e((string) ($btn['text'] ?? ''));
                $btnUrl = e((string) ($btn['url'] ?? ''));
                $btnStyleRaw = (string) ($btn['style'] ?? 'primary');
                $btnStyle = in_array($btnStyleRaw, ['primary','secondary'], true) ? $btnStyleRaw : 'primary';
                $imgHtml = $img ? '<img src="'.e($img).'" alt="" style="max-width: 100%; height: auto;">' : '';
                $btnHtml = ($btnText && $btnUrl) ? ('<a class="btn btn-'.e($btnStyle).' mt-2" href="'.$btnUrl.'">'.$btnText.'</a>') : '';
                $bgStyle = $img ? ' style="position:relative;overflow:hidden"' : '';
                $overlayHtml = ($overlay && $img) ? '<div style="position:absolute;inset:0;background:rgba(0,0,0,.28)"></div>' : '';
                $txtStyle = ($overlay && $img) ? ' style="position:relative;color:#fff"' : '';
                $imgBg = $img ? '<div style="position:absolute;inset:0;background:center/cover no-repeat url('.e($img).');filter:'.($overlay?'brightness(0.8)':'none').'"></div>' : '';
                $out .= '<section class="section"'.$bgStyle.'>'.$imgBg.$overlayHtml.'<div class="container-page text-'.e($align).'"'.$txtStyle.'>'
                     .  ($h ? '<h1 class="mb-2">'.$h.'</h1>' : '')
                     .  ($sh ? '<p class="lead">'.$sh.'</p>' : '')
                     .  ($img && !$overlay ? $imgHtml : '')
                     .  $btnHtml
                     .  '</div></section>';
            } elseif ($type === 'text') {
                $html = (string) ($b['html'] ?? '');
                $out .= '<section class="section"><div class="container-page prose">'.$html.'</div></section>';
            } elseif ($type === 'image') {
                $url = (string) ($b['url'] ?? '');
                $alt = e((string) ($b['alt'] ?? ''));
                $cap = e((string) ($b['caption'] ?? ''));
                $alignRaw = (string) ($b['align'] ?? 'center');
                $align = in_array($alignRaw, ['left','center','right'], true) ? $alignRaw : 'center';
                $widthRaw = (string) ($b['width'] ?? '100%');
                $width = in_array($widthRaw, ['100%','75%','50%'], true) ? $widthRaw : '100%';
                if ($url) {
                    $out .= '<section class="section"><div class="container-page text-'.e($align).'">'
                         .  '<figure style="display:inline-block;max-width:100%"><img src="'.e($url).'" alt="'.$alt.'" style="width:'.e($width).';max-width:100%;height:auto;">'
                         .  ($cap ? '<figcaption class="text-muted">'.$cap.'</figcaption>' : '')
                         .  '</figure></div></section>';
                }
            } elseif ($type === 'faq') {
                $items = (array) ($b['items'] ?? []);
                $out .= '<section class="section"><div class="container-page"><div class="vstack gap-2">';
                foreach ($items as $it) {
                    $q = e((string) ($it['q'] ?? ''));
                    $a = (string) ($it['a'] ?? '');
                    if ($q || $a) {
                        $out .= '<details class="card p-3"><summary class="fw-semibold">'.$q.'</summary><div class="prose mt-2">'.$a.'</div></details>';
                    }
                }
                $out .= '</div></div></section>';
            } elseif ($type === 'features') {
                $items = (array) ($b['items'] ?? []);
                $cols = max(1, min((int) ($b['columns'] ?? 2), 4));
                $colClass = match ($cols) { 1 => 'col-12', 2 => 'col-md-6', 3 => 'col-md-4', default => 'col-md-3' };
                $out .= '<section class="section"><div class="container-page"><div class="row g-3">';
                foreach ($items as $it) {
                    $t = e((string) ($it['title'] ?? ''));
                    $x = e((string) ($it['text'] ?? ''));
                    $icon = (string) ($it['icon'] ?? '');
                    $iconHtml = $icon ? '<img src="'.e($icon).'" alt="" style="width:28px;height:28px;object-fit:contain;margin-right:8px">' : '';
                    $out .= '<div class="'.$colClass.'"><div class="card p-3 h-100 d-flex"><div class="d-flex align-items-start">'.$iconHtml.'<div class="fw-semibold">'.$t.'</div></div><div class="text-muted mt-1">'.$x.'</div></div></div>';
                }
                $out .= '</div></div></section>';
            } elseif ($type === 'cta') {
                $text = e((string) ($b['text'] ?? 'Learn more'));
                $url  = e((string) ($b['url'] ?? '/'));
                $styleRaw = (string) ($b['style'] ?? 'primary');
                $style = in_array($styleRaw, ['primary','secondary'], true) ? $styleRaw : 'primary';
                $newtab = !empty($b['newtab']);
                $target = $newtab ? ' target="_blank" rel="noopener"' : '';
                $out .= '<section class="section"><div class="container-page text-center"><a class="btn btn-'.e($style).'" href="'.$url.'"'.$target.'>'.$text.'</a></div></section>';
            }
        }
        return $out ?: '<section class="section"><div class="container-page"></div></section>';
    }

    private function logLegacyMiss(Request $request, string $slug): void
    {
        try {
            if (BotPathMatcher::shouldBlock($request)) {
                return;
            }
            $attemptedPath = '/'.ltrim((string) $request->path(), '/');
            if ($attemptedPath === '//') {
                $attemptedPath = '/';
            }
            LegacyPageVisit::create([
                'slug' => $slug,
                'path' => $attemptedPath,
                'full_url' => $request->fullUrl(),
                'referer' => (string) $request->headers->get('referer'),
                'user_agent' => (string) $request->userAgent(),
                'ip_address' => (string) $request->ip(),
                'metadata' => [
                    'query' => $request->query(),
                    'v3' => (bool) env('VITE_V3_BUILD', false),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
