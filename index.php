<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/cms.php';
require_once __DIR__ . '/includes/theme.php';
require_once __DIR__ . '/includes/seo.php';

cms_ensure_defaults();

$pageTitle = cms_get('seo.titulo', 'MiDoc | Cirugía General y Pediátrica — Dr. Hernán Caihuara');
$pageDescription = cms_get(
    'seo.descripcion',
    'Clínica MiDoc en Saint Jude. Dr. Hernán Caihuara Salas: cirugía general, pediátrica y laparoscópica. Atención profesional. Agenda tu cita al 70688374.'
);
$pageKeywords = cms_get(
    'seo.keywords',
    'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano Bolivia, laparoscópica, hernia, apendicitis'
);
$siteName = cms_get('config.sitio_nombre', 'MiDoc');
$canonicalUrl = seo_home_url();
$ogImage = seo_absolute_url(cms_get('hero.imagen', brand_logo_src()));
$pageRobots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

$seoJsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'MedicalClinic',
            '@id' => seo_home_url() . '#clinic',
            'name' => $siteName,
            'alternateName' => 'Clínica MiDoc — Dr. Hernán Caihuara',
            'url' => seo_home_url(),
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
        ],
        [
            '@type' => 'WebSite',
            '@id' => seo_home_url() . '#website',
            'name' => $siteName,
            'url' => seo_home_url(),
            'description' => $pageDescription,
            'publisher' => ['@id' => seo_home_url() . '#clinic'],
            'inLanguage' => 'es-BO',
        ],
    ],
];

$basePath = '';
$phone = cms_get('config.telefono', '70688374');
$whatsappNumber = cms_get('config.whatsapp', '59170688374');
$whatsappGeneralUrl = whatsapp_chat_url($whatsappNumber, whatsapp_cita_mensaje_general());
$doctorPhoto = cms_get('especialista.foto');
$heroBgImage = cms_get('hero.fondo');
$heroSideImage = cms_get('hero.imagen');
$mapImage = cms_get('ubicacion.mapa_imagen');
$mapsUrl = cms_get('ubicacion.maps_url', 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9');
$mapsEmbed = cms_maps_embed(
    'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1700.9216896407104!2d-65.7663181!3d-19.5776747!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x93f94dc889649eaf%3A0x6e961c40ac838987!2sClinica%20Saint%20Jude!5e1!3m2!1ses!2sbo!4v1787687019456!5m2!1ses!2sbo'
);
$email = cms_get('config.email', 'midochernandigital@gmail.com');

$heroBadge = cms_get('hero.badge');
$heroTitulo = cms_get('hero.titulo');
$heroSubtitulo = cms_get('hero.subtitulo');
$statExp = cms_get('hero.stat_experiencia', '15');
$statCir = cms_get('hero.stat_cirugias', '1000');

$serviciosTitulo = cms_get('servicios.titulo');
$serviciosSubtitulo = cms_get('servicios.subtitulo');
$serviciosTituloAdultos = cms_get('servicios.titulo_adultos', 'Cirugía General Adultos');
$serviciosTituloPediatrica = cms_get('servicios.titulo_pediatrica', 'Cirugía Pediátrica');
$contactoTitulo = cms_get('contacto.titulo');
$contactoSubtitulo = cms_get('contacto.subtitulo');
$ubicacionDireccion = cms_get('ubicacion.direccion');
$ubicacionHorarios = cms_get('ubicacion.horarios');
$ubicacionClinica = cms_get('ubicacion.clinica', 'Clínica Saint Jude');
$ubicacionZona = cms_get('ubicacion.zona', 'Zona San Clemente');

$redesVisible = cms_is_truthy(cms_get('redes.visible', '1'));
$redesTitulo = cms_get('redes.titulo', 'Síguenos en nuestras redes sociales');
$redesSubtitulo = cms_get('redes.subtitulo', 'Contenido, tips de salud y novedades de MiDoc.');
$redesFacebook = cms_get('redes.facebook', 'https://www.facebook.com/share/1Yfdz5ojML/');
$redesYoutube = cms_get('redes.youtube', 'https://www.youtube.com/@midochernan');
$redesTiktok = cms_get('redes.tiktok', 'https://www.tiktok.com/@midochernan');

$videosVisible = cms_is_truthy(cms_get('videos.visible', '1'));
$videosTitulo = cms_get('videos.titulo', 'Videos y contenido');
$videosSubtitulo = cms_get('videos.subtitulo', 'Mira nuestras publicaciones y material educativo.');
$landingVideosPublic = $videosVisible ? landing_videos_list(false) : [];

$serviciosLanding = servicios_list(false, true);

// Fallback estático si no hay BD / seed
if ($serviciosLanding === []) {
    $serviciosLanding = [
        ['slug' => 'adulto-apendicitis', 'titulo' => 'Apendicitis', 'descripcion' => 'Tratamiento quirúrgico de la apendicitis aguda en adultos.', 'icono' => 'gastroenterology', 'categoria' => 'adultos', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'adulto-colecistitis-litiasica', 'titulo' => 'Colecistitis litiásica', 'descripcion' => 'Cirugía de vesícula por colecistitis litiásica.', 'icono' => 'gastroenterology', 'categoria' => 'adultos', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'adulto-hernias', 'titulo' => 'Hernias', 'descripcion' => 'Reparación de hernias abdominales e inguinales en adultos.', 'icono' => 'personal_injury', 'categoria' => 'adultos', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'adulto-trauma-toracico', 'titulo' => 'Trauma torácico', 'descripcion' => 'Atención quirúrgica de traumatismos del tórax.', 'icono' => 'pulmonology', 'categoria' => 'adultos', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'adulto-trauma-abdominal', 'titulo' => 'Trauma abdominal', 'descripcion' => 'Manejo quirúrgico del trauma abdominal.', 'icono' => 'emergency', 'categoria' => 'adultos', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'adulto-cirugia-laparoscopica', 'titulo' => 'Cirugía laparoscópica', 'descripcion' => 'Procedimientos mínimamente invasivos en adultos.', 'icono' => 'surgical', 'categoria' => 'adultos', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'adulto-ecografia-abdominal', 'titulo' => 'Ecografía abdominal', 'descripcion' => 'Evaluación diagnóstica por ecografía abdominal.', 'icono' => 'radiology', 'categoria' => 'adultos', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'adulto-endoscopia-digestiva', 'titulo' => 'Endoscopia digestiva', 'descripcion' => 'Estudios y procedimientos de endoscopia digestiva.', 'icono' => 'gastroenterology', 'categoria' => 'adultos', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'pediatrico-apendicitis', 'titulo' => 'Apendicitis', 'descripcion' => 'Cirugía de apendicitis en pacientes pediátricos.', 'icono' => 'gastroenterology', 'categoria' => 'pediatrica', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'pediatrico-colecistitis-litiasica', 'titulo' => 'Colecistitis litiásica', 'descripcion' => 'Manejo quirúrgico de colecistitis litiásica en niños.', 'icono' => 'gastroenterology', 'categoria' => 'pediatrica', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'pediatrico-hernias-labio-leporino', 'titulo' => 'Hernias, labio leporino', 'descripcion' => 'Reparación de hernias y labio leporino en pediatría.', 'icono' => 'face', 'categoria' => 'pediatrica', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'pediatrico-criptorquidia-fimosis', 'titulo' => 'Criptorquidia, fimosis', 'descripcion' => 'Cirugía pediátrica de criptorquidia y fimosis.', 'icono' => 'urology', 'categoria' => 'pediatrica', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'pediatrico-trauma-abdominal', 'titulo' => 'Trauma abdominal', 'descripcion' => 'Atención del trauma abdominal en niños.', 'icono' => 'emergency', 'categoria' => 'pediatrica', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'pediatrico-cirugia-laparoscopica', 'titulo' => 'Cirugía laparoscópica', 'descripcion' => 'Cirugía laparoscópica pediátrica.', 'icono' => 'surgical', 'categoria' => 'pediatrica', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'pediatrico-cirugia-neonatal', 'titulo' => 'Cirugía neonatal', 'descripcion' => 'Procedimientos quirúrgicos en neonatos.', 'icono' => 'crib', 'categoria' => 'pediatrica', 'imagen_url' => null, 'destacado' => 0],
        ['slug' => 'pediatrico-malformaciones', 'titulo' => 'Malformaciones', 'descripcion' => 'Corrección quirúrgica de malformaciones congénitas.', 'icono' => 'genetics', 'categoria' => 'pediatrica', 'imagen_url' => null, 'destacado' => 0],
    ];
}

$serviciosGrouped = servicios_group_by_categoria($serviciosLanding);
$citaMotivos = cita_motivos_list();
$citaMotivosGeneral = array_values(array_filter($citaMotivos, static fn ($m) => ($m['grupo'] ?? '') !== 'pediatrico'));
$citaMotivosPediatrico = array_values(array_filter($citaMotivos, static fn ($m) => ($m['grupo'] ?? '') === 'pediatrico'));

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
<a href="<?= htmlspecialchars($whatsappGeneralUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="px-5 sm:px-8 py-3.5 sm:py-4 rounded-lg font-label-md text-xs sm:text-base font-bold uppercase tracking-wide text-cyan-deep border-2 border-light-cyan bg-light-cyan/20 hover:bg-light-cyan hover:text-white transition-all flex items-center justify-center gap-2 sm:gap-3 w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="currentColor" aria-hidden="true">
                              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                            </svg>
                            WhatsApp: <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
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
<div class="mb-8 md:mb-16 reveal reveal-left min-w-0">
<h2 class="font-headline-lg text-2xl sm:text-3xl md:text-5xl font-black text-primary mb-3 md:mb-6 tracking-tight landing-break"><?= htmlspecialchars($serviciosTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
<p class="font-body-lg text-sm sm:text-base md:text-xl text-on-surface-variant max-w-3xl leading-relaxed landing-break"><?= htmlspecialchars($serviciosSubtitulo, ENT_QUOTES, 'UTF-8') ?></p>
</div>

<?php
$serviciosBloques = [
    [
        'id' => 'servicios-adultos',
        'titulo' => $serviciosTituloAdultos,
        'items' => $serviciosGrouped['adultos'],
        'icon' => 'person',
    ],
    [
        'id' => 'servicios-pediatrica',
        'titulo' => $serviciosTituloPediatrica,
        'items' => $serviciosGrouped['pediatrica'],
        'icon' => 'child_care',
    ],
];
foreach ($serviciosBloques as $bloqueIndex => $bloque):
    if ($bloque['items'] === []) {
        continue;
    }
?>
<div id="<?= htmlspecialchars($bloque['id'], ENT_QUOTES, 'UTF-8') ?>" class="mb-10 md:mb-20 last:mb-0 min-w-0 reveal reveal-left <?= $bloqueIndex === 1 ? 'reveal-delay-1' : '' ?>">
<div class="flex items-center gap-3 mb-5 md:mb-8 min-w-0">
<span class="w-11 h-11 md:w-14 md:h-14 rounded-full border-2 border-light-cyan bg-light-cyan/20 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-cyan-deep text-xl md:text-2xl"><?= htmlspecialchars($bloque['icon'], ENT_QUOTES, 'UTF-8') ?></span>
</span>
<h3 class="font-headline-md text-xl sm:text-2xl md:text-3xl font-bold text-on-surface tracking-tight landing-break"><?= htmlspecialchars($bloque['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5 min-w-0">
<?php foreach ($bloque['items'] as $svc):
    $titulo = (string) $svc['titulo'];
    $desc = (string) ($svc['descripcion'] ?? '');
    $detalle = trim((string) ($svc['detalle'] ?? ''));
    $icono = trim((string) ($svc['icono'] ?? 'medical_services')) ?: 'medical_services';
    $imagen = trim((string) ($svc['imagen_url'] ?? ''));
    $modalBody = $detalle !== '' ? $detalle : $desc;
?>
<button type="button"
        data-servicio-modal
        data-servicio-titulo="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>"
        data-servicio-desc="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?>"
        data-servicio-detalle="<?= htmlspecialchars($modalBody, ENT_QUOTES, 'UTF-8') ?>"
        data-servicio-icono="<?= htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') ?>"
        data-servicio-imagen="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>"
        class="group text-left bg-mist rounded-xl border border-sage/60 hover:border-light-cyan hover:shadow-[0_0_15px_rgba(0,62,103,0.18)] transition-all p-4 sm:p-5 min-w-0 flex gap-3 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-light-cyan">
<span class="w-10 h-10 rounded-lg bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0 group-hover:bg-light-cyan/25 transition-colors">
<span class="material-symbols-outlined text-xl"><?= htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') ?></span>
</span>
<div class="min-w-0 flex-1">
<h4 class="font-headline-md text-base sm:text-lg font-bold text-on-surface landing-break leading-snug"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h4>
<?php if ($desc !== ''): ?>
<p class="mt-1 font-body-md text-xs sm:text-sm text-on-surface-variant landing-break leading-relaxed line-clamp-3"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<span class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-cyan-deep">
Ver más <span class="material-symbols-outlined text-sm">arrow_forward</span>
</span>
</div>
</button>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
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
<h4 class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant mb-1.5">WhatsApp / Línea directa</h4>
<p class="font-body-md text-lg sm:text-xl md:text-2xl text-cyan-deep font-black">
<a href="<?= htmlspecialchars($whatsappGeneralUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="hover:underline underline-offset-2 inline-flex items-center gap-2">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-6 h-6 shrink-0 text-cyan-deep" fill="currentColor" aria-hidden="true">
<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
</svg>
<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
</a>
</p>
<p class="text-xs sm:text-sm text-on-surface-variant mt-1">Toca el número para escribirnos por WhatsApp</p>
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
<form id="cita-form"
      class="grid grid-cols-1 gap-5 md:gap-8 min-w-0"
      action="#"
      method="get"
      onsubmit="return false;"
      data-whatsapp-number="<?= htmlspecialchars(whatsapp_digits($whatsappNumber), ENT_QUOTES, 'UTF-8') ?>"
      data-whatsapp-general="<?= htmlspecialchars($whatsappGeneralUrl, ENT_QUOTES, 'UTF-8') ?>">
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
<option value="">¿Qué molestia o síntoma tienes?</option>
<?php if ($citaMotivosGeneral !== []): ?>
<optgroup label="Síntomas generales">
<?php foreach ($citaMotivosGeneral as $opt): ?>
<option value="<?= htmlspecialchars((string) $opt['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $opt['titulo'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</optgroup>
<?php endif; ?>
<?php if ($citaMotivosPediatrico !== []): ?>
<optgroup label="En niños">
<?php foreach ($citaMotivosPediatrico as $opt): ?>
<option value="<?= htmlspecialchars((string) $opt['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $opt['titulo'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</optgroup>
<?php endif; ?>
</select>
<p class="text-xs text-on-surface-variant">No necesitas saber el diagnóstico: elige la molestia que más se parezca.</p>
</div>
<div class="space-y-2 sm:space-y-3 min-w-0">
<label class="font-label-md text-xs sm:text-sm uppercase tracking-wider sm:tracking-widest text-on-surface-variant" for="message">Mensaje Adicional</label>
<textarea class="w-full max-w-full box-border bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-3.5 focus:outline-none focus:border-light-cyan focus:ring-1 focus:ring-light-cyan transition-all font-body-md text-base min-h-[110px]" id="message" name="mensaje" placeholder="Ej: me duele el estómago desde ayer..."></textarea>
</div>
<button id="cita-submit" class="bg-brand-yellow hover:bg-brand-orange text-on-secondary-fixed hover:text-white px-5 sm:px-8 py-4 sm:py-5 rounded-lg font-label-md text-sm sm:text-lg font-bold uppercase tracking-wider transition-colors w-full flex items-center justify-center gap-2 sm:gap-3 mt-2 disabled:opacity-60" type="button">
                        <span id="cita-submit-label">Solicitar cita</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0" fill="currentColor" aria-hidden="true">
                          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                        </svg>
</button>
<p id="cita-form-msg" class="hidden font-label-md text-sm text-center landing-break" role="status" aria-live="polite"></p>
<p class="text-xs sm:text-sm text-on-surface-variant text-center landing-break">Se abrirá WhatsApp con tus datos. La cita se solicita solo cuando pulses <strong>Enviar</strong> en WhatsApp.</p>
</form>
</div>
</div>
</div>
</section>

<?php if ($videosVisible && $landingVideosPublic !== []): ?>
<!-- Videos / contenido -->
<section id="videos" class="py-12 sm:py-16 md:py-24 px-4 sm:px-6 md:px-12 bg-mist border-t border-sage/40 w-full overflow-x-hidden">
<div class="landing-shell container mx-auto max-w-7xl min-w-0">
<div class="mb-8 md:mb-12 reveal reveal-left min-w-0 max-w-3xl">
<h2 class="font-headline-lg text-2xl sm:text-3xl md:text-5xl font-black text-primary mb-3 md:mb-4 tracking-tight landing-break"><?= htmlspecialchars($videosTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
<p class="font-body-lg text-sm sm:text-base md:text-xl text-on-surface-variant leading-relaxed landing-break"><?= htmlspecialchars($videosSubtitulo, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 min-w-0">
<?php foreach ($landingVideosPublic as $i => $vid): ?>
  <?php
    $vidId = (string) ($vid['youtube_id'] ?? '');
    $thumb = youtube_thumb_url($vidId);
    $embed = youtube_embed_url($vidId);
  ?>
  <button type="button"
          data-video-modal
          data-video-src="<?= htmlspecialchars($embed, ENT_QUOTES, 'UTF-8') ?>"
          data-video-title="<?= htmlspecialchars((string) $vid['titulo'], ENT_QUOTES, 'UTF-8') ?>"
          class="group text-left bg-mist rounded-xl overflow-hidden border border-sage/60 hover:border-light-cyan hover:shadow-[0_0_15px_rgba(0,62,103,0.22)] transition-all reveal reveal-up min-w-0 <?= $i % 3 === 1 ? 'reveal-delay-1' : ($i % 3 === 2 ? 'reveal-delay-2' : '') ?>">
    <div class="relative aspect-video overflow-hidden bg-surface-container">
      <img src="<?= htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"/>
      <span class="absolute inset-0 flex items-center justify-center bg-on-surface/25 group-hover:bg-on-surface/35 transition-colors">
        <span class="w-14 h-14 rounded-full border-2 border-white/80 text-white flex items-center justify-center backdrop-blur-sm bg-cyan-deep/40">
          <span class="material-symbols-outlined text-3xl" style="font-variation-settings:'FILL' 1;">play_arrow</span>
        </span>
      </span>
    </div>
    <div class="p-4 sm:p-5 min-w-0">
      <h3 class="font-headline-md text-base sm:text-lg font-bold text-on-surface landing-break"><?= htmlspecialchars((string) $vid['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
      <?php if (!empty($vid['descripcion'])): ?>
        <p class="font-body-md text-sm text-on-surface-variant mt-1.5 landing-break line-clamp-2"><?= htmlspecialchars((string) $vid['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
    </div>
  </button>
<?php endforeach; ?>
</div>
</div>
</section>
<?php endif; ?>

<?php if ($redesVisible): ?>
<!-- Redes sociales -->
<section id="redes" class="py-12 sm:py-16 md:py-20 px-4 sm:px-6 md:px-12 bg-surface-container-low border-t border-sage/40 w-full overflow-x-hidden">
<div class="landing-shell container mx-auto max-w-3xl text-center min-w-0 reveal reveal-up">
<h2 class="font-headline-lg text-2xl sm:text-3xl md:text-4xl font-black text-cyan-deep mb-3 tracking-tight landing-break"><?= htmlspecialchars($redesTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
<p class="font-body-lg text-sm sm:text-base md:text-lg text-on-surface-variant mb-8 md:mb-10 leading-relaxed landing-break"><?= htmlspecialchars($redesSubtitulo, ENT_QUOTES, 'UTF-8') ?></p>
<div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
  <?php if (trim($redesFacebook) !== ''): ?>
  <a href="<?= htmlspecialchars($redesFacebook, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook MiDoc"
     class="social-icon-btn w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 border-cyan-deep/40 text-cyan-deep bg-transparent hover:bg-cyan-deep hover:text-white hover:border-cyan-deep transition-all inline-flex items-center justify-center">
    <?= social_icon_svg('facebook', 'w-7 h-7 sm:w-8 sm:h-8') ?>
  </a>
  <?php endif; ?>
  <?php if (trim($redesYoutube) !== ''): ?>
  <a href="<?= htmlspecialchars($redesYoutube, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube MiDoc"
     class="social-icon-btn w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 border-cyan-deep/40 text-cyan-deep bg-transparent hover:bg-cyan-deep hover:text-white hover:border-cyan-deep transition-all inline-flex items-center justify-center">
    <?= social_icon_svg('youtube', 'w-7 h-7 sm:w-8 sm:h-8') ?>
  </a>
  <?php endif; ?>
  <?php if (trim($redesTiktok) !== ''): ?>
  <a href="<?= htmlspecialchars($redesTiktok, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok MiDoc"
     class="social-icon-btn w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 border-cyan-deep/40 text-cyan-deep bg-transparent hover:bg-cyan-deep hover:text-white hover:border-cyan-deep transition-all inline-flex items-center justify-center">
    <?= social_icon_svg('tiktok', 'w-7 h-7 sm:w-8 sm:h-8') ?>
  </a>
  <?php endif; ?>
</div>
</div>
</section>
<?php endif; ?>

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

<!-- Modal video YouTube -->
<div id="video-modal" class="video-modal fixed inset-0 z-[85] hidden items-center justify-center p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="video-modal-title" hidden>
  <button type="button" class="absolute inset-0 bg-on-surface/60 backdrop-blur-sm" data-video-close aria-label="Cerrar"></button>
  <div class="relative z-10 w-full max-w-4xl bg-mist rounded-2xl border border-light-cyan/40 shadow-[0_20px_60px_rgba(0,62,103,0.3)] overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 border-b border-sage/50">
      <h2 id="video-modal-title" class="font-headline-md text-base sm:text-lg font-bold text-on-surface landing-break min-w-0 truncate">Video</h2>
      <button type="button" data-video-close class="w-11 h-11 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-light-cyan/20 hover:text-cyan-deep transition-colors shrink-0" aria-label="Cerrar">
        <span class="material-symbols-outlined text-2xl">close</span>
      </button>
    </div>
    <div class="relative w-full aspect-video bg-on-surface">
      <iframe id="video-modal-iframe" class="absolute inset-0 w-full h-full border-0" title="Video de YouTube" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </div>
  </div>
</div>

<!-- Modal detalle de servicio -->
<div id="servicio-modal" class="servicio-modal fixed inset-0 z-[82] hidden items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true" aria-labelledby="servicio-modal-title" hidden>
  <button type="button" class="absolute inset-0 bg-on-surface/50 backdrop-blur-sm" data-servicio-close aria-label="Cerrar"></button>
  <div class="relative z-10 w-full sm:max-w-2xl max-h-[92vh] sm:max-h-[90vh] overflow-y-auto bg-mist rounded-t-2xl sm:rounded-2xl border border-light-cyan/40 shadow-[0_20px_60px_rgba(0,62,103,0.25)]">
    <div class="sticky top-0 z-10 flex items-center justify-between gap-3 px-4 sm:px-6 py-3.5 border-b border-sage/50 bg-mist/95 backdrop-blur-md">
      <div class="flex items-center gap-2 min-w-0">
        <span id="servicio-modal-icon-wrap" class="w-9 h-9 rounded-lg bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
          <span id="servicio-modal-icon" class="material-symbols-outlined text-xl">medical_services</span>
        </span>
        <h2 id="servicio-modal-title" class="font-headline-md text-lg sm:text-xl font-bold text-on-surface landing-break min-w-0 truncate">Servicio</h2>
      </div>
      <button type="button" data-servicio-close class="w-11 h-11 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-light-cyan/20 hover:text-cyan-deep transition-colors shrink-0" aria-label="Cerrar">
        <span class="material-symbols-outlined text-2xl">close</span>
      </button>
    </div>
    <div class="p-4 sm:p-6 space-y-4">
      <div id="servicio-modal-media" class="relative w-full aspect-video rounded-xl overflow-hidden border border-sage/50 bg-surface-container hidden">
        <img id="servicio-modal-img" src="" alt="" class="absolute inset-0 w-full h-full object-cover"/>
      </div>
      <div id="servicio-modal-media-fallback" class="w-full aspect-video rounded-xl border border-dashed border-sage/60 bg-light-cyan/10 flex flex-col items-center justify-center gap-2 text-cyan-deep">
        <span id="servicio-modal-fallback-icon" class="material-symbols-outlined text-5xl">medical_services</span>
        <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Sin imagen del servicio</span>
      </div>
      <p id="servicio-modal-desc" class="font-body-md text-sm text-on-surface-variant landing-break leading-relaxed"></p>
      <div id="servicio-modal-detalle" class="font-body-md text-sm sm:text-base text-on-surface landing-break leading-relaxed whitespace-pre-line"></div>
      <a href="#contacto" data-servicio-close class="w-full bg-brand-yellow text-on-secondary-fixed px-5 py-3.5 rounded-lg font-label-md text-sm font-bold uppercase tracking-wider hover:bg-brand-orange hover:text-white transition-colors inline-flex justify-center items-center gap-2">
        <span class="material-symbols-outlined">event_available</span>
        Solicitar cita
      </a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
