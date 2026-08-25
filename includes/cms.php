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
        'config.telefono' => ['seccion' => 'config', 'tipo' => 'texto', 'etiqueta' => 'Teléfono principal', 'valor' => '70688374'],
        'config.email' => ['seccion' => 'config', 'tipo' => 'texto', 'etiqueta' => 'Email de contacto', 'valor' => 'info@clinicamidoc.com'],
        'seo.titulo' => ['seccion' => 'seo', 'tipo' => 'texto', 'etiqueta' => 'Título SEO (Google)', 'valor' => 'MiDoc - Cirugía General y Pediátrica | Dr. Hernán Caihuara'],
        'seo.descripcion' => ['seccion' => 'seo', 'tipo' => 'texto', 'etiqueta' => 'Descripción SEO (Google)', 'valor' => 'Clínica MiDoc. Dr. Hernán Caihuara Salas, Cirujano General Adultos y Cirujano Pediatra en Clínica Saint Jude. Agenda tu cita.'],
        'seo.keywords' => ['seccion' => 'seo', 'tipo' => 'texto', 'etiqueta' => 'Palabras clave SEO', 'valor' => 'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano, laparoscópica'],
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
        'servicios.titulo' => ['seccion' => 'servicios', 'tipo' => 'texto', 'etiqueta' => 'Título sección servicios', 'valor' => 'Cirugía General'],
        'servicios.subtitulo' => ['seccion' => 'servicios', 'tipo' => 'texto', 'etiqueta' => 'Subtítulo servicios', 'valor' => 'Procedimientos quirúrgicos especializados con enfoque en la rápida recuperación.'],
        'contacto.titulo' => ['seccion' => 'contacto', 'tipo' => 'texto', 'etiqueta' => 'Título formulario', 'valor' => 'Agenda tu Cita Médica'],
        'contacto.subtitulo' => ['seccion' => 'contacto', 'tipo' => 'texto', 'etiqueta' => 'Subtítulo formulario', 'valor' => 'Déjanos tus datos y nos pondremos en contacto contigo a la brevedad.'],
        'ubicacion.clinica' => ['seccion' => 'ubicacion', 'tipo' => 'texto', 'etiqueta' => 'Nombre clínica', 'valor' => 'Clínica Saint Jude'],
        'ubicacion.zona' => ['seccion' => 'ubicacion', 'tipo' => 'texto', 'etiqueta' => 'Zona', 'valor' => 'Zona San Clemente'],
        'ubicacion.direccion' => ['seccion' => 'ubicacion', 'tipo' => 'texto', 'etiqueta' => 'Dirección completa', 'valor' => "Piso 1, Plaza El Radialista N° 102,\nZona San Clemente"],
        'ubicacion.horarios' => ['seccion' => 'ubicacion', 'tipo' => 'texto', 'etiqueta' => 'Horarios de atención', 'valor' => "Lunes a Viernes: 8:00 AM - 6:00 PM\nSábados: Previa Cita"],
        'ubicacion.maps_url' => ['seccion' => 'ubicacion', 'tipo' => 'url', 'etiqueta' => 'Link Google Maps (abrir en app/web)', 'valor' => 'https://maps.app.goo.gl/SeunT1EjTJ2AG4Nn9'],
        'ubicacion.maps_embed' => ['seccion' => 'ubicacion', 'tipo' => 'url', 'etiqueta' => 'URL embed del mapa (iframe)', 'valor' => 'https://www.google.com/maps?q=Plaza+El+Radialista+102,+Zona+San+Clemente&output=embed'],
        'ubicacion.mapa_imagen' => ['seccion' => 'ubicacion', 'tipo' => 'imagen', 'etiqueta' => 'Imagen de fondo (sección mapa)', 'valor' => $map],
        'footer.texto' => ['seccion' => 'footer', 'tipo' => 'texto', 'etiqueta' => 'Texto del footer', 'valor' => 'Atención médica de excelencia por el Dr. Hernán Caihuara Salas.'],
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
    } catch (Throwable $e) {
        // Landing sigue con fallbacks locales
    }
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
             ORDER BY FIELD(seccion, \'config\',\'seo\',\'hero\',\'especialista\',\'servicios\',\'contacto\',\'ubicacion\',\'footer\',\'theme\'), clave'
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
 * @return list<array<string,mixed>>
 */
function servicios_list(bool $includeInactive = false, bool $forLanding = false): array
{
    try {
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
 * @param array{titulo:string,descripcion:string,icono?:string,imagen_url?:?string,orden?:int,activo?:int,destacado?:int,slug?:string} $data
 */
function servicio_create(array $data): int
{
    $slug = servicio_unique_slug($data['slug'] ?? $data['titulo']);
    $stmt = db()->prepare(
        'INSERT INTO servicios (slug, titulo, descripcion, icono, imagen_url, orden, activo, destacado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $slug,
        $data['titulo'],
        $data['descripcion'],
        $data['icono'] ?? 'medical_services',
        $data['imagen_url'] ?? null,
        (int) ($data['orden'] ?? 0),
        (int) ($data['activo'] ?? 1),
        (int) ($data['destacado'] ?? 0),
    ]);
    return (int) db()->lastInsertId();
}

/**
 * @param array{titulo?:string,descripcion?:string,icono?:string,imagen_url?:?string,orden?:int,activo?:int,destacado?:int,slug?:string} $data
 */
function servicio_update(int $id, array $data): void
{
    $current = servicio_find($id);
    if ($current === null) {
        throw new RuntimeException('Servicio no encontrado.');
    }

    $titulo = $data['titulo'] ?? $current['titulo'];
    $slugIn = $data['slug'] ?? $current['slug'];
    $slug = servicio_unique_slug((string) $slugIn, $id);

    $stmt = db()->prepare(
        'UPDATE servicios SET slug=?, titulo=?, descripcion=?, icono=?, imagen_url=?, orden=?, activo=?, destacado=?
         WHERE id=? AND deleted_at IS NULL'
    );
    $stmt->execute([
        $slug,
        $titulo,
        $data['descripcion'] ?? $current['descripcion'],
        $data['icono'] ?? $current['icono'],
        array_key_exists('imagen_url', $data) ? $data['imagen_url'] : $current['imagen_url'],
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
