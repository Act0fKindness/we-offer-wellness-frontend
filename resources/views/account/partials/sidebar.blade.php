@php
    $name = trim((string) optional($accountUser)->name);
    $initials = collect(explode(' ', $name))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');
    $initials = $initials !== '' ? mb_strtoupper($initials) : 'YOU';
@endphp

<aside class="account-sidebar" aria-label="Customer account navigation">
  <div class="account-card account-user-card">
    <div class="account-avatar" aria-hidden="true">{{ $initials }}</div>
    <div>
      <div class="account-user-name">{{ optional($accountUser)->name ?? 'Customer' }}</div>
      <div class="account-user-email">{{ optional($accountUser)->email ?? '—' }}</div>
    </div>
  </div>
  <nav class="account-nav" aria-label="Account sections">
    @foreach($navItems as $item)
      @php $isActive = ($current ?? 'dashboard') === $item['slug']; @endphp
      <a href="{{ $item['href'] }}" class="account-nav__link {{ $isActive ? 'is-active' : '' }}" aria-current="{{ $isActive ? 'page' : 'false' }}">
        <span class="account-nav__icon" aria-hidden="true">{{ mb_strtoupper(mb_substr($item['label'], 0, 1)) }}</span>
        <span class="account-nav__text">
          <span class="label">{{ $item['label'] }}</span>
          <span class="hint">{{ $item['description'] }}</span>
        </span>
      </a>
    @endforeach
  </nav>
  <div class="account-card account-support">
    <p class="account-support__title">Need help?</p>
    <p class="account-support__text">Chat with our concierge team for booking changes, receipts, or personalised suggestions.</p>
    <div class="account-support__actions">
      <a class="btn-wow btn-wow--outline btn-sm" href="/contact">Message support</a>
      <a class="account-support__link" href="mailto:hello@weofferwellness.co.uk">hello@weofferwellness.co.uk</a>
    </div>
  </div>
</aside>
