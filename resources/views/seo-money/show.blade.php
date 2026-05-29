@extends('layouts.app')

@push('head')
  <title>{{ $seo['title'] ?? 'We Offer Wellness™' }}</title>
  @if(!empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
  @if(!empty($seo['robots']))<meta name="robots" content="{{ $seo['robots'] }}">@endif
  @if(!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
  @php
    $pageCanonical = $seo['canonical'] ?? url()->current();
    $breadcrumbLd = [
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $page['h1'] ?? $page['title'] ?? 'Wellness', 'item' => $pageCanonical],
      ],
    ];
    $itemListLd = [
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'itemListElement' => collect($products ?? [])
        ->values()
        ->take(12)
        ->map(function ($product, $index) {
            $slug = \Illuminate\Support\Str::slug((string) ($product->title ?? $product->name ?? $product->id));
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => url('/offerings/' . $product->id . '-' . $slug),
                'name' => (string) ($product->title ?? $product->name ?? 'Offering'),
            ];
        })
        ->values()
        ->all(),
    ];
    $faqLd = [
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'mainEntity' => collect($page['faqs'] ?? [])
        ->map(function (array $faq) {
            return [
                '@type' => 'Question',
                'name' => (string) ($faq['q'] ?? ''),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => (string) ($faq['a'] ?? ''),
                ],
            ];
        })
        ->filter(fn (array $faq) => $faq['name'] !== '' && trim((string) data_get($faq, 'acceptedAnswer.text', '')) !== '')
        ->values()
        ->all(),
    ];
  @endphp
  <script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
  <script type="application/ld+json">{!! json_encode($itemListLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
  <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
  <style>
    .seo-money-page{
      --ink:#101828;
      --muted:#596275;
      --line:#dfe4ea;
      --line-soft:#edf0f2;
      --green:#4f9381;
      --green-dark:#417c6d;
      --green-soft:#e8f5f1;
      --gold-soft:#ffe5b3;
      --gold-text:#6f4b10;
      --blue-soft:#e8f0ff;
      --blue-text:#254a85;
      padding:64px 0 84px;
      background:
        radial-gradient(circle at top left, rgba(79,147,129,.08), transparent 30%),
        radial-gradient(circle at top right, rgba(255,181,73,.08), transparent 24%),
        #fff;
    }
    .seo-money-grid{
      display:grid;
      grid-template-columns:minmax(0,1fr) minmax(320px,.86fr);
      gap:24px;
      align-items:stretch;
    }
    .seo-money-copy,
    .seo-money-panel,
    .seo-money-section{
      background:rgba(255,255,255,.98);
      border:1px solid var(--line);
      border-radius:22px;
      box-shadow:0 18px 54px rgba(16,24,40,.07);
    }
    .seo-money-copy{
      padding:32px;
    }
    .seo-money-kicker{
      margin:0 0 10px;
      color:#344054;
      font-size:13px;
      font-weight:300;
      letter-spacing:.16em;
      text-transform:uppercase;
    }
    .seo-money-copy h1,
    .seo-money-panel h2,
    .seo-money-section h2{
      margin:0;
      color:var(--ink);
      font-family:"Playfair Display", Georgia, serif;
      font-weight:500;
      letter-spacing:-.055em;
    }
    .seo-money-copy h1{
      max-width:11ch;
      font-size:clamp(42px,5.8vw,78px);
      line-height:.94;
    }
    .seo-money-copy p,
    .seo-money-panel p,
    .seo-money-section p{
      color:var(--muted);
      line-height:1.6;
    }
    .seo-money-copy p{
      max-width:68ch;
      margin:16px 0 0;
      font-size:17px;
    }
    .seo-money-points{
      display:grid;
      gap:10px;
      margin-top:24px;
    }
    .seo-money-point{
      display:flex;
      gap:10px;
      align-items:flex-start;
      color:#344054;
      font-size:14px;
      line-height:1.45;
    }
    .seo-money-point::before{
      content:"✓";
      width:22px;
      height:22px;
      flex:0 0 22px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border-radius:999px;
      background:var(--green-soft);
      color:var(--green);
      font-size:12px;
      font-weight:800;
      margin-top:1px;
    }
    .seo-money-actions{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      margin-top:26px;
    }
    .seo-money-panel{
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      gap:18px;
      padding:26px;
      background:linear-gradient(180deg, rgba(232,245,241,.72), rgba(255,255,255,.96));
    }
    .seo-money-panel h2{
      font-size:clamp(34px,4vw,54px);
      line-height:.96;
    }
    .seo-money-panel p{
      margin:14px 0 0;
      font-size:15px;
      line-height:1.55;
    }
    .seo-money-search{
      display:grid;
      gap:10px;
      margin-top:18px;
      padding:18px;
      border:1px solid var(--line-soft);
      border-radius:18px;
      background:#fff;
    }
    .seo-money-search label{
      color:#344054;
      font-size:13px;
      font-weight:700;
    }
    .seo-money-search .row{
      display:grid;
      grid-template-columns:1fr auto;
      gap:10px;
    }
    .seo-money-search input{
      width:100%;
      height:44px;
      border:1px solid #d0d5dd;
      border-radius:14px;
      background:#fff;
      color:#111827;
      padding:0 14px;
      font-size:15px;
      outline:none;
    }
    .seo-money-search input:focus{
      border-color:var(--green);
      box-shadow:0 0 0 3px rgba(79,147,129,.14);
    }
    .seo-money-search .btn{
      min-height:44px;
      border-radius:14px;
    }
    .seo-money-shell{
      margin-top:22px;
    }
    .seo-money-section{
      padding:26px;
      margin-top:24px;
    }
    .seo-money-section h2{
      font-size:clamp(30px,4vw,52px);
      line-height:.96;
    }
    .seo-money-section p{
      margin:12px 0 0;
      font-size:15px;
    }
    .seo-money-grid-cards{
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      gap:18px;
      margin-top:22px;
    }
    .seo-money-listing{
      margin-top:24px;
    }
    .seo-money-empty{
      margin-top:18px;
      padding:18px;
      border:1px dashed #d0d5dd;
      border-radius:18px;
      background:#fcfcfd;
      color:#344054;
    }
    .seo-money-links{
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      gap:14px;
      margin-top:18px;
    }
    .seo-money-linkcard{
      display:block;
      padding:16px;
      border:1px solid var(--line-soft);
      border-radius:16px;
      background:#fff;
      text-decoration:none;
      box-shadow:0 10px 26px rgba(16,24,40,.04);
    }
    .seo-money-linkcard strong{
      display:block;
      color:var(--ink);
      font-size:15px;
      line-height:1.35;
    }
    .seo-money-linkcard span{
      display:block;
      margin-top:4px;
      color:var(--muted);
      font-size:13px;
      line-height:1.4;
    }
    .seo-money-faq{
      display:grid;
      gap:12px;
      margin-top:18px;
    }
    .seo-money-faq details{
      padding:16px 18px;
      border:1px solid var(--line-soft);
      border-radius:16px;
      background:#fff;
    }
    .seo-money-faq summary{
      cursor:pointer;
      list-style:none;
      font-weight:700;
      color:var(--ink);
    }
    .seo-money-faq summary::-webkit-details-marker{
      display:none;
    }
    .seo-money-faq p{
      margin-top:10px;
    }
    @media (max-width: 992px){
      .seo-money-grid,
      .seo-money-grid-cards,
      .seo-money-links{
        grid-template-columns:1fr;
      }
    }
    @media (max-width: 560px){
      .seo-money-copy,
      .seo-money-panel,
      .seo-money-section{
        padding:20px;
      }
      .seo-money-search .row{
        grid-template-columns:1fr;
      }
      .seo-money-copy h1{
        font-size:42px;
      }
    }
  </style>
@endpush

@section('content')
@php
  $popularLocations = collect($popularLocations ?? []);
  $products = collect($products ?? []);
@endphp

<section class="seo-money-page">
  <div class="container-page">
    <div class="seo-money-grid">
      <div class="seo-money-copy">
        <div class="seo-money-kicker">{{ $page['kicker'] ?? 'Search' }}</div>
        <h1>{{ $page['h1'] ?? $page['title'] }}</h1>
        <p>{{ $page['intro'] ?? '' }}</p>

        <div class="seo-money-points">
          @foreach(($page['highlights'] ?? []) as $point)
            <div class="seo-money-point">{{ $point }}</div>
          @endforeach
        </div>

        <div class="seo-money-actions">
          <a href="#money-results" class="btn btn-primary">Browse live listings</a>
          <a href="#money-faq" class="btn btn-light">Read FAQs</a>
        </div>
      </div>

      <aside class="seo-money-panel">
        <div>
          <h2>Start with your location</h2>
          <p>{{ $page['search_helper'] ?? 'Enter your town or postcode to narrow the results.' }}</p>
        </div>

        <form class="seo-money-search" method="get" action="{{ url('/locations') }}">
          <label for="money-place">Location</label>
          <div class="row">
            <input id="money-place" name="place" type="text" placeholder="{{ $page['search_placeholder'] ?? 'e.g. Maidstone' }}" value="{{ request()->query('place', request()->query('postcode', '')) }}">
            <button class="btn btn-primary" type="submit">Search</button>
          </div>
          <p class="text-muted mb-0" style="font-size:13px; line-height:1.45;">
            We will use this to help you find nearby results and keep you on the canonical location path.
          </p>
        </form>

        <div>
          <h2 style="font-size:clamp(24px,2.8vw,32px);">Popular locations</h2>
          <p>Use these as quick entry points if you want to browse faster.</p>
        </div>
      </aside>
    </div>

    <section class="seo-money-section">
      <h2>Popular locations</h2>
      <p>These location pages are useful starting points for finding live listings by county, town or region.</p>
      <div class="seo-money-links">
        @foreach($popularLocations as $location)
          <a class="seo-money-linkcard" href="{{ url($location['path'] ?? '/') }}">
            <strong>{{ $location['title'] ?? $location['label'] ?? 'Location' }}</strong>
            <span>{{ $location['country'] ?? 'We Offer Wellness' }}</span>
          </a>
        @endforeach
      </div>
    </section>

    <section class="seo-money-section" id="money-results">
      <h2>{{ $page['result_label'] ?? 'Live listings' }}</h2>
      <p>{{ $page['result_intro'] ?? 'Browse the strongest matches available now.' }}</p>

      @if($products->isNotEmpty())
        <div class="seo-money-listing">
          <div class="seo-money-grid-cards">
            @foreach($products as $product)
              @include('partials.product_card_v4', ['product' => $product])
            @endforeach
          </div>
        </div>
      @else
        <div class="seo-money-empty">
          No live listings matched this search yet. Use the therapy pages and location links above to keep browsing the current live catalogue.
        </div>
      @endif
    </section>

    <section class="seo-money-section">
      <h2>Related pages</h2>
      <p>These pages support the same search intent without creating duplicate URL families.</p>
      <div class="seo-money-links">
        @foreach(($page['related_links'] ?? []) as $link)
          <a class="seo-money-linkcard" href="{{ $link['href'] ?? '#' }}">
            <strong>{{ $link['label'] ?? 'Related page' }}</strong>
            <span>Open the canonical landing page</span>
          </a>
        @endforeach
      </div>
    </section>

    <section class="seo-money-section" id="money-faq">
      <h2>Frequently asked questions</h2>
      <div class="seo-money-faq">
        @foreach(($page['faqs'] ?? []) as $faq)
          <details>
            <summary>{{ $faq['q'] ?? '' }}</summary>
            <p>{{ $faq['a'] ?? '' }}</p>
          </details>
        @endforeach
      </div>
    </section>
  </div>
</section>
@endsection
