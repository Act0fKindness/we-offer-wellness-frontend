@extends('layouts.app')

@section('title', $title)

@section('head')
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
@endsection

@section('content')
<main class="container-page py-10">
    <h1 class="text-3xl font-semibold">{{ $title }}</h1>

    <div class="prose max-w-none mt-6">
        <p>
            This page outlines general safety guidance and common contraindications. It does not replace medical advice.
            If you’re unsure, please consult a qualified healthcare professional before booking.
        </p>

        <h2>Before you book</h2>
        <ul>
            <li>Tell your practitioner about any medical conditions, medications, injuries, or pregnancy.</li>
            <li>Stop immediately if you feel unwell, dizzy, or in pain.</li>
            <li>If you have urgent symptoms or feel unsafe, seek medical support right away.</li>
        </ul>

        <h2>Common contraindications</h2>
        <p>Add your modality-specific guidance here (breathwork, sound baths, massage, etc.).</p>
    </div>
</main>
@endsection
