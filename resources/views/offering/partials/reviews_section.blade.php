@php
    $offering = is_array($product ?? null) ? $product : [];
    $reviewItems = collect($offering['client_reviews'] ?? [])
        ->filter(static fn (array $review): bool => trim((string) ($review['body'] ?? '')) !== '')
        ->values();
    $reviewCount = (int) ($offering['review_count'] ?? $reviewItems->count());
    $reviewAverage = $reviewCount > 0
        ? round((float) ($offering['rating'] ?? 0), 1)
        : 0.0;
    $reviewSummary = $reviewCount > 0
        ? number_format($reviewAverage, 1) . ' · ' . $reviewCount . ' review' . ($reviewCount === 1 ? '' : 's')
        : 'Be the first to review';
    $practitionerName = trim((string) data_get($offering, 'practitioner.name', 'this practitioner'));
    if ($practitionerName === '') {
        $practitionerName = 'this practitioner';
    }
    $reviewStoreUrl = trim((string) data_get($offering, 'practitioner.review_url', ''));
    $canLeaveReview = $reviewStoreUrl !== '';
    $reviewRedirectUrl = request()->fullUrlWithQuery(['review' => 1]) . '#reviews';
    $reviewAuthMode = old('auth_mode', 'login');
    $showReviewAuthModal = $canLeaveReview && ! auth()->check() && $errors->any();
    $reviewShouldScroll = $canLeaveReview && auth()->check() && (
        request()->boolean('review')
        || $errors->has('rating')
        || $errors->has('review_text')
        || $errors->has('review_title')
    );
    $activeUserReview = auth()->check()
        ? $reviewItems->firstWhere('user_id', auth()->id())
        : null;
    $activeReviewRating = (int) ($activeUserReview['rating'] ?? 5);
    $activeReviewTitle = trim((string) ($activeUserReview['title'] ?? ''));
    $activeReviewText = trim((string) ($activeUserReview['body'] ?? ''));
    $reviewStoreUrl = $reviewStoreUrl !== '' ? rtrim($reviewStoreUrl, '/') : '';
@endphp

@if($canLeaveReview || $reviewItems->isNotEmpty())
    <section class="offering-reviews section" id="reviews">
        <div class="offering-reviews__header">
            <div>
                <p class="kicker">Reviews</p>
                <h2>Customer reviews.</h2>
                <p class="section-intro">
                    Feedback from clients who have booked sessions with {{ $practitionerName }}.
                </p>
            </div>

            <div class="offering-reviews__summary">
                <span class="offering-reviews__badge">{{ $reviewSummary }}</span>
            </div>
        </div>

        @if(session('status'))
            <div class="review-success-alert">{{ session('status') }}</div>
        @endif

        @if($canLeaveReview)
            @auth
                <div class="review-composer-card" id="offeringReviewComposerCard" aria-live="polite">
                    <div class="review-composer-head">
                        <div>
                            <p class="eyebrow">Write a review</p>
                            <h3>{{ $activeUserReview ? 'Update your review.' : 'Share your experience.' }}</h3>
                            <p>
                                {{ $activeUserReview
                                    ? 'You can update the review you already left for this practitioner.'
                                    : 'Your review will be linked to your client account.' }}
                            </p>
                        </div>
                    </div>

                    <form class="review-write-form" id="offeringReviewComposerForm" action="{{ $reviewStoreUrl }}" method="post">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $reviewRedirectUrl }}">

                        <div class="review-rating-field">
                            <label>Rating</label>
                            <input type="hidden" name="rating" id="offeringReviewRatingInput" value="{{ old('rating', $activeReviewRating) }}">
                            <div class="review-star-input" id="offeringReviewStarInput" aria-label="Choose rating">
                                @for($rating = 1; $rating <= 5; $rating++)
                                    <button class="review-star-btn{{ $rating <= old('rating', $activeReviewRating) ? ' is-active' : '' }}" type="button" data-rating="{{ $rating }}" aria-label="{{ $rating }} star{{ $rating === 1 ? '' : 's' }}">★</button>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="review-auth-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="review-field">
                            <label for="offeringReviewTitle">Review title</label>
                            <input id="offeringReviewTitle" name="review_title" type="text" value="{{ old('review_title', $activeReviewTitle) }}" placeholder="Optional headline">
                            @error('review_title')
                                <div class="review-auth-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="review-field">
                            <label for="offeringReviewBody">Your review</label>
                            <textarea id="offeringReviewBody" name="review_text" rows="5" placeholder="Share what your session was like..." required>{{ old('review_text', $activeReviewText) }}</textarea>
                            @error('review_text')
                                <div class="review-auth-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-primary review-submit" type="submit">{{ $activeUserReview ? 'Update review' : 'Post review' }}</button>

                        <p class="review-privacy-note">
                            Your review will appear under your client account.
                        </p>
                    </form>
                </div>
            @endauth

            @guest
                <div class="review-gate-card">
                    <div class="review-gate-copy">
                        <p class="eyebrow">Leave a review</p>
                        <h3>Login or create an account to leave a review.</h3>
                        <p>
                            Reviews are linked to client accounts so feedback stays trusted for people choosing a practitioner.
                        </p>
                    </div>

                    <div class="review-gate-actions">
                        <button class="btn btn-secondary" type="button" data-review-auth-open="login">Login</button>
                        <button class="btn btn-primary" type="button" data-review-auth-open="register">Create account</button>
                    </div>
                </div>
            @endguest
        @endif

        @if($reviewItems->isNotEmpty())
            <div class="review-list">
                @foreach($reviewItems->take(3) as $review)
                    @php
                        $reviewTitle = trim((string) ($review['title'] ?? ''));
                        $reviewBody = trim((string) ($review['body'] ?? ''));
                        $reviewAuthor = trim((string) ($review['author'] ?? 'Verified client'));
                        if ($reviewAuthor === '') {
                            $reviewAuthor = 'Verified client';
                        }
                        $reviewDate = trim((string) ($review['date'] ?? ''));
                        $reviewRating = max(1, min(5, (int) ($review['rating'] ?? 0)));
                    @endphp
                    <article class="card h-100 p-4 offering-review-card">
                        @if($reviewTitle !== '')
                            <p class="review-card-title">{{ $reviewTitle }}</p>
                        @endif
                        <div class="review-stars" aria-label="{{ $reviewRating }} out of 5 stars">{{ str_repeat('★', $reviewRating) }}</div>
                        <p class="review-card-body">{{ $reviewBody !== '' ? $reviewBody : 'A client rated this practitioner.' }}</p>
                        <footer>
                            {{ $reviewAuthor }}
                            @if($reviewDate !== '')
                                · {{ $reviewDate }}
                            @endif
                        </footer>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">Be the first to review.</div>
        @endif

        @if($canLeaveReview)
            <div class="review-modal-backdrop{{ $showReviewAuthModal ? ' is-open' : '' }}" id="offeringReviewAuthBackdrop"></div>

            <section class="review-modal{{ $showReviewAuthModal ? ' is-open' : '' }}" id="offeringReviewAuthModal" role="dialog" aria-label="Login or create an account to leave a review" aria-modal="true">
                <div class="review-modal-head">
                    <div>
                        <p class="eyebrow">Leave a review</p>
                        <h3>Login or create an account.</h3>
                    </div>
                    <button class="review-modal-close" type="button" id="closeOfferingReviewAuthModal" aria-label="Close review authentication modal">×</button>
                </div>

                <div class="review-modal-tabs" role="tablist" aria-label="Review authentication options">
                    <button class="review-modal-tab{{ $reviewAuthMode === 'login' ? ' is-active' : '' }}" type="button" data-review-auth-tab="login" role="tab" aria-selected="{{ $reviewAuthMode === 'login' ? 'true' : 'false' }}">Login</button>
                    <button class="review-modal-tab{{ $reviewAuthMode === 'register' ? ' is-active' : '' }}" type="button" data-review-auth-tab="register" role="tab" aria-selected="{{ $reviewAuthMode === 'register' ? 'true' : 'false' }}">Create account</button>
                </div>

                <div class="review-modal-panel{{ $reviewAuthMode === 'login' ? ' is-active' : '' }}" data-review-auth-panel="login" role="tabpanel">
                    <p>Use your existing client account to leave a review for this practitioner.</p>

                    <form class="review-auth-form" method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $reviewRedirectUrl }}">
                        <input type="hidden" name="auth_mode" value="login">

                        <div class="review-auth-field">
                            <label for="offeringReviewLoginEmail">Email</label>
                            <input id="offeringReviewLoginEmail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                            @error('email')
                                <div class="review-auth-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="review-auth-field">
                            <label for="offeringReviewLoginPassword">Password</label>
                            <input id="offeringReviewLoginPassword" name="password" type="password" autocomplete="current-password" required>
                            @error('password')
                                <div class="review-auth-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <label class="review-auth-check">
                            <input type="checkbox" name="remember">
                            <span>Keep me signed in on this device.</span>
                        </label>

                        <button class="btn btn-primary review-submit" type="submit">Login</button>
                    </form>
                </div>

                <div class="review-modal-panel{{ $reviewAuthMode === 'register' ? ' is-active' : '' }}" data-review-auth-panel="register" role="tabpanel">
                    <p>Create a client account, then you can leave a review for this practitioner.</p>

                    <form class="review-auth-form" method="POST" action="{{ route('register') }}">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $reviewRedirectUrl }}">
                        <input type="hidden" name="auth_mode" value="register">

                        <div class="review-auth-grid">
                            <div class="review-auth-field">
                                <label for="offeringReviewFirstName">First name</label>
                                <input id="offeringReviewFirstName" name="first_name" type="text" value="{{ old('first_name') }}" autocomplete="given-name" required>
                                @error('first_name')
                                    <div class="review-auth-alert">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="review-auth-field">
                                <label for="offeringReviewLastName">Last name</label>
                                <input id="offeringReviewLastName" name="last_name" type="text" value="{{ old('last_name') }}" autocomplete="family-name" required>
                                @error('last_name')
                                    <div class="review-auth-alert">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="review-auth-field">
                            <label for="offeringReviewRegisterEmail">Email</label>
                            <input id="offeringReviewRegisterEmail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                            @error('email')
                                <div class="review-auth-alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="review-auth-grid">
                            <div class="review-auth-field">
                                <label for="offeringReviewRegisterPassword">Password</label>
                                <input id="offeringReviewRegisterPassword" name="password" type="password" autocomplete="new-password" required>
                                @error('password')
                                    <div class="review-auth-alert">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="review-auth-field">
                                <label for="offeringReviewRegisterPasswordConfirmation">Confirm password</label>
                                <input id="offeringReviewRegisterPasswordConfirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                            </div>
                        </div>

                        <label class="review-auth-check">
                            <input type="checkbox" name="terms" {{ old('terms') ? 'checked' : '' }} required>
                            <span>I agree to the <a href="/terms">Terms</a> and <a href="/privacy">Privacy Policy</a>.</span>
                        </label>
                        @error('terms')
                            <div class="review-auth-alert">{{ $message }}</div>
                        @enderror

                        <button class="btn btn-primary review-submit" type="submit">Create account</button>
                    </form>
                </div>
            </section>
        @endif
    </section>
@endif

@once
    <style>
        .offering-reviews {
            margin-top: 40px;
        }

        .offering-reviews__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
        }

        .offering-reviews__summary {
            display: flex;
            justify-content: flex-end;
            flex-shrink: 0;
        }

        .offering-reviews__badge {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 5px 8px;
            border: 1px solid #cbe5da;
            border-radius: var(--radius);
            background: var(--green-soft);
            color: var(--green-dark);
            font-size: 12px;
        }

        .review-gate-card,
        .review-composer-card,
        .offering-review-card {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #ffffff;
        }

        .review-gate-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 22px;
            margin-bottom: 18px;
            background: var(--soft);
        }

        .review-composer-card {
            padding: 22px;
            margin-bottom: 18px;
            background: var(--soft);
        }

        .review-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .review-gate-copy h3,
        .review-composer-head h3,
        .review-modal-head h3 {
            margin: 0;
            color: var(--ink);
            font-size: 24px;
            line-height: 1.05;
            letter-spacing: -0.04em;
            font-weight: 500;
        }

        .review-gate-copy p,
        .review-composer-head p,
        .review-modal-body p {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.55;
        }

        .review-gate-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .review-gate-actions .btn {
            min-height: 44px;
            white-space: nowrap;
        }

        .review-write-form {
            display: grid;
            gap: 12px;
        }

        .review-rating-field {
            display: grid;
            gap: 8px;
        }

        .review-rating-field label,
        .review-auth-field label {
            color: var(--green-dark);
            font-size: 12px;
        }

        .review-star-input {
            display: inline-flex;
            width: fit-content;
            gap: 4px;
            padding: 6px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #ffffff;
        }

        .review-star-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: var(--radius);
            background: transparent;
            color: #c8d1cc;
            cursor: pointer;
            font-size: 22px;
            line-height: 1;
        }

        .review-star-btn.is-active {
            color: var(--amber);
            background: #fff8e5;
        }

        .review-field {
            display: grid;
            gap: 6px;
        }

        .review-field input,
        .review-field textarea,
        .review-auth-field input {
            width: 100%;
            min-height: 44px;
            padding: 0 12px;
            border: 1px solid var(--line-dark);
            border-radius: var(--radius);
            background: #ffffff;
            color: var(--ink);
            font: inherit;
            font-size: 14px;
            font-weight: 400;
        }

        .review-field textarea {
            min-height: 130px;
            padding: 12px;
            resize: vertical;
            line-height: 1.5;
        }

        .review-field input:focus,
        .review-field textarea:focus,
        .review-auth-field input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(84, 148, 131, 0.16);
        }

        .review-submit {
            min-height: 44px;
            width: fit-content;
        }

        .review-privacy-note {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .review-auth-alert {
            margin-top: 6px;
            color: var(--red);
            font-size: 12px;
        }

        .review-success-alert {
            margin-bottom: 18px;
            padding: 12px;
            border: 1px solid #cbe5da;
            border-radius: var(--radius);
            background: #ffffff;
            color: var(--green-dark);
            font-size: 14px;
            line-height: 1.45;
        }

        .offering-review-card {
            height: 100%;
            padding: 18px;
        }

        .review-card-title {
            margin: 0 0 8px;
            color: var(--green-dark);
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .review-card-body {
            margin: 12px 0 0;
            color: var(--ink);
            line-height: 1.6;
            font-size: 15px;
        }

        .offering-review-card footer {
            margin-top: 14px;
            color: var(--muted);
            font-size: 13px;
        }

        .review-stars {
            color: var(--amber);
            letter-spacing: 0.08em;
            font-size: 13px;
        }

        .review-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 900;
            display: none;
            background: rgba(14, 34, 27, 0.56);
        }

        .review-modal-backdrop.is-open {
            display: block;
        }

        .review-modal {
            position: fixed;
            left: 50%;
            top: 50%;
            z-index: 910;
            display: none;
            width: min(92vw, 520px);
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #ffffff;
            box-shadow: 0 24px 80px rgba(7, 29, 51, 0.22);
            transform: translate(-50%, -50%);
        }

        .review-modal.is-open {
            display: block;
        }

        .review-modal-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
            border-bottom: 1px solid var(--line);
            background: var(--soft);
        }

        .review-modal-head .eyebrow {
            margin-bottom: 6px;
        }

        .review-modal-close {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border: 1px solid var(--line-dark);
            border-radius: var(--radius);
            background: #ffffff;
            color: var(--ink);
            cursor: pointer;
            font-size: 24px;
            line-height: 1;
        }

        .review-modal-tabs {
            display: flex;
            gap: 8px;
            padding: 18px 18px 0;
        }

        .review-modal-tab {
            min-height: 40px;
            padding: 0 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius) var(--radius) 0 0;
            background: #ffffff;
            color: var(--muted);
            cursor: pointer;
            font-size: 13px;
        }

        .review-modal-tab.is-active {
            color: var(--green-dark);
            background: var(--green-soft);
            border-color: #cbe5da;
        }

        .review-modal-panel {
            display: none;
            padding: 18px;
        }

        .review-modal-panel.is-active {
            display: block;
        }

        .review-auth-form {
            display: grid;
            gap: 12px;
        }

        .review-auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .review-auth-field {
            display: grid;
            gap: 6px;
        }

        .review-auth-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: var(--muted);
            font-size: 13px;
        }

        .review-auth-check input {
            margin-top: 2px;
        }

        .review-auth-check a {
            color: var(--green-dark);
        }

        .review-auth-check a:hover {
            color: var(--green);
        }

        .review-auth-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .review-auth-actions .btn {
            min-height: 44px;
        }

        @media (max-width: 760px) {
            .offering-reviews__header,
            .review-gate-card,
            .review-modal-head {
                display: grid;
                grid-template-columns: 1fr;
            }

            .review-gate-actions {
                justify-content: flex-start;
            }

            .review-gate-actions .btn,
            .review-submit {
                width: 100%;
            }

            .review-auth-grid {
                grid-template-columns: 1fr;
            }

            .review-modal {
                left: 0;
                right: 0;
                top: auto;
                bottom: 0;
                width: auto;
                max-height: 92vh;
                overflow: auto;
                border-radius: var(--radius) var(--radius) 0 0;
                transform: none;
            }

            .review-modal-tabs {
                padding: 14px 14px 0;
            }

            .review-modal-panel,
            .review-modal-head {
                padding-left: 14px;
                padding-right: 14px;
            }
        }
    </style>

    <script>
        (function () {
            const reviewAuthModal = document.getElementById('offeringReviewAuthModal');
            const reviewAuthBackdrop = document.getElementById('offeringReviewAuthBackdrop');
            const reviewAuthOpenButtons = document.querySelectorAll('[data-review-auth-open]');
            const reviewAuthTabs = document.querySelectorAll('[data-review-auth-tab]');
            const reviewAuthPanels = document.querySelectorAll('[data-review-auth-panel]');
            const closeReviewAuthModal = document.getElementById('closeOfferingReviewAuthModal');
            const reviewComposerCard = document.getElementById('offeringReviewComposerCard');
            const reviewStarInput = document.getElementById('offeringReviewStarInput');
            const reviewRatingInput = document.getElementById('offeringReviewRatingInput');
            const reviewShouldOpenModal = @json($showReviewAuthModal);
            const reviewInitialMode = @json($reviewAuthMode);
            const reviewShouldScroll = @json($reviewShouldScroll);
            let selectedReviewRating = Number(reviewRatingInput?.value || 5);

            function setReviewAuthMode(mode) {
                const nextMode = mode === 'register' ? 'register' : 'login';

                reviewAuthTabs.forEach(tab => {
                    const isActive = tab.dataset.reviewAuthTab === nextMode;
                    tab.classList.toggle('is-active', isActive);
                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                reviewAuthPanels.forEach(panel => {
                    panel.classList.toggle('is-active', panel.dataset.reviewAuthPanel === nextMode);
                });

                if (reviewAuthModal) {
                    reviewAuthModal.dataset.activeMode = nextMode;
                }
            }

            function focusReviewAuthField(mode) {
                if (!reviewAuthModal) {
                    return;
                }

                const panel = reviewAuthModal.querySelector(`[data-review-auth-panel="${mode}"]`);
                if (!panel) {
                    return;
                }

                const firstInput = panel.querySelector('input:not([type="hidden"])');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 80);
                }
            }

            function openReviewAuthModal(mode = reviewInitialMode) {
                if (!reviewAuthModal) {
                    return;
                }

                const nextMode = mode === 'register' ? 'register' : 'login';
                setReviewAuthMode(nextMode);
                reviewAuthModal.classList.add('is-open');

                if (reviewAuthBackdrop) {
                    reviewAuthBackdrop.classList.add('is-open');
                }

                document.body.style.overflow = 'hidden';
                focusReviewAuthField(nextMode);
            }

            function closeReviewAuthModalFn() {
                if (!reviewAuthModal) {
                    return;
                }

                reviewAuthModal.classList.remove('is-open');

                if (reviewAuthBackdrop) {
                    reviewAuthBackdrop.classList.remove('is-open');
                }

                document.body.style.overflow = '';
            }

            function updateReviewStars(rating) {
                const nextRating = Math.max(1, Math.min(5, Number(rating) || 5));
                selectedReviewRating = nextRating;

                if (reviewRatingInput) {
                    reviewRatingInput.value = String(nextRating);
                }

                document.querySelectorAll('.review-star-btn').forEach(button => {
                    button.classList.toggle('is-active', Number(button.dataset.rating) <= selectedReviewRating);
                });
            }

            reviewAuthOpenButtons.forEach(button => {
                button.addEventListener('click', () => {
                    openReviewAuthModal(button.dataset.reviewAuthOpen || reviewInitialMode);
                });
            });

            reviewAuthTabs.forEach(button => {
                button.addEventListener('click', () => {
                    setReviewAuthMode(button.dataset.reviewAuthTab || 'login');
                    focusReviewAuthField(button.dataset.reviewAuthTab || 'login');
                });
            });

            if (closeReviewAuthModal) {
                closeReviewAuthModal.addEventListener('click', closeReviewAuthModalFn);
            }

            if (reviewAuthBackdrop) {
                reviewAuthBackdrop.addEventListener('click', closeReviewAuthModalFn);
            }

            if (reviewStarInput) {
                reviewStarInput.addEventListener('click', event => {
                    const button = event.target.closest('.review-star-btn');

                    if (!button) {
                        return;
                    }

                    updateReviewStars(Number(button.dataset.rating));
                });

                updateReviewStars(selectedReviewRating);
            }

            if (reviewShouldOpenModal) {
                openReviewAuthModal(reviewInitialMode);
            }

            if (reviewComposerCard && reviewShouldScroll) {
                setTimeout(() => {
                    reviewComposerCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                }, 180);
            }

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    closeReviewAuthModalFn();
                }
            });
        })();
    </script>
@endonce
