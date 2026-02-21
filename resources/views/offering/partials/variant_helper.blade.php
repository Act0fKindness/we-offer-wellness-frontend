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
  .wow-helper .panel { width: 340px; max-height: calc(100vh - 140px); overflow: auto; background: #fff; border: 1px solid #e5e7eb; border-left: none; border-radius: 0 10px 10px 0; box-shadow: 0 6px 24px rgba(2,6,23,.2); transform: translateX(-100%); transition: transform .25s ease; }
  .wow-helper[data-open="1"] .panel { transform: translateX(0); }
  .wow-helper[data-open="1"] .toggle { left: 340px; }
  .wow-helper .head { position: sticky; top: 0; background: #0f172a; color: #fff; padding: 10px 12px; border-radius: 0 10px 0 0; display:flex; align-items:center; gap:.5rem }
  .wow-helper .body { padding: 10px 12px; }
  .wow-helper h6 { font-size: 12px; text-transform: uppercase; letter-spacing: .12em; margin: 12px 0 6px; color:#374151 }
  .wow-helper table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .wow-helper th, .wow-helper td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top }
  .wow-helper .pill { display:inline-flex; align-items:center; gap:.25rem; padding:.15rem .45rem; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:999px; margin:0 .25rem .25rem 0; font-size: 11px; color:#111827 }
  @media (max-width: 991px){ .wow-helper { top: 80px } }
</style>

<div class="wow-helper" id="wowVariantHelper" data-open="0" aria-hidden="true">
  <div class="panel" role="complementary" aria-label="Variant helper">
    <div class="head"><i class="bi bi-database-gear"></i> <strong style="font-size:13px;">Product Data Helper</strong></div>
    <div class="body">
      <div class="small text-muted">Product ID: {{ $dbg['id'] ?? '' }}</div>
      @if(!empty($dbg['category']))
        <div class="small text-muted">Category: {{ $dbg['category']['name'] ?? '' }}</div>
      @endif

      @if(!empty($opts))
        <h6>Options</h6>
        @foreach($opts as $o)
          <div class="mb-1">
            <div class="small fw-semibold">{{ $o['name'] ?? 'Option' }}</div>
            <div class="mt-1">
              @foreach(($o['values'] ?? []) as $v)
                @php $val = is_array($v) ? ($v['value'] ?? '') : (string)$v; @endphp
                <span class="pill">{{ $val }}</span>
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
            <tr><th>ID</th><th>Options</th><th>Price</th><th>Compare</th></tr>
          </thead>
          <tbody>
          @foreach($vars as $v)
            @php
              $vp = $norm($v['price'] ?? null);
              $vc = $norm($v['compare'] ?? ($v['compare_at_price'] ?? null));
              $ops = $v['options'] ?? [];
              if (is_string($ops)) { $ops = [$ops]; }
            @endphp
            <tr>
              <td>{{ $v['id'] ?? '' }}</td>
              <td>{{ implode(' · ', array_filter(array_map('strval',$ops))) }}</td>
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
})();
</script>
