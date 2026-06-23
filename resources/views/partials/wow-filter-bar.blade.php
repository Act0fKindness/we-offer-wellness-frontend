@php
  $action = trim((string) ($action ?? url()->current()));
  $clearUrl = trim((string) ($clearUrl ?? $action));
  $ariaLabel = trim((string) ($ariaLabel ?? 'Filters'));
  $mobileLabel = trim((string) ($mobileLabel ?? 'Filters'));
  $resultLabel = trim((string) ($resultLabel ?? 'results'));
  $segments = array_values(is_array($segments ?? null) ? $segments : []);
  $chips = array_values(is_array($chips ?? null) ? $chips : []);
  $currentQuery = is_array($currentQuery ?? null) ? $currentQuery : request()->query();
  $resultCount = (int) ($resultCount ?? 0);
  $filterId = trim((string) ($filterId ?? 'wow-filter-' . substr(md5($action . '|' . $ariaLabel), 0, 10)));
  $filterColumns = max(1, min(4, count($segments)));

  $defaultIcons = [
    'sort' => '<svg viewBox="0 0 24 24"><path d="M7 4v16M7 4 4 7M7 4l3 3M17 20V4M17 20l-3-3M17 20l3-3"/></svg>',
    'type' => '<svg viewBox="0 0 24 24"><path d="M4 7h6M14 7h6M10 7a2 2 0 1 0 4 0 2 2 0 0 0-4 0ZM4 17h10M18 17h2M14 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z"/></svg>',
    'format' => '<svg viewBox="0 0 24 24"><path d="M7 3v3M17 3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="M8 13h.01M12 13h.01M16 13h.01M8 17h.01M12 17h.01M16 17h.01"/></svg>',
    'location' => '<svg viewBox="0 0 24 24"><path d="M11.6 11.6c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm0-7.6C8.5 4 6 6.5 6 9.6 6 13.8 11.6 20 11.6 20s5.6-6.2 5.6-10.4c0-3.1-2.5-5.6-5.6-5.6z"/></svg>',
    'default' => '<svg viewBox="0 0 24 24"><path d="M5 6h14M5 12h10M5 18h14"/></svg>',
  ];

  $queryJson = json_encode($currentQuery, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

@once
  @push('head')
    <style>
      .wow-filter-component {
        position: relative;
        z-index: 5;
        --wow-filter-ink: #111827;
        --wow-filter-muted: #667085;
        --wow-filter-line: #dfe5ee;
        --wow-filter-soft-line: #edf1f5;
        --wow-filter-green: #549483;
        --wow-filter-green-dark: #437c6d;
        --wow-filter-green-soft: #e8f5f1;
        --wow-filter-shadow: 0 24px 64px rgba(16, 24, 40, .12);
        --wow-filter-shadow-soft: 0 16px 42px rgba(16, 24, 40, .075);
        --wow-filter-focus: 0 0 0 4px rgba(84, 148, 131, .18);
        --wow-filter-font: 'Manrope', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        --wow-filter-serif: 'Playfair Display', Georgia, serif;
      }

      .wow-filter-component,
      .wow-filter-component button,
      .wow-filter-component input {
        font-family: var(--wow-filter-font);
      }

      .wow-filter-status {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        white-space: nowrap;
        clip-path: inset(50%);
      }

      .wow-filter-shell {
        position: relative;
        border: 1px solid rgba(223, 229, 238, .96);
        border-radius: 28px;
        background: rgba(255, 255, 255, .96);
        box-shadow: var(--wow-filter-shadow-soft);
        backdrop-filter: blur(14px);
      }

      .wow-filter-main {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        min-height: 86px;
        padding: 8px;
      }

      .wow-filter-trigger {
        position: relative;
        min-width: 0;
        border: 0;
        border-radius: 20px;
        background: transparent;
        color: var(--wow-filter-ink);
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) 22px;
        gap: 13px;
        align-items: center;
        padding: 0 18px;
        text-align: left;
        transition: background 160ms ease, box-shadow 160ms ease, transform 160ms ease;
        isolation: isolate;
      }

      .wow-filter-trigger:hover,
      .wow-filter-trigger.is-open {
        background: #f8faf9;
        z-index: 2;
      }

      .wow-filter-trigger.is-open {
        box-shadow: inset 0 0 0 1px rgba(84, 148, 131, .18), 0 0 0 3px rgba(84, 148, 131, .14);
        transform: translateY(-1px);
      }

      .wow-filter-trigger:focus-visible,
      .wow-panel-close:focus-visible,
      .wow-panel-option:focus-visible,
      .wow-panel-done:focus-visible,
      .wow-panel-clear:focus-visible,
      .wow-chip button:focus-visible,
      .wow-mini-pill:focus-visible,
      .wow-input-button:focus-visible,
      .wow-filter-input input:focus-visible {
        outline: none;
        box-shadow: var(--wow-filter-focus);
      }

      .wow-filter-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #565d67;
      }

      .wow-filter-icon svg,
      .wow-chevron svg {
        width: 20px;
        height: 20px;
        stroke: currentColor;
        fill: none;
        stroke-width: 1.8;
      }

      .wow-filter-copy {
        min-width: 0;
      }

      .wow-filter-label {
        display: block;
        color: var(--wow-filter-muted);
        font-size: 14px;
        line-height: 1.1;
      }

      .wow-filter-value {
        display: block;
        margin-top: 4px;
        color: var(--wow-filter-ink);
        font-size: 17px;
        line-height: 1.2;
        letter-spacing: -.015em;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .wow-filter-value.is-placeholder {
        color: #747b84;
      }

      .wow-chevron {
        width: 20px;
        height: 20px;
        color: #4b5563;
        transition: transform 180ms ease;
      }

      .wow-filter-trigger.is-open .wow-chevron {
        transform: rotate(180deg);
      }

      .wow-panel {
        position: absolute;
        left: var(--panel-left, 0);
        top: calc(100% + 10px);
        z-index: 100;
        width: min(var(--panel-width, 430px), calc(100vw - 40px));
        border: 1px solid var(--wow-filter-line);
        border-radius: 24px;
        background: #fff;
        box-shadow: var(--wow-filter-shadow);
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px) scale(.985);
        transform-origin: var(--panel-arrow, 32px) top;
        transition: opacity 170ms ease, visibility 170ms ease, transform 170ms ease;
      }

      .wow-panel.is-open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
      }

      .wow-panel::before {
        content: "";
        position: absolute;
        left: var(--panel-arrow, 32px);
        top: -7px;
        width: 14px;
        height: 14px;
        background: #fff;
        border-left: 1px solid var(--wow-filter-line);
        border-top: 1px solid var(--wow-filter-line);
        transform: rotate(45deg);
      }

      .wow-panel-header {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 18px 20px;
        border-bottom: 1px solid var(--wow-filter-soft-line);
        background: #fbfcfd;
      }

      .wow-panel-header-icon {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--wow-filter-green-soft);
        color: var(--wow-filter-green-dark);
      }

      .wow-panel-header-icon svg {
        width: 24px;
        height: 24px;
        stroke: currentColor;
        fill: none;
        stroke-width: 1.8;
      }

      .wow-panel-title {
        margin: 0;
        color: #111827;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.02em;
      }

      .wow-panel-subtitle {
        margin: 3px 0 0;
        color: var(--wow-filter-muted);
        font-size: 13px;
        line-height: 1.35;
      }

      .wow-panel-close {
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 999px;
        background: #fff;
        color: #111827;
        font-size: 25px;
        line-height: 1;
      }

      .wow-panel-close:hover {
        background: #f3f4f6;
      }

      .wow-panel-body {
        padding: 14px;
      }

      .wow-panel-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px;
        border-top: 1px solid var(--wow-filter-soft-line);
        background: #fbfcfd;
      }

      .wow-panel-clear,
      .wow-panel-done {
        min-height: 42px;
        border-radius: 999px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 700;
        border: 0;
      }

      .wow-panel-clear {
        background: transparent;
        color: var(--wow-filter-muted);
      }

      .wow-panel-clear:hover {
        color: #111827;
        background: #f2f4f7;
      }

      .wow-panel-done {
        background: var(--wow-filter-green);
        color: #fff;
      }

      .wow-panel-done:hover {
        background: var(--wow-filter-green-dark);
      }

      .wow-panel-options {
        display: grid;
        gap: 6px;
      }

      .wow-panel-option {
        width: 100%;
        min-height: 54px;
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr) auto;
        gap: 11px;
        align-items: center;
        border: 1px solid transparent;
        border-radius: 15px;
        background: #fff;
        color: #111827;
        padding: 0 12px;
        text-align: left;
        text-decoration: none;
        transition: background 150ms ease, border-color 150ms ease, transform 150ms ease;
      }

      .wow-panel-option:hover,
      .wow-panel-option[aria-checked="true"] {
        background: #f7faf9;
        border-color: rgba(84, 148, 131, .18);
      }

      .wow-panel-option:active {
        transform: scale(.992);
      }

      .wow-option-radio {
        width: 22px;
        height: 22px;
        border: 1.5px solid #cbd5e1;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
      }

      .wow-panel-option[aria-checked="true"] .wow-option-radio {
        border-color: var(--wow-filter-green);
      }

      .wow-panel-option[aria-checked="true"] .wow-option-radio::after {
        content: "";
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--wow-filter-green);
      }

      .wow-option-title {
        display: block;
        font-size: 15px;
        font-weight: 750;
      }

      .wow-option-subtitle {
        display: block;
        margin-top: 3px;
        color: var(--wow-filter-muted);
        font-size: 13px;
      }

      .wow-option-count {
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #f2f4f7;
        color: var(--wow-filter-muted);
        padding: 0 10px;
        font-size: 13px;
        white-space: nowrap;
      }

      .wow-filter-input {
        display: grid;
        gap: 8px;
      }

      .wow-filter-input label {
        color: var(--wow-filter-muted);
        font-size: 13px;
        font-weight: 700;
      }

      .wow-filter-input input {
        width: 100%;
        height: 50px;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        padding: 0 12px;
        font-size: 15px;
      }

      .wow-filter-input input:focus {
        outline: none;
        border-color: rgba(84, 148, 131, .52);
      }

      .wow-input-button {
        width: 100%;
        min-height: 46px;
        border: 0;
        border-radius: 14px;
        background: var(--wow-filter-green);
        color: #fff;
        font-size: 14px;
        font-weight: 800;
      }

      .wow-input-button:hover {
        background: var(--wow-filter-green-dark);
      }

      .wow-filter-meta {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-top: 14px;
        padding: 0 4px;
      }

      .wow-active-chips {
        min-width: 0;
        flex: 1 1 auto;
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        align-items: center;
      }

      .wow-active-chips:empty {
        display: none;
      }

      .wow-filter-results {
        flex: 0 0 auto;
        margin-left: auto;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(84, 148, 131, .20);
        border-radius: 999px;
        background: var(--wow-filter-green-soft);
        color: #2f6f60;
        padding: 0 15px;
        font-size: 14px;
        font-weight: 750;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .045);
      }

      .wow-filter-results span {
        color: var(--wow-filter-muted);
        font-weight: 550;
        margin-left: 5px;
      }

      .wow-chip {
        min-height: 40px;
        max-width: 100%;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        border: 1px solid #e1e7ee;
        border-radius: 999px;
        background: rgba(255, 255, 255, .94);
        color: #1f2937;
        padding: 0 11px 0 14px;
        font-size: 14px;
        line-height: 1;
        box-shadow: 0 10px 24px rgba(16, 24, 40, .055);
        animation: wow-chip-in 180ms ease both;
      }

      @keyframes wow-chip-in {
        from { opacity: 0; transform: translateY(4px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
      }

      .wow-chip strong {
        font-weight: 750;
      }

      .wow-chip button {
        width: 22px;
        height: 22px;
        border: 1px solid rgba(84, 148, 131, .18);
        padding: 0;
        border-radius: 999px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(241, 246, 244, .9));
        color: #3f6e61;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-shadow: 0 6px 14px rgba(16, 24, 40, .08);
        transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease, border-color .16s ease, color .16s ease;
      }

      .wow-chip button svg {
        width: 12px;
        height: 12px;
        display: block;
        fill: currentColor;
      }

      .wow-chip button:hover {
        transform: translateY(-1px);
        border-color: rgba(84, 148, 131, .34);
        background: linear-gradient(180deg, #fff, #e8f4f0);
        color: #2f5d51;
        box-shadow: 0 8px 18px rgba(16, 24, 40, .12);
      }

      .wow-active-chips > .wow-panel-clear[data-clear-all] {
        min-height: 40px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: var(--wow-filter-green-dark);
        padding: 0 8px;
        box-shadow: none;
        font-size: 14px;
        font-weight: 750;
      }

      .wow-active-chips > .wow-panel-clear[data-clear-all]:hover {
        background: var(--wow-filter-green-soft);
      }

      .wow-mobile-filter-toggle {
        display: none;
        width: 100%;
        min-height: 64px;
        border: 1px solid var(--wow-filter-line);
        border-radius: 20px;
        background: #fff;
        color: #111827;
        box-shadow: var(--wow-filter-shadow-soft);
        align-items: center;
        justify-content: space-between;
        padding: 0 18px;
        font-size: 17px;
        font-weight: 750;
      }

      .wow-mobile-filter-toggle span {
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: var(--wow-filter-green-soft);
        color: #2f6f60;
        padding: 0 10px;
        font-size: 13px;
      }

      .wow-more-section {
        display: grid;
        gap: 18px;
      }

      .wow-more-heading {
        margin: 0;
        color: #111827;
        font-size: 13px;
        font-weight: 850;
        letter-spacing: .08em;
        text-transform: uppercase;
      }

      @media (max-width: 1050px) {
        .wow-filter-main {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }
      }

      @media (max-width: 700px) {
        .wow-mobile-filter-toggle {
          display: flex;
        }

        .wow-filter-shell {
          position: fixed;
          inset: auto 0 0;
          z-index: 200;
          border-radius: 28px 28px 0 0;
          max-height: 88vh;
          overflow: auto;
          transform: translateY(calc(100% + 20px));
          transition: transform 260ms cubic-bezier(.2, .8, .2, 1);
          box-shadow: 0 -24px 70px rgba(16, 24, 40, .18);
        }

        .wow-filter-shell.is-mobile-open {
          transform: translateY(0);
        }

        .wow-filter-main {
          grid-template-columns: 1fr;
          padding: 10px;
          gap: 8px;
        }

        .wow-filter-trigger {
          min-height: 72px;
        }

        .wow-filter-meta {
          display: grid;
          grid-template-columns: minmax(0, 1fr) auto;
          align-items: start;
        }

        .wow-filter-results {
          grid-column: 2;
          grid-row: 1;
        }

        .wow-active-chips {
          grid-column: 1 / -1;
          grid-row: 2;
          margin-top: 8px;
        }

        .wow-panel {
          position: fixed;
          inset: auto 12px 12px !important;
          width: auto;
          max-height: 84vh;
          overflow: auto;
          border-radius: 24px;
        }

        .wow-panel::before {
          display: none;
        }

        .wow-panel-footer {
          position: sticky;
          bottom: 0;
          z-index: 5;
        }

        .wow-panel-done {
          min-width: 112px;
          box-shadow: 0 12px 24px rgba(84, 148, 131, .20);
        }
      }

      @media (max-width: 520px) {
        .wow-chip {
          width: auto;
          max-width: calc(100vw - 42px);
        }

        .wow-panel-header {
          grid-template-columns: 38px minmax(0, 1fr) auto;
          padding: 16px;
        }
      }

      @media (prefers-reduced-motion: reduce) {
        .wow-filter-component *,
        .wow-filter-component *::before,
        .wow-filter-component *::after {
          animation-duration: .001ms !important;
          animation-iteration-count: 1 !important;
          transition-duration: .001ms !important;
          scroll-behavior: auto !important;
        }
      }
    </style>
  @endpush

  @push('scripts')
    <script>
      (() => {
        const root = document.querySelector('[data-wow-filter-component][data-wow-filter-id="{{ $filterId }}"]');
        if (!root) return;

        const shell = root.querySelector('[data-filter-shell]');
        const triggers = [...root.querySelectorAll('[data-filter-trigger]')];
        const panels = [...root.querySelectorAll('[data-panel]')];
        const mobileToggle = root.querySelector('[data-mobile-toggle]');
        const live = root.querySelector('[data-live-status]');
        const chips = root.querySelector('[data-chip-list]');
        const resultCount = root.querySelector('[data-result-count]');
        const mobileCount = root.querySelector('[data-mobile-count]');
        const currentQuery = JSON.parse(root.dataset.currentQuery || '{}');
        const action = root.dataset.action || window.location.pathname;
        const clearUrl = root.dataset.clearUrl || action;
        const filterId = root.dataset.wowFilterId || '';
        const isMobile = () => window.matchMedia('(max-width: 700px)').matches;

        function announce(message) {
          if (live) live.textContent = message;
        }

        function buildUrl(updates = {}, clearPage = true) {
          const url = new URL(action, window.location.origin);
          const next = { ...currentQuery, ...updates };

          Object.entries(next).forEach(([key, value]) => {
            if (value === null || value === undefined || value === '') {
              url.searchParams.delete(key);
            } else {
              url.searchParams.set(key, value);
            }
          });

          if (clearPage) {
            url.searchParams.delete('page');
          }

          return url.toString();
        }

        function closePanels({ restoreFocus = false } = {}) {
          const openTrigger = triggers.find((trigger) => trigger.classList.contains('is-open'));
          triggers.forEach((trigger) => {
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
          });
          panels.forEach((panel) => panel.classList.remove('is-open'));

          if (restoreFocus && openTrigger && !isMobile()) {
            openTrigger.focus();
          }
        }

        function closeMobileFilters() {
          root.classList.remove('is-mobile-panel-flow');
          shell?.classList.remove('is-mobile-open');
          mobileToggle?.setAttribute('aria-expanded', 'false');
        }

        function openPanel(name, trigger) {
          const panel = root.querySelector(`[data-panel="${name}"]`);
          if (!panel) return;

          closePanels();

          trigger.classList.add('is-open');
          trigger.setAttribute('aria-expanded', 'true');

          const rootRect = root.getBoundingClientRect();
          const triggerRect = trigger.getBoundingClientRect();
          const preferredWidth = Number(panel.dataset.panelWidth || (name === 'location' ? 480 : 430));
          const width = Math.min(preferredWidth, window.innerWidth - 40);
          let left = triggerRect.left - rootRect.left;
          const maxLeft = Math.max(0, rootRect.width - width);
          left = Math.max(0, Math.min(left, maxLeft));
          const arrow = Math.max(28, triggerRect.left - rootRect.left - left + 32);

          panel.style.setProperty('--panel-left', `${left}px`);
          panel.style.setProperty('--panel-width', `${preferredWidth}px`);
          panel.style.setProperty('--panel-arrow', `${arrow}px`);
          panel.classList.add('is-open');

          if (isMobile()) {
            root.classList.add('is-mobile-panel-flow');
            shell?.classList.remove('is-mobile-open');
            mobileToggle?.setAttribute('aria-expanded', 'false');
          }

          const focusable = panel.querySelector('button:not([data-close-panel]), input');
          window.setTimeout(() => focusable?.focus(), 50);
        }

        function applyUpdate(param, value) {
          window.location.href = buildUrl({ [param]: value });
        }

        function removeParam(param) {
          window.location.href = buildUrl({ [param]: null });
        }

        function syncMobileCount() {
          const activeCount = chips ? chips.querySelectorAll('.wow-chip').length : 0;
          if (mobileCount) {
            mobileCount.textContent = `${activeCount} active`;
          }
        }

        triggers.forEach((trigger) => {
          trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const name = trigger.dataset.filterTrigger;
            if (trigger.classList.contains('is-open')) {
              closePanels({ restoreFocus: true });
            } else {
              openPanel(name, trigger);
            }
          });

          trigger.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'ArrowDown') {
              event.preventDefault();
              openPanel(trigger.dataset.filterTrigger, trigger);
            }
          });
        });

        root.querySelectorAll('[data-close-panel]').forEach((button) => {
          button.addEventListener('click', () => {
            if (isMobile() && root.classList.contains('is-mobile-panel-flow')) {
              closeMobileFilters();
            } else {
              closePanels({ restoreFocus: true });
            }
          });
        });

        root.querySelectorAll('[data-filter-option]').forEach((button) => {
          button.addEventListener('click', () => {
            const param = button.dataset.param || button.closest('[data-panel]')?.dataset.panel || '';
            const value = button.dataset.value || '';
            applyUpdate(param, value);
          });
        });

        root.querySelectorAll('[data-remove-param]').forEach((button) => {
          button.addEventListener('click', () => {
            removeParam(button.dataset.removeParam || '');
          });
        });

        root.querySelectorAll('[data-clear-all]').forEach((button) => {
          button.addEventListener('click', () => {
            window.location.href = clearUrl;
          });
        });

        root.querySelectorAll('[data-clear-param]').forEach((button) => {
          button.addEventListener('click', () => {
            removeParam(button.dataset.clearParam || '');
          });
        });

        root.querySelectorAll('[data-apply-query]').forEach((button) => {
          button.addEventListener('click', () => {
            const panel = button.closest('[data-panel]');
            const input = panel?.querySelector('[data-filter-input]');
            const param = button.dataset.param || '';
            const value = (input?.value || '').trim();
            applyUpdate(param, value);
          });
        });

        root.querySelectorAll('[data-filter-input]').forEach((input) => {
          input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
              event.preventDefault();
              const param = input.dataset.filterInput || '';
              applyUpdate(param, input.value.trim());
            }
          });
        });

        mobileToggle?.addEventListener('click', () => {
          const next = !shell?.classList.contains('is-mobile-open');
          closePanels();
          root.classList.remove('is-mobile-panel-flow');
          shell?.classList.toggle('is-mobile-open', next);
          mobileToggle.setAttribute('aria-expanded', next ? 'true' : 'false');
        });

        document.addEventListener('click', (event) => {
          if (!root.contains(event.target)) {
            closePanels();
            closeMobileFilters();
          }
        });

        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape') {
            closePanels({ restoreFocus: true });
            closeMobileFilters();
          }
        });

        root.querySelectorAll('[data-panel]').forEach((panel) => {
          const trigger = root.querySelector(`[data-filter-trigger="${panel.dataset.panel}"]`);
          if (!trigger) return;

          const rootRect = root.getBoundingClientRect();
          const triggerRect = trigger.getBoundingClientRect();
          const preferredWidth = Number(panel.dataset.panelWidth || (panel.dataset.panel === 'location' ? 480 : 430));
          const width = Math.min(preferredWidth, window.innerWidth - 40);
          let left = triggerRect.left - rootRect.left;
          const maxLeft = Math.max(0, rootRect.width - width);
          left = Math.max(0, Math.min(left, maxLeft));
          const arrow = Math.max(28, triggerRect.left - rootRect.left - left + 32);

          panel.style.setProperty('--panel-left', `${left}px`);
          panel.style.setProperty('--panel-width', `${preferredWidth}px`);
          panel.style.setProperty('--panel-arrow', `${arrow}px`);
        });

        if (chips && chips.children.length > 0) {
          syncMobileCount();
        } else if (mobileCount) {
          mobileCount.textContent = '0 active';
        }

        if (resultCount) {
          announce(`${resultCount.textContent || 0} results available with current filters.`);
        }

        if (!root.dataset.wowFilterId) {
          root.dataset.wowFilterId = filterId;
        }
      })();
    </script>
  @endpush
@endonce

<section
  class="wow-filter-component"
  id="{{ $filterId }}"
  data-wow-filter-component
  data-wow-filter-id="{{ $filterId }}"
  data-action="{{ $action }}"
  data-clear-url="{{ $clearUrl }}"
  data-current-query='@json($currentQuery, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)'
  aria-label="{{ $ariaLabel }}"
>
  <button class="wow-mobile-filter-toggle" type="button" data-mobile-toggle aria-expanded="false">
    {{ $mobileLabel }}
    <span data-mobile-count>{{ count($chips) }} active</span>
  </button>

  <div class="wow-filter-status" aria-live="polite" data-live-status>Filters ready.</div>

  <div class="wow-filter-shell" data-filter-shell>
    <div class="wow-filter-main" style="grid-template-columns: repeat({{ $filterColumns }}, minmax(0, 1fr));">
      @foreach($segments as $segment)
        @php
          $segmentKey = trim((string) ($segment['key'] ?? 'filter-' . $loop->index));
          $segmentLabel = trim((string) ($segment['label'] ?? ucfirst($segmentKey)));
          $segmentValue = trim((string) ($segment['value'] ?? ''));
          $segmentPlaceholder = trim((string) ($segment['placeholder'] ?? 'Any'));
          $segmentIcon = (string) ($segment['icon'] ?? ($defaultIcons[$segmentKey] ?? $defaultIcons['default']));
          $segmentPanelTitle = trim((string) ($segment['panelTitle'] ?? $segmentLabel));
          $segmentPanelSubtitle = trim((string) ($segment['panelSubtitle'] ?? 'Select an option.'));
          $segmentKind = strtolower(trim((string) ($segment['kind'] ?? 'options')));
          $segmentParam = trim((string) ($segment['param'] ?? $segmentKey));
          $segmentPanelWidth = (int) ($segment['panelWidth'] ?? 430);
          $segmentId = $filterId . '-panel-' . $segmentKey;
        @endphp
        <button class="wow-filter-trigger" type="button" data-filter-trigger="{{ $segmentKey }}" aria-expanded="false" aria-controls="{{ $segmentId }}">
          <span class="wow-filter-icon">{!! $segmentIcon !!}</span>
          <span class="wow-filter-copy">
            <span class="wow-filter-label">{{ $segmentLabel }}</span>
            <span class="wow-filter-value {{ $segmentValue === '' ? 'is-placeholder' : '' }}" data-value="{{ $segmentKey }}">{{ $segmentValue !== '' ? $segmentValue : $segmentPlaceholder }}</span>
          </span>
          <span class="wow-chevron"><svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></span>
        </button>
      @endforeach
    </div>
  </div>

  <div class="wow-filter-meta" aria-label="Selected filters and result count">
    <div class="wow-active-chips" data-chip-list aria-label="Active filters">
      @foreach($chips as $chip)
        @php
          $chipLabel = trim((string) ($chip['label'] ?? 'Filter'));
          $chipValue = trim((string) ($chip['value'] ?? ''));
          $chipParam = trim((string) ($chip['param'] ?? ''));
        @endphp
        @if($chipLabel !== '' && $chipValue !== '' && $chipParam !== '')
          <span class="wow-chip">
            <span><strong>{{ $chipLabel }}:</strong> {{ $chipValue }}</span>
            <button type="button" data-remove-param="{{ $chipParam }}" aria-label="Remove {{ $chipLabel }} filter">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M6.7 6.7a1 1 0 0 1 1.4 0L12 10.6l3.9-3.9a1 1 0 1 1 1.4 1.4L13.4 12l3.9 3.9a1 1 0 1 1-1.4 1.4L12 13.4l-3.9 3.9a1 1 0 0 1-1.4-1.4l3.9-3.9-3.9-3.9a1 1 0 0 1 0-1.4Z"/>
              </svg>
            </button>
          </span>
        @endif
      @endforeach
      @if(!empty($chips))
        <button class="wow-panel-clear" type="button" data-clear-all>Clear filters</button>
      @endif
    </div>
    <div class="wow-filter-results">
      <strong data-result-count>{{ $resultCount }}</strong><span>{{ $resultLabel }}</span>
    </div>
  </div>

  @foreach($segments as $segment)
    @php
      $segmentKey = trim((string) ($segment['key'] ?? 'filter-' . $loop->index));
      $segmentLabel = trim((string) ($segment['label'] ?? ucfirst($segmentKey)));
      $segmentPanelTitle = trim((string) ($segment['panelTitle'] ?? $segmentLabel));
      $segmentPanelSubtitle = trim((string) ($segment['panelSubtitle'] ?? 'Select an option.'));
      $segmentKind = strtolower(trim((string) ($segment['kind'] ?? 'options')));
      $segmentParam = trim((string) ($segment['param'] ?? $segmentKey));
      $segmentPanelWidth = (int) ($segment['panelWidth'] ?? 430);
      $segmentId = $filterId . '-panel-' . $segmentKey;
      $segmentPlaceholder = trim((string) ($segment['placeholder'] ?? 'Any'));
    @endphp
    <div class="wow-panel" id="{{ $segmentId }}" data-panel="{{ $segmentKey }}" data-panel-width="{{ $segmentPanelWidth }}" role="dialog" aria-modal="false" aria-labelledby="{{ $segmentId }}-title">
      <div class="wow-panel-header">
        <span class="wow-panel-header-icon">{!! $segment['icon'] ?? ($defaultIcons[$segmentKey] ?? $defaultIcons['default']) !!}</span>
        <span>
          <h2 class="wow-panel-title" id="{{ $segmentId }}-title">{{ $segmentPanelTitle }}</h2>
          <p class="wow-panel-subtitle">{{ $segmentPanelSubtitle }}</p>
        </span>
        <button class="wow-panel-close" type="button" data-close-panel aria-label="Close">×</button>
      </div>
      <div class="wow-panel-body">
        @if($segmentKind === 'input')
          <div class="wow-filter-input">
            <label for="{{ $segmentId }}-input">{{ $segment['inputLabel'] ?? $segmentLabel }}</label>
            <input
              id="{{ $segmentId }}-input"
              type="text"
              data-filter-input="{{ $segmentParam }}"
              value="{{ trim((string) ($segment['inputValue'] ?? '')) }}"
              placeholder="{{ $segment['inputPlaceholder'] ?? $segmentPlaceholder ?? 'Type here' }}"
            >
            <button class="wow-input-button" type="button" data-apply-query data-param="{{ $segmentParam }}">
              {{ $segment['buttonLabel'] ?? 'Apply' }}
            </button>
          </div>
        @else
          <div class="wow-panel-options" role="radiogroup" aria-label="{{ $segmentLabel }}">
            @foreach(array_values((array) ($segment['options'] ?? [])) as $option)
              @php
                $optionLabel = trim((string) ($option['label'] ?? 'Option'));
                $optionValue = (string) ($option['value'] ?? '');
                $optionSubtitle = trim((string) ($option['subtitle'] ?? ''));
                $optionCount = trim((string) ($option['count'] ?? ''));
                $optionSelected = (bool) ($option['selected'] ?? false);
              @endphp
              <button class="wow-panel-option" type="button" role="radio" aria-checked="{{ $optionSelected ? 'true' : 'false' }}" data-filter-option data-param="{{ $segmentParam }}" data-value="{{ $optionValue }}">
                <span class="wow-option-radio"></span>
                <span>
                  <span class="wow-option-title">{{ $optionLabel }}</span>
                  @if($optionSubtitle !== '')
                    <span class="wow-option-subtitle">{{ $optionSubtitle }}</span>
                  @endif
                </span>
                @if($optionCount !== '')
                  <span class="wow-option-count">{{ $optionCount }}</span>
                @endif
              </button>
            @endforeach
          </div>
        @endif
      </div>
      <div class="wow-panel-footer">
        <button class="wow-panel-clear" type="button" data-clear-param="{{ $segmentParam }}">Clear</button>
        <button class="wow-panel-done" type="button" data-close-panel>Done</button>
      </div>
    </div>
  @endforeach
</section>
