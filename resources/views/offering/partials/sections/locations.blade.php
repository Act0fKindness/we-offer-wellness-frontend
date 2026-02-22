@php $locationsList = $locationsList ?? []; @endphp
@if(!empty($locationsList))
  <div class="wow-acc-item">
    <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowLocation" aria-expanded="false" aria-controls="wowLocation">
      <div class="wow-acc-left"><h4 class="wow-acc-title m-0">Location</h4></div>
      <div class="wow-acc-icon" data-icon-for="#wowLocation">+</div>
    </button>
    <div id="wowLocation" class="collapse">
      <div class="wow-acc-body">
        <div class="wow-loc-meta">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M12 21s7-4.4 7-11a7 7 0 0 0-14 0c0 6.6 7 11 7 11Z"></path>
            <circle cx="12" cy="10" r="2"></circle>
          </svg>
          <span><span id="wowLocCount">{{ count($locationsList) }}</span> Locations</span>
        </div>
        <div class="wow-loc-panel">
          <div class="row g-0">
            <div class="col-12 col-lg-5">
              <ul class="wow-loc-list" id="wowLocList">
                @foreach($locationsList as $l)
                  <li class="wow-loc-item">
                    <div>
                      <div>{{ $l }}</div>
                    </div>
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                      <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                  </li>
                @endforeach
              </ul>
            </div>
            <div class="col-12 col-lg-7">
              <div class="wow-map">
                <iframe id="wowMapFrame"
                        src="https://www.openstreetmap.org/export/embed.html?bbox=-8.65%2C49.8%2C1.77%2C60.9&layer=mapnik&marker=54.5%2C-2.0"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif
