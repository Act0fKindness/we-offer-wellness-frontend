@php
  $locs = is_array($locationsList ?? null) ? array_values(array_filter($locationsList)) : [];
  $hasOnline = false;
  $physicalLocations = [];
  foreach ($locs as $loc) {
      $label = trim((string) $loc);
      if ($label === '') { continue; }
      if (strcasecmp($label, 'online') === 0) { $hasOnline = true; continue; }
      $physicalLocations[] = $label;
  }
  $physicalCount = count($physicalLocations);
  $participantRange = $participantRange ?? null;
@endphp
<div class="wow-features">
  <div class="row row-cols-3 row-cols-lg-6 g-3 text-center wow-feature-grid">
    @if($participantRange)
      <div class="col">
        <div class="wow-feature">
          <div class="wow-icon-circle">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
          </div>
          <b>{{ $participantRange['label'] }}</b><span>{{ $participantRange['suffix'] }}</span>
        </div>
      </div>
    @endif
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z"/></svg>
        </div>
        <b>12 months</b><span>validity</span>
      </div>
    </div>
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          @if($hasOnline && $physicalCount === 0)
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000" stroke-linecap="round" stroke-width="1.5" d="M4.37 7.657c2.063.528 2.396 2.806 3.202 3.87 1.07 1.413 2.075 1.228 3.192 2.644 1.805 2.289 1.312 5.705 1.312 6.705M20 15h-1a4 4 0 0 0-4 4v1M8.587 3.992c0 .822.112 1.886 1.515 2.58 1.402.693 2.918.351 2.918 2.334 0 .276 0 2.008 1.972 2.008 2.026.031 2.026-1.678 2.026-2.008 0-.65.527-.9 1.177-.9H20M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
          @else
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/></svg>
          @endif
        </div>
        @if($hasOnline && $physicalCount === 0)
          <b>Online</b><span>only</span>
        @elseif($hasOnline && $physicalCount > 0)
          <b>{{ $physicalCount }} {{ \Illuminate\Support\Str::plural('location', $physicalCount) }}</b><span>in the UK &amp; Online</span>
        @elseif($physicalCount > 0)
          <b>{{ $physicalCount }} {{ \Illuminate\Support\Str::plural('location', $physicalCount) }}</b><span>in the UK</span>
        @else
          <b>Flexible</b><span>format</span>
        @endif
      </div>
    </div>
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m11.5 11.5 2.071 1.994M4 10h5m11 0h-1.5M12 7V4M7 7V4m10 3V4m-7 13H8v-2l5.227-5.292a1.46 1.46 0 0 1 2.065 2.065L10 17Zm-5 3h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"/></svg>
        </div>
        <b>Flexible</b><span>booking</span>
      </div>
    </div>
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35A7.13 7.13 0 0 0 19.03 12M6 20v-4h4"/></svg>
        </div>
        <b>Easy</b><span>exchanges</span>
      </div>
    </div>
    <div class="col">
      <div class="wow-feature">
        <div class="wow-icon-circle">
          <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16v-5.5A3.5 3.5 0 0 0 7.5 7m3.5 9H4v-5.5A3.5 3.5 0 0 1 7.5 7m3.5 9v4M7.5 7H14m0 0V4h2.5M14 7v3m-3.5 6H20v-6a3 3 0 0 0-3-3m-2 9v4m-8-6.5h1"/></svg>
        </div>
        <b>Instant</b><span>delivery</span>
      </div>
    </div>
  </div>
</div>
