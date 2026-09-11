@props([
    'searchUrl' => route('search'),
    'initialQuery' => request()->query('what', request()->query('q', '')),
    'initialWhere' => request()->query('where', ''),
    'initialMode' => request()->query('mode', ''),
    'mapboxKey' => config('services.mapbox.token'),
])

@php
    $searchUrl = rtrim($searchUrl, '/');
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap');

    [data-wow-home-searchbar-v4] {
        width: 100%;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__shell {
        position: relative;
        z-index: 50;
        width: 100%;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__desktop {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__desktop-shell {
        display: flex;
        align-items: center;
        min-height: 68px;
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(155, 165, 180, 0.45);
        border-radius: 9999px;
        box-shadow: 0 10px 30px rgba(28, 39, 56, 0.08);
        transition: box-shadow 200ms ease;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__field {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        height: 100%;
        padding: 0 20px;
        border-radius: 9999px;
        cursor: text;
        transition: background-color 150ms ease;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__field:hover {
        background: rgba(0, 0, 0, 0.025);
    }

    [data-wow-home-searchbar-v4] .wow-home-search__field--what {
        flex: 1.7;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__field--where {
        flex: 1;
        border-left: 1px solid rgba(155, 165, 180, 0.45);
    }

    [data-wow-home-searchbar-v4] .wow-home-search__icon {
        flex-shrink: 0;
        color: #8e9bb0;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__label {
        display: block;
        margin-bottom: 1px;
        font-size: 11px;
        font-weight: 650;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #758096;
        cursor: text;
        user-select: none;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__input {
        width: 100%;
        padding: 0;
        border: 0;
        outline: none;
        background: transparent;
        font-size: 14.5px;
        font-weight: 500;
        color: #1a202c;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__input::placeholder {
        color: #818896;
        font-weight: 400;
        opacity: 1;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__clear {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        border-radius: 9999px;
        background: #e4e8ee;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 150ms ease;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__clear:hover {
        background: #d0d5de;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 50;
        min-width: 320px;
        width: 100%;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 1rem;
        box-shadow: 0 16px 48px rgba(16, 24, 40, 0.14);
    }

    [data-wow-home-searchbar-v4] .wow-home-search__dropdown--where {
        left: auto;
        right: 0;
        min-width: 280px;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__dropdown-heading {
        padding: 12px 16px 4px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #98a2b3;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__option {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        text-align: left;
        transition: background-color 150ms ease;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__option:hover {
        background: #f7f9fb;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__option-title {
        flex: 1;
        font-size: 13.5px;
        font-weight: 500;
        color: #1a202c;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__option-pill {
        flex-shrink: 0;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 700;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__submit {
        flex-shrink: 0;
        width: 58px;
        height: 58px;
        margin-right: 4px;
        border-radius: 9999px;
        background: #101828;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
        transition: background-color 150ms ease, transform 150ms ease;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__submit:hover {
        background: #1d2939;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__submit:active {
        transform: scale(0.96);
    }

    [data-wow-home-searchbar-v4] .wow-home-search__mobile {
        width: 100%;
        max-width: 520px;
        margin: 0 auto;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__mobile-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__mobile-button {
        width: 100%;
        display: flex;
        align-items: center;
        min-height: 60px;
        padding: 0 20px;
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(117, 128, 150, 0.42);
        border-radius: 9999px;
        box-shadow: 0 8px 24px rgba(28, 39, 56, 0.09);
        transition: background-color 150ms ease, box-shadow 150ms ease;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__mobile-button:active {
        background: #f7f9fb;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__mobile-submit {
        width: 100%;
        min-height: 58px;
        border-radius: 9999px;
        background: #101828;
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
    }

    [data-wow-home-searchbar-v4] .wow-home-search__modal {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: none;
        align-items: flex-end;
        background: rgba(0, 0, 0, 0.4);
        padding: 12px;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__modal-panel {
        width: 100%;
        max-width: 560px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 28px;
        background: #fff;
        box-shadow: 0 24px 60px rgba(16, 24, 40, 0.24);
    }

    [data-wow-home-searchbar-v4] .wow-home-search__modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border-bottom: 1px solid #eaecf0;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__modal-close {
        padding: 8px;
        border-radius: 9999px;
        color: #667085;
        transition: background-color 150ms ease, color 150ms ease;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__modal-close:hover {
        background: #f2f4f7;
        color: #101828;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__modal-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d0d5dd;
        border-radius: 18px;
        font-size: 15px;
        font-weight: 500;
        color: #101828;
        outline: none;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__modal-input:focus {
        border-color: #98a2b3;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__modal-list {
        max-height: 55vh;
        overflow: auto;
        margin-top: 16px;
        display: grid;
        gap: 8px;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__mobile-where-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__mobile-where-grid > button {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 18px;
        border: 1px solid #eaecf0;
        text-align: left;
        transition: background-color 150ms ease;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__mobile-where-grid > button:hover {
        background: #f9fafb;
    }

    [data-wow-home-searchbar-v4] .wow-home-search__what-list,
    [data-wow-home-searchbar-v4] .wow-home-search__where-list {
        max-height: 290px;
        overflow: auto;
        padding: 8px 0;
    }

    @media (max-width: 991.98px) {
        [data-wow-home-searchbar-v4] .wow-home-search__desktop {
            display: none;
        }
    }

    @media (min-width: 992px) {
        [data-wow-home-searchbar-v4] .wow-home-search__mobile {
            display: none;
        }
    }
</style>

<div
    data-wow-home-searchbar-v4
    data-search-url="{{ $searchUrl }}"
    data-initial-query="{{ $initialQuery }}"
    data-initial-where="{{ $initialWhere }}"
    data-initial-mode="{{ $initialMode }}"
    data-mapbox-key="{{ $mapboxKey }}"
    class="wow-home-search__shell"
>
    <form
        id="wow-home-search-desktop"
        class="wow-home-search__desktop"
        autocomplete="off"
        novalidate
    >
        <div class="wow-home-search__desktop-shell">
            <div
                id="desktop-what-field"
                class="wow-home-search__field wow-home-search__field--what"
            >
                <span class="wow-home-search__icon flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#F4F6FB]">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                        <path d="M13 4l2.2 5.6L21 12l-5.8 2.4L13 20l-2.2-5.6L5 12l5.8-2.4L13 4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <label for="desktop-what" class="wow-home-search__label">What</label>
                    <input
                        id="desktop-what"
                        type="search"
                        class="wow-home-search__input block"
                        placeholder="Search offerings"
                        value="{{ $initialQuery }}"
                    >
                </div>

                <button
                    type="button"
                    id="desktop-clear-what"
                    class="wow-home-search__clear hidden h-8 w-8 shrink-0 items-center justify-center"
                    aria-label="Clear what"
                >
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M7 7l10 10M17 7L7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>

                <div
                    id="desktop-what-dropdown"
                    class="wow-home-search__dropdown hidden"
                >
                    <div class="flex items-center justify-between border-b border-[#EAECF0] px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-[#101828]">What are you looking for?</p>
                            <p class="text-xs text-[#667085]">Choose a category or search by name</p>
                        </div>
                    </div>
                    <ul id="desktop-what-list" class="wow-home-search__what-list"></ul>
                </div>
            </div>

            <div
                id="desktop-where-field"
                class="wow-home-search__field wow-home-search__field--where"
            >
                <span class="wow-home-search__icon flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#F4F6FB]">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                        <path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <label for="desktop-where" class="wow-home-search__label">Where</label>
                    <input
                        id="desktop-where"
                        type="search"
                        class="wow-home-search__input block"
                        placeholder="Online or location"
                        value="{{ $initialWhere }}"
                    >
                </div>

                <button
                    type="button"
                    id="desktop-clear-where"
                    class="wow-home-search__clear hidden h-8 w-8 shrink-0 items-center justify-center"
                    aria-label="Clear where"
                >
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M7 7l10 10M17 7L7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>

                <div
                    id="desktop-where-dropdown"
                    class="wow-home-search__dropdown wow-home-search__dropdown--where hidden"
                >
                    <div class="border-b border-[#EAECF0] px-4 py-3">
                        <p class="text-sm font-semibold text-[#101828]">Where would you like to go?</p>
                        <p class="text-xs text-[#667085]">Use your location, go online, or pick a place</p>
                    </div>
                    <div class="space-y-2 px-4 py-3">
                        <button type="button" id="desktop-use-location" class="flex w-full items-center gap-3 rounded-2xl border border-[#EAECF0] px-4 py-3 text-left transition-colors hover:bg-[#F9FAFB]">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-[#101828]">Use my location</span>
                                <span class="block text-xs text-[#667085]">Find offerings nearby</span>
                            </span>
                        </button>
                        <button type="button" id="desktop-online" class="flex w-full items-center gap-3 rounded-2xl border border-[#EAECF0] px-4 py-3 text-left transition-colors hover:bg-[#F9FAFB]">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M4 12h16M12 4c2.5 2.4 4 5.2 4 8s-1.5 5.6-4 8M12 4c-2.5 2.4-4 5.2-4 8s1.5 5.6 4 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-[#101828]">Online</span>
                                <span class="block text-xs text-[#667085]">Virtual sessions and classes</span>
                            </span>
                        </button>
                    </div>
                    <div class="border-t border-[#EAECF0] px-4 py-3">
                        <div id="desktop-location-list-heading-wrap" class="mb-3 hidden">
                            <p id="desktop-location-list-heading" class="text-xs font-semibold uppercase tracking-[0.16em] text-[#667085]">Popular places</p>
                        </div>
                        <ul id="desktop-location-list" class="wow-home-search__where-list space-y-2"></ul>
                    </div>
                </div>
            </div>

            <button
                type="submit"
                class="wow-home-search__submit"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
            </button>
        </div>
        <input type="hidden" id="desktop-mode" value="{{ $initialMode }}">
    </form>

    <form
        id="wow-home-search-mobile"
        class="wow-home-search__mobile"
        autocomplete="off"
        novalidate
    >
        <div class="wow-home-search__mobile-card">
            <div class="grid grid-cols-1 gap-2.5">
                <button
                    type="button"
                    id="mobile-open-what"
                    class="wow-home-search__mobile-button"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M13 4l2.2 5.6L21 12l-5.8 2.4L13 20l-2.2-5.6L5 12l5.8-2.4L13 4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-[#667085]">What</span>
                        <span id="mobile-what-label" class="mt-1 block truncate text-sm font-medium text-[#101828]">{{ $initialQuery ?: 'Search offerings' }}</span>
                    </span>
                </button>

                <button
                    type="button"
                    id="mobile-open-where"
                    class="wow-home-search__mobile-button"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-[#667085]">Where</span>
                        <span id="mobile-where-label" class="mt-1 block truncate text-sm font-medium text-[#101828]">{{ $initialWhere ?: 'Online or location' }}</span>
                    </span>
                </button>
            </div>

            <button
                type="submit"
                class="wow-home-search__mobile-submit mt-3 inline-flex items-center justify-center"
            >
                Search
            </button>
        </div>

        <div id="mobile-what-modal" class="fixed inset-0 z-50 hidden items-end bg-black/40 p-3">
            <div class="wow-home-search__modal-panel">
                <div class="wow-home-search__modal-head">
                    <div>
                        <p class="text-sm font-semibold text-[#101828]">What are you looking for?</p>
                        <p class="text-xs text-[#667085]">Choose a category or search by name</p>
                    </div>
                    <button type="button" data-close-mobile-modal class="wow-home-search__modal-close">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M7 7l10 10M17 7L7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-4">
                    <input
                        id="mobile-what-input"
                        type="search"
                        class="wow-home-search__modal-input"
                        placeholder="Search offerings"
                        value="{{ $initialQuery }}"
                    >
                    <ul id="mobile-what-list" class="wow-home-search__modal-list"></ul>
                </div>
            </div>
        </div>

        <div id="mobile-where-modal" class="fixed inset-0 z-50 hidden items-end bg-black/40 p-3">
            <div class="wow-home-search__modal-panel">
                <div class="wow-home-search__modal-head">
                    <div>
                        <p class="text-sm font-semibold text-[#101828]">Where would you like to go?</p>
                        <p class="text-xs text-[#667085]">Use your location, go online, or pick a place</p>
                    </div>
                    <button type="button" data-close-mobile-modal class="wow-home-search__modal-close">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M7 7l10 10M17 7L7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-4">
                    <input
                        id="mobile-where-input"
                        type="search"
                        class="wow-home-search__modal-input"
                        placeholder="Online or location"
                        value="{{ $initialWhere }}"
                    >
                    <div class="wow-home-search__mobile-where-grid mt-4 sm:grid-cols-2">
                        <button type="button" id="mobile-use-location" class="flex items-center gap-3 rounded-2xl border border-[#EAECF0] px-4 py-3 text-left transition-colors hover:bg-[#F9FAFB]">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <path d="M12 21s6-5.2 6-11a6 6 0 1 0-12 0c0 5.8 6 11 6 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-[#101828]">Use my location</span>
                                <span class="block text-xs text-[#667085]">Find offerings nearby</span>
                            </span>
                        </button>
                        <button type="button" id="mobile-online" class="flex items-center gap-3 rounded-2xl border border-[#EAECF0] px-4 py-3 text-left transition-colors hover:bg-[#F9FAFB]">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#F4F6FB] text-[#344054]">
                                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M4 12h16M12 4c2.5 2.4 4 5.2 4 8s-1.5 5.6-4 8M12 4c-2.5 2.4-4 5.2-4 8s1.5 5.6 4 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-[#101828]">Online</span>
                                <span class="block text-xs text-[#667085]">Virtual sessions and classes</span>
                            </span>
                        </button>
                    </div>
                    <div class="mt-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-[#667085]">Popular places</p>
                        <ul id="mobile-where-list" class="wow-home-search__modal-list"></ul>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="mobile-mode" value="{{ $initialMode }}">
    </form>
</div>
