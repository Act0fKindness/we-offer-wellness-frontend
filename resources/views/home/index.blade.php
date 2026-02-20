@extends('layouts.app')

@section('title', 'We Offer Wellness® — Holistic therapy, done right')
@section('meta_description', 'New classes daily, frequent workshops & events, plus restorative retreats—led by trusted practitioners at We Offer Wellness®.')

@section('content')

    @include('home.sections.hero')
    @include('home.sections.mindful_times_ribbon')
    @include('home.sections.search_bar')
    @include('home.sections.schedule')

    {{-- Additional homepage sections (scaffolded) --}}
    @includeIf('home.sections.gifts')
    @includeIf('home.sections.comfort')
    @includeIf('home.sections.values')
    @includeIf('home.sections.reset_guide')
    @includeIf('home.sections.partners')
    @includeIf('home.sections.practitioner_chats')
    @includeIf('home.sections.mindful_times_more')
    @includeIf('home.sections.newsletter_or_card')

@endsection

