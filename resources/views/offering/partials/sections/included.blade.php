@php $included = trim((string)($included ?? '')); @endphp
@if($included !== '')
  <div class="wow-acc-item">
    <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowIncluded" aria-expanded="true" aria-controls="wowIncluded">
      <div class="wow-acc-left"><h4 class="wow-acc-title m-0">What's Included</h4></div>
      <div class="wow-acc-icon" data-icon-for="#wowIncluded">−</div>
    </button>
    <div id="wowIncluded" class="collapse show">
      <div class="wow-acc-body">
        <div class="wow-paragraph">{!! nl2br(e($included)) !!}</div>
      </div>
    </div>
  </div>
@endif

