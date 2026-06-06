@once
<style>
  .product-v4-ghost-card-scope{
    --pv4g-ink:#101828;
    --pv4g-muted:#667085;
    --pv4g-line:#dde3ea;
    --pv4g-soft:#edf0f2;
    --pv4g-soft-2:#f6f8fa;
    --pv4g-green:#4f9381;
    --pv4g-green-soft:#e8f5f1;
    --pv4g-blue-soft:#e8f0ff;
    --pv4g-gold-soft:#ffe5b3;
    --pv4g-border:rgba(16,24,40,.18);
    --pv4g-shadow:0 12px 34px rgba(16,24,40,.045);
    --pv4g-radius:13px !important;
  }

  .product-v4-ghost-card-scope .wow-card{
    display:block;
    border-radius:var(--pv4g-radius);
    background:#fff !important;
    border:1px solid var(--pv4g-border) !important;
    box-shadow:var(--pv4g-shadow) !important;
    overflow:hidden;
    transition:transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
  }

  .product-v4-ghost-card-scope .wow-card:hover,
  .product-v4-ghost-card-scope .wow-card:focus-within{
    transform:translateY(-3px);
    border-color:rgba(79,147,129,.42) !important;
    box-shadow:0 20px 48px rgba(16,24,40,.085) !important;
  }

  .product-v4-ghost-card-scope .product-v4-ghost-card{
    display:flex;
    flex-direction:column;
    min-height:492px;
    width:100%;
    overflow:hidden;
    background:transparent;
    border:0;
    border-radius:var(--pv4g-radius);
    box-shadow:none;
  }

  .product-v4-ghost-card__media{
    position:relative;
    height:clamp(260px, 42vw, 374px);
    overflow:hidden;
    border-radius:var(--pv4g-radius) var(--pv4g-radius) 0 0;
    background:
      radial-gradient(circle at 24% 28%, rgba(79,147,129,.14), transparent 30%),
      radial-gradient(circle at 74% 70%, rgba(37,74,133,.10), transparent 32%),
      linear-gradient(135deg, #eef2f4 0%, #f8fbfd 100%);
  }

  .product-v4-ghost-card__media::after{
    content:"";
    position:absolute;
    inset:0 0 auto 0;
    height:72px;
    pointer-events:none;
    background:linear-gradient(180deg, rgba(16,24,40,.22), rgba(16,24,40,0));
  }

  .product-v4-ghost-card__shine,
  .product-v4-ghost-card__shine::before{
    position:absolute;
    inset:0;
    pointer-events:none;
    overflow:hidden;
  }

  .product-v4-ghost-card__shine::before{
    content:"";
    transform:translateX(-120%);
    background:linear-gradient(
      100deg,
      transparent 0%,
      rgba(255,255,255,.18) 28%,
      rgba(255,255,255,.82) 50%,
      rgba(255,255,255,.18) 72%,
      transparent 100%
    );
    animation:pv4g-shimmer 1.65s ease-in-out infinite;
  }

  .product-v4-ghost-card__signal,
  .product-v4-ghost-card__favorite,
  .product-v4-ghost-card__pill,
  .product-v4-ghost-card__line,
  .product-v4-ghost-card__chip,
  .product-v4-ghost-card__day,
  .product-v4-ghost-card__button,
  .product-v4-ghost-card__price-value,
  .product-v4-ghost-card__price-label{
    position:relative;
    overflow:hidden;
    background:var(--pv4g-soft);
  }

  .product-v4-ghost-card__signal::before,
  .product-v4-ghost-card__favorite::before,
  .product-v4-ghost-card__pill::before,
  .product-v4-ghost-card__line::before,
  .product-v4-ghost-card__chip::before,
  .product-v4-ghost-card__day::before,
  .product-v4-ghost-card__button::before,
  .product-v4-ghost-card__price-value::before,
  .product-v4-ghost-card__price-label::before{
    content:"";
    position:absolute;
    inset:0;
    transform:translateX(-120%);
    background:linear-gradient(
      100deg,
      transparent 0%,
      rgba(255,255,255,.16) 28%,
      rgba(255,255,255,.86) 50%,
      rgba(255,255,255,.16) 72%,
      transparent 100%
    );
    animation:pv4g-shimmer 1.65s ease-in-out infinite;
  }

  .product-v4-ghost-card__signal,
  .product-v4-ghost-card__favorite{
    position:absolute;
    z-index:3;
    border:1px solid rgba(208,213,221,.92);
    box-shadow:0 8px 18px rgba(16,24,40,.08);
  }

  .product-v4-ghost-card__signal{
    top:10px;
    left:10px;
    width:118px;
    height:28px;
    border-radius:999px;
    background:rgba(255,247,237,.94);
  }

  .product-v4-ghost-card__favorite{
    top:10px;
    right:10px;
    width:38px;
    height:38px;
    border-radius:999px;
    background:rgba(255,255,255,.96);
  }

  .product-v4-ghost-card__badges{
    position:absolute;
    left:10px;
    bottom:10px;
    z-index:3;
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    max-width:calc(100% - 20px);
  }

  .product-v4-ghost-card__pill{
    min-height:26px;
    border-radius:999px;
    border:1px solid rgba(148,163,184,.24);
  }

  .product-v4-ghost-card__pill--gold{
    width:122px;
    background:linear-gradient(90deg, var(--pv4g-gold-soft), #fff6de);
    border-color:rgba(240,200,121,.68);
  }

  .product-v4-ghost-card__pill--blue{
    width:96px;
    background:linear-gradient(90deg, var(--pv4g-blue-soft), #f7faff);
    border-color:rgba(199,216,251,.78);
  }

  .product-v4-ghost-card__body{
    flex:1;
    display:flex;
    flex-direction:column;
    gap:10px;
    padding:13px 14px 12px;
  }

  .product-v4-ghost-card__line{
    height:23px;
    border-radius:999px;
    background:linear-gradient(90deg, #e7edf3, #f1f5f9);
  }

  .product-v4-ghost-card__title{
    min-height:45px;
  }

  .product-v4-ghost-card__title--one{
    width:86%;
  }

  .product-v4-ghost-card__title--two{
    width:72%;
  }

  .product-v4-ghost-card__provider{
    width:43%;
    height:24px;
  }

  .product-v4-ghost-card__rating{
    display:flex;
    align-items:center;
    gap:7px;
    margin-top:2px;
    margin-bottom:2px;
  }

  .product-v4-ghost-card__stars{
    display:inline-flex;
    gap:4px;
    white-space:nowrap;
  }

  .product-v4-ghost-card__star{
    width:18px;
    height:18px;
    display:inline-block;
    background:#d6dde7;
    clip-path:polygon(50% 0%, 61% 34%, 97% 35%, 68% 56%, 79% 91%, 50% 70%, 21% 91%, 32% 56%, 3% 35%, 39% 34%);
    animation:pv4g-star-pulse 1.85s ease-in-out infinite;
  }

  .product-v4-ghost-card__star:nth-child(2){ animation-delay:.1s; }
  .product-v4-ghost-card__star:nth-child(3){ animation-delay:.2s; }
  .product-v4-ghost-card__star:nth-child(4){ animation-delay:.3s; }
  .product-v4-ghost-card__star:nth-child(5){ animation-delay:.4s; opacity:.55; }

  .product-v4-ghost-card__rating-copy{
    width:170px;
    height:22px;
    margin-left:8px;
  }

  .product-v4-ghost-card__summary{
    height:24px;
  }

  .product-v4-ghost-card__summary--one{
    width:92%;
  }

  .product-v4-ghost-card__summary--two{
    width:98%;
  }

  .product-v4-ghost-card__summary--three{
    width:78%;
  }

  .product-v4-ghost-card__meta{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    min-height:26px;
    margin-top:2px;
  }

  .product-v4-ghost-card__chip{
    min-height:27px;
    border-radius:999px;
    border:1px solid rgba(148,163,184,.22);
    background:#fff;
  }

  .product-v4-ghost-card__chip--duration{
    width:76px;
  }

  .product-v4-ghost-card__chip--online{
    width:98px;
    background:linear-gradient(90deg, var(--pv4g-green-soft), #f4fbf8);
    border-color:rgba(79,147,129,.24);
  }

  .product-v4-ghost-card__chip--location{
    width:104px;
  }

  .product-v4-ghost-card__availability{
    margin-top:4px;
    min-height:62px;
    border:1px solid #e3e8ee;
    border-radius:11px;
    background:linear-gradient(180deg, rgba(232,245,241,.5), rgba(255,255,255,.96)), #fff;
    padding:8px;
  }

  .product-v4-ghost-card__availability-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:9px;
    margin-bottom:7px;
  }

  .product-v4-ghost-card__availability-label{
    width:160px;
    height:15px;
  }

  .product-v4-ghost-card__availability-note{
    width:90px;
    height:12px;
  }

  .product-v4-ghost-card__days{
    display:grid;
    grid-template-columns:repeat(7, minmax(0, 1fr));
    gap:3px;
  }

  .product-v4-ghost-card__day{
    height:21px;
    border-radius:6px;
    border:1px dashed rgba(148,163,184,.28);
    background:#fff;
  }

  .product-v4-ghost-card__day--active{
    border-style:solid;
    background:rgba(79,147,129,.15);
    border-color:rgba(79,147,129,.32);
  }

  .product-v4-ghost-card__footer{
    display:grid;
    grid-template-columns:1fr auto;
    gap:10px;
    align-items:center;
    padding:12px 14px;
    border-top:1px solid var(--pv4g-soft);
    background:#fff;
    border-radius:0 0 var(--pv4g-radius) var(--pv4g-radius);
  }

  .product-v4-ghost-card__price{
    min-width:0;
  }

  .product-v4-ghost-card__price-label{
    width:70px;
    height:20px;
    margin-bottom:10px;
    opacity:.72;
  }

  .product-v4-ghost-card__price-value{
    width:146px;
    height:42px;
    border-radius:12px;
    background:#e2e8f0;
  }

  .product-v4-ghost-card__actions{
    display:flex;
    gap:7px;
    align-items:center;
  }

  .product-v4-ghost-card__button{
    height:38px;
    border-radius:4px;
    border:1px solid rgba(16,24,40,.12);
    background:rgba(255,255,255,.92);
    box-shadow:0 10px 22px rgba(16,24,40,.07);
  }

  .product-v4-ghost-card__button--outline{
    width:112px;
  }

  .product-v4-ghost-card__button--cta{
    width:78px;
    border-color:transparent;
    background:linear-gradient(90deg, rgba(79,147,129,.82), rgba(79,147,129,.62));
  }

  @keyframes pv4g-shimmer{
    100%{ transform:translateX(120%); }
  }

  @keyframes pv4g-star-pulse{
    0%,100%{ transform:scale(1); opacity:.72; }
    50%{ transform:scale(1.08); opacity:1; }
  }

  @media (max-width: 620px){
    .product-v4-ghost-card-scope .product-v4-ghost-card{
      min-height:auto;
    }

    .product-v4-ghost-card__media{
      height:356px;
    }

    .product-v4-ghost-card__footer{
      grid-template-columns:1fr;
    }

    .product-v4-ghost-card__actions{
      width:100%;
    }

    .product-v4-ghost-card__button{
      flex:1;
      min-width:0;
    }

    .product-v4-ghost-card__button--outline,
    .product-v4-ghost-card__button--cta{
      width:auto;
    }

    .product-v4-ghost-card__rating-copy{
      width:132px;
    }

    .product-v4-ghost-card__provider{
      width:58%;
    }
  }

  @media (min-width: 621px){
    .product-v4-ghost-card-scope .wow-card.md{
      width:280px;
      max-width:280px;
      flex:0 0 280px;
      justify-self:start;
    }

    .product-v4-ghost-card-scope .product-v4-ghost-card{
      width:280px;
    }
  }

  @media (prefers-reduced-motion: reduce){
    .product-v4-ghost-card__shine::before,
    .product-v4-ghost-card__star{
      animation:none;
    }
  }
</style>
@endonce

<div class="wow-therapy-card-scope product-v4-ghost-card-scope">
  <div class="wow-card md" aria-busy="true" aria-label="Loading offering card">
    <article class="product-v4-ghost-card">
      <div class="product-v4-ghost-card__media" aria-hidden="true">
        <span class="product-v4-ghost-card__shine"></span>
        <span class="product-v4-ghost-card__signal"></span>
        <span class="product-v4-ghost-card__favorite"></span>

        <div class="product-v4-ghost-card__badges">
          <span class="product-v4-ghost-card__pill product-v4-ghost-card__pill--gold"></span>
          <span class="product-v4-ghost-card__pill product-v4-ghost-card__pill--blue"></span>
        </div>
      </div>

      <div class="product-v4-ghost-card__body">
        <span class="product-v4-ghost-card__line product-v4-ghost-card__title product-v4-ghost-card__title--one" aria-hidden="true"></span>
        <span class="product-v4-ghost-card__line product-v4-ghost-card__title product-v4-ghost-card__title--two" aria-hidden="true"></span>

        <span class="product-v4-ghost-card__line product-v4-ghost-card__provider" aria-hidden="true"></span>

        <div class="product-v4-ghost-card__rating" aria-hidden="true">
          <span class="product-v4-ghost-card__stars">
            <span class="product-v4-ghost-card__star"></span>
            <span class="product-v4-ghost-card__star"></span>
            <span class="product-v4-ghost-card__star"></span>
            <span class="product-v4-ghost-card__star"></span>
            <span class="product-v4-ghost-card__star"></span>
          </span>
          <span class="product-v4-ghost-card__line product-v4-ghost-card__rating-copy"></span>
        </div>

        <span class="product-v4-ghost-card__line product-v4-ghost-card__summary product-v4-ghost-card__summary--one" aria-hidden="true"></span>
        <span class="product-v4-ghost-card__line product-v4-ghost-card__summary product-v4-ghost-card__summary--two" aria-hidden="true"></span>
        <span class="product-v4-ghost-card__line product-v4-ghost-card__summary product-v4-ghost-card__summary--three" aria-hidden="true"></span>

        <div class="product-v4-ghost-card__meta" aria-hidden="true">
          <span class="product-v4-ghost-card__chip product-v4-ghost-card__chip--duration"></span>
          <span class="product-v4-ghost-card__chip product-v4-ghost-card__chip--online"></span>
          <span class="product-v4-ghost-card__chip product-v4-ghost-card__chip--location"></span>
        </div>

        <section class="product-v4-ghost-card__availability" aria-label="Loading availability calendar">
          <div class="product-v4-ghost-card__availability-top" aria-hidden="true">
            <span class="product-v4-ghost-card__line product-v4-ghost-card__availability-label"></span>
            <span class="product-v4-ghost-card__line product-v4-ghost-card__availability-note"></span>
          </div>

          <div class="product-v4-ghost-card__days" aria-hidden="true">
            <span class="product-v4-ghost-card__day"></span>
            <span class="product-v4-ghost-card__day"></span>
            <span class="product-v4-ghost-card__day"></span>
            <span class="product-v4-ghost-card__day"></span>
            <span class="product-v4-ghost-card__day product-v4-ghost-card__day--active"></span>
            <span class="product-v4-ghost-card__day"></span>
            <span class="product-v4-ghost-card__day"></span>
          </div>
        </section>
      </div>

      <footer class="product-v4-ghost-card__footer">
        <div class="product-v4-ghost-card__price" aria-hidden="true">
          <span class="product-v4-ghost-card__line product-v4-ghost-card__price-label"></span>
          <span class="product-v4-ghost-card__price-value"></span>
        </div>

        <div class="product-v4-ghost-card__actions" aria-hidden="true">
          <span class="product-v4-ghost-card__button product-v4-ghost-card__button--outline"></span>
          <span class="product-v4-ghost-card__button product-v4-ghost-card__button--cta"></span>
        </div>
      </footer>
    </article>
  </div>
</div>
