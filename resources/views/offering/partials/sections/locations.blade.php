@php $locationsList = $locationsList ?? []; @endphp
@if(!empty($locationsList))
  <div class="wow-acc-item">
    <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowLocation" aria-expanded="false" aria-controls="wowLocation">
      <div class="wow-acc-left"><h4 class="wow-acc-title m-0">Location(s)</h4></div>
      <div class="wow-acc-icon" data-icon-for="#wowLocation">+</div>
    </button>
    <div id="wowLocation" class="collapse">
      <div class="wow-acc-body">
        <ul class="wow-loc-list">
          @foreach($locationsList as $l)
            <li class="wow-loc-item">{{ $l }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
@endif

