@php
  $sum = trim((string)($summary ?? ''));
  $about = trim((string)($body ?? ''));
  $whatExp = trim((string)($what ?? ''));
  $includedHtml = trim((string)($included ?? ''));
  $safetyTxt = trim((string)($safety ?? ''));
  $contraTxt = trim((string)($contra ?? ''));
  $locs = $locationsList ?? [];
@endphp

<style>
  :root{ --wow-accent:#18b36b; --wow-accent-2:#0fa25e; --wow-text:#101828; --wow-muted:rgba(16,24,40,.68); --wow-line:rgba(16,24,40,.14); --wow-soft-2:rgba(16,24,40,.08); --wow-radius-lg:20px }
  .wow-acc{ border-top:1px solid var(--wow-line); }
  .wow-acc-item{ border-bottom:1px solid var(--wow-line); }
  .wow-acc-header{ width:100%; background:transparent; border:0; padding:14px 0; display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer }
  .wow-acc-left{ display:flex; align-items:flex-start; gap:10px; min-width:0 }
  .wow-acc-left:before{ content:""; width:4px; height:22px; border-radius:999px; background:var(--wow-accent); margin-top:3px; flex:0 0 auto }
  .wow-acc-title{ font-size:20px; font-weight:900; margin:0; line-height:1.15; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
  .wow-acc-icon{ width:38px; height:38px; border-radius:999px; display:flex; align-items:center; justify-content:center; color:var(--wow-accent); font-size:20px }
  .wow-acc-body{ padding:0 0 14px 14px }
  .wow-section-title{ display:flex; align-items:flex-start; gap:10px; font-size:22px; font-weight:900; margin:0 0 10px }
  .wow-section-title:before{ content:""; width:4px; height:22px; border-radius:999px; margin-top:4px; background:var(--wow-accent); flex:0 0 auto }
  .wow-paragraph{ font-size:15px; line-height:1.7; color:var(--wow-text); margin:0 0 10px }
  .wow-clamp{ display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden }
  .wow-clamp[data-lines="2"]{ -webkit-line-clamp:2 }
  .wow-clamp[data-lines="3"]{ -webkit-line-clamp:3 }
  .wow-expanded .wow-clamp{ -webkit-line-clamp:unset !important; overflow:visible }
  .wow-readmore{ display:inline-flex; gap:8px; align-items:center; color:var(--wow-accent-2); font-weight:700; text-decoration:none; border-bottom:1px solid transparent; padding-bottom:2px }
  .wow-readmore:hover{ border-bottom-color: rgba(24,179,107,.35) }
  .wow-included{ list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px }
  .wow-included li{ display:flex; gap:12px; align-items:flex-start; font-size:15px; line-height:1.6; color:var(--wow-text) }
  .wow-included .thumb{ width:18px; height:18px; margin-top:3px; flex:0 0 auto; stroke:var(--wow-text); opacity:.9 }
  .wow-guidelines{ margin:0; padding-left:18px; color:#0f172a; line-height:1.8; list-style-type:circle; font-size:15px }
  /* Icon feature row */
  .wow-features{ margin:18px 0 18px; padding:6px 0 2px }
  .wow-feature{ text-align:center; padding:8px 6px }
  .wow-icon-circle{ width:68px; height:68px; border-radius:999px; margin:0 auto 10px auto; display:flex; align-items:center; justify-content:center; border:1px solid var(--wow-soft-2); background:#fff; box-shadow:0 10px 24px rgba(16,24,40,.06) }
  .wow-icon-circle svg{ width:28px; height:28px; stroke:var(--wow-accent) }
  .wow-feature b{ display:block; font-size:13px; font-weight:800; line-height:1.1 }
  .wow-feature span{ display:block; font-size:13px; font-weight:800; line-height:1.1 }
  .wow-loc-meta{ display:flex; align-items:center; gap:10px; color:var(--wow-text); font-weight:800; margin:8px 0 12px }
  .wow-loc-meta svg{ width:18px; height:18px; stroke:var(--wow-text); opacity:.85 }
  .wow-loc-panel{ border-radius:var(--wow-radius-lg); overflow:hidden; background:#fff; border:1px solid var(--wow-soft-2); box-shadow:0 16px 40px rgba(16,24,40,.06) }
  .wow-loc-list{ padding:10px; position:relative; height:320px; overflow:auto; background:#fff; list-style:none; margin:0 }
  .wow-loc-list:after{ content:""; position:absolute; top:10px; right:6px; width:3px; height:calc(100% - 20px); border-radius:999px; background:rgba(24,179,107,.18); pointer-events:none }
  .wow-loc-item{ width:100%; border:0; background:transparent; padding:12px 12px; border-radius:12px; display:flex; align-items:center; justify-content:space-between; gap:10px; font-weight:800; color:var(--wow-text); text-align:left }
  .wow-loc-item small{ font-weight:700; color:var(--wow-muted) }
  .wow-loc-item svg{ width:16px; height:16px; stroke:rgba(16,24,40,.65) }
  .wow-map{ height:320px; width:100%; border-left:1px solid var(--wow-soft-2); background:#f4f6f8 }
</style>

@include('offering.partials.sections.summary', ['summary' => $sum])

@include('offering.partials.sections.about', ['body' => $about])

@include('offering.partials.sections.features', ['locationsList' => $locs])

<div class="wow-acc mt-3">
  @include('offering.partials.sections.included', ['included' => $includedHtml])

  @include('offering.partials.sections.what_on_day', ['what' => $whatExp])

  @include('offering.partials.sections.guidelines', ['safety' => $safetyTxt, 'contra' => $contraTxt])

  @include('offering.partials.sections.locations', ['locationsList' => $locs])

  @include('offering.partials.sections.availability', ['availability' => $availability ?? ''])
  @include('offering.partials.sections.how_it_works', ['how' => $how ?? ''])

  @include('offering.partials.sections.you_may_also_like')
</div>

<script>
  (function(){
    function iconFor(sel){ return document.querySelector('[data-icon-for="'+sel+'"]'); }
    document.querySelectorAll('.collapse').forEach(function(el){
      el.addEventListener('shown.bs.collapse', function(){ var ic = iconFor('#'+el.id); if(ic) ic.textContent = '−'; });
      el.addEventListener('hidden.bs.collapse', function(){ var ic = iconFor('#'+el.id); if(ic) ic.textContent = '+'; });
    });
    document.addEventListener('click', function(e){
      var a = e.target.closest('[data-wow-readmore]'); if(!a) return; e.preventDefault();
      var target = a.getAttribute('data-target'); var wrap = document.querySelector(target); if(!wrap) return;
      wrap.classList.toggle('wow-expanded');
      a.textContent = wrap.classList.contains('wow-expanded') ? 'Read Less' : 'Read More';
    });
  })();
</script>
