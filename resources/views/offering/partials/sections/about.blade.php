@php $body = trim((string)($body ?? '')); @endphp
@if($body !== '')
  <h3 class="wow-section-title mt-3">About this experience</h3>
  <div class="wow-about" id="wowAbout">
    <div class="wow-paragraph wow-clamp" data-lines="3">{!! html_entity_decode($body) !!}</div>
    <a href="#" class="wow-readmore" data-wow-readmore data-target="#wowAbout">Read More</a>
  </div>
@endif
