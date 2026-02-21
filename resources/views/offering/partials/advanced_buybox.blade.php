<style>
    .buybox{position:sticky;top:24px;max-width:420px;margin-left:auto;margin-right:0}
    .buybox .card {
        background: #fff;
        backdrop-filter: saturate(1.2) blur(12px);
        -webkit-backdrop-filter: saturate(1.2) blur(12px);
        border: 1px solid #ddd;
        border-radius: 11px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .1);
        padding: 30px 25px 20px !important;
    }
    .chips{display:flex;gap:.5rem;flex-wrap:nowrap;width:100%}
    .chip{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border:1px solid rgba(0,0,0,.08);background:#fff;border-radius:16px;padding:.6rem .9rem;font-weight:600;flex:1 1 0}
    .chip.active{box-shadow:0 6px 14px rgba(16,185,129,.25);transform:translateY(-1px)}
    .price{font-size:2rem;font-weight:800;color:#0f172a}
    .compare{color:#9ca3af;text-decoration:line-through;font-size:1rem;margin-left:.5rem}
    .pills{display:flex;flex-wrap:wrap;gap:.5rem}
    .pill{border:1px solid #549483;background:#fff;border-radius:12px;padding:.6rem .9rem;font-weight:600}
    .pill[aria-checked="true"]{border-color:#549483;box-shadow:0 6px 16px rgba(0,0,0,.15)}
    .stepper{display:inline-grid;grid-template-columns:44px 64px 44px;align-items:center;border:1px solid rgba(0,0,0,.12);border-radius:14px;background:#fff}
    .stepper button{border:0;background:transparent;height:46px;font-size:20px}
    .stepper input{border:0;background:transparent;height:46px;text-align:center;font-weight:700}
    .meta{display:flex;gap:1rem;flex-wrap:wrap;color:#374151}
    .meta .item{display:flex;align-items:center;gap:.5rem;font-size:.9rem}
    .trust{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
    .trust .cell{display:flex;align-items:center;gap:.6rem;border:1px solid rgba(0,0,0,.06);background:rgba(255,255,255,.8);border-radius:12px;padding:.75rem}
    .mode-note{font-size:.85rem;color:#6b7280}
    .rating{display:flex;align-items:center;gap:.4rem}
    .btn-main{background:#549483;color:#fff;border:none}
    .btn-basket{background:#f1f3f5;color:#111827;border:1px solid #d0d5dd}
    .group-range{display:none}
    .booking-wrap{border:1px solid #e5e7eb;border-radius:12px;padding:12px;background:#fafafa}
    .badge-note{font-size:.8rem;background:#eef7f2;color:#185a44;border:1px solid #c7e5d9}
    .sel-pill{display:inline-flex;gap:.5rem;align-items:center;border:1px solid #e5e7eb;background:#fff;border-radius:999px;padding:.25rem .6rem}
    .sel-pill .edit{cursor:pointer}
    .cal-head{display:flex;align-items:center;justify-content:space-between}
    .cal-month{font-weight:700}
    .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:.5rem}
    .cal-dayname{text-transform:uppercase;font-size:.75rem;color:#6b7280}
    .cal-cell{position:relative;border:1px solid #e5e7eb;border-radius:14px;height:42px;display:flex;align-items:center;justify-content:center;background:#fff;cursor:pointer}
    .cal-cell[aria-disabled="true"]{opacity:.4;cursor:not-allowed;background:#f8f9fa}
    .cal-cell.active{outline:2px solid #549483;box-shadow:0 6px 16px rgba(0,0,0,.12)}
    .slot{border:1px solid #e5e7eb;border-radius:10px;padding:.5rem .7rem;background:#fff;cursor:pointer}
    .slot.active{border-color:#549483;outline:2px solid #549483}
    .slot.reserved{border-color:#f59e0b;background:#fffbe6;cursor:not-allowed;opacity:.9}
    .tz-pill{border:1px solid #e5e7eb;border-radius:12px;padding:.35rem .6rem;background:#fff}
    .calendar-side{border-left:1px solid #f0f2f4}
    @media (max-width: 991px){
        .buybox{display:none !important}
        #mobileBar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;position:fixed;left:0;right:0;bottom:0;z-index:1030;background:#fff;border-top:1px solid #e5e7eb;padding:.6rem .9rem;box-shadow:0 -6px 14px rgba(0,0,0,.06)}
        #mobileBar .m-left{display:flex;flex-direction:column}
        #mobileBar .price{font-size:1.35rem;margin:0}
        #mobileBar .rating{gap:.35rem;margin-top:.1rem}
        #mobileBar .rating .text-secondary{font-size:.8rem}
        #mobileBar .btn-main{padding:.6rem 1rem}
        #mobileBar .btn-basket{padding:.6rem 1rem}
        .modal-content.mobile-times .calendar-side{display:block}
        .modal-content.mobile-times .left-col{display:none !important}
        #slotList .slot{display:block;width:100%;text-align:left}
    }
    @media (min-width: 1200px){.modal-xl{--bs-modal-width: 882px;}}
    .hourglass-spin{display:inline-block;transform-origin:50% 50%;animation:hourglassFlip 1.6s cubic-bezier(.35,.11,.27,.99) infinite}
    @keyframes hourglassFlip{0%,12%{transform:rotate(0deg)}45%,55%{transform:rotate(180deg)}88%,100%{transform:rotate(360deg)}}
    #pillHoldBanner.hourglass-active{animation:bannerPulse 1.6s ease-in-out infinite}
    @keyframes bannerPulse{0%,100%{filter:none}50%{filter:drop-shadow(0 0 6px rgba(245,158,11,.45))}}
    @media (prefers-reduced-motion:reduce){.hourglass-spin,#pillHoldBanner.hourglass-active{animation:none}}
</style>

<div class="container-wrap p-3">
    <aside class="buybox" id="buybox">
        <div class="card p-3 p-md-4">
            <div class="kicker mb-1">{{ ucfirst($product['type'] ?? 'Experience') }}</div>
            <h1 class="h4 m-0">{{ $product['title'] ?? 'Offering' }}</h1>
            <div class="d-flex align-items-baseline gap-2 my-1 mt-0">
                <div class="price" id="price">£0.00</div><div class="compare" id="compare"></div>
            </div>


            <div class="rating mb-2">
                <div class="stars" id="stars"></div>
                <div class="text-secondary small" id="ratingText"></div>
            </div>

            <!-- Format control derived from Location(s) -->
            <div id="bbFormatBlock" class="mb-2" style="display:none">
                <div class="text-secondary small mb-1">Format</div>
                <div class="pills mb-2" id="bbFormatPills"></div>
            </div>

            <div id="options"></div>

            <div class="group-range mb-3" id="groupRange">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="text-secondary small mb-0" for="groupCount">Group size</label>
                    <span class="text-muted small">(3–10)</span>
                </div>
                <div class="stepper" aria-label="Group size">
                    <button type="button" id="groupDec">−</button>
                    <input id="groupCount" value="3" inputmode="numeric" aria-live="polite" aria-label="Group size">
                    <button type="button" id="groupInc">+</button>
                </div>
            </div>

            <div class="booking-wrap mt-3 mb-2">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-calendar-event"></i>
                    <div class="fw-semibold">Availability</div>
                    <span class="badge rounded-pill ms-auto badge-note">Select a date or decide later</span>
                </div>

                <div class="d-flex gap-2 mb-2" role="group" aria-label="Availability choice">
                    <button type="button" class="btn btn-outline-secondary active" id="btnBookLater">
                        <i class="bi bi-clock-history me-1"></i>Decide later
                    </button>
                    <button type="button" class="btn btn-outline-success" id="btnBookNow">
                        <i class="bi bi-check2-circle me-1"></i>Select date
                    </button>
                </div>

                <div class="small" id="bookingSelectionRow" style="display:none">
                    <span class="sel-pill">
                        <i class="bi bi-calendar2-week"></i>
                        <span id="bookingSelectionText"></span>
                        <a class="edit text-decoration-none ms-1" id="changeBooking">Change</a>
                    </span>
                </div>

                <div class="small text-warning d-flex align-items-center gap-2 mt-2" id="pillHoldBanner" style="display:none;">
                    <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                    <span>Held for <span id="pillHoldCountdown">10:00</span></span>
                </div>

                <div class="small text-secondary mt-2" id="bookNote">
                    Choose a date now, or decide later. We’ll still secure your order and you can confirm with your practitioner anytime.
                </div>

                <input type="hidden" id="bookingChoice" value="later">
                <input type="hidden" id="preferredDateValue" value="">
                <input type="hidden" id="preferredTimeValue" value="">
                <input type="hidden" id="preferredTZValue" value="">
            </div>

            <div class="mb-4 d-flex align-items-center gap-3 mt-3">
                <label class="text-secondary small">Qty</label>
                <div class="stepper">
                    <button type="button" id="dec">−</button>
                    <input id="qty" value="1" inputmode="numeric">
                    <button type="button" id="inc">+</button>
                </div>
            </div>

            <div class="d-grid gap-2 mb-2" id="ctaWrap">
                <button class="btn btn-basket btn-lg" id="addBtn">Add to basket</button>
                <button class="btn btn-main btn-lg" id="buyNow">Buy now</button>
            </div>

            <div class="meta mb-3 mt-2">
                <div class="item"><i class="bi bi-patch-check"></i><span>90-day validity</span></div>
                <div class="item"><i class="bi bi-arrow-left-right"></i><span>Free exchanges</span></div>
                <div class="item"><i class="bi bi-leaf"></i><span>Carbon-neutral delivery</span></div>
            </div>

            <div class="trust mb-2">
                <div class="cell"><i class="bi bi-shield-lock"></i><div><div class="fw-semibold">Secure checkout</div><div class="text-secondary small">256-bit SSL • PCI-DSS</div></div></div>
                <div class="cell"><i class="bi bi-truck"></i><div><div class="fw-semibold">Instantly</div><div class="text-secondary small">to your inbox</div></div></div>
            </div>
        </div>
    </aside>
</div>

<div id="mobileBar" class="d-lg-none">
    <div class="m-left">
        <div class="price" id="mPrice">£0.00</div>
        <div class="rating">
            <div class="stars" id="mStars"></div>
            <div class="text-secondary small" id="mRatingText"></div>
        </div>
    </div>
    <div class="m-right">
        <button class="btn btn-main" id="mobileAdd">Add to basket</button>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="addToast" class="toast text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex"><div class="toast-body">Added to your basket</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="configModalLabel">Customise your order</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="sheetOptions"></div>

                <div class="group-range mb-3" id="groupRangeSheet">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="text-secondary small mb-0" for="groupCountSheet">Group size</label>
                        <span class="text-muted small">(3–10)</span>
                    </div>
                    <div class="stepper" aria-label="Group size">
                        <button type="button" id="groupDecSheet">−</button>
                        <input id="groupCountSheet" value="3" inputmode="numeric" aria-live="polite" aria-label="Group size">
                        <button type="button" id="groupIncSheet">+</button>
                    </div>
                </div>

                <div class="booking-wrap">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-calendar-event"></i>
                        <div class="fw-semibold">Availability</div>
                        <span class="badge rounded-pill ms-auto badge-note">Select a date or decide later</span>
                    </div>
                    <div class="d-flex gap-2" role="group" aria-label="Availability choice (sheet)">
                        <button type="button" class="btn btn-outline-secondary active" id="sheetBookLater">
                            <i class="bi bi-clock-history me-1"></i>Decide later
                        </button>
                        <button type="button" class="btn btn-outline-success" id="sheetBookNow">
                            <i class="bi bi-check2-circle me-1"></i>Select date
                        </button>
                    </div>
                    <div class="small text-secondary mt-2" id="sheetBookingNote">Choose now or decide later — your order is still secured.</div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="me-auto small text-secondary" id="sheetSubtotal">Subtotal: £0.00</div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-main" id="sheetConfirm">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" id="bookingModalContent">
            <div class="modal-header">
                <div>
                    <div class="text-muted small">Select a Date & Time</div>
                    <h5 class="modal-title" id="bookingModalLabel">Discovery Call — We Offer Wellness™</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-lg-6 left-col">
                        <div class="cal-head mb-2">
                            <button class="btn btn-outline-secondary btn-sm" id="calPrev"><i class="bi bi-chevron-left"></i></button>
                            <div class="cal-month" id="calMonthLabel">Month YYYY</div>
                            <button class="btn btn-outline-secondary btn-sm" id="calNext"><i class="bi bi-chevron-right"></i></button>
                        </div>
                        <div class="cal-grid mb-2" id="calDayNames"></div>
                        <div class="cal-grid" id="calGrid" aria-label="Calendar dates"></div>
                        <div class="small text-secondary mt-2">
                            Time zone: <span class="tz-pill" id="tzCurrent"></span>
                            <select class="form-select form-select-sm d-inline-block ms-2" style="width:auto" id="tzSelect"></select>
                        </div>
                    </div>
                    <div class="col-lg-6 calendar-side">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <button class="btn btn-outline-secondary btn-sm d-lg-none me-1" id="mobileBack">
                                <i class="bi bi-arrow-left"></i> Back
                            </button>
                            <i class="bi bi-clock-history"></i><div class="fw-semibold">Available times</div>
                        </div>
                        <div id="slotList" class="d-flex flex-wrap gap-2"></div>
                        <hr class="my-3">
                        <div id="bookingSummary" class="small text-secondary">No date selected.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="me-auto text-secondary small" id="modalHint">Pick a date, then choose a time.</div>
                <div class="small fw-semibold text-warning" id="holdTimer" style="display:none;">
                    Holding your slot for <span id="holdCountdown">10:00</span>
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-main" id="confirmBooking" disabled>Confirm selection</button>
            </div>
        </div>
    </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Exact placeholder behaviour (demo data) from provided snippet
// Will be overridden with real product data if available from Blade.
const product={rating:4.9,ratingCount:128,options:[{name:"Format",values:["In-person","Online"]},{name:"People",values:["1 Person","2 Persons","3+ Group"]}],variants:[{id:1,options:["In-person","1 Person"],price:35000,compare:0,available:true},{id:2,options:["In-person","2 Persons"],price:65000,compare:70000,available:true},{id:3,options:["In-person","3+ Group"],price:90000,compare:0,available:true},{id:4,options:["Online","1 Person"],price:32000,compare:0,available:true},{id:5,options:["Online","2 Persons"],price:60000,compare:65000,available:true},{id:6,options:["Online","3+ Group"],price:85000,compare:0,available:true}]};
try{
  const bbOptions = @json($product['options'] ?? []);
  const bbVariants = @json($product['variants'] ?? []);
  const bbRating = @json($product['rating'] ?? null);
  const bbRatingCount = @json($product['review_count'] ?? null);
  function toPennies(v){ if(v==null) return null; let n=Number(v); if(!isFinite(n)) return null; if(n<1000) return Math.round(n*100); return Math.round(n); }
  if(Array.isArray(bbOptions) && bbOptions.length){
    product.options = bbOptions.map(function(o){
      const vals = Array.isArray(o.values) ? o.values.map(function(v){ return (v && typeof v==='object' && 'value' in v) ? (v.value||'') : String(v||'') }) : [];
      return { name: o.name || 'Option', values: vals };
    });
  }
  if(Array.isArray(bbVariants) && bbVariants.length){
    product.variants = bbVariants.map(function(v){
      const ops = Array.isArray(v.options) ? v.options : [];
      const price = toPennies(v.price ?? null);
      const compare = toPennies(v.compare ?? v.compare_at_price ?? null);
      return { id: v.id || 0, options: ops, price: price || 0, compare: compare || 0, available: true };
    });
  }
  if(bbRating!=null) product.rating = Number(bbRating)||0;
  if(bbRatingCount!=null) product.ratingCount = Number(bbRatingCount)||0;
}catch(e){}
const HOLD_MINUTES=10;const bookings={};function dateKey(d){return d.toISOString().slice(0,10)}function ensureDay(d){const k=dateKey(d);if(!bookings[k])bookings[k]={booked:new Set(),reserved:{}};return bookings[k]}(function seed(){const day=new Date(Date.UTC(2025,9,7,0,0,0));const d=ensureDay(day);d.booked.add("16:28");const reservedStart=new Date(Date.UTC(2025,9,7,16,30));d.reserved["16:30"]={until:new Date(reservedStart.getTime()+HOLD_MINUTES*60000)}})();
const state={mode:"evoucher",selected:product.options.map(o=>o.values[0]),qty:1,variant:null,groupCount:3,recur:{cadence:"none",length:1}};const priceEl=document.getElementById("price"),compareEl=document.getElementById("compare"),optionsWrap=document.getElementById("options"),addBtn=document.getElementById("addBtn"),buyNow=document.getElementById("buyNow"),qty=document.getElementById("qty"),dec=document.getElementById("dec"),inc=document.getElementById("inc"),toastEl=document.getElementById("addToast"),stars=document.getElementById("stars"),ratingText=document.getElementById("ratingText"),groupRange=document.getElementById("groupRange"),groupCount=document.getElementById("groupCount"),groupInc=document.getElementById("groupInc"),groupDec=document.getElementById("groupDec");
const mPrice=document.getElementById('mPrice'),mStars=document.getElementById('mStars'),mRatingText=document.getElementById('mRatingText'),mobileAdd=document.getElementById('mobileAdd');
const configModalEl=document.getElementById('configModal'),configModal=new bootstrap.Modal(configModalEl),sheetOptions=document.getElementById('sheetOptions'),groupRangeSheet=document.getElementById('groupRangeSheet'),groupCountSheet=document.getElementById('groupCountSheet'),groupIncSheet=document.getElementById('groupIncSheet'),groupDecSheet=document.getElementById('groupDecSheet'),sheetBookLater=document.getElementById('sheetBookLater'),sheetBookNow=document.getElementById('sheetBookNow'),sheetConfirm=document.getElementById('sheetConfirm'),sheetSubtotal=document.getElementById('sheetSubtotal');
const btnBookNow=document.getElementById('btnBookNow'),btnBookLater=document.getElementById('btnBookLater'),bookingChoice=document.getElementById('bookingChoice'),preferredDateValue=document.getElementById('preferredDateValue'),preferredTimeValue=document.getElementById('preferredTimeValue'),preferredTZValue=document.getElementById('preferredTZValue'),bookingSelectionRow=document.getElementById('bookingSelectionRow'),bookingSelectionText=document.getElementById('bookingSelectionText'),changeBooking=document.getElementById('changeBooking');
const bookingModalEl=document.getElementById('bookingModal'),bookingModal=new bootstrap.Modal(bookingModalEl),bookingModalContent=document.getElementById('bookingModalContent'),calMonthLabel=document.getElementById('calMonthLabel'),calDayNames=document.getElementById('calDayNames'),calGrid=document.getElementById('calGrid'),calPrev=document.getElementById('calPrev'),calNext=document.getElementById('calNext'),slotList=document.getElementById('slotList'),bookingSummary=document.getElementById('bookingSummary'),confirmBooking=document.getElementById('confirmBooking'),tzCurrent=document.getElementById('tzCurrent'),tzSelect=document.getElementById('tzSelect'),modalHint=document.getElementById('modalHint'),mobileBack=document.getElementById('mobileBack'),holdTimer=document.getElementById('holdTimer'),holdCountdown=document.getElementById('holdCountdown');
const pillHoldBanner=document.getElementById('pillHoldBanner'),pillHoldCountdown=document.getElementById('pillHoldCountdown'),pillHourglass=pillHoldBanner.querySelector('i.bi-hourglass-split');
function fmt(c){try{return new Intl.NumberFormat("en-GB",{style:"currency",currency:"GBP"}).format(c/100)}catch(e){return "£"+(c/100).toFixed(2)}}
function renderStars(){stars.innerHTML="";const full=Math.round(product.rating);for(let i=1;i<=5;i++){const icon=document.createElement("i");icon.className=i<=full?"bi bi-star-fill text-success":"bi bi-star text-secondary";stars.appendChild(icon)}ratingText.textContent=`${product.rating.toFixed(1)} (${product.ratingCount})`;if(mStars){mStars.innerHTML=stars.innerHTML;mRatingText.textContent=ratingText.textContent}}
function buildOptionsInto(container){
  container.innerHTML="";
  const locIdx = findLocationIndex();
  (product.options||[]).forEach((opt,optIdx)=>{
    const label=document.createElement("div"); label.className="text-secondary small mb-1"; label.textContent=opt.name; container.appendChild(label);
    const row=document.createElement("div"); row.className="pills mb-2"; row.setAttribute('data-opt-idx', String(optIdx));
    (opt.values||[]).forEach(val=>{
      const txt = String(val||'');
      const b=document.createElement("button"); b.type="button"; b.className="pill"; b.setAttribute("role","radio"); b.dataset.optIdx=String(optIdx); b.dataset.optValue=txt;
      b.setAttribute("aria-checked", txt===state.selected[optIdx] ? "true":"false"); b.textContent=txt;
      b.addEventListener("click",()=>{
        state.selected[optIdx]=txt;
        syncOptionAria(optIdx);
        if (optIdx===locIdx) { try{ syncFormatUI(); }catch(e){} }
        updateVariant();
        updateSheetSubtotal();
        if(container===sheetOptions){ groupRangeSheet.style.display=isGroup()?"block":"none" }
      });
      row.appendChild(b)
    });
    container.appendChild(row)
    // Ensure initial aria is correct
    syncOptionAria(optIdx);
  })
}

function syncOptionAria(optIdx){
  try{
    const selector = '.pills[data-opt-idx="'+String(optIdx)+'"] .pill';
    document.querySelectorAll(selector).forEach(function(p){
      const val = String(p.dataset.optValue||'');
      p.setAttribute('aria-checked', val===String(state.selected[optIdx]||'') ? 'true' : 'false');
    });
  }catch(e){}
}
function buildOptions(){optionsWrap.innerHTML="";buildOptionsInto(optionsWrap)}
function findLocationIndex(){
  var idx=-1; try{ (product.options||[]).forEach(function(o,i){ var n=String(o?.name||'').toLowerCase(); if(idx===-1 && (n.includes('location'))) idx=i; }); }catch(e){}
  return idx;
}
var __locIdxCache = null;
function syncFormatUI(){
  try{
    var locIdx = (__locIdxCache != null) ? __locIdxCache : findLocationIndex();
    if(locIdx == null || locIdx < 0) return;
    var pills = document.getElementById('bbFormatPills'); if(!pills) return;
    var cur = String(state.selected[locIdx]||'');
    var online = cur.toLowerCase()==='online';
    pills.querySelectorAll('.pill').forEach(function(p){
      var isOnline = String(p.dataset.variantLocation||'').toLowerCase()==='online';
      p.setAttribute('aria-checked', online ? (isOnline?'true':'false') : (!isOnline?'true':'false'));
    });
  }catch(e){}
}
function buildFormatBlock(){
  var block=document.getElementById('bbFormatBlock'); var pills=document.getElementById('bbFormatPills');
  if(!block||!pills) return; pills.innerHTML='';
  var locIdx=findLocationIndex(); __locIdxCache = locIdx; if(locIdx<0){ block.style.display='none'; return; }
  var vals=((product.options[locIdx]||{}).values||[]).map(function(v){ return String(v||'') });
  var hasOnline=vals.some(function(v){ return v.toLowerCase()==='online' });
  var phys=vals.filter(function(v){ return v && v.toLowerCase()!=='online' });
  var hasPhys=phys.length>0; if(!(hasOnline||hasPhys)){ block.style.display='none'; return; }
  function makeBtn(label, dataVal){ var b=document.createElement('button'); b.type='button'; b.className='pill'; b.setAttribute('role','radio'); b.dataset.variantLocation=dataVal; b.textContent=label; b.setAttribute('aria-checked','false'); return b; }
  if(hasPhys){ var inBtn=makeBtn('In-person', phys[0]); inBtn.addEventListener('click', function(){ state.selected[locIdx]=phys[0]; syncFormatUI(); syncOptionAria(locIdx); updateVariant(); }); pills.appendChild(inBtn); }
  if(hasOnline){ var onBtn=makeBtn('Online', 'Online'); onBtn.addEventListener('click', function(){ state.selected[locIdx]='Online'; syncFormatUI(); syncOptionAria(locIdx); updateVariant(); }); pills.appendChild(onBtn); }
  syncFormatUI(); block.style.display='block';
}
function findVariant(){let v=product.variants.find(v=>v.options.every((o,i)=>o===state.selected[i]));if(!v){v=product.variants.find(v=>v.available)||product.variants[0]}return v}
function isGroup(){return (state.selected[1]||"").toLowerCase().includes("3+")}
function variantFor(format,people){return product.variants.find(v=>v.options[0]===format&&v.options[1]===people)}
function stepForFormat(format){const v1=variantFor(format,"1 Person");const v2=variantFor(format,"2 Persons");if(v1&&v2) return Math.max(0, v2.price - v1.price);const vg=variantFor(format,"3+ Group");if(v2&&vg) return Math.max(0, vg.price - v2.price);return 25000}
function priceForGroup(format,n){const base=variantFor(format,"3+ Group");const step=stepForFormat(format);if(!base) return step*n;const extra=Math.max(0,n-3);return base.price + extra*step}
function compareForGroup(format,n){const v1=variantFor(format,"1 Person");const v2=variantFor(format,"2 Persons");const base=variantFor(format,"3+ Group");let step=0;if(v1&&v2&&v2.compare&&v1.compare&&v2.compare>v1.compare) step=v2.compare-v1.compare;else if(base&&base.compare) step=Math.round(base.compare/3);const extra=Math.max(0,n-3);return (base&&base.compare?base.compare:0) + extra*step}
function unitPriceWithMode(){let base=state.variant.price;if(isGroup()){const format=state.selected[0];const n=Math.min(10, Math.max(3, parseInt(state.groupCount||3,10)));base=priceForGroup(format,n)}return base}
function totals(){
  const unit=unitPriceWithMode();
  const total=unit*state.qty;
  let cmpUnit=0;
  if(isGroup()){
    const format=state.selected[0];
    const n=Math.min(10, Math.max(3, parseInt(state.groupCount||3,10)));
    cmpUnit=compareForGroup(format,n);
  } else if(state.variant && state.variant.compare && state.variant.compare>state.variant.price){
    cmpUnit=state.variant.compare;
  }
  return { unit, total, cmpUnit };
}
function updatePriceUI(){
  const t=totals();
  // Show unit price for current variant selection
  priceEl.textContent=fmt(t.unit);
  if(mPrice) mPrice.textContent=fmt(t.unit);
  if(t.cmpUnit && t.cmpUnit>t.unit){ compareEl.textContent=fmt(t.cmpUnit); compareEl.style.display="inline"; }
  else { compareEl.textContent=""; compareEl.style.display="none"; }
  updateSheetSubtotal();
  try { document.dispatchEvent(new CustomEvent('wow:price', { detail: { unit: t.unit, compare: t.cmpUnit } })); } catch(e) {}
}
function updateSheetSubtotal(){if(!sheetSubtotal) return;const t=totals();sheetSubtotal.textContent=`Subtotal: ${fmt(t.total)}`}
function updateVariant(){
  state.variant=findVariant();
  const show=isGroup();
  if(groupRange){
    if(show){groupRange.style.display="block";clampGroupCount();}
    else{groupRange.style.display="none";state.groupCount=3;if(groupCount) groupCount.value=3;}
  }
  addBtn.disabled=!state.variant || !state.variant.available;
  addBtn.textContent=(state.variant && state.variant.available)?"Add to basket":"Sold out";
  updatePriceUI();
  try { syncFormatUI(); } catch(e) {}
  // Notify helper of current selection + variant id
  try{ document.dispatchEvent(new CustomEvent('wow:selected', { detail: { options: (state.selected||[]), variantId: state.variant ? state.variant.id : null } })); }catch(e){}
}
function clampGroupCount(){let v=parseInt(groupCount.value||"3",10);if(isNaN(v)||v<3) v=3; if(v>10) v=10;groupCount.value=v; state.groupCount=v}
function stepGroup(delta){let v=parseInt(groupCount.value||"3",10); if(isNaN(v)) v=3;v=Math.min(10,Math.max(3,v+delta));groupCount.value=v; state.groupCount=v; updatePriceUI()}
function clampGroupCountSheet(){let v=parseInt(groupCountSheet.value||"3",10);if(isNaN(v)||v<3) v=3; if(v>10) v=10;groupCountSheet.value=v; state.groupCount=v}
function stepGroupSheet(delta){let v=parseInt(groupCountSheet.value||"3",10); if(isNaN(v)) v=3;v=Math.min(10,Math.max(3,v+delta));groupCountSheet.value=v; state.groupCount=v; updatePriceUI()}
function wireQty(){if(inc && dec && qty){inc.addEventListener("click",()=>{qty.value=Math.max(1,parseInt(qty.value||"1",10)+1);state.qty=parseInt(qty.value,10);updatePriceUI()});dec.addEventListener("click",()=>{qty.value=Math.max(1,parseInt(qty.value||"1",10)-1);state.qty=parseInt(qty.value,10);updatePriceUI()});qty.addEventListener("input",()=>{qty.value=qty.value.replace(/[^0-9]/g,"")||1;state.qty=parseInt(qty.value,10);updatePriceUI()})}if(groupInc && groupDec && groupCount){groupInc.addEventListener("click",()=>stepGroup(1));groupDec.addEventListener("click",()=>stepGroup(-1));groupCount.addEventListener("input",()=>{groupCount.value=groupCount.value.replace(/[^0-9]/g,"");clampGroupCount();updatePriceUI()});groupCount.addEventListener("blur",()=>{clampGroupCount();updatePriceUI()})}if(groupIncSheet && groupDecSheet && groupCountSheet){groupIncSheet.addEventListener("click",()=>stepGroupSheet(1));groupDecSheet.addEventListener("click",()=>stepGroupSheet(-1));groupCountSheet.addEventListener("input",()=>{groupCountSheet.value=groupCountSheet.value.replace(/[^0-9]/g,"");clampGroupCountSheet();updatePriceUI()});groupCountSheet.addEventListener("blur",()=>{clampGroupCountSheet();updatePriceUI()})}}
function isMobile(){return window.matchMedia('(max-width: 991px)').matches}
function doAddToCart(){const t=new bootstrap.Toast(toastEl); t.show();const badge=document.querySelector("[data-cart-count]")||document.body.appendChild(Object.assign(document.createElement("div"),{dataset:{cartCount:""}}));if(!badge.className){badge.className="position-fixed top-0 end-0 m-3 badge bg-success rounded-pill p-3";badge.textContent="0"}const incBy=isMobile()?1:(parseInt(qty.value||"1",10) || 1);badge.textContent=String(parseInt(badge.textContent||"0",10)+incBy)}
function wireCTA(){addBtn.addEventListener("click",e=>{e.preventDefault();const t=new bootstrap.Toast(toastEl);t.show()});buyNow.addEventListener("click",e=>{e.preventDefault();const t=new bootstrap.Toast(toastEl);t.show()})}
mobileAdd.addEventListener('click',()=>{buildOptionsInto(sheetOptions);groupRangeSheet.style.display=isGroup()?'block':'none';groupCountSheet.value=String(state.groupCount||3);if(bookingChoice.value==='now'){sheetBookLater.classList.remove('active');sheetBookNow.classList.add('active');}else{sheetBookLater.classList.add('active');sheetBookNow.classList.remove('active');}updateSheetSubtotal();configModal.show()});
const calendarState={viewYear:new Date().getFullYear(),viewMonth:new Date().getMonth(),selectedDate:null,selectedTime:null,tz:Intl.DateTimeFormat().resolvedOptions().timeZone||'Europe/London'};
function exitMobileTimesMode(){bookingModalContent.classList.remove('mobile-times')}
function clearBookingSelection(){bookingChoice.value='later';preferredDateValue.value='';preferredTimeValue.value='';preferredTZValue.value='';bookingSelectionRow.style.display='none';bookingSelectionText.textContent='';calendarState.selectedDate=null;calendarState.selectedTime=null;bookingSummary.textContent='No date selected.';confirmBooking.disabled=true;modalHint.textContent='Pick a date, then choose a time.';btnBookLater?.classList.add('active');btnBookNow?.classList.remove('active');exitMobileTimesMode();stopUserHold()}
btnBookLater?.addEventListener('click',clearBookingSelection);btnBookNow?.addEventListener('click',()=>{btnBookNow.classList.add('active');btnBookLater.classList.remove('active');bookingChoice.value='now';bookingModal.show()});changeBooking?.addEventListener('click',(e)=>{e.preventDefault();btnBookNow.click()});
sheetBookLater.addEventListener('click',()=>{sheetBookLater.classList.add('active');sheetBookNow.classList.remove('active');bookingChoice.value='later';});sheetBookNow.addEventListener('click',()=>{sheetBookNow.classList.add('active');sheetBookLater.classList.remove('active');bookingChoice.value='now';});sheetConfirm.addEventListener('click',()=>{if(bookingChoice.value==='now' && !(preferredDateValue.value && preferredTimeValue.value)){configModal.hide();bookingModal.show();return;}configModal.hide();doAddToCart();});
function generateSlotsForDate(d){const day=d.getDay(); if(day===0||day===6) return [];const slots=[]; for(let h=9;h<=16;h++){slots.push(`${String(h).padStart(2,'0')}:00`);slots.push(`${String(h).padStart(2,'0')}:30`)} return slots}
function renderDayNames(){calDayNames.innerHTML='';['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].forEach(n=>{const el=document.createElement('div');el.className='cal-dayname text-center';el.textContent=n;calDayNames.appendChild(el)})}
function daysInMonth(y,m){return new Date(y,m+1,0).getDate()}function firstWeekday(y,m){const js=new Date(y,m,1).getDay();return (js+6)%7}function isSameDate(a,b){return a&&b&&a.getFullYear()===b.getFullYear()&&a.getMonth()===b.getMonth()&&a.getDate()===b.getDate()}
function renderCalendar(){const y=calendarState.viewYear,m=calendarState.viewMonth;calMonthLabel.textContent=new Date(y,m,1).toLocaleDateString(undefined,{month:'long',year:'numeric'});calGrid.innerHTML='';const lead=firstWeekday(y,m),total=daysInMonth(y,m);if(!calDayNames.children.length) renderDayNames();const today=new Date(); today.setHours(0,0,0,0);for(let i=0;i<lead;i++){const d=document.createElement('div');d.className='cal-cell';d.setAttribute('aria-disabled','true');calGrid.appendChild(d)}for(let day=1;day<=total;day++){const cellDate=new Date(y,m,day); cellDate.setHours(0,0,0,0);const btn=document.createElement('button'); btn.type='button'; btn.className='cal-cell'; btn.textContent=String(day);const past=cellDate<today; if(past) btn.setAttribute('aria-disabled','true');btn.addEventListener('click',()=>{if(past) return;calendarState.selectedDate=cellDate; calendarState.selectedTime=null;[...calGrid.querySelectorAll('.cal-cell')].forEach(c=>c.classList.remove('active'));btn.classList.add('active'); renderSlots(); updateSummary(); confirmBooking.disabled=true; modalHint.textContent='Choose a time.'; if (window.matchMedia('(max-width: 991px)').matches) bookingModalContent.classList.add('mobile-times');});if(isSameDate(cellDate,calendarState.selectedDate)) btn.classList.add('active');calGrid.appendChild(btn)}}
function validReserved(dayObj, timeKey){const res = dayObj.reserved[timeKey]; if(!res) return null; const now = new Date(); if(now >= res.until){delete dayObj.reserved[timeKey]; return null;} return res }
function mmss(ms){const total=Math.max(0,Math.ceil(ms/1000));const m=String(Math.floor(total/60)).padStart(2,'0');const s=String(total%60).padStart(2,'0');return `${m}:${s}`}
let userHoldInterval=null,userHoldUntil=null,userHoldKey=null;
function startUserHold(dateObj,timeStr){stopUserHold();const k=dateKey(new Date(Date.UTC(dateObj.getFullYear(),dateObj.getMonth(),dateObj.getDate())));const dayObj=ensureDay(new Date(Date.UTC(dateObj.getFullYear(),dateObj.getMonth(),dateObj.getDate())));userHoldUntil=new Date(Date.now()+HOLD_MINUTES*60000);dayObj.reserved[timeStr]={until:userHoldUntil};userHoldKey={k,timeStr};document.getElementById('holdTimer').style.display='block';const banner=document.getElementById('pillHoldBanner');banner.style.display='flex';banner.classList.add('hourglass-active');banner.querySelector('i')?.classList.add('hourglass-spin');tickUserHold();userHoldInterval=setInterval(tickUserHold,1000);renderSlots()}
function stopUserHold(){document.getElementById('holdTimer').style.display='none';document.getElementById('holdCountdown').textContent='10:00';const banner=document.getElementById('pillHoldBanner');banner.style.display='none';banner.classList.remove('hourglass-active');banner.querySelector('i')?.classList.remove('hourglass-spin');document.getElementById('pillHoldCountdown').textContent='10:00';if(userHoldInterval){clearInterval(userHoldInterval);userHoldInterval=null}if(userHoldKey){const d = bookings[userHoldKey.k];if(d && d.reserved[userHoldKey.timeStr]){delete d.reserved[userHoldKey.timeStr]}userHoldKey=null}userHoldUntil=null}
function tickUserHold(){if(!userHoldUntil){document.getElementById('holdTimer').style.display='none';const banner=document.getElementById('pillHoldBanner');banner.style.display='none';banner.classList.remove('hourglass-active');banner.querySelector('i')?.classList.remove('hourglass-spin');return}const remaining=userHoldUntil-Date.now();if(remaining<=0){stopUserHold();calendarState.selectedTime=null;updateSummary();confirmBooking.disabled=true;modalHint.textContent='Hold expired — please choose another time.';renderSlots();return}const t=mmss(remaining);document.getElementById('holdCountdown').textContent=t;document.getElementById('pillHoldCountdown').textContent=t}
function refreshReservedCountdowns(){const spans=[...document.querySelectorAll('button.slot.reserved span[data-until]')];if(!spans.length) return;const now=new Date();spans.forEach(s=>{const until=new Date(s.dataset.until);const remaining=until-now;if(remaining<=0){renderSlots()}else{s.textContent=`(${mmss(remaining)}) reserved`}})}
function renderSlots(){slotList.innerHTML='';const d=calendarState.selectedDate;if(!d){slotList.innerHTML='<div class="text-secondary small">Select a date to see available times.</div>';return}const slots=generateSlotsForDate(d); if(!slots.length){slotList.innerHTML='<div class="text-secondary small">No times available for this date.</div>';return;}const dayObj=ensureDay(new Date(Date.UTC(d.getFullYear(),d.getMonth(),d.getDate())));slots.forEach(s=>{if(dayObj.booked.has(s)) return;const reservedObj=validReserved(dayObj,s);const b=document.createElement('button');b.type='button';b.className='slot';b.textContent=s;if(reservedObj){b.classList.add('reserved');b.disabled=true;const span=document.createElement('span');span.className='ms-1 small';span.dataset.until=reservedObj.until.toISOString();span.textContent='(reserved)';b.appendChild(span)}else{b.addEventListener('click',()=>{[...slotList.querySelectorAll('.slot')].forEach(x=>x.classList.remove('active'));b.classList.add('active');calendarState.selectedTime=s;updateSummary();confirmBooking.disabled=false;modalHint.textContent='Nice choice — we’ll hold this for 10 minutes.';startUserHold(d,s);})}if(calendarState.selectedTime===s) b.classList.add('active');slotList.appendChild(b)});refreshReservedCountdowns()}
function populateTimezones(){const tzs=['Europe/London','Europe/Dublin','Europe/Lisbon','Europe/Paris','Europe/Berlin','UTC','America/New_York','America/Chicago','America/Denver','America/Los_Angeles','Asia/Dubai','Asia/Kolkata','Asia/Singapore','Australia/Sydney'];tzSelect.innerHTML='';tzs.forEach(tz=>{const o=document.createElement('option');o.value=tz;o.textContent=tz;if(tz===calendarState.tz)o.selected=true;tzSelect.appendChild(o)});tzCurrent.textContent=calendarState.tz;tzSelect.addEventListener('change',()=>{calendarState.tz=tzSelect.value;tzCurrent.textContent=calendarState.tz;updateSummary()})}
function updateSummary(){if(calendarState.selectedDate && calendarState.selectedTime){const ds=calendarState.selectedDate.toLocaleDateString(undefined,{weekday:'long',day:'numeric',month:'long',year:'numeric'});bookingSummary.innerHTML=`<div class="fw-semibold">${ds}</div><div>${calendarState.selectedTime} (${calendarState.tz})</div>`}else if(calendarState.selectedDate){const ds=calendarState.selectedDate.toLocaleDateString(undefined,{weekday:'long',day:'numeric',month:'long',year:'numeric'});bookingSummary.textContent=`${ds} — select a time`}else bookingSummary.textContent='No date selected.'}
calPrev.addEventListener('click',()=>{calendarState.viewMonth--; if(calendarState.viewMonth<0){calendarState.viewMonth=11;calendarState.viewYear--} renderCalendar()});calNext.addEventListener('click',()=>{calendarState.viewMonth++; if(calendarState.viewMonth>11){calendarState.viewMonth=0;calendarState.viewYear++} renderCalendar()});mobileBack?.addEventListener('click',()=>{bookingModalContent.classList.remove('mobile-times')});
confirmBooking.addEventListener('click',()=>{if(!(calendarState.selectedDate && calendarState.selectedTime)) return;preferredDateValue.value=calendarState.selectedDate.toISOString().slice(0,10);preferredTimeValue.value=calendarState.selectedTime;preferredTZValue.value=calendarState.tz;const ds=calendarState.selectedDate.toLocaleDateString(undefined,{weekday:'short',day:'numeric',month:'short',year:'numeric'});bookingSelectionText.textContent=`${ds} • ${calendarState.selectedTime}`;bookingSelectionRow.style.display='inline-block';bookingModal.hide()});
bookingModalEl.addEventListener('shown.bs.modal',()=>{bookingModalContent.classList.remove('mobile-times');if(!calDayNames.children.length){populateTimezones();}renderCalendar();renderSlots();updateSummary()});
// (mode note removed)
function wireCTA(){addBtn.addEventListener("click",e=>{e.preventDefault();const t=new bootstrap.Toast(toastEl);t.show()});buyNow.addEventListener("click",e=>{e.preventDefault();const t=new bootstrap.Toast(toastEl);t.show()})}
function init(){renderStars();buildOptions();buildFormatBlock();updateVariant();wireQty();wireCTA();updatePriceUI();window.addEventListener('resize',()=>{ bookingModalContent.classList.remove('mobile-times'); })}
init();

// Listen for external variant selection (from Product Data Helper)
try {
  document.addEventListener('wow:selectVariant', function(ev){
    try {
      var opts = ev?.detail?.options; if(!Array.isArray(opts) || !opts.length) return;
      // Align selection by index
      for (var i=0; i<opts.length; i++) { state.selected[i] = String(opts[i]||''); }
      // Sync option rows UI
      for (var j=0; j<(product.options||[]).length; j++){ syncOptionAria(j); }
      // Sync format pills and update
      try { syncFormatUI(); } catch(e) {}
      updateVariant();
    } catch(e) {}
  });
} catch(e) {}
</script>
