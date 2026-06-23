@once
<style>
  .product-v4-1-ghost-card-scope{
    --pv41g-line:#dde3ea;
    --pv41g-soft:#edf0f2;
    --pv41g-green:#4f9381;
    --pv41g-green-soft:#e8f5f1;
    --pv41g-blue-soft:#e8f0ff;
    --pv41g-gold-soft:#ffe5b3;
    --pv41g-border:rgba(16,24,40,.18);
    --pv41g-shadow:0 12px 34px rgba(16,24,40,.045);
    --pv41g-radius:13px;
  }

  .product-v4-1-ghost-card-scope .product-v4-1-ghost-card{
    position:relative;
    display:flex;
    flex-direction:column;
    width:280px;
    min-width:280px;
    max-width:280px;
    min-height:492px;
    overflow:hidden;
    background:#fff;
    border:1px solid var(--pv41g-border);
    border-radius:var(--pv41g-radius);
    box-shadow:var(--pv41g-shadow);
  }

  .product-v4-1-ghost-card__media{
    position:relative;
    height:176px;
    overflow:hidden;
    background:
      radial-gradient(circle at 24% 28%, rgba(79,147,129,.14), transparent 30%),
      radial-gradient(circle at 74% 70%, rgba(37,74,133,.10), transparent 32%),
      linear-gradient(135deg, #eef2f4 0%, #f8fbfd 100%);
  }

  .product-v4-1-ghost-card__media::after{
    content:"";
    position:absolute;
    inset:0 0 auto 0;
    height:72px;
    background:linear-gradient(180deg, rgba(16,24,40,.22), rgba(16,24,40,0));
  }

  .product-v4-1-ghost-card__shine,
  .product-v4-1-ghost-card__shine::before,
  .product-v4-1-ghost-card__signal,
  .product-v4-1-ghost-card__badge,
  .product-v4-1-ghost-card__title,
  .product-v4-1-ghost-card__line,
  .product-v4-1-ghost-card__chip,
  .product-v4-1-ghost-card__day,
  .product-v4-1-ghost-card__button,
  .product-v4-1-ghost-card__price-value,
  .product-v4-1-ghost-card__price-label{
    position:relative;
    overflow:hidden;
    background:var(--pv41g-soft);
  }

  .product-v4-1-ghost-card__shine,
  .product-v4-1-ghost-card__shine::before{
    position:absolute;
    inset:0;
    pointer-events:none;
  }

  .product-v4-1-ghost-card__shine::before{
    content:"";
    transform:translateX(-120%);
    background:linear-gradient(100deg, transparent 0%, rgba(255,255,255,.18) 28%, rgba(255,255,255,.82) 50%, rgba(255,255,255,.18) 72%, transparent 100%);
    animation:pv41g-shimmer 1.65s ease-in-out infinite;
  }

  .product-v4-1-ghost-card__signal::before,
  .product-v4-1-ghost-card__badge::before,
  .product-v4-1-ghost-card__title::before,
  .product-v4-1-ghost-card__line::before,
  .product-v4-1-ghost-card__chip::before,
  .product-v4-1-ghost-card__day::before,
  .product-v4-1-ghost-card__button::before,
  .product-v4-1-ghost-card__price-value::before,
  .product-v4-1-ghost-card__price-label::before{
    content:"";
    position:absolute;
    inset:0;
    transform:translateX(-120%);
    background:linear-gradient(100deg, transparent 0%, rgba(255,255,255,.16) 28%, rgba(255,255,255,.86) 50%, rgba(255,255,255,.16) 72%, transparent 100%);
    animation:pv41g-shimmer 1.65s ease-in-out infinite;
  }

  .product-v4-1-ghost-card__signal{
    position:absolute;
    top:10px;
    left:10px;
    z-index:3;
    width:110px;
    height:28px;
    border-radius:999px;
    background:rgba(255,247,237,.94);
  }

  .product-v4-1-ghost-card__badges{
    position:absolute;
    left:10px;
    bottom:10px;
    z-index:3;
    display:flex;
    flex-wrap:wrap;
    gap:6px;
  }

  .product-v4-1-ghost-card__badge{
    width:72px;
    height:26px;
    border-radius:999px;
  }

  .product-v4-1-ghost-card__badge--blue{
    width:88px;
    background:rgba(232,240,255,.96);
  }

  .product-v4-1-ghost-card__quick-view{
    position:absolute;
    right:10px;
    bottom:10px;
    z-index:3;
    width:74px;
    height:30px;
    border-radius:4px;
    background:rgba(255,255,255,.94);
  }

  .product-v4-1-ghost-card__body{
    flex:1;
    display:flex;
    flex-direction:column;
    gap:8px;
    padding:13px 14px 12px;
  }

  .product-v4-1-ghost-card__type{
    width:62px;
    height:11px;
    border-radius:999px;
    background:rgba(79,147,129,.12);
  }

  .product-v4-1-ghost-card__title{
    width:100%;
    height:45px;
    border-radius:10px;
  }

  .product-v4-1-ghost-card__provider{
    width:140px;
    height:12px;
    border-radius:999px;
  }

  .product-v4-1-ghost-card__rating{
    display:flex;
    align-items:center;
    gap:7px;
  }

  .product-v4-1-ghost-card__stars{
    display:inline-flex;
    gap:2px;
  }

  .product-v4-1-ghost-card__star{
    width:14px;
    height:14px;
    border-radius:4px;
    background:#f3d77c;
  }

  .product-v4-1-ghost-card__rating-copy{
    width:108px;
    height:12px;
    border-radius:999px;
  }

  .product-v4-1-ghost-card__summary{
    display:grid;
    gap:5px;
  }

  .product-v4-1-ghost-card__summary .product-v4-1-ghost-card__line{
    height:10px;
    border-radius:999px;
  }

  .product-v4-1-ghost-card__summary .product-v4-1-ghost-card__line:nth-child(1){ width:100%; }
  .product-v4-1-ghost-card__summary .product-v4-1-ghost-card__line:nth-child(2){ width:92%; }
  .product-v4-1-ghost-card__summary .product-v4-1-ghost-card__line:nth-child(3){ width:72%; }

  .product-v4-1-ghost-card__meta{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    min-height:26px;
  }

  .product-v4-1-ghost-card__chip{
    width:80px;
    height:27px;
    border-radius:999px;
    background:var(--pv41g-green-soft);
  }

  .product-v4-1-ghost-card__chip--blue{
    width:92px;
    background:var(--pv41g-blue-soft);
  }

  .product-v4-1-ghost-card__availability{
    margin-top:4px;
    min-height:62px;
    border:1px solid rgba(79,147,129,.24);
    border-radius:11px;
    background:linear-gradient(180deg, rgba(232,245,241,.64), rgba(255,255,255,.94));
    padding:8px;
  }

  .product-v4-1-ghost-card__availability-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:9px;
    margin-bottom:7px;
  }

  .product-v4-1-ghost-card__availability-label{
    width:112px;
    height:12px;
    border-radius:999px;
  }

  .product-v4-1-ghost-card__availability-note{
    width:68px;
    height:10px;
    border-radius:999px;
  }

  .product-v4-1-ghost-card__days{
    display:grid;
    grid-template-columns:repeat(7, minmax(0, 1fr));
    gap:3px;
  }

  .product-v4-1-ghost-card__day{
    height:21px;
    border-radius:6px;
  }

  .product-v4-1-ghost-card__footer{
    display:grid;
    gap:10px;
    padding:12px 14px;
    border-top:1px solid #edf0f2;
    background:#fff;
  }

  .product-v4-1-ghost-card__price{
    display:grid;
    gap:5px;
  }

  .product-v4-1-ghost-card__price-label{
    width:34px;
    height:10px;
    border-radius:999px;
  }

  .product-v4-1-ghost-card__price-value{
    width:78px;
    height:22px;
    border-radius:999px;
  }

  .product-v4-1-ghost-card__actions{
    display:flex;
    gap:7px;
  }

  .product-v4-1-ghost-card__button{
    flex:1;
    height:38px;
    border-radius:4px;
  }

  @keyframes pv41g-shimmer{
    100%{ transform:translateX(115%); }
  }

  @media (max-width: 620px){
    .product-v4-1-ghost-card-scope .product-v4-1-ghost-card{
      width:clamp(260px, 86vw, 280px);
      min-width:clamp(260px, 86vw, 280px);
      max-width:none;
      min-height:auto;
    }
  }
</style>
@endonce

<div class="product-v4-1-ghost-card-scope" aria-busy="true" aria-label="Loading therapy card">
  <div class="product-v4-1-ghost-card">
    <div class="product-v4-1-ghost-card__media" aria-hidden="true">
      <span class="product-v4-1-ghost-card__shine"></span>
      <span class="product-v4-1-ghost-card__signal"></span>
      <div class="product-v4-1-ghost-card__badges">
        <span class="product-v4-1-ghost-card__badge"></span>
        <span class="product-v4-1-ghost-card__badge product-v4-1-ghost-card__badge--blue"></span>
      </div>
      <span class="product-v4-1-ghost-card__quick-view"></span>
    </div>

    <div class="product-v4-1-ghost-card__body">
      <span class="product-v4-1-ghost-card__type" aria-hidden="true"></span>
      <span class="product-v4-1-ghost-card__title" aria-hidden="true"></span>
      <span class="product-v4-1-ghost-card__provider" aria-hidden="true"></span>

      <div class="product-v4-1-ghost-card__rating" aria-hidden="true">
        <span class="product-v4-1-ghost-card__stars">
          <span class="product-v4-1-ghost-card__star"></span>
          <span class="product-v4-1-ghost-card__star"></span>
          <span class="product-v4-1-ghost-card__star"></span>
          <span class="product-v4-1-ghost-card__star"></span>
          <span class="product-v4-1-ghost-card__star"></span>
        </span>
        <span class="product-v4-1-ghost-card__rating-copy"></span>
      </div>

      <div class="product-v4-1-ghost-card__summary" aria-hidden="true">
        <span class="product-v4-1-ghost-card__line"></span>
        <span class="product-v4-1-ghost-card__line"></span>
        <span class="product-v4-1-ghost-card__line"></span>
      </div>

      <div class="product-v4-1-ghost-card__meta" aria-hidden="true">
        <span class="product-v4-1-ghost-card__chip"></span>
        <span class="product-v4-1-ghost-card__chip product-v4-1-ghost-card__chip--blue"></span>
      </div>

      <div class="product-v4-1-ghost-card__availability" aria-hidden="true">
        <div class="product-v4-1-ghost-card__availability-top">
          <span class="product-v4-1-ghost-card__availability-label"></span>
          <span class="product-v4-1-ghost-card__availability-note"></span>
        </div>
        <div class="product-v4-1-ghost-card__days">
          @for($i = 0; $i < 7; $i++)
            <span class="product-v4-1-ghost-card__day"></span>
          @endfor
        </div>
      </div>
    </div>

    <footer class="product-v4-1-ghost-card__footer" aria-hidden="true">
      <div class="product-v4-1-ghost-card__price">
        <span class="product-v4-1-ghost-card__price-label"></span>
        <span class="product-v4-1-ghost-card__price-value"></span>
      </div>
      <div class="product-v4-1-ghost-card__actions">
        <span class="product-v4-1-ghost-card__button"></span>
        <span class="product-v4-1-ghost-card__button"></span>
      </div>
    </footer>
  </div>
</div>
