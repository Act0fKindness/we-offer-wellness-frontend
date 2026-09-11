@php
  $breadcrumbItems = collect($crumbs ?? [])
    ->map(function ($crumb) {
      return [
        'label' => trim((string) data_get($crumb, 'label', '')),
        'url' => trim((string) data_get($crumb, 'url', '')),
      ];
    })
    ->filter(fn (array $crumb) => $crumb['label'] !== '')
    ->values();

  $chipItems = collect($chips ?? [])
    ->map(fn ($chip) => trim((string) $chip))
    ->filter(fn (string $chip) => $chip !== '')
    ->values();

  $schemaEnabled = $schemaEnabled ?? true;
  $schemaUrl = trim((string) ($schemaUrl ?? url()->current()));
  if ($schemaUrl === '') {
    $schemaUrl = url()->current();
  }

  $schemaId = trim((string) ($schemaId ?? ($schemaUrl . '#breadcrumb')));
  $mobileCurrent = trim((string) ($mobileCurrent ?? ($breadcrumbItems->last()['label'] ?? '')));
  $mobileBackUrl = trim((string) ($mobileBackUrl ?? ($breadcrumbItems->count() > 1 ? ($breadcrumbItems->slice(-2, 1)->first()['url'] ?? '') : '')));
  $mobileBackLabel = trim((string) ($mobileBackLabel ?? 'Back'));
  $renderVisual = $renderVisual ?? true;

  $schemaJsonLd = null;
  $schemaList = [];
  if ($schemaEnabled && $breadcrumbItems->count() > 0) {
    foreach ($breadcrumbItems as $index => $crumb) {
      $itemUrl = trim((string) ($crumb['url'] ?? ''));
      if ($itemUrl === '') {
        $itemUrl = ($index === $breadcrumbItems->count() - 1) ? $schemaUrl : url()->current();
      }

      $schemaList[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $crumb['label'],
        'item' => $itemUrl,
      ];
    }

    $schemaJsonLd = [
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      '@id' => $schemaId,
      'itemListElement' => $schemaList,
    ];
  }
@endphp

@once
  @push('styles')
    <style>
      .wow-breadcrumbs {
        position: relative;
        z-index: 2;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 50px rgba(16, 24, 40, 0.08);
        backdrop-filter: blur(12px);
      }

      .wow-breadcrumbs__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
      }

      .wow-breadcrumb-list {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
        min-width: 0;
      }

      .wow-breadcrumb-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
      }

      .wow-breadcrumb-link,
      .wow-breadcrumb-current,
      .wow-breadcrumb-chip,
      .wow-breadcrumb-mobile-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 36px;
        padding: 9px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        background: #ffffff;
        color: #667085;
        font-size: 13px;
        line-height: 1;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 8px 24px rgba(16, 24, 40, 0.04);
        transition: transform 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
      }

      .wow-breadcrumb-link:hover,
      .wow-breadcrumb-mobile-back:hover {
        color: #003c3c;
        border-color: rgba(15, 107, 87, 0.38);
        transform: translateY(-1px);
        box-shadow: 0 12px 30px rgba(16, 24, 40, 0.08);
      }

      .wow-breadcrumb-current {
        max-width: 420px;
        color: #003c3c;
        background: #d4fbe6;
        border-color: rgba(15, 107, 87, 0.16);
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .wow-breadcrumb-text {
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .wow-breadcrumb-separator {
        color: #98a2b3;
        font-size: 17px;
        line-height: 1;
        font-weight: 900;
      }

      .wow-breadcrumb-home-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #eefaf4;
        color: #0f6b57;
        font-size: 12px;
        line-height: 1;
        font-weight: 900;
      }

      .wow-breadcrumb-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex: 0 0 auto;
        flex-wrap: wrap;
      }

      .wow-breadcrumb-chip {
        color: #0f6b57;
        background: #f2fffa;
        border-color: #ccebe0;
        box-shadow: none;
      }

      .wow-breadcrumb-mobile {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-width: 0;
        width: 100%;
      }

      .wow-breadcrumb-mobile-current {
        min-width: 0;
        color: #003c3c;
        font-size: 13px;
        line-height: 1.2;
        font-weight: 900;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: right;
      }

      @media (max-width: 991px) {
        .wow-breadcrumbs__inner {
          align-items: flex-start;
          flex-direction: column;
        }

        .wow-breadcrumb-meta {
          width: 100%;
          justify-content: flex-start;
        }

        .wow-breadcrumb-current {
          max-width: 280px;
        }
      }

      @media (max-width: 991px) {
        .wow-breadcrumb-list,
        .wow-breadcrumb-meta {
          display: none;
        }

        .wow-breadcrumb-mobile {
          display: flex;
        }

        .wow-breadcrumbs {
          padding: 10px;
          border-radius: 18px;
        }

        .wow-breadcrumb-mobile-back {
          min-height: 38px;
          padding: 10px 13px;
        }
      }
    </style>
  @endpush
@endonce

@if($schemaEnabled && !empty($schemaList))
  @push('head')
    @once
      <script type="application/ld+json">{!! json_encode($schemaJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
    @endonce
  @endpush
@endif

@if($renderVisual && $breadcrumbItems->count() > 1)
  <div class="container mt-3">
    <nav class="wow-breadcrumbs" aria-label="Breadcrumb">
      <div class="wow-breadcrumbs__inner">
        <ol class="wow-breadcrumb-list">
          @foreach($breadcrumbItems as $index => $crumb)
            <li class="wow-breadcrumb-item">
              @if($index < $breadcrumbItems->count() - 1)
                <a href="{{ $crumb['url'] }}" class="wow-breadcrumb-link">
                  @if($index === 0)
                    <span class="wow-breadcrumb-home-icon">⌂</span>
                  @endif
                  <span class="wow-breadcrumb-text">{{ $crumb['label'] }}</span>
                </a>
                <span class="wow-breadcrumb-separator">›</span>
              @else
                <span class="wow-breadcrumb-current" aria-current="page">
                  <span class="wow-breadcrumb-text">{{ $crumb['label'] }}</span>
                </span>
              @endif
            </li>
          @endforeach
        </ol>

        @if($chipItems->isNotEmpty())
          <div class="wow-breadcrumb-meta">
            @foreach($chipItems as $chip)
              <span class="wow-breadcrumb-chip">{{ $chip }}</span>
            @endforeach
          </div>
        @endif

        <div class="wow-breadcrumb-mobile">
          @if($mobileBackUrl !== '')
            <a href="{{ $mobileBackUrl }}" class="wow-breadcrumb-mobile-back">‹ {{ $mobileBackLabel }}</a>
          @else
            <span class="wow-breadcrumb-mobile-back">‹ {{ $mobileBackLabel }}</span>
          @endif
          <span class="wow-breadcrumb-mobile-current">{{ $mobileCurrent }}</span>
        </div>
      </div>
    </nav>
  </div>
@endif
