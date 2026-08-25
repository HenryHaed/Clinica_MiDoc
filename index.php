<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/cms.php';
require_once __DIR__ . '/includes/theme.php';
require_once __DIR__ . '/includes/seo.php';

cms_ensure_defaults();

$pageTitle = cms_get('seo.titulo', 'MiDoc - Cirugía General y Pediátrica | Dr. Hernán Caihuara');
$pageDescription = cms_get('seo.descripcion', 'Clínica MiDoc. Dr. Hernán Caihuara Salas, Cirujano General Adultos y Cirujano Pediatra en Clínica Saint Jude. Agenda tu cita.');
$pageKeywords = cms_get('seo.keywords', 'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano, laparoscópica');
$siteName = cms_get('config.sitio_nombre', 'MiDoc');
$canonicalUrl = seo_absolute_url('index.php');
$ogImage = seo_absolute_url(cms_get('hero.imagen', brand_logo_src()));
$pageRobots = 'index, follow, max-image-preview:large';

$seoJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'MedicalClinic',
    'name' => $siteName,
    'url' => seo_absolute_url('index.php'),
    'logo' => seo_absolute_url(brand_logo_src()),
    'image' => $ogImage,
    'description' => $pageDescription,
    'telephone' => cms_get('config.telefono', '70688374'),
    'email' => cms_get('config.email', 'info@clinicamidoc.com'),
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', cms_get('ubicacion.direccion', 'Clínica Saint Jude'))),
        'addressLocality' => cms_get('ubicacion.zona', 'Zona San Clemente'),
        'addressCountry' => 'BO',
    ],
    'medicalSpecialty' => ['Cirugía General', 'Cirugía Pediátrica', 'Cirugía Laparoscópica'],
    'employee' => [
        '@type' => 'Physician',
        'name' => cms_get('especialista.nombre', 'Dr. Hernán Caihuara Salas'),
        'jobTitle' => cms_get('especialista.rol', 'Cirujano General Adultos y Cirujano Pediatra'),
        'image' => seo_absolute_url(cms_get('especialista.foto', brand_logo_src())),
    ],
];

$basePath = '';
$phone = cms_get('config.telefono', '70688374');
$doctorPhoto = cms_get('especialista.foto');
$heroBgImage = cms_get('hero.fondo');
$heroSideImage = cms_get('hero.imagen');
$mapImage = cms_get('ubicacion.mapa_imagen');
$mapsUrl = cms_get('ubicacion.maps_url', 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9');
$mapsEmbed = cms_get(
    'ubicacion.maps_embed',
    'https://www.google.com/maps?q=Plaza+El+Radialista+102,+Zona+San+Clemente&output=embed'
);

$heroBadge = cms_get('hero.badge');
$heroTitulo = cms_get('hero.titulo');
$heroSubtitulo = cms_get('hero.subtitulo');
$statExp = cms_get('hero.stat_experiencia', '15');
$statCir = cms_get('hero.stat_cirugias', '1000');

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
<main class="landing-shell w-full max-w-full overflow-x-hidden">
<!-- Hero Section -->
<section id="especialistas" class="relative min-h-[100svh] flex items-center justify-center pt-16 md:pt-20 pb-10 md:pb-8 overflow-hidden bg-mist w-full">
<!-- Background Image with Overlay -->
<div class="absolute inset-0 z-0">
<img alt="Modern clinic hospital background" class="w-full h-full object-cover" src="<?= htmlspecialchars($heroBgImage, ENT_QUOTES, 'UTF-8') ?>"/>
<div class="absolute inset-0 brand-hero-overlay"></div>
</div>
<div class="landing-shell relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-12 grid md:grid-cols-2 gap-6 md:gap-16 items-start min-w-0">
<div class="flex flex-col gap-4 md:gap-8 reveal reveal-up min-w-0 w-full max-w-full md:pt-6">
<div class="inline-flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full border border-light-cyan bg-light-cyan/20 text-cyan-deep w-fit max-w-full">
<span class="material-symbols-outlined text-base text-light-cyan shrink-0">verified</span>
<span class="font-label-sm text-[10px] sm:text-sm font-bold uppercase tracking-wider sm:tracking-widest landing-break"><?= htmlspecialchars($heroBadge, ENT_QUOTES, 'UTF-8') ?></span>
</div>
<h1 class="font-headline-lg text-[1.65rem] leading-[1.15] sm:text-4xl md:text-6xl lg:text-7xl font-black text-on-surface tracking-tight md:tracking-tighter landing-break max-w-full">
                        <?= cms_format_hero_title($heroTitulo) ?>
                    </h1>
<p class="font-body-lg text-[0.95rem] sm:text-xl text-on-surface-variant max-w-xl leading-relaxed landing-break">
                        <?= htmlspecialchars($heroSubtitulo, ENT_QUOTES, 'UTF-8') ?>
                    </p>
<div class="flex flex-col sm:flex-row flex-wrap gap-2.5 sm:gap-4 mt-1 md:mt-4 w-full">
<a href="#contacto" class="bg-brand-yellow text-on-secondary-fixed px-5 sm:px-8 py-3.5 sm:py-4 rounded-lg font-label-md text-xs sm:text-base font-bold uppercase tracking-wide hover:bg-brand-orange hover:text-white transition-all flex items-center justify-center gap-2 sm:gap-3 shadow-[0_0_20px_rgba(255,145,0,0.45)] w-full sm:w-auto">
                            Agenda tu Cita
                            <span class="material-symbols-outlined text-xl">arrow_forward</span>
</a>
<a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" class="px-5 sm:px-8 py-3.5 sm:py-4 rounded-lg font-label-md text-xs sm:text-base font-bold uppercase tracking-wide text-cyan-deep border-2 border-light-cyan bg-light-cyan/20 hover:bg-light-cyan hover:text-white transition-all flex items-center justify-center gap-2 sm:gap-3 w-full sm:w-auto">
                            Línea: <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
                        </a>
</div>
<div class="grid grid-cols-2 gap-3 sm:flex sm:items-center sm:gap-12 mt-6 md:mt-12 pt-6 md:pt-12 border-t border-sage/60 w-full min-w-0">
<div class="min-w-0">
<div class="font-headline-lg text-3xl sm:text-4xl md:text-5xl font-black text-cyan-deep mb-1 sm:mb-2"><span data-counter="<?= htmlspecialchars($statExp, ENT_QUOTES, 'UTF-8') ?>" data-suffix="+"><?= htmlspecialchars($statExp, ENT_QUOTES, 'UTF-8') ?>+</span></div>
<div class="font-label-md text-[10px] sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant leading-snug">Años de Experiencia</div>
</div>
<div class="min-w-0 sm:border-l sm:border-sage sm:pl-12">
<div class="font-headline-lg text-3xl sm:text-4xl md:text-5xl font-black text-brand-orange mb-1 sm:mb-2"><span data-counter="<?= htmlspecialchars($statCir, ENT_QUOTES, 'UTF-8') ?>" data-suffix="+"><?= htmlspecialchars($statCir, ENT_QUOTES, 'UTF-8') ?>+</span></div>
<div class="font-label-md text-[10px] sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant leading-snug">Cirugías Exitosas</div>
</div>
</div>
</div>
<div class="relative aspect-square w-full max-w-[18rem] sm:max-w-[22rem] md:max-w-[24rem] lg:max-w-[28rem] mx-auto md:ml-auto md:mr-0 mt-[100px] min-w-0 rounded-xl overflow-hidden border border-light-cyan/50 shadow-[0_0_30px_rgba(0,62,103,0.22)] reveal reveal-up reveal-delay-2">
<img alt="Modern operating room" class="absolute inset-0 w-full h-full object-cover object-top" src="<?= htmlspecialchars($heroSideImage, ENT_QUOTES, 'UTF-8') ?>"/>
<div class="absolute inset-0 bg-gradient-to-t from-mist/90 via-transparent to-transparent"></div>
</div>
</div>
</section>
<!-- Services Section -->
<section class="py-12 sm:py-16 md:py-32 px-4 sm:px-6 md:px-12 bg-mist border-t border-sage/40 w-full overflow-x-hidden" id="servicios">
<div class="landing-shell container mx-auto max-w-7xl min-w-0">
<div class="mb-8 md:mb-20 reveal reveal-left min-w-0">
<h2 class="font-headline-lg text-2xl sm:text-3xl md:text-5xl font-black text-primary mb-3 md:mb-6 tracking-tight landing-break"><?= htmlspecialchars($serviciosTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
<p class="font-body-lg text-sm sm:text-base md:text-xl text-on-surface-variant max-w-3xl leading-relaxed landing-break"><?= htmlspecialchars($serviciosSubtitulo, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8 min-w-0">
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
<div class="bg-mist rounded-xl overflow-hidden border border-brand-orange/40 hover:border-brand-orange hover:shadow-[0_0_15px_rgba(255,145,0,0.25)] transition-all group cursor-pointer lg:col-span-2 flex flex-col justify-center reveal reveal-left min-w-0 <?= $delayClass ?>">
<div class="flex flex-col md:flex-row min-w-0">
<div class="w-full md:w-1/2 h-44 sm:h-56 md:h-auto min-h-[180px] md:min-h-[220px] overflow-hidden shrink-0">
<?= $imgHtml ?>
</div>
<div class="flex-1 p-4 sm:p-5 md:p-8 min-w-0">
<h3 class="font-headline-md text-xl sm:text-2xl md:text-3xl font-bold text-on-surface mb-2 md:mb-4 landing-break"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h3>
<p class="font-body-md text-sm sm:text-base md:text-lg text-on-surface-variant mb-4 md:mb-8 landing-break leading-relaxed"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></p>
<a class="inline-flex items-center gap-2 font-label-md text-sm sm:text-base font-bold uppercase tracking-wider text-primary group-hover:text-on-surface transition-colors" href="#contacto">
                                    Explorar técnica <span class="material-symbols-outlined">arrow_forward</span>
</a>
</div>
</div>
</div>
<?php else: ?>
<div class="bg-mist rounded-xl overflow-hidden border border-sage/60 hover:border-light-cyan hover:shadow-[0_0_15px_rgba(0,62,103,0.28)] transition-all group cursor-pointer flex flex-col h-full reveal reveal-left min-w-0 <?= $delayClass ?>">
<div class="h-40 sm:h-48 w-full overflow-hidden shrink-0">
<?= $imgHtml ?>
</div>
<div class="p-4 sm:p-5 md:p-8 flex flex-col flex-grow min-w-0">
<h3 class="font-headline-md text-lg sm:text-xl md:text-2xl font-bold text-on-surface mb-2 md:mb-4 landing-break"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h3>
<p class="font-body-md text-sm sm:text-base md:text-lg text-on-surface-variant mb-4 md:mb-8 flex-grow landing-break leading-relaxed"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></p>
<a class="inline-flex items-center gap-2 font-label-md text-sm sm:text-base font-bold uppercase tracking-wider text-cyan-deep group-hover:text-light-cyan transition-colors mt-auto" href="#contacto">
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
<section class="py-12 sm:py-16 md:py-32 px-4 sm:px-6 md:px-12 bg-mist border-t border-sage/40 flex items-center justify-center w-full overflow-x-hidden" id="contacto">
<div class="landing-shell container mx-auto max-w-5xl min-w-0 w-full">
<div class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-start min-w-0">
<div class="reveal reveal-left min-w-0">
<h2 class="font-headline-lg text-2xl sm:text-3xl md:text-5xl font-black text-primary mb-3 md:mb-6 tracking-tight landing-break"><?= htmlspecialchars($contactoTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
<p class="font-body-lg text-sm sm:text-base md:text-xl text-on-surface-variant mb-6 md:mb-12 leading-relaxed landing-break"><?= htmlspecialchars($contactoSubtitulo, ENT_QUOTES, 'UTF-8') ?></p>
<div class="space-y-5 md:space-y-8">
<div class="flex items-start gap-3 md:gap-6 min-w-0">
<div class="w-11 h-11 md:w-16 md:h-16 rounded-full border-2 border-light-cyan bg-light-cyan/20 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-cyan-deep text-xl md:text-2xl">location_on</span>
</div>
<div class="min-w-0">
<h4 class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant mb-1.5">Dirección</h4>
<p class="font-body-md text-sm sm:text-base md:text-xl text-on-surface font-bold landing-break leading-relaxed">
<a href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="hover:text-cyan-deep transition-colors underline-offset-2 hover:underline"><?= cms_nl2br_safe($ubicacionDireccion) ?></a>
</p>
<p class="mt-2">
<button type="button" data-ubicacion-modal class="font-label-md text-xs sm:text-sm font-bold uppercase tracking-wider text-cyan-deep hover:text-light-cyan transition-colors inline-flex items-center gap-1.5">
<span class="material-symbols-outlined text-base">map</span>
Ver en el mapa
</button>
</p>
</div>
</div>
<div class="flex items-start gap-3 md:gap-6 min-w-0">
<div class="w-11 h-11 md:w-16 md:h-16 rounded-full border-2 border-brand-orange bg-brand-yellow/30 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-surface text-xl md:text-2xl">call</span>
</div>
<div class="min-w-0">
<h4 class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant mb-1.5">Línea Directa</h4>
<p class="font-body-md text-lg sm:text-xl md:text-2xl text-cyan-deep font-black"><a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></a></p>
</div>
</div>
<div class="flex items-start gap-3 md:gap-6 min-w-0">
<div class="w-11 h-11 md:w-16 md:h-16 rounded-full border-2 border-mint bg-mint/20 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-teal text-xl md:text-2xl">schedule</span>
</div>
<div class="min-w-0">
<h4 class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant mb-1.5">Horarios de Atención</h4>
<p class="font-body-md text-sm sm:text-lg text-on-surface font-bold landing-break leading-relaxed"><?= cms_nl2br_safe($ubicacionHorarios) ?></p>
</div>
</div>
</div>
</div>
<div class="bg-mist p-4 sm:p-8 md:p-10 rounded-xl border border-light-cyan/40 shadow-[0_0_20px_rgba(0,62,103,0.12)] reveal reveal-right reveal-delay-1 min-w-0 w-full max-w-full">
<form id="cita-form" class="grid grid-cols-1 gap-5 md:gap-8 min-w-0" method="post" action="api/cita.php" novalidate>
<div class="space-y-2 sm:space-y-3 min-w-0">
<label class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant" for="name">Nombre Completo</label>
<input class="w-full max-w-full box-border bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3.5 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base" id="name" name="nombre" placeholder="Ej: Juan Pérez" type="text" required autocomplete="name"/>
</div>
<div class="space-y-2 sm:space-y-3 min-w-0">
<label class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant" for="phone">Teléfono / Celular</label>
<input class="w-full max-w-full box-border bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3.5 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base" id="phone" name="telefono" placeholder="Ej: 70688374" type="tel" required autocomplete="tel"/>
</div>
<div class="space-y-2 sm:space-y-3 min-w-0">
<label class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant" for="service">Motivo de Consulta</label>
<select class="w-full max-w-full box-border bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3.5 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base appearance-none" id="service" name="servicio" required>
<option value="">Selecciona un servicio...</option>
<?php foreach ($serviciosForm as $opt): ?>
<option value="<?= htmlspecialchars((string) $opt['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $opt['titulo'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="space-y-2 sm:space-y-3 min-w-0">
<label class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant" for="message">Mensaje Adicional</label>
<textarea class="w-full max-w-full box-border bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3.5 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base min-h-[110px]" id="message" name="mensaje" placeholder="Detalles de la cita..."></textarea>
</div>
<button id="cita-submit" class="bg-brand-yellow hover:bg-brand-orange text-on-secondary-fixed hover:text-white px-5 sm:px-8 py-4 sm:py-5 rounded-lg font-label-md text-sm sm:text-lg font-bold uppercase tracking-wider transition-colors w-full flex items-center justify-center gap-2 sm:gap-3 mt-2 disabled:opacity-60" type="submit">
                        <span id="cita-submit-label">Solicitar Cita Ahora</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
</button>
<p id="cita-form-msg" class="hidden font-label-md text-sm text-center landing-break" role="status" aria-live="polite"></p>
</form>
</div>
</div>
</div>
</section>
<!-- Location Map Section -->
<section id="ubicacion" class="h-[48vh] min-h-[320px] sm:min-h-[360px] md:h-[60vh] md:min-h-[500px] w-full max-w-full border-t border-sage/40 relative bg-mist flex items-center justify-center overflow-hidden">
<div class="absolute inset-0 bg-cover bg-center grayscale" style="background-image: url('<?= htmlspecialchars($mapImage, ENT_QUOTES, 'UTF-8') ?>')"></div>
<div class="relative z-10 bg-mist/95 backdrop-blur-md p-5 sm:p-8 rounded-xl border border-light-cyan/40 w-[calc(100%-2rem)] max-w-sm mx-auto text-center shadow-[0_0_20px_rgba(0,62,103,0.14)] reveal reveal-up min-w-0">
<div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full border-2 border-light-cyan bg-light-cyan/20 flex items-center justify-center mb-4 sm:mb-6">
<span class="material-symbols-outlined text-cyan-deep text-3xl sm:text-4xl">pin_drop</span>
</div>
<h3 class="font-headline-md text-2xl sm:text-3xl font-bold text-on-surface mb-2 landing-break"><?= htmlspecialchars($ubicacionClinica, ENT_QUOTES, 'UTF-8') ?></h3>
<p class="font-body-md text-base sm:text-lg text-on-surface-variant mb-6 sm:mb-8 landing-break"><?= htmlspecialchars($ubicacionZona, ENT_QUOTES, 'UTF-8') ?></p>
<div class="flex flex-col gap-2.5">
<button type="button" data-ubicacion-modal class="w-full border-2 border-brand-orange text-on-surface px-4 sm:px-6 py-3.5 sm:py-4 rounded-lg font-label-md text-sm sm:text-base font-bold uppercase tracking-wider hover:bg-brand-yellow transition-colors inline-flex justify-center items-center gap-2 sm:gap-3">
<span class="material-symbols-outlined">map</span>
                    Ver ubicación
                </button>
<a href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="w-full border-2 border-light-cyan text-cyan-deep px-4 sm:px-6 py-3 rounded-lg font-label-md text-xs sm:text-sm font-bold uppercase tracking-wider hover:bg-light-cyan/20 transition-colors inline-flex justify-center items-center gap-2">
<span class="material-symbols-outlined text-xl">directions</span>
                    Abrir en Google Maps
                </a>
</div>
</div>
</section>
</main>

<!-- Modal ubicación + Google Maps -->
<div id="ubicacion-modal" class="ubicacion-modal fixed inset-0 z-[80] hidden items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="ubicacion-modal-title" hidden>
  <button type="button" class="ubicacion-modal-backdrop absolute inset-0 bg-on-surface/50 backdrop-blur-sm" data-ubicacion-close aria-label="Cerrar"></button>
  <div class="ubicacion-modal-panel relative z-10 w-full sm:max-w-2xl max-h-[92vh] sm:max-h-[90vh] overflow-y-auto bg-mist rounded-t-2xl sm:rounded-2xl border border-light-cyan/40 shadow-[0_20px_60px_rgba(0,62,103,0.25)]">
    <div class="sticky top-0 z-10 flex items-center justify-between gap-3 px-4 sm:px-6 py-3.5 border-b border-sage/50 bg-mist/95 backdrop-blur-md">
      <h2 id="ubicacion-modal-title" class="font-headline-md text-lg sm:text-xl font-bold text-on-surface landing-break min-w-0">Nuestra ubicación</h2>
      <button type="button" data-ubicacion-close class="w-11 h-11 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-light-cyan/20 hover:text-cyan-deep transition-colors shrink-0" aria-label="Cerrar">
        <span class="material-symbols-outlined text-2xl">close</span>
      </button>
    </div>
    <div class="p-4 sm:p-6 space-y-4">
      <div class="space-y-1.5 min-w-0">
        <p class="font-headline-md text-xl font-bold text-cyan-deep landing-break"><?= htmlspecialchars($ubicacionClinica, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="font-body-md text-sm sm:text-base text-on-surface-variant landing-break"><?= htmlspecialchars($ubicacionZona, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="font-body-md text-sm sm:text-base text-on-surface font-semibold landing-break leading-relaxed">
          <a href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="hover:text-cyan-deep underline-offset-2 hover:underline"><?= cms_nl2br_safe($ubicacionDireccion) ?></a>
        </p>
        <p class="font-body-md text-sm text-on-surface-variant landing-break pt-1"><?= cms_nl2br_safe($ubicacionHorarios) ?></p>
      </div>
      <div class="relative w-full aspect-[4/3] sm:aspect-video rounded-xl overflow-hidden border border-sage/50 bg-surface-container">
        <iframe
          id="ubicacion-map-iframe"
          class="absolute inset-0 w-full h-full border-0"
          title="Mapa de ubicación MiDoc"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          allowfullscreen
          data-src="<?= htmlspecialchars($mapsEmbed, ENT_QUOTES, 'UTF-8') ?>"
        ></iframe>
      </div>
      <a href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="w-full bg-brand-yellow text-on-secondary-fixed px-5 py-3.5 rounded-lg font-label-md text-sm font-bold uppercase tracking-wider hover:bg-brand-orange hover:text-white transition-colors inline-flex justify-center items-center gap-2">
        <span class="material-symbols-outlined">directions</span>
        Cómo llegar en Google Maps
      </a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
