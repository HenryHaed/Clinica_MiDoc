<?php
/**
 * Navegación landing: top compacto + barra inferior tipo app (móvil).
 */
$doctorPhoto = $doctorPhoto ?? 'https://lh3.googleusercontent.com/aida-public/AB6AXuDQ00TRNKSzW1IeH4fiOrmtUbcjbLCCGlPgfqZ_8yz3xGbzOPR_3PFPiqDHUnlcw0lT1aJHtm2aOeaVqnqMcuUWt6WGqWTzHUGEwXo9ANOEvrPZgJanwn-8iM8q_Ih2OaqvFbJXuZJg5nddpYjfMt3yjTMtfW6cyXJE4JCT9doBg-hvy-7UaDLCoPM9oQ2yg-shU3Ipn7V23KOOA03yR8lzdSodgbpiOWyvvV5CC5b0p4DwZ73pzDmB';
$phone = $phone ?? '70688374';
$landingNav = $landingNav ?? 'inicio';
?>
<nav class="fixed top-0 left-0 right-0 z-50 bg-mist/95 backdrop-blur-md border-b border-light-cyan/40 h-14 md:h-20">
  <div class="h-full max-w-7xl mx-auto flex items-center justify-between px-4 md:px-12">
    <a href="index.php" class="hover:opacity-90 transition-opacity min-h-11 min-w-11 flex items-center"><?= brand_mark('MiDoc', 'h-8 w-8 md:h-10 md:w-10 rounded-md object-cover shrink-0') ?></a>

    <div class="hidden md:flex items-center gap-8">
      <a class="text-on-surface-variant font-label-md text-sm uppercase tracking-widest hover:text-cyan-deep transition-colors" href="#servicios">Servicios</a>
      <a class="text-on-surface-variant font-label-md text-sm uppercase tracking-widest hover:text-cyan-deep transition-colors" href="#especialistas">Especialistas</a>
      <a class="text-cyan-deep font-bold border-b-2 border-light-cyan font-label-md text-sm uppercase tracking-widest pb-1" href="#contacto">Contacto</a>
    </div>

    <div class="flex items-center gap-1 md:gap-4">
      <a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" class="w-11 h-11 flex items-center justify-center rounded-full hover:bg-light-cyan/25 transition-colors" aria-label="Llamar">
        <span class="material-symbols-outlined text-cyan-deep text-2xl">phone</span>
      </a>
      <a href="login.php" class="hidden md:inline-flex border border-light-cyan text-cyan-deep px-4 py-2 rounded-lg font-label-md text-sm font-bold uppercase tracking-wide hover:bg-light-cyan/25 transition-colors">Iniciar Sesión</a>
      <a href="#contacto" class="hidden md:inline-flex bg-brand-yellow text-on-secondary-fixed px-6 py-3 rounded-lg font-label-md text-sm font-bold uppercase tracking-wide hover:bg-brand-orange hover:text-white transition-all shadow-[0_0_15px_rgba(255,145,0,0.35)]">Agenda tu Cita</a>
      <div class="hidden md:block w-10 h-10 rounded-full bg-surface-container-highest overflow-hidden border border-outline-variant">
        <img alt="Dr. Hernán Caihuara Salas" class="w-full h-full object-cover" src="<?= htmlspecialchars($doctorPhoto, ENT_QUOTES, 'UTF-8') ?>"/>
      </div>
    </div>
  </div>
</nav>

<nav class="landing-bottom-nav md:hidden fixed bottom-0 left-0 right-0 z-50 bg-mist/95 backdrop-blur-xl border-t border-sage/50 shadow-[0_-8px_24px_rgba(0,62,103,0.08)]" aria-label="Navegación principal">
  <div class="grid grid-cols-5 items-end px-1 pt-1">
    <a href="index.php#especialistas" data-nav="inicio" class="js-land-nav flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 text-on-surface-variant <?= $landingNav === 'inicio' ? 'is-active' : '' ?>">
      <span class="material-symbols-outlined text-[26px]">home</span>
      <span class="text-[10px] font-bold tracking-wide">Inicio</span>
    </a>
    <a href="index.php#servicios" data-nav="servicios" class="js-land-nav flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 text-on-surface-variant">
      <span class="material-symbols-outlined text-[26px]">medical_services</span>
      <span class="text-[10px] font-bold tracking-wide">Servicios</span>
    </a>
    <a href="index.php#contacto" data-nav="contacto" class="js-land-nav flex flex-col items-center justify-center -mt-5">
      <span class="w-14 h-14 rounded-full bg-brand-yellow text-on-secondary-fixed shadow-[0_8px_20px_rgba(255,145,0,0.45)] flex items-center justify-center border-4 border-mist">
        <span class="material-symbols-outlined text-[28px]" style="font-variation-settings:'FILL' 1;">calendar_month</span>
      </span>
      <span class="text-[10px] font-bold tracking-wide text-cyan-deep mt-0.5">Cita</span>
    </a>
    <a href="index.php#ubicacion" data-nav="ubicacion" class="js-land-nav flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 text-on-surface-variant">
      <span class="material-symbols-outlined text-[26px]">location_on</span>
      <span class="text-[10px] font-bold tracking-wide">Mapa</span>
    </a>
    <a href="login.php" data-nav="cuenta" class="flex flex-col items-center justify-center gap-0.5 min-h-12 py-1 text-on-surface-variant">
      <span class="material-symbols-outlined text-[26px]">person</span>
      <span class="text-[10px] font-bold tracking-wide">Cuenta</span>
    </a>
  </div>
</nav>
