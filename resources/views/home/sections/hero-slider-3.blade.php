{{-- resources/views/home/sections/hero-slider-3.blade.php --}}

<style>
  .whero.whero--s3 {
    --s3-grid-size: 45px;
    --s3-grid-line: rgba(255, 255, 255, 0.15);
    --s3-height: clamp(480px, 58vh, 700px);

    position: relative;
    overflow: hidden;
    height: 100%;
    color: #ffffff;
    background:
      linear-gradient(90deg, var(--s3-grid-line) 1px, transparent 1px var(--s3-grid-size)) 50% 50% / var(--s3-grid-size) var(--s3-grid-size),
      linear-gradient(var(--s3-grid-line) 1px, transparent 1px var(--s3-grid-size)) 50% 50% / var(--s3-grid-size) var(--s3-grid-size),
      linear-gradient(180deg, #0f1115 0%, #000000 100%);
  }

  .whero.whero--s3::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background: linear-gradient(
      to top left,
      #0b0c10 0%,
      #0b0c10 34%,
      rgba(11, 12, 16, 0.82) 46%,
      rgba(11, 12, 16, 0.34) 58%,
      rgba(11, 12, 16, 0) 72%
    );
  }

  .whero.whero--s3 .whero-pad {
    min-height: var(--s3-height);
    height: 100%;
    padding-top: clamp(40px, 5vw, 72px);
    padding-bottom: clamp(36px, 4vw, 60px);
  }

  .whero.whero--s3 .row,
  .whero.whero--s3 .s3-copy,
  .whero.whero--s3 .s3-media-col {
    position: relative;
    z-index: 1;
  }

  .whero.whero--s3 .s3-copy {
    max-width: 720px;
  }

  .whero.whero--s3 .s3-eyebrow {
    display: inline-flex;
    align-items: center;
    margin-bottom: 1rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.22);
    background: rgba(255, 255, 255, 0.08);
    padding: 0.4rem 0.85rem;
    font-size: 0.78rem;
    letter-spacing: 0.12em;
    line-height: 1;
    font-weight: 600;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.96);
  }

  .whero.whero--s3 .s3-title {
    margin: 0;
    color: #ffffff;
    font-size: clamp(1.9rem, 2.6vw + 0.75rem, 3.15rem);
    line-height: 0.98;
    font-weight: 500;
    letter-spacing: -0.03em;
  }

  .whero.whero--s3 .s3-description {
    margin: 1rem 0 0;
    max-width: 680px;
    color: rgba(255, 255, 255, 0.88);
    font-size: clamp(.98rem, 0.75rem + 0.45vw, 1.08rem);
    line-height: 1.6;
  }

  .whero.whero--s3 .s3-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.9rem;
    margin-top: 1.5rem;
  }

  .whero.whero--s3 .s3-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    border: 1px solid transparent;
    border-radius: 0;
    padding: 0.8rem 1.15rem;
    font-size: 0.98rem;
    font-weight: 500;
    line-height: 1;
    text-decoration: none;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
  }

  .whero.whero--s3 .s3-btn-primary {
    background: #0f62ff;
    border-color: #0f62ff;
    color: #ffffff;
  }

  .whero.whero--s3 .s3-btn-primary:hover {
    background: #0f62ff;
    border-color: #0f62ff;
    color: #ffffff;
  }

  .whero.whero--s3 .s3-btn-secondary {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
  }

  .whero.whero--s3 .s3-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.26);
    color: #ffffff;
  }

  .whero.whero--s3 .s3-tour-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
    color: #ffffff;
    text-decoration: none;
    font-size: 0.92rem;
    font-weight: 500;
    letter-spacing: 0.01em;
  }

  .whero.whero--s3 .s3-tour-link:hover {
    color: #4589ff;
  }

  .whero.whero--s3 .s3-media-col {
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }

  .whero.whero--s3 .s3-media-holder {
    position: relative;
    width: 100%;
    max-width: 640px;
    aspect-ratio: 16 / 9;
    margin-left: auto;
  }

  .whero.whero--s3 .s3-video-shell {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #000000;
    border: 1px solid #111111;
  }

  .whero.whero--s3 .s3-video-shell video {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .whero.whero--s3 .s3-video-controls {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 1rem;
    pointer-events: none;
  }

  .whero.whero--s3 .s3-video-control-row {
    display: inline-flex;
    align-items: center;
    gap: 0.7rem;
    opacity: 1;
    transform: translateY(0);
    transition: opacity 0.2s ease, transform 0.2s ease;
    pointer-events: auto;
  }

  .whero.whero--s3 .s3-video-shell.is-playing:not(.is-interacting) .s3-video-control-row {
    opacity: 0;
    transform: translateY(8px);
    pointer-events: none;
  }

  .whero.whero--s3 .s3-video-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.24);
    background: rgba(15, 17, 21, 0.72);
    color: #ffffff;
    cursor: pointer;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    transition: background-color 0.2s ease, border-color 0.2s ease;
  }

  .whero.whero--s3 .s3-video-btn:hover {
    background: rgba(15, 17, 21, 0.8);
    border-color: rgba(255, 255, 255, 0.3);
  }

  .whero.whero--s3 .s3-video-btn:focus-visible {
    outline: 2px solid #0f62ff;
    outline-offset: 3px;
  }

  .whero.whero--s3 .s3-video-btn svg {
    display: block;
    width: 18px;
    height: 18px;
    fill: currentColor;
  }

  .whero.whero--s3 .s3-video-btn--main {
    width: 56px;
    height: 56px;
    border-radius: 999px;
  }

  .whero.whero--s3 .s3-video-btn--main svg {
    width: 20px;
    height: 20px;
  }

  .whero.whero--s3 .s3-video-btn--aux {
    width: 40px;
    height: 40px;
    border-radius: 999px;
  }

  .whero.whero--s3 .s3-video-btn [data-icon='pause'],
  .whero.whero--s3 .s3-video-btn [data-icon='volume'],
  .whero.whero--s3 .s3-video-shell.is-playing .s3-video-btn [data-icon='play'],
  .whero.whero--s3 .s3-video-shell.is-muted .s3-video-btn [data-icon='volume-on'] {
    display: none;
  }

  .whero.whero--s3 .s3-video-shell.is-playing .s3-video-btn [data-icon='pause'],
  .whero.whero--s3 .s3-video-shell.is-muted .s3-video-btn [data-icon='volume'],
  .whero.whero--s3 .s3-video-shell:not(.is-playing) .s3-video-btn [data-icon='play'],
  .whero.whero--s3 .s3-video-shell:not(.is-muted) .s3-video-btn [data-icon='volume-on'] {
    display: block;
  }

  @media (max-width: 991.98px) {
    .whero.whero--s3 .whero-pad {
      min-height: auto;
      padding-top: 3.5rem;
      padding-bottom: 2.5rem;
    }

    .whero.whero--s3 .s3-copy {
      max-width: 100%;
    }

    .whero.whero--s3 .s3-media-col {
      justify-content: flex-start;
    }

    .whero.whero--s3 .s3-media-holder {
      max-width: 100%;
    }
  }

  @media (max-width: 767.98px) {
    .whero.whero--s3 {
      --s3-grid-size: 34px;
    }

    .whero.whero--s3 .s3-copy {
      text-align: center;
      margin-left: auto;
      margin-right: auto;
    }

    .whero.whero--s3 .s3-actions {
      flex-direction: column;
      align-items: stretch;
    }

    .whero.whero--s3 .s3-btn {
      width: 100%;
    }

    .whero.whero--s3 .s3-tour-link {
      justify-content: center;
    }

    .whero.whero--s3 .s3-video-controls {
      padding: 1rem;
    }

    .whero.whero--s3 .s3-video-control-row {
      gap: 0.55rem;
    }

    .whero.whero--s3 .s3-video-btn--main {
      width: 56px;
      height: 56px;
    }

    .whero.whero--s3 .s3-video-btn--aux {
      width: 40px;
      height: 40px;
    }
  }

  @media (max-width: 575.98px) {
    .whero.whero--s3 .whero-pad {
      padding-top: 72px;
      padding-bottom: 32px;
    }
  }
</style>

<section data-v-f43bb09d="" class="whero whero--s3">
  <div data-v-f43bb09d="" class="container whero-pad">
    <div data-v-f43bb09d="" class="row align-items-center g-5">
      <div data-v-f43bb09d="" class="col-12 col-lg-6">
        <div class="s3-copy">
          <span class="s3-eyebrow">WHY BECOME A WOW PRACTITIONER </span>

          <h2 class="s3-title">WOW Studio is the all-in-one platform for managing and growing your wellness practice</h2>

          <p class="s3-description">Built for wellness practitioners by wellness practitioners who actually understand the work.
<br><br>
WOW Studio brings client management, forms, bookings, video sessions, payments, reminders, reviews, QR codes, and short links into one connected platform — built by practitioners who understand the work.
</p>

          <div class="s3-actions">
            <a href="https://studio.weofferwellness.co.uk/" class="s3-btn s3-btn-primary">Become a WOW Practitioner</a>
          </div>
        </div>
      </div>

      <div data-v-f43bb09d="" class="col-12 col-lg-6 s3-media-col">
        <div class="s3-media-holder">
          <div class="s3-video-shell is-muted" data-wow-s3-video-shell>
            <video muted loop preload="metadata" playsinline title="WOW Studio product walkthrough" data-wow-s3-video>
              <source src="https://atease.weofferwellness.co.uk/storage/uploads/videos/6c66fc27-fc42-4a9a-9b25-6f3dbc7745e9.mp4" type="video/mp4">
              Your browser does not support the video tag.
            </video>

            <div class="s3-video-controls">
              <div class="s3-video-control-row">
                <button type="button" class="s3-video-btn s3-video-btn--aux" data-wow-s3-video-restart aria-label="Restart video">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 5a7 7 0 1 1-6.33 4H3l3.5-3.5L10 9H7.78A5.5 5.5 0 1 0 12 6.5V5Z"/>
                  </svg>
                </button>

                <button type="button" class="s3-video-btn s3-video-btn--main" data-wow-s3-video-toggle aria-label="Play video">
                  <svg viewBox="0 0 24 24" aria-hidden="true" data-icon="play">
                    <path d="M8 5.14v13.72c0 .68.73 1.11 1.33.78l10.1-5.86a.9.9 0 0 0 0-1.56L9.33 4.36A.9.9 0 0 0 8 5.14Z"/>
                  </svg>
                  <svg viewBox="0 0 24 24" aria-hidden="true" data-icon="pause">
                    <path d="M8 5h3v14H8zm5 0h3v14h-3z"/>
                  </svg>
                </button>

                <button type="button" class="s3-video-btn s3-video-btn--aux" data-wow-s3-video-mute aria-label="Unmute video">
                  <svg viewBox="0 0 24 24" aria-hidden="true" data-icon="volume">
                    <path d="M14 4.46v15.08a1 1 0 0 1-1.64.77L7.59 16H4a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h3.59l4.77-4.31A1 1 0 0 1 14 4.46Zm4.94 3.65 1.41 1.41L17.88 12l2.47 2.47-1.41 1.41L16.47 13.4 14 15.88l-1.41-1.41L15.06 12l-2.47-2.47L14 8.11l2.47 2.48 2.47-2.48Z"/>
                  </svg>
                  <svg viewBox="0 0 24 24" aria-hidden="true" data-icon="volume-on">
                    <path d="M14 4.46v15.08a1 1 0 0 1-1.64.77L7.59 16H4a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h3.59l4.77-4.31A1 1 0 0 1 14 4.46Zm3.5 2.54a1 1 0 0 1 1.41 0 7 7 0 0 1 0 9.9 1 1 0 1 1-1.41-1.41 5 5 0 0 0 0-7.08 1 1 0 0 1 0-1.41Zm-2.83 2.83a1 1 0 0 1 1.41 0 3 3 0 0 1 0 4.24 1 1 0 0 1-1.41-1.41 1 1 0 0 0 0-1.42 1 1 0 0 1 0-1.41Z"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  (function () {
    const initializedShells = new WeakSet();
    const hideDelay = 2800;

    function initVideoShell(shell) {
      if (!shell || initializedShells.has(shell)) return;
      initializedShells.add(shell);

      const video = shell.querySelector('[data-wow-s3-video]');
      const toggleButton = shell.querySelector('[data-wow-s3-video-toggle]');
      const restartButton = shell.querySelector('[data-wow-s3-video-restart]');
      const muteButton = shell.querySelector('[data-wow-s3-video-mute]');

      if (!video || !toggleButton || !restartButton || !muteButton) return;

      let interactionTimeout = null;

      const clearInteractionTimeout = () => {
        if (interactionTimeout) {
          window.clearTimeout(interactionTimeout);
          interactionTimeout = null;
        }
      };

      const showControlsTemporarily = () => {
        clearInteractionTimeout();
        shell.classList.add('is-interacting');

        if (!video.paused) {
          interactionTimeout = window.setTimeout(() => {
            shell.classList.remove('is-interacting');
          }, hideDelay);
        }
      };

      const syncState = () => {
        shell.classList.toggle('is-playing', !video.paused && !video.ended);
        shell.classList.toggle('is-muted', !!video.muted);
        toggleButton.setAttribute('aria-label', video.paused ? 'Play video' : 'Pause video');
        muteButton.setAttribute('aria-label', video.muted ? 'Unmute video' : 'Mute video');

        if (video.paused || video.ended) {
          clearInteractionTimeout();
          shell.classList.add('is-interacting');
        }
      };

      const togglePlayback = async () => {
        try {
          if (video.paused || video.ended) {
            await video.play();
            showControlsTemporarily();
          } else {
            video.pause();
          }
        } catch (_) {
          shell.classList.add('is-interacting');
        }

        syncState();
      };

      const startAutoplay = async () => {
        try {
          video.muted = true;
          await video.play();
          showControlsTemporarily();
        } catch (_) {
          shell.classList.add('is-interacting');
        }

        syncState();
      };

      toggleButton.addEventListener('click', (event) => {
        event.preventDefault();
        togglePlayback();
      });

      restartButton.addEventListener('click', async (event) => {
        event.preventDefault();
        video.currentTime = 0;

        if (video.paused || video.ended) {
          try {
            await video.play();
          } catch (_) {
            // no-op
          }
        }

        showControlsTemporarily();
        syncState();
      });

      muteButton.addEventListener('click', (event) => {
        event.preventDefault();
        video.muted = !video.muted;
        showControlsTemporarily();
        syncState();
      });

      shell.addEventListener('mouseenter', () => {
        clearInteractionTimeout();
        shell.classList.add('is-interacting');
      });

      shell.addEventListener('mouseleave', () => {
        if (!video.paused) {
          clearInteractionTimeout();
          shell.classList.remove('is-interacting');
        }
      });

      shell.addEventListener('focusin', () => {
        clearInteractionTimeout();
        shell.classList.add('is-interacting');
      });

      shell.addEventListener('focusout', () => {
        if (!video.paused) {
          clearInteractionTimeout();
          shell.classList.remove('is-interacting');
        }
      });

      shell.addEventListener('click', (event) => {
        if (event.target.closest('button')) return;
        if (!video.paused) showControlsTemporarily();
      });

      video.addEventListener('play', syncState);
      video.addEventListener('pause', syncState);
      video.addEventListener('ended', syncState);
      video.addEventListener('volumechange', syncState);

      syncState();
      startAutoplay();
    }

    function initAllHeroVideos() {
      document.querySelectorAll('.whero--s3 [data-wow-s3-video-shell]').forEach(initVideoShell);
    }

    function boot() {
      initAllHeroVideos();
      window.setTimeout(initAllHeroVideos, 160);
      window.setTimeout(initAllHeroVideos, 650);
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', boot);
    } else {
      boot();
    }
  })();
</script>
