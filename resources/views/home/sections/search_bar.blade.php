<section data-v-f43bb09d="" class="py-4">
    <div data-v-f43bb09d="" class="container">
        <x-ultra-search-bar prefix="home-template" :show-seven-day-chip="true" />
    </div>
</section><!---->

<style>
/* Ensure search suggestion panes overlay content but stay under the header */
.wow-ultra{ position: relative; z-index: 30; }
.wow-ultra .pane{ z-index: 39; }
.wow-ultra .pane.narrow{
  z-index: 39;
  left: 0px !important;
  right: 0px !important;
  width: min(560px, 96vw);
  max-width: 96vw;
  height: auto;
  max-height: 304px;
  overflow: auto;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.wow-ultra .pane.narrow::-webkit-scrollbar{ width:0; height:0 }
/* Desktop default: show text label, hide icon on Search button */
.btn-wow.is-squarish.btn-xl .btn-label{ display:inline; }
.btn-wow.is-squarish.btn-xl .btn-icon{ display:none; }
@media (max-width: 991.98px){
  /* Clamp What suggestions pane within viewport on mobile */
  #home-template-what-pane{
    left: auto !important;
    right: 0 !important;
    width: min(560px, calc(100vw - 24px));
    max-width: calc(100vw - 24px);
  }
  .wow-ultra .bar{
    background: rgba(255,255,255,.14);
    border-radius:40px;
    border:none;
    border-top: 1px solid rgba(255,255,255,0.5);
    border-bottom: 1px solid rgba(0,0,0,0.08);
    position: sticky; top: 84px; z-index: 30;
    -webkit-backdrop-filter: blur(14px);
    backdrop-filter: blur(14px);
    box-shadow: 0 14px 40px rgba(16,24,40,.14);
  }
  .wow-ultra .pane.narrow{
    width: auto;
    max-width: none;
  }
  .wow-ultra .bar::before{ content:""; position:absolute; inset:0; border-radius: inherit; pointer-events:none; background: linear-gradient(180deg, rgba(255,255,255,.28), rgba(255,255,255,.08)); opacity:.55; }
  .wow-ultra .bar > *{ position: relative; z-index: 1; }
  .wow-ultra .seg{ border-radius:40px; }
  /* Hide Where/When/Who on mobile */
  #home-template-seg-where,
  #home-template-seg-when,
  #home-template-seg-who{ display:none !important; }
  .btn-wow.is-squarish.btn-xl{
    border-radius:50% !important;
    position:absolute; right:11px; top:50%; transform: translateY(-50%);
    display:inline-flex; align-items:center; justify-content:center;
    width:45px; height:45px; min-width:45px; min-height:45px; max-width:45px; max-height:45px;
    padding:0 !important; line-height:45px; overflow:hidden;
  }
  .btn-wow.is-squarish.btn-xl .btn-label{ display:none; }
  .btn-wow.is-squarish.btn-xl .btn-icon{ display:inline-flex; }
  .btn-wow.is-squarish.btn-xl .icon-search{ width:24px; height:24px; color:#fff; }
}

@media (min-width: 992px){
  .wow-ultra .bar{
    border-radius:19px;
    border:3px solid rgba(0,0,0,0.1);
  }
}
</style>
