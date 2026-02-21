@php
  $dbg = $product ?? [];
  $opts = $dbg['options'] ?? [];
  $vars = $dbg['variants'] ?? [];
  $norm = function($v){ if(!is_numeric($v)) return null; $n=(float)$v; if($n>=1000) $n=$n/100; return $n; };
  $fmt = function($n){ return $n===null? '' : '£'.number_format((float)$n, 2); };
@endphp

<style>
  .wow-helper { position: fixed; top: 110px; left: 0; z-index: 1040; font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; }
  .wow-helper .toggle { position: fixed; top: 118px; left: 0; width: 28px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background: #0f172a; color: #fff; border-radius: 0 8px 8px 0; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.25); z-index: 1041 }
  .wow-helper .panel { width: 460px; max-height: calc(100vh - 140px); overflow: auto; background: #fff; border: 1px solid #e5e7eb; border-left: none; border-radius: 0 10px 10px 0; box-shadow: 0 6px 24px rgba(2,6,23,.2); transform: translateX(-100%); transition: transform .25s ease; }
  .wow-helper[data-open="1"] .panel { transform: translateX(0); }
  .wow-helper[data-open="1"] .toggle { left: 460px; }
  .wow-helper .head { position: sticky; top: 0; background: #0f172a; color: #fff; padding: 10px 12px; border-radius: 0 10px 0 0; display:flex; align-items:center; gap:.5rem }
  .wow-helper .body { padding: 10px 12px; }
  .wow-helper h6 { font-size: 12px; text-transform: uppercase; letter-spacing: .12em; margin: 12px 0 6px; color:#374151 }
  .wow-helper table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .wow-helper th, .wow-helper td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top }
  .wow-helper .pill { display:inline-flex; align-items:center; gap:.25rem; padding:.15rem .45rem; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:999px; margin:0 .25rem .25rem 0; font-size: 11px; color:#111827 }
  .wow-helper .pill.active { background:#eef2ff; border-color:#c7d2fe }
  .wow-helper tr.vh-row { cursor: pointer; }
  .wow-helper tr.vh-row:hover { background: #f9fafb; }
  .wow-helper tr.vh-row.active { background:#eef2ff; }
  @media (max-width: 991px){ .wow-helper { top: 80px } }
</style>

<div class="wow-helper" id="wowVariantHelper" data-open="0" aria-hidden="true">
  <div class="panel" role="complementary" aria-label="Variant helper">
    <div class="head"><i class="bi bi-database-gear"></i> <strong style="font-size:13px;">Product Data Helper</strong></div>
    <div class="body">
      <div class="small text-muted">Product ID: {{ $dbg['id'] ?? '' }}</div>
      <div class="small text-muted">Current price: <span id="vhPrice">—</span></div>
      @if(!empty($dbg['category']))
        <div class="small text-muted">Category: {{ $dbg['category']['name'] ?? '' }}</div>
      @endif

      @if(!empty($opts))
        <h6>Options</h6>
        @foreach($opts as $oi => $o)
          <div class="mb-1" data-vh-opt-group="{{ $oi }}">
            <div class="small fw-semibold">{{ $o['name'] ?? 'Option' }}</div>
            <div class="mt-1">
              @foreach(($o['values'] ?? []) as $vi => $v)
                @php $val = is_array($v) ? ($v['value'] ?? '') : (string)$v; @endphp
                <button type="button" class="pill vh-opt {{ $vi===0 ? 'active' : '' }}" data-opt-idx="{{ $oi }}" data-opt-val="{{ $val }}">{{ $val }}</button>
              @endforeach
            </div>
          </div>
        @endforeach
      @endif

      <h6>Variants</h6>
      @if(empty($vars))
        <div class="text-muted small">No variants.</div>
      @else
        <table>
          <thead>
            <tr>
              <th>ID</th>
              @foreach($opts as $o)
                <th>{{ $o['name'] ?? 'Option' }}</th>
              @endforeach
              <th>Price</th>
              <th>Compare</th>
            </tr>
          </thead>
          <tbody>
          @foreach($vars as $v)
            @php
              $vp = $norm($v['price'] ?? null);
              $vc = $norm($v['compare'] ?? ($v['compare_at_price'] ?? null));
              $ops = $v['options'] ?? [];
              if (is_string($ops)) { $ops = [$ops]; }
            @endphp
            <tr class="vh-row" data-opts='@json($ops)'>
              <td>{{ $v['id'] ?? '' }}</td>
              @foreach($opts as $idx => $o)
                <td>{{ $ops[$idx] ?? '' }}</td>
              @endforeach
              <td>{{ $fmt($vp) }}</td>
              <td>{{ $fmt($vc) }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
  <button type="button" class="toggle" id="wowVariantToggle" aria-label="Toggle variant helper" aria-expanded="false"><i class="bi bi-chevron-right"></i></button>
</div>

<script>
(function(){
  var root = document.getElementById('wowVariantHelper');
  var btn = document.getElementById('wowVariantToggle');
  if(!root || !btn) return;
  function setOpen(open){ root.setAttribute('data-open', open ? '1':'0'); btn.setAttribute('aria-expanded', open?'true':'false'); try{ btn.querySelector('i').className = open ? 'bi bi-chevron-left' : 'bi bi-chevron-right'; }catch(e){} }
  btn.addEventListener('click', function(){ var o = root.getAttribute('data-open')==='1'; setOpen(!o); });
  // Hook row click -> select variant via event
  try{
    root.querySelectorAll('tr.vh-row').forEach(function(row){
      row.addEventListener('click', function(){
        try{
          var opts = JSON.parse(row.dataset.opts||'[]');
          document.dispatchEvent(new CustomEvent('wow:selectVariant', { detail: { options: opts } }));
        }catch(e){}
      });
    });
  }catch(e){}
  // Reflect Buy Box price in helper and selection highlighting
  try{
    var priceSpan = document.getElementById('vhPrice');
    document.addEventListener('wow:price', function(ev){ try{ var unit = ev?.detail?.unit; if(unit!=null){ priceSpan.textContent = '£'+Number(unit/100).toFixed(2); } }catch(e){} });
    document.addEventListener('wow:selected', function(ev){ try{
      var vid = ev?.detail?.variantId || null; var opts = ev?.detail?.options || [];
      // Highlight row
      root.querySelectorAll('tr.vh-row').forEach(function(tr){ tr.classList.remove('active'); });
      root.querySelectorAll('tr.vh-row').forEach(function(tr){ if(String(tr.querySelector('td')?.textContent||'').trim()===String(vid||'')) tr.classList.add('active'); });
      // Highlight option pills (loose match for locations)
      var groups = root.querySelectorAll('[data-vh-opt-group]');
      function norm(s){ return String(s||'').trim().toLowerCase().replace(/[^a-z0-9]+/g,''); }
      function loose(a,b){ var A=norm(a), B=norm(b); if(!A||!B) return A===B; if(A.includes('online')||B.includes('online')) return A.includes('online') && B.includes('online'); return A.includes(B) || B.includes(A); }
      groups.forEach(function(g){
        var idx = Number(g.getAttribute('data-vh-opt-group')||'-1'); if(idx<0) return; var cur = String(opts[idx]||'');
        g.querySelectorAll('.vh-opt').forEach(function(b){ b.classList.toggle('active', loose(b.dataset.optVal||'', cur)); });
      });
    }catch(e){} });
  }catch(e){}

  // Allow clicking option pills to change selection
  try{
    root.querySelectorAll('.vh-opt').forEach(function(b){
      b.addEventListener('click', function(){
        try{
          var idx = Number(b.dataset.optIdx||'-1'); var val = String(b.dataset.optVal||''); if(idx<0) return;
          // Build selection from current active pills
          var groups = root.querySelectorAll('[data-vh-opt-group]'); var sel=[];
          groups.forEach(function(g, gi){ var active = g.querySelector('.vh-opt.active'); sel[gi] = String(active ? active.dataset.optVal : ''); });
          sel[idx] = val; // override clicked
          // Dispatch selectVariant
          document.dispatchEvent(new CustomEvent('wow:selectVariant', { detail: { options: sel } }));
        }catch(e){}
      });
    });
  }catch(e){}
})();
</script>
