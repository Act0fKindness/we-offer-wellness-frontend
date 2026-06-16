@php
  $guidePanel = $guidePanel ?? null;

  if ($guidePanel === null) {
    $guidePanel = app(\App\Services\GuideRegistryService::class)->modalityGuidePanel(
      (string) ($guidePanelModality ?? ''),
      isset($guidePanelFormat) ? (string) $guidePanelFormat : null,
    );
  }
@endphp

@if(!empty($guidePanel))
  <section class="card p-4 mb-4" style="border-radius:20px;border:1px solid #dfe4ea;background:linear-gradient(180deg, rgba(232,245,241,.78), rgba(255,255,255,.98));box-shadow:0 18px 54px rgba(16,24,40,.05);">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div style="max-width:64ch;">
        <div class="kicker" style="margin-bottom:10px;">{{ $guidePanel['eyebrow'] ?? 'Explore guides' }}</div>
        <h2 style="margin:0;color:#101828;font-size:clamp(28px,3.8vw,42px);line-height:1;font-family:'Playfair Display', Georgia, serif;font-weight:500;letter-spacing:-0.04em;">
          {{ $guidePanel['title'] ?? 'Guides' }}
        </h2>
        @if(!empty($guidePanel['summary']))
          <p class="text-ink-600 mt-2" style="max-width:62ch;">{{ $guidePanel['summary'] }}</p>
        @endif
      </div>
      @if(!empty($guidePanel['hub_url']))
        <div class="hidden md:block">
          <a href="{{ $guidePanel['hub_url'] }}" class="btn btn-light">{{ $guidePanel['hub_label'] ?? 'Browse guides' }}</a>
        </div>
      @endif
    </div>

    @if(!empty($guidePanel['links']))
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
        @foreach($guidePanel['links'] as $link)
          <a href="{{ $link['url'] }}" class="card p-3" style="display:block;border-radius:16px;border:1px solid #e5e7eb;background:#fff;text-decoration:none;color:inherit;box-shadow:0 10px 26px rgba(16,24,40,.04);">
            <strong style="display:block;color:#101828;line-height:1.35;">{{ $link['title'] ?? $link['label'] ?? 'Guide' }}</strong>
            @if(!empty($link['summary']))
              <span style="display:block;margin-top:8px;color:#596275;font-size:13px;line-height:1.45;">{{ $link['summary'] }}</span>
            @endif
          </a>
        @endforeach
      </div>
    @endif

    @if(!empty($guidePanel['hub_url']))
      <div class="md:hidden mt-4">
        <a href="{{ $guidePanel['hub_url'] }}" class="btn btn-light">{{ $guidePanel['hub_label'] ?? 'Browse guides' }}</a>
      </div>
    @endif
  </section>
@endif
