<section data-v-f43bb09d="" id="mindful-times" class="section">
    <div data-v-f43bb09d="" class="container-page">
        <div data-v-f43bb09d="" class="mb-8 flex items-end justify-between">
            <div data-v-f43bb09d="">
                <div data-v-f43bb09d="" class="kicker">Mindful Times</div>
                <h2 data-v-f43bb09d="">Guides, practitioner interviews and tools to help you feel
                    better</h2></div>
            <a data-v-f43bb09d="" class="btn-wow btn-wow--outline btn-sm btn-arrow"
               href="https://times.weofferwellness.co.uk" data-loader-init="1"><span data-v-f43bb09d=""
                                                                                     class="btn-label">Visit Mindful Times</span><span
                data-v-f43bb09d="" class="btn-icon-wrap" aria-hidden="true"><svg data-v-f43bb09d=""
                                                                                 class="btn-icon-hover"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"><path
                data-v-f43bb09d="" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path></svg><svg data-v-f43bb09d=""
                                                                                 class="btn-icon-default"
                                                                                 xmlns="http://www.w3.org/2000/svg"
                                                                                 viewBox="0 0 24 24"><path
                data-v-f43bb09d="" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M15 12l-4 4m4-4-4-4"></path></svg></span><span data-v-f43bb09d=""
                                                                                   class="btn-spinner"
                                                                                   aria-hidden="true"><span
                data-v-f43bb09d="" class="spin"></span></span></a></div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" id="mt-articles"></div>
    </div>

    <script>
    (function(){
      var mount = document.getElementById('mt-articles');
      if(!mount) return;
      function esc(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]); }); }
      function card(a){
        var img = a.img ? '<img src="'+esc(a.img)+'" alt="'+esc(a.title)+'" class="h-44 w-full object-cover rounded-t-xl">' : '';
        var tag = a.tag ? '<div class="kicker mb-2">'+esc(a.tag)+'</div>' : '';
        return '<a href="'+esc(a.href)+'" class="block overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card focus:outline-none focus:ring-2 focus:ring-emerald-500">'
          + img
          + '<div class="p-4">'+ tag +'<div class="font-semibold leading-snug">'+esc(a.title)+'</div>'
          + (a.excerpt ? '<div class="text-ink-600 mt-1 text-sm">'+esc(a.excerpt)+'</div>' : '')
          + '</div></a>';
      }
      mount.innerHTML = '<div class="text-muted">Loading latest stories…</div>';
      fetch('/api/articles?limit=6', { headers: { 'Accept':'application/json' }})
        .then(function(r){ return r.json(); })
        .then(function(items){
          if(!Array.isArray(items) || items.length===0){ mount.innerHTML = '<div class="text-muted">No stories yet. <a class="link-wow" href="https://times.weofferwellness.co.uk">Visit Mindful Times</a>.</div>'; return; }
          mount.innerHTML = items.slice(0,6).map(card).join('');
        })
        .catch(function(){ mount.innerHTML = '<div class="text-muted">Couldn\'t load stories right now.</div>'; });
    })();
    </script>
</section>
