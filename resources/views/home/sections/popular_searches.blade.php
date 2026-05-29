<section class="wow-popular-searches">
    <div class="container-page">
        <div class="wow-popular-searches__inner">
            <div class="wow-popular-searches__copy">
                <div class="wow-kicker">Popular searches</div>
                <h2>Fast entry points to the most useful landing pages</h2>
                <p>These are the direct paths people use when they want to browse by therapy, location or high-intent search.</p>
            </div>

            <div class="wow-popular-searches__grid">
                <a class="wow-popular-searches__card" href="/reiki-near-me">
                    <strong>Reiki near me</strong>
                    <span>Trusted Reiki practitioners and distance sessions.</span>
                </a>
                <a class="wow-popular-searches__card" href="/sound-healing-near-me">
                    <strong>Sound healing near me</strong>
                    <span>Sound baths, workshops and live sessions.</span>
                </a>
                <a class="wow-popular-searches__card" href="/holistic-therapy-near-me">
                    <strong>Holistic therapy near me</strong>
                    <span>Broad wellness search across trusted therapies.</span>
                </a>
                <a class="wow-popular-searches__card" href="/wellness-classes-near-me">
                    <strong>Wellness classes near me</strong>
                    <span>Yoga, meditation, breathwork and group sessions.</span>
                </a>
                <a class="wow-popular-searches__card" href="/holistic-therapies-uk">
                    <strong>Holistic therapies UK</strong>
                    <span>UK-wide hub for online and in-person listings.</span>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .wow-popular-searches{
        margin: 0 0 64px;
    }
    .wow-popular-searches__inner{
        display:grid;
        grid-template-columns:minmax(0,.88fr) minmax(0,1.12fr);
        gap:20px;
        padding:24px;
        border:1px solid var(--wow-line);
        border-radius:18px;
        background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.98));
        box-shadow:0 18px 54px rgba(16,24,40,.06);
    }
    .wow-popular-searches__copy h2{
        margin:0;
        color:var(--wow-ink);
        font-family:var(--wow-serif);
        font-size:clamp(30px,3.6vw,46px);
        line-height:.98;
        letter-spacing:-.055em;
    }
    .wow-popular-searches__copy p{
        margin:14px 0 0;
        color:var(--wow-muted);
        font-size:15px;
        line-height:1.55;
        max-width:58ch;
    }
    .wow-popular-searches__grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:12px;
    }
    .wow-popular-searches__card{
        display:block;
        padding:16px;
        border-radius:16px;
        border:1px solid var(--wow-soft-line);
        background:#fff;
        text-decoration:none;
        box-shadow:0 10px 26px rgba(16,24,40,.04);
    }
    .wow-popular-searches__card strong{
        display:block;
        color:var(--wow-ink);
        font-size:15px;
        line-height:1.35;
    }
    .wow-popular-searches__card span{
        display:block;
        margin-top:4px;
        color:var(--wow-muted);
        font-size:13px;
        line-height:1.45;
    }
    @media (max-width: 992px){
        .wow-popular-searches__inner,
        .wow-popular-searches__grid{
            grid-template-columns:1fr;
        }
    }
</style>
