@extends('layouts.app')

@php
    $status = (int) ($status ?? 500);
    $content = [
        400 => ['label' => 'Bad request', 'title' => 'That request didn’t quite work.', 'message' => 'Please check the link or information you entered and try again.'],
        401 => ['label' => 'Sign in required', 'title' => 'You need to sign in to continue.', 'message' => 'This page is available to members with the right access.'],
        403 => ['label' => 'Access denied', 'title' => 'You don’t have access to this page.', 'message' => 'The page exists, but it isn’t available with your current permissions.'],
        404 => ['label' => 'Page not found', 'title' => 'We can’t find that page.', 'message' => 'The link may be out of date, or the page may have moved.'],
        405 => ['label' => 'Method not allowed', 'title' => 'That action isn’t available here.', 'message' => 'Please return to the previous page and try again.'],
        408 => ['label' => 'Request timed out', 'title' => 'That took a little too long.', 'message' => 'Please try again when you’re ready.'],
        419 => ['label' => 'Session expired', 'title' => 'Your session has expired.', 'message' => 'Please refresh the page and try again.'],
        429 => ['label' => 'Too many requests', 'title' => 'Let’s take a short pause.', 'message' => 'There have been a few too many requests. Please try again in a moment.'],
        500 => ['label' => 'Something went wrong', 'title' => 'We hit a little snag.', 'message' => 'Our team has been notified. Please try again, or head back to the homepage.'],
        502 => ['label' => 'Bad gateway', 'title' => 'The service is having a moment.', 'message' => 'Please try again shortly.'],
        503 => ['label' => 'Temporarily unavailable', 'title' => 'We’re taking a moment to reset.', 'message' => 'The site should be back shortly. Please try again in a little while.'],
        504 => ['label' => 'Gateway timeout', 'title' => 'That took longer than expected.', 'message' => 'Please try again in a moment.'],
    ];
    $copy = $content[$status] ?? $content[500];
    $title = $copy['title'].' | We Offer Wellness®';
    // The normal head partial is shared with application pages. Supply safe
    // defaults so an exception never causes a second exception in the shell.
    $seo = [];
    $pageTitle = $title;
    $metaDescription = $copy['message'];
    $canonical = url('/'.$status);
    $type = $category = $categories = $landing = $location = $locationQuery = $locationSearch = null;
    $city = $county = $town = $products = $results = $offeringResults = $page = null;
@endphp

@push('styles')
<style>
    .wow-error-page { min-height: 62vh; display: grid; place-items: center; padding: 72px 20px 104px; background: #f7faf8; }
    .wow-error-card { width: min(720px, 100%); text-align: center; }
    .wow-error-code { color: #599d91; font-size: clamp(76px, 16vw, 168px); line-height: .85; font-weight: 800; letter-spacing: -.09em; margin: 0 0 28px; }
    .wow-error-label { margin: 0 0 13px; color: var(--ink-500, #61706d); text-transform: uppercase; letter-spacing: .22em; font-size: 11px; font-weight: 800; }
    .wow-error-card h1 { margin: 0 auto 16px; max-width: 620px; font-family: 'Playfair Display', Georgia, serif; font-size: clamp(32px, 5vw, 58px); line-height: 1.08; letter-spacing: -.035em; }
    .wow-error-message { max-width: 500px; margin: 0 auto; color: var(--ink-600, #61706d); font-size: 15px; line-height: 1.75; }
    .wow-error-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin-top: 30px; }
    .wow-error-button { min-height: 46px; padding: 0 21px; border: 1px solid var(--ink-900, #101b25); border-radius: 3px; display: inline-flex; align-items: center; justify-content: center; font: 700 13px Manrope, sans-serif; text-decoration: none; cursor: pointer; }
    .wow-error-button--primary { background: var(--ink-900, #101b25); color: white; }
    .wow-error-button--secondary { background: white; color: var(--ink-900, #101b25); border-color: var(--ink-200, #dce7e2); }
    .wow-error-button:hover, .wow-error-button:focus-visible { transform: translateY(-1px); }
    @media (max-width: 540px) { .wow-error-page { padding-top: 48px; padding-bottom: 72px; } .wow-error-code { margin-bottom: 24px; } }
</style>
@endpush

@section('content')
<main class="wow-error-page" role="main">
    <section class="wow-error-card" aria-labelledby="error-title">
        <p class="wow-error-code" aria-hidden="true">{{ $status }}</p>
        <p class="wow-error-label">{{ $copy['label'] }}</p>
        <h1 id="error-title">{{ $copy['title'] }}</h1>
        <p class="wow-error-message">{{ $copy['message'] }}</p>
        <div class="wow-error-actions">
            <a class="wow-error-button wow-error-button--primary" href="{{ url('/') }}">Back to homepage</a>
            <button class="wow-error-button wow-error-button--secondary" type="button" onclick="window.history.back()">Go back</button>
        </div>
    </section>
</main>
@endsection
