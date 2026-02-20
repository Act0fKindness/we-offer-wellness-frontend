@extends('layouts.app')

@section('content')
    {{-- Existing sections temporarily disabled for new initiative */
    {{-- @include('home.sections.search_bar') --}}
    {{-- @include('home.sections.hero') --}}
    {{-- @include('home.sections.values') --}}
    {{-- @include('home.sections.comfort') --}}
    {{-- @include('home.sections.schedule') --}}
    {{-- @include('home.sections.practitioner_chats') --}}
    {{-- @include('home.sections.gifts') --}}
    {{-- @include('home.sections.mindful_times_ribbon') --}}
    {{-- @include('home.sections.mindful_times_more') --}}
    {{-- @include('home.sections.reset_guide') --}}
    {{-- @include('home.sections.newsletter_or_card') --}}
    {{-- @include('home.sections.partners') --}}

    {{-- New homepage structure --}}
    @include('home.sections.hero')
    @include('home.sections.outcome_promise')
    @include('home.sections.how_it_works')
    @include('home.sections.browse_by_outcome')
    @include('home.sections.social_proof')
    @include('home.sections.reset_quiz')
    @include('home.sections.featured_experiences')
    @include('home.sections.transformation_stories')
    @include('home.sections.why_different')
    @include('home.sections.corporate_entry')
    @include('home.sections.studio_entry')
    @include('home.sections.limited_availability')
    @include('home.sections.faq')
    @include('home.sections.final_cta')
@endsection
