@php $how = trim((string)($how ?? '')); @endphp
<div class="wow-acc-item">
  <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowHowItWorks" aria-expanded="false" aria-controls="wowHowItWorks">
    <div class="wow-acc-left"><h4 class="wow-acc-title m-0">How it works</h4></div>
    <div class="wow-acc-icon" data-icon-for="#wowHowItWorks">+</div>
  </button>
  <div id="wowHowItWorks" class="collapse">
    <div class="wow-acc-body">
      @if($how!=='')
        <div class="wow-paragraph">{!! nl2br(e($how)) !!}</div>
      @else
        <p class="wow-paragraph">Pay for the experience and we’ll send your confirmation (and voucher details if applicable) to you — or directly to the recipient. Then you simply check the info and book your preferred slot.</p>
      @endif
    </div>
  </div>
</div>
