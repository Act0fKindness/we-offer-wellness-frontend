@props([
    'searchUrl' => route('search'),
    'initialQuery' => request()->query('what', request()->query('q', '')),
    'initialWhere' => request()->query('where', ''),
    'initialMode' => request()->query('mode', ''),
    'mapboxKey' => config('services.mapbox.token'),
])

<div
    data-wow-home-searchbar-v4
    data-search-url="{{ rtrim($searchUrl, '/') }}"
    data-initial-query="{{ $initialQuery }}"
    data-initial-where="{{ $initialWhere }}"
    data-initial-mode="{{ $initialMode }}"
    data-mapbox-key="{{ $mapboxKey }}"
    class="w-full"
>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap');

        [data-wow-home-searchbar-v4] {
            width: 100%;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        [data-wow-home-searchbar-v4] [hidden] {
            display: none !important;
        }

        [data-wow-home-searchbar-v4] .desktop-shell {
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        [data-wow-home-searchbar-v4] .desktop-shell.is-active {
            border-color: rgba(155, 165, 180, 0.6);
            box-shadow: 0 0 0 3px rgba(79, 147, 129, 0.15), 0 10px 30px rgba(28, 39, 56, 0.10);
        }

        [data-wow-home-searchbar-v4] .desktop-field {
            transition: background-color 150ms ease, box-shadow 150ms ease;
        }

        [data-wow-home-searchbar-v4] .desktop-field.is-active {
            background: #fff;
            box-shadow: 0 2px 12px rgba(16, 24, 40, 0.06);
        }

        [data-wow-home-searchbar-v4] .desktop-icon,
        [data-wow-home-searchbar-v4] .mobile-main-icon {
            transition: color 150ms ease;
        }

        [data-wow-home-searchbar-v4] .desktop-icon.is-active,
        [data-wow-home-searchbar-v4] .mobile-main-icon.is-active {
            color: #4f9381 !important;
        }

        [data-wow-home-searchbar-v4] .mobile-sheet {
            height: 88dvh;
        }
    </style>

    <form id="desktop-search-form" class="hidden md:block w-full max-w-[900px] mx-auto" novalidate autocomplete="off">
        <div id="desktop-search-shell" class="desktop-shell flex items-center min-h-[68px] bg-white/97 border border-[rgba(155,165,180,0.45)] rounded-full shadow-[0_10px_30px_rgba(28,39,56,0.08)]">
            <div id="desktop-what-field" class="desktop-field relative flex items-center gap-3 px-5 flex-[1.7] min-w-0 h-full rounded-full cursor-text hover:bg-black/[0.025]">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles desktop-icon desktop-what-icon shrink-0 text-[#8e9bb0]" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path><path d="M20 3v4"></path><path d="M22 5h-4"></path><path d="M4 17v2"></path><path d="M5 18H3"></path></svg>
                <div class="flex flex-col min-w-0 flex-1">
                    <label for="desktop-what" class="text-[11px] font-[650] text-[#758096] mb-[1px] cursor-text select-none">Search</label>
                    <input id="desktop-what" type="search" autocomplete="off" value="{{ $initialQuery }}" placeholder="Therapies, events, classes & more" class="bg-transparent border-0 outline-none p-0 text-[14.5px] text-[#1a202c] font-medium placeholder:text-[#818896] placeholder:font-normal w-full [appearance:textfield] [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none" />
                </div>
                <button id="desktop-clear-what" type="button" hidden class="shrink-0 w-5 h-5 rounded-full bg-[#e4e8ee] flex items-center justify-center hover:bg-[#d0d5de] transition-colors" aria-label="Clear search"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x text-[#4a5568]" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></button>
                <div id="desktop-what-dropdown" hidden class="absolute top-[calc(100%+8px)] left-0 w-full min-w-[320px] bg-white rounded-2xl border border-black/10 shadow-[0_16px_48px_rgba(16,24,40,0.14)] overflow-hidden z-50">
                    <div class="px-4 pt-3 pb-1"><span id="desktop-what-heading" class="text-[10px] font-bold uppercase tracking-widest text-[#98a2b3]">Popular experiences</span></div>
                    <ul id="desktop-what-list" class="py-1"></ul>
                </div>
            </div>

            <div id="desktop-where-field" class="desktop-field relative flex items-center gap-3 px-5 flex-1 min-w-0 h-full rounded-full cursor-text transition-colors duration-150 hover:bg-black/[0.025] border-l border-[rgba(155,165,180,0.45)]">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin desktop-icon desktop-where-icon shrink-0 text-[#8e9bb0]" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <div class="flex flex-col min-w-0 flex-1">
                    <label for="desktop-where" class="text-[11px] font-[650] text-[#758096] mb-[1px] cursor-text select-none">Where</label>
                    <input id="desktop-where" type="search" autocomplete="off" value="{{ $initialWhere }}" placeholder="Town, city or Online" class="bg-transparent border-0 outline-none p-0 text-[14.5px] text-[#1a202c] font-medium placeholder:text-[#818896] placeholder:font-normal w-full [appearance:textfield] [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none" />
                    <span id="desktop-near-me-chip" hidden class="inline-flex w-fit items-center gap-1.5 rounded-full bg-[#e8f5f1] px-2.5 py-1 text-[13px] font-semibold text-[#2a5e52]"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-navigation" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>Near me</span>
                </div>
                <button id="desktop-clear-where" type="button" hidden class="shrink-0 w-5 h-5 rounded-full bg-[#e4e8ee] flex items-center justify-center hover:bg-[#d0d5de] transition-colors" aria-label="Clear location"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x text-[#4a5568]" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></button>
                <div id="desktop-where-dropdown" hidden class="absolute top-[calc(100%+8px)] left-0 w-full min-w-[280px] bg-white rounded-2xl border border-black/10 shadow-[0_16px_48px_rgba(16,24,40,0.14)] overflow-hidden z-50">
                    <button id="desktop-use-location" type="button" class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-[#f0faf7] border-b border-black/6 transition-colors text-left">
                        <span class="w-8 h-8 rounded-full bg-[#e8f5f1] flex items-center justify-center shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-navigation text-[#4f9381]" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg></span>
                        <span class="flex-1"><span class="block text-[13.5px] font-semibold text-[#1a202c]">Use my location</span><span class="block text-[11px] text-[#98a2b3]">Find wellness near you</span></span><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-[#c0c8d4]" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                    </button>
                    <button id="desktop-online" type="button" class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-[#f0faf7] border-b border-black/6 transition-colors text-left">
                        <span class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi text-blue-500" aria-hidden="true"><path d="M12 20h.01"></path><path d="M2 8.82a15 15 0 0 1 20 0"></path><path d="M5 12.859a10 10 0 0 1 14 0"></path><path d="M8.5 16.429a5 5 0 0 1 7 0"></path></svg></span>
                        <span class="flex-1"><span class="block text-[13.5px] font-semibold text-[#1a202c]">Online</span><span class="block text-[11px] text-[#98a2b3]">Join from anywhere</span></span><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-[#c0c8d4]" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                    </button>
                    <div id="desktop-location-list-heading-wrap" class="px-4 pt-2.5 pb-1"><span id="desktop-location-heading" class="text-[10px] font-bold uppercase tracking-widest text-[#98a2b3]">Popular</span></div>
                    <ul id="desktop-location-list" class="pb-1.5"></ul>
                </div>
            </div>

            <button type="submit" aria-label="Search" class="shrink-0 w-[58px] h-[58px] mr-[4px] rounded-full bg-[#101828] text-white flex items-center justify-center shadow-[0_4px_12px_rgba(0,0,0,0.18)] hover:bg-[#1d2939] active:scale-[0.96] transition-all duration-150"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg></button>
        </div>
        <input type="hidden" id="desktop-mode" value="{{ $initialMode }}">
    </form>

    <form id="mobile-search-form" class="md:hidden w-full max-w-[520px] mx-auto" novalidate autocomplete="off">
        <div class="flex flex-col gap-2.5">
            <div class="relative">
                <button id="mobile-open-what" type="button" class="w-full flex items-center h-[60px] bg-white rounded-full px-5 text-left border border-[rgba(117,128,150,0.42)] transition-all duration-150 shadow-[0_8px_24px_rgba(28,39,56,0.09)] active:bg-[#f7f9fb]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles mobile-main-icon mobile-what-main-icon shrink-0 mr-3 text-[#8e9bb0]" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path><path d="M20 3v4"></path><path d="M22 5h-4"></path><path d="M4 17v2"></path><path d="M5 18H3"></path></svg>
                    <span id="mobile-what-display" class="flex-1 min-w-0 text-[15px] font-medium truncate {{ $initialQuery ? 'text-[#111827]' : 'text-[#687283] font-normal' }}">{{ $initialQuery ?: 'Search therapies, events & more' }}</span>
                </button>
            </div>
            <div class="relative">
                <button id="mobile-open-where" type="button" class="w-full flex items-center h-[60px] bg-white rounded-full px-5 text-left border border-[rgba(117,128,150,0.42)] transition-all duration-150 shadow-[0_8px_24px_rgba(28,39,56,0.09)] active:bg-[#f7f9fb]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin mobile-main-icon mobile-where-main-icon shrink-0 mr-3 text-[#8e9bb0]" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span id="mobile-where-display" class="flex-1 min-w-0 text-[15px] font-medium truncate {{ $initialWhere ? 'text-[#111827]' : 'text-[#687283] font-normal' }}">{{ $initialWhere ?: 'Near me, town or Online' }}</span>
                    <span id="mobile-near-me-chip" hidden class="inline-flex items-center gap-1.5 rounded-full bg-[#e8f5f1] px-2.5 py-1 text-[13px] font-semibold text-[#2a5e52]"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-navigation" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>Near me</span>
                </button>
            </div>
            <button type="submit" class="w-full h-[54px] rounded-full bg-[#101828] text-white font-[650] text-[15px] tracking-[-0.01em] shadow-[0_4px_14px_rgba(0,0,0,0.2)] hover:bg-[#1d2939] active:scale-[0.98] transition-all duration-150 flex items-center justify-center gap-2.5 mt-1">Search <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg></button>
        </div>
        <p class="flex items-center justify-center gap-1.5 mt-3 text-[12px] text-[#707989]"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg>Trusted practitioners and experiences</p>
        <input type="hidden" id="mobile-mode" value="{{ $initialMode }}">
    </form>

    <div id="mobile-where-modal" hidden class="fixed inset-0 z-[100] flex flex-col justify-end">
        <div class="mobile-modal-overlay absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
        <div class="mobile-sheet relative bg-white rounded-t-[24px] flex flex-col overflow-hidden shadow-[0_-8px_40px_rgba(0,0,0,0.18)]">
            <div class="mobile-sheet-handle flex justify-center pt-3 pb-1 shrink-0 cursor-grab active:cursor-grabbing touch-none"><div class="w-10 h-1 bg-[#d3d7de] rounded-full"></div></div>
            <div class="flex items-center justify-between px-5 py-3 shrink-0"><div><p class="text-[11px] text-[#98a2b3] font-semibold uppercase tracking-wider">Search filter</p><h2 class="text-[1.2rem] font-bold text-[#101828]">Where?</h2></div><button type="button" class="mobile-modal-close w-9 h-9 rounded-full bg-[#f2f4f7] flex items-center justify-center hover:bg-[#e8eaed] transition-colors" aria-label="Close"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></button></div>
            <div class="px-5 pb-3 shrink-0"><div class="relative"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-4 top-1/2 -translate-y-1/2 text-[#697386]" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg><input id="mobile-where-input" type="search" value="{{ $initialWhere }}" placeholder="Town, city or postcode" class="w-full h-[52px] pl-10 pr-4 border border-[#dce1e7] rounded-[14px] text-[15px] text-[#1a202c] bg-white outline-none focus:border-[#4f9381] focus:ring-2 focus:ring-[#4f9381]/20 transition-all [appearance:textfield]" /></div></div>
            <div class="flex-1 overflow-y-auto px-2 pb-8">
                <button id="mobile-use-location" type="button" class="w-full flex items-center gap-3.5 px-3 py-4 border-b border-[#eef0f3] text-left"><span class="w-9 h-9 rounded-full bg-[#e8f5f1] flex items-center justify-center shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-navigation text-[#4f9381]" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg></span><span class="flex-1"><strong class="block text-[15px] text-[#1a202c] font-semibold">Use my location</strong><small class="text-[12px] text-[#98a2b3]">Find wellness near you</small></span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-[#b8c0cc]" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button>
                <button id="mobile-online" type="button" class="w-full flex items-center gap-3.5 px-3 py-4 border-b border-[#eef0f3] text-left"><span class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi text-blue-500" aria-hidden="true"><path d="M12 20h.01"></path><path d="M2 8.82a15 15 0 0 1 20 0"></path><path d="M5 12.859a10 10 0 0 1 14 0"></path><path d="M8.5 16.429a5 5 0 0 1 7 0"></path></svg></span><span class="flex-1"><strong class="block text-[15px] text-[#1a202c] font-semibold">Online</strong><small class="text-[12px] text-[#98a2b3]">Join from anywhere</small></span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-[#b8c0cc]" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button>
                <div id="mobile-where-list"></div>
            </div>
        </div>
    </div>

    <div id="mobile-what-modal" hidden class="fixed inset-0 z-[100] flex flex-col justify-end">
        <div class="mobile-modal-overlay absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
        <div class="mobile-sheet relative bg-white rounded-t-[24px] flex flex-col overflow-hidden shadow-[0_-8px_40px_rgba(0,0,0,0.18)]">
            <div class="mobile-sheet-handle flex justify-center pt-3 pb-1 shrink-0 cursor-grab active:cursor-grabbing touch-none"><div class="w-10 h-1 bg-[#d3d7de] rounded-full"></div></div>
            <div class="flex items-center justify-between px-5 py-3 shrink-0"><div><p class="text-[11px] text-[#98a2b3] font-semibold uppercase tracking-wider">Search filter</p><h2 class="text-[1.2rem] font-bold text-[#101828]">What are you looking for?</h2></div><button type="button" class="mobile-modal-close w-9 h-9 rounded-full bg-[#f2f4f7] flex items-center justify-center hover:bg-[#e8eaed] transition-colors" aria-label="Close"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg></button></div>
            <div class="px-5 pb-3 shrink-0"><div class="relative"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-4 top-1/2 -translate-y-1/2 text-[#697386]" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg><input id="mobile-what-input" type="search" value="{{ $initialQuery }}" placeholder="Therapies, events, classes & more" class="w-full h-[52px] pl-10 pr-4 border border-[#dce1e7] rounded-[14px] text-[15px] text-[#1a202c] bg-white outline-none focus:border-[#4f9381] focus:ring-2 focus:ring-[#4f9381]/20 transition-all [appearance:textfield]" /></div></div>
            <div id="mobile-what-list" class="flex-1 overflow-y-auto px-2 pb-8"></div>
        </div>
    </div>
</div>
