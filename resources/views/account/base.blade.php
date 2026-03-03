@extends('layouts.app')

@php
    $accountUser = $user ?? auth()->user();
    $navItems = [
        [
            'slug' => 'dashboard',
            'label' => 'Overview',
            'description' => 'Snapshot of your activity',
            'href' => route('account.dashboard'),
            'icon' => 'home',
        ],
        [
            'slug' => 'orders',
            'label' => 'Orders & receipts',
            'description' => 'View booking history',
            'href' => route('account.orders'),
            'icon' => 'receipt',
        ],
        [
            'slug' => 'profile',
            'label' => 'Profile & contact',
            'description' => 'Name, email, preferences',
            'href' => route('profile.edit'),
            'icon' => 'user',
        ],
    ];
    $currentSlug = $current ?? 'dashboard';
@endphp

@section('content')
<section class="section account-section">
  <div class="container-page">
    <div class="account-layout">
      @include('account.partials.sidebar', [
        'navItems' => $navItems,
        'current' => $currentSlug,
        'accountUser' => $accountUser,
      ])
      <div class="account-content">
        <header class="account-page-header">
          @if(!empty($eyebrow))
            <div class="kicker">{{ $eyebrow }}</div>
          @endif
          <div class="account-page-heading">
            <h1 class="mb-1">{{ $title ?? 'Your account' }}</h1>
            @if(!empty($intro))
              <p class="lead-cart">{{ $intro }}</p>
            @endif
          </div>
        </header>
        @yield('account-content')
      </div>
    </div>
  </div>
</section>
@endsection
