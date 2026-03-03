@extends('layouts.app')

@section('content')

@include('home.sections.hero-slider')

@include('home.sections.mindful_times_ribbon')

@include('home.sections.search_bar')

@if (!empty($hasClassesThisWeek))
@include('home.sections.schedule')
@endif

@include('home.sections.gifts')

@include('home.sections.no-travel-needed')

@include('home.sections.trust-feel-safe')

@include('home.sections.our_approach')

@include('home.sections.practitioner_chats_converstions')

@include('home.sections.mindfultimes_guides_interviews')

@include('home.sections.discover_category')

@include('home.sections.gift_cards_occasion')

@endsection
