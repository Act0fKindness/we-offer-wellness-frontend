// Auto spinner + temporary loading state on click for .btn-wow / .cta-btn

export function initClickLoaders() {
  const buttons = document.querySelectorAll('.btn-wow, .cta-btn')
  buttons.forEach((btn) => {
    if (btn.dataset.loaderInit === '1') return

    // Inject spinner if missing
    if (!btn.querySelector('.btn-spinner')) {
      const sp = document.createElement('span')
      sp.className = 'btn-spinner'
      sp.setAttribute('aria-hidden', 'true')
      sp.innerHTML = '<span class="spin"></span>'
      btn.appendChild(sp)
    }

    btn.addEventListener(
      'click',
      (e) => {
        // Allow navigation for real links; guard only for href="#" or missing
        if (
          btn.tagName === 'A' &&
          ((btn.getAttribute('href') || '').trim() === '#' || !btn.getAttribute('href'))
        ) {
          e.preventDefault()
        }
        if (btn.classList.contains('is-loading')) return
        const ms = parseInt(btn.getAttribute('data-load-ms') || '1200', 10)
        btn.classList.add('is-loading')
        btn.setAttribute('aria-busy', 'true')
        if (btn.tagName === 'BUTTON') btn.disabled = true
        setTimeout(() => {
          btn.classList.remove('is-loading')
          btn.removeAttribute('aria-busy')
          if (btn.tagName === 'BUTTON') btn.disabled = false
        }, ms)
      },
      { passive: false },
    )

    btn.dataset.loaderInit = '1'
  })
}

