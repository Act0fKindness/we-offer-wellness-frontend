# WOW V3 — Font + Typography Style Deck (Source of Truth)

Fonts (via Google): Manrope (body), Playfair Display (section headings), Instrument Sans (UI)

Add to <head> (we already include these in Blade layouts):

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

Load the deck CSS (already wired in layouts):

<link rel="stylesheet" href="/css/wow-typography.css">

## Usage Examples

- Section heading (Playfair Display)
<div class="wow-section-kicker">Our approach</div>
<h2 class="wow-section-title">Holistic therapies, grounded in care</h2>
<p class="wow-section-lede">Evidence-informed modalities and trauma-aware practitioners are prioritised…</p>

- Landing page headline (Manrope)
<h1 class="wow-page-title">Wellness experiences that actually feel like self-care</h1>
<p class="wow-page-subtitle">Book trusted practitioners, online or near you…</p>

- Homepage hero H1 (choose editorial or modern)
<h1 class="wow-hero-title wow-hero-title--editorial">Find your calm, your way</h1>
<!-- or -->
<h1 class="wow-hero-title wow-hero-title--modern">Find your calm, your way</h1>

- Product card (Manrope title)
<div class="wow-card__type">Experience</div>
<h3 class="wow-card__title">Sound Bath &amp; Breathwork</h3>
<div class="wow-card__rating">★ 4.8 (214)</div>
<div class="wow-card__price">£55.00</div>

- Buttons + Inputs (Instrument Sans)
<label class="wow-label" for="q">Search</label>
<input id="q" class="wow-input form-control" placeholder="Try ‘sleep’ or ‘stress’">
<button class="wow-btn btn btn-primary">Book now</button>

## Notes
- Do not globally apply Playfair to all headings. Only opt-in via .wow-section-title or .wow-prose h2.
- Body text defaults to Manrope. UI text (nav, buttons, inputs) uses Instrument Sans.
- The scale is fluid (clamp). Type stays legible across breakpoints.
- One-liners: .font-display, .font-body, .font-ui; .fw-*, .lh-*, .tracking-* utilities are provided.
