<div class="wow-cookie-banner" id="wowCookieBanner" data-cookie-banner hidden aria-hidden="true">
  <div class="wow-cookie-banner__panel" role="dialog" aria-modal="true" aria-labelledby="wowCookieTitle" aria-describedby="wowCookieDesc">
    <div class="wow-cookie-banner__head">
      <div>
        <p class="wow-cookie-banner__eyebrow">Privacy in practice</p>
        <h2 id="wowCookieTitle">Your cookie preferences</h2>
        <p id="wowCookieDesc">We use cookies to keep the site secure, understand performance, and tailor rituals you’ll actually enjoy. You’re in control of what’s on.</p>
      </div>
      <button type="button" class="wow-cookie-banner__close" data-cookie-dismiss aria-label="Close cookie preferences">
        ×
      </button>
    </div>

    <div class="wow-cookie-banner__toggles">
      <article class="wow-cookie-toggle">
        <div>
          <p class="wow-cookie-toggle__title">Essential</p>
          <p class="wow-cookie-toggle__copy">Security, session stability, and basic booking features. These always stay on.</p>
        </div>
        <span class="wow-cookie-toggle__badge">Required</span>
      </article>
      <article class="wow-cookie-toggle" data-cookie-option>
        <div>
          <p class="wow-cookie-toggle__title">Analytics</p>
          <p class="wow-cookie-toggle__copy">Helps us understand which rituals resonate so we can improve the product.</p>
        </div>
        <button type="button" class="wow-cookie-switch" data-cookie-toggle="analytics" aria-pressed="false" aria-label="Toggle analytics cookies">
          <span class="wow-cookie-switch__handle"></span>
        </button>
      </article>
      <article class="wow-cookie-toggle" data-cookie-option>
        <div>
          <p class="wow-cookie-toggle__title">Personalisation</p>
          <p class="wow-cookie-toggle__copy">Remembers your goals, locations and browsing vibe for more relevant recommendations.</p>
        </div>
        <button type="button" class="wow-cookie-switch" data-cookie-toggle="personalization" aria-pressed="false" aria-label="Toggle personalisation cookies">
          <span class="wow-cookie-switch__handle"></span>
        </button>
      </article>
      <article class="wow-cookie-toggle" data-cookie-option>
        <div>
          <p class="wow-cookie-toggle__title">Marketing</p>
          <p class="wow-cookie-toggle__copy">Lets us share the right launches across email, ads and social without overloading you.</p>
        </div>
        <button type="button" class="wow-cookie-switch" data-cookie-toggle="marketing" aria-pressed="false" aria-label="Toggle marketing cookies">
          <span class="wow-cookie-switch__handle"></span>
        </button>
      </article>
    </div>

    <p class="wow-cookie-banner__note">You can revisit these settings anytime from the footer or our <a href="/cookies">cookie policy</a>. Preferences refresh every 12 months.</p>

    <div class="wow-cookie-banner__actions">
      <button type="button" class="btn-wow btn-wow--ghost" data-cookie-reject>Reject non-essential</button>
      <button type="button" class="btn-wow btn-wow--outline" data-cookie-save>Save choices</button>
      <button type="button" class="btn-wow btn-wow--cta" data-cookie-accept>Accept all</button>
    </div>
  </div>
</div>

<style>
  .wow-cookie-banner{ position:fixed; inset:auto 20px 20px auto; z-index:1200; max-width:420px; width:100%; font-family:'Manrope',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; }
  .wow-cookie-banner[hidden]{ display:none !important; }
  .wow-cookie-banner__panel{ background:#0b1220; color:#fff; border-radius:18px; padding:28px; box-shadow:0 30px 120px rgba(8,12,24,.45); border:1px solid rgba(255,255,255,.08); display:flex; flex-direction:column; gap:18px; }
  .wow-cookie-banner__head{ display:flex; gap:12px; align-items:flex-start; }
  .wow-cookie-banner__eyebrow{ text-transform:uppercase; letter-spacing:.24em; font-size:12px; color:rgba(255,255,255,.72); margin:0 0 6px; }
  .wow-cookie-banner__head h2{ margin:0 0 8px; font-size:1.4rem; }
  .wow-cookie-banner__head p{ margin:0; color:rgba(255,255,255,.8); }
  .wow-cookie-banner__close{ border:none; background:rgba(255,255,255,.1); color:#fff; width:32px; height:32px; border-radius:999px; font-size:20px; line-height:1; cursor:pointer; }
  .wow-cookie-banner__close:hover{ background:rgba(255,255,255,.2); }
  .wow-cookie-banner__toggles{ display:flex; flex-direction:column; gap:12px; }
  .wow-cookie-toggle{ display:flex; justify-content:space-between; gap:16px; background:rgba(255,255,255,.05); border-radius:16px; padding:16px; border:1px solid rgba(255,255,255,.08); }
  .wow-cookie-toggle__title{ margin:0 0 4px; font-weight:600; }
  .wow-cookie-toggle__copy{ margin:0; color:rgba(255,255,255,.75); font-size:.9rem; }
  .wow-cookie-toggle__badge{ align-self:center; font-size:.75rem; letter-spacing:.2em; text-transform:uppercase; color:#4ade80; border:1px solid rgba(74,222,128,.4); border-radius:999px; padding:4px 10px; }
  .wow-cookie-switch{ width:50px; height:28px; border-radius:999px; border:1px solid rgba(255,255,255,.3); background:rgba(255,255,255,.15); position:relative; cursor:pointer; transition:background .2s ease,border-color .2s ease; }
  .wow-cookie-switch__handle{ position:absolute; inset:3px auto 3px 3px; width:22px; height:22px; border-radius:50%; background:#fff; transition:transform .2s ease, background .2s ease; display:block; }
  .wow-cookie-switch[aria-pressed="true"]{ background:#34d399; border-color:#10b981; }
  .wow-cookie-switch[aria-pressed="true"] .wow-cookie-switch__handle{ transform:translateX(20px); background:#0b1220; }
  .wow-cookie-banner__note{ margin:0; font-size:.85rem; color:rgba(255,255,255,.7); }
  .wow-cookie-banner__actions{ display:flex; flex-wrap:wrap; gap:10px; }
  .wow-cookie-banner__actions .btn-wow{ flex:1 1 auto; justify-content:center; min-width:120px; }
  @media (max-width:640px){
    .wow-cookie-banner{ inset:auto 16px 16px 16px; max-width:none; }
    .wow-cookie-banner__panel{ border-radius:14px; }
  }
</style>

<script>
(function(){
  const STORAGE_KEY = 'wow_cookie_preferences';
  const COOKIE_KEY = 'wow_cookie_preferences';
  const VERSION = 1;
  const banner = document.getElementById('wowCookieBanner');
  if (!banner) return;

  const toggles = banner.querySelectorAll('[data-cookie-toggle]');
  const acceptAllBtn = banner.querySelector('[data-cookie-accept]');
  const rejectBtn = banner.querySelector('[data-cookie-reject]');
  const saveBtn = banner.querySelector('[data-cookie-save]');
  const closeBtn = banner.querySelector('[data-cookie-dismiss]');

  function defaults(){
    return {
      necessary: true,
      analytics: false,
      personalization: false,
      marketing: false,
      version: VERSION,
      updated_at: null,
    };
  }

  function readPrefs(){
    try {
      const raw = window.localStorage.getItem(STORAGE_KEY);
      if (!raw) return defaults();
      const data = JSON.parse(raw);
      if (!data || data.version !== VERSION) return defaults();
      return Object.assign(defaults(), data);
    } catch (_err) {
      return defaults();
    }
  }

  function persist(prefs){
    const payload = Object.assign({}, prefs, { version: VERSION, updated_at: new Date().toISOString() });
    try { window.localStorage.setItem(STORAGE_KEY, JSON.stringify(payload)); } catch (_err) {}
    try {
      document.cookie = COOKIE_KEY + '=' + encodeURIComponent(JSON.stringify({
        version: VERSION,
        analytics: !!payload.analytics,
        personalization: !!payload.personalization,
        marketing: !!payload.marketing,
      })) + '; Path=/; Max-Age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';
    } catch (_err) {}
    return payload;
  }

  function applyToUI(prefs){
    toggles.forEach((btn) => {
      const key = btn.getAttribute('data-cookie-toggle');
      if (!key) return;
      const value = !!prefs[key];
      btn.setAttribute('aria-pressed', value ? 'true' : 'false');
    });
  }

  function showBanner(){
    banner.hidden = false;
    banner.setAttribute('aria-hidden', 'false');
  }

  function hideBanner(){
    banner.hidden = true;
    banner.setAttribute('aria-hidden', 'true');
  }

  function currentPrefsFromUI(){
    const prefs = defaults();
    toggles.forEach((btn) => {
      const key = btn.getAttribute('data-cookie-toggle');
      if (!key) return;
      prefs[key] = btn.getAttribute('aria-pressed') === 'true';
    });
    return prefs;
  }

  function setAllOptional(value){
    toggles.forEach((btn) => {
      const key = btn.getAttribute('data-cookie-toggle');
      if (!key) return;
      btn.setAttribute('aria-pressed', value ? 'true' : 'false');
    });
  }

  toggles.forEach((btn) => {
    btn.addEventListener('click', () => {
      const current = btn.getAttribute('aria-pressed') === 'true';
      btn.setAttribute('aria-pressed', current ? 'false' : 'true');
    });
  });

  acceptAllBtn?.addEventListener('click', () => {
    setAllOptional(true);
    persist(currentPrefsFromUI());
    hideBanner();
  });

  rejectBtn?.addEventListener('click', () => {
    setAllOptional(false);
    persist(currentPrefsFromUI());
    hideBanner();
  });

  saveBtn?.addEventListener('click', () => {
    persist(currentPrefsFromUI());
    hideBanner();
  });

  closeBtn?.addEventListener('click', () => {
    hideBanner();
  });

  function shouldShow(){
    const prefs = readPrefs();
    applyToUI(prefs);
    return !prefs.updated_at;
  }

  function openPreferences(){
    applyToUI(readPrefs());
    showBanner();
  }

  window.WOWCookiePreferences = {
    open: openPreferences,
  };

  document.querySelectorAll('[data-cookie-preferences-trigger]').forEach((btn) => {
    btn.addEventListener('click', (event) => {
      try { event.preventDefault(); } catch (_err) {}
      openPreferences();
    });
  });

  if (shouldShow()) {
    showBanner();
  }
})();
</script>
