@php 
  $body = trim((string)($body ?? ''));
  if ($body !== '') { $body = \App\Support\ContentFormatter::format($body); }
@endphp
@if($body !== '')
  <h3 class="wow-section-title mt-3">About this therapy</h3>
  <div class="wow-about" id="wowAbout">
    <div class="wow-paragraph wow-clamp" data-lines="3">{!! $body !!}</div>
    <a href="#" class="wow-readmore" data-wow-readmore data-target="#wowAbout">Read More</a>
  </div>
@endif
