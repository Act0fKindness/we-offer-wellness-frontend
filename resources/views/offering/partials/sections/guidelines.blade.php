@php $safety = trim((string)($safety ?? '')); $contra = trim((string)($contra ?? '')); @endphp
<div class="wow-acc-item">
  <button class="wow-acc-header" type="button" data-bs-toggle="collapse" data-bs-target="#wowGuidelines" aria-expanded="false" aria-controls="wowGuidelines">
    <div class="wow-acc-left"><h4 class="wow-acc-title m-0">Participant guidelines</h4></div>
    <div class="wow-acc-icon" data-icon-for="#wowGuidelines">+</div>
  </button>
  <div id="wowGuidelines" class="collapse">
    <div class="wow-acc-body">
      @if($safety!=='' || $contra!=='')
        @if($safety!=='')<div class="wow-paragraph">{!! nl2br(e($safety)) !!}</div>@endif
        @if($contra!=='')<div class="wow-paragraph">{!! nl2br(e($contra)) !!}</div>@endif
      @else
        <ul class="wow-guidelines">
          <li>Minimum age: 18 for alcohol (where applicable)</li>
          <li>Dietary requirements can be catered for (please tell us in advance)</li>
          <li>If pregnant, recently injured, or managing a medical condition, check suitability before booking</li>
        </ul>
      @endif
    </div>
  </div>
  </div>
