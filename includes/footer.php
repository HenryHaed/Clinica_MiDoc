<?php
/**
 * Footer — landing
 */
$year = date('Y');
$phone = $phone ?? '70688374';
$whatsappGeneralUrl = $whatsappGeneralUrl ?? ('https://wa.me/591' . preg_replace('/\D+/', '', (string) $phone));
$basePath = $basePath ?? '';
$mapsUrl = $mapsUrl ?? 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9';
$email = $email ?? (function_exists('cms_get') ? cms_get('config.email', 'midochernandigital@gmail.com') : 'midochernandigital@gmail.com');
$ubicacionDireccionFooter = $ubicacionDireccion ?? "Clínica Saint Jude, Piso 1, Plaza El Radialista N° 102, Zona San Clemente";
$ubicacionHorariosFooter = $ubicacionHorarios ?? "Lun - Vie: 8am - 6pm\nSábados: Previa Cita";
$redesFacebook = $redesFacebook ?? 'https://www.facebook.com/share/1Yfdz5ojML/';
$redesYoutube = $redesYoutube ?? 'https://www.youtube.com/@midochernan';
$redesTiktok = $redesTiktok ?? 'https://www.tiktok.com/@midochernan';
?>
<footer class="bg-mist border-t border-sage/50 w-full max-w-full overflow-x-hidden pt-12 md:pt-24 pb-8 md:pb-12">
<div class="landing-shell container mx-auto max-w-7xl px-4 sm:px-6 md:px-12 min-w-0">
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-16 mb-10 md:mb-20">
<div class="min-w-0">
<div class="mb-5">
<?= brand_mark('MiDoc', 'h-10 w-10 sm:h-12 sm:w-12 rounded-lg object-cover shrink-0', 'font-headline-lg text-2xl sm:text-3xl font-black text-cyan-deep tracking-tight') ?>
</div>
<p class="font-body-md text-sm sm:text-lg text-on-surface-variant mb-6 leading-relaxed landing-break">
                        Atención médica de excelencia por el Dr. Hernán Caihuara Salas, brindando cuidado experto y compasivo para toda la familia.
                    </p>
<div class="flex flex-wrap gap-3">
<?php if (trim((string) $redesFacebook) !== ''): ?>
<a class="w-11 h-11 rounded-full border border-cyan-deep/40 text-cyan-deep bg-transparent hover:bg-cyan-deep hover:text-white transition-all inline-flex items-center justify-center" href="<?= htmlspecialchars($redesFacebook, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
<?= function_exists('social_icon_svg') ? social_icon_svg('facebook', 'w-5 h-5') : '<span class="material-symbols-outlined">share</span>' ?>
</a>
<?php endif; ?>
<?php if (trim((string) $redesYoutube) !== ''): ?>
<a class="w-11 h-11 rounded-full border border-cyan-deep/40 text-cyan-deep bg-transparent hover:bg-cyan-deep hover:text-white transition-all inline-flex items-center justify-center" href="<?= htmlspecialchars($redesYoutube, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
<?= function_exists('social_icon_svg') ? social_icon_svg('youtube', 'w-5 h-5') : '<span class="material-symbols-outlined">play_circle</span>' ?>
</a>
<?php endif; ?>
<?php if (trim((string) $redesTiktok) !== ''): ?>
<a class="w-11 h-11 rounded-full border border-cyan-deep/40 text-cyan-deep bg-transparent hover:bg-cyan-deep hover:text-white transition-all inline-flex items-center justify-center" href="<?= htmlspecialchars($redesTiktok, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
<?= function_exists('social_icon_svg') ? social_icon_svg('tiktok', 'w-5 h-5') : '<span class="material-symbols-outlined">music_note</span>' ?>
</a>
<?php endif; ?>
<a class="w-11 h-11 rounded-full border border-cyan-deep/40 text-cyan-deep bg-transparent hover:bg-cyan-deep hover:text-white transition-all inline-flex items-center justify-center" href="mailto:<?= htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8') ?>" aria-label="Correo">
<span class="material-symbols-outlined">mail</span>
</a>
</div>
</div>
<div class="min-w-0">
<h4 class="font-label-md text-xs sm:text-sm text-on-surface font-bold mb-5 uppercase tracking-wider sm:tracking-widest">Enlaces Rápidos</h4>
<ul class="space-y-3">
<li><a class="font-body-md text-sm sm:text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#servicios">Nuestros Servicios</a></li>
<li><a class="font-body-md text-sm sm:text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#especialistas">Sobre el Doctor</a></li>
<li><a class="font-body-md text-sm sm:text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#contacto">Agendar Cita</a></li>
<?php if (!empty($videosVisible) && !empty($landingVideosPublic)): ?>
<li><a class="font-body-md text-sm sm:text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#videos">Videos</a></li>
<?php endif; ?>
<?php if (!empty($redesVisible)): ?>
<li><a class="font-body-md text-sm sm:text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#redes">Redes sociales</a></li>
<?php endif; ?>
<li><button type="button" data-ubicacion-modal class="font-body-md text-sm sm:text-lg text-on-surface-variant hover:text-cyan-deep transition-colors text-left">Ubicación</button></li>
</ul>
</div>
<div class="min-w-0">
<h4 class="font-label-md text-xs sm:text-sm text-on-surface font-bold mb-5 uppercase tracking-wider sm:tracking-widest">Especialidades</h4>
<ul class="space-y-3">
<li><a class="font-body-md text-sm sm:text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#servicios-adultos">Cirugía General Adultos</a></li>
<li><a class="font-body-md text-sm sm:text-lg text-on-surface-variant hover:text-cyan-deep transition-colors" href="#servicios-pediatrica">Cirugía Pediátrica</a></li>
</ul>
</div>
<div class="min-w-0">
<h4 class="font-label-md text-xs sm:text-sm text-on-surface font-bold mb-5 uppercase tracking-wider sm:tracking-widest">Contacto</h4>
<ul class="space-y-4">
<li class="flex items-start gap-3 min-w-0">
<span class="material-symbols-outlined text-cyan-deep text-xl shrink-0">location_on</span>
<a href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="font-body-md text-sm sm:text-lg text-on-surface-variant landing-break hover:text-cyan-deep transition-colors underline-offset-2 hover:underline"><?= function_exists('cms_nl2br_safe') ? cms_nl2br_safe(is_string($ubicacionDireccionFooter) ? $ubicacionDireccionFooter : '') : htmlspecialchars((string) $ubicacionDireccionFooter, ENT_QUOTES, 'UTF-8') ?></a>
</li>
<li class="flex items-center gap-3">
<span class="material-symbols-outlined text-cyan-deep text-xl shrink-0">call</span>
<a class="font-body-md text-sm sm:text-lg text-on-surface-variant font-bold hover:text-cyan-deep transition-colors" href="<?= htmlspecialchars($whatsappGeneralUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?> (WhatsApp)</a>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary text-xl shrink-0">schedule</span>
<span class="font-body-md text-sm sm:text-lg text-on-surface-variant"><?= function_exists('cms_nl2br_safe') ? cms_nl2br_safe(is_string($ubicacionHorariosFooter) ? $ubicacionHorariosFooter : '') : 'Lun - Vie: 8am - 6pm<br/>Sábados: Previa Cita' ?></span>
</li>
<li>
<a href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 font-label-md text-xs sm:text-sm font-bold uppercase tracking-wider text-cyan-deep hover:text-light-cyan transition-colors">
<span class="material-symbols-outlined text-base">directions</span>
Cómo llegar
</a>
</li>
</ul>
</div>
</div>
<div class="pt-6 border-t border-surface-container-highest flex flex-col md:flex-row justify-between items-center gap-4">
<div class="text-on-surface-variant font-body-md text-xs sm:text-base text-center md:text-left landing-break px-1">
                    © <?= (int) $year ?> MiDoc - Dr. Hernán Caihuara Salas. Clínica Saint Jude. Todos los derechos reservados.
                </div>
<div class="flex gap-6">
<a class="text-on-surface-variant font-label-md text-xs sm:text-sm uppercase tracking-wider hover:text-cyan-deep transition-colors" href="#">Privacidad</a>
<a class="text-on-surface-variant font-label-md text-xs sm:text-sm uppercase tracking-wider hover:text-cyan-deep transition-colors" href="#">Términos</a>
<span class="hidden" id="api-status-label" aria-hidden="true"></span>
</div>
</div>
</div>
</footer>
<script src="<?= htmlspecialchars(rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/') . '/assets/js/main.js?v=' . (string) @filemtime(__DIR__ . '/../assets/js/main.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
