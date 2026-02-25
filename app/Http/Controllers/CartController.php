<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function page()
    {
        $items = session('cart.items', []);
        if (empty($items)) {
            $cookie = request()->cookie('wow_cart');
            if ($cookie) { $restored = json_decode($cookie, true) ?: []; if (is_array($restored)) { session(['cart.items' => $restored]); } }
        }
        return view('cart.index');
    }

    public function promo(Request $request)
    {
        $code = strtoupper(trim((string)$request->input('code', '')));
        if ($code !== '') session(['cart_promo_code' => $code]); else session()->forget('cart_promo_code');
        return response()->json(['ok' => true, 'code' => $code]);
    }

    public function count()
    {
        $items = session('cart.items', []);
        if (empty($items)) {
            $cookie = request()->cookie('wow_cart');
            if ($cookie) { $restored = json_decode($cookie, true) ?: []; if (is_array($restored)) { $items = $restored; session(['cart.items' => $items]); } }
        }
        $count = array_sum(array_map(fn($it)=> (int)($it['qty'] ?? 0), $items));
        return response()->json(['count'=>$count])->header('Cache-Control','no-store, no-cache, must-revalidate')->header('Pragma','no-cache')->header('Vary','Cookie');
    }

    public function mini()
    {
        $items = session('cart.items', []);
        if (empty($items)) {
            $cookie = request()->cookie('wow_cart');
            if ($cookie) { $restored = json_decode($cookie, true) ?: []; if (is_array($restored)) { session(['cart.items' => $restored]); } }
        }
        return response(view('partials.mini_cart')->render(), 200)
            ->header('Content-Type','text/html')
            ->header('Cache-Control','no-store, no-cache, must-revalidate')
            ->header('Pragma','no-cache')
            ->header('Vary','Cookie');
    }

    public function add(Request $request)
    {
        $id = (int) $request->input('id'); $qty = max(1, (int)$request->input('qty', 1));
        if ($id <= 0) return response()->json(['ok'=>false,'error'=>'invalid'], 400);
        $p = 
Product::query()->withMin('variants','price')->find($id);
        if (!$p) return response()->json(['ok'=>false,'error'=>'not_found'], 404);
        $price = $p->variants_min_price ?? $p->price ?? 0; $price = (float)$price;
        $slug = \Illuminate\Support\Str::slug($p->title ?: (string)$p->id);
        $t = strtolower((string)($p->product_type ?? '')); $tags = strtolower((string)($p->tags_list ?? '')); $seg='therapies';
        if (str_contains($t,'workshop')) $seg='workshops'; elseif (str_contains($t,'event')) $seg='events'; elseif (str_contains($t,'class')) $seg='classes'; elseif (str_contains($t,'retreat')) $seg='retreats'; elseif (str_contains($t,'gift')||str_contains($tags,'gift')) $seg='gifts';
        $url = url('/'.$seg.'/'.$p->id.'-'.$slug);
        $image = method_exists($p,'getFirstImageUrl') ? $p->getFirstImageUrl() : null;
        $items = session('cart.items', []);
        $key = (string)$id;
        if(isset($items[$key])){ $items[$key]['qty'] = (int)($items[$key]['qty'] ?? 1) + $qty; }
        else { $items[$key] = ['id'=>$id, 'title'=>$p->title, 'price'=>$price, 'qty'=>$qty, 'image'=>$image, 'url'=>$url]; }
        session(['cart.items' => $items]);
        $cookie = cookie('wow_cart', json_encode($items), 60*24*30);
        $count = array_sum(array_map(fn($it)=> (int)($it['qty'] ?? 0), $items));
        return response()->json(['ok'=>true,'count'=>$count])->withCookie($cookie);
    }

    public function remove(Request $request)
    {
        $id = (string) $request->input('id'); $items = session('cart.items', []); unset($items[$id]); session(['cart.items'=>$items]);
        $cookie = cookie('wow_cart', json_encode($items), 60*24*30);
        return response()->json(['ok'=>true])->withCookie($cookie);
    }

    public function update(Request $request)
    {
        $id = (string)$request->input('id'); $qty = max(0,(int)$request->input('qty',1));
        $items = session('cart.items', []); if(!isset($items[$id])) return response()->json(['ok'=>false],404);
        if($qty===0){ unset($items[$id]); } else { $items[$id]['qty']=$qty; }
        session(['cart.items'=>$items]);
        $cookie = cookie('wow_cart', json_encode($items), 60*24*30);
        return response()->json(['ok'=>true])->withCookie($cookie);
    }
}

