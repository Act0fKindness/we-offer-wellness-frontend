@php
  $sum = trim((string)($summary ?? ''));
  $about = trim((string)($body ?? ''));
  $whatExp = trim((string)($what ?? ''));
  $includedHtml = trim((string)($included ?? ''));
  $safetyTxt = trim((string)($safety ?? ''));
  $contraTxt = trim((string)($contra ?? ''));
  $locs = $locationsList ?? [];
  $participantRange = $participantRange ?? null;
@endphp

<style>
  :root{ --wow-accent:#549483; --wow-accent-2:#17643d; --wow-text:#101828; --wow-muted:rgba(16,24,40,.68); --wow-line:rgba(16,24,40,.14); --wow-soft-2:rgba(16,24,40,.08); --wow-radius-lg:20px }
  .wow-acc{ border-top:1px solid var(--wow-line); }
  .wow-acc-item{ border-bottom:1px solid var(--wow-line); }
  .wow-acc-header{ width:100%; background:transparent; border:0; padding:14px 0; display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer }
  .wow-acc-left{ display:flex; align-items:flex-start; gap:10px; min-width:0 }
  .wow-acc-left:before{ content:""; width:4px; height:22px; border-radius:999px; background:var(--wow-accent); margin-top:3px; flex:0 0 auto }
  .wow-acc-title{ font-size:20px; font-weight:400; margin:0; line-height:1.15; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
  .wow-acc-icon{ width:38px; height:38px; border-radius:999px; display:flex; align-items:center; justify-content:center; color:var(--wow-accent); font-size:20px }
  .wow-acc-body{ padding:0 0 14px 14px }
  .wow-section-title{ display:flex; align-items:flex-start; gap:10px; font-size:22px; font-weight:400; margin:0 0 10px }
  /* Every .wow-section-title after the first gets extra top margin */
  .wow-section-title ~ .wow-section-title{ margin-top:20px }
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
  .wow-badge-note{ display:inline-block; margin-left:8px; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700; background:#eef7f2; color:#185a44; border:1px solid #c7e5d9 }
  /* Icon feature row */
  .wow-features{ margin:18px 0 18px; padding:6px 0 2px }
  .wow-feature{ text-align:center; padding:8px 6px }
  .wow-icon-circle{ width:68px; height:68px; border-radius:999px; margin:0 auto 10px auto; display:flex; align-items:center; justify-content:center; border:1px solid var(--wow-soft-2); background:#fff; box-shadow:0 10px 24px rgba(16,24,40,.06) }
  .wow-icon-circle svg{ width:28px; height:28px; stroke:var(--wow-accent) }
  .wow-feature b{ display:block; font-size:13px; font-weight:800; line-height:1.1 }
  .wow-feature span{ display:block; font-size:13px; font-weight:800; line-height:1.1 }
  .wow-loc-meta{ display:flex; align-items:center; gap:10px; color:var(--wow-text); font-weight:800; margin:8px 0 12px }
  .wow-loc-meta svg{ width:18px; height:18px; stroke:var(--wow-text); opacity:.85 }
  .wow-loc-panel{ border-radius:var(--wow-radius-lg); overflow:hidden; background:linear-gradient(180deg,#f8fbfa,#eef5f1); border:1px solid var(--wow-soft-2); box-shadow:0 18px 40px rgba(16,24,40,.08) }
  .wow-loc-list{ padding:12px; height:340px; overflow:auto; background:transparent; list-style:none; margin:0; border-right:1px solid rgba(16,24,40,.08); }
  .wow-loc-list li{ list-style:none; margin:0; padding:0; }
  .wow-loc-item{ width:100%; border:0; background:rgba(255,255,255,.45); padding:12px 14px; border-radius:14px; display:flex; align-items:flex-start; gap:12px; font-weight:600; color:var(--wow-text); text-align:left; transition:all .15s ease; width:100%; cursor:pointer; }
  .wow-loc-item + li .wow-loc-item{ margin-top:10px; }
  .wow-loc-item:hover{ background:#fff; box-shadow:0 10px 28px rgba(16,24,40,.08); }
  .wow-loc-item.active{ background:#fff; box-shadow:0 14px 32px rgba(16,24,40,.12); }
  .wow-loc-pill{ width:10px; height:10px; border-radius:999px; background:#d1d5db; margin-top:6px; flex:0 0 auto; transition:all .2s ease; }
  .wow-loc-item.active .wow-loc-pill{ background:#549483; box-shadow:0 0 0 4px rgba(84,148,131,.18); }
  .wow-loc-copy{ flex:1; display:flex; flex-direction:column; gap:2px; min-width:0; }
  .wow-loc-title{ font-weight:700; font-size:15px; color:var(--wow-text); word-break:break-word; }
  .wow-loc-sub{ font-weight:500; color:var(--wow-muted); font-size:13px; word-break:break-word; }
  .wow-loc-chevron{ display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; }
  .wow-loc-chevron svg{ width:18px; height:18px; stroke:rgba(16,24,40,.4); transition:color .15s ease, stroke .15s ease; }
  .wow-loc-item.active .wow-loc-chevron svg{ stroke:#549483; }
  .wow-map{ position:relative; min-height:340px; width:100%; border-left:1px solid var(--wow-soft-2); background:linear-gradient(180deg,#f4f6f8,#e3ece8); border-top-right-radius:var(--wow-radius-lg); border-bottom-right-radius:var(--wow-radius-lg); overflow:hidden; }
  .wow-map canvas{ border-top-right-radius:var(--wow-radius-lg); border-bottom-right-radius:var(--wow-radius-lg); }
  .wow-map-placeholder{ position:absolute; inset:0; display:grid; place-items:center; color:#6b7280; font-weight:600; text-align:center; padding:1rem; }
  .wow-pin{ width:30px; height:30px; border-radius:50% 50% 50% 0; transform:rotate(-45deg); background:linear-gradient(135deg,#5ecba1,#3d8b72); border:2px solid #fff; box-shadow:0 12px 26px rgba(16,24,40,.3); position:relative; }
  .wow-pin::after{ content:""; position:absolute; width:10px; height:10px; border-radius:50%; background:#fff; top:50%; left:50%; transform:translate(-50%,-50%); }
  .wow-pin.is-active{ background:linear-gradient(135deg,#3d8b72,#266956); box-shadow:0 16px 30px rgba(16,24,40,.4); }
</style>

@include('offering.partials.sections.summary', ['summary' => $sum])

@include('offering.partials.sections.about', ['body' => $about])

@include('offering.partials.sections.features', ['locationsList' => $locs, 'participantRange' => $participantRange])

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
