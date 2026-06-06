@props([
    'prefix' => 'mobile-search',
])

<div class="wow-ultra only-what wow-ultra--header-search" id="{{ $prefix }}-root">
    <form class="bar" role="search" action="/search" method="get">
        <div class="seg" id="{{ $prefix }}-seg-what">
            <i class="bi bi-stars fs-5 text-muted" aria-hidden="true"></i>
            <div class="flex-grow-1">
        <div class="seg-label">What</div>
        <input id="{{ $prefix }}-what" type="text" name="what" autocomplete="off" placeholder="Massage, yoga, breathwork…" aria-expanded="false" aria-controls="{{ $prefix }}-what-pane" required>
      </div>
            <div id="{{ $prefix }}-what-pane" class="pane narrow d-none" role="listbox" aria-label="What suggestions">
                <div id="{{ $prefix }}-what-list" class="listy"></div>
            </div>
        </div>

        <button type="submit" class="btn-wow is-squarish btn-xl" aria-label="Search">
            <span class="btn-icon" aria-hidden="true">
                <svg class="icon-search" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"></path>
                </svg>
            </span>
        </button>
    </form>
</div>

@once
    <style>
        .wow-ultra--header-search{
            width:100% !important;
        }
        .wow-ultra--header-search .bar{
            background:#fff !important;
            border-radius:18px !important;
            padding:5px !important;
            box-shadow:0 14px 40px rgba(10,22,70,.14) !important;
            display:flex !important;
            gap:5px !important;
            flex-wrap:nowrap !important;
            width:100% !important;
            box-sizing:border-box !important;
        }
        .wow-ultra--header-search .seg-label,
        .wow-ultra--header-search .seg input,
        .wow-ultra--header-search .pane,
        .wow-ultra--header-search .pane .section-title,
        .wow-ultra--header-search .listy,
        .wow-ultra--header-search .item,
        .wow-ultra--header-search .item .title{
            font-family:'Manrope', var(--bs-font-sans-serif) !important;
        }
        .wow-ultra--header-search .seg{
            flex:1 1 100% !important;
            display:flex !important;
            gap:10px !important;
            align-items:center !important;
            background:#fff !important;
            border:1px solid rgba(15,23,42,.12) !important;
            border-radius:14px !important;
            padding:14px 16px !important;
            position:relative !important;
            max-height:58px !important;
            min-width:0 !important;
        }
        .wow-ultra--header-search .seg:focus-within{
            box-shadow:0 0 0 3px rgba(23,55,197,.16) !important;
            border-color:transparent !important;
        }
        .wow-ultra--header-search .seg-label{
            font-weight:600 !important;
            color:#111827 !important;
            font-size:11px !important;
            line-height:1 !important;
            margin:0 0 2px 0 !important;
        }
        .wow-ultra--header-search .seg input{
            border:0 !important;
            outline:0 !important;
            width:100% !important;
            background:transparent !important;
            font-size:1rem !important;
            line-height:1.25 !important;
            padding:0 !important;
            margin:0 !important;
        }
        .wow-ultra--header-search .pane{
            position:absolute !important;
            left:0 !important;
            right:0 !important;
            top:calc(100% + 10px) !important;
            background:#fff !important;
            border:1px solid #e6e9f2 !important;
            border-radius:16px !important;
            box-shadow:0 14px 40px rgba(10,22,70,.14) !important;
            z-index:40 !important;
            overflow:hidden !important;
            text-align:left !important;
        }
        .wow-ultra--header-search .pane.narrow{
            z-index:39 !important;
            left:0 !important;
            right:0 !important;
            width:min(560px,96vw) !important;
            max-width:96vw !important;
            height:auto !important;
            max-height:304px !important;
            overflow:auto !important;
            -ms-overflow-style:none !important;
            scrollbar-width:none !important;
        }
        @media (max-width: 991.98px){
            .wow-ultra--header-search .pane.narrow{
                width:auto !important;
                max-width:none !important;
            }
        }
        .wow-ultra--header-search .pane.narrow::-webkit-scrollbar{
            width:0;
            height:0;
        }
        .wow-ultra--header-search .pane .section-title{
            font-size:.85rem !important;
            font-weight:700 !important;
            letter-spacing:.01em !important;
            color:#111827 !important;
            padding:10px 14px !important;
            background:#f9fafb !important;
            border-bottom:1px solid #eef2f7 !important;
        }
        .wow-ultra--header-search .listy{
            max-height:360px !important;
            overflow:auto !important;
            padding:6px 0 !important;
        }
        .wow-ultra--header-search .item{
            display:flex !important;
            align-items:center !important;
            gap:10px !important;
            padding:12px 14px !important;
            text-align:left !important;
            background:#fff !important;
            border:0 !important;
            width:100% !important;
            color:#0f172a !important;
        }
        .wow-ultra--header-search .item:hover,
        .wow-ultra--header-search .item[aria-selected="true"]{
            background:#f2f5ff !important;
        }
        .wow-ultra--header-search .item .title{
            font-weight:600 !important;
            color:#0f172a !important;
        }
        .wow-ultra--header-search .item .type{
            font-size:.75rem !important;
            padding:.1rem .5rem !important;
            border-radius:999px !important;
            background:#eef2ff !important;
            color:#2536eb !important;
            margin-left:.5rem !important;
        }
        .wow-ultra--header-search .btn-wow.is-squarish.btn-xl{
            flex:0 0 auto !important;
            width:45px !important;
            height:45px !important;
            min-width:45px !important;
            min-height:45px !important;
            max-width:45px !important;
            max-height:45px !important;
            padding:0 !important;
            border-radius:50% !important;
            display:inline-flex !important;
            align-items:center !important;
            justify-content:center !important;
            align-self:center !important;
        }
        .wow-ultra--header-search .btn-wow.is-squarish.btn-xl .btn-icon{
            display:inline-flex !important;
        }
        .wow-ultra--header-search .icon-search{
            width:24px !important;
            height:24px !important;
            color:#fff !important;
        }
        .wow-ultra--header-search .seg input::placeholder{
            color:#9ca3af !important;
        }
        @media (max-width: 991.98px){
            .mobile-search-drawer .wow-ultra--header-search .bar{
                background: rgba(255, 255, 255, .14) !important;
                border-radius: 33px !important;
                border: none !important;
                border-top: 1px solid rgba(255, 255, 255, 0.5) !important;
                border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
                -webkit-backdrop-filter: blur(14px) !important;
                backdrop-filter: blur(14px) !important;
                box-shadow: 0 14px 40px rgba(16, 24, 40, .14) !important;
            }
            .mobile-search-drawer .wow-ultra--header-search .bar::before{
                content:"" !important;
                position:absolute !important;
                inset:0 !important;
                border-radius:inherit !important;
                pointer-events:none !important;
                background: linear-gradient(180deg, rgba(255,255,255,.28), rgba(255,255,255,.08)) !important;
                opacity:.55 !important;
            }
            .mobile-search-drawer .wow-ultra--header-search .bar > *{
                position:relative !important;
                z-index:1 !important;
            }
            .mobile-search-drawer .wow-ultra--header-search .seg{
                border-radius:40px !important;
            }
            .mobile-search-drawer .wow-ultra--header-search .btn-wow.is-squarish.btn-xl{
                border-radius:50% !important;
                position:absolute !important;
                right:11px !important;
                top:50% !important;
                transform:translateY(-50%) !important;
                width:45px !important;
                height:45px !important;
                min-width:45px !important;
                min-height:45px !important;
                max-width:45px !important;
                max-height:45px !important;
                padding:0 !important;
                line-height:45px !important;
                overflow:hidden !important;
            }
            .mobile-search-drawer .wow-ultra--header-search .btn-wow.is-squarish.btn-xl .btn-label{
                display:none !important;
            }
            .mobile-search-drawer .wow-ultra--header-search .btn-wow.is-squarish.btn-xl .btn-icon{
                display:inline-flex !important;
            }
            .mobile-search-drawer .wow-ultra--header-search .btn-wow.is-squarish.btn-xl .icon-search{
                width:24px !important;
                height:24px !important;
                color:#fff !important;
            }
            .mobile-search-drawer .wow-ultra--header-search .seg input,
            .mobile-search-drawer .wow-ultra--header-search .seg-label{
                font-family:'Manrope', var(--bs-font-sans-serif) !important;
                color: var(--ink-900) !important;
            }
        }
    </style>
@endonce
