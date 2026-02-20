<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
      $limit = (int) $request->integer('limit', 6);

      // 1) Prefer Mindful Times API if configured/available
      $timesBase = rtrim((string) env('TIMES_BASE_URL', ''), '/');
      if ($timesBase !== '') {
        try {
          $res = Http::timeout(4)->acceptJson()->get($timesBase.'/api/articles', [ 'limit' => $limit ]);
          if ($res->successful()) {
            $json = $res->json();
            if (is_array($json)) {
              return response()->json($json);
            }
          }
        } catch (\Throwable $e) {
          // fall through to local
        }
      }

      // 2) Fallback to local DB if remote not available
      $base = Article::query()->with(['featuredMedia', 'category']);
      $queryPublished = (clone $base)
        ->where(function($q){
          $q->where('status', 'published')
            ->orWhere('status', 'active')
            ->orWhere('status', 1)
            ->orWhere('status', 'Published')
            ->orWhere('status', 'PUBLISHED');
        })
        ->latest('id');

      $articles = $queryPublished->limit($limit)->get();
      if ($articles->isEmpty()) {
        $articles = (clone $base)->latest('id')->limit($limit)->get();
      }

      $items = $articles->map(function(Article $a){
        $img = null;
        if ($a->relationLoaded('featuredMedia') && $a->featuredMedia) {
          $media = $a->featuredMedia;
          $path = $media->url ?? $media->path ?? $media->media_url ?? null;
          if ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
              $img = $path;
            } else {
              $backend = rtrim((string) env('BACKEND_ASSET_URL', env('BACKEND_URL', '')), '/');
              $clean = ltrim($path, '/');
              $img = $backend ? ($backend . '/storage/' . $clean) : asset('storage/'.$clean);
            }
          }
        }
        // Fallback: first from media()
        if (!$img && method_exists($a, 'media') && $a->media()->exists()) {
          $m = $a->media()->first();
          $p = $m->url ?? $m->path ?? $m->media_url ?? null;
          if ($p) {
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
              $img = $p;
            } else {
              $backend = rtrim((string) env('BACKEND_ASSET_URL', env('BACKEND_URL', '')), '/');
              $clean = ltrim($p, '/');
              $img = $backend ? ($backend . '/storage/' . $clean) : asset('storage/'.$clean);
            }
          }
        }

        // Build external Times URL: category/year/month/slug-id
        $catName = optional($a->category)->name ?: 'journal';
        $cat = Str::slug($catName);
        $year = optional($a->created_at)->format('Y') ?? date('Y');
        $month = optional($a->created_at)->format('m') ?? date('m');
        $slug = Str::slug((string) ($a->title ?: 'article'));
        $timesBase = rtrim(env('TIMES_BASE_URL', 'https://times.weofferwellness.co.uk'), '/');
        $href = $timesBase . "/{$cat}/{$year}/{$month}/{$slug}-{$a->id}";

        return [
          'id' => $a->id,
          'title' => $a->title,
          'excerpt' => str($a->content ?? '')->stripTags()->limit(140)->value(),
          'tag' => optional($a->category)->name ?? 'MindfulTimes',
          'img' => $img,
          'href' => $href,
        ];
      });

      return response()->json($items);
    }
}
