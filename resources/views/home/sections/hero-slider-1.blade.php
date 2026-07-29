{{-- Customer-focused homepage hero slide. --}}

<style>
  .whero.whero--s1 {
    min-height: 100%;
    overflow: visible;
    z-index: 2;
  }

  .wow-hero-swiper,
  .wow-hero-swiper .swiper-wrapper,
  .wow-hero-swiper .swiper-slide {
    overflow: visible;
  }

  .whero.whero--s1 .container.whero-pad {
    display: flex;
    align-items: center;
    min-height: 100%;
  }

  .whero.whero--s1 .s1-copy {
    width: min(100%, 980px);
    margin: 0 auto;
    text-align: center;
  }

  .whero.whero--s1 .whero-eyebrow {
    display: inline-flex;
  }

  .whero.whero--s1 .whero-title,
  .whero.whero--s1 .whero-sub {
    margin-right: auto;
    margin-left: auto;
  }

  .whero.whero--s1 .whero-sub {
    max-width: 720px;
  }

  .whero.whero--s1 .s1-search {
    position: relative;
    z-index: 100;
    width: min(100%, 920px);
    margin: 2rem auto 0;
    text-align: left;
  }

  @media (max-width: 575.98px) {
    .whero.whero--s1 .container.whero-pad {
      padding: 84px 20px 34px !important;
    }

    .whero.whero--s1 .s1-search {
      margin-top: 1.25rem;
    }
  }
</style>

<section class="whero whero--s1" aria-labelledby="homepage-hero-title">
  <div class="whero-radial" aria-hidden="true"></div>

  <div class="container whero-pad">
    <div class="s1-copy">
      <span class="whero-eyebrow">Holistic wellbeing, all in one place</span>
      <h1 id="homepage-hero-title" class="whero-title">Find holistic therapies, events, workshops, festivals and retreats</h1>
      <p class="whero-sub mt-3">Explore trusted practitioners and experiences online or near you, then choose the support, session or event that feels right for you.</p>

      <div class="s1-search">
        <x-home-searchbar-v4 id-prefix="hero-search-v4" mobile-top-offset="var(--wow-header-offset, 0px)" />
      </div>
    </div>
  </div>
</section>
