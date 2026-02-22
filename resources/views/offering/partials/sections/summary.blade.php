@php $summary = trim((string)($summary ?? '')); @endphp
@if($summary !== '')
  <h3 class="wow-section-title">Summary</h3>
  <div class="wow-about" id="wowSummary">
    <div class="wow-paragraph wow-clamp" data-lines="3">{!! nl2br(e($summary)) !!}</div>
    <a href="#" class="wow-readmore" data-wow-readmore data-target="#wowSummary">Read More</a>
  </div>
@endif

