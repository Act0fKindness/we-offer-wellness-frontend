<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class CartController extends Controller
{
    public function page(Request $request)
    {
        $this->getCartItems();
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
        $items = $this->listCartItems();
        $count = array_sum(array_map(fn($it)=> (int)($it['qty'] ?? 0), $items));
        return response()->json(['count'=>$count])->header('Cache-Control','no-store, no-cache, must-revalidate')->header('Pragma','no-cache')->header('Vary','Cookie');
    }

    public function mini(Request $request)
    {
        $items = $this->listCartItems();
        $normalized = [];
        $count = 0;
        $total = 0.0;
        foreach ($items as $row) {
            $price = $this->formatPrice($row['price'] ?? 0);
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $normalized[] = [
                'id' => (string) ($row['id'] ?? Str::uuid()->toString()),
                'title' => $row['title'] ?? 'Item',
                'price' => $price,
                'qty' => $qty,
                'image' => $row['image'] ?? null,
                'url' => $row['url'] ?? '#',
            ];
            $count += $qty;
            $total += $price * $qty;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'items' => $normalized,
                'count' => $count,
                'total' => $total,
                'currency' => 'GBP',
            ])->header('Cache-Control','no-store, no-cache, must-revalidate')
              ->header('Pragma','no-cache')
              ->header('Vary','Cookie');
        }

        return response(view('partials.mini_cart', [
            'items' => $normalized,
            'count' => $count,
            'total' => $total,
        ])->render(), 200)
            ->header('Content-Type','text/html')
            ->header('Cache-Control','no-store, no-cache, must-revalidate')
            ->header('Pragma','no-cache')
            ->header('Vary','Cookie');
    }

    public function add(Request $request)
    {
        $items = $this->getCartItems();
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
        $items = $this->getCartItems();
        $id = (string) $request->input('id'); unset($items[$id]); session(['cart.items'=>$items]);
        $cookie = cookie('wow_cart', json_encode($items), 60*24*30);
        return response()->json(['ok'=>true])->withCookie($cookie);
    }

    public function update(Request $request)
    {
        $items = $this->getCartItems();
        $id = (string)$request->input('id'); $qty = max(0,(int)$request->input('qty',1));
        if(!isset($items[$id])) return response()->json(['ok'=>false],404);
        if($qty===0){ unset($items[$id]); } else { $items[$id]['qty']=$qty; }
        session(['cart.items'=>$items]);
        $cookie = cookie('wow_cart', json_encode($items), 60*24*30);
        return response()->json(['ok'=>true])->withCookie($cookie);
    }

    public function clear(Request $request)
    {
        session()->forget('cart.items');
        $cookie = cookie('wow_cart', json_encode([]), 60*24*30);
        return response()->json(['ok' => true])->withCookie($cookie);
    }

    public function gift(Request $request)
    {
        $code = strtoupper(trim((string)$request->input('code', '')));
        if ($code !== '') session(['cart_gift_code' => $code]); else session()->forget('cart_gift_code');
        return response()->json(['ok' => true, 'code' => $code]);
    }

    protected function getCartItems(): array
    {
        $items = session('cart.items', []);
        if (empty($items)) {
            $cookie = request()->cookie('wow_cart');
            if ($cookie) {
                $restored = json_decode($cookie, true) ?: [];
                $items = $this->normalizeLegacyCart($restored);
                session(['cart.items' => $items]);
            }
        }

        if (isset($items['items']) && is_array($items['items'])) {
            $items = $this->normalizeLegacyCart($items);
            session(['cart.items' => $items]);
        }

        return is_array($items) ? $items : [];
    }

    protected function listCartItems(): array
    {
        $store = $this->getCartItems();
        $list = [];
        foreach ($store as $id => $row) {
            if (!is_array($row)) {
                continue;
            }
            $line = $row;
            $line['id'] = $line['id'] ?? $id;
            $list[] = $line;
        }
        return $list;
    }

    protected function normalizeLegacyCart(array $payload): array
    {
        if (isset($payload['items']) && is_array($payload['items'])) {
            $payload = $payload['items'];
        }

        $normalized = [];
        foreach ($payload as $key => $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $line = $this->mapLineItem($entry);
            $id = (string) ($line['id'] ?? $key ?? Str::uuid()->toString());
            $line['id'] = $id;
            $normalized[$id] = $line;
        }

        return $normalized;
    }

    protected function mapLineItem(array $entry): array
    {
        return [
            'id' => $entry['id'] ?? null,
            'title' => $entry['title'] ?? $entry['name'] ?? 'Item',
            'price' => $entry['price'] ?? $entry['unit'] ?? $entry['unit_amount'] ?? 0,
            'qty' => max(1, (int) ($entry['qty'] ?? $entry['quantity'] ?? 1)),
            'image' => $entry['image'] ?? $entry['img'] ?? null,
            'url' => $entry['url'] ?? $entry['href'] ?? '#',
        ];
    }

    protected function formatPrice($value): float
    {
        $price = (float) $value;
        if ($price >= 1000 && fmod($price, 1) === 0.0) {
            return $price / 100;
        }
        return $price;
    }
}
