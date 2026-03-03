@php $availability = trim((string)($availability ?? '')); @endphp
<div class="wow-acc-item">
  <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowAvailability" aria-expanded="false" aria-controls="wowAvailability">
    <div class="wow-acc-left"><h4 class="wow-acc-title m-0">Availability <span class="wow-badge-note">Select a date</span></h4></div>
    <div class="wow-acc-icon" data-icon-for="#wowAvailability">+</div>
  </button>
  <div id="wowAvailability" class="collapse">
    <div class="wow-acc-body">
      @if($availability!=='')
        <div class="wow-paragraph">{!! nl2br(e($availability)) !!}</div>
      @else
        <p class="wow-paragraph">Your voucher is valid for 12 months from the date of purchase. Please book and take your therapy before the expiry date.</p>
        <p class="wow-paragraph">This therapy is available 7 days a week (subject to practitioner availability).</p>
        <p class="wow-paragraph">We recommend booking at least 2–4 weeks in advance to ensure your preferred slots are available.</p>
        <p class="wow-paragraph">Please allow up to 60 minutes for the full therapy (plus a few minutes to settle in).</p>
      @endif
    </div>
  </div>
</div>
