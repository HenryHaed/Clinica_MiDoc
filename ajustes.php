<?php
/**
 * Ajustes del sistema — CMS landing + especialidades/servicios.
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

$allowedTabs = ['contenido', 'colores', 'imagenes', 'servicios', 'especialidades'];
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
            $icono = trim((string) ($_POST['icono'] ?? 'medical_services')) ?: 'medical_services';
            $orden = (int) ($_POST['orden'] ?? 0);
            $activo = isset($_POST['activo']) ? 1 : 0;
            $destacado = isset($_POST['destacado']) ? 1 : 0;
            $slug = trim((string) ($_POST['slug'] ?? ''));

            if ($titulo === '' || $descripcion === '') {
                throw new RuntimeException('Título y descripción son obligatorios.');
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
                'icono' => $icono,
                'orden' => $orden,
                'activo' => $activo,
                'destacado' => $destacado,
            ];
            if ($slug !== '') {
                $payload['slug'] = $slug;
            }
            if (!$keepImage) {
                $payload['imagen_url'] = $imagenUrl;
            }

            if ($id > 0) {
                servicio_update($id, $payload);
                ajustes_redirect('servicios', 'Especialidad / servicio actualizado.');
            }
            servicio_create($payload);
            ajustes_redirect('servicios', 'Especialidad / servicio creado.');
        }

        if ($action === 'servicio_delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('ID inválido.');
            }
            servicio_soft_delete($id);
            ajustes_redirect('servicios', 'Servicio eliminado (oculto en la landing).');
        }

        if ($action === 'especialidad_save') {
            $id = (int) ($_POST['id'] ?? 0);
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
            $activo = isset($_POST['activo']) ? 1 : 0;
            if ($nombre === '') {
                throw new RuntimeException('El nombre es obligatorio.');
            }
            $data = [
                'nombre' => $nombre,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'activo' => $activo,
            ];
            if ($id > 0) {
                especialidad_update($id, $data);
                ajustes_redirect('especialidades', 'Especialidad actualizada.');
            }
            especialidad_create($data);
            ajustes_redirect('especialidades', 'Especialidad creada.');
        }

        if ($action === 'especialidad_delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('ID inválido.');
            }
            especialidad_delete($id);
            ajustes_redirect('especialidades', 'Especialidad eliminada o desactivada.');
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
$imageKeys = [];
foreach (cms_default_defs() as $clave => $def) {
    if ($def['tipo'] === 'imagen') {
        $imageKeys[$clave] = [
            'etiqueta' => $def['etiqueta'],
            'valor' => cms_get($clave),
            'seccion' => $def['seccion'],
        ];
    }
}

$servicios = servicios_list(true, false);
$editServicio = null;
if (isset($_GET['edit_servicio'])) {
    $editServicio = servicio_find((int) $_GET['edit_servicio']);
}

$especialidades = especialidades_list(true);
$editEsp = null;
if (isset($_GET['edit_esp'])) {
    $editEsp = especialidad_find((int) $_GET['edit_esp']);
}

$sectionLabels = [
    'config' => 'Configuración general',
    'seo' => 'SEO Google',
    'hero' => 'Hero (portada)',
    'especialista' => 'Especialista',
    'servicios' => 'Sección servicios',
    'contacto' => 'Contacto / citas',
    'ubicacion' => 'Ubicación / Google Maps',
    'footer' => 'Footer',
    'theme' => 'Colores del sitio',
];

$navActive = 'ajustes';
$headerTitle = 'Ajustes del sistema';
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
    <p class="font-body-md text-on-surface-variant mt-1 max-w-2xl">Edita textos, imágenes y especialidades de la landing.</p>
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
    'servicios' => ['label' => 'Servicios', 'icon' => 'medical_services'],
    'especialidades' => ['label' => 'Catálogo', 'icon' => 'menu_book'],
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
            $isLong = strlen((string) $row['valor']) > 80 || str_contains($clave, 'subtitulo') || str_contains($clave, 'bio') || str_contains($clave, 'texto') || str_contains($clave, 'horarios') || str_contains($clave, 'direccion') || str_contains($clave, 'descripcion') || str_contains($clave, 'keywords');
            $isUrl = ($row['tipo'] ?? '') === 'url' || str_contains($clave, 'maps_') || str_contains($clave, '_url');
          ?>
          <div class="<?= $isLong ? 'md:col-span-2' : '' ?> space-y-2">
            <label class="font-label-caps text-label-caps text-on-surface-variant" for="cms-<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars((string) $row['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
            </label>
            <?php if ($isLong): ?>
              <textarea id="cms-<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"
                        name="cms[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]"
                        rows="3"
                        class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-4 py-3 font-body-md focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"><?= htmlspecialchars((string) $row['valor'], ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php else: ?>
              <input id="cms-<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"
                     type="<?= $isUrl ? 'url' : 'text' ?>"
                     name="cms[<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>]"
                     value="<?= htmlspecialchars((string) $row['valor'], ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-4 py-3 font-body-md focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
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
      <p class="text-sm text-on-surface-variant">Arrastra o elige un archivo. JPG, PNG, WEBP o GIF · máximo 5 MB.</p>
    </div>
  </div>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <?php foreach ($imageKeys as $clave => $meta): ?>
    <div class="bg-mist border border-sage/50 rounded-2xl p-5 space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="font-label-caps text-label-caps text-cyan-deep"><?= htmlspecialchars($meta['seccion'], ENT_QUOTES, 'UTF-8') ?></p>
          <h4 class="font-headline-md text-lg font-bold text-on-background"><?= htmlspecialchars($meta['etiqueta'], ENT_QUOTES, 'UTF-8') ?></h4>
        </div>
        <?php if ($meta['valor'] !== ''): ?>
          <span class="inline-flex items-center gap-1 rounded-full bg-mint/50 text-teal px-2.5 py-1 text-xs font-bold">
            <span class="material-symbols-outlined text-sm">check</span> Asignada
          </span>
        <?php else: ?>
          <span class="inline-flex items-center gap-1 rounded-full bg-surface-container-high text-on-surface-variant px-2.5 py-1 text-xs font-bold">Sin imagen</span>
        <?php endif; ?>
      </div>
      <form method="post" enctype="multipart/form-data" class="space-y-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
        <input type="hidden" name="action" value="save_cms_image"/>
        <input type="hidden" name="clave" value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"/>
        <label class="js-dropzone block cursor-pointer rounded-xl border-2 border-dashed border-light-cyan/40 bg-mist hover:bg-mint/20 transition-colors">
          <input type="file" name="archivo" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only js-dropzone-input" required/>
          <div class="js-dropzone-preview h-44 rounded-t-xl overflow-hidden bg-surface-container-low">
            <?php if ($meta['valor'] !== ''): ?>
              <img src="<?= htmlspecialchars($meta['valor'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover"/>
            <?php else: ?>
              <div class="h-full flex flex-col items-center justify-center text-on-surface-variant gap-1">
                <span class="material-symbols-outlined text-4xl">add_photo_alternate</span>
                <span class="text-sm">Sin imagen</span>
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

<?php if ($tab === 'servicios'): ?>
<section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
  <div class="xl:col-span-1 bg-mist border border-sage/50 rounded-2xl p-6">
    <div class="flex items-center gap-3 mb-2">
      <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center"><span class="material-symbols-outlined"><?= $editServicio ? 'edit' : 'add' ?></span></span>
      <h3 class="font-headline-md text-lg font-bold"><?= $editServicio ? 'Editar servicio' : 'Nuevo servicio' ?></h3>
    </div>
    <p class="text-sm text-on-surface-variant mb-5">Aparecen como cards en la landing y como opciones del formulario de cita.</p>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="action" value="servicio_save"/>
      <input type="hidden" name="id" value="<?= (int) ($editServicio['id'] ?? 0) ?>"/>
      <input type="hidden" name="icono" value="<?= htmlspecialchars((string) ($editServicio['icono'] ?? 'medical_services'), ENT_QUOTES, 'UTF-8') ?>"/>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Título</label>
        <input name="titulo" required value="<?= htmlspecialchars((string) ($editServicio['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Slug (opcional)</label>
        <input name="slug" value="<?= htmlspecialchars((string) ($editServicio['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan" placeholder="auto desde título"/>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Descripción</label>
        <textarea name="descripcion" required rows="4" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"><?= htmlspecialchars((string) ($editServicio['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Orden</label>
        <input type="number" name="orden" value="<?= (int) ($editServicio['orden'] ?? 0) ?>" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
      </div>
      <div class="space-y-2">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Imagen de la especialidad</label>
        <label class="js-dropzone block cursor-pointer rounded-xl border-2 border-dashed border-light-cyan/40 bg-mist hover:bg-mint/20 transition-colors">
          <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only js-dropzone-input"/>
          <div class="js-dropzone-preview h-32 rounded-t-xl overflow-hidden bg-surface-container-low">
            <?php if (!empty($editServicio['imagen_url'])): ?>
              <img src="<?= htmlspecialchars((string) $editServicio['imagen_url'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover"/>
            <?php else: ?>
              <div class="h-full flex items-center justify-center text-on-surface-variant gap-2 text-sm">
                <span class="material-symbols-outlined">add_photo_alternate</span> Sin imagen
              </div>
            <?php endif; ?>
          </div>
          <p class="js-dropzone-hint px-4 py-3 text-sm font-bold text-cyan-deep text-center">
            Arrastra una imagen aquí o haz clic para elegirla
          </p>
        </label>
        <p class="text-xs text-on-surface-variant">JPG, PNG, WEBP o GIF · máximo 5 MB.</p>
      </div>
      <label class="flex items-center gap-3 rounded-xl border border-sage/40 bg-surface-container-lowest/50 px-3 py-2.5 text-sm cursor-pointer">
        <input type="checkbox" name="activo" value="1" class="rounded border-outline-variant text-light-cyan focus:ring-light-cyan" <?= !isset($editServicio) || !empty($editServicio['activo']) ? 'checked' : '' ?>/>
        Activo en landing y formulario
      </label>
      <label class="flex items-center gap-3 rounded-xl border border-sage/40 bg-surface-container-lowest/50 px-3 py-2.5 text-sm cursor-pointer">
        <input type="checkbox" name="destacado" value="1" class="rounded border-outline-variant text-light-cyan focus:ring-light-cyan" <?= !empty($editServicio['destacado']) ? 'checked' : '' ?>/>
        Destacado (card amplia)
      </label>
      <div class="flex flex-wrap gap-2 pt-1">
        <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-5 py-2.5 rounded-xl font-bold hover:bg-cyan-deep transition-colors">
          <span class="material-symbols-outlined"><?= $editServicio ? 'save' : 'add' ?></span>
          <?= $editServicio ? 'Guardar' : 'Crear' ?>
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

  <div class="xl:col-span-2 space-y-3">
    <h3 class="font-headline-md text-lg font-bold px-1">Listado</h3>
    <?php if ($servicios === []): ?>
      <div class="bg-mist border border-dashed border-sage/70 rounded-2xl p-10 text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-4xl mb-2">medical_services</span>
        <p>No hay servicios. Créalos en el formulario o importa el schema SQL.</p>
      </div>
    <?php endif; ?>
    <?php foreach ($servicios as $s): ?>
      <article class="bg-mist border border-sage/50 rounded-2xl p-4 flex flex-col sm:flex-row gap-4">
        <div class="w-full sm:w-28 h-24 rounded-xl overflow-hidden border border-sage/40 bg-surface-container-low shrink-0">
          <?php if (!empty($s['imagen_url'])): ?>
            <img src="<?= htmlspecialchars((string) $s['imagen_url'], ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover"/>
          <?php else: ?>
            <div class="h-full flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined">image</span></div>
          <?php endif; ?>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="text-xs font-bold text-on-surface-variant">#<?= (int) $s['orden'] ?></span>
            <h4 class="font-bold text-on-background"><?= htmlspecialchars((string) $s['titulo'], ENT_QUOTES, 'UTF-8') ?></h4>
            <?php if (!empty($s['destacado'])): ?>
              <span class="text-xs bg-brand-orange/15 text-brand-orange px-2 py-0.5 rounded-full font-bold">Destacado</span>
            <?php endif; ?>
            <?php if (!empty($s['activo'])): ?>
              <span class="text-xs bg-mint/50 text-teal px-2 py-0.5 rounded-full font-bold">Activo</span>
            <?php else: ?>
              <span class="text-xs bg-surface-container-high text-on-surface-variant px-2 py-0.5 rounded-full font-bold">Inactivo</span>
            <?php endif; ?>
          </div>
          <p class="text-xs text-on-surface-variant truncate"><?= htmlspecialchars((string) $s['slug'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="flex sm:flex-col gap-2 shrink-0">
          <a class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl border border-light-cyan/40 text-cyan-deep font-bold text-sm hover:bg-light-cyan hover:text-white transition-colors" href="ajustes.php?tab=servicios&edit_servicio=<?= (int) $s['id'] ?>">
            <span class="material-symbols-outlined text-lg">edit</span> Editar
          </a>
          <form method="post" onsubmit="return confirm('¿Eliminar este servicio de la landing?');">
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
</section>
<?php endif; ?>

<?php if ($tab === 'especialidades'): ?>
<section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
  <div class="xl:col-span-1 bg-mist border border-sage/50 rounded-2xl p-6">
    <div class="flex items-center gap-3 mb-2">
      <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center"><span class="material-symbols-outlined"><?= $editEsp ? 'edit' : 'add' ?></span></span>
      <h3 class="font-headline-md text-lg font-bold"><?= $editEsp ? 'Editar especialidad' : 'Nueva especialidad' ?></h3>
    </div>
    <p class="text-sm text-on-surface-variant mb-5">Catálogo interno del personal médico. Si está en uso, al eliminar solo se desactiva.</p>
    <form method="post" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
      <input type="hidden" name="action" value="especialidad_save"/>
      <input type="hidden" name="id" value="<?= (int) ($editEsp['id'] ?? 0) ?>"/>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Nombre</label>
        <input name="nombre" required value="<?= htmlspecialchars((string) ($editEsp['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
      </div>
      <div class="space-y-1">
        <label class="font-label-caps text-label-caps text-on-surface-variant">Descripción</label>
        <textarea name="descripcion" rows="3" class="w-full rounded-xl border border-outline-variant bg-mist px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"><?= htmlspecialchars((string) ($editEsp['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <label class="flex items-center gap-3 rounded-xl border border-sage/40 bg-surface-container-lowest/50 px-3 py-2.5 text-sm cursor-pointer">
        <input type="checkbox" name="activo" value="1" class="rounded border-outline-variant text-light-cyan focus:ring-light-cyan" <?= !isset($editEsp) || !empty($editEsp['activo']) ? 'checked' : '' ?>/>
        Activa
      </label>
      <div class="flex flex-wrap gap-2 pt-1">
        <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-5 py-2.5 rounded-xl font-bold hover:bg-cyan-deep transition-colors">
          <span class="material-symbols-outlined"><?= $editEsp ? 'save' : 'add' ?></span>
          <?= $editEsp ? 'Guardar' : 'Crear' ?>
        </button>
        <?php if ($editEsp): ?>
          <a href="ajustes.php?tab=especialidades" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold border border-outline-variant bg-mist hover:border-light-cyan transition-colors">
            <span class="material-symbols-outlined">close</span>
            Cancelar
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
  <div class="xl:col-span-2 space-y-3">
    <h3 class="font-headline-md text-lg font-bold px-1">Catálogo</h3>
    <?php if ($especialidades === []): ?>
      <div class="bg-mist border border-dashed border-sage/70 rounded-2xl p-10 text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-4xl mb-2">menu_book</span>
        <p>Aún no hay especialidades en el catálogo.</p>
      </div>
    <?php endif; ?>
    <?php foreach ($especialidades as $e): ?>
      <article class="bg-mist border border-sage/50 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-sage/50 text-teal flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined">local_hospital</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <h4 class="font-bold text-on-background"><?= htmlspecialchars((string) $e['nombre'], ENT_QUOTES, 'UTF-8') ?></h4>
            <?php if (!empty($e['activo'])): ?>
              <span class="text-xs bg-mint/50 text-teal px-2 py-0.5 rounded-full font-bold">Activa</span>
            <?php else: ?>
              <span class="text-xs bg-surface-container-high text-on-surface-variant px-2 py-0.5 rounded-full font-bold">Inactiva</span>
            <?php endif; ?>
          </div>
          <?php if (!empty($e['descripcion'])): ?>
            <p class="text-sm text-on-surface-variant mt-1"><?= htmlspecialchars((string) $e['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
        </div>
        <div class="flex gap-2 shrink-0">
          <a class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl border border-light-cyan/40 text-cyan-deep font-bold text-sm hover:bg-light-cyan hover:text-white transition-colors" href="ajustes.php?tab=especialidades&edit_esp=<?= (int) $e['id'] ?>">
            <span class="material-symbols-outlined text-lg">edit</span> Editar
          </a>
          <form method="post" onsubmit="return confirm('¿Eliminar o desactivar esta especialidad?');">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
            <input type="hidden" name="action" value="especialidad_delete"/>
            <input type="hidden" name="id" value="<?= (int) $e['id'] ?>"/>
            <button type="submit" class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl border border-error/30 text-error font-bold text-sm hover:bg-error hover:text-white transition-colors">
              <span class="material-symbols-outlined text-lg">delete</span> Eliminar
            </button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
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
})();
</script>
<?php require __DIR__ . '/includes/admin_chrome_end.php'; ?>
