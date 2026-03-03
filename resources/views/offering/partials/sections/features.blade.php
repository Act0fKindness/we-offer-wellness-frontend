@php
  $locs = $locationsList ?? [];
  $locCount = is_array($locs) ? count($locs) : 0;
  $participantRange = $participantRange ?? null;
@endphp
<div class="wow-features">
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3 text-center">
    @if($participantRange)
      <div class="col">
        <div class="wow-feature">
          <div class="wow-icon-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
              <path d="M20 21a8 8 0 0 0-16 0"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </div>
          <b>{{ $participantRange['label'] }}</b><span>{{ $participantRange['suffix'] }}</span>
        </div>
      </div>
    @endif
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M8 2v3"></path><path d="M16 2v3"></path>
            <rect x="3" y="5" width="18" height="17" rx="2"></rect>
            <path d="M3 10h18"></path>
          </svg>
        </div>
        <b>12 months</b><span>validity</span>
      </div>
    </div>
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M12 21s7-4.4 7-11a7 7 0 0 0-14 0c0 6.6 7 11 7 11Z"></path>
            <circle cx="12" cy="10" r="2"></circle>
          </svg>
        </div>
        <b><span class="text-decoration-underline">{{ $locCount }}</span></b><span>in the UK</span>
      </div>
    </div>
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M4 7h16"></path>
            <path d="M7 4v6"></path>
            <path d="M17 4v6"></path>
            <path d="M5 21h14a2 2 0 0 0 2-2V9H3v10a2 2 0 0 0 2 2Z"></path>
          </svg>
        </div>
        <b>Flexible</b><span>booking</span>
      </div>
    </div>
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M3 12h18"></path>
            <path d="M7 8l-4 4 4 4"></path>
            <path d="M17 16l4-4-4-4"></path>
          </svg>
        </div>
        <b>Easy</b><span>exchanges</span>
      </div>
    </div>
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M4 4h16v16H4z"></path>
            <path d="M22 6 12 13 2 6"></path>
          </svg>
        </div>
        <b>Instant</b><span>delivery</span>
      </div>
    </div>
  </div>
</div>
