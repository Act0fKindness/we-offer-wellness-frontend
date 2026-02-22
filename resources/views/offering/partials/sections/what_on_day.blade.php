@php $what = trim((string)($what ?? '')); @endphp
@if($what !== '')
  <div class="wow-acc-item">
    <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowOnTheDay" aria-expanded="true" aria-controls="wowOnTheDay">
      <div class="wow-acc-left"><h4 class="wow-acc-title m-0">What happens on the day?</h4></div>
      <div class="wow-acc-icon" data-icon-for="#wowOnTheDay">−</div>
    </button>
    <div id="wowOnTheDay" class="collapse show">
      <div class="wow-acc-body" id="wowOnDayBlock">
        <div class="wow-paragraph wow-clamp" data-lines="3">{!! nl2br(e($what)) !!}</div>
        <a href="#" class="wow-readmore" data-wow-readmore data-target="#wowOnDayBlock">Read More</a>
      </div>
    </div>
  </div>
@endif

