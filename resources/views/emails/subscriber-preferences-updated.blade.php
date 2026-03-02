@extends('emails.layout')

@section('title','Preferences updated')
@section('content')
  <span class="eyebrow">All set</span>
  <h1>Your preferences are saved</h1>
  <p>Thanks for letting us know what you want to hear about. Here’s what we’ll send going forward:</p>
  <ul style="margin:0 0 18px 20px; color:#4b5565;">
    @if(($preferences['online'] ?? false))
      <li>Online-only sessions, classes, and on-demand rituals.</li>
    @endif
    @if(($preferences['in_person'] ?? false))
      <li>In-person experiences near {{ $preferences['location'] ?? 'you' }} (within {{ $preferences['radius'] ?? 25 }} km).</li>
    @endif
    @if(!empty($preferences['goals']))
      <li>Recommendations focused on: {{ implode(', ', array_map('ucwords', $preferences['goals'])) }}.</li>
    @endif
  </ul>
  <p style="margin-bottom:0;">Change of heart? <a href="{{ $preferencesUrl }}" target="_blank" rel="noopener">Update your preferences</a> anytime.</p>
@endsection
