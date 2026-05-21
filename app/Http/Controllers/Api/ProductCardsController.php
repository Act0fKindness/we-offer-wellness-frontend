<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductCardsController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->integer('limit', 12);
        $limit = max(1, min($limit, 24));
        $pm = (float) $request->input('price_max', 50);
        $mode = strtolower((string) $request->input('mode', 'online'));

        $q = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->with(['media','options.values','category'])
            ->where(function ($w) {
                $w->whereHas('status', function ($qs) { $qs->whereIn('status', ['live','approved']); });
            });

        if ($mode === 'online') {
            $q->whereHas('options', function ($oq) {
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', function ($vq) { $vq->whereRaw("LOWER(value) = 'online'"); });
            });
        } elseif ($mode === 'in-person') {
            $q->whereHas('options', function ($oq) {
                $oq->where('meta_name', 'locations')
                   ->whereHas('values', function ($vq) { $vq->whereRaw("LOWER(value) <> 'online'"); });
            });
        }

        $q->where(function ($qq) use ($pm) {
            $qq->where(function ($qp) use ($pm) { $qp->where('price','<',1000)->where('price','<=',$pm); })
               ->orWhere(function ($qp) use ($pm) { $qp->where('price','>=',1000)->where('price','<=',$pm*100); })
               ->orWhereHas('variants', function ($qv) use ($pm) {
                   $qv->where(function ($qq2) use ($pm) { $qq2->where('price','<',1000)->where('price','<=',$pm); })
                      ->orWhere(function ($qq2) use ($pm) { $qq2->where('price','>=',1000)->where('price','<=',$pm*100); });
               });
        });

        $q->orderByRaw('COALESCE(reviews_avg_rating, 0) * LOG(1 + COALESCE(reviews_count, 0)) DESC')
          ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
          ->orderByRaw('COALESCE(reviews_count, 0) DESC');

        $products = $q->limit($limit)->get();
        $html = '';
        foreach ($products as $p) {
            $html .= view('partials.product_card', ['product' => $p])->render();
        }
        return response($html)->header('Content-Type', 'text/html');
    }
}
