@php $how = trim((string)($how ?? '')); @endphp
@if($how !== '')
  <div class="wow-acc-item">
    <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowHowItWorks" aria-expanded="false" aria-controls="wowHowItWorks">
      <div class="wow-acc-left"><h4 class="wow-acc-title m-0">How it works</h4></div>
      <div class="wow-acc-icon" data-icon-for="#wowHowItWorks">+</div>
    </button>
    <div id="wowHowItWorks" class="collapse">
      <div class="wow-acc-body">
        <div class="wow-paragraph">{!! nl2br(e($how)) !!}</div>
      </div>
    </div>
  </div>
@endif

