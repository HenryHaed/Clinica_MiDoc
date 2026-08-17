<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/cms.php';

cms_ensure_defaults();

$pageTitle = cms_get('seo.titulo');
$pageDescription = cms_get('seo.descripcion');
$basePath = '';
$phone = cms_get('config.telefono', '70688374');
$doctorPhoto = cms_get('especialista.foto');
$heroBgImage = cms_get('hero.fondo');
$heroSideImage = cms_get('hero.imagen');
$mapImage = cms_get('ubicacion.mapa_imagen');
$mapsUrl = cms_get('ubicacion.maps_url');

$heroBadge = cms_get('hero.badge');
$heroTitulo = cms_get('hero.titulo');
$heroSubtitulo = cms_get('hero.subtitulo');
$statExp = cms_get('hero.stat_experiencia', '15');
$statCir = cms_get('hero.stat_cirugias', '1000');
$heroCardTitulo = cms_get('hero.card_titulo');
$heroCardTexto = cms_get('hero.card_texto');

$serviciosTitulo = cms_get('servicios.titulo');
$serviciosSubtitulo = cms_get('servicios.subtitulo');
$contactoTitulo = cms_get('contacto.titulo');
$contactoSubtitulo = cms_get('contacto.subtitulo');
$ubicacionDireccion = cms_get('ubicacion.direccion');
$ubicacionHorarios = cms_get('ubicacion.horarios');
$ubicacionClinica = cms_get('ubicacion.clinica', 'Clínica Saint Jude');
$ubicacionZona = cms_get('ubicacion.zona', 'Zona San Clemente');

$serviciosLanding = servicios_list(false, true);
$serviciosForm = servicios_list(false, false);

// Fallback estático si no hay BD / seed
if ($serviciosLanding === []) {
    $serviciosLanding = [
        ['slug' => 'apendicitis', 'titulo' => 'Apendicitis', 'descripcion' => 'Intervención quirúrgica oportuna y segura para el tratamiento de la apendicitis aguda.', 'icono' => 'medical_services', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'colecistitis', 'titulo' => 'Colecistitis', 'descripcion' => 'Tratamiento especializado para problemas de la vesícula biliar mediante técnicas modernas.', 'icono' => 'healing', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'hernias', 'titulo' => 'Hernias', 'descripcion' => 'Reparación quirúrgica de hernias abdominales e inguinales para restaurar su calidad de vida.', 'icono' => 'accessibility_new', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'laparoscopica', 'titulo' => 'Cirugía Laparoscópica', 'descripcion' => 'Técnicas mínimamente invasivas que reducen el dolor postoperatorio, acortan el tiempo de recuperación y dejan cicatrices más pequeñas.', 'icono' => 'biotech', 'imagen_url' => 'https://lh3.googleusercontent.com/aida/AP1WRLsv8K-AAmcJ5r8o_2JQk67pdxZ8ONKRtTue1Qp_a5ribEzRz51WIMeoZ0YPdBdRuD6APvL7wJJuzy7JW2icImOk5E1VVVbLnkkm6zb-9XSlpznt4KGmqvo_3989YyuSn4UCSE8aqJVW3qfecvQZz2Pkz4FlxkSgHnuPwACAKap2IFNe8aCBKaGOhSnAwBOrFcmHPSwo12Z0bZiouQonqRzgm6yFifaJOttdtZALerVAfh-jHsKZrb9gF90', 'destacado' => 1],
        ['slug' => 'toracica', 'titulo' => 'Cirugía Torácica', 'descripcion' => 'Procedimientos especializados para afecciones del tórax, garantizando atención experta y cuidado integral.', 'icono' => 'pulmonology', 'imagen_url' => null, 'destacado' => 0],
    ];
}
if ($serviciosForm === []) {
    $serviciosForm = array_merge($serviciosLanding, [
        ['slug' => 'otro', 'titulo' => 'Consulta General / Otro', 'descripcion' => '', 'icono' => 'stethoscope'],
    ]);
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>
<!-- Main Content -->
<main class="w-full">
<!-- Hero Section -->
<section id="especialistas" class="relative min-h-[100svh] flex items-center justify-center pt-16 md:pt-20 pb-8 overflow-hidden bg-mist">
<!-- Background Image with Overlay -->
<div class="absolute inset-0 z-0">
<img alt="Modern clinic hospital background" class="w-full h-full object-cover" src="<?= htmlspecialchars($heroBgImage, ENT_QUOTES, 'UTF-8') ?>"/>
<div class="absolute inset-0 brand-hero-overlay"></div>
</div>
<div class="container mx-auto px-5 md:px-12 relative z-10 grid md:grid-cols-2 gap-8 md:gap-16 items-center">
<div class="flex flex-col gap-5 md:gap-8 reveal reveal-up">
<div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-light-cyan bg-light-cyan/20 text-cyan-deep w-fit">
<span class="material-symbols-outlined text-base text-light-cyan">verified</span>
<span class="font-label-sm text-xs sm:text-sm font-bold uppercase tracking-widest"><?= htmlspecialchars($heroBadge, ENT_QUOTES, 'UTF-8') ?></span>
</div>
<h1 class="font-headline-lg text-[1.85rem] leading-tight sm:text-4xl md:text-6xl lg:text-7xl font-black text-on-surface tracking-tighter">
                        <?= cms_format_hero_title($heroTitulo) ?>
                    </h1>
<p class="font-body-lg text-base sm:text-xl text-on-surface-variant max-w-xl leading-relaxed">
                        <?= htmlspecialchars($heroSubtitulo, ENT_QUOTES, 'UTF-8') ?>
                    </p>
<div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-6 mt-2 md:mt-4">
<a href="#contacto" class="bg-brand-yellow text-on-secondary-fixed px-6 sm:px-8 py-3.5 sm:py-4 rounded-lg font-label-md text-sm sm:text-base font-bold uppercase tracking-wide hover:bg-brand-orange hover:text-white transition-all flex items-center justify-center gap-3 shadow-[0_0_20px_rgba(255,145,0,0.45)]">
                            Agenda tu Cita
                            <span class="material-symbols-outlined">arrow_forward</span>
</a>
<a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" class="px-6 sm:px-8 py-3.5 sm:py-4 rounded-lg font-label-md text-sm sm:text-base font-bold uppercase tracking-wide text-cyan-deep border-2 border-light-cyan bg-light-cyan/20 hover:bg-light-cyan hover:text-white transition-all flex items-center justify-center gap-3 shadow-[0_0_18px_rgba(0,62,103,0.35)]">
                            Línea: <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
                        </a>
</div>
<div class="flex items-center gap-6 sm:gap-12 mt-8 md:mt-12 pt-8 md:pt-12 border-t border-sage/60">
<div>
<div class="font-headline-lg text-4xl md:text-5xl font-black text-cyan-deep mb-2"><span data-counter="<?= htmlspecialchars($statExp, ENT_QUOTES, 'UTF-8') ?>" data-suffix="+"><?= htmlspecialchars($statExp, ENT_QUOTES, 'UTF-8') ?>+</span></div>
<div class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-on-surface-variant">Años de Experiencia</div>
</div>
<div class="w-px h-12 sm:h-16 bg-sage"></div>
<div>
<div class="font-headline-lg text-4xl md:text-5xl font-black text-brand-orange mb-2"><span data-counter="<?= htmlspecialchars($statCir, ENT_QUOTES, 'UTF-8') ?>" data-suffix="+"><?= htmlspecialchars($statCir, ENT_QUOTES, 'UTF-8') ?>+</span></div>
<div class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-on-surface-variant">Cirugías Exitosas</div>
</div>
</div>
</div>
<div class="relative h-72 sm:h-96 md:h-[560px] lg:h-[700px] w-full rounded-xl overflow-hidden border border-light-cyan/50 shadow-[0_0_30px_rgba(0,62,103,0.22)] reveal reveal-up reveal-delay-2">
<img alt="Modern operating room" class="w-full h-full object-cover" src="<?= htmlspecialchars($heroSideImage, ENT_QUOTES, 'UTF-8') ?>"/>
<div class="absolute inset-0 bg-gradient-to-t from-mist/90 via-transparent to-transparent"></div>
<div class="absolute bottom-4 left-4 right-4 sm:bottom-8 sm:left-8 sm:right-8 bg-mist/95 backdrop-blur-md p-4 sm:p-8 rounded-xl border border-light-cyan/40">
<div class="flex items-center gap-3 sm:gap-4 mb-2 sm:mb-4">
<span class="material-symbols-outlined text-light-cyan text-3xl sm:text-4xl drop-shadow-[0_0_8px_rgba(0,62,103,0.55)]">health_and_safety</span>
<h3 class="font-headline-md text-lg sm:text-2xl font-bold text-on-surface"><?= htmlspecialchars($heroCardTitulo, ENT_QUOTES, 'UTF-8') ?></h3>
</div>
<p class="font-body-md text-sm sm:text-lg text-on-surface-variant"><?= htmlspecialchars($heroCardTexto, ENT_QUOTES, 'UTF-8') ?></p>
</div>
</div>
</div>
</section>
<!-- Services Section -->
<section class="py-16 md:py-32 px-5 md:px-12 bg-mist border-t border-sage/40" id="servicios">
<div class="container mx-auto max-w-7xl">
<div class="mb-10 md:mb-20 reveal reveal-left">
<h2 class="font-headline-lg text-3xl md:text-5xl font-black text-primary mb-4 md:mb-6 tracking-tight"><?= htmlspecialchars($serviciosTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
<p class="font-body-lg text-base md:text-xl text-on-surface-variant max-w-3xl leading-relaxed"><?= htmlspecialchars($serviciosSubtitulo, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<?php
$delay = 0;
foreach ($serviciosLanding as $svc):
    $titulo = (string) $svc['titulo'];
    $desc = (string) $svc['descripcion'];
    $destacado = !empty($svc['destacado']);
    $img = trim((string) ($svc['imagen_url'] ?? ''));
    $delayClass = 'reveal-delay-' . (($delay % 3) + 1);
    $delay++;
    $imgHtml = $img !== ''
        ? '<img alt="' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '"/>'
        : '<div class="w-full h-full flex items-center justify-center bg-surface-container text-on-surface-variant/70 text-sm px-4 text-center">Imagen de especialidad</div>';

    if ($destacado):
?>
<div class="bg-mist rounded-xl overflow-hidden border border-brand-orange/40 hover:border-brand-orange hover:shadow-[0_0_15px_rgba(255,145,0,0.25)] transition-all group cursor-pointer lg:col-span-2 flex flex-col justify-center reveal reveal-left <?= $delayClass ?>">
<div class="flex flex-col md:flex-row">
<div class="w-full md:w-1/2 h-56 md:h-auto min-h-[220px] overflow-hidden shrink-0">
<?= $imgHtml ?>
</div>
<div class="flex-1 p-5 md:p-8">
<h3 class="font-headline-md text-2xl md:text-3xl font-bold text-on-surface mb-3 md:mb-4"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h3>
<p class="font-body-md text-base md:text-lg text-on-surface-variant mb-6 md:mb-8"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></p>
<a class="inline-flex items-center gap-2 font-label-md text-base font-bold uppercase tracking-wider text-primary group-hover:text-on-surface transition-colors" href="#contacto">
                                    Explorar técnica <span class="material-symbols-outlined">arrow_forward</span>
</a>
</div>
</div>
</div>
<?php else: ?>
<div class="bg-mist rounded-xl overflow-hidden border border-sage/60 hover:border-light-cyan hover:shadow-[0_0_15px_rgba(0,62,103,0.28)] transition-all group cursor-pointer flex flex-col h-full reveal reveal-left <?= $delayClass ?>">
<div class="h-48 w-full overflow-hidden">
<?= $imgHtml ?>
</div>
<div class="p-5 md:p-8 flex flex-col flex-grow">
<h3 class="font-headline-md text-xl md:text-2xl font-bold text-on-surface mb-3 md:mb-4"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h3>
<p class="font-body-md text-base md:text-lg text-on-surface-variant mb-6 md:mb-8 flex-grow"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></p>
<a class="inline-flex items-center gap-2 font-label-md text-base font-bold uppercase tracking-wider text-cyan-deep group-hover:text-light-cyan transition-colors mt-auto" href="#contacto">
                            Saber más <span class="material-symbols-outlined">arrow_forward</span>
</a>
</div>
</div>
<?php
    endif;
endforeach;
?>
</div>
</div>
</section>
<!-- Schedule Appointment Section -->
<section class="py-16 md:py-32 px-5 md:px-12 bg-mist border-t border-sage/40 flex items-center justify-center" id="contacto">
<div class="container mx-auto max-w-5xl">
<div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-start">
<div class="reveal reveal-left">
<h2 class="font-headline-lg text-3xl md:text-5xl font-black text-primary mb-4 md:mb-6 tracking-tight"><?= htmlspecialchars($contactoTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
<p class="font-body-lg text-base md:text-xl text-on-surface-variant mb-8 md:mb-12 leading-relaxed"><?= htmlspecialchars($contactoSubtitulo, ENT_QUOTES, 'UTF-8') ?></p>
<div class="space-y-6 md:space-y-8">
<div class="flex items-start gap-4 md:gap-6">
<div class="w-12 h-12 md:w-16 md:h-16 rounded-full border-2 border-light-cyan bg-light-cyan/20 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-cyan-deep text-2xl">location_on</span>
</div>
<div>
<h4 class="font-label-md text-sm uppercase tracking-widest text-on-surface-variant mb-2">Dirección</h4>
<p class="font-body-md text-base md:text-xl text-on-surface font-bold"><?= cms_nl2br_safe($ubicacionDireccion) ?></p>
</div>
</div>
<div class="flex items-start gap-4 md:gap-6">
<div class="w-12 h-12 md:w-16 md:h-16 rounded-full border-2 border-brand-orange bg-brand-yellow/30 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-surface text-2xl">call</span>
</div>
<div>
<h4 class="font-label-md text-sm uppercase tracking-widest text-on-surface-variant mb-2">Línea Directa</h4>
<p class="font-body-md text-xl md:text-2xl text-cyan-deep font-black"><a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></a></p>
</div>
</div>
<div class="flex items-start gap-4 md:gap-6">
<div class="w-12 h-12 md:w-16 md:h-16 rounded-full border-2 border-mint bg-mint/20 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-teal text-2xl">schedule</span>
</div>
<div>
<h4 class="font-label-md text-sm uppercase tracking-widest text-on-surface-variant mb-2">Horarios de Atención</h4>
<p class="font-body-md text-lg text-on-surface font-bold"><?= cms_nl2br_safe($ubicacionHorarios) ?></p>
</div>
</div>
</div>
</div>
<div class="bg-mist p-5 sm:p-8 md:p-10 rounded-xl border border-light-cyan/40 shadow-[0_0_20px_rgba(0,62,103,0.12)] reveal reveal-right reveal-delay-1">
<form id="cita-form" class="grid grid-cols-1 gap-6 md:gap-8" method="post" action="api/cita.php" novalidate>
<div class="space-y-3">
<label class="font-label-md text-sm uppercase tracking-widest text-on-surface-variant" for="name">Nombre Completo</label>
<input class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base sm:text-lg" id="name" name="nombre" placeholder="Ej: Juan Pérez" type="text" required autocomplete="name"/>
</div>
<div class="space-y-3">
<label class="font-label-md text-sm uppercase tracking-widest text-on-surface-variant" for="phone">Teléfono / Celular</label>
<input class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base sm:text-lg" id="phone" name="telefono" placeholder="Ej: 70688374" type="tel" required autocomplete="tel"/>
</div>
<div class="space-y-3">
<label class="font-label-md text-sm uppercase tracking-widest text-on-surface-variant" for="service">Motivo de Consulta</label>
<select class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base sm:text-lg appearance-none" id="service" name="servicio" required>
<option value="">Selecciona un servicio...</option>
<?php foreach ($serviciosForm as $opt): ?>
<option value="<?= htmlspecialchars((string) $opt['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $opt['titulo'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="space-y-3">
<label class="font-label-md text-sm uppercase tracking-widest text-on-surface-variant" for="message">Mensaje Adicional</label>
<textarea class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 sm:px-6 py-3.5 sm:py-4 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base sm:text-lg min-h-[120px]" id="message" name="mensaje" placeholder="Detalles de la cita..."></textarea>
</div>
<button id="cita-submit" class="bg-brand-yellow hover:bg-brand-orange text-on-secondary-fixed hover:text-white px-8 py-5 rounded-lg font-label-md text-lg font-bold uppercase tracking-wider transition-colors w-full flex items-center justify-center gap-3 mt-4 disabled:opacity-60" type="submit">
                        <span id="cita-submit-label">Solicitar Cita Ahora</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
</button>
<p id="cita-form-msg" class="hidden font-label-md text-sm text-center" role="status" aria-live="polite"></p>
</form>
</div>
</div>
</div>
</section>
<!-- Location Map Section -->
<section id="ubicacion" class="h-[50vh] min-h-[360px] md:h-[60vh] md:min-h-[500px] w-full border-t border-sage/40 relative bg-mist flex items-center justify-center">
<div class="absolute inset-0 bg-cover bg-center grayscale" style="background-image: url('<?= htmlspecialchars($mapImage, ENT_QUOTES, 'UTF-8') ?>')"></div>
<div class="relative z-10 bg-mist/95 backdrop-blur-md p-6 sm:p-8 rounded-xl border border-light-cyan/40 max-w-sm mx-5 text-center shadow-[0_0_20px_rgba(0,62,103,0.14)] reveal reveal-up">
<div class="w-20 h-20 mx-auto rounded-full border-2 border-light-cyan bg-light-cyan/20 flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-cyan-deep text-4xl">pin_drop</span>
</div>
<h3 class="font-headline-md text-3xl font-bold text-on-surface mb-2"><?= htmlspecialchars($ubicacionClinica, ENT_QUOTES, 'UTF-8') ?></h3>
<p class="font-body-md text-lg text-on-surface-variant mb-8"><?= htmlspecialchars($ubicacionZona, ENT_QUOTES, 'UTF-8') ?></p>
<a href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="w-full border-2 border-brand-orange text-on-surface px-6 py-4 rounded-lg font-label-md text-base font-bold uppercase tracking-wider hover:bg-brand-yellow transition-colors inline-flex justify-center items-center gap-3">
<span class="material-symbols-outlined">directions</span>
                    Cómo Llegar
                </a>
</div>
</section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
