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
        'energy' => 'More energy',
        'mind' => 'Mindfulness & breath',
    ];
    $interestBadges = [];
    if ($prefOnline) { $interestBadges[] = 'Online rituals'; }
    if ($prefInPerson) { $interestBadges[] = 'In-person sessions'; }
    if (empty($interestBadges)) { $interestBadges[] = 'Launch alerts & new drops'; }
    $goalLabels = array_intersect_key($goalOptions, array_flip($prefGoals));
    $goalSummary = $goalLabels ? implode(' · ', $goalLabels) : 'Curated calm & launches';
    $upsells = $upsellProducts ?? [];
    $articles = $mindfulArticles ?? [];
@endphp

@section('title', 'Update email preferences | We Offer Wellness®')
@section('meta_description', 'Tell us the type of wellness rituals you want to hear about and we’ll tailor every email.')

@section('content')
<div class="pref-page">
  <section class="section pref-hero">
    <div class="container-page pref-hero__grid">
      <div class="pref-hero__copy">
        <p class="kicker">Preference centre</p>
        <h1>Keep your inbox curated</h1>
        <p class="lead">Hey {{ $subscriber->first_name ?? $subscriber->name ?? 'friend' }}, tell us how you’d like We Offer Wellness to show up. We’ll only send launches, rituals, and experiences that match.</p>
        <div class="pref-hero__cta-row">
          <a href="#pref-form" class="btn-wow btn-wow--cta">Edit preferences</a>
          <a href="/" class="btn-wow btn-wow--ghost">Back to site</a>
        </div>
      </div>
      <div class="pref-hero__card">
        <p class="pref-hero__eyebrow">Your current mix</p>
        <ul class="pref-hero__list">
          @foreach($interestBadges as $label)
            <li>{{ $label }}</li>
          @endforeach
        </ul>
        <p class="pref-hero__note">Goals: {{ $goalSummary }}</p>
      </div>
    </div>
  </section>

  <section class="section pref-manage" id="pref-form">
    <div class="container-page pref-manage__grid">
      <div class="pref-card pref-card--form">
        <div class="pref-card__header">
          <p class="kicker">Manage delivery</p>
          <h2>Email preferences</h2>
          <p>Choose what lands in your inbox. Everything saves instantly once you hit the button below.</p>
        </div>
        @if(session('status'))
          <div class="pref-alert pref-alert--success" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
          <div class="pref-alert pref-alert--error" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('subscribe.preferences.update', ['token' => $token]) }}" class="pref-form">
          @csrf
          <div class="pref-fieldset">
            <div class="pref-fieldset__legend">How do you want to experience WOW?</div>
            <label class="pref-checkbox">
              <input type="checkbox" name="interests[]" value="online" {{ $prefOnline ? 'checked' : '' }}>
              <span>
                <strong>Online sessions</strong>
                <small>On-demand rituals, livestreams and recordings.</small>
              </span>
            </label>
            <label class="pref-checkbox">
              <input type="checkbox" name="interests[]" value="in_person" {{ $prefInPerson ? 'checked' : '' }}>
              <span>
                <strong>In-person sessions near me</strong>
                <small>Studios, practitioner pop-ups and travelling residencies.</small>
              </span>
            </label>
          </div>

          <div class="pref-fieldset">
            <div class="pref-fieldset__legend">Where should we look?</div>
            <input type="text" name="location" class="pref-input" placeholder="e.g., East London or UK-wide" value="{{ $prefLocation }}">
            <small class="pref-hint">Leave blank for UK-wide discoveries.</small>
          </div>

          <div class="pref-fieldset">
            <div class="pref-fieldset__legend">Travel radius</div>
            <select name="radius" class="pref-input">
              @foreach([10, 25, 50, 100, 200] as $radius)
                <option value="{{ $radius }}" {{ $prefRadius === $radius ? 'selected' : '' }}>{{ $radius }} km</option>
              @endforeach
            </select>
          </div>

          <div class="pref-fieldset">
            <div class="pref-fieldset__legend">Goals & vibes</div>
            <p class="pref-hint">Pick as many as you like — we’ll rotate recommendations to keep things fresh.</p>
            <div class="pref-chip-grid">
              @foreach($goalOptions as $value => $label)
                <label class="pref-chip">
                  <input type="checkbox" name="goals[]" value="{{ $value }}" {{ in_array($value, $prefGoals, true) ? 'checked' : '' }}>
                  <span>{{ $label }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="pref-form__actions">
            <button type="submit" class="btn-wow btn-wow--cta">Save preferences</button>
            <a href="{{ route('subscribe.unsubscribe', ['token' => $token]) }}" class="btn-wow btn-wow--ghost">Unsubscribe instead</a>
          </div>
          <p class="pref-hint">Need a full reset? Every update sends you a confirmation email so you’re always in control.</p>
        </form>
      </div>
      <aside class="pref-card pref-card--summary">
        <div class="pref-card__header">
          <p class="kicker">Snapshot</p>
          <h3>What you’re set to receive</h3>
        </div>
        <ul class="pref-summary-list">
          @foreach($interestBadges as $label)
            <li>
              <span>{{ $label }}</span>
              <small>Handpicked drops that match this mode.</small>
            </li>
          @endforeach
          <li>
            <span>Goals: {{ $goalSummary }}</span>
            <small>We’ll prioritise rituals with these outcomes.</small>
          </li>
          @if($prefLocation)
            <li>
              <span>Location focus: {{ $prefLocation }}</span>
              <small>Within {{ $prefRadius }}km.</small>
            </li>
          @endif
        </ul>
        <div class="pref-card__footer">
          <p>Prefer text alerts instead? Coming soon — keep an eye on your inbox.</p>
        </div>
      </aside>
    </div>
  </section>

  @if(!empty($upsells))
    <section class="section pref-upsell">
      <div class="container-page">
        <div class="pref-upsell__head">
          <div>
            <p class="kicker">Don’t miss these</p>
            <h2>Experiences pairing well with your picks</h2>
          </div>
          <a href="/therapies" class="btn-wow btn-wow--outline btn-sm">Browse all</a>
        </div>
        <div class="pref-upsell__grid">
          @foreach($upsells as $product)
            <article class="pref-upsell-card">
              @if(!empty($product['image']))
                <div class="pref-upsell-card__media">
                  <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}">
                </div>
              @endif
              <div class="pref-upsell-card__body">
                @if(!empty($product['tag']))
                  <span class="pref-tag">{{ $product['tag'] }}</span>
                @endif
                <h3>{{ $product['title'] }}</h3>
                <p class="pref-upsell-card__meta">
                  @if(!empty($product['mode']))
                    <span>{{ $product['mode'] }}</span>
                  @endif
                  @if(!empty($product['location']))
                    <span>{{ $product['location'] }}</span>
                  @endif
                </p>
                <div class="pref-upsell-card__cta">
                  <div class="pref-price">£{{ number_format($product['price'] ?? 0, 2) }}</div>
                  @if(!empty($product['url']))
                    <a href="{{ $product['url'] }}" target="_blank" rel="noopener" class="btn-wow btn-wow--ghost btn-sm">View</a>
                  @endif
                </div>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @if(!empty($articles))
    <section class="section pref-articles">
      <div class="container-page">
        <div class="pref-articles__head">
          <div>
            <p class="kicker">Mindful Times</p>
            <h2>Don’t miss out on fresh reads</h2>
            <p class="lead">Stories, interviews, and practical tools from the Mindful Times desk.</p>
          </div>
          <a href="https://times.weofferwellness.co.uk" target="_blank" rel="noopener" class="btn-wow btn-wow--outline btn-sm">Visit Mindful Times</a>
        </div>
        <div class="pref-articles__grid">
          @foreach($articles as $article)
            <article class="pref-article-card">
              <a href="{{ $article['href'] ?? '#' }}" target="_blank" rel="noopener" class="pref-article-card__media">
                @if(!empty($article['img']))
                  <img src="{{ $article['img'] }}" alt="{{ $article['title'] }}">
                @endif
              </a>
              <div class="pref-article-card__body">
                <span class="pref-tag">{{ $article['tag'] ?? 'MindfulTimes' }}</span>
                <h3><a href="{{ $article['href'] ?? '#' }}" target="_blank" rel="noopener">{{ $article['title'] }}</a></h3>
                @if(!empty($article['excerpt']))
                  <p>{{ Str::limit(strip_tags($article['excerpt']), 140) }}</p>
                @endif
                <a href="{{ $article['href'] ?? '#' }}" target="_blank" rel="noopener" class="pref-article-card__link">Read story →</a>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif
</div>

<style>
  .pref-page{ background:radial-gradient(900px 320px at 20% 0%, rgba(74,136,120,.12), transparent 60%),radial-gradient(900px 240px at 80% 0%, rgba(15,23,42,.08), transparent 60%),#f6f7fb; }
  .pref-hero{ padding-top:3rem; padding-bottom:1rem; }
  .pref-hero__grid{ display:grid; gap:32px; align-items:center; }
  @media (min-width:900px){ .pref-hero__grid{ grid-template-columns:1.1fr .9fr; } }
  .pref-hero__copy h1{ font-size: clamp(2.2rem, 4vw, 3.4rem); margin-bottom:.5rem; }
  .pref-hero__cta-row{ display:flex; gap:12px; flex-wrap:wrap; margin-top:1.5rem; }
  .pref-hero__card{ background:#0b1220; color:#fff; border-radius:28px; padding:32px; box-shadow:0 40px 80px rgba(11,18,32,.25); }
  .pref-hero__eyebrow{ text-transform:uppercase; letter-spacing:.3em; font-size:12px; color:rgba(255,255,255,.65); margin-bottom:16px; }
  .pref-hero__list{ list-style:none; padding:0; margin:0 0 12px; display:flex; flex-wrap:wrap; gap:10px; }
  .pref-hero__list li{ padding:8px 14px; border-radius:999px; border:1px solid rgba(255,255,255,.25); font-weight:700; font-size:.9rem; }
  .pref-hero__note{ margin:0; color:rgba(255,255,255,.8); font-size:.95rem; }
  .pref-manage{ padding-top:0; padding-bottom:3rem; }
  .pref-manage__grid{ display:grid; gap:32px; }
  @media (min-width:1024px){ .pref-manage__grid{ grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); align-items:start; } }
  .pref-card{ background:#fff; border-radius:32px; padding:32px; box-shadow:0 40px 90px rgba(15,23,42,.08); border:1px solid rgba(15,23,42,.06); }
  .pref-card__header .kicker{ text-transform:uppercase; letter-spacing:.2em; font-size:12px; color:var(--ink-500, #6b7280); margin-bottom:6px; }
  .pref-card__header h2,.pref-card__header h3{ margin-top:0; }
  .pref-form{ display:flex; flex-direction:column; gap:22px; }
  .pref-fieldset{ display:flex; flex-direction:column; gap:12px; }
  .pref-fieldset__legend{ font-weight:700; font-size:1rem; color:#0f172a; }
  .pref-checkbox{ display:flex; gap:12px; padding:14px 16px; border:1px solid var(--ink-200,#e2e8f0); border-radius:18px; background:#f8fafc; align-items:flex-start; font-size:.95rem; }
  .pref-checkbox input{ margin-top:4px; }
  .pref-checkbox strong{ display:block; font-size:1rem; }
  .pref-checkbox small{ display:block; color:#475569; }
  .pref-input{ width:100%; border-radius:18px; border:1px solid var(--ink-200,#e2e8f0); padding:14px 16px; font-size:1rem; background:#fff; }
  .pref-chip-grid{ display:flex; flex-wrap:wrap; gap:12px; }
  .pref-chip{ border:1px solid var(--ink-200,#e2e8f0); border-radius:999px; padding:8px 14px; font-weight:600; cursor:pointer; display:inline-flex; gap:8px; align-items:center; }
  .pref-chip input{ accent-color:#0b1220; }
  .pref-form__actions{ display:flex; flex-wrap:wrap; gap:12px; align-items:center; }
  .pref-hint{ color:#64748b; font-size:.9rem; margin:0; }
  .pref-alert{ border-radius:18px; padding:12px 16px; font-weight:600; margin-bottom:18px; }
  .pref-alert--success{ background:rgba(16,185,129,.12); color:#0f5132; border:1px solid rgba(16,185,129,.3); }
  .pref-alert--error{ background:rgba(248,113,113,.12); color:#991b1b; border:1px solid rgba(248,113,113,.3); }
  .pref-summary-list{ list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:18px; }
  .pref-summary-list li{ padding-bottom:14px; border-bottom:1px solid rgba(15,23,42,.08); }
  .pref-summary-list span{ font-weight:700; display:block; }
  .pref-summary-list small{ color:#64748b; }
  .pref-card__footer{ margin-top:18px; padding:16px; border-radius:20px; background:#f8fafc; font-weight:600; color:#475569; }
  .pref-upsell__head,.pref-articles__head{ display:flex; flex-direction:column; gap:12px; margin-bottom:24px; }
  @media (min-width:800px){ .pref-upsell__head,.pref-articles__head{ flex-direction:row; align-items:center; justify-content:space-between; }
    .pref-upsell__head > div,.pref-articles__head > div{ max-width:640px; }
  }
  .pref-upsell__grid{ display:grid; gap:20px; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); }
  .pref-upsell-card{ border:1px solid rgba(15,23,42,.08); border-radius:28px; overflow:hidden; background:#fff; box-shadow:0 30px 70px rgba(15,23,42,.08); display:flex; flex-direction:column; }
  .pref-upsell-card__media img{ width:100%; height:200px; object-fit:cover; display:block; }
  .pref-upsell-card__body{ padding:24px; display:flex; flex-direction:column; gap:10px; flex:1; }
  .pref-tag{ display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; font-size:.75rem; text-transform:uppercase; letter-spacing:.12em; border:1px solid rgba(15,23,42,.2); color:#475569; }
  .pref-upsell-card__meta{ display:flex; flex-direction:column; gap:6px; color:#475569; font-size:.9rem; margin:0; }
  .pref-upsell-card__cta{ display:flex; justify-content:space-between; align-items:center; margin-top:auto; gap:12px; flex-wrap:wrap; }
  .pref-price{ font-size:1.4rem; font-weight:800; }
  .pref-articles__grid{ display:grid; gap:24px; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); }
  .pref-article-card{ border:1px solid rgba(15,23,42,.08); border-radius:26px; overflow:hidden; background:#fff; box-shadow:0 28px 60px rgba(15,23,42,.08); display:flex; flex-direction:column; }
  .pref-article-card__media img{ width:100%; height:200px; object-fit:cover; display:block; }
  .pref-article-card__body{ padding:24px; display:flex; flex-direction:column; gap:10px; flex:1; }
  .pref-article-card__body h3{ margin:0; font-size:1.2rem; }
  .pref-article-card__body p{ margin:0; color:#475569; }
  .pref-article-card__link{ font-weight:700; color:#0b1220; text-decoration:none; margin-top:auto; }
</style>
@endsection
