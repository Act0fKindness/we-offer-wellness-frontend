@php $availability = trim((string)($availability ?? '')); @endphp
@if($availability !== '')
  <div class="wow-acc-item">
    <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowAvailability" aria-expanded="false" aria-controls="wowAvailability">
      <div class="wow-acc-left"><h4 class="wow-acc-title m-0">Availability</h4></div>
      <div class="wow-acc-icon" data-icon-for="#wowAvailability">+</div>
    </button>
    <div id="wowAvailability" class="collapse">
      <div class="wow-acc-body">
        <div class="wow-paragraph">{!! nl2br(e($availability)) !!}</div>
      </div>
    </div>
  </div>
@endif

