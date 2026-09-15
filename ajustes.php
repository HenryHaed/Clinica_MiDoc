<?php
/**
 * Ajustes del sistema — CMS landing + servicios.
 * Roles: admin, super_admin (capabilities cms | config).
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cms.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/theme.php';

auth_start();

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: login.php');
    exit;
}

auth_require(...AUTH_PANEL_ROLES);

if (!auth_can('cms') && !auth_can('config')) {
    http_response_code(403);
    echo 'No tienes permiso para Ajustes del sistema.';
    exit;
}

$user = auth_user();
assert($user !== null);
$userId = (int) $user['id'];

cms_ensure_defaults($userId);

$allowedTabs = ['contenido', 'colores', 'imagenes', 'videos', 'servicios'];
$tab = (string) ($_GET['tab'] ?? 'contenido');
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'contenido';
}

$flashOk = null;
$flashErr = null;

function ajustes_redirect(string $tab, string $msg, bool $ok = true): never
{
    $q = http_build_query([
        'tab' => $tab,
        $ok ? 'ok' : 'err' => $msg,
    ]);
    header('Location: ajustes.php?' . $q);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_csrf_validate($_POST['_csrf'] ?? null)) {
        ajustes_redirect($tab, 'Token CSRF inválido. Recarga la página.', false);
    }

    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'save_cms_text') {
            $keys = $_POST['cms'] ?? [];
            $seccion = trim((string) ($_POST['seccion'] ?? ''));
            if (!is_array($keys)) {
                throw new RuntimeException('Datos inválidos.');
            }
            if ($seccion === '' || !preg_match('/^[a-z0-9_]+$/i', $seccion)) {
                throw new RuntimeException('Sección no válida.');
            }
            $defs = cms_default_defs();
            $saved = 0;
            foreach ($keys as $clave => $valor) {
                $clave = (string) $clave;
                if (!preg_match('/^[a-z0-9_.]+$/i', $clave)) {
                    continue;
                }
                if (($defs[$clave]['seccion'] ?? '') !== $seccion) {
                    continue;
                }
                if (($defs[$clave]['tipo'] ?? '') === 'imagen') {
                    continue;
                }
                if (($defs[$clave]['tipo'] ?? '') === 'color') {
                    continue;
                }
                cms_set($clave, trim((string) $valor), $userId);
                $saved++;
            }
            if ($saved === 0) {
                throw new RuntimeException('No hay campos para guardar en esta sección.');
            }
            $labels = [
                'config' => 'Configuración general',
                'seo' => 'SEO Google',
                'hero' => 'Hero (portada)',
                'especialista' => 'Especialista',
                'servicios' => 'Sección servicios',
                'contacto' => 'Contacto / citas',
                'ubicacion' => 'Ubicación / Google Maps',
                'redes' => 'Redes sociales',
                'videos' => 'Videos / contenido',
                'footer' => 'Footer',
            ];
            ajustes_redirect('contenido', 'Guardado: ' . ($labels[$seccion] ?? $seccion) . '.');
        }

        if ($action === 'save_theme_colors') {
            $keys = $_POST['cms'] ?? [];
            if (!is_array($keys)) {
                throw new RuntimeException('Datos de color inválidos.');
            }
            foreach (theme_editable_defs() as $def) {
                $clave = $def['clave'];
                $raw = trim((string) ($keys[$clave] ?? ''));
                $norm = theme_normalize_hex($raw);
                if ($norm === null) {
                    throw new RuntimeException('Color inválido en «' . $def['etiqueta'] . '». Usa formato #RRGGBB.');
                }
                cms_set($clave, $norm, $userId);
            }
            ajustes_redirect('colores', 'Colores guardados. Recarga la landing (Ctrl+F5) para verlos.');
        }

        if ($action === 'reset_theme_colors') {
            foreach (theme_editable_defs() as $def) {
                cms_set($def['clave'], $def['valor'], $userId);
            }
            ajustes_redirect('colores', 'Paleta restaurada a los colores originales de MiDoc.');
        }

        if ($action === 'save_cms_image') {
            $clave = (string) ($_POST['clave'] ?? '');
            $defs = cms_default_defs();
            if (!isset($defs[$clave]) || ($defs[$clave]['tipo'] ?? '') !== 'imagen') {
                throw new RuntimeException('Clave de imagen no válida.');
            }

            if (empty($_FILES['archivo']['name'])) {
                throw new RuntimeException('Elige o arrastra una imagen para actualizar.');
            }

            $up = upload_image($_FILES['archivo'], 'cms');
            if (!$up['ok']) {
                throw new RuntimeException($up['error'] ?? 'Error de subida.');
            }
            $newPath = $up['path'];

            cms_set($clave, $newPath, $userId);
            ajustes_redirect('imagenes', 'Imagen actualizada: ' . ($defs[$clave]['etiqueta'] ?? $clave));
        }

        if ($action === 'servicio_save') {
            $id = (int) ($_POST['id'] ?? 0);
            $titulo = trim((string) ($_POST['titulo'] ?? ''));
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
            $detalle = trim((string) ($_POST['detalle'] ?? ''));
            $icono = trim((string) ($_POST['icono'] ?? 'medical_services')) ?: 'medical_services';
            if (!preg_match('/^[a-z0-9_]+$/i', $icono)) {
                $icono = 'medical_services';
            }
            $orden = (int) ($_POST['orden'] ?? 0);
            $activo = isset($_POST['activo']) ? 1 : 0;
            $destacado = isset($_POST['destacado']) ? 1 : 0;
            $slug = trim((string) ($_POST['slug'] ?? ''));
            $categoria = trim((string) ($_POST['categoria'] ?? 'adultos')) === 'pediatrica' ? 'pediatrica' : 'adultos';

            if ($titulo === '' || $descripcion === '') {
                throw new RuntimeException('Título y descripción corta son obligatorios.');
            }

            $imagenUrl = null;
            $keepImage = true;
            if (!empty($_FILES['imagen']['name'])) {
                $up = upload_image($_FILES['imagen'], 'servicio');
                if (!$up['ok']) {
                    throw new RuntimeException($up['error'] ?? 'Error de subida.');
                }
                $imagenUrl = $up['path'];
                $keepImage = false;
            }

            $payload = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'detalle' => $detalle !== '' ? $detalle : null,
                'icono' => $icono,
                'orden' => $orden,
                'activo' => $activo,
                'destacado' => $destacado,
                'categoria' => $categoria,
            ];
            if ($slug !== '') {
                $payload['slug'] = $slug;
            }
            if (!$keepImage) {
                $payload['imagen_url'] = $imagenUrl;
            }

            if ($id > 0) {
                servicio_update($id, $payload);
                ajustes_redirect('servicios', 'Servicio actualizado.');
            }
            servicio_create($payload);
            ajustes_redirect('servicios', 'Servicio creado.');
        }

        if ($action === 'servicio_delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('ID inválido.');
            }
            servicio_soft_delete($id);
            ajustes_redirect('servicios', 'Servicio eliminado (oculto en la landing).');
        }

        if ($action === 'video_save') {
            $id = (int) ($_POST['id'] ?? 0);
            $titulo = trim((string) ($_POST['titulo'] ?? ''));
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
            $youtubeUrl = trim((string) ($_POST['youtube_url'] ?? ''));
            $orden = (int) ($_POST['orden'] ?? 0);
            $activo = isset($_POST['activo']) ? 1 : 0;
            if ($titulo === '' || $youtubeUrl === '') {
                throw new RuntimeException('Título y URL de YouTube son obligatorios.');
            }
            $payload = [
                'titulo' => $titulo,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'youtube_url' => $youtubeUrl,
                'orden' => $orden,
                'activo' => $activo,
            ];
            if ($id > 0) {
                landing_video_update($id, $payload);
                ajustes_redirect('videos', 'Video actualizado.');
            }
            landing_video_create($payload);
            ajustes_redirect('videos', 'Video publicado en la landing.');
        }

        if ($action === 'video_delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('ID inválido.');
            }
            landing_video_delete($id);
            ajustes_redirect('videos', 'Video eliminado.');
        }

        throw new RuntimeException('Acción no reconocida.');
    } catch (Throwable $e) {
        ajustes_redirect($tab, $e->getMessage(), false);
    }
}

if (isset($_GET['ok'])) {
    $flashOk = (string) $_GET['ok'];
}
if (isset($_GET['err'])) {
    $flashErr = (string) $_GET['err'];
}

$csrf = auth_csrf_token();
$cmsGrouped = cms_rows_grouped();

/** Medidas recomendadas según uso actual en la landing */
$imageAspectHints = [
    'hero.fondo' => [
        'ratio' => '16:9',
        'detail' => 'Horizontal · fondo a pantalla completa (ideal ≈ 1920×1080 px)',
    ],
    'hero.imagen' => [
        'ratio' => '1:1',
        'detail' => 'Cuadrada · foto lateral del hero (ideal ≈ 800×800 px o más)',
    ],
    'especialista.foto' => [
        'ratio' => '1:1',
        'detail' => 'Cuadrada · avatar circular del doctor en el navbar (ideal ≈ 400×400 px)',
    ],
    'ubicacion.mapa_imagen' => [
        'ratio' => '16:9',
        'detail' => 'Horizontal · fondo de la sección ubicación (ideal ≈ 1600×900 px)',
    ],
];

$imageKeys = [];
foreach (cms_default_defs() as $clave => $def) {
    if ($def['tipo'] === 'imagen') {
        $hint = $imageAspectHints[$clave] ?? [
            'ratio' => '—',
            'detail' => 'Cualquier proporción razonable; se recorta con object-cover',
        ];
        $imageKeys[$clave] = [
            'etiqueta' => $def['etiqueta'],
            'valor' => cms_get($clave),
            'seccion' => $def['seccion'],
            'ratio' => $hint['ratio'],
            'ratio_detail' => $hint['detail'],
        ];
    }
}

$servicios = servicios_list(true, false);
$serviciosActivosAdmin = array_values(array_filter(
    $servicios,
    static fn (array $s): bool => !empty($s['activo'])
));
$serviciosInactivosAdmin = array_values(array_filter(
    $servicios,
    static fn (array $s): bool => empty($s['activo'])
));
$serviciosGroupedAdmin = servicios_group_by_categoria($serviciosActivosAdmin);
$editServicio = null;
if (isset($_GET['edit_servicio'])) {
    $editServicio = servicio_find((int) $_GET['edit_servicio']);
}

$landingVideos = landing_videos_list(true);
$editVideo = null;
if (isset($_GET['edit_video'])) {
    $editVideo = landing_video_find((int) $_GET['edit_video']);
}

$sectionLabels = [
    'config' => 'Configuración general',
    'seo' => 'SEO Google',
    'hero' => 'Hero (portada)',
    'especialista' => 'Especialista',
    'servicios' => 'Sección servicios',
    'contacto' => 'Contacto / citas',
    'ubicacion' => 'Ubicación / Google Maps',
    'redes' => 'Redes sociales',
    'videos' => 'Videos / contenido',
    'footer' => 'Footer',
    'theme' => 'Colores del sitio',
];

$navActive = 'ajustes';
$headerTitle = 'Ajustes';
$pageTitleAdmin = 'MiDoc - Ajustes del sistema';
require __DIR__ . '/includes/admin_chrome_start.php';
?>

<?php if ($flashOk): ?>
<div class="flex items-start gap-3 rounded-xl border border-primary/25 bg-primary-container/20 text-on-surface px-4 py-3">
  <span class="material-symbols-outlined text-primary mt-0.5">check_circle</span>
  <p class="font-body-md flex-1 pt-0.5"><?= htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<?php endif; ?>
<?php if ($flashErr): ?>
<div class="flex items-start gap-3 rounded-xl border border-error/30 bg-error-container text-on-error-container px-4 py-3">
  <span class="material-symbols-outlined mt-0.5">error</span>
  <p class="font-body-md flex-1 pt-0.5"><?= htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<?php endif; ?>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
  <div>
    <p class="font-label-caps text-label-caps text-on-surface-variant mb-1">PANEL</p>
    <h3 class="font-headline-md text-headline-md font-bold text-on-background">Contenido del sitio</h3>
    <p class="font-body-md text-on-surface-variant mt-1 max-w-2xl">Edita textos, imágenes, videos y servicios de la landing.</p>
  </div>
  <div class="inline-flex items-center gap-2 rounded-xl border border-light-cyan/30 bg-mist px-3 py-2 text-sm text-on-surface-variant">
    <span class="material-symbols-outlined text-cyan-deep text-xl">photo_library</span>
    <span>JPG, PNG, WEBP o GIF · máximo 5 MB</span>
  </div>
</div>

<nav class="flex flex-nowrap sm:flex-wrap gap-2 p-1.5 rounded-2xl bg-sage/40 border border-sage/50 overflow-x-auto">
<?php
$tabs = [
    'contenido' => ['label' => 'Textos', 'icon' => 'edit_note'],
    'colores' => ['label' => 'Colores', 'icon' => 'palette'],
    'imagenes' => ['label' => 'Imágenes', 'icon' => 'image'],
    'videos' => ['label' => 'Videos', 'icon' => 'smart_display'],
    'servicios' => ['label' => 'Servicios', 'icon' => 'medical_services'],
];
foreach ($tabs as $key => $meta):
    $active = $tab === $key;
?>
  <a href="ajustes.php?tab=<?= urlencode($key) ?>"
     class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-label-caps text-label-caps uppercase tracking-wider transition-all whitespace-nowrap <?= $active ? 'bg-light-cyan text-white shadow-sm' : 'text-on-surface-variant hover:bg-mist' ?>">
    <span class="material-symbols-outlined text-xl"><?= htmlspecialchars($meta['icon'], ENT_QUOTES, 'UTF-8') ?></span>
    <?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?>
  </a>
<?php endforeach; ?>
</nav>

<?php if ($tab === 'contenido'): ?>
<section class="space-y-6">
  <div class="flex items-center gap-3 mb-2">
    <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center"><span class="material-symbols-outlined">edit_note</span></span>
    <div>
      <h3 class="font-headline-md text-lg font-bold text-on-background">Textos y títulos</h3>
      <p class="text-sm text-on-surface-variant">Cada sección tiene su propio botón Guardar.</p>
    </div>
  </div>
  <?php foreach ($cmsGrouped as $seccion => $rows): ?>
    <?php
      if ($seccion === 'theme') {
          continue;
      }
      $textRows = array_values(array_filter($rows, static fn ($r) => ($r['tipo'] ?? '') !== 'imagen' && ($r['tipo'] ?? '') !== 'color'));
      if ($textRows === []) {
          continue;
      }
    ?>
    <form method="post" class="rounded-2xl border border-sage/50 bg-mist p-5 md:p-6 space-y-5">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="action" value="save_cms_text"/>
      <input type="hidden" name="seccion" value="<?= htmlspecialchars($seccion, ENT_QUOTES, 'UTF-8') ?>"/>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <h4 class="font-label-caps text-label-caps text-cyan-deep"><?= htmlspecialchars($sectionLabels[$seccion] ?? $seccion, ENT_QUOTES, 'UTF-8') ?></h4>
        <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-cyan-deep transition-colors shadow-sm">
          <span class="material-symbols-outlined text-xl">save</span>
          Guardar cambios
        </button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($textRows as $row): ?>
          <?php
            $clave = (string) $row['clave'];
            $isSwitch = str_ends_with($clave, '.visible');
            $isLong = !$isSwitch && (strlen((string) $row['valor']) > 80 || str_contains($clave, 'subtitulo') || str_contains($clave, 'bio') || str_contains($clave, 'texto') || str_contains($clave, 'horarios') || str_contains($clave, 'direccion') || str_contains($clave, 'descripcion') || str_contains($clave, 'keywords') || str_contains($clave, 'maps_embed'));
            $isUrl = (($row['tipo'] ?? '') === 'url' || str_contains($clave, 'maps_') || str_contains($clave, '_url') || str_contains($clave, 'facebook') || str_contains($clave, 'youtube') || str_contains($clave, 'tiktok'))
                && !str_contains($clave, 'maps_embed');
            $fieldValor = (string) $row['valor'];
            if ($clave === 'ubicacion.maps_embed') {
                $normEmbed = cms_normalize_maps_embed($fieldValor);
                if ($normEmbed !== '') {
                    $fieldValor = $normEmbed;
                }
            }
          ?>
          <div class="<?= $isLong || $isSwitch ? 'md:col-span-2' : '' ?> space-y-2">
            <?php if ($isSwitch): ?>
              <label class="flex items-center justify-between gap-4 rounded-xl border border-sage/40 bg-surface-container-lowest px-4 py-3 cursor-pointer">
                <span class="min-w-0">
                  <span class="font-label-caps text-label-caps text-cyan-deep block"><?= htmlspecialchars((string) $row['etiqueta'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="text-xs text-on-surface-variant">Si está apagado, la sección no aparece en la landing.</span>
                </span>
                <span class="relative inline-flex items-center shrink-0">
                  <input type="hidden" name="cms[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]" value="0"/>
                  <input type="checkbox"
                         name="cms[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]"
                         value="1"
                         class="peer sr-only"
                         <?= cms_is_truthy((string) $row['valor']) ? 'checked' : '' ?>/>
                  <span class="w-12 h-7 rounded-full bg-outline-variant peer-checked:bg-light-cyan transition-colors"></span>
                  <span class="absolute left-1 top-1 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                </span>
              </label>
            <?php else: ?>
            <label class="font-label-caps text-label-caps text-on-surface-variant" for="cms-<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars((string) $row['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
            </label>
            <?php if ($clave === 'ubicacion.maps_embed'): ?>
              <p class="text-xs text-on-surface-variant">Puedes pegar la URL del mapa o el código iframe completo de Google Maps; el sistema guarda solo la URL.</p>
            <?php endif; ?>
            <?php if ($isLong): ?>
              <textarea id="cms-<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"
                        name="cms[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]"
                        rows="<?= $clave === 'ubicacion.maps_embed' ? '4' : '3' ?>"
                        class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-4 py-3 font-body-md focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"><?= htmlspecialchars($fieldValor, ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php else: ?>
              <input id="cms-<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"
                     type="<?= $isUrl ? 'url' : 'text' ?>"
                     name="cms[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]"
                     value="<?= htmlspecialchars($fieldValor, ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-4 py-3 font-body-md focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            <?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="flex justify-end pt-1 border-t border-sage/40">
        <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-cyan-deep transition-colors">
          <span class="material-symbols-outlined text-xl">save</span>
          Guardar <?= htmlspecialchars($sectionLabels[$seccion] ?? 'sección', ENT_QUOTES, 'UTF-8') ?>
        </button>
      </div>
    </form>
  <?php endforeach; ?>
  <a href="index.php" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold border border-outline-variant bg-mist text-on-surface hover:border-light-cyan transition-colors">
    <span class="material-symbols-outlined">visibility</span>
    Ver landing
  </a>
</section>
<?php endif; ?>

<?php if ($tab === 'colores'): ?>
<?php
  $brandNow = theme_resolve_brand();
  $previewPalette = [
    ['key' => 'cyan', 'label' => 'Cian'],
    ['key' => 'yellow', 'label' => 'Amarillo'],
    ['key' => 'orange', 'label' => 'Naranja'],
    ['key' => 'teal', 'label' => 'Teal'],
    ['key' => 'mint', 'label' => 'Menta'],
    ['key' => 'sage', 'label' => 'Sage'],
    ['key' => 'mist', 'label' => 'Fondo'],
  ];
?>
<section class="space-y-6">
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center"><span class="material-symbols-outlined">palette</span></span>
      <div>
        <h3 class="font-headline-md text-lg font-bold text-on-background">Colores del sitio</h3>
        <p class="text-sm text-on-surface-variant">Usa la rueda de color o escribe un hex (#RRGGBB). Afecta landing y panel.</p>
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      <?php foreach ($previewPalette as $sw): ?>
        <div class="flex flex-col items-center gap-1">
          <span class="js-theme-swatch w-9 h-9 rounded-full border border-outline-variant shadow-sm" data-brand="<?= htmlspecialchars($sw['key'], ENT_QUOTES, 'UTF-8') ?>" style="background:<?= htmlspecialchars($brandNow[$sw['key']] ?? '#ccc', ENT_QUOTES, 'UTF-8') ?>"></span>
          <span class="text-[10px] font-bold text-on-surface-variant"><?= htmlspecialchars($sw['label'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <form method="post" id="theme-colors-form" class="rounded-2xl border border-sage/50 bg-mist p-5 md:p-8 space-y-6">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
    <input type="hidden" name="action" value="save_theme_colors"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
      <?php foreach (theme_editable_defs() as $brandKey => $def): ?>
        <?php
          $clave = $def['clave'];
          $hex = $brandNow[$brandKey] ?? $def['valor'];
        ?>
        <div class="rounded-xl border border-sage/40 bg-surface-container-lowest/70 p-4 space-y-3">
          <div>
            <label class="font-label-caps text-label-caps text-cyan-deep" for="pick-<?= htmlspecialchars($brandKey, ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
            </label>
            <p class="text-xs text-on-surface-variant mt-1"><?= htmlspecialchars($def['ayuda'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="flex items-center gap-3">
            <input
              type="color"
              id="pick-<?= htmlspecialchars($brandKey, ENT_QUOTES, 'UTF-8') ?>"
              class="js-color-wheel h-14 w-16 shrink-0 cursor-pointer rounded-lg border border-outline-variant bg-transparent p-0.5"
              value="<?= htmlspecialchars($hex, ENT_QUOTES, 'UTF-8') ?>"
              data-brand="<?= htmlspecialchars($brandKey, ENT_QUOTES, 'UTF-8') ?>"
              data-sync="hex-<?= htmlspecialchars($brandKey, ENT_QUOTES, 'UTF-8') ?>"
              aria-label="Rueda de color <?= htmlspecialchars($def['etiqueta'], ENT_QUOTES, 'UTF-8') ?>"
            />
            <input
              type="text"
              id="hex-<?= htmlspecialchars($brandKey, ENT_QUOTES, 'UTF-8') ?>"
              name="cms[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]"
              value="<?= htmlspecialchars($hex, ENT_QUOTES, 'UTF-8') ?>"
              pattern="^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$"
              maxlength="7"
              spellcheck="false"
              class="js-color-hex flex-1 min-w-0 rounded-xl border border-outline-variant bg-mist px-3 py-3 font-mono text-sm uppercase focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"
              data-brand="<?= htmlspecialchars($brandKey, ENT_QUOTES, 'UTF-8') ?>"
              data-sync="pick-<?= htmlspecialchars($brandKey, ENT_QUOTES, 'UTF-8') ?>"
              required
            />
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="flex flex-wrap gap-3 pt-2 border-t border-sage/40">
      <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-6 py-3 rounded-xl font-bold hover:bg-cyan-deep transition-colors shadow-md">
        <span class="material-symbols-outlined">save</span>
        Guardar colores
      </button>
      <a href="index.php" target="_blank" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl font-bold border border-outline-variant bg-surface-container-lowest text-on-surface hover:border-light-cyan transition-colors">
        <span class="material-symbols-outlined">visibility</span>
        Ver landing
      </a>
    </div>
  </form>

  <form method="post" onsubmit="return confirm('¿Restaurar la paleta original de MiDoc?');" class="rounded-xl border border-dashed border-outline-variant bg-surface-container-lowest/50 p-4 flex flex-wrap items-center justify-between gap-3">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
    <input type="hidden" name="action" value="reset_theme_colors"/>
    <p class="text-sm text-on-surface-variant">¿Quieres volver a los colores de fábrica?</p>
    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm border border-outline-variant text-on-surface hover:border-brand-orange hover:text-brand-orange transition-colors">
      <span class="material-symbols-outlined text-xl">restart_alt</span>
      Restaurar originales
    </button>
  </form>
</section>
<script>
(function () {
  function normHex(v) {
    v = String(v || '').trim();
    if (/^#[0-9A-Fa-f]{6}$/.test(v)) return v.toUpperCase();
    if (/^#[0-9A-Fa-f]{3}$/.test(v)) {
      var s = v.slice(1);
      return ('#' + s[0] + s[0] + s[1] + s[1] + s[2] + s[2]).toUpperCase();
    }
    return null;
  }
  function updateSwatch(brand, hex) {
    document.querySelectorAll('.js-theme-swatch[data-brand="' + brand + '"]').forEach(function (el) {
      el.style.background = hex;
    });
  }
  document.querySelectorAll('.js-color-wheel').forEach(function (wheel) {
    wheel.addEventListener('input', function () {
      var hex = normHex(wheel.value);
      if (!hex) return;
      var hexInput = document.getElementById(wheel.getAttribute('data-sync'));
      if (hexInput) hexInput.value = hex;
      updateSwatch(wheel.getAttribute('data-brand'), hex);
    });
  });
  document.querySelectorAll('.js-color-hex').forEach(function (input) {
    input.addEventListener('change', function () {
      var hex = normHex(input.value);
      if (!hex) return;
      input.value = hex;
      var wheel = document.getElementById(input.getAttribute('data-sync'));
      if (wheel) wheel.value = hex;
      updateSwatch(input.getAttribute('data-brand'), hex);
    });
  });
})();
</script>
<?php endif; ?>

<?php if ($tab === 'imagenes'): ?>
<section class="space-y-6">
  <div class="flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center"><span class="material-symbols-outlined">image</span></span>
    <div>
      <h3 class="font-headline-md text-lg font-bold text-on-background">Imágenes de la landing</h3>
      <p class="text-sm text-on-surface-variant">Arrastra o elige un archivo. JPG, PNG, WEBP o GIF · máximo 5 MB. Usa la proporción recomendada para que se vea bien.</p>
    </div>
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <?php foreach ($imageKeys as $clave => $meta): ?>
    <div class="bg-mist border border-sage/50 rounded-2xl p-5 space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="font-label-caps text-label-caps text-cyan-deep"><?= htmlspecialchars($meta['seccion'], ENT_QUOTES, 'UTF-8') ?></p>
          <h4 class="font-headline-md text-lg font-bold text-on-background"><?= htmlspecialchars($meta['etiqueta'], ENT_QUOTES, 'UTF-8') ?></h4>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-lg border border-light-cyan/40 bg-light-cyan/10 text-cyan-deep px-2.5 py-1 text-xs font-bold tabular-nums">
              <span class="material-symbols-outlined text-sm">aspect_ratio</span>
              <?= htmlspecialchars((string) $meta['ratio'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <span class="text-xs text-on-surface-variant landing-break"><?= htmlspecialchars((string) $meta['ratio_detail'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>
        <?php if ($meta['valor'] !== ''): ?>
          <span class="inline-flex items-center gap-1 rounded-full bg-mint/50 text-teal px-2.5 py-1 text-xs font-bold shrink-0">
            <span class="material-symbols-outlined text-sm">check</span> Asignada
          </span>
        <?php else: ?>
          <span class="inline-flex items-center gap-1 rounded-full bg-surface-container-high text-on-surface-variant px-2.5 py-1 text-xs font-bold shrink-0">Sin imagen</span>
        <?php endif; ?>
      </div>
      <form method="post" enctype="multipart/form-data" class="space-y-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
        <input type="hidden" name="action" value="save_cms_image"/>
        <input type="hidden" name="clave" value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"/>
        <label class="js-dropzone block cursor-pointer rounded-xl border-2 border-dashed border-light-cyan/40 bg-mist hover:bg-mint/20 transition-colors">
          <input type="file" name="archivo" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only js-dropzone-input" required/>
          <?php
            $previewAspect = ((string) $meta['ratio'] === '1:1') ? 'aspect-square max-h-56' : 'aspect-video max-h-52';
          ?>
          <div class="js-dropzone-preview <?= $previewAspect ?> w-full rounded-t-xl overflow-hidden bg-surface-container-low mx-auto">
            <?php if ($meta['valor'] !== ''): ?>
              <img src="<?= htmlspecialchars($meta['valor'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover"/>
            <?php else: ?>
              <div class="h-full flex flex-col items-center justify-center text-on-surface-variant gap-1 px-3 text-center">
                <span class="material-symbols-outlined text-4xl">add_photo_alternate</span>
                <span class="text-sm font-bold">Proporción <?= htmlspecialchars((string) $meta['ratio'], ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            <?php endif; ?>
          </div>
          <p class="js-dropzone-hint px-4 py-3 text-sm font-bold text-cyan-deep text-center">
            Arrastra una imagen aquí o haz clic para elegirla
          </p>
        </label>
        <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-cyan-deep transition-colors">
          <span class="material-symbols-outlined text-xl">photo_camera</span>
          Actualizar imagen
        </button>
      </form>
    </div>
  <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($tab === 'videos'): ?>
<section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
  <div class="xl:col-span-1 bg-mist border border-sage/50 rounded-2xl p-6">
    <div class="flex items-center gap-3 mb-2">
      <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center"><span class="material-symbols-outlined"><?= $editVideo ? 'edit' : 'smart_display' ?></span></span>
      <h3 class="font-headline-md text-lg font-bold"><?= $editVideo ? 'Editar video' : 'Nuevo video' ?></h3>
    </div>
    <p class="text-sm text-on-surface-variant mb-5">Pega el enlace de YouTube. En la landing se abre en modal. La visibilidad de toda la sección se controla en Textos → Videos.</p>
    <form method="post" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="action" value="video_save"/>
      <input type="hidden" name="id" value="<?= (int) ($editVideo['id'] ?? 0) ?>"/>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Título</label>
        <input name="titulo" required value="<?= htmlspecialchars((string) ($editVideo['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">URL de YouTube</label>
        <input type="url" name="youtube_url" required placeholder="https://www.youtube.com/watch?v=..." value="<?= htmlspecialchars((string) ($editVideo['youtube_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Descripción (opcional)</label>
        <textarea name="descripcion" rows="3" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"><?= htmlspecialchars((string) ($editVideo['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Orden</label>
        <input type="number" name="orden" value="<?= (int) ($editVideo['orden'] ?? 0) ?>" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
      </div>
      <label class="flex items-center justify-between gap-3 rounded-xl border border-sage/40 bg-surface-container-lowest/50 px-3 py-2.5 text-sm cursor-pointer">
        <span>Visible en la landing</span>
        <span class="relative inline-flex items-center">
          <input type="checkbox" name="activo" value="1" class="peer sr-only" <?= !isset($editVideo) || !empty($editVideo['activo']) ? 'checked' : '' ?>/>
          <span class="w-12 h-7 rounded-full bg-outline-variant peer-checked:bg-light-cyan transition-colors"></span>
          <span class="absolute left-1 top-1 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
        </span>
      </label>
      <div class="flex flex-wrap gap-2 pt-1">
        <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-5 py-2.5 rounded-xl font-bold hover:bg-cyan-deep transition-colors">
          <span class="material-symbols-outlined"><?= $editVideo ? 'save' : 'add' ?></span>
          <?= $editVideo ? 'Guardar' : 'Publicar' ?>
        </button>
        <?php if ($editVideo): ?>
          <a href="ajustes.php?tab=videos" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold border border-outline-variant bg-mist hover:border-light-cyan transition-colors">Cancelar</a>
        <?php endif; ?>
      </div>
    </form>
    <p class="text-xs text-on-surface-variant mt-4">
      Tip: para ocultar toda la sección Videos ve a <a class="font-bold text-cyan-deep underline" href="ajustes.php?tab=contenido">Textos → Videos</a> y apaga el switch.
    </p>
  </div>

  <div class="xl:col-span-2 space-y-3">
    <h3 class="font-headline-md text-lg font-bold px-1">Listado de videos</h3>
    <?php if ($landingVideos === []): ?>
      <div class="bg-mist border border-dashed border-sage/70 rounded-2xl p-10 text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-4xl mb-2">smart_display</span>
        <p>Aún no hay videos. Publica el primero con el formulario.</p>
      </div>
    <?php endif; ?>
    <?php foreach ($landingVideos as $v): ?>
      <article class="bg-mist border border-sage/50 rounded-2xl p-4 flex flex-col sm:flex-row gap-4">
        <div class="w-full sm:w-40 aspect-video rounded-xl overflow-hidden border border-sage/40 bg-surface-container-low shrink-0">
          <img src="<?= htmlspecialchars(youtube_thumb_url((string) $v['youtube_id']), ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover"/>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="text-xs font-bold text-on-surface-variant">#<?= (int) $v['orden'] ?></span>
            <h4 class="font-bold text-on-background"><?= htmlspecialchars((string) $v['titulo'], ENT_QUOTES, 'UTF-8') ?></h4>
            <?php if (!empty($v['activo'])): ?>
              <span class="text-xs bg-mint/50 text-teal px-2 py-0.5 rounded-full font-bold">Activo</span>
            <?php else: ?>
              <span class="text-xs bg-surface-container-high text-on-surface-variant px-2 py-0.5 rounded-full font-bold">Oculto</span>
            <?php endif; ?>
          </div>
          <?php if (!empty($v['descripcion'])): ?>
            <p class="text-sm text-on-surface-variant line-clamp-2 mb-2"><?= htmlspecialchars((string) $v['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
          <p class="text-xs text-on-surface-variant truncate mb-3"><?= htmlspecialchars((string) $v['youtube_url'], ENT_QUOTES, 'UTF-8') ?></p>
          <div class="flex flex-wrap gap-2">
            <a href="ajustes.php?tab=videos&edit_video=<?= (int) $v['id'] ?>" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-light-cyan/40 text-cyan-deep font-bold text-sm hover:bg-light-cyan hover:text-white transition-colors">
              <span class="material-symbols-outlined text-base">edit</span> Editar
            </a>
            <form method="post" onsubmit="return confirm('¿Eliminar este video?');">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
              <input type="hidden" name="action" value="video_delete"/>
              <input type="hidden" name="id" value="<?= (int) $v['id'] ?>"/>
              <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-error/30 text-error font-bold text-sm hover:bg-error-container transition-colors">
                <span class="material-symbols-outlined text-base">delete</span> Eliminar
              </button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($tab === 'servicios'): ?>
<?php
  $serviciosTituloAdultosAdmin = cms_get('servicios.titulo_adultos', 'Cirugía General Adultos');
  $serviciosTituloPediatricaAdmin = cms_get('servicios.titulo_pediatrica', 'Cirugía Pediátrica');
  $bloquesServiciosAdmin = [
      ['key' => 'adultos', 'titulo' => $serviciosTituloAdultosAdmin, 'items' => $serviciosGroupedAdmin['adultos'] ?? [], 'icon' => 'person'],
      ['key' => 'pediatrica', 'titulo' => $serviciosTituloPediatricaAdmin, 'items' => $serviciosGroupedAdmin['pediatrica'] ?? [], 'icon' => 'child_care'],
  ];
  $otrosServiciosAdmin = $serviciosGroupedAdmin['otro'] ?? [];
  $iconPalette = servicios_icon_palette();
  $iconoActual = trim((string) ($editServicio['icono'] ?? 'medical_services')) ?: 'medical_services';
  if (!in_array($iconoActual, $iconPalette, true)) {
      array_unshift($iconPalette, $iconoActual);
  }
?>
<section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
  <div class="xl:col-span-1 bg-mist border border-sage/50 rounded-2xl p-6">
    <div class="flex items-center gap-3 mb-2">
      <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center"><span class="material-symbols-outlined"><?= $editServicio ? 'edit' : 'add' ?></span></span>
      <h3 class="font-headline-md text-lg font-bold"><?= $editServicio ? 'Editar servicio' : 'Añadir servicio' ?></h3>
    </div>
    <p class="text-sm text-on-surface-variant mb-5">
      Tarjeta en la landing + modal al pulsar (imagen y detalle). Elige icono, texto corto, detalle largo e imagen.
    </p>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="action" value="servicio_save"/>
      <input type="hidden" name="id" value="<?= (int) ($editServicio['id'] ?? 0) ?>"/>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Título</label>
        <input name="titulo" required value="<?= htmlspecialchars((string) ($editServicio['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan" placeholder="Ej: Hernias"/>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Slug (opcional)</label>
        <input name="slug" value="<?= htmlspecialchars((string) ($editServicio['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan" placeholder="auto desde título"/>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Descripción corta (tarjeta)</label>
        <textarea name="descripcion" required rows="2" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan" placeholder="Texto breve en la tarjeta"><?= htmlspecialchars((string) ($editServicio['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Detalle del modal</label>
        <textarea name="detalle" rows="4" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan" placeholder="Texto más completo al abrir el servicio. Si lo dejas vacío, se usa la descripción corta."><?= htmlspecialchars((string) ($editServicio['detalle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="space-y-2">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Icono</label>
        <p class="text-xs text-on-surface-variant">Elige entre iconos ya usados y otros médicos recomendados.</p>
        <input type="hidden" name="icono" id="servicio-icono-input" value="<?= htmlspecialchars($iconoActual, ENT_QUOTES, 'UTF-8') ?>"/>
        <div class="grid grid-cols-5 sm:grid-cols-6 gap-2 max-h-48 overflow-y-auto p-1 rounded-xl border border-sage/40 bg-surface-container-lowest/40">
          <?php foreach ($iconPalette as $iconName): ?>
            <button type="button"
                    class="js-icon-pick w-full aspect-square rounded-xl border flex items-center justify-center transition-colors <?= $iconName === $iconoActual ? 'border-light-cyan bg-light-cyan/20 text-cyan-deep' : 'border-transparent text-on-surface-variant hover:border-light-cyan/40 hover:bg-mist' ?>"
                    data-icon="<?= htmlspecialchars($iconName, ENT_QUOTES, 'UTF-8') ?>"
                    title="<?= htmlspecialchars($iconName, ENT_QUOTES, 'UTF-8') ?>"
                    aria-label="<?= htmlspecialchars($iconName, ENT_QUOTES, 'UTF-8') ?>">
              <span class="material-symbols-outlined text-xl"><?= htmlspecialchars($iconName, ENT_QUOTES, 'UTF-8') ?></span>
            </button>
          <?php endforeach; ?>
        </div>
        <p class="text-xs text-on-surface-variant">Seleccionado: <strong id="servicio-icono-label"><?= htmlspecialchars($iconoActual, ENT_QUOTES, 'UTF-8') ?></strong></p>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Atención / categoría</label>
        <select name="categoria" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan">
          <?php $catEdit = (string) ($editServicio['categoria'] ?? 'adultos'); ?>
          <option value="adultos" <?= $catEdit !== 'pediatrica' ? 'selected' : '' ?>>Cirugía General Adultos</option>
          <option value="pediatrica" <?= $catEdit === 'pediatrica' ? 'selected' : '' ?>>Cirugía Pediátrica (niños)</option>
        </select>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Orden</label>
        <input type="number" name="orden" value="<?= (int) ($editServicio['orden'] ?? 0) ?>" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
      </div>
      <div class="space-y-2">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Imagen del modal</label>
        <p class="text-xs text-on-surface-variant">Se muestra al abrir el servicio. Proporción recomendada <strong>16:9</strong> (≈ 1200×675 px).</p>
        <label class="js-dropzone block cursor-pointer rounded-xl border-2 border-dashed border-light-cyan/40 bg-mist hover:bg-mint/20 transition-colors">
          <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only js-dropzone-input"/>
          <div class="js-dropzone-preview aspect-video max-h-40 w-full rounded-t-xl overflow-hidden bg-surface-container-low">
            <?php if (!empty($editServicio['imagen_url'])): ?>
              <img src="<?= htmlspecialchars((string) $editServicio['imagen_url'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover"/>
            <?php else: ?>
              <div class="h-full flex items-center justify-center text-on-surface-variant gap-2 text-sm">
                <span class="material-symbols-outlined">add_photo_alternate</span> Sin imagen
              </div>
            <?php endif; ?>
          </div>
          <p class="js-dropzone-hint px-4 py-3 text-sm font-bold text-cyan-deep text-center">
            Arrastra o elige imagen 16:9
          </p>
        </label>
      </div>
      <label class="flex items-center gap-3 rounded-xl border border-sage/40 bg-surface-container-lowest/50 px-3 py-2.5 text-sm cursor-pointer">
        <input type="checkbox" name="activo" value="1" class="rounded border-outline-variant text-light-cyan focus:ring-light-cyan" <?= !isset($editServicio) || !empty($editServicio['activo']) ? 'checked' : '' ?>/>
        Visible en la landing
      </label>
      <div class="flex flex-wrap gap-2 pt-1">
        <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-5 py-2.5 rounded-xl font-bold hover:bg-cyan-deep transition-colors">
          <span class="material-symbols-outlined"><?= $editServicio ? 'save' : 'add' ?></span>
          <?= $editServicio ? 'Guardar' : 'Añadir' ?>
        </button>
        <?php if ($editServicio): ?>
          <a href="ajustes.php?tab=servicios" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold border border-outline-variant bg-mist hover:border-light-cyan transition-colors">
            <span class="material-symbols-outlined">close</span>
            Cancelar
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="xl:col-span-2 space-y-8">
    <?php foreach ($bloquesServiciosAdmin as $bloque): ?>
      <div class="space-y-3">
        <div class="flex items-center gap-2 px-1">
          <span class="w-9 h-9 rounded-full bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-xl"><?= htmlspecialchars($bloque['icon'], ENT_QUOTES, 'UTF-8') ?></span>
          </span>
          <div class="min-w-0">
            <h3 class="font-headline-md text-lg font-bold"><?= htmlspecialchars($bloque['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-xs text-on-surface-variant"><?= count($bloque['items']) ?> servicio(s)</p>
          </div>
        </div>
        <?php if ($bloque['items'] === []): ?>
          <div class="bg-mist border border-dashed border-sage/70 rounded-2xl p-6 text-center text-on-surface-variant text-sm">
            Aún no hay servicios en este bloque. Usa el formulario para añadir uno.
          </div>
        <?php endif; ?>
        <?php foreach ($bloque['items'] as $s): ?>
          <article class="bg-mist border border-sage/50 rounded-2xl p-4 flex flex-col sm:flex-row gap-4">
            <div class="w-12 h-12 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined"><?= htmlspecialchars((string) ($s['icono'] ?? 'medical_services'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="text-xs font-bold text-on-surface-variant">#<?= (int) $s['orden'] ?></span>
                <h4 class="font-bold text-on-background"><?= htmlspecialchars((string) $s['titulo'], ENT_QUOTES, 'UTF-8') ?></h4>
                <?php if (!empty($s['imagen_url'])): ?>
                  <span class="text-xs bg-light-cyan/15 text-cyan-deep px-2 py-0.5 rounded-full font-bold">Con imagen</span>
                <?php endif; ?>
                <?php if (!empty($s['detalle'])): ?>
                  <span class="text-xs bg-mint/40 text-teal px-2 py-0.5 rounded-full font-bold">Detalle</span>
                <?php endif; ?>
                <?php if (!empty($s['activo'])): ?>
                  <span class="text-xs bg-mint/50 text-teal px-2 py-0.5 rounded-full font-bold">Visible</span>
                <?php else: ?>
                  <span class="text-xs bg-surface-container-high text-on-surface-variant px-2 py-0.5 rounded-full font-bold">Oculto</span>
                <?php endif; ?>
              </div>
              <p class="text-sm text-on-surface-variant line-clamp-2"><?= htmlspecialchars((string) ($s['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="flex sm:flex-col gap-2 shrink-0">
              <a class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl border border-light-cyan/40 text-cyan-deep font-bold text-sm hover:bg-light-cyan hover:text-white transition-colors" href="ajustes.php?tab=servicios&edit_servicio=<?= (int) $s['id'] ?>">
                <span class="material-symbols-outlined text-lg">edit</span> Editar
              </a>
              <form method="post" onsubmit="return confirm('¿Ocultar este servicio de la landing?');">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
                <input type="hidden" name="action" value="servicio_delete"/>
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>"/>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl border border-error/30 text-error font-bold text-sm hover:bg-error hover:text-white transition-colors">
                  <span class="material-symbols-outlined text-lg">delete</span> Eliminar
                </button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <?php if ($otrosServiciosAdmin !== []): ?>
      <div class="space-y-3 pt-2 border-t border-sage/40">
        <h3 class="font-headline-md text-base font-bold px-1 text-on-surface-variant">Otros (formulario / interno)</h3>
        <?php foreach ($otrosServiciosAdmin as $s): ?>
          <article class="bg-mist border border-sage/40 rounded-2xl p-4 flex flex-col sm:flex-row gap-3 opacity-90">
            <div class="flex-1 min-w-0">
              <h4 class="font-bold text-on-background"><?= htmlspecialchars((string) $s['titulo'], ENT_QUOTES, 'UTF-8') ?></h4>
              <p class="text-xs text-on-surface-variant"><?= htmlspecialchars((string) $s['slug'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl border border-light-cyan/40 text-cyan-deep font-bold text-sm" href="ajustes.php?tab=servicios&edit_servicio=<?= (int) $s['id'] ?>">Editar</a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($serviciosInactivosAdmin)): ?>
      <details class="pt-2 border-t border-sage/40">
        <summary class="cursor-pointer font-bold text-sm text-on-surface-variant px-1 py-2">
          Ocultos / antiguos (<?= count($serviciosInactivosAdmin) ?>)
        </summary>
        <div class="space-y-2 mt-2">
          <?php foreach ($serviciosInactivosAdmin as $s): ?>
            <article class="bg-surface-container-lowest border border-dashed border-sage/50 rounded-xl p-3 flex flex-wrap items-center gap-2 justify-between">
              <div class="min-w-0">
                <p class="font-bold text-sm text-on-background"><?= htmlspecialchars((string) $s['titulo'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-xs text-on-surface-variant truncate"><?= htmlspecialchars((string) $s['slug'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
              <a class="text-sm font-bold text-cyan-deep" href="ajustes.php?tab=servicios&edit_servicio=<?= (int) $s['id'] ?>">Reactivar / editar</a>
            </article>
          <?php endforeach; ?>
        </div>
      </details>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<script>
(function () {
  document.querySelectorAll('.js-dropzone').forEach(function (zone) {
    var input = zone.querySelector('.js-dropzone-input');
    var preview = zone.querySelector('.js-dropzone-preview');
    var hint = zone.querySelector('.js-dropzone-hint');
    if (!input || !preview) return;

    function showFile(file) {
      if (!file || !String(file.type || '').startsWith('image/')) return;
      var dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      var url = URL.createObjectURL(file);
      preview.innerHTML = '';
      var img = document.createElement('img');
      img.src = url;
      img.alt = '';
      img.className = 'w-full h-full object-cover';
      preview.appendChild(img);
      if (hint) hint.textContent = 'Listo: ' + file.name + ' · clic para cambiar';
    }

    ['dragenter', 'dragover'].forEach(function (ev) {
      zone.addEventListener(ev, function (e) {
        e.preventDefault();
        e.stopPropagation();
        zone.classList.add('ring-2', 'ring-light-cyan', 'bg-mint/20');
      });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      zone.addEventListener(ev, function (e) {
        e.preventDefault();
        e.stopPropagation();
        zone.classList.remove('ring-2', 'ring-light-cyan', 'bg-mint/20');
      });
    });
    zone.addEventListener('drop', function (e) {
      var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
      showFile(file);
    });
    input.addEventListener('change', function () {
      showFile(input.files && input.files[0]);
    });
  });

  var iconInput = document.getElementById('servicio-icono-input');
  var iconLabel = document.getElementById('servicio-icono-label');
  document.querySelectorAll('.js-icon-pick').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var icon = btn.getAttribute('data-icon') || 'medical_services';
      if (iconInput) iconInput.value = icon;
      if (iconLabel) iconLabel.textContent = icon;
      document.querySelectorAll('.js-icon-pick').forEach(function (b) {
        b.classList.remove('border-light-cyan', 'bg-light-cyan/20', 'text-cyan-deep');
        b.classList.add('border-transparent', 'text-on-surface-variant');
      });
      btn.classList.remove('border-transparent', 'text-on-surface-variant');
      btn.classList.add('border-light-cyan', 'bg-light-cyan/20', 'text-cyan-deep');
    });
  });
})();
</script>
<?php require __DIR__ . '/includes/admin_chrome_end.php'; ?>
