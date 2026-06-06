<?php

namespace App\Http\Controllers;

use App\Models\OfferingV3;
use App\Models\Booking;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Reservation;
use App\Models\VendorAvailability;
use App\Models\VendorDetail;
use App\Models\VendorTier;
use App\Services\AvailabilityWindowService;
use App\Support\EventListing;
use App\Support\ProductRanking;
use App\Support\ProductSearchFilters;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    private array $nextAvailabilityCache = [];

    public function index(Request $request)
    {
        $format = strtolower((string) $request->query('format', ''));
        if ($format === 'online') {
            return redirect('/classes?mode=online&anytime=1', 302);
        }

        if ($request->has('category')) {
            $raw = trim((string) $request->query('category'));
            if ($raw !== '') {
                $slug = Str::slug($raw);
                $type = strtolower((string) $request->query('type', 'therapies'));
                $allowed = ['therapies','events','workshops','classes','retreats','gifts'];
                if (!in_array($type, $allowed, true)) { $type = 'therapies'; }
                return redirect('/'.$type.'/'.$slug, 302);
            }
        }

        if ($format && !$request->has('mode')) {
            $qs = $request->query();
            $qs['mode'] = $format;
            unset($qs['format']);
            $to = url('/search') . '?' . http_build_query($qs);
            return redirect($to, 302);
        }

        // Server-rendered results to support Blade product card + map
        $base = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withMin('variants', 'price')
            ->withMax('variants', 'price')
            ->with(['media', 'options.values', 'category', 'status', 'vendor.locations', 'vendor.tiers', 'vendor.user.settings']);

        $query = clone $base;
        // Only published products
        $query->where(function($q){
            $q->whereHas('status', function($qs){ $qs->whereIn('status', ['live','approved']); });
        });

        $what = $request->string('what')->toString();
        if ($what) {
            $pattern = "%{$what}%";
            $query->where(function ($q) use ($pattern) {
                $q->where('title', 'like', $pattern)
                  ->orWhereHas('vendor', function ($vq) use ($pattern) {
                      $vq->where('vendor_name', 'like', $pattern);
                  })
                  ->orWhere('summary', 'like', $pattern)
                  ->orWhere('body_html', 'like', $pattern)
                  ->orWhere('what_to_expect', 'like', $pattern)
                  ->orWhere('included', 'like', $pattern)
                  ->orWhere('tags_list', 'like', $pattern);
            });
        }

        // Type filter
        $type = $request->string('type')->toString();
        if ($type) {
            $this->applyTypeFilter($query, $type);
        }

        // Tag filter
        if ($tag = $request->string('tag')->toString()) {
            $lc = strtolower($tag);
            if (in_array($lc, ['gift','gifts'], true)) {
                $query->where(function($q){
                    $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%voucher%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%card%'")
                      ->orWhereRaw("LOWER(COALESCE(tags_list,'')) like '%present%'");
                });
            } else {
                $safe = str_replace(['%','_'], ['\\%','\\_'], $lc);
                $query->whereRaw("LOWER(COALESCE(tags_list,'')) like ?", ['%'.$safe.'%']);
            }
        }

        // Mode (online/in-person)
        $modeInput = $request->filled('mode') ? $request->string('mode')->toString() : $request->string('format')->toString();
        if ($modeInput) {
            $mode = strtolower($modeInput);
            $query->whereHas('options', function ($q) use ($mode) {
                $q->where('meta_name', 'locations')
                  ->whereHas('values', function ($q2) use ($mode) {
                      if ($mode === 'online') { $q2->whereRaw('LOWER(value) = ?', ['online']); }
                      else { $q2->whereRaw('LOWER(value) != ?', ['online']); }
                  });
            });
        }

        ProductSearchFilters::applyWhereFilter($query, $request->string('where')->toString());

        $adults = $request->has('adults') ? (int) $request->input('adults') : null;
        $groupType = $request->has('group_type') ? $request->string('group_type')->toString() : null;
        ProductSearchFilters::applyWhoFilter($query, $adults, $groupType);

        if ($request->filled('price_max')) {
            $pm = (float) $request->input('price_max');
            $query->where(function($q) use ($pm) {
                $q->where(function($qp) use ($pm){ $qp->where('price', '<', 1000)->where('price', '<=', $pm); })
                  ->orWhere(function($qp) use ($pm){ $qp->where('price', '>=', 1000)->where('price', '<=', $pm * 100); })
                  ->orWhereHas('variants', function($qv) use ($pm){
                      $qv->where(function($qq) use ($pm){ $qq->where('price', '<', 1000)->where('price', '<=', $pm); })
                         ->orWhere(function($qq) use ($pm){ $qq->where('price', '>=', 1000)->where('price', '<=', $pm * 100); });
                  });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int)$request->input('category_id'));
        } elseif ($request->filled('category')) {
            $cat = strtolower((string) $request->input('category'));
            $like = '%'.$cat.'%';
            $ids = ProductCategory::query()
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->pluck('id')->all();
            if (!empty($ids)) $query->whereIn('category_id', $ids);
        }

        if ($request->boolean('anytime')) {
            $query->whereRaw("(JSON_EXTRACT(meta_json, '$.date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.date')) = '')")
                  ->whereRaw("(JSON_EXTRACT(meta_json, '$.start_date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.start_date')) = '')")
                  ->whereRaw("(JSON_EXTRACT(meta_json, '$.end_date') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(meta_json, '$.end_date')) = '')");
        }

        $sort = $request->string('sort', 'popular')->toString();

        $perPage = (int) $request->integer('per_page', 48);
        $perPage = min(96, max(12, $perPage));

        $availabilityRange = $this->requestedAvailabilityRange($request);
        $productItems = $query->get();
        if ($availabilityRange) {
            $productItems = $productItems
                ->filter(fn (Product $product) => $this->hasAvailabilityInRange($product, $availabilityRange))
                ->values();
        }
        $productItems = $productItems
            ->reject(fn (Product $product) => EventListing::isPast($product) && ! $this->isEventLikeProduct($product))
            ->filter(fn (Product $product) => method_exists($product, 'hasDisplayableImage') ? $product->hasDisplayableImage() : true)
            ->values();
        $productItems = $productItems->map(fn (Product $product) => $this->decorateSearchProduct($product));

        $offeringItems = $this->buildV3SearchItems($request, $what, $type, $tag, $modeInput, $priceMax = $request->filled('price_max') ? (float) $request->input('price_max') : null);
        if ($availabilityRange) {
            $offeringItems = $offeringItems
                ->filter(fn (OfferingV3 $offering) => $this->hasAvailabilityInRange($offering, $availabilityRange))
                ->values();
        }
        if ($groupType) {
            $offeringItems = $offeringItems
                ->filter(fn (OfferingV3 $offering) => $this->offeringMatchesGroupType($offering, $groupType))
                ->values();
        }
        $offeringItems = $offeringItems
            ->reject(fn (OfferingV3 $offering) => EventListing::isPast($offering) && ! $this->isEventLikeOffering($offering))
            ->filter(fn (OfferingV3 $offering) => method_exists($offering, 'hasDisplayableImage') ? $offering->hasDisplayableImage() : true)
            ->values();
        $offeringItems = $offeringItems->map(fn (OfferingV3 $offering) => $this->decorateSearchOffering($offering));

        $items = $productItems->concat($offeringItems)->values();
        $items = $this->sortSearchItems($items, $sort);

        $total = $items->count();
        $page = max(1, (int) $request->integer('page', 1));
        $lastPage = max(1, (int) ceil($total / max(1, $perPage)));
        $page = min($page, $lastPage);
        $products = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        $seoWhat = Str::squish($what);
        $seoWhere = Str::squish((string) $request->string('where')->toString());
        $seoTitleBase = 'Search all Offerings';
        if ($seoWhat !== '' && $seoWhere !== '') {
            $seoTitleBase = 'Search ' . $seoWhat . ' in ' . $seoWhere;
        } elseif ($seoWhat !== '') {
            $seoTitleBase = 'Search ' . $seoWhat;
        } elseif ($seoWhere !== '') {
            $seoTitleBase = 'Search in ' . $seoWhere;
        }

        $seoDescription = $seoWhat !== ''
            ? 'Search ' . $seoWhat . ' and browse live therapies, classes, events and workshops on We Offer Wellness.'
            : 'Search all offerings and browse live therapies, classes, events and workshops on We Offer Wellness.';

        $gridHtml = view('search.partials.results_cards', ['products' => $products])->render();
        $paginationHtml = ($products instanceof LengthAwarePaginator && $products->total() > 0)
            ? $products->withQueryString()->onEachSide(1)->links('pagination::bootstrap-4')->render()
            : '';

        if ($request->expectsJson()) {
            return response()->json([
                'count' => $products->total(),
                'count_text' => $products->total() . ' results',
                'grid_html' => $gridHtml,
                'pagination_html' => $paginationHtml,
            ]);
        }

        return view('search.index', [
            'mapsKey' => env('GOOGLE_MAPS_API_KEY'),
            'products' => $products,
            'resultCount' => $products->total(),
            'perPage' => $perPage,
            'searchGridHtml' => $gridHtml,
            'searchPaginationHtml' => $paginationHtml,
            'seo' => [
                'title' => $seoTitleBase . ' | We Offer Wellness®',
                'description' => $seoDescription,
                'canonical' => url()->full(),
                'og_type' => 'website',
            ],
        ]);
    }

    private function buildV3SearchItems(
        Request $request,
        string $what,
        ?string $type,
        ?string $tag,
        ?string $modeInput,
        ?float $priceMax
    ): Collection {
        $query = OfferingV3::query()
            ->with(['category', 'type', 'vendor.user.settings', 'vendor.tiers', 'media', 'coverMedia'])
            ->whereIn('status', ['live', 'approved']);

        if ($what !== '') {
            $pattern = "%{$what}%";
            $query->where(function ($q) use ($pattern) {
                $q->where('title', 'like', $pattern)
                  ->orWhereHas('vendor', function ($vq) use ($pattern) {
                      $vq->where('vendor_name', 'like', $pattern);
                  })
                  ->orWhere('summary', 'like', $pattern)
                  ->orWhereExists(function ($dq) use ($pattern) {
                      $dq->selectRaw('1')
                         ->from('offering_details')
                         ->whereColumn('offering_details.offering_id', 'offerings.id')
                         ->where(function ($inner) use ($pattern) {
                             $inner->where('description', 'like', $pattern)
                                   ->orWhere('what_to_expect', 'like', $pattern)
                                   ->orWhere('whats_included', 'like', $pattern);
                         });
                  });
            });
        }

        if ($type) {
            $this->applyV3TypeFilter($query, $type);
        }

        if ($tag = $request->string('tag')->toString()) {
            $lc = strtolower($tag);
            if (in_array($lc, ['gift','gifts'], true)) {
                $query->where(function($q){
                    $q->whereRaw("LOWER(COALESCE(title,'')) like '%gift%'")
                      ->orWhereRaw("LOWER(COALESCE(summary,'')) like '%gift%'")
                      ->orWhereHas('category', function ($cq) {
                          $cq->whereRaw("LOWER(COALESCE(name,'')) like '%gift%'");
                      })
                      ->orWhereHas('type', function ($tq) {
                          $tq->whereRaw("LOWER(COALESCE(name,'')) like '%gift%'");
                      });
                });
            } else {
                $safe = str_replace(['%','_'], ['\\%','\\_'], $lc);
                $query->where(function($q) use ($safe) {
                    $q->whereRaw("LOWER(COALESCE(title,'')) like ?", ['%'.$safe.'%'])
                      ->orWhereRaw("LOWER(COALESCE(summary,'')) like ?", ['%'.$safe.'%'])
                      ->orWhereHas('category', function ($cq) use ($safe) {
                          $cq->whereRaw("LOWER(COALESCE(name,'')) like ?", ['%'.$safe.'%']);
                      })
                      ->orWhereHas('type', function ($tq) use ($safe) {
                          $tq->whereRaw("LOWER(COALESCE(name,'')) like ?", ['%'.$safe.'%']);
                      });
                });
            }
        }

        if ($priceMax !== null) {
            $query->where('price', '<=', $priceMax);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        } elseif ($request->filled('category')) {
            $cat = strtolower(trim((string) $request->input('category')));
            if ($cat !== '') {
                $like = '%'.$cat.'%';
                $ids = ProductCategory::query()
                    ->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->pluck('id')
                    ->all();
                if (!empty($ids)) {
                    $query->whereIn('category_id', $ids);
                }
            }
        }

        return $query->get();
    }

    private function requestedAvailabilityRange(Request $request): ?array
    {
        $start = $this->parseSearchDate($request->string('when_start')->toString());
        $end = $this->parseSearchDate($request->string('when_end')->toString());

        if (! $start && $request->filled('when')) {
            $when = trim($request->string('when')->toString());
            $parts = preg_split('/\s+-\s+|\s+—\s+/', $when) ?: [];
            $start = $this->parseSearchDate($parts[0] ?? $when);
            $end = $this->parseSearchDate($parts[1] ?? '');
        }

        if (! $start) {
            return null;
        }

        if (! $end) {
            $end = $start->copy();
        }

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    private function parseSearchDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function hasAvailabilityInRange(Product|OfferingV3 $item, array $range): bool
    {
        $vendor = $item->vendor;
        $user = $vendor?->user;
        if (! $user) {
            return false;
        }

        $settings = AvailabilityWindowService::extractAvailabilitySettings($user);
        $timezone = (string) ($settings['timezone'] ?? config('app.timezone', 'Europe/London'));

        try {
            $rangeStart = Carbon::parse($range['start'], $timezone)->startOfDay();
            $rangeEnd = Carbon::parse($range['end'], $timezone)->startOfDay();
        } catch (\Throwable $e) {
            return false;
        }

        if ($rangeEnd->lt($rangeStart)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
        }

        $bookingHorizon = max(1, min(365, (int) ($settings['bookingHorizon'] ?? 30)));
        $horizonEnd = Carbon::now($timezone)->startOfDay()->addDays($bookingHorizon)->endOfDay();
        if ($rangeStart->gt($horizonEnd)) {
            return false;
        }
        if ($rangeEnd->gt($horizonEnd)) {
            $rangeEnd = $horizonEnd->copy()->startOfDay();
        }

        $weeklyWindows = AvailabilityWindowService::buildWeeklyWindows($user);
        $specificRecords = VendorAvailability::where('user_id', $user->id)
            ->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->orderBy('date')
            ->get();
        $specificWindows = AvailabilityWindowService::buildSpecificWindows($specificRecords);

        $holdCutoff = Carbon::now($timezone)->subMinutes(10);
        $reservationRecords = Reservation::where('user_id', $user->id)
            ->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->where(function ($query) use ($holdCutoff) {
                $query->where('is_confirmed', true)
                    ->orWhere('created_at', '>=', $holdCutoff);
            })
            ->orderBy('date')
            ->get();

        $bookingRecords = Booking::where('user_id', $user->id)
            ->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->orderBy('date')
            ->get();

        $duration = $this->availabilityDurationMinutes($item, $settings);
        $days = max(1, (int) $rangeStart->diffInDays($rangeEnd) + 1);
        $slotsByDay = AvailabilityWindowService::generateSlots(
            $weeklyWindows,
            $settings,
            $duration,
            $days,
            $rangeStart,
            $specificWindows,
            $reservationRecords->concat($bookingRecords)->all()
        );

        foreach ($slotsByDay as $day) {
            if (! empty($day['slots']) && is_array($day['slots'])) {
                return true;
            }
        }

        return false;
    }

    private function availabilityDurationMinutes(Product|OfferingV3 $item, array $settings): int
    {
        if ($item instanceof OfferingV3) {
            $schedule = DB::table('offering_schedule')->where('offering_id', $item->id)->first();
            $duration = (int) ($schedule->duration_minutes ?? 0);
            if ($duration > 0) {
                return max(15, $duration);
            }
        }

        if ($item instanceof Product) {
            $meta = is_array($item->meta_json ?? null) ? $item->meta_json : [];
            foreach (['duration_minutes', 'duration_mins', 'duration'] as $key) {
                $duration = $this->parseDurationMinutes($meta[$key] ?? null);
                if ($duration > 0) {
                    return max(15, $duration);
                }
            }
        }

        return max(15, (int) ($settings['slotInterval'] ?? 60) ?: 60);
    }

    private function parseDurationMinutes(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $text = strtolower(trim((string) $value));
        if ($text === '') {
            return 0;
        }

        if (preg_match('/(\d+)\s*(hour|hr|hrs|hours)/i', $text, $match)) {
            return (int) $match[1] * 60;
        }

        if (preg_match('/(\d+)\s*(minute|min|mins|minutes)/i', $text, $match)) {
            return (int) $match[1];
        }

        if (preg_match('/\b(\d+)\b/', $text, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    private function sortSearchItems(Collection $items, string $sort): Collection
    {
        $sort = strtolower(trim($sort));

        if (in_array($sort, ['', 'popular', 'relevance'], true)) {
            return $items->sort(function ($left, $right) {
                $leftNext = $this->nextAvailabilitySortValue($left);
                $rightNext = $this->nextAvailabilitySortValue($right);
                if ($leftNext !== $rightNext) {
                    return $leftNext <=> $rightNext;
                }

                foreach ([
                    [ProductRanking::itemKindPriority($left), ProductRanking::itemKindPriority($right)],
                    [ProductRanking::planPriority($left), ProductRanking::planPriority($right)],
                    [ProductRanking::ratingValue($left), ProductRanking::ratingValue($right)],
                    [ProductRanking::reviewCountValue($left), ProductRanking::reviewCountValue($right)],
                ] as [$leftValue, $rightValue]) {
                    if ($leftValue === $rightValue) {
                        continue;
                    }

                    return $rightValue <=> $leftValue;
                }

                return strcasecmp(ProductRanking::titleValue($left), ProductRanking::titleValue($right));
            })->values();
        }

        return ProductRanking::sortCollection($items, $sort);
    }

    private function applyV3TypeFilter($query, ?string $type): void
    {
        if (!$type) return;
        $lc = strtolower(trim($type));

        if (in_array($lc, ['events', 'event'], true)) {
            $query->where(function ($q) {
                $q->whereHas('type', function ($tq) {
                    $tq->whereRaw("LOWER(COALESCE(name,'')) like '%event%'")
                       ->orWhereRaw("LOWER(COALESCE(name,'')) like '%workshop%'");
                })->orWhereHas('category', function ($cq) {
                    $cq->whereRaw("LOWER(COALESCE(name,'')) like '%event%'")
                       ->orWhereRaw("LOWER(COALESCE(name,'')) like '%workshop%'");
                });
            });
        } elseif (in_array($lc, ['workshops', 'workshop'], true)) {
            $query->whereHas('type', function ($tq) {
                $tq->whereRaw("LOWER(COALESCE(name,'')) like '%workshop%'");
            });
        } elseif (in_array($lc, ['classes', 'class'], true)) {
            $query->whereHas('type', function ($tq) {
                $tq->whereRaw("LOWER(COALESCE(name,'')) like '%class%'");
            });
        } elseif (in_array($lc, ['retreats', 'retreat'], true)) {
            $query->where(function ($q) {
                $q->whereHas('type', function ($tq) {
                    $tq->whereRaw("LOWER(COALESCE(name,'')) like '%retreat%'");
                })->orWhereHas('category', function ($cq) {
                    $cq->whereRaw("LOWER(COALESCE(name,'')) like '%retreat%'");
                });
            });
        } elseif (in_array($lc, ['gifts', 'gift'], true)) {
            $query->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(title,'')) like '%gift%'")
                  ->orWhereRaw("LOWER(COALESCE(summary,'')) like '%gift%'")
                  ->orWhereHas('type', function ($tq) {
                      $tq->whereRaw("LOWER(COALESCE(name,'')) like '%gift%'");
                  })
                  ->orWhereHas('category', function ($cq) {
                      $cq->whereRaw("LOWER(COALESCE(name,'')) like '%gift%'");
                  });
            });
        }
    }

    private function offeringMatchesGroupType(OfferingV3 $offering, ?string $groupType): bool
    {
        $groupType = strtolower(trim((string) $groupType));
        if (! in_array($groupType, ['solo', 'couple', 'group'], true)) {
            return true;
        }

        $rows = DB::table('offering_price_options')
            ->where('offering_id', $offering->id)
            ->select(['audience_type', 'pricing_type'])
            ->get();

        if ($rows->isNotEmpty()) {
            foreach ($rows as $row) {
                $audience = strtolower(trim((string) ($row->audience_type ?? '')));
                $pricing = strtolower(trim((string) ($row->pricing_type ?? '')));

                if ($groupType === 'solo' && ($audience === 'solo' || str_contains($pricing, 'solo'))) {
                    return true;
                }

                if ($groupType === 'couple' && ($audience === 'couple' || str_contains($pricing, 'couple'))) {
                    return true;
                }

                if ($groupType === 'group' && ($audience === 'group' || str_contains($pricing, 'group'))) {
                    return true;
                }
            }
        }

        $haystack = strtolower(trim(implode(' ', array_filter([
            (string) ($offering->title ?? ''),
            (string) ($offering->summary ?? ''),
            (string) ($offering->type?->name ?? ''),
            (string) ($offering->category?->name ?? ''),
        ]))));

        if ($groupType === 'solo') {
            return str_contains($haystack, 'solo') || str_contains($haystack, '1 person') || str_contains($haystack, '1-to-1') || str_contains($haystack, '1:1');
        }

        if ($groupType === 'couple') {
            return str_contains($haystack, 'couple') || str_contains($haystack, '2 person') || str_contains($haystack, 'pair') || str_contains($haystack, 'duo');
        }

        return str_contains($haystack, 'group') || str_contains($haystack, 'workshop') || str_contains($haystack, 'class');
    }

    private function offeringMode(OfferingV3 $offering): ?string
    {
        $locations = $offering->getLocations();
        $hasOnline = in_array('Online', $locations, true);
        $physical = array_values(array_filter($locations, fn ($location) => $location !== 'Online'));

        if ($hasOnline && count($physical) === 0) {
            return 'online';
        }

        if (count($physical) > 0 && !$hasOnline) {
            return 'in-person';
        }

        if ($hasOnline && count($physical) > 0) {
            return 'mixed';
        }

        return null;
    }

    private function offeringUrl(OfferingV3 $offering): string
    {
        $slug = Str::slug($offering->title ?: (string) $offering->id);

        return url('/offerings/' . $offering->id . '-' . $slug);
    }

    private function applyTypeFilter($query, ?string $type): void
    {
        if (!$type) return;
        $lc = strtolower(trim($type));
        if ($lc === 'events' || $lc === 'event') {
            $query->where(function($q){
                $q->whereRaw("LOWER(product_type) like '%event%'")
                  ->orWhereRaw("LOWER(product_type) like '%workshop%'")
                  ->orWhereNotNull('meta_json->date')
                  ->orWhereNotNull('meta_json->start_date');
            });
        } elseif ($lc === 'workshops' || $lc === 'workshop') {
            $query->whereRaw("LOWER(product_type) like '%workshop%'");
        } elseif ($lc === 'classes' || $lc === 'class') {
            $query->whereRaw("LOWER(product_type) like '%class%'");
        } elseif (in_array($lc, ['therapies','therapy','experience','experiences'], true)) {
            $query->where(function($q){
                $q->whereRaw("LOWER(COALESCE(product_type,'')) not like '%event%'")
                  ->whereRaw("LOWER(COALESCE(product_type,'')) not like '%workshop%'")
                  ->whereRaw("LOWER(COALESCE(product_type,'')) not like '%class%'");
            });
        } elseif ($lc === 'retreats' || $lc === 'retreat') {
            $query->whereRaw("LOWER(product_type) like '%retreat%'");
        } elseif ($lc === 'gifts' || $lc === 'gift') {
            $query->where(function($q){
                $q->whereRaw("LOWER(COALESCE(tags_list,'')) like '%gift%'")
                  ->orWhereRaw("LOWER(COALESCE(product_type,'')) like '%gift%'");
            });
        } else {
            $safe = str_replace(['%','_'], ['\\%','\\_'], $lc);
            $query->whereRaw("LOWER(COALESCE(product_type,'')) like ?", ['%'.$safe.'%']);
        }
    }

    private function decorateSearchProduct(Product $product): Product
    {
        $locations = method_exists($product, 'getLocations') ? $product->getLocations() : [];
        $isOnline = in_array('Online', $locations, true);
        $physical = array_values(array_filter($locations, fn ($location) => $location !== 'Online'));
        $vendorPlan = $this->resolveVendorPlan($product->vendor);
        $meta = $product->meta_json ?? [];
        $slug = Str::slug($product->title ?: (string) $product->id);

        $product->setAttribute('type', $product->product_type ?: 'experience');
        $product->setAttribute('category', $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null);
        $product->setAttribute('mode', $isOnline && count($physical) === 0 ? 'Online' : (count($physical) ? 'In-person' : null));
        $product->setAttribute('location', $physical[0] ?? ($isOnline ? 'Online' : null));
        $product->setAttribute('locations', $locations);
        $product->setAttribute('price', $product->price ?? null);
        $product->setAttribute('compare_at_price', $meta['compare_at_price'] ?? null);
        $product->setAttribute('currency', $meta['currency'] ?? 'GBP');
        $product->setAttribute('rating', round((float) ($product->reviews_avg_rating ?? 0), 1) ?: null);
        $product->setAttribute('review_count', (int) ($product->reviews_count ?? 0));
        $product->setAttribute('image', method_exists($product, 'getFirstImageUrl') ? $product->getFirstImageUrl() : null);
        $product->setAttribute('tags', $product->tags_list ? array_map('trim', explode(',', $product->tags_list)) : []);
        $product->setAttribute('url', url('/offerings/' . $product->id . '-' . $slug));
        $product->setAttribute('vendor_name', $product->vendor?->vendor_name ?? null);
        $product->setAttribute('source_version', 'legacy');
        $product->setAttribute('plan_key', $vendorPlan['key']);
        $product->setAttribute('plan_label', $vendorPlan['label']);
        $product->setAttribute('plan_priority', $vendorPlan['priority']);
        $nextAvailableAt = $this->nextAvailableAt($product);
        $product->setAttribute('next_available_at', $nextAvailableAt?->toIso8601String());
        $product->setAttribute('next_available_timestamp', $nextAvailableAt?->timestamp);
        $isEventLike = EventListing::isEventLike($product);
        $product->setAttribute('is_event_like', $isEventLike);
        $product->setAttribute('is_past_event', $isEventLike && EventListing::isPast($product));

        return $product;
    }

    private function typeSegment(Product $product): string
    {
        $type = strtolower((string) ($product->product_type ?? ''));
        if (str_contains($type, 'workshop')) {
            return 'workshops';
        }
        if (str_contains($type, 'event')) {
            return 'events';
        }
        if (str_contains($type, 'class')) {
            return 'classes';
        }
        if (str_contains($type, 'retreat')) {
            return 'retreats';
        }
        if (str_contains($type, 'gift')) {
            return 'gifts';
        }

        return 'therapies';
    }

    private function decorateSearchOffering(OfferingV3 $offering): OfferingV3
    {
        $vendorPlan = $this->resolveVendorPlan($offering->vendor);
        $vendorReviewSummary = $offering->vendor?->review_summary ?? ['count' => 0, 'rating' => null];
        $isEventLike = EventListing::isEventLike($offering);

        $offering->setAttribute('product_type', (string) ($offering->type?->name ?? $offering->category?->name ?? 'experience'));
        $offering->setAttribute('tags_list', trim(implode(',', array_filter([
            (string) ($offering->type?->name ?? ''),
            (string) ($offering->category?->name ?? ''),
            (string) ($offering->summary ?? ''),
        ]))));
        $offering->setAttribute('vendor_name', $offering->vendor?->vendor_name ?? null);
        $offering->setAttribute('source_version', 'v3');
        $offering->setAttribute('variants_min_price', $offering->price);
        $offering->setAttribute('reviews_avg_rating', null);
        $offering->setAttribute('reviews_count', 0);
        $offering->setAttribute('url', $this->offeringUrl($offering));
        $offering->setAttribute('image', $offering->getFirstImageUrl());
        $offering->setAttribute('locations', $offering->getLocations());
        $offering->setAttribute('mode', $this->offeringMode($offering));
        $offering->setAttribute('rating', isset($vendorReviewSummary['rating']) ? round((float) $vendorReviewSummary['rating'], 1) : null);
        $offering->setAttribute('review_count', (int) ($vendorReviewSummary['count'] ?? 0));
        $offering->setAttribute('vendor_rating', isset($vendorReviewSummary['rating']) ? round((float) $vendorReviewSummary['rating'], 1) : null);
        $offering->setAttribute('vendor_review_count', (int) ($vendorReviewSummary['count'] ?? 0));
        $offering->setAttribute('plan_key', $vendorPlan['key']);
        $offering->setAttribute('plan_label', $vendorPlan['label']);
        $offering->setAttribute('plan_priority', $vendorPlan['priority']);
        $nextAvailableAt = $this->nextAvailableAt($offering);
        $offering->setAttribute('next_available_at', $nextAvailableAt?->toIso8601String());
        $offering->setAttribute('next_available_timestamp', $nextAvailableAt?->timestamp);
        $offering->setAttribute('is_event_like', $isEventLike);
        $offering->setAttribute('is_past_event', $isEventLike && EventListing::isPast($offering));

        return $offering;
    }

    private function isEventLikeProduct(Product $product): bool
    {
        return EventListing::isEventLike($product);
    }

    private function isEventLikeOffering(OfferingV3 $offering): bool
    {
        return EventListing::isEventLike($offering);
    }

    private function nextAvailabilitySortValue(mixed $item): int
    {
        $timestamp = data_get($item, 'next_available_timestamp');
        if (is_numeric($timestamp) && (int) $timestamp > 0) {
            return (int) $timestamp;
        }

        $value = data_get($item, 'next_available_at');
        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value)->timestamp;
            } catch (\Throwable $e) {
                return PHP_INT_MAX;
            }
        }

        return PHP_INT_MAX;
    }

    private function nextAvailableAt(Product|OfferingV3 $item): ?Carbon
    {
        $vendor = $item->vendor;
        $user = $vendor?->user;
        if (! $user) {
            return null;
        }

        $settings = AvailabilityWindowService::extractAvailabilitySettings($user);
        $timezone = (string) ($settings['timezone'] ?? config('app.timezone', 'Europe/London'));
        $duration = $this->availabilityDurationMinutes($item, $settings);
        $bookingHorizon = max(1, min(365, (int) ($settings['bookingHorizon'] ?? 30)));
        $cacheKey = implode(':', [
            (string) $user->id,
            (string) $duration,
            (string) $bookingHorizon,
            $timezone,
        ]);

        if (array_key_exists($cacheKey, $this->nextAvailabilityCache)) {
            return $this->nextAvailabilityCache[$cacheKey];
        }

        try {
            $anchor = Carbon::now($timezone)->startOfDay();
            $rangeEnd = $anchor->copy()->addDays($bookingHorizon)->endOfDay();
            $weeklyWindows = AvailabilityWindowService::buildWeeklyWindows($user);
            $specificRecords = VendorAvailability::where('user_id', $user->id)
                ->whereBetween('date', [$anchor->toDateString(), $rangeEnd->toDateString()])
                ->orderBy('date')
                ->get();
            $specificWindows = AvailabilityWindowService::buildSpecificWindows($specificRecords);

            $holdCutoff = Carbon::now($timezone)->subMinutes(10);
            $reservationRecords = Reservation::where('user_id', $user->id)
                ->whereBetween('date', [$anchor->toDateString(), $rangeEnd->toDateString()])
                ->where(function ($query) use ($holdCutoff) {
                    $query->where('is_confirmed', true)
                        ->orWhere('created_at', '>=', $holdCutoff);
                })
                ->orderBy('date')
                ->get();

            $bookingRecords = Booking::where('user_id', $user->id)
                ->whereBetween('date', [$anchor->toDateString(), $rangeEnd->toDateString()])
                ->orderBy('date')
                ->get();

            $slotsByDay = AvailabilityWindowService::generateSlots(
                $weeklyWindows,
                $settings,
                $duration,
                $bookingHorizon,
                $anchor,
                $specificWindows,
                $reservationRecords->concat($bookingRecords)->all()
            );

            foreach ($slotsByDay as $day) {
                $firstSlot = $day['slots'][0]['iso'] ?? null;
                if (! $firstSlot) {
                    continue;
                }

                $next = Carbon::parse($firstSlot, $timezone);
                $this->nextAvailabilityCache[$cacheKey] = $next;

                return $next;
            }
        } catch (\Throwable $e) {
            // Fall through and cache the miss so repeated items don't keep recalculating.
        }

        $this->nextAvailabilityCache[$cacheKey] = null;

        return null;
    }

    private function resolveVendorPlan(?VendorDetail $vendor): array
    {
        $tierValue = null;

        try {
            $tier = null;
            if ($vendor) {
                if ($vendor->relationLoaded('tiers')) {
                    $tier = $vendor->tiers
                        ->sortByDesc(fn ($row) => $row->plan_started_at ?? $row->id ?? 0)
                        ->first();
                } else {
                    $tier = $vendor->tiers()->orderByDesc('plan_started_at')->orderByDesc('id')->first();
                }
            }
            $tierValue = (string) ($tier?->tier ?? '');
        } catch (\Throwable $e) {
            $tierValue = '';
        }

        $key = $this->canonicalPlanKey($tierValue);
        $isStarter = $key === 'starter' || $key === '';

        return [
            'key' => $key ?: 'starter',
            'label' => $this->planTitleForKey($key ?: 'starter'),
            'priority' => $isStarter ? 0 : 1,
        ];
    }

    private function canonicalPlanKey(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));
        $normalized = str_replace(['_', ' '], '-', $normalized);

        return match ($normalized) {
            'community', 'starter', 'standard', 'free-starter', 'starter-package' => 'starter',
            'core', 'business-accelerator', 'business-accelerator-package', 'businessaccelerator' => 'business-accelerator',
            'premium', 'premium-accelerator', 'premiumaccelerator' => 'premium-accelerator',
            'become-partner', 'partner' => 'become-partner',
            default => $normalized,
        };
    }

    private function planTitleForKey(?string $value): string
    {
        return match ($this->canonicalPlanKey($value)) {
            'starter' => 'Starter',
            'business-accelerator' => 'Business Accelerator',
            'premium-accelerator' => 'Premium Accelerator',
            'become-partner' => 'Become Partner',
            default => Str::headline(trim((string) $value)) ?: 'Plan',
        };
    }
}
