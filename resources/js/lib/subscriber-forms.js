const SESSION_TOKEN_KEY = 'wow_subscriber_session_token';
const SESSION_START_KEY = 'wow_subscriber_session_started_at';
const DEFAULT_SUCCESS_MESSAGE = 'Check your email to confirm your subscription.';

let sessionStart = loadNumber(SESSION_START_KEY);
if (!sessionStart) {
  sessionStart = Date.now();
  storeNumber(SESSION_START_KEY, sessionStart);
}

let toastEl = null;
let toastTimer = null;

function loadNumber(key) {
  try {
    const raw = sessionStorage.getItem(key);
    return raw ? Number(raw) : null;
  } catch (_err) {
    return null;
  }
}

function storeNumber(key, value) {
  try {
    sessionStorage.setItem(key, String(value));
  } catch (_err) {}
}

function loadToken() {
  try {
    return sessionStorage.getItem(SESSION_TOKEN_KEY);
  } catch (_err) {
    return null;
  }
}

function storeToken(token) {
  if (!token) return;
  try {
    sessionStorage.setItem(SESSION_TOKEN_KEY, token);
  } catch (_err) {}
}

function sessionDurationSeconds() {
  return Math.max(0, Math.round((Date.now() - (sessionStart || Date.now())) / 1000));
}

function collectMeta() {
  const nav = typeof navigator !== 'undefined' ? navigator : {};
  const scr = typeof window !== 'undefined' ? window.screen || {} : {};
  let timezone = null;
  try {
    timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
  } catch (_err) {}
  const languages = Array.isArray(nav.languages) ? nav.languages.join(',') : (nav.language || null);
  return {
    timezone,
    locale: nav.language || null,
    languages,
    platform: nav.platform || null,
    user_agent: nav.userAgent || null,
    device_memory: typeof nav.deviceMemory === 'number' ? String(nav.deviceMemory) : null,
    hardware_concurrency: typeof nav.hardwareConcurrency === 'number' ? String(nav.hardwareConcurrency) : null,
    screen_width: scr.width || null,
    screen_height: scr.height || null,
  };
}

function basePayload(source) {
  const landing = source || 'site:subscribe-form';
  return {
    landing_path: landing,
    referrer: document.referrer ? document.referrer.substring(0, 2048) : null,
    session_token: loadToken(),
    session_started_at: new Date(sessionStart || Date.now()).toISOString(),
    session_duration_seconds: sessionDurationSeconds(),
    ...collectMeta(),
  };
}

async function submitSubscriber(additionalPayload = {}) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const payload = Object.assign({}, additionalPayload);
  const response = await fetch('/api/v3-subscribers', {
    method: 'POST',
    headers: Object.assign({
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }, csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  const bodyJson = await response.json().catch(() => ({}));
  if (!response.ok || bodyJson.error) {
    const message = extractError(bodyJson) || 'Something went wrong. Please try again in a moment.';
    const error = new Error(message);
    error.body = bodyJson;
    throw error;
  }
  if (bodyJson.session_token) {
    storeToken(bodyJson.session_token);
  }
  return bodyJson;
}

function extractError(body) {
  if (!body) return null;
  if (body.errors) {
    const first = Object.values(body.errors)[0];
    if (Array.isArray(first) && first.length) {
      return first[0];
    }
  }
  return body.error || null;
}

function ensureFeedbackEl(form) {
  let el = form.querySelector('[data-subscriber-feedback]');
  if (!el) {
    el = document.createElement('p');
    el.dataset.subscriberFeedback = '1';
    el.className = 'subscriber-feedback';
    el.setAttribute('aria-live', 'polite');
    el.hidden = true;
    form.appendChild(el);
  }
  return el;
}

function ensureToast() {
  if (toastEl && document.body.contains(toastEl)) return toastEl;
  toastEl = document.createElement('div');
  toastEl.className = 'subscriber-toast';
  toastEl.setAttribute('role', 'status');
  toastEl.setAttribute('aria-live', 'polite');
  toastEl.hidden = true;
  document.body.appendChild(toastEl);
  return toastEl;
}

function showToast(message) {
  const el = ensureToast();
  const text = message || DEFAULT_SUCCESS_MESSAGE;
  el.textContent = text;
  el.hidden = false;
  el.classList.add('is-visible');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    el.classList.remove('is-visible');
    el.hidden = true;
  }, 3200);
}

function showMessage(el, text = '', isSuccess) {
  if (!el) {
    if (isSuccess === true) showToast(text);
    return;
  }

  if (typeof isSuccess !== 'boolean') {
    el.textContent = '';
    el.hidden = true;
    el.classList.remove('is-success', 'is-error');
    return;
  }

  if (isSuccess) {
    const msg = text || DEFAULT_SUCCESS_MESSAGE;
    el.textContent = msg;
    el.hidden = true;
    el.classList.add('is-success');
    el.classList.remove('is-error');
    showToast(msg);
    return;
  }

  const message = text || '';
  el.textContent = message;
  el.hidden = !message;
  el.classList.remove('is-success');
  if (message) {
    el.classList.add('is-error');
  } else {
    el.classList.remove('is-error');
  }
}

function setLoading(form, loading) {
  const btn = form.querySelector('[type="submit"]');
  if (!btn) return;
  btn.disabled = loading;
  btn.classList.toggle('is-loading', !!loading);
  if (loading) {
    btn.setAttribute('aria-busy', 'true');
  } else {
    btn.removeAttribute('aria-busy');
  }
}

function initSubscriberForms() {
  const forms = document.querySelectorAll('[data-subscriber-form]');
  forms.forEach((form) => {
    const input = form.querySelector('input[type="email"], input[name="email"]');
    if (!input) return;
    const source = form.getAttribute('data-subscriber-source') || form.getAttribute('data-subscriber-form') || 'site:subscribe-form';
    const feedback = ensureFeedbackEl(form);
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      showMessage(feedback);
      const email = input.value.trim();
      if (!email) {
        showMessage(feedback, 'Please enter your email address.', false);
        input.focus();
        return;
      }
      if (input.checkValidity && !input.checkValidity()) {
        input.reportValidity?.();
        return;
      }
      setLoading(form, true);
      try {
        const result = await submitSubscriber(Object.assign(basePayload(source), { email }));
        form.reset();
        const successMessage = result?.message || DEFAULT_SUCCESS_MESSAGE;
        showMessage(feedback, successMessage, true);
      } catch (error) {
        showMessage(feedback, error.message || 'Something went wrong. Please try again.', false);
      } finally {
        setLoading(form, false);
      }
    });
  });
}

try {
  window.WOWSubscriber = {
    submit: submitSubscriber,
    basePayload,
    loadToken,
    storeToken,
    sessionDurationSeconds,
  };
  window.dispatchEvent(new CustomEvent('wow:subscriber-ready'));
} catch (_err) {}

export { initSubscriberForms, submitSubscriber, basePayload };
