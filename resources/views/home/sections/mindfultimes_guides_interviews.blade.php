<section id="mindful-times" class="section">
  <div class="container-page">
    <div class="mb-8 flex items-end justify-between">
      <div>
        <div class="kicker">Mindful Times</div>
        <h2>Guides, practitioner interviews and tools to help you feel better</h2>
      </div>
      <a class="btn-wow btn-wow--outline btn-sm btn-arrow"
         href="https://times.weofferwellness.co.uk" data-loader-init="1">
        <span class="btn-label">Visit Mindful Times</span>
        <span class="btn-icon-wrap" aria-hidden="true">
          <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg>
          <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path></svg>
        </span>
        <span class="btn-spinner" aria-hidden="true"><span class="spin"></span></span>
      </a>
    </div>

    <div id="mt-wrap">
      <div class="text-muted">Loading latest stories…</div>
    </div>
  </div>

  <style>
    /* Scoped styles for the tabloid layout within #mindful-times */
    #mindful-times .tabloid-wrap{ display:grid; gap:16px; }
    #mindful-times .tabloid-hero{ position:relative; border-radius:24px; overflow:hidden; border:1px solid rgba(255,255,255,.18); background:#0b1220; box-shadow:0 18px 50px rgba(16,24,40,.22); min-height:360px; }
    #mindful-times .tabloid-hero .bg{ position:absolute; inset:0; opacity:.95 }
    #mindful-times .tabloid-hero .bg img{ width:100%; height:100%; object-fit:cover; filter:contrast(1.06) saturate(1.05); transform:scale(1.01) }
    #mindful-times .tabloid-hero::after{ content:""; position:absolute; inset:0; background: linear-gradient(180deg, rgba(0,0,0,.05) 0%, rgba(0,0,0,.55) 55%, rgba(0,0,0,.82) 100%); }
    #mindful-times .tabloid-hero .content{ position:relative; z-index:2; padding:24px; display:flex; flex-direction:column; justify-content:flex-end; gap:10px; min-height:360px }
    #mindful-times .tabloid-hero .strap{ display:flex; align-items:center; gap:10px }
    #mindful-times .tabloid-hero .bigword{ font-weight:900; letter-spacing:.06em; text-transform:uppercase; font-size: clamp(34px, 5vw, 72px); line-height:.95; margin:0; color:#fff; text-shadow:0 16px 50px rgba(0,0,0,.35) }
    #mindful-times .tabloid-hero .wow-meta{ color: rgba(255,255,255,.78) }
    #mindful-times .tabloid-hero .wow-meta .cat{ color:#9fd0ff }
    #mindful-times .tabloid-row{ display:grid; gap:14px; grid-template-columns: repeat(3, 1fr); }
    #mindful-times .tabloid-small{ display:grid; gap:10px; padding:12px; border-radius:18px; border:1px solid var(--ink-200, rgba(16,24,40,.12)); background:#fff; box-shadow:0 12px 30px rgba(16,24,40,.08) }
    #mindful-times .tabloid-small .wow-media{ height:150px; border-radius:16px; overflow:hidden; background:#f8fafc }
    #mindful-times .tabloid-small .wow-media img{ width:100%; height:100%; object-fit:cover }
    #mindful-times .wow-link{ color:inherit; text-decoration:none }
    #mindful-times .wow-h{ margin:0; font-weight:800; letter-spacing:-.02em; line-height:1.2; font-size:16px }
    #mindful-times .wow-h--hero{ font-size: clamp(28px, 3vw, 44px) }
    #mindful-times .wow-meta{ display:flex; gap:10px; align-items:center; margin-top:6px; font-size:12px; color:rgba(11,18,32,.55); flex-wrap:wrap; font-weight:700 }
    #mindful-times .wow-meta .cat{ color:#d0021b; font-weight:900 }
    #mindful-times .tag{ display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; border:1px solid rgba(16,24,40,.12); background:rgba(255,255,255,.9); font-weight:900; font-size:12px; letter-spacing:.02em; text-transform:uppercase; box-shadow:0 10px 24px rgba(16,24,40,.14) }
    #mindful-times .tag--exclusive{ color:#fff; background:#e11d48; border-color:transparent }
    @media (max-width: 992px){
      #mindful-times .tabloid-hero, #mindful-times .tabloid-hero .content{ min-height:320px }
      #mindful-times .tabloid-row{ grid-template-columns: 1fr }
      #mindful-times .tabloid-small .wow-media{ height:190px }
    }
  </style>

  <script>
    (function(){
      var mount = document.getElementById('mt-wrap');
      if(!mount) return;
      function esc(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]); }); }
      function normImg(u){
        if(!u) return '';
        try { var url = new URL(u, window.location.origin); var path = url.pathname + (url.search||''); return 'https://atease.weofferwellness.co.uk' + path; }
        catch(e){ var p = String(u||''); if(p && p.charAt(0) !== '/') p = '/' + p; return 'https://atease.weofferwellness.co.uk' + p; }
      }
      function render(items){
        if(!Array.isArray(items) || items.length===0){ mount.innerHTML = '<div class="text-muted">No stories yet. <a class="link-wow" href="https://times.weofferwellness.co.uk">Visit Mindful Times</a>.</div>'; return; }
        var list = items.slice(0,4);
        var hero = list[0];
        var rest = list.slice(1,4);
        var heroImg = hero && hero.img ? normImg(hero.img) : '';
        var tag = (hero && (hero.tag||'MindfulTimes')) || 'MindfulTimes';
        var heroHtml = hero ? (
          '<a class="wow-link tabloid-hero" href="'+esc(hero.href||'#')+'" aria-label="Featured article">'
          + '<div class="bg">'+ (heroImg ? '<img loading="lazy" src="'+esc(heroImg)+'" alt="'+esc(hero.title)+'">' : '') +'</div>'
          + '<div class="content">'
          +   '<div class="strap"><span class="tag tag--exclusive">FEATURED</span><span class="tag" style="background:rgba(255,255,255,.92)">'+esc(tag)+'</span></div>'
          +   '<p class="bigword">INSIGHT</p>'
          +   '<h3 class="wow-h wow-h--hero" style="color:#fff">'+esc(hero.title)+'</h3>'
          +   '<div class="wow-meta"><span class="cat">'+esc(tag)+'</span></div>'
          + '</div>'
          + '</a>'
        ) : '';
        function small(a){
          var src = a.img ? normImg(a.img) : '';
          return '<a class="wow-link tabloid-small" href="'+esc(a.href||'#')+'">'
            + '<div class="wow-media">'+ (src ? '<img loading="lazy" src="'+esc(src)+'" alt="'+esc(a.title)+'">' : '') +'</div>'
            + '<div><h4 class="wow-h">'+esc(a.title)+'</h4><div class="wow-meta"><span class="cat">'+esc(a.tag||'MindfulTimes')+'</span></div></div>'
            + '</a>';
        }
        var html = '<div class="tabloid-wrap">'+ heroHtml + '<div class="tabloid-row">'+ rest.map(small).join('') + '</div></div>';
        mount.innerHTML = html;
      }
      fetch('/api/articles?limit=4', { headers: { 'Accept':'application/json' }})
        .then(function(r){ return r.json(); })
        .then(render)
        .catch(function(){ mount.innerHTML = '<div class="text-muted">Couldn\'t load stories right now.</div>'; });
    })();
  </script>
</section>
