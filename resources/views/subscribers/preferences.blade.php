@extends('layouts.app')

@php
    $prefs = $preferences ?? [];
    $oldInterests = old('interests');
    $prefOnline = is_array($oldInterests) ? in_array('online', $oldInterests, true) : ($prefs['online'] ?? false);
    $prefInPerson = is_array($oldInterests) ? in_array('in_person', $oldInterests, true) : ($prefs['in_person'] ?? false);
    $prefLocation = old('location', $prefs['location'] ?? '');
    $prefRadius = (int) old('radius', $prefs['radius'] ?? 25);
    $oldGoals = old('goals');
    $prefGoals = is_array($oldGoals) ? $oldGoals : ($prefs['goals'] ?? []);
    $goalOptions = [
        'sleep' => 'Better sleep',
        'stress' => 'Stress & anxiety relief',
        'pain' => 'Pain management',
    ];
@endphp

@section('title', 'Update email preferences | We Offer Wellness®')
@section('meta_description', 'Tell us the type of wellness rituals you want to hear about and we’ll tailor every email.')

@section('content')
<section class="section">
  <div class="container-page" style="max-width:820px;">
    <article class="account-card">
      <div class="account-card__header">
        <p class="eyebrow">We Offer Wellness®</p>
        <h1>Email preferences</h1>
        <p class="lead-cart">Choose what you want to hear about. You can update this anytime.</p>
      </div>
      <div class="account-card__body">
        @if(session('status'))
          <div class="account-auth-alert show" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
          <div class="account-auth-alert show" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('subscribe.preferences.update', ['token' => $token]) }}" class="account-auth-form">
          @csrf
          <div class="account-auth-field-group">
            <label class="account-auth-label">Interests</label>
            <label class="account-auth-check">
              <input type="checkbox" name="interests[]" value="online" {{ $prefOnline ? 'checked' : '' }}>
              Online sessions & on-demand rituals
            </label>
            <label class="account-auth-check">
              <input type="checkbox" name="interests[]" value="in_person" {{ $prefInPerson ? 'checked' : '' }}>
              In-person sessions near me
            </label>
          </div>

          <div class="account-auth-field-group">
            <label class="account-auth-label">Where should we look?</label>
            <input type="text" name="location" class="account-auth-input" placeholder="e.g., East London or UK-wide" value="{{ $prefLocation }}">
            <small class="text-muted">Leave blank if you’re happy to browse anywhere.</small>
          </div>

          <div class="account-auth-field-group">
            <label class="account-auth-label">Travel radius</label>
            <select name="radius" class="account-auth-input">
              @foreach([10, 25, 50, 100, 200] as $radius)
                <option value="{{ $radius }}" {{ $prefRadius === $radius ? 'selected' : '' }}>{{ $radius }} km</option>
              @endforeach
            </select>
          </div>

          <div class="account-auth-field-group">
            <label class="account-auth-label">Goals & vibes</label>
            @foreach($goalOptions as $value => $label)
              <label class="account-auth-check">
                <input type="checkbox" name="goals[]" value="{{ $value }}" {{ in_array($value, $prefGoals, true) ? 'checked' : '' }}>
                {{ $label }}
              </label>
            @endforeach
          </div>

          <div class="account-auth-row" style="gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn-wow btn-wow--cta">Save preferences</button>
            <a href="{{ route('subscribe.unsubscribe', ['token' => $token]) }}" class="btn-wow btn-wow--ghost">Unsubscribe instead</a>
          </div>
        </form>
      </div>
    </article>
  </div>
</section>
@endsection
