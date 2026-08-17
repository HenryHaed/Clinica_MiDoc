<?php
/**
 * Footer — idéntico a prototipo.html
 */
$year = date('Y');
$phone = $phone ?? '70688374';
$basePath = $basePath ?? '';
?>
<!-- Comprehensive Footer -->
<footer class="bg-mist border-t border-sage/50 w-full pt-14 md:pt-24 pb-8 md:pb-12">
<div class="container mx-auto max-w-7xl px-5 md:px-12">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 md:gap-16 mb-12 md:mb-20">
<div>
<div class="mb-6">
<?= brand_mark('MiDoc', 'h-12 w-12 rounded-lg object-cover shrink-0', 'font-headline-lg text-3xl font-black text-cyan-deep tracking-tight') ?>
</div>
<p class="font-body-md text-lg text-on-surface-variant mb-8 leading-relaxed">
                        Atención médica de excelencia por el Dr. Hernán Caihuara Salas, brindando cuidado experto y compasivo para toda la familia.
                    </p>
<div class="flex gap-4">
<a class="w-12 h-12 rounded-full border border-light-cyan/60 flex items-center justify-center text-cyan-deep hover:border-light-cyan hover:bg-light-cyan/25 hover:text-on-surface transition-all" href="#" aria-label="Compartir">
<span class="material-symbols-outlined">share</span>
</a>
<a class="w-12 h-12 rounded-full border border-light-cyan/60 flex items-center justify-center text-cyan-deep hover:border-light-cyan hover:bg-light-cyan/25 hover:text-on-surface transition-all" href="mailto:info@clinicamidoc.com" aria-label="Correo">
<span class="material-symbols-outlined">mail</span>
</a>
</div>
</div>
<!-- Quick Links -->
<div>
<h4 class="font-label-md text-sm text-on-surface font-bold mb-8 uppercase tracking-widest">Enlaces Rápidos</h4>
<ul class="space-y-4">
<li><a class="font-body-md text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#servicios">Nuestros Servicios</a></li>
<li><a class="font-body-md text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#especialistas">Sobre el Doctor</a></li>
<li><a class="font-body-md text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#contacto">Agendar Cita</a></li>
<li><a class="font-body-md text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#ubicacion">Ubicación</a></li>
</ul>
</div>
<!-- Services Links -->
<div>
<h4 class="font-label-md text-sm text-on-surface font-bold mb-8 uppercase tracking-widest">Especialidades</h4>
<ul class="space-y-4">
<li><a class="font-body-md text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#servicios">Cirugía General</a></li>
<li><a class="font-body-md text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#especialistas">Cirugía Pediátrica</a></li>
<li><a class="font-body-md text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#servicios">Cirugía Laparoscópica</a></li>
<li><a class="font-body-md text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#servicios">Cirugía Torácica</a></li>
</ul>
</div>
<!-- Contact Info -->
<div>
<h4 class="font-label-md text-sm text-on-surface font-bold mb-8 uppercase tracking-widest">Contacto</h4>
<ul class="space-y-6">
<li class="flex items-start gap-4">
<span class="material-symbols-outlined text-cyan-deep text-xl">location_on</span>
<span class="font-body-md text-lg text-on-surface-variant">Clínica Saint Jude, Piso 1, Plaza El Radialista N° 102, Zona San Clemente</span>
</li>
<li class="flex items-center gap-4">
<span class="material-symbols-outlined text-cyan-deep text-xl">call</span>
<a class="font-body-md text-lg text-on-surface-variant font-bold" href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></a>
</li>
<li class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary text-xl">schedule</span>
<span class="font-body-md text-lg text-on-surface-variant">Lun - Vie: 8am - 6pm<br/>Sábados: Previa Cita</span>
</li>
</ul>
</div>
</div>
<!-- Bottom Bar -->
<div class="pt-8 border-t border-surface-container-highest flex flex-col md:flex-row justify-between items-center gap-6">
<div class="text-on-surface-variant font-body-md text-base text-center md:text-left">
                    © <?= (int) $year ?> MiDoc - Dr. Hernán Caihuara Salas. Clínica Saint Jude. Todos los derechos reservados.
                </div>
<div class="flex gap-8">
<a class="text-on-surface-variant font-label-md text-sm uppercase tracking-widest hover:text-cyan-deep transition-colors" href="#">Privacidad</a>
<a class="text-on-surface-variant font-label-md text-sm uppercase tracking-widest hover:text-cyan-deep transition-colors" href="#">Términos</a>
<span class="hidden" id="api-status-label" aria-hidden="true"></span>
</div>
</div>
</div>
</footer>
<script src="<?= htmlspecialchars(rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/') . '/assets/js/main.js', ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
