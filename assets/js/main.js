/**
 * MiDoc — landing interactivity
 */
(function () {
  'use strict';

  function showReveal(el) {
    el.classList.add('is-visible');
  }

  function isInViewport(el) {
    const rect = el.getBoundingClientRect();
    const vh = window.innerHeight || document.documentElement.clientHeight;
    return rect.top < vh * 0.95 && rect.bottom > 40;
  }

  // Activa reveals y muestra de inmediato el hero (evita pantalla vacía)
  document.documentElement.classList.add('js');
  const revealEls = document.querySelectorAll('.reveal');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (revealEls.length) {
    if (reduceMotion || !('IntersectionObserver' in window)) {
      revealEls.forEach(showReveal);
    } else {
      const pending = [];
      revealEls.forEach((el) => {
        if (isInViewport(el)) {
          showReveal(el);
        } else {
          pending.push(el);
        }
      });

      if (pending.length) {
        const revealIo = new IntersectionObserver(
          (entries, observer) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                showReveal(entry.target);
                observer.unobserve(entry.target);
              }
            });
          },
          { threshold: 0.08, rootMargin: '0px 0px -5% 0px' }
        );
        pending.forEach((el) => revealIo.observe(el));
      }
    }
  }

  function apiBase() {
    const path = window.location.pathname;
    const base = path.endsWith('/')
      ? path
      : path.substring(0, path.lastIndexOf('/') + 1);
    return base;
  }

  // ----- Bottom nav active section -----
  const landNavLinks = document.querySelectorAll('.js-land-nav[data-nav]');
  const sectionMap = {
    inicio: document.getElementById('especialistas'),
    servicios: document.getElementById('servicios'),
    contacto: document.getElementById('contacto'),
    ubicacion: document.getElementById('ubicacion'),
  };

  function setLandNav(key) {
    landNavLinks.forEach((link) => {
      link.classList.toggle('is-active', link.getAttribute('data-nav') === key);
    });
  }

  if (landNavLinks.length && 'IntersectionObserver' in window) {
    const navIo = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const id = entry.target.id;
          if (id === 'especialistas') setLandNav('inicio');
          else if (id === 'servicios') setLandNav('servicios');
          else if (id === 'contacto') setLandNav('contacto');
          else if (id === 'ubicacion') setLandNav('ubicacion');
        });
      },
      { threshold: 0.28, rootMargin: '-20% 0px -45% 0px' }
    );
    Object.values(sectionMap).forEach((el) => {
      if (el) navIo.observe(el);
    });
  }

  // ----- Stats counters -----
  function animateCounter(el) {
    const target = parseInt(el.getAttribute('data-counter') || '0', 10);
    const suffix = el.getAttribute('data-suffix') || '';
    const duration = 1200;
    const start = performance.now();

    function tick(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(target * eased).toLocaleString('es') + suffix;
      if (progress < 1) requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
  }

  const counters = document.querySelectorAll('[data-counter]');
  if (counters.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animateCounter(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.4 }
    );
    counters.forEach((el) => io.observe(el));
  } else {
    counters.forEach(animateCounter);
  }

  // ----- Appointment form -----
  const form = document.getElementById('cita-form');
  const submitBtn = document.getElementById('cita-submit');
  const submitLabel = document.getElementById('cita-submit-label');
  const formMsg = document.getElementById('cita-form-msg');

  function showFormMessage(text, ok) {
    if (!formMsg) return;
    formMsg.textContent = text;
    formMsg.classList.remove('hidden', 'text-primary', 'text-error');
    formMsg.classList.add(ok ? 'text-primary' : 'text-error');
  }

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const payload = {
        nombre: form.nombre.value.trim(),
        telefono: form.telefono.value.trim(),
        servicio: form.servicio.value,
        mensaje: form.mensaje.value.trim(),
      };

      if (submitBtn) submitBtn.disabled = true;
      if (submitLabel) submitLabel.textContent = 'Enviando…';

      try {
        const res = await fetch(apiBase() + 'api/cita.php', {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data.ok) {
          const firstError =
            data.errors && typeof data.errors === 'object'
              ? Object.values(data.errors)[0]
              : null;
          throw new Error(firstError || data.message || 'No se pudo enviar la solicitud.');
        }

        showFormMessage(data.message || 'Solicitud enviada correctamente.', true);
        form.reset();
      } catch (err) {
        showFormMessage(err.message || 'Error de conexión. Intenta de nuevo.', false);
      } finally {
        if (submitBtn) submitBtn.disabled = false;
        if (submitLabel) submitLabel.textContent = 'Solicitar Cita Ahora';
      }
    });
  }

  // ----- API health -----
  const statusLabel = document.getElementById('api-status-label');
  if (statusLabel) {
    fetch(apiBase() + 'api/status.php', { headers: { Accept: 'application/json' } })
      .then((res) => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then((data) => {
        const dbOk = data.database && data.database.ok;
        statusLabel.textContent = dbOk ? 'Sistema: OK' : 'Sistema: OK (BD pendiente)';
      })
      .catch(() => {
        statusLabel.textContent = 'Sistema: sin respuesta API';
      });
  }

  // ----- Ubicación modal + Google Maps -----
  const ubicacionModal = document.getElementById('ubicacion-modal');
  const ubicacionIframe = document.getElementById('ubicacion-map-iframe');

  function openUbicacionModal() {
    if (!ubicacionModal) return;
    if (ubicacionIframe && !ubicacionIframe.getAttribute('src')) {
      const src = ubicacionIframe.getAttribute('data-src');
      if (src) ubicacionIframe.setAttribute('src', src);
    }
    ubicacionModal.hidden = false;
    ubicacionModal.classList.add('is-open');
    ubicacionModal.classList.remove('hidden');
    document.body.classList.add('ubicacion-modal-open');
    setLandNav('ubicacion');
  }

  function closeUbicacionModal() {
    if (!ubicacionModal) return;
    ubicacionModal.classList.remove('is-open');
    ubicacionModal.classList.add('hidden');
    ubicacionModal.hidden = true;
    document.body.classList.remove('ubicacion-modal-open');
  }

  document.querySelectorAll('[data-ubicacion-modal]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      openUbicacionModal();
    });
  });

  document.querySelectorAll('[data-ubicacion-close]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      closeUbicacionModal();
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && ubicacionModal && !ubicacionModal.hidden) {
      closeUbicacionModal();
    }
  });

  if (window.location.hash === '#ubicacion') {
    openUbicacionModal();
  }
})();
