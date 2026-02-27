<section class="mt-strip" aria-label="Mindful Times updates">
    <div class="container mt-inner">
        <span class="mt-pill">Mindful Times</span>
        <p class="mt-copy">New stories, guides and practitioner advice every week.</p>
        <nav class="mt-actions" aria-label="Mindful Times links">
            <a class="mt-link" href="https://times.weofferwellness.co.uk">Read the latest</a>
            <span class="mt-dot" aria-hidden="true"></span>
            <a class="mt-link" href="https://times.weofferwellness.co.uk/seeking-wellness" style="display:inline-flex; align-items:center; gap:8px;">
                <span class="mt-mini" aria-hidden="true">
                    <img src="https://d3t3ozftmdmh3i.cloudfront.net/staging/podcast_uploaded_nologo/43081384/43081384-1742651273912-3e0476a750f9f.jpg" alt="Seeking Wellness podcast cover">
                </span>
                <span>Seeking Wellness with Tash</span>
            </a>
        </nav>
    </div>
    <div class="mt-drawer" role="region" aria-label="Mindful Times details">
        <p>New stories, guides and practitioner advice every week.</p>
        <div class="mt-mobile-actions">
            <a class="mt-btnlink" href="https://times.weofferwellness.co.uk">
                <span class="mt-btntext">
                    <span class="k">Read</span>
                    <span class="v">The latest stories & guides</span>
                </span>
            </a>
            <a class="mt-btnlink" href="https://times.weofferwellness.co.uk/seeking-wellness">
                <span class="mt-mini" aria-hidden="true">
                    <img src="https://d3t3ozftmdmh3i.cloudfront.net/staging/podcast_uploaded_nologo/43081384/43081384-1742651273912-3e0476a750f9f.jpg" alt="Seeking Wellness podcast cover">
                </span>
                <span class="mt-btntext">
                    <span class="k">Listen</span>
                    <span class="v">Seeking Wellness with Tash</span>
                </span>
            </a>
        </div>
    </div>
</section>

<style>
:root {
    --ink:#0b1220;
    --muted:rgba(11,18,32,.72);
    --wow-green:#4A8878;
    --card: rgba(255,255,255,.86);
    --stroke: rgba(11,18,32,.10);
    --shadow: 0 10px 26px rgba(0,0,0,.08);
    --radius: 3px;
    --ui: "Instrument Sans", system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial, sans-serif;
    --body: Manrope, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
.mt-strip { position:relative; border-radius: var(--radius); overflow:hidden; border:1px solid var(--stroke); box-shadow: var(--shadow); background: var(--card); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); margin-bottom: 18px; }
.mt-strip::before { content:""; position:absolute; inset:0; background:radial-gradient(800px 220px at 18% -10%, rgba(74,136,120,.10), transparent 60%), radial-gradient(700px 240px at 100% 50%, rgba(74,136,120,.07), transparent 65%); pointer-events:none; }
.mt-inner { position:relative; display:flex; align-items:center; gap:12px; padding:10px 12px; min-height:54px; }
.mt-pill { flex:0 0 auto; font-family: var(--ui); font-weight:500; letter-spacing:.08em; text-transform:uppercase; font-size:12px; padding:7px 10px; border-radius:999px; color:rgba(11,18,32,.85); background:rgba(74,136,120,.12); border:1px solid rgba(74,136,120,.18); white-space:nowrap; }
.mt-copy { flex:1 1 auto; min-width:0; font-size:14px; color:var(--muted); line-height:1.2; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.mt-actions { flex:0 0 auto; display:flex; align-items:center; gap:10px; white-space:nowrap; font-family:var(--ui); font-weight:600; font-size:14px; }
.mt-link { color:rgba(11,18,32,.86); text-decoration:none; padding:6px 8px; border-radius:10px; transition:background .15s ease, transform .15s ease; }
.mt-link:hover { background:rgba(11,18,32,.04); transform:translateY(-1px); }
.mt-link:focus-visible { outline:2px solid rgba(74,136,120,.45); outline-offset:2px; }
.mt-dot { width:4px; height:4px; border-radius:50%; background:rgba(11,18,32,.30); display:inline-block; }
.mt-mini { width:22px; height:22px; border-radius:6px; overflow:hidden; flex:0 0 auto; border:1px solid rgba(11,18,32,.10); background:rgba(11,18,32,.04); }
.mt-mini img { width:100%; height:100%; object-fit:cover; display:block; }
.mt-drawer { position:relative; display:none; border-top:1px solid rgba(11,18,32,.08); padding:10px 12px 12px; background:rgba(255,255,255,.72); }
.mt-drawer p { margin:0; color:rgba(11,18,32,.72); font-size:13px; line-height:1.35; }
.mt-mobile-actions { display:flex; flex-direction:column; gap:8px; margin-top:10px; font-family:var(--ui); }
.mt-btnlink { display:inline-flex; align-items:center; gap:10px; padding:10px 10px; border:1px solid rgba(11,18,32,.10); background:rgba(255,255,255,.84); border-radius:12px; text-decoration:none; color:rgba(11,18,32,.88); font-weight:650; transition:transform .15s ease, background .15s ease; width:100%; }
.mt-btnlink:hover { transform:translateY(-1px); background:rgba(255,255,255,.96); }
.mt-btnlink:focus-visible { outline:2px solid rgba(74,136,120,.45); outline-offset:2px; }
.mt-btntext { display:flex; flex-direction:column; line-height:1.15; min-width:0; }
.mt-btntext .k { font-size:12px; font-weight:650; color:rgba(11,18,32,.55); letter-spacing:.02em; }
.mt-btntext .v { font-size:13.5px; font-weight:750; color:rgba(11,18,32,.88); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; }
@media (max-width:680px) { .mt-actions { display:none; } .mt-copy { white-space:nowrap; } .mt-drawer { display:block; } .mt-inner { padding:10px 10px; gap:10px; } }
@media (min-width:681px) { .mt-drawer { display:none; } .mt-inner { padding:10px 14px; } }
@media (prefers-reduced-motion:reduce) { .mt-link, .mt-btnlink { transition:none !important; } }
</style>
