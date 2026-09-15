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

  // ----- Appointment form → WhatsApp -----
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

  function buildWhatsAppCitaMessage(payload) {
    const lines = [
      '*Solicitud de cita médica — MiDoc*',
      '',
      'Hola, deseo reservar una cita. Estos son mis datos:',
      '',
      '• *Nombre:* ' + (payload.nombre || '—'),
      '• *Teléfono de contacto:* ' + (payload.telefono || '—'),
      '• *Motivo / dolencia:* ' + (payload.servicioLabel || payload.servicio || '—'),
    ];
    if (payload.mensaje) {
      lines.push('• *Mensaje adicional:* ' + payload.mensaje);
    }
    lines.push('', 'Quedo atento(a) a su confirmación. Gracias.');
    return lines.join('\n');
  }

  function openWhatsAppCita(number, message) {
    const digits = String(number || '').replace(/\D+/g, '') || '59170688374';
    // Misma pestaña: evita bloqueo de ventanas emergentes y abre WhatsApp de forma fiable
    const url = 'https://wa.me/' + digits + '?text=' + encodeURIComponent(message);
    window.location.href = url;
  }

  function handleCitaRequest() {
    if (!form) return;

    const servicioSelect = form.querySelector('[name="servicio"]');
    const selectedOpt =
      servicioSelect instanceof HTMLSelectElement && servicioSelect.selectedIndex >= 0
        ? servicioSelect.options[servicioSelect.selectedIndex]
        : null;
    const servicioValue =
      servicioSelect instanceof HTMLSelectElement ? String(servicioSelect.value || '').trim() : '';
    const servicioLabel = selectedOpt ? String(selectedOpt.text || '').trim() : '';

    const payload = {
      nombre: (form.nombre && form.nombre.value ? form.nombre.value : '').trim(),
      telefono: (form.telefono && form.telefono.value ? form.telefono.value : '').trim(),
      servicio: servicioValue,
      servicioLabel: servicioLabel,
      mensaje: (form.mensaje && form.mensaje.value ? form.mensaje.value : '').trim(),
    };

    if (payload.nombre.length < 3) {
      showFormMessage('Ingresa tu nombre completo.', false);
      if (form.nombre) form.nombre.focus();
      return;
    }
    if (payload.telefono.replace(/\D+/g, '').length < 7) {
      showFormMessage('Ingresa un teléfono válido.', false);
      if (form.telefono) form.telefono.focus();
      return;
    }
    if (!payload.servicio) {
      showFormMessage('Selecciona un motivo de consulta.', false);
      if (servicioSelect) servicioSelect.focus();
      return;
    }

    const waNumber = form.getAttribute('data-whatsapp-number') || '59170688374';
    const waMessage = buildWhatsAppCitaMessage(payload);

    // Registro interno opcional (no muestra mensaje de “cita recibida”)
    try {
      fetch(apiBase() + 'api/cita.php', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          nombre: payload.nombre,
          telefono: payload.telefono,
          servicio: payload.servicio,
          mensaje: payload.mensaje,
        }),
        keepalive: true,
      }).catch(function () {});
    } catch (_err) {}

    // Redirige a WhatsApp con el borrador; el usuario debe pulsar Enviar
    openWhatsAppCita(waNumber, waMessage);
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleCitaRequest();
    });
  }
  if (submitBtn) {
    submitBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      handleCitaRequest();
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

  // ----- Video YouTube modal -----
  const videoModal = document.getElementById('video-modal');
  const videoIframe = document.getElementById('video-modal-iframe');
  const videoTitle = document.getElementById('video-modal-title');

  function openVideoModal(src, title) {
    if (!videoModal || !videoIframe) return;
    videoIframe.setAttribute('src', src);
    if (videoTitle) videoTitle.textContent = title || 'Video';
    videoModal.hidden = false;
    videoModal.classList.add('is-open');
    videoModal.classList.remove('hidden');
    document.body.classList.add('video-modal-open');
  }

  function closeVideoModal() {
    if (!videoModal) return;
    videoModal.classList.remove('is-open');
    videoModal.classList.add('hidden');
    videoModal.hidden = true;
    document.body.classList.remove('video-modal-open');
    if (videoIframe) videoIframe.setAttribute('src', '');
  }

  document.querySelectorAll('[data-video-modal]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      openVideoModal(el.getAttribute('data-video-src') || '', el.getAttribute('data-video-title') || 'Video');
    });
  });

  document.querySelectorAll('[data-video-close]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      closeVideoModal();
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (videoModal && !videoModal.hidden) closeVideoModal();
  });

  // ----- Modal detalle de servicio -----
  const servicioModal = document.getElementById('servicio-modal');
  const servicioTitle = document.getElementById('servicio-modal-title');
  const servicioDesc = document.getElementById('servicio-modal-desc');
  const servicioDetalle = document.getElementById('servicio-modal-detalle');
  const servicioImg = document.getElementById('servicio-modal-img');
  const servicioMedia = document.getElementById('servicio-modal-media');
  const servicioMediaFallback = document.getElementById('servicio-modal-media-fallback');
  const servicioIcon = document.getElementById('servicio-modal-icon');
  const servicioFallbackIcon = document.getElementById('servicio-modal-fallback-icon');

  function openServicioModal(data) {
    if (!servicioModal) return;
    const titulo = data.titulo || 'Servicio';
    const icono = data.icono || 'medical_services';
    const imagen = data.imagen || '';
    const desc = data.desc || '';
    const detalle = data.detalle || desc;

    if (servicioTitle) servicioTitle.textContent = titulo;
    if (servicioIcon) servicioIcon.textContent = icono;
    if (servicioFallbackIcon) servicioFallbackIcon.textContent = icono;
    if (servicioDesc) {
      servicioDesc.textContent = desc;
      servicioDesc.classList.toggle('hidden', !desc || desc === detalle);
    }
    if (servicioDetalle) servicioDetalle.textContent = detalle;

    if (imagen && servicioImg && servicioMedia) {
      servicioImg.setAttribute('src', imagen);
      servicioImg.setAttribute('alt', titulo);
      servicioMedia.classList.remove('hidden');
      if (servicioMediaFallback) servicioMediaFallback.classList.add('hidden');
    } else {
      if (servicioImg) servicioImg.removeAttribute('src');
      if (servicioMedia) servicioMedia.classList.add('hidden');
      if (servicioMediaFallback) servicioMediaFallback.classList.remove('hidden');
    }

    servicioModal.hidden = false;
    servicioModal.classList.add('is-open');
    servicioModal.classList.remove('hidden');
    document.body.classList.add('servicio-modal-open');
    setLandNav('servicios');
  }

  function closeServicioModal() {
    if (!servicioModal) return;
    servicioModal.classList.remove('is-open');
    servicioModal.classList.add('hidden');
    servicioModal.hidden = true;
    document.body.classList.remove('servicio-modal-open');
  }

  document.querySelectorAll('[data-servicio-modal]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      openServicioModal({
        titulo: el.getAttribute('data-servicio-titulo') || 'Servicio',
        desc: el.getAttribute('data-servicio-desc') || '',
        detalle: el.getAttribute('data-servicio-detalle') || '',
        icono: el.getAttribute('data-servicio-icono') || 'medical_services',
        imagen: el.getAttribute('data-servicio-imagen') || '',
      });
    });
  });

  document.querySelectorAll('[data-servicio-close]').forEach((el) => {
    el.addEventListener('click', () => {
      closeServicioModal();
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && servicioModal && !servicioModal.hidden) {
      closeServicioModal();
    }
  });
})();
