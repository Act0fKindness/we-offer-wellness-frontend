<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
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
                'variant_label' => $row['variant_label'] ?? null,
                'product_id' => $row['product_id'] ?? null,
                'variant_id' => $row['variant_id'] ?? null,
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
        $id = (int) $request->input('id');
        $variantId = (int) $request->input('variant_id');
        $qty = max(1, (int)$request->input('qty', 1));
        if ($id <= 0) return response()->json(['ok'=>false,'error'=>'invalid'], 400);
        $product = Product::query()->with(['variants'])->withMin('variants','price')->find($id);
        if (!$product) return response()->json(['ok'=>false,'error'=>'not_found'], 404);

        $price = $product->variants_min_price ?? $product->price ?? 0;
        $price = $this->formatPrice($price);
        $variant = null;
        $variantLabel = trim((string)$request->input('variant_label', '')) ?: null;
        $variantOptions = [];
        if ($variantId > 0) {
            $variant = $product->variants?->firstWhere('id', $variantId);
            if (!$variant) {
                $variant = ProductVariant::query()
                    ->where('id', $variantId)
                    ->where('product_id', $product->id)
                    ->first();
            }
        }
        if ($variant) {
            $price = $this->formatPrice($variant->price ?? $price);
            $opts = $variant->options;
            if (is_string($opts)) {
                $decoded = json_decode($opts, true);
                $opts = is_array($decoded) ? array_values($decoded) : [];
            } elseif (!is_array($opts)) {
                $opts = [];
            }
            $variantOptions = $opts;
            if (!$variantLabel) {
                $variantLabel = $this->buildVariantLabel($variant->title ?? null, $variantOptions);
            }
        }

        $slug = \Illuminate\Support\Str::slug($product->title ?: (string)$product->id);
        $t = strtolower((string)($product->product_type ?? '')); $tags = strtolower((string)($product->tags_list ?? '')); $seg='therapies';
        if (str_contains($t,'workshop')) $seg='workshops'; elseif (str_contains($t,'event')) $seg='events'; elseif (str_contains($t,'class')) $seg='classes'; elseif (str_contains($t,'retreat')) $seg='retreats'; elseif (str_contains($t,'gift')||str_contains($tags,'gift')) $seg='gifts';
        $url = url('/'.$seg.'/'.$product->id.'-'.$slug);
        $image = method_exists($product,'getFirstImageUrl') ? $product->getFirstImageUrl() : null;
        $key = $this->cartKey($product->id, $variant?->id);
        if(isset($items[$key])){
            $items[$key]['qty'] = (int)($items[$key]['qty'] ?? 1) + $qty;
            if($variantLabel){ $items[$key]['variant_label'] = $variantLabel; }
        }
        else {
            $items[$key] = [
                'id' => $key,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'variant_label' => $variantLabel,
                'options' => $variantOptions,
                'title' => $product->title,
                'price' => $price,
                'qty' => $qty,
                'image' => $image,
                'url' => $url,
            ];
        }
        session(['cart.items' => $items]);
        $cookie = cookie('wow_cart', json_encode($items), 60*24*30);
        $count = array_sum(array_map(fn($it)=> (int)($it['qty'] ?? 0), $items));
        return response()->json(['ok'=>true,'count'=>$count,'key'=>$key])->withCookie($cookie);
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

        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $key => $row) {
            if (!is_array($row)) {
                continue;
            }
            $line = $this->mapLineItem($row);
            $productId = $line['product_id'] ?? (is_numeric($key) ? (int)$key : null);
            $variantId = $line['variant_id'] ?? null;
            $cartKey = $this->cartKey($productId ?? $key, $variantId);
            $line['id'] = $cartKey;
            $normalized[$cartKey] = $line;
        }
        session(['cart.items' => $normalized]);

        return $normalized;
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
            $productId = $line['product_id'] ?? (is_numeric($key) ? (int)$key : null);
            $cartKey = $this->cartKey($productId ?? $key, $line['variant_id'] ?? null);
            $line['id'] = $cartKey;
            $normalized[$cartKey] = $line;
        }

        return $normalized;
    }

    protected function mapLineItem(array $entry): array
    {
        $price = $entry['price'] ?? $entry['unit'] ?? $entry['unit_amount'] ?? 0;
        $variantId = $entry['variant_id'] ?? null;
        $variantLabel = $entry['variant_label'] ?? $entry['options_label'] ?? null;
        $options = $entry['options'] ?? [];
        if (!is_array($options)) {
            $options = [];
        }
        $rawProductId = $entry['product_id'] ?? $entry['productId'] ?? null;
        if ($rawProductId === null && isset($entry['id'])) {
            $rawProductId = str_starts_with((string)$entry['id'], 'p:') ? substr((string)$entry['id'], 2) : $entry['id'];
        }
        return [
            'product_id' => $rawProductId,
            'variant_id' => $variantId,
            'variant_label' => $variantLabel,
            'options' => $options,
            'title' => $entry['title'] ?? $entry['name'] ?? 'Item',
            'price' => $this->formatPrice($price ?? 0),
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

    protected function buildVariantLabel(?string $title, array $options = []): ?string
    {
        $cleanTitle = trim((string)$title);
        if ($cleanTitle !== '' && stripos($cleanTitle, 'default') === false) {
            return $cleanTitle;
        }
        $values = array_values(array_filter(array_map(function ($value) {
            if (is_string($value)) {
                return trim($value);
            }
            if (is_scalar($value)) {
                return trim((string)$value);
            }
            return '';
        }, $options), fn($v) => $v !== ''));
        if (!empty($values)) {
            return implode(' • ', $values);
        }
        return null;
    }

    protected function cartKey($productId, $variantId = null): string
    {
        if ($variantId) {
            return 'v:' . (string)$variantId;
        }
        if (is_string($productId) && (str_starts_with($productId, 'v:') || str_starts_with($productId, 'p:'))) {
            return $productId;
        }
        return 'p:' . (string)$productId;
    }
}
