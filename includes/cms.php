<?php
/**
 * Lectura/escritura de cms_contenidos y servicios (landing).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/** @var array<string,string>|null */
$GLOBALS['_cms_cache'] = null;

/**
 * Defaults de imágenes/textos si la BD aún no tiene valor.
 *
 * @return array<string, array{seccion:string,tipo:string,etiqueta:string,valor:string}>
 */
function cms_default_defs(): array
{
    $heroBg = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAVMg_KK96LXgNH4IXadk-Z-dy-WtVQitA4H4Qp_-mo4_dVNOT45HLur0gXoM0LJ3i0IlI8YxHoOra7dZ_oH7fbSita9Jwr93lnVVqzBWLTv9jZHwqWt6fl8f1HryVxjrDRL3ThP1aFDmSWEXe7wEh5S7MvCqblaVB_h2QOU7R2tJmtGVktXPUqaH88-4dJa26ivxD3z0ajKAPddK0GR3SYD5lW4nA0TsTlTCQdpdhcsjDQpQJqMT6W';
    $heroSide = 'https://lh3.googleusercontent.com/aida/AP1WRLstwO-aXpojVZEq3QNz-jTqzQ6_1hDoe4LK6awDBgE_PQPGsCT0Nh8DZ0CuxjvVrTDplSRoKpyYEAvKz68J9bFBRCwOzBDL7OKil2EgGUb-qnmORwIqYPjfAcRSsQddbzYeYe4CKwT9auvZIK9y9aaJqFsGo5_TJJ2h5sboOA7b_i5w5PZOja1TNPzXqn4LLN8BSYIu84QSqEOjbzGGphMrZzd5GqrfpAJZoWWjvd-DosGoQYTIh_KZmsc';
    $doctor = 'https://lh3.googleusercontent.com/aida-public/AB6AXuDQ00TRNKSzW1IeH4fiOrmtUbcjbLCCGlPgfqZ_8yz3xGbzOPR_3PFPiqDHUnlcw0lT1aJHtm2aOeaVqnqMcuUWt6WGqWTzHUGEwXo9ANOEvrPZgJanwn-8iM8q_Ih2OaqvFbJXuZJg5nddpYjfMt3yjTMtfW6cyXJE4JCT9doBg-hvy-7UaDLCoPM9oQ2yg-shU3Ipn7V23KOOA03yR8lzdSodgbpiOWyvvV5CC5b0p4DwZ73pzDmB';
    $map = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBg1ToGGMqGq3xdnCJhsXVMxOceBu_gwvZ84s5Tc04DinwW6azM-guuPPDFTkypubfv_YhfHHEfYwMbLy7RKa1Rx5uqFi6LR4wFAociBNow7DarvnWo9v5QjNXNMtNUNCMDVCg5ij69JJErG1-fWNti4UQSxqJDQ_EDmfkHMSVlPwz_6ARp7UQofCMZoD8q-SuHYhGsJJs9nk1-vxK2sAZLlBApRn2meaBy7Px02nxWvnqr5EP3GCl7';

    return [
        'config.sitio_nombre' => ['seccion' => 'config', 'tipo' => 'texto', 'etiqueta' => 'Nombre del sitio', 'valor' => 'MiDoc'],
        'config.telefono' => ['seccion' => 'config', 'tipo' => 'texto', 'etiqueta' => 'Teléfono visible (landing)', 'valor' => '70688374'],
        'config.whatsapp' => ['seccion' => 'config', 'tipo' => 'texto', 'etiqueta' => 'WhatsApp citas (código país + número)', 'valor' => '59170688374'],
        'config.email' => ['seccion' => 'config', 'tipo' => 'texto', 'etiqueta' => 'Email de contacto (footer / mailto)', 'valor' => 'midochernandigital@gmail.com'],
        'seo.titulo' => ['seccion' => 'seo', 'tipo' => 'texto', 'etiqueta' => 'Título SEO (Google)', 'valor' => 'MiDoc | Cirugía General y Pediátrica — Dr. Hernán Caihuara'],
        'seo.descripcion' => ['seccion' => 'seo', 'tipo' => 'texto', 'etiqueta' => 'Descripción SEO (Google)', 'valor' => 'Clínica MiDoc en Saint Jude. Dr. Hernán Caihuara Salas: cirugía general, pediátrica y laparoscópica. Atención profesional. Agenda tu cita al 70688374.'],
        'seo.keywords' => ['seccion' => 'seo', 'tipo' => 'texto', 'etiqueta' => 'Palabras clave SEO', 'valor' => 'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano Bolivia, laparoscópica, hernia, apendicitis'],
        'hero.badge' => ['seccion' => 'hero', 'tipo' => 'texto', 'etiqueta' => 'Badge del hero', 'valor' => 'Tu Salud es Primordial'],
        'hero.titulo' => ['seccion' => 'hero', 'tipo' => 'texto', 'etiqueta' => 'Título principal', 'valor' => 'Atención médica de excelencia para ti y tu familia.'],
        'hero.subtitulo' => ['seccion' => 'hero', 'tipo' => 'texto', 'etiqueta' => 'Subtítulo hero', 'valor' => 'Dr. Hernán Caihuara Salas. Cirujano General "Adultos" y Cirujano Pediatra.'],
        'hero.fondo' => ['seccion' => 'hero', 'tipo' => 'imagen', 'etiqueta' => 'Imagen de fondo del hero', 'valor' => $heroBg],
        'hero.imagen' => ['seccion' => 'hero', 'tipo' => 'imagen', 'etiqueta' => 'Imagen lateral del hero', 'valor' => $heroSide],
        'hero.stat_experiencia' => ['seccion' => 'hero', 'tipo' => 'numero', 'etiqueta' => 'Años de experiencia', 'valor' => '15'],
        'hero.stat_cirugias' => ['seccion' => 'hero', 'tipo' => 'numero', 'etiqueta' => 'Cirugías exitosas', 'valor' => '1000'],
        'especialista.nombre' => ['seccion' => 'especialista', 'tipo' => 'texto', 'etiqueta' => 'Nombre del doctor', 'valor' => 'Dr. Hernán Caihuara Salas'],
        'especialista.rol' => ['seccion' => 'especialista', 'tipo' => 'texto', 'etiqueta' => 'Especialidad visible', 'valor' => 'Cirujano General “Adultos” y Cirujano Pediatra'],
        'especialista.foto' => ['seccion' => 'especialista', 'tipo' => 'imagen', 'etiqueta' => 'Foto del doctor (navbar)', 'valor' => $doctor],
        'servicios.titulo' => ['seccion' => 'servicios', 'tipo' => 'texto', 'etiqueta' => 'Título sección servicios', 'valor' => 'Atención en Cirugía General Adultos y Niños'],
        'servicios.subtitulo' => ['seccion' => 'servicios', 'tipo' => 'texto', 'etiqueta' => 'Subtítulo servicios', 'valor' => 'Procedimientos que ofrece el Dr. Hernán Caihuara Salas en Cirugía General Adultos y Cirugía Pediátrica.'],
        'servicios.titulo_adultos' => ['seccion' => 'servicios', 'tipo' => 'texto', 'etiqueta' => 'Título bloque adultos', 'valor' => 'Cirugía General Adultos'],
        'servicios.titulo_pediatrica' => ['seccion' => 'servicios', 'tipo' => 'texto', 'etiqueta' => 'Título bloque pediátrica', 'valor' => 'Cirugía Pediátrica'],
        'contacto.titulo' => ['seccion' => 'contacto', 'tipo' => 'texto', 'etiqueta' => 'Título formulario', 'valor' => 'Agenda tu Cita Médica'],
        'contacto.subtitulo' => ['seccion' => 'contacto', 'tipo' => 'texto', 'etiqueta' => 'Subtítulo formulario', 'valor' => 'Déjanos tus datos y nos pondremos en contacto contigo a la brevedad.'],
        'ubicacion.clinica' => ['seccion' => 'ubicacion', 'tipo' => 'texto', 'etiqueta' => 'Nombre clínica', 'valor' => 'Clínica Saint Jude'],
        'ubicacion.zona' => ['seccion' => 'ubicacion', 'tipo' => 'texto', 'etiqueta' => 'Zona', 'valor' => 'Zona San Clemente'],
        'ubicacion.direccion' => ['seccion' => 'ubicacion', 'tipo' => 'texto', 'etiqueta' => 'Dirección completa', 'valor' => "Piso 1, Plaza El Radialista N° 102,\nZona San Clemente"],
        'ubicacion.horarios' => ['seccion' => 'ubicacion', 'tipo' => 'texto', 'etiqueta' => 'Horarios de atención', 'valor' => "Lunes a Viernes: 8:00 AM - 6:00 PM\nSábados: Previa Cita"],
        'ubicacion.maps_url' => ['seccion' => 'ubicacion', 'tipo' => 'url', 'etiqueta' => 'Link Google Maps (abrir en app/web)', 'valor' => 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9'],
        'ubicacion.maps_embed' => ['seccion' => 'ubicacion', 'tipo' => 'url', 'etiqueta' => 'Mapa embed (URL o pegar el iframe de Google Maps)', 'valor' => 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1700.9216896407104!2d-65.7663181!3d-19.5776747!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x93f94dc889649eaf%3A0x6e961c40ac838987!2sClinica%20Saint%20Jude!5e1!3m2!1ses!2sbo!4v1787687019456!5m2!1ses!2sbo'],
        'ubicacion.mapa_imagen' => ['seccion' => 'ubicacion', 'tipo' => 'imagen', 'etiqueta' => 'Imagen de fondo (sección mapa)', 'valor' => $map],
        'footer.texto' => ['seccion' => 'footer', 'tipo' => 'texto', 'etiqueta' => 'Texto del footer', 'valor' => 'Atención médica de excelencia por el Dr. Hernán Caihuara Salas.'],
        'redes.titulo' => ['seccion' => 'redes', 'tipo' => 'texto', 'etiqueta' => 'Título sección redes', 'valor' => 'Síguenos en nuestras redes sociales'],
        'redes.subtitulo' => ['seccion' => 'redes', 'tipo' => 'texto', 'etiqueta' => 'Subtítulo redes', 'valor' => 'Contenido, tips de salud y novedades de MiDoc.'],
        'redes.visible' => ['seccion' => 'redes', 'tipo' => 'numero', 'etiqueta' => 'Mostrar sección redes (1=sí, 0=no)', 'valor' => '1'],
        'redes.facebook' => ['seccion' => 'redes', 'tipo' => 'url', 'etiqueta' => 'Facebook (URL)', 'valor' => 'https://www.facebook.com/share/1Yfdz5ojML/'],
        'redes.youtube' => ['seccion' => 'redes', 'tipo' => 'url', 'etiqueta' => 'YouTube (URL)', 'valor' => 'https://www.youtube.com/@midochernan'],
        'redes.tiktok' => ['seccion' => 'redes', 'tipo' => 'url', 'etiqueta' => 'TikTok (URL)', 'valor' => 'https://www.tiktok.com/@midochernan'],
        'videos.titulo' => ['seccion' => 'videos', 'tipo' => 'texto', 'etiqueta' => 'Título sección videos', 'valor' => 'Videos y contenido'],
        'videos.subtitulo' => ['seccion' => 'videos', 'tipo' => 'texto', 'etiqueta' => 'Subtítulo videos', 'valor' => 'Mira nuestras publicaciones y material educativo.'],
        'videos.visible' => ['seccion' => 'videos', 'tipo' => 'numero', 'etiqueta' => 'Mostrar sección videos (1=sí, 0=no)', 'valor' => '1'],
        'theme.cyan' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Cian / azul principal', 'valor' => '#003E67'],
        'theme.yellow' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Amarillo (botones CTA)', 'valor' => '#F9D423'],
        'theme.orange' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Naranja (acento)', 'valor' => '#FF9100'],
        'theme.teal' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Teal / verde primario', 'valor' => '#169A8A'],
        'theme.mint' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Menta', 'valor' => '#3DE2AD'],
        'theme.sage' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Sage (bordes)', 'valor' => '#B2D9C2'],
        'theme.mist' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Fondo (mist)', 'valor' => '#D9EEE1'],
        'theme.text' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Texto principal', 'valor' => '#14352F'],
        'theme.text_muted' => ['seccion' => 'theme', 'tipo' => 'color', 'etiqueta' => 'Texto secundario', 'valor' => '#3A5F56'],
    ];
}

function cms_ensure_defaults(?int $userId = null): void
{
    try {
        $pdo = db();
        $exists = $pdo->prepare('SELECT 1 FROM cms_contenidos WHERE clave = ? LIMIT 1');
        $ins = $pdo->prepare(
            'INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach (cms_default_defs() as $clave => $def) {
            $exists->execute([$clave]);
            if ($exists->fetchColumn()) {
                continue;
            }
            $ins->execute([$def['seccion'], $clave, $def['tipo'], $def['valor'], $def['etiqueta'], $userId]);
        }

        // Migración suave: enlace Maps corto oficial (solo si aún está el seed antiguo de búsqueda)
        $pdo->exec(
            "UPDATE cms_contenidos
             SET valor = 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9',
                 etiqueta = 'Link Google Maps (abrir en app/web)',
                 updated_at = CURRENT_TIMESTAMP
             WHERE clave = 'ubicacion.maps_url'
               AND (
                 valor LIKE '%google.com/maps/search%'
                 OR valor = ''
                 OR valor IS NULL
               )"
        );
        $pdo->exec(
            "UPDATE sedes
             SET maps_url = 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9'
             WHERE es_principal = 1
               AND (
                 maps_url LIKE '%google.com/maps/search%'
                 OR maps_url = ''
                 OR maps_url IS NULL
               )"
        );

        // SEO: mejora título/descripción si aún están los textos cortos/antiguos de seed
        $pdo->exec(
            "UPDATE cms_contenidos
             SET valor = 'MiDoc | Cirugía General y Pediátrica — Dr. Hernán Caihuara',
                 etiqueta = 'Título SEO (Google)',
                 updated_at = CURRENT_TIMESTAMP
             WHERE clave = 'seo.titulo'
               AND valor IN (
                 'MiDoc - Salud Primordial',
                 'MiDoc - Cirugía General y Pediátrica | Dr. Hernán Caihuara',
                 ''
               )"
        );
        $pdo->exec(
            "UPDATE cms_contenidos
             SET valor = 'Clínica MiDoc en Saint Jude. Dr. Hernán Caihuara Salas: cirugía general, pediátrica y laparoscópica. Atención profesional. Agenda tu cita al 70688374.',
                 etiqueta = 'Descripción SEO (Google)',
                 updated_at = CURRENT_TIMESTAMP
             WHERE clave = 'seo.descripcion'
               AND (
                 valor = ''
                 OR valor IS NULL
                 OR valor LIKE 'Clínica MiDoc. Dr. Hernán Caihuara Salas, Cirujano General Adultos%'
                 OR valor LIKE 'Dr. Hernán Caihuara Salas. Cirujano General%'
               )"
        );

        // Título sección servicios: migrar nombres antiguos
        $pdo->exec(
            "UPDATE cms_contenidos
             SET valor = 'Atención en Cirugía General Adultos y Niños',
                 etiqueta = 'Título sección servicios',
                 updated_at = CURRENT_TIMESTAMP
             WHERE clave = 'servicios.titulo'
               AND valor IN (
                 'Cirugía General',
                 'Cirugía general',
                 'Servicios disponibles en Cirugía General Adultos y Niños',
                 ''
               )"
        );
        $pdo->exec(
            "UPDATE cms_contenidos
             SET valor = 'Procedimientos que ofrece el Dr. Hernán Caihuara Salas en Cirugía General Adultos y Cirugía Pediátrica.',
                 updated_at = CURRENT_TIMESTAMP
             WHERE clave = 'servicios.subtitulo'
               AND (
                 valor = ''
                 OR valor IS NULL
                 OR valor LIKE 'Procedimientos quirúrgicos especializados%'
               )"
        );

        // Email de contacto (footer)
        $pdo->exec(
            "UPDATE cms_contenidos
             SET valor = 'midochernandigital@gmail.com',
                 etiqueta = 'Email de contacto (footer / mailto)',
                 updated_at = CURRENT_TIMESTAMP
             WHERE clave = 'config.email'
               AND valor IN ('info@clinicamidoc.com', '')"
        );

        // Si pegaron un <iframe> completo en maps_embed, guardar solo la URL src
        $embedRow = $pdo->query(
            "SELECT id, valor FROM cms_contenidos WHERE clave = 'ubicacion.maps_embed' LIMIT 1"
        )->fetch(PDO::FETCH_ASSOC);
        if (is_array($embedRow)) {
            $rawEmbed = (string) ($embedRow['valor'] ?? '');
            $normEmbed = cms_normalize_maps_embed($rawEmbed);
            if ($normEmbed !== '' && $normEmbed !== $rawEmbed) {
                $updEmbed = $pdo->prepare(
                    "UPDATE cms_contenidos
                     SET valor = ?, etiqueta = 'Mapa embed (URL o pegar el iframe de Google Maps)', updated_at = CURRENT_TIMESTAMP
                     WHERE id = ?"
                );
                $updEmbed->execute([$normEmbed, (int) $embedRow['id']]);
            } elseif ($rawEmbed === '' || str_contains($rawEmbed, 'Plaza+El+Radialista')) {
                $pdo->prepare(
                    "UPDATE cms_contenidos
                     SET valor = ?, etiqueta = 'Mapa embed (URL o pegar el iframe de Google Maps)', updated_at = CURRENT_TIMESTAMP
                     WHERE id = ?"
                )->execute([
                    cms_default_defs()['ubicacion.maps_embed']['valor'],
                    (int) $embedRow['id'],
                ]);
            }
        }

        landing_videos_ensure_table($pdo);
        servicios_ensure_catalog($pdo);
    } catch (Throwable $e) {
        // Landing sigue con fallbacks locales
    }
}

/**
 * Acepta URL de embed o el HTML completo del iframe de Google Maps.
 */
function cms_normalize_maps_embed(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }

    if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $raw, $m)) {
        $raw = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    $raw = trim($raw, " \t\n\r\0\x0B\"'");
    if (!preg_match('#^https?://#i', $raw)) {
        return '';
    }

    return $raw;
}

function cms_maps_embed(?string $fallback = null): string
{
    $default = cms_default_defs()['ubicacion.maps_embed']['valor'] ?? '';
    $raw = cms_get('ubicacion.maps_embed', $fallback ?? $default);
    $norm = cms_normalize_maps_embed($raw);
    return $norm !== '' ? $norm : ($fallback ?? $default);
}

/**
 * @return array<string,string>
 */
function cms_all(): array
{
    if (is_array($GLOBALS['_cms_cache'])) {
        return $GLOBALS['_cms_cache'];
    }

    $map = [];
    foreach (cms_default_defs() as $clave => $def) {
        $map[$clave] = $def['valor'];
    }

    try {
        $rows = db()->query('SELECT clave, valor FROM cms_contenidos')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $val = trim((string) ($row['valor'] ?? ''));
            if ($val !== '') {
                $map[(string) $row['clave']] = $val;
            }
        }
    } catch (Throwable $e) {
        // keep defaults
    }

    $GLOBALS['_cms_cache'] = $map;
    return $map;
}

function cms_get(string $clave, ?string $fallback = null): string
{
    $all = cms_all();
    if (isset($all[$clave]) && $all[$clave] !== '') {
        return $all[$clave];
    }
    if ($fallback !== null) {
        return $fallback;
    }
    return cms_default_defs()[$clave]['valor'] ?? '';
}

function cms_set(string $clave, string $valor, ?int $userId = null): void
{
    if ($clave === 'ubicacion.maps_embed') {
        $norm = cms_normalize_maps_embed($valor);
        if ($norm !== '') {
            $valor = $norm;
        }
    }

    $defs = cms_default_defs();
    $seccion = $defs[$clave]['seccion'] ?? explode('.', $clave)[0];
    $tipo = $defs[$clave]['tipo'] ?? 'texto';
    $etiqueta = $defs[$clave]['etiqueta'] ?? $clave;

    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO cms_contenidos (seccion, clave, tipo, valor, etiqueta, updated_by)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE valor = VALUES(valor), updated_by = VALUES(updated_by), updated_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute([$seccion, $clave, $tipo, $valor, $etiqueta, $userId]);
    $GLOBALS['_cms_cache'] = null;
}

/**
 * @return list<array<string,mixed>>
 */
function cms_rows_grouped(): array
{
    cms_ensure_defaults();
    $known = cms_default_defs();
    try {
        $rows = db()->query(
            'SELECT id, seccion, clave, tipo, valor, etiqueta
             FROM cms_contenidos
             ORDER BY FIELD(seccion, \'config\',\'seo\',\'hero\',\'especialista\',\'servicios\',\'contacto\',\'ubicacion\',\'redes\',\'videos\',\'footer\',\'theme\'), clave'
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }

    $grouped = [];
    foreach ($rows as $row) {
        $clave = (string) ($row['clave'] ?? '');
        if ($clave === '' || !isset($known[$clave])) {
            continue;
        }
        $grouped[$row['seccion']][] = $row;
    }
    return $grouped;
}

function cms_format_hero_title(string $title): string
{
    $safe = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $replaced = preg_replace(
        '/\b(excelencia)\b/iu',
        '<span class="text-light-cyan drop-shadow-[0_0_8px_rgba(0,62,103,0.35)]">$1</span>',
        $safe
    );
    return $replaced ?? $safe;
}

function cms_nl2br_safe(string $text): string
{
    return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'), false);
}

function servicios_slugify(string $text): string
{
    $t = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $t = strtolower((string) $t);
    $t = preg_replace('/[^a-z0-9]+/', '-', $t) ?? '';
    $t = trim($t, '-');
    return $t !== '' ? $t : 'servicio-' . bin2hex(random_bytes(3));
}

/**
 * Catálogo oficial Adultos / Pediátrica.
 *
 * @return list<array{slug:string,titulo:string,descripcion:string,icono:string,categoria:string,orden:int}>
 */
function servicios_seed_catalog(): array
{
    return [
        ['slug' => 'adulto-apendicitis', 'titulo' => 'Apendicitis', 'descripcion' => 'Tratamiento quirúrgico de la apendicitis aguda en adultos.', 'icono' => 'gastroenterology', 'categoria' => 'adultos', 'orden' => 10],
        ['slug' => 'adulto-colecistitis-litiasica', 'titulo' => 'Colecistitis litiásica', 'descripcion' => 'Cirugía de vesícula por colecistitis litiásica.', 'icono' => 'gastroenterology', 'categoria' => 'adultos', 'orden' => 20],
        ['slug' => 'adulto-hernias', 'titulo' => 'Hernias', 'descripcion' => 'Reparación de hernias abdominales e inguinales en adultos.', 'icono' => 'personal_injury', 'categoria' => 'adultos', 'orden' => 30],
        ['slug' => 'adulto-trauma-toracico', 'titulo' => 'Trauma torácico', 'descripcion' => 'Atención quirúrgica de traumatismos del tórax.', 'icono' => 'pulmonology', 'categoria' => 'adultos', 'orden' => 40],
        ['slug' => 'adulto-trauma-abdominal', 'titulo' => 'Trauma abdominal', 'descripcion' => 'Manejo quirúrgico del trauma abdominal.', 'icono' => 'emergency', 'categoria' => 'adultos', 'orden' => 50],
        ['slug' => 'adulto-cirugia-laparoscopica', 'titulo' => 'Cirugía laparoscópica', 'descripcion' => 'Procedimientos mínimamente invasivos en adultos.', 'icono' => 'surgical', 'categoria' => 'adultos', 'orden' => 60],
        ['slug' => 'adulto-ecografia-abdominal', 'titulo' => 'Ecografía abdominal', 'descripcion' => 'Evaluación diagnóstica por ecografía abdominal.', 'icono' => 'radiology', 'categoria' => 'adultos', 'orden' => 70],
        ['slug' => 'adulto-endoscopia-digestiva', 'titulo' => 'Endoscopia digestiva', 'descripcion' => 'Estudios y procedimientos de endoscopia digestiva.', 'icono' => 'gastroenterology', 'categoria' => 'adultos', 'orden' => 80],
        ['slug' => 'pediatrico-apendicitis', 'titulo' => 'Apendicitis', 'descripcion' => 'Cirugía de apendicitis en pacientes pediátricos.', 'icono' => 'gastroenterology', 'categoria' => 'pediatrica', 'orden' => 110],
        ['slug' => 'pediatrico-colecistitis-litiasica', 'titulo' => 'Colecistitis litiásica', 'descripcion' => 'Manejo quirúrgico de colecistitis litiásica en niños.', 'icono' => 'gastroenterology', 'categoria' => 'pediatrica', 'orden' => 120],
        ['slug' => 'pediatrico-hernias-labio-leporino', 'titulo' => 'Hernias, labio leporino', 'descripcion' => 'Reparación de hernias y labio leporino en pediatría.', 'icono' => 'face', 'categoria' => 'pediatrica', 'orden' => 130],
        ['slug' => 'pediatrico-criptorquidia-fimosis', 'titulo' => 'Criptorquidia, fimosis', 'descripcion' => 'Cirugía pediátrica de criptorquidia y fimosis.', 'icono' => 'urology', 'categoria' => 'pediatrica', 'orden' => 140],
        ['slug' => 'pediatrico-trauma-abdominal', 'titulo' => 'Trauma abdominal', 'descripcion' => 'Atención del trauma abdominal en niños.', 'icono' => 'emergency', 'categoria' => 'pediatrica', 'orden' => 150],
        ['slug' => 'pediatrico-cirugia-laparoscopica', 'titulo' => 'Cirugía laparoscópica', 'descripcion' => 'Cirugía laparoscópica pediátrica.', 'icono' => 'surgical', 'categoria' => 'pediatrica', 'orden' => 160],
        ['slug' => 'pediatrico-cirugia-neonatal', 'titulo' => 'Cirugía neonatal', 'descripcion' => 'Procedimientos quirúrgicos en neonatos.', 'icono' => 'crib', 'categoria' => 'pediatrica', 'orden' => 170],
        ['slug' => 'pediatrico-malformaciones', 'titulo' => 'Malformaciones', 'descripcion' => 'Corrección quirúrgica de malformaciones congénitas.', 'icono' => 'genetics', 'categoria' => 'pediatrica', 'orden' => 180],
        ['slug' => 'otro', 'titulo' => 'Consulta General / Otro', 'descripcion' => 'Consulta general u otro motivo no listado en el catálogo.', 'icono' => 'stethoscope', 'categoria' => 'adultos', 'orden' => 999],
    ];
}

function servicios_ensure_schema(?PDO $pdo = null): void
{
    $pdo = $pdo ?? db();
    $cols = [];
    try {
        foreach ($pdo->query('SHOW COLUMNS FROM servicios')->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $cols[(string) ($row['Field'] ?? '')] = true;
        }
    } catch (Throwable $e) {
        return;
    }

    if (!isset($cols['categoria'])) {
        try {
            $pdo->exec(
                "ALTER TABLE servicios
                 ADD COLUMN categoria VARCHAR(40) NOT NULL DEFAULT 'adultos' AFTER imagen_url,
                 ADD KEY idx_servicios_categoria (categoria)"
            );
        } catch (Throwable $e) {
            // ya existe
        }
    }

    if (!isset($cols['detalle'])) {
        try {
            $pdo->exec(
                "ALTER TABLE servicios
                 ADD COLUMN detalle TEXT NULL AFTER descripcion"
            );
        } catch (Throwable $e) {
            // ya existe
        }
    }
}

/**
 * Iconos Material Symbols usados / recomendados para servicios.
 *
 * @return list<string>
 */
function servicios_icon_palette(): array
{
    $base = [
        'medical_services',
        'gastroenterology',
        'personal_injury',
        'pulmonology',
        'emergency',
        'surgical',
        'radiology',
        'face',
        'urology',
        'crib',
        'genetics',
        'stethoscope',
        'healing',
        'monitor_heart',
        'cardiology',
        'vaccines',
        'local_hospital',
        'health_and_safety',
        'syringe',
        'ent',
        'dermatology',
        'pediatrics',
        'child_care',
        'pregnant_woman',
        'accessibility_new',
        'psychology',
        'bloodtype',
        'vital_signs',
        'ultrasound',
        'biotech',
    ];

    try {
        $rows = db()->query(
            "SELECT DISTINCT icono FROM servicios
             WHERE deleted_at IS NULL AND icono IS NOT NULL AND icono <> ''
             ORDER BY icono ASC"
        )->fetchAll(PDO::FETCH_COLUMN);
        foreach ($rows as $icon) {
            $icon = trim((string) $icon);
            if ($icon !== '') {
                $base[] = $icon;
            }
        }
    } catch (Throwable $e) {
        // sin BD
    }

    $unique = [];
    foreach ($base as $icon) {
        $unique[$icon] = true;
    }
    return array_keys($unique);
}

/**
 * Asegura columna categoria + inserta el catálogo si faltan filas.
 * Desactiva el listado antiguo plano (apendicitis, hernias, etc.).
 */
function servicios_ensure_catalog(?PDO $pdo = null): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $pdo = $pdo ?? db();
    servicios_ensure_schema($pdo);

    $exists = $pdo->prepare('SELECT id FROM servicios WHERE slug = ? LIMIT 1');
    $ins = $pdo->prepare(
        'INSERT INTO servicios (slug, titulo, descripcion, icono, imagen_url, categoria, orden, activo, destacado)
         VALUES (?, ?, ?, ?, NULL, ?, ?, 1, 0)'
    );
    $updIcon = $pdo->prepare(
        'UPDATE servicios SET icono = ? WHERE slug = ? AND deleted_at IS NULL'
    );

    foreach (servicios_seed_catalog() as $seed) {
        $exists->execute([$seed['slug']]);
        if ($exists->fetchColumn()) {
            // Mantener iconos médicos del catálogo alineados
            $updIcon->execute([$seed['icono'], $seed['slug']]);
            continue;
        }
        $ins->execute([
            $seed['slug'],
            $seed['titulo'],
            $seed['descripcion'],
            $seed['icono'],
            $seed['categoria'],
            $seed['orden'],
        ]);
    }

    // Ocultar catálogo plano antiguo si ya está el nuevo
    $hasNew = $pdo->query(
        "SELECT 1 FROM servicios WHERE slug = 'adulto-apendicitis' AND deleted_at IS NULL LIMIT 1"
    )->fetchColumn();
    if ($hasNew) {
        $pdo->exec(
            "UPDATE servicios
             SET activo = 0
             WHERE deleted_at IS NULL
               AND slug IN ('apendicitis', 'colecistitis', 'hernias', 'laparoscopica', 'toracica')"
        );
    }

    $done = true;
}

/**
 * @param list<array<string,mixed>> $list
 * @return array{adultos:list<array<string,mixed>>,pediatrica:list<array<string,mixed>>,otro:list<array<string,mixed>>}
 */
function servicios_group_by_categoria(array $list): array
{
    $out = ['adultos' => [], 'pediatrica' => [], 'otro' => []];
    foreach ($list as $svc) {
        $slug = (string) ($svc['slug'] ?? '');
        if ($slug === 'otro') {
            $out['otro'][] = $svc;
            continue;
        }
        $cat = (string) ($svc['categoria'] ?? 'adultos');
        if ($cat !== 'pediatrica') {
            $cat = 'adultos';
        }
        $out[$cat][] = $svc;
    }
    return $out;
}

/**
 * @return list<array<string,mixed>>
 */
function servicios_list(bool $includeInactive = false, bool $forLanding = false): array
{
    try {
        servicios_ensure_catalog();
        $sql = 'SELECT * FROM servicios WHERE deleted_at IS NULL';
        if (!$includeInactive) {
            $sql .= ' AND activo = 1';
        }
        if ($forLanding) {
            $sql .= " AND slug <> 'otro'";
        }
        $sql .= ' ORDER BY orden ASC, id ASC';
        return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * @return array<string,mixed>|null
 */
function servicio_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM servicios WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function servicio_unique_slug(string $base, ?int $ignoreId = null): string
{
    $slug = servicios_slugify($base);
    $i = 0;
    while (true) {
        $try = $i === 0 ? $slug : $slug . '-' . $i;
        $sql = 'SELECT id FROM servicios WHERE slug = ? AND deleted_at IS NULL';
        $params = [$try];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        $stmt = db()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        if (!$stmt->fetchColumn()) {
            return $try;
        }
        $i++;
    }
}

/**
 * @param array{titulo:string,descripcion:string,detalle?:?string,icono?:string,imagen_url?:?string,orden?:int,activo?:int,destacado?:int,slug?:string,categoria?:string} $data
 */
function servicio_create(array $data): int
{
    servicios_ensure_schema();
    $slug = servicio_unique_slug($data['slug'] ?? $data['titulo']);
    $categoria = ($data['categoria'] ?? 'adultos') === 'pediatrica' ? 'pediatrica' : 'adultos';
    $stmt = db()->prepare(
        'INSERT INTO servicios (slug, titulo, descripcion, detalle, icono, imagen_url, categoria, orden, activo, destacado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $slug,
        $data['titulo'],
        $data['descripcion'],
        $data['detalle'] ?? null,
        $data['icono'] ?? 'medical_services',
        $data['imagen_url'] ?? null,
        $categoria,
        (int) ($data['orden'] ?? 0),
        (int) ($data['activo'] ?? 1),
        (int) ($data['destacado'] ?? 0),
    ]);
    return (int) db()->lastInsertId();
}

/**
 * @param array{titulo?:string,descripcion?:string,detalle?:?string,icono?:string,imagen_url?:?string,orden?:int,activo?:int,destacado?:int,slug?:string,categoria?:string} $data
 */
function servicio_update(int $id, array $data): void
{
    servicios_ensure_schema();
    $current = servicio_find($id);
    if ($current === null) {
        throw new RuntimeException('Servicio no encontrado.');
    }

    $titulo = $data['titulo'] ?? $current['titulo'];
    $slugIn = $data['slug'] ?? $current['slug'];
    $slug = servicio_unique_slug((string) $slugIn, $id);
    $categoriaIn = $data['categoria'] ?? ($current['categoria'] ?? 'adultos');
    $categoria = $categoriaIn === 'pediatrica' ? 'pediatrica' : 'adultos';

    $stmt = db()->prepare(
        'UPDATE servicios SET slug=?, titulo=?, descripcion=?, detalle=?, icono=?, imagen_url=?, categoria=?, orden=?, activo=?, destacado=?
         WHERE id=? AND deleted_at IS NULL'
    );
    $stmt->execute([
        $slug,
        $titulo,
        $data['descripcion'] ?? $current['descripcion'],
        array_key_exists('detalle', $data) ? $data['detalle'] : ($current['detalle'] ?? null),
        $data['icono'] ?? $current['icono'],
        array_key_exists('imagen_url', $data) ? $data['imagen_url'] : $current['imagen_url'],
        $categoria,
        (int) ($data['orden'] ?? $current['orden']),
        (int) ($data['activo'] ?? $current['activo']),
        (int) ($data['destacado'] ?? $current['destacado']),
        $id,
    ]);
}

function servicio_soft_delete(int $id): void
{
    $stmt = db()->prepare(
        'UPDATE servicios SET deleted_at = NOW(), activo = 0 WHERE id = ? AND deleted_at IS NULL'
    );
    $stmt->execute([$id]);
}

/**
 * Catálogo especialidades (personal / referencia).
 *
 * @return list<array<string,mixed>>
 */
function especialidades_list(bool $includeInactive = true): array
{
    try {
        $sql = 'SELECT * FROM especialidades';
        if (!$includeInactive) {
            $sql .= ' WHERE activo = 1';
        }
        $sql .= ' ORDER BY nombre ASC';
        return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * @return array<string,mixed>|null
 */
function especialidad_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM especialidades WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function especialidad_unique_slug(string $base, ?int $ignoreId = null): string
{
    $slug = servicios_slugify($base);
    $i = 0;
    while (true) {
        $try = $i === 0 ? $slug : $slug . '-' . $i;
        $sql = 'SELECT id FROM especialidades WHERE slug = ?';
        $params = [$try];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        $stmt = db()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        if (!$stmt->fetchColumn()) {
            return $try;
        }
        $i++;
    }
}

/**
 * @param array{nombre:string,descripcion?:?string,activo?:int,slug?:string} $data
 */
function especialidad_create(array $data): int
{
    $slug = especialidad_unique_slug($data['slug'] ?? $data['nombre']);
    $stmt = db()->prepare(
        'INSERT INTO especialidades (slug, nombre, descripcion, activo) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
        $slug,
        $data['nombre'],
        $data['descripcion'] ?? null,
        (int) ($data['activo'] ?? 1),
    ]);
    return (int) db()->lastInsertId();
}

/**
 * @param array{nombre?:string,descripcion?:?string,activo?:int,slug?:string} $data
 */
function especialidad_update(int $id, array $data): void
{
    $current = especialidad_find($id);
    if ($current === null) {
        throw new RuntimeException('Especialidad no encontrada.');
    }
    $nombre = $data['nombre'] ?? $current['nombre'];
    $slug = especialidad_unique_slug((string) ($data['slug'] ?? $current['slug']), $id);
    $stmt = db()->prepare(
        'UPDATE especialidades SET slug=?, nombre=?, descripcion=?, activo=? WHERE id=?'
    );
    $stmt->execute([
        $slug,
        $nombre,
        array_key_exists('descripcion', $data) ? $data['descripcion'] : $current['descripcion'],
        (int) ($data['activo'] ?? $current['activo']),
        $id,
    ]);
}

function especialidad_delete(int $id): void
{
    // Si está en uso por personal, solo desactivar
    $used = db()->prepare('SELECT COUNT(*) FROM personal WHERE especialidad_id = ? AND deleted_at IS NULL');
    $used->execute([$id]);
    if ((int) $used->fetchColumn() > 0) {
        $stmt = db()->prepare('UPDATE especialidades SET activo = 0 WHERE id = ?');
        $stmt->execute([$id]);
        return;
    }
    $stmt = db()->prepare('DELETE FROM especialidades WHERE id = ?');
    $stmt->execute([$id]);
}

function cms_is_truthy(string $value): bool
{
    $v = strtolower(trim($value));
    return in_array($v, ['1', 'true', 'si', 'sí', 'yes', 'on'], true);
}

/**
 * Normaliza teléfono a dígitos para wa.me (Bolivia: agrega 591 si son 8 dígitos).
 */
function whatsapp_digits(string $raw): string
{
    $d = preg_replace('/\D+/', '', $raw) ?? '';
    if ($d === '') {
        return '59170688374';
    }
    // Quitar 00 internacional
    if (str_starts_with($d, '00')) {
        $d = substr($d, 2);
    }
    // Celular BO típico 7/6xxxxxxx (8 dígitos)
    if (strlen($d) === 8 && ($d[0] === '6' || $d[0] === '7')) {
        $d = '591' . $d;
    }
    return $d;
}

function whatsapp_chat_url(string $phoneOrDigits, string $message = ''): string
{
    $url = 'https://wa.me/' . whatsapp_digits($phoneOrDigits);
    if (trim($message) !== '') {
        $url .= '?text=' . rawurlencode($message);
    }
    return $url;
}

function whatsapp_cita_mensaje_general(): string
{
    return "Hola, quiero hacer una reserva de cita médica en *MiDoc*.";
}

/**
 * Motivos del formulario (dolencias generales, no nombres de cirugías).
 *
 * @return list<array{slug:string,titulo:string,grupo:string}>
 */
function cita_motivos_list(): array
{
    return [
        ['slug' => 'dolor-estomago-abdomen', 'titulo' => 'Dolor de estómago o abdomen', 'grupo' => 'general'],
        ['slug' => 'dolor-lado-abdomen', 'titulo' => 'Dolor fuerte en un lado del abdomen', 'grupo' => 'general'],
        ['slug' => 'nauseas-vomitos-dolor', 'titulo' => 'Náuseas o vómitos con dolor abdominal', 'grupo' => 'general'],
        ['slug' => 'hinchazon-bulto', 'titulo' => 'Hinchazón o bulto en abdomen o ingle', 'grupo' => 'general'],
        ['slug' => 'dolor-pecho-torax', 'titulo' => 'Dolor, golpe o molestia en el pecho / tórax', 'grupo' => 'general'],
        ['slug' => 'problemas-digestivos', 'titulo' => 'Problemas digestivos (digestión, tragar, etc.)', 'grupo' => 'general'],
        ['slug' => 'trauma-abdomen-pecho', 'titulo' => 'Golpe o trauma en abdomen o pecho', 'grupo' => 'general'],
        ['slug' => 'molestia-genital', 'titulo' => 'Molestia o inflamación en zona genital', 'grupo' => 'general'],
        ['slug' => 'dolor-estomago-nino', 'titulo' => 'Dolor de estómago en niño/a', 'grupo' => 'pediatrico'],
        ['slug' => 'bulto-hernia-nino', 'titulo' => 'Hinchazón o bulto en niño/a', 'grupo' => 'pediatrico'],
        ['slug' => 'problema-prepucio-testiculo', 'titulo' => 'Problema en prepucio o testículos (niño)', 'grupo' => 'pediatrico'],
        ['slug' => 'malformacion-congenita', 'titulo' => 'Malformación o anomalía de nacimiento', 'grupo' => 'pediatrico'],
        ['slug' => 'trauma-nino', 'titulo' => 'Golpe o trauma en niño/a', 'grupo' => 'pediatrico'],
        ['slug' => 'no-se-diagnostico', 'titulo' => 'No sé qué tengo / quiero una evaluación', 'grupo' => 'general'],
        ['slug' => 'otro', 'titulo' => 'Otro (lo detallo en el mensaje)', 'grupo' => 'general'],
    ];
}

/** @return list<string> */
function cita_motivos_slugs(): array
{
    return array_column(cita_motivos_list(), 'slug');
}

/**
 * @param array{nombre:string,telefono:string,servicio:string,servicio_label?:string,mensaje?:string} $data
 */
function whatsapp_cita_mensaje_formulario(array $data): string
{
    $nombre = trim((string) ($data['nombre'] ?? ''));
    $telefono = trim((string) ($data['telefono'] ?? ''));
    $servicio = trim((string) ($data['servicio_label'] ?? $data['servicio'] ?? ''));
    $mensaje = trim((string) ($data['mensaje'] ?? ''));

    $lines = [
        '*Solicitud de cita médica — MiDoc*',
        '',
        'Hola, deseo reservar una cita. Estos son mis datos:',
        '',
        '• *Nombre:* ' . ($nombre !== '' ? $nombre : '—'),
        '• *Teléfono de contacto:* ' . ($telefono !== '' ? $telefono : '—'),
        '• *Motivo / dolencia:* ' . ($servicio !== '' ? $servicio : '—'),
    ];
    if ($mensaje !== '') {
        $lines[] = '• *Mensaje adicional:* ' . $mensaje;
    }
    $lines[] = '';
    $lines[] = 'Quedo atento(a) a su confirmación. Gracias.';

    return implode("\n", $lines);
}

function youtube_id_from_url(string $url): ?string
{
    $url = trim($url);
    if ($url === '') {
        return null;
    }
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
        return $url;
    }
    if (preg_match('~(?:youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/)|youtu\.be/)([a-zA-Z0-9_-]{11})~i', $url, $m)) {
        return $m[1];
    }
    if (preg_match('~[?&]v=([a-zA-Z0-9_-]{11})~', $url, $m)) {
        return $m[1];
    }
    return null;
}

function youtube_embed_url(string $videoId): string
{
    return 'https://www.youtube.com/embed/' . rawurlencode($videoId) . '?autoplay=1&rel=0';
}

function youtube_thumb_url(string $videoId): string
{
    return 'https://img.youtube.com/vi/' . rawurlencode($videoId) . '/hqdefault.jpg';
}

function landing_videos_ensure_table(?PDO $pdo = null): void
{
    $pdo = $pdo ?? db();
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS landing_videos (
          id INT UNSIGNED NOT NULL AUTO_INCREMENT,
          titulo VARCHAR(180) NOT NULL,
          descripcion TEXT NULL,
          youtube_url VARCHAR(500) NOT NULL,
          youtube_id VARCHAR(20) NOT NULL,
          orden INT NOT NULL DEFAULT 0,
          activo TINYINT(1) NOT NULL DEFAULT 1,
          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id),
          KEY idx_landing_videos_activo_orden (activo, orden, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

/**
 * @return list<array<string,mixed>>
 */
function landing_videos_list(bool $includeInactive = false): array
{
    try {
        landing_videos_ensure_table();
        $sql = 'SELECT * FROM landing_videos';
        if (!$includeInactive) {
            $sql .= ' WHERE activo = 1';
        }
        $sql .= ' ORDER BY orden ASC, id DESC';
        return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * @return array<string,mixed>|null
 */
function landing_video_find(int $id): ?array
{
    landing_videos_ensure_table();
    $stmt = db()->prepare('SELECT * FROM landing_videos WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * @param array{titulo:string,descripcion?:?string,youtube_url:string,orden?:int,activo?:int} $data
 */
function landing_video_create(array $data): int
{
    landing_videos_ensure_table();
    $ytId = youtube_id_from_url((string) $data['youtube_url']);
    if ($ytId === null) {
        throw new RuntimeException('URL de YouTube no válida. Usa un enlace de video (watch, youtu.be o shorts).');
    }
    $stmt = db()->prepare(
        'INSERT INTO landing_videos (titulo, descripcion, youtube_url, youtube_id, orden, activo)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['titulo'],
        $data['descripcion'] ?? null,
        trim((string) $data['youtube_url']),
        $ytId,
        (int) ($data['orden'] ?? 0),
        (int) ($data['activo'] ?? 1),
    ]);
    return (int) db()->lastInsertId();
}

/**
 * @param array{titulo?:string,descripcion?:?string,youtube_url?:string,orden?:int,activo?:int} $data
 */
function landing_video_update(int $id, array $data): void
{
    landing_videos_ensure_table();
    $current = landing_video_find($id);
    if ($current === null) {
        throw new RuntimeException('Video no encontrado.');
    }
    $url = (string) ($data['youtube_url'] ?? $current['youtube_url']);
    $ytId = youtube_id_from_url($url);
    if ($ytId === null) {
        throw new RuntimeException('URL de YouTube no válida.');
    }
    $stmt = db()->prepare(
        'UPDATE landing_videos
         SET titulo=?, descripcion=?, youtube_url=?, youtube_id=?, orden=?, activo=?
         WHERE id=?'
    );
    $stmt->execute([
        $data['titulo'] ?? $current['titulo'],
        array_key_exists('descripcion', $data) ? $data['descripcion'] : $current['descripcion'],
        trim($url),
        $ytId,
        (int) ($data['orden'] ?? $current['orden']),
        (int) ($data['activo'] ?? $current['activo']),
        $id,
    ]);
}

function landing_video_delete(int $id): void
{
    landing_videos_ensure_table();
    $stmt = db()->prepare('DELETE FROM landing_videos WHERE id = ?');
    $stmt->execute([$id]);
}

/**
 * Icono social SVG con currentColor (adapta a colores del tema).
 */
function social_icon_svg(string $network, string $class = 'w-7 h-7'): string
{
    $class = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
    $common = 'fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="' . $class . '" aria-hidden="true"';
    return match ($network) {
        'facebook' => '<svg viewBox="0 0 24 24" ' . $common . '><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" ' . $common . '><path d="M22.5 8.2a3 3 0 0 0-2.1-2.1C18.7 5.5 12 5.5 12 5.5s-6.7 0-8.4.6A3 3 0 0 0 1.5 8.2 31.5 31.5 0 0 0 1 12a31.5 31.5 0 0 0 .5 3.8 3 3 0 0 0 2.1 2.1c1.7.6 8.4.6 8.4.6s6.7 0 8.4-.6a3 3 0 0 0 2.1-2.1A31.5 31.5 0 0 0 23 12a31.5 31.5 0 0 0-.5-3.8z"/><path d="m10 15.2 5.5-3.2L10 8.8v6.4z" fill="currentColor" stroke="none"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" ' . $common . '><path d="M14.5 4c.4 2.2 1.8 3.8 4 4.2v2.6c-1.4-.1-2.7-.6-3.8-1.4v5.8a5.8 5.8 0 1 1-5.8-5.8c.3 0 .6 0 .9.1v2.8a3 3 0 1 0 2.1 2.9V4h2.6z"/></svg>',
        default => '<svg viewBox="0 0 24 24" ' . $common . '><circle cx="12" cy="12" r="8"/></svg>',
    };
}
