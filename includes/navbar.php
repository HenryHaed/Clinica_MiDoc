<?php
/**
 * Navegación landing: top compacto + barra inferior tipo app (móvil).
 */
$doctorPhoto = $doctorPhoto ?? 'https://lh3.googleusercontent.com/aida-public/AB6AXuDQ00TRNKSzW1IeH4fiOrmtUbcjbLCCGlPgfqZ_8yz3xGbzOPR_3PFPiqDHUnlcw0lT1aJHtm2aOeaVqnqMcuUWt6WGqWTzHUGEwXo9ANOEvrPZgJanwn-8iM8q_Ih2OaqvFbJXuZJg5nddpYjfMt3yjTMtfW6cyXJE4JCT9doBg-hvy-7UaDLCoPM9oQ2yg-shU3Ipn7V23KOOA03yR8lzdSodgbpiOWyvvV5CC5b0p4DwZ73pzDmB';
$phone = $phone ?? '70688374';
$whatsappGeneralUrl = $whatsappGeneralUrl ?? ('https://wa.me/591' . preg_replace('/\D+/', '', $phone));
$landingNav = $landingNav ?? 'inicio';
?>
<nav class="fixed top-0 left-0 right-0 z-50 bg-mist/95 backdrop-blur-md border-b border-light-cyan/40 h-14 md:h-20 w-full max-w-full">
  <div class="h-full w-full max-w-7xl mx-auto flex items-center justify-between gap-2 px-4 sm:px-5 md:px-12 min-w-0">
    <a href="index.php" class="hover:opacity-90 transition-opacity min-h-11 flex items-center min-w-0 shrink">
      <?= brand_mark('MiDoc', 'h-8 w-8 md:h-10 md:w-10 rounded-md object-cover shrink-0', 'font-headline-lg text-lg sm:text-2xl font-black text-cyan-deep tracking-tight truncate') ?>
    </a>

    <div class="hidden md:flex items-center gap-8 shrink-0">
      <a class="text-on-surface-variant font-label-md text-sm uppercase tracking-widest hover:text-cyan-deep transition-colors" href="#servicios">Servicios</a>
      <a class="text-on-surface-variant font-label-md text-sm uppercase tracking-widest hover:text-cyan-deep transition-colors" href="#especialistas">Especialistas</a>
      <a class="text-cyan-deep font-bold border-b-2 border-light-cyan font-label-md text-sm uppercase tracking-widest pb-1" href="#contacto">Contacto</a>
    </div>

    <div class="flex items-center gap-1 md:gap-4 shrink-0">
      <a href="<?= htmlspecialchars($whatsappGeneralUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="hidden md:flex w-11 h-11 items-center justify-center rounded-full hover:bg-light-cyan/25 transition-colors text-cyan-deep" aria-label="WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-6 h-6" fill="currentColor" aria-hidden="true">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
        </svg>
      </a>
      <a href="login.php" class="w-11 h-11 flex items-center justify-center rounded-full hover:bg-light-cyan/25 transition-colors text-cyan-deep" aria-label="Iniciar sesión" title="Iniciar sesión">
        <span class="material-symbols-outlined text-[26px]">account_circle</span>
      </a>
      <a href="#contacto" class="hidden md:inline-flex bg-brand-yellow text-on-secondary-fixed px-6 py-3 rounded-lg font-label-md text-sm font-bold uppercase tracking-wide hover:bg-brand-orange hover:text-white transition-all shadow-[0_0_15px_rgba(255,145,0,0.35)]">Agenda tu Cita</a>
      <div class="hidden md:block w-10 h-10 rounded-full bg-surface-container-highest overflow-hidden border border-outline-variant">
        <img alt="Dr. Hernán Caihuara Salas" class="w-full h-full object-cover" src="<?= htmlspecialchars($doctorPhoto, ENT_QUOTES, 'UTF-8') ?>"/>
      </div>
    </div>
  </div>
</nav>

<nav class="landing-bottom-nav md:hidden fixed bottom-0 left-0 right-0 z-50 bg-mist/95 backdrop-blur-xl border-t border-sage/50 shadow-[0_-8px_24px_rgba(0,62,103,0.08)] w-full max-w-full" aria-label="Navegación principal">
  <div class="grid grid-cols-5 items-end gap-0 px-0.5 pt-1 w-full">
    <a href="index.php#especialistas" data-nav="inicio" class="js-land-nav flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 px-0.5 text-on-surface-variant <?= $landingNav === 'inicio' ? 'is-active' : '' ?>">
      <span class="material-symbols-outlined text-[22px] sm:text-[26px]">home</span>
      <span class="text-[9px] sm:text-[10px] font-bold tracking-wide leading-none">Inicio</span>
    </a>
    <a href="index.php#servicios" data-nav="servicios" class="js-land-nav flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 px-0.5 text-on-surface-variant">
      <span class="material-symbols-outlined text-[22px] sm:text-[26px]">medical_services</span>
      <span class="text-[9px] sm:text-[10px] font-bold tracking-wide leading-none">Servicios</span>
    </a>
    <a href="index.php#contacto" data-nav="contacto" class="js-land-nav flex flex-col items-center justify-center -mt-4 px-0.5">
      <span class="nav-cita-btn w-14 h-14 rounded-full bg-brand-yellow text-on-secondary-fixed shadow-[0_8px_20px_rgba(255,145,0,0.45)] flex items-center justify-center border-4 border-mist">
        <span class="material-symbols-outlined text-[28px]" style="font-variation-settings:'FILL' 1;">calendar_month</span>
      </span>
      <span class="text-[9px] sm:text-[10px] font-bold tracking-wide text-cyan-deep mt-0.5 leading-none">Cita</span>
    </a>
    <a href="index.php#ubicacion" data-nav="ubicacion" data-ubicacion-modal class="js-land-nav flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 px-0.5 text-on-surface-variant">
      <span class="material-symbols-outlined text-[22px] sm:text-[26px]">location_on</span>
      <span class="text-[9px] sm:text-[10px] font-bold tracking-wide leading-none">Mapa</span>
    </a>
    <a href="<?= htmlspecialchars($whatsappGeneralUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 px-0.5 text-on-surface-variant" aria-label="WhatsApp" title="WhatsApp">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-[22px] h-[22px] sm:w-[26px] sm:h-[26px]" fill="currentColor" aria-hidden="true">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
      </svg>
      <span class="text-[9px] sm:text-[10px] font-bold tracking-wide leading-none">WhatsApp</span>
    </a>
  </div>
</nav>
