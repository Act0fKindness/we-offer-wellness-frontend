@php
  $title = $title ?? 'Gallery';
  $imgs = collect($images ?? [])
    ->filter(fn($u) => is_string($u) && trim($u) !== '')
    ->map(function($u){
      // Replace v3.weofferwellness.co.uk with atease.weofferwellness.co.uk
      return str_replace('v3.weofferwellness.co.uk', 'atease.weofferwellness.co.uk', $u);
    })
    ->values()
    ->all();
@endphp

<section id="wowGallery" data-arrows="off" aria-label="Image gallery" tabindex="0">
  <style>
    /* Scoped styles for WOW gallery */
    #wowGallery{
      position: relative;
      overflow: hidden;
      display:block;
    }

    #wowGallery{
      --bg: #ffffff;
      --card: #ffffff;
      --border: rgba(16,24,40,.10);
      --shadow: 0 12px 30px rgba(16,24,40,.10);
      --radius: 18px;
      --gap: 14px;

      /* Arrow theme */
      --arrowSize: 46px;
      --arrowIcon: rgba(11,18,32,.82);
      --arrowGlass: rgba(255,255,255,.78);
      --arrowBorder: rgba(255,255,255,.35);
      --arrowShadow: 0 14px 34px rgba(16,24,40,.18);
      --arrowShadowHover: 0 18px 44px rgba(16,24,40,.22);
      --arrowRing: rgba(46,125,90,.35);

      /* Edge glow/fade */
      --edgeFade: rgba(250,250,250,1);
      --edgeWidth: 90px;
    }

    #wowGallery .viewport{ overflow:hidden; border-radius:22px; }
    #wowGallery .track{ display:flex; width:100%; transition: transform 420ms cubic-bezier(.2,.9,.2,1); will-change: transform; }
    #wowGallery .page{ flex: 0 0 100%; padding: 18px; }

    /* Mosaic layout */
    #wowGallery .mosaic{ display:grid; grid-template-columns: 1.7fr 1fr; gap: var(--gap); align-items:stretch; }
    #wowGallery .tile{ position:relative; border-radius: var(--radius); overflow:hidden; border:1px solid var(--border); background:#f2f4f7; min-height:140px; }
    #wowGallery .tile img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transform:scale(1); transition: transform 420ms ease; }
    #wowGallery .tile:hover img{ transform: scale(1.03); }
    #wowGallery .tile--big{ min-height: 320px; }
    #wowGallery .rightGrid{ display:grid; grid-template-columns: 1fr 1.05fr; grid-template-rows: 1fr 1fr; gap: var(--gap); min-height:320px; }
    #wowGallery .tile--r1{ grid-column:1; grid-row:1; }
    #wowGallery .tile--r2{ grid-column:1; grid-row:2; }
    #wowGallery .tile--tall{ grid-column:2; grid-row:1 / span 2; }

    /* Arrows */
    #wowGallery .arrow{
      position:absolute; top:50%; transform: translateY(-50%); width: var(--arrowSize); height: var(--arrowSize);
      border-radius:999px; background: linear-gradient(180deg, rgba(255,255,255,.92), var(--arrowGlass));
      border:1px solid var(--arrowBorder); box-shadow: var(--arrowShadow); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
      display:grid; place-items:center; cursor:pointer; user-select:none; z-index:10;
      transition: transform 220ms cubic-bezier(.2,.9,.2,1), box-shadow 220ms cubic-bezier(.2,.9,.2,1), opacity 180ms ease, filter 180ms ease;
    }
    #wowGallery .arrow:hover{ transform: translateY(-50%) scale(1.06); box-shadow: var(--arrowShadowHover); filter: brightness(1.02); }
    #wowGallery .arrow:active{ transform: translateY(-50%) scale(.98); box-shadow: 0 10px 22px rgba(16,24,40,.16); }
    #wowGallery .arrow:focus-visible{ outline:0; box-shadow: var(--arrowShadow), 0 0 0 4px var(--arrowRing); }
    #wowGallery .arrow[disabled]{ opacity:0; pointer-events:none; }
    #wowGallery .arrow--left{ left:14px; }
    #wowGallery .arrow--right{ right:14px; }
    #wowGallery .arrow svg{ width:18px; height:18px; stroke: var(--arrowIcon); transition: transform 220ms ease; }
    #wowGallery .arrow--left:hover svg{ transform: translateX(-1px); }
    #wowGallery .arrow--right:hover svg{ transform: translateX(1px); }
    #wowGallery[data-arrows="off"] .arrow{ display:none; }

    /* Edge fades */
    #wowGallery::before, #wowGallery::after{
      content:""; position:absolute; top:0; bottom:0; width: var(--edgeWidth); z-index:6; pointer-events:none; opacity:0; transition: opacity 200ms ease;
    }
    #wowGallery::before{ left:0; background: linear-gradient(90deg, var(--edgeFade), rgba(250,250,250,0)); }
    #wowGallery::after{ right:0; background: linear-gradient(270deg, var(--edgeFade), rgba(250,250,250,0)); }
    #wowGallery[data-arrows="on"]:not(.at-start)::before{ opacity:1; }
    #wowGallery[data-arrows="on"]:not(.at-end)::after{ opacity:1; }

    /* Responsive */
    @media (max-width: 860px){
      #wowGallery .page{ padding:14px; }
      #wowGallery .mosaic{ grid-template-columns:1fr; }
      #wowGallery .tile--big{ min-height:240px; }
      #wowGallery .rightGrid{ min-height:240px; }
      #wowGallery{ --arrowSize:42px; --edgeWidth:70px; }
    }
    @media (max-width: 520px){
      #wowGallery{ --gap:10px; --radius:16px; --arrowSize:40px; --edgeWidth:62px; }
      #wowGallery .tile--big{ min-height:220px; }
      #wowGallery .rightGrid{ grid-template-columns:1fr 1fr; min-height:220px; }
      #wowGallery .tile--tall{ grid-row: 1 / span 2; }
      #wowGallery .arrow--left{ left:10px; }
      #wowGallery .arrow--right{ right:10px; }
    }
    @media (prefers-reduced-motion: reduce){
      #wowGallery .track, #wowGallery .tile img, #wowGallery .arrow, #wowGallery .arrow svg{ transition:none !important; }
    }
  </style>

  <button class="arrow arrow--left" id="prevBtn" type="button" aria-label="Previous images">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14.5 5.5L8.5 12l6 6.5"/>
    </svg>
  </button>

  <div class="viewport">
    <div class="track" id="track"></div>
  </div>

  <button class="arrow arrow--right" id="nextBtn" type="button" aria-label="Next images">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9.5 5.5L15.5 12l-6 6.5"/>
    </svg>
  </button>

  <script>
    (function(){
      const images = @json($imgs);
      const altText = @json($title);

      const gallery = document.getElementById("wowGallery");
      const track = document.getElementById("track");
      const prevBtn = document.getElementById("prevBtn");
      const nextBtn = document.getElementById("nextBtn");

      if (!gallery || !track || !prevBtn || !nextBtn) return;

      let pageIndex = 0;
      let pageCount = 0;

      function chunk(array, size){
        const out = [];
        for (let i = 0; i < array.length; i += size) out.push(array.slice(i, i + size));
        return out;
      }

      function tile(url, className){
        const div = document.createElement("div");
        div.className = `tile ${className || ""}`.trim();
        const img = document.createElement("img");
        img.loading = "lazy";
        img.alt = altText || "";
        img.src = url;
        div.appendChild(img);
        return div;
      }

      function build(){
        track.innerHTML = "";

        const pages = chunk(images, 4);
        pageCount = pages.length;

        gallery.dataset.arrows = pageCount > 1 ? "on" : "off";

        pages.forEach((set) => {
          if (!set || set.length === 0) return;

          const page = document.createElement("div");
          page.className = "page";

          const mosaic = document.createElement("div");
          mosaic.className = "mosaic";

          if (set[0]) mosaic.appendChild(tile(set[0], "tile--big"));

          const right = document.createElement("div");
          right.className = "rightGrid";

          if (set[1]) right.appendChild(tile(set[1], "tile--r1"));
          if (set[2]) right.appendChild(tile(set[2], "tile--r2"));
          if (set[3]) right.appendChild(tile(set[3], "tile--tall"));

          mosaic.appendChild(right);
          page.appendChild(mosaic);
          track.appendChild(page);
        });

        pageIndex = 0;
        update();
        bindSwipe();
        observeHoverHint();
      }

      function update(){
        track.style.transform = `translateX(${-pageIndex * 100}%)`;

        const atStart = pageIndex === 0;
        const atEnd = pageIndex >= pageCount - 1;

        prevBtn.toggleAttribute("disabled", atStart);
        nextBtn.toggleAttribute("disabled", atEnd);

        gallery.classList.toggle("at-start", atStart);
        gallery.classList.toggle("at-end", atEnd);
      }

      function next(){ if (pageIndex < pageCount - 1){ pageIndex++; update(); } }
      function prev(){ if (pageIndex > 0){ pageIndex--; update(); } }

      prevBtn.addEventListener("click", prev);
      nextBtn.addEventListener("click", next);

      gallery.addEventListener("keydown", (e) => {
        if (e.key === "ArrowRight") next();
        if (e.key === "ArrowLeft") prev();
      });

      let cleanupSwipe = null;
      function bindSwipe(){
        if (cleanupSwipe) cleanupSwipe();

        let startX = 0, startY = 0, dragging = false;

        const onDown = (e) => {
          const p = "touches" in e ? e.touches[0] : e;
          startX = p.clientX; startY = p.clientY; dragging = true;
        };
        const onMove = (e) => {
          if (!dragging) return;
          const p = "touches" in e ? e.touches[0] : e;
          const dx = p.clientX - startX;
          const dy = p.clientY - startY;
          if (Math.abs(dy) > Math.abs(dx)) return;
          if ("touches" in e) e.preventDefault();
        };
        const onUp = (e) => {
          if (!dragging) return;
          dragging = false;
          const p = "changedTouches" in e ? e.changedTouches[0] : e;
          const dx = p.clientX - startX;
          if (dx < -50) next();
          if (dx > 50) prev();
        };
        const opts = { passive: false };
        gallery.addEventListener("mousedown", onDown);
        gallery.addEventListener("mousemove", onMove);
        gallery.addEventListener("mouseup", onUp);
        gallery.addEventListener("mouseleave", onUp);
        gallery.addEventListener("touchstart", onDown, opts);
        gallery.addEventListener("touchmove", onMove, opts);
        gallery.addEventListener("touchend", onUp, opts);
        cleanupSwipe = () => {
          gallery.removeEventListener("mousedown", onDown);
          gallery.removeEventListener("mousemove", onMove);
          gallery.removeEventListener("mouseup", onUp);
          gallery.removeEventListener("mouseleave", onUp);
          gallery.removeEventListener("touchstart", onDown, opts);
          gallery.removeEventListener("touchmove", onMove, opts);
          gallery.removeEventListener("touchend", onUp, opts);
        };
      }

      function observeHoverHint(){
        if (pageCount <= 1) return;
        const key = "wow_gallery_arrow_hint_v1";
        if (localStorage.getItem(key)) return;
        let interacted = false;
        const mark = () => { interacted = true; };
        gallery.addEventListener("pointerdown", mark, { once:true });
        gallery.addEventListener("keydown", mark, { once:true });
        setTimeout(() => {
          if (interacted) return;
          nextBtn.animate([
            { transform: "translateY(-50%) scale(1)", boxShadow: "0 14px 34px rgba(16,24,40,.18)" },
            { transform: "translateY(-50%) scale(1.12)", boxShadow: "0 18px 44px rgba(16,24,40,.24)" },
            { transform: "translateY(-50%) scale(1)", boxShadow: "0 14px 34px rgba(16,24,40,.18)" }
          ], { duration: 900, easing: "cubic-bezier(.2,.9,.2,1)" });
          localStorage.setItem(key, "1");
        }, 1500);
      }

      if (images.length > 0) build();
      else gallery.style.display = 'none';
    })();
  </script>
</section>
