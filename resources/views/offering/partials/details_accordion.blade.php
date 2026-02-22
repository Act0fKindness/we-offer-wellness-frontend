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
  .wow-acc{ border-top:1px solid var(--ink-200); }
  .wow-acc-item{ border-bottom:1px solid var(--ink-200); }
  .wow-acc-header{ width:100%; background:transparent; border:0; padding:14px 0; display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer }
  .wow-acc-left{ display:flex; align-items:flex-start; gap:10px; min-width:0 }
  .wow-acc-left:before{ content:""; width:4px; height:22px; border-radius:999px; background:#18b36b; margin-top:3px; flex:0 0 auto }
  .wow-acc-title{ font-size:18px; font-weight:800; margin:0; line-height:1.15; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
  .wow-acc-icon{ width:32px; height:32px; border-radius:999px; display:flex; align-items:center; justify-content:center; color:#18b36b; font-size:18px }
  .wow-acc-body{ padding:0 0 14px 14px }
  .wow-section-title{ display:flex; align-items:flex-start; gap:10px; font-size:18px; font-weight:900; margin:0 0 6px }
  .wow-section-title:before{ content:""; width:4px; height:22px; border-radius:999px; margin-top:4px; background:#18b36b; flex:0 0 auto }
  .wow-paragraph{ font-size:15px; line-height:1.7; color:#0f172a; margin:0 0 10px }
  .wow-clamp{ display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden }
  .wow-clamp[data-lines="2"]{ -webkit-line-clamp:2 }
  .wow-clamp[data-lines="3"]{ -webkit-line-clamp:3 }
  .wow-expanded .wow-clamp{ -webkit-line-clamp:unset !important; overflow:visible }
  .wow-readmore{ display:inline-flex; gap:8px; align-items:center; color:#0fa25e; font-weight:700; text-decoration:none; border-bottom:1px solid transparent; padding-bottom:2px }
  .wow-readmore:hover{ border-bottom-color: rgba(24,179,107,.35) }
  .wow-included{ list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px }
  .wow-included li{ display:flex; gap:10px; align-items:flex-start; font-size:15px; line-height:1.6; color:#0f172a }
  .wow-included .thumb{ width:16px; height:16px; margin-top:4px; flex:0 0 auto; stroke:#0f172a; opacity:.9 }
  .wow-guidelines{ margin:0; padding-left:18px; color:#0f172a; line-height:1.8; list-style-type:circle; font-size:15px }
  .wow-loc-list{ list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:6px }
  .wow-loc-item{ display:flex; align-items:center; gap:8px; font-weight:700; padding:6px 8px; border-radius:10px; border:1px solid var(--ink-200) }
</style>

@include('offering.partials.sections.summary', ['summary' => $sum])

@include('offering.partials.sections.about', ['body' => $about])

<div class="wow-acc mt-3">
  @include('offering.partials.sections.included', ['included' => $includedHtml])

  @include('offering.partials.sections.what_on_day', ['what' => $whatExp])

  @include('offering.partials.sections.guidelines', ['safety' => $safetyTxt, 'contra' => $contraTxt])

  @include('offering.partials.sections.locations', ['locationsList' => $locs])
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
