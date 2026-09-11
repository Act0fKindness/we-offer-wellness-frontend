<section class="wow-mindful-times-section" aria-label="Mindful Times articles" id="mindful-times">
  <div class="wow-mindful-container container-page">
    <header class="wow-section-heading">
      <div>
        <p class="wow-kicker">Mindful Times</p>
        <h2>Stories, interviews and practical guides for modern wellbeing</h2>
        <p>Editorial features from We Offer Wellness® - practitioner interviews, grounded guides, event stories and helpful tools for feeling better.</p>
      </div>

      <a href="https://times.weofferwellness.co.uk/" class="btn-wow btn-wow--outline btn-sm btn-arrow wow-mindful-cta" target="_blank" rel="noopener" data-loader-init="1">
        <span class="btn-label">Visit Mindful Times</span>
        <span class="btn-icon-wrap" aria-hidden="true">
          <svg class="btn-icon-hover" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg>
          <svg class="btn-icon-default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path></svg>
        </span>
      </a>
    </header>

    <div class="wow-news-board">
      <a class="wow-lead-story wow-link" href="#" target="_blank" rel="noopener" id="mindful-times-featured">
        <div class="wow-lead-media">
          <img src="" alt="" loading="lazy">
        </div>

        <div class="wow-lead-content">
          <div>
            <div class="wow-news-meta">
              <span class="wow-news-pill wow-news-pill--red">Featured</span>
              <span class="wow-news-pill">Mindful Times</span>
            </div>

            <h3>Featured story</h3>
            <p>Loading latest stories...</p>
          </div>

          <div class="wow-story-footer">
            <span>Latest from Mindful Times</span>
            <span class="wow-read-link">Read interviews &rarr;</span>
          </div>
        </div>
      </a>

      <aside class="wow-editor-list" aria-label="Editor's picks">
        <div class="wow-editor-head">
          <p class="wow-kicker">Editor’s picks</p>
          <h3>Fresh from the newsroom</h3>
          <p>Quick reads, interviews and features from the Mindful Times desk.</p>
        </div>

        <div id="mindful-times-editor-list">
          <div class="wow-editor-item" aria-hidden="true">
            <div class="wow-editor-thumb"></div>
            <div>
              <strong>Loading stories</strong>
              <span>Mindful Times</span>
            </div>
          </div>
        </div>
      </aside>
    </div>

    <div class="wow-article-grid" aria-label="Latest articles" id="mindful-times-article-grid"></div>
  </div>

  <style>
    #mindful-times.wow-mindful-times-section{
      position:relative;
      overflow:hidden;
      background: linear-gradient(180deg, rgba(255, 242, 246, 0.88), rgba(255, 248, 250, 0.72));
      margin: 48px 0 64px;
      padding: 68px 0 82px;
      border-top: 1px solid rgba(226, 169, 181, 0.38);
      border-bottom: 1px solid rgba(226, 169, 181, 0.38);
      box-shadow:
        inset 24px 0 34px -26px rgba(130, 44, 72, 0.34),
        inset -24px 0 34px -26px rgba(130, 44, 72, 0.34);
    }
    #mindful-times .wow-mindful-container{
      position:relative;
      z-index:1;
      width:min(100% - 40px, 1280px);
      margin:0 auto;
    }
#mindful-times .wow-kicker{
  margin:0 0 10px;
  color:#344054;
  font-size:13px;
  font-weight:300;
  letter-spacing:0.16em;
      text-transform:uppercase;
    }
    #mindful-times .wow-section-heading{
      display:grid;
      grid-template-columns:minmax(0, 1fr) auto;
      gap:24px;
      align-items:end;
      margin-bottom:30px;
    }
    #mindful-times .wow-section-heading h2{
      max-width:1030px;
      margin:0;
      color:#101828;
      font-family:"Playfair Display", Georgia, "Times New Roman", serif;
      font-size:clamp(46px, 6.4vw, 82px);
      font-weight:500;
      line-height:0.94;
      letter-spacing:-0.06em;
    }
    #mindful-times .wow-section-heading p{
      max-width:680px;
      margin:16px 0 0;
      color:#596275;
      font-size:17px;
      line-height:1.58;
    }
    #mindful-times .wow-mindful-cta{
      align-self:end;
      white-space:nowrap;
    }
    #mindful-times .wow-news-board{
      display:grid;
      grid-template-columns:minmax(0, 1.35fr) minmax(340px, 0.65fr);
      gap:22px;
      align-items:stretch;
      margin-bottom:22px;
    }
    #mindful-times .wow-lead-story,
    #mindful-times .wow-editor-list,
    #mindful-times .wow-article-card{
      background:rgba(255,255,255,0.98);
      border:1px solid #dfe4ea;
      box-shadow:0 14px 42px rgba(16,24,40,.055);
    }
    #mindful-times .wow-link{
      color:inherit;
      text-decoration:none;
    }
    #mindful-times .wow-lead-story{
      display:grid;
      grid-template-columns:minmax(320px, 0.95fr) minmax(0, 1.05fr);
      min-height:480px;
      border-radius:18px;
      overflow:hidden;
    }
    #mindful-times .wow-lead-media{
      position:relative;
      min-height:100%;
      background:#111827;
      overflow:hidden;
    }
    #mindful-times .wow-lead-media img,
    #mindful-times .wow-article-image img,
    #mindful-times .wow-editor-thumb img{
      width:100%;
      height:100%;
      display:block;
      object-fit:cover;
    }
    #mindful-times .wow-lead-media::after{
      content:"Featured";
      position:absolute;
      left:18px;
      top:18px;
      min-height:30px;
      display:inline-flex;
      align-items:center;
      border-radius:999px;
      background:#e60023;
      color:#fff;
      padding:0 12px;
      font-size:12px;
      font-weight:800;
      letter-spacing:0.04em;
      text-transform:uppercase;
    }
    #mindful-times .wow-lead-content{
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      padding:28px;
    }
    #mindful-times .wow-news-meta{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      align-items:center;
      margin-bottom:18px;
    }
    #mindful-times .wow-news-pill{
      min-height:28px;
      display:inline-flex;
      align-items:center;
      border-radius:999px;
      background:#f2f4f7;
      color:#344054;
      padding:0 10px;
      font-size:12px;
      font-weight:700;
    }
    #mindful-times .wow-news-pill--red{
      background:#f6d5dc;
      color:#8f1532;
      border:1px solid rgba(143, 21, 50, 0.16);
    }
    #mindful-times .wow-lead-content h3{
      margin:0;
      color:#101828;
      font-family:"Playfair Display", Georgia, "Times New Roman", serif;
      font-size:clamp(38px, 4.6vw, 64px);
      font-weight:500;
      line-height:0.96;
      letter-spacing:-0.06em;
    }
    #mindful-times .wow-lead-content p{
      max-width:560px;
      margin:18px 0 0;
      color:#596275;
      font-size:16px;
      line-height:1.58;
    }
    #mindful-times .wow-story-footer{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:18px;
      margin-top:28px;
      padding-top:18px;
      border-top:1px solid #edf0f2;
      color:#667085;
      font-size:13px;
    }
    #mindful-times .wow-read-link{
      color:#24594d;
      font-weight:800;
      white-space:nowrap;
    }
    #mindful-times .wow-editor-list{
      border-radius:18px;
      overflow:hidden;
    }
    #mindful-times .wow-editor-head{
      padding:20px;
      border-bottom:1px solid #edf0f2;
    }
    #mindful-times .wow-editor-head h3{
      margin:0;
      color:#101828;
      font-size:18px;
      line-height:1.2;
      letter-spacing:-0.03em;
    }
    #mindful-times .wow-editor-head p{
      margin:8px 0 0;
      color:#667085;
      font-size:13px;
      line-height:1.45;
    }
    #mindful-times .wow-editor-item{
      display:grid;
      grid-template-columns:76px 1fr;
      gap:14px;
      padding:16px 20px;
      border-bottom:1px solid #edf0f2;
      text-decoration:none;
      transition:background 160ms ease;
    }
    #mindful-times .wow-editor-item:last-child{ border-bottom:0; }
    #mindful-times .wow-editor-item:hover{ background:#f8fafc; }
    #mindful-times .wow-editor-thumb{
      width:76px;
      height:58px;
      overflow:hidden;
      border-radius:8px;
      background:#eef2f4;
      flex:0 0 auto;
    }
    #mindful-times .wow-editor-item strong{
      display:block;
      color:#101828;
      font-size:14px;
      line-height:1.25;
      letter-spacing:-0.02em;
    }
    #mindful-times .wow-editor-item span{
      display:block;
      margin-top:6px;
      color:#8f1532;
      font-size:12px;
      font-weight:700;
    }
    #mindful-times .wow-article-grid{
      display:grid;
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:22px;
    }
    #mindful-times .wow-article-card{
      overflow:hidden;
      border-radius:14px;
      text-decoration:none;
      transition:transform 160ms ease, border-color 160ms ease;
    }
    #mindful-times .wow-article-card:hover{
      transform:translateY(-2px);
      border-color:rgba(79, 147, 129, 0.42);
    }
    #mindful-times .wow-article-image{
      height:190px;
      overflow:hidden;
      background:#eef2f4;
    }
    #mindful-times .wow-article-body{
      padding:18px;
    }
    #mindful-times .wow-article-body h3{
      min-height:58px;
      margin:12px 0 0;
      color:#101828;
      font-size:20px;
      line-height:1.12;
      letter-spacing:-0.035em;
    }
    #mindful-times .wow-article-body p{
      margin:10px 0 0;
      color:#667085;
      font-size:14px;
      line-height:1.5;
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;
    }
    #mindful-times .wow-card-foot{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
      margin-top:18px;
      padding-top:14px;
      border-top:1px solid #edf0f2;
      color:#667085;
      font-size:13px;
    }
    #mindful-times .wow-empty{
      padding:18px 20px;
      color:#667085;
      font-size:14px;
    }
    @media (max-width:1080px){
      #mindful-times .wow-news-board,
      #mindful-times .wow-lead-story{ grid-template-columns:1fr; }
      #mindful-times .wow-lead-media{ min-height:360px; }
      #mindful-times .wow-article-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width:760px){
      #mindful-times.wow-mindful-times-section{ padding:42px 0 58px; }
      #mindful-times .wow-section-heading{
        display:block !important;
        grid-template-columns:none !important;
        align-items:start;
      }
      #mindful-times .wow-lead-story{
        grid-template-columns:1fr;
        min-height:0;
      }
      #mindful-times .wow-lead-media{
        min-height:240px;
        height:240px;
      }
      #mindful-times .wow-lead-content{
        min-height:0;
        overflow:hidden;
        padding:22px;
      }
      #mindful-times .wow-lead-content > div:first-child{
        min-height:0;
      }
      #mindful-times .wow-lead-content h3{
        display:-webkit-box;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:3;
        overflow:hidden;
      }
      #mindful-times .wow-lead-content p{
        display:-webkit-box;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:4;
        overflow:hidden;
        margin-top:14px;
      }
      #mindful-times .wow-story-footer{
        flex-direction:column;
        align-items:flex-start;
        margin-top:18px;
      }
      #mindful-times .wow-mindful-cta{ width:100%; }
    }
    @media (max-width:560px){
      #mindful-times .wow-mindful-container,
      #mindful-times .wow-page-grid{ width:min(100% - 28px, 1280px); }
      #mindful-times .wow-section-heading h2{ font-size:46px; }
      #mindful-times .wow-lead-content h3{ font-size:34px; line-height:1; }
      #mindful-times .wow-lead-media{ min-height:220px; height:220px; }
      #mindful-times .wow-article-grid{ grid-template-columns:1fr; }
    }
  </style>

  <script>
    (function(){
      var featured = document.getElementById('mindful-times-featured');
      var editorList = document.getElementById('mindful-times-editor-list');
      var articleGrid = document.getElementById('mindful-times-article-grid');
      if(!featured || !editorList || !articleGrid) return;

      function esc(s){
        return String(s || '').replace(/[&<>"']/g, function(c){
          return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
        });
      }

      function normImg(u){
        if(!u) return '';
        try {
          var url = new URL(u, window.location.origin);
          var path = url.pathname + (url.search || '');
          return 'https://atease.weofferwellness.co.uk' + path;
        } catch(e) {
          var p = String(u || '');
          if(p && p.charAt(0) !== '/') p = '/' + p;
          return 'https://atease.weofferwellness.co.uk' + p;
        }
      }

      function decodeEntities(text){
        var out = String(text || '');
        var textarea = document.createElement('textarea');
        var prev = null;
        var passes = 0;
        while (out !== prev && passes < 3) {
          prev = out;
          textarea.innerHTML = out;
          out = textarea.value || textarea.textContent || out;
          passes += 1;
        }
        return out.replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim();
      }

      function shortText(text, max){
        var clean = decodeEntities(text);
        if(!clean) return '';
        var limit = Math.max(0, parseInt(max, 10) || 0);
        if(!limit || clean.length <= limit) return clean;
        return clean.slice(0, Math.max(0, limit - 1)).trimEnd() + '…';
      }

      function firstWords(title){
        var clean = decodeEntities(title);
        if(!clean) return 'Mindful Times';
        var parts = clean.split(' ');
        return parts.slice(0, 4).join(' ');
      }

      function renderCard(item, index){
        var src = item.img ? normImg(item.img) : '';
        var title = decodeEntities(item.title || 'Untitled story');
        var tag = item.tag || 'Mindful Times';
        var href = item.href || '#';
        var lead = shortText(item.excerpt || 'Read the latest story from Mindful Times.', 88);
        var pill = index === 0 ? 'Interviews' : tag;
        return (
          '<a href="'+esc(href)+'" class="wow-article-card wow-link" target="_blank" rel="noopener">'
          + '<div class="wow-article-image">'+ (src ? '<img loading="lazy" src="'+esc(src)+'" alt="'+esc(title)+'">' : '') +'</div>'
          + '<div class="wow-article-body">'
          +   '<span class="wow-news-pill'+(index === 0 ? ' wow-news-pill--red' : '')+'">'+esc(pill)+'</span>'
          +   '<h3>'+esc(title)+'</h3>'
          +   '<p>'+esc(lead)+'</p>'
          +   '<div class="wow-card-foot"><span>Mindful Times</span><strong>Read &rarr;</strong></div>'
          + '</div>'
          + '</a>'
        );
      }

      function renderFeatured(item){
        var src = item.img ? normImg(item.img) : '';
        var title = decodeEntities(item.title || 'Featured story');
        var tag = item.tag || 'Mindful Times';
        var href = item.href || '#';
        var excerpt = shortText(item.excerpt || 'Read the latest story from Mindful Times.', 120);
        featured.href = href;
        featured.setAttribute('aria-label', 'Featured article: ' + title);
        featured.querySelector('.wow-lead-media img').src = src || '';
        featured.querySelector('.wow-lead-media img').alt = title;
        featured.querySelector('.wow-lead-content h3').textContent = title;
        featured.querySelector('.wow-lead-content p').textContent = excerpt;
        var meta = featured.querySelectorAll('.wow-news-pill');
        if(meta[0]) meta[0].textContent = 'Featured';
        if(meta[1]) meta[1].textContent = tag;
      }

      function renderEditors(items){
        var list = items.slice(1, 4);
        if(!list.length){
          editorList.innerHTML = '<div class="wow-empty">No stories yet. <a href="https://times.weofferwellness.co.uk" target="_blank" rel="noopener">Visit Mindful Times</a>.</div>';
          return;
        }
        editorList.innerHTML = list.map(function(item){
          var src = item.img ? normImg(item.img) : '';
          var title = decodeEntities(item.title || 'Untitled story');
          var tag = item.tag || 'Mindful Times';
          var href = item.href || '#';
          return (
            '<a href="'+esc(href)+'" class="wow-editor-item wow-link" target="_blank" rel="noopener">'
            + '<div class="wow-editor-thumb">'+ (src ? '<img loading="lazy" src="'+esc(src)+'" alt="'+esc(title)+'">' : '') +'</div>'
            + '<div><strong>'+esc(firstWords(title))+'</strong><span>'+esc(tag)+'</span></div>'
            + '</a>'
          );
        }).join('');
      }

      function renderGrid(items){
        var list = items.slice(0, 3);
        if(!list.length){
          articleGrid.innerHTML = '<div class="wow-empty">No stories yet. <a href="https://times.weofferwellness.co.uk" target="_blank" rel="noopener">Visit Mindful Times</a>.</div>';
          return;
        }
        articleGrid.innerHTML = list.map(renderCard).join('');
      }

      fetch('/api/articles?limit=9', { headers: { 'Accept': 'application/json' } })
        .then(function(r){ return r.json(); })
        .then(function(items){
          if(!Array.isArray(items) || items.length === 0){
            articleGrid.innerHTML = '<div class="wow-empty">No stories yet. <a href="https://times.weofferwellness.co.uk" target="_blank" rel="noopener">Visit Mindful Times</a>.</div>';
          return;
        }
          var unique = [];
          var seen = {};
          items.forEach(function(item){
            var key = String(item && (item.href || item.url || item.link || item.title || '')).trim().toLowerCase();
            if (!key) return;
            if (seen[key]) return;
            seen[key] = true;
            unique.push(item);
          });
          if (!unique.length) {
            articleGrid.innerHTML = '<div class="wow-empty">No stories yet. <a href="https://times.weofferwellness.co.uk" target="_blank" rel="noopener">Visit Mindful Times</a>.</div>';
            editorList.innerHTML = '<div class="wow-empty">No stories yet. <a href="https://times.weofferwellness.co.uk" target="_blank" rel="noopener">Visit Mindful Times</a>.</div>';
            return;
          }
          renderFeatured(unique[0]);
          renderEditors(unique.slice(1));
          renderGrid(unique.slice(4));
        })
        .catch(function(){
          articleGrid.innerHTML = '<div class="wow-empty">Couldn\'t load stories right now.</div>';
          editorList.innerHTML = '<div class="wow-empty">Couldn\'t load stories right now.</div>';
          featured.querySelector('.wow-lead-content h3').textContent = 'Couldn\'t load stories right now';
          featured.querySelector('.wow-lead-content p').textContent = 'Try again in a moment.';
          featured.querySelector('.wow-lead-media img').removeAttribute('src');
        });
    })();
  </script>
</section>
