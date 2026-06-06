<section class="wow-discovery-section" aria-label="Shop modalities and gift cards">
    <div class="container-page wow-container">
        <header class="wow-section-heading">
            <div>
                <p class="wow-kicker">Discover</p>
                <h2>Shop wellness by modality</h2>
                <p>Explore therapies, classes, workshops and experiences by the kind of support you are looking for.</p>
            </div>

            <a href="/therapies" class="btn-wow btn-wow--outline btn-arrow" data-loader-init="1">
                <span class="btn-label">Browse all modalities</span>
                <span class="btn-icon-wrap" aria-hidden="true">
                    <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path>
                    </svg>
                    <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path>
                    </svg>
                </span>
            </a>
        </header>

        <div class="wow-modality-board">
            <a href="{{ url('/breathwork') }}" class="wow-modality-feature" data-loader-init="1">
                <img src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1200&q=80" alt="Breathwork therapy">
                <div class="wow-modality-feature__content">
                    <span class="wow-tag wow-tag--green">Featured modality</span>
                    <h3>Breathwork</h3>
                    <p>Guided breathing sessions for calm, clarity, nervous-system support and deeper connection with yourself.</p>
                    <div class="wow-modality-feature__footer">
                        <span>Online and in-person options</span>
                        <strong>Explore breathwork &rarr;</strong>
                    </div>
                </div>
            </a>

            <div class="wow-modality-grid">
                <a href="{{ url('/sound-healing') }}" class="wow-modality-card" data-loader-init="1">
                    <div class="wow-modality-card__image">
                        <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=700&q=80" alt="Sound healing">
                    </div>
                    <div class="wow-modality-card__body">
                        <h3>Sound Healing</h3>
                        <p>Immersive sound, vibration and restorative sessions.</p>
                        <span class="wow-card-link">Browse modality &rarr;</span>
                    </div>
                </a>

                <a href="{{ url('/massage') }}" class="wow-modality-card" data-loader-init="1">
                    <div class="wow-modality-card__image">
                        <img src="https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=700&q=80" alt="Massage therapy">
                    </div>
                    <div class="wow-modality-card__body">
                        <h3>Massage Therapy</h3>
                        <p>Hands-on support for tension, recovery and rest.</p>
                        <span class="wow-card-link">Browse modality &rarr;</span>
                    </div>
                </a>

                <a href="{{ url('/yoga') }}" class="wow-modality-card" data-loader-init="1">
                    <div class="wow-modality-card__image">
                        <img src="https://images.unsplash.com/photo-1599901860904-17e6ed7083a0?auto=format&fit=crop&w=700&q=80" alt="Yoga and movement">
                    </div>
                    <div class="wow-modality-card__body">
                        <h3>Yoga &amp; Movement</h3>
                        <p>Classes and sessions for mobility, strength and calm.</p>
                        <span class="wow-card-link">Browse modality &rarr;</span>
                    </div>
                </a>

                <a href="{{ url('/coaching') }}" class="wow-modality-card" data-loader-init="1">
                    <div class="wow-modality-card__image">
                        <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=700&q=80" alt="Coaching and mindset">
                    </div>
                    <div class="wow-modality-card__body">
                        <h3>Coaching &amp; Mindset</h3>
                        <p>Guidance for confidence, clarity and personal growth.</p>
                        <span class="wow-card-link">Browse modality &rarr;</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>
