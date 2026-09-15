<?php
/**
 * Usuarios del panel — listado, edición de perfil y contraseñas.
 *
 * Permisos:
 * - super_admin: edita a todos (incluido él). Su propia contraseña exige la actual.
 * - admin: edita al resto, excepto super_admin y otros admin (incluido él).
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/usuarios.php';
require_once __DIR__ . '/includes/theme.php';

auth_start();

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: login.php');
    exit;
}

auth_require(...AUTH_PANEL_ROLES);

if (!auth_can('usuarios')) {
    http_response_code(403);
    echo 'No tienes permiso para gestionar usuarios.';
    exit;
}

$user = auth_user();
assert($user !== null);

usuarios_ensure_profile_columns();

$navActive = 'usuarios';
$pageTitleAdmin = 'Usuarios — MiDoc Admin';
$roleSubtitle = auth_has_role('super_admin')
    ? 'Gestión de cuentas y contraseñas'
    : 'Edición de cuentas operativas';

function usuarios_redirect(string $msg, bool $ok = true, ?int $editId = null, bool $nuevo = false): never
{
    $params = [$ok ? 'ok' : 'err' => $msg];
    if ($editId !== null && $editId > 0) {
        $params['edit'] = $editId;
    } elseif ($nuevo) {
        $params['nuevo'] = 1;
    }
    header('Location: usuarios.php?' . http_build_query($params));
    exit;
}

$flashOk = isset($_GET['ok']) ? (string) $_GET['ok'] : null;
$flashErr = isset($_GET['err']) ? (string) $_GET['err'] : null;
$creatableRoles = roles_creatable_for($user);
$showNuevo = isset($_GET['nuevo']) && $creatableRoles !== [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_csrf_validate($_POST['_csrf'] ?? null)) {
        usuarios_redirect('Token CSRF inválido. Recarga la página.', false);
    }

    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'create_user') {
            if ($creatableRoles === []) {
                throw new RuntimeException('No tienes permiso para crear usuarios.');
            }
            $rolSlug = trim((string) ($_POST['rol_slug'] ?? ''));
            if (!auth_can_create_role($user, $rolSlug)) {
                throw new RuntimeException('No puedes asignar ese rol.');
            }

            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $apellidoPaterno = trim((string) ($_POST['apellido_paterno'] ?? ''));
            $apellidoMaterno = trim((string) ($_POST['apellido_materno'] ?? ''));
            $datosExtras = trim((string) ($_POST['datos_extras'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            $password2 = (string) ($_POST['password2'] ?? '');
            $activo = isset($_POST['activo']) ? 1 : 0;

            usuario_validate_password($password, $password2);

            $newId = usuario_create([
                'rol_slug' => $rolSlug,
                'email' => $email,
                'password' => $password,
                'nombre' => $nombre,
                'apellido_paterno' => $apellidoPaterno !== '' ? $apellidoPaterno : null,
                'apellido_materno' => $apellidoMaterno !== '' ? $apellidoMaterno : null,
                'datos_extras' => $datosExtras !== '' ? $datosExtras : null,
                'activo' => $activo,
            ]);

            usuarios_redirect('Usuario creado correctamente.', true, $newId);
        }

        $targetId = (int) ($_POST['id'] ?? 0);
        $target = usuario_find($targetId);

        if ($target === null) {
            throw new RuntimeException('Usuario no encontrado.');
        }
        if (!auth_can_manage_user($user, $target)) {
            throw new RuntimeException('No tienes permiso para modificar este usuario.');
        }

        if ($action === 'save_profile') {
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $apellidoPaterno = trim((string) ($_POST['apellido_paterno'] ?? ''));
            $apellidoMaterno = trim((string) ($_POST['apellido_materno'] ?? ''));
            $datosExtras = trim((string) ($_POST['datos_extras'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $isSelf = (int) $target['id'] === (int) $user['id'];
            $activo = $isSelf ? 1 : (isset($_POST['activo']) ? 1 : 0);

            if ($nombre === '') {
                throw new RuntimeException('El nombre es obligatorio.');
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Correo electrónico no válido.');
            }
            if (usuario_email_taken($email, (int) $target['id'])) {
                throw new RuntimeException('Ese correo ya está en uso por otro usuario.');
            }

            $payload = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellidoPaterno !== '' ? $apellidoPaterno : null,
                'apellido_materno' => $apellidoMaterno !== '' ? $apellidoMaterno : null,
                'datos_extras' => $datosExtras !== '' ? $datosExtras : null,
                'email' => $email,
                'activo' => $activo,
            ];
            usuario_update_profile((int) $target['id'], $payload);

            $fresh = usuario_find((int) $target['id']);
            if ($fresh !== null) {
                usuario_refresh_session_if_self($fresh);
            }

            usuarios_redirect('Datos del usuario actualizados.', true, (int) $target['id']);
        }

        if ($action === 'reset_password') {
            $password = (string) ($_POST['password'] ?? '');
            $password2 = (string) ($_POST['password2'] ?? '');
            usuario_validate_password($password, $password2);

            $isSelf = (int) $target['id'] === (int) $user['id'];
            if ($isSelf) {
                $current = (string) ($_POST['password_actual'] ?? '');
                if ($current === '') {
                    throw new RuntimeException('Debes confirmar tu contraseña actual.');
                }
                $hash = (string) ($target['password_hash'] ?? '');
                if ($hash === '' || !password_verify($current, $hash)) {
                    throw new RuntimeException('La contraseña actual no es correcta.');
                }
            }

            usuario_set_password((int) $target['id'], $password);
            usuarios_redirect('Contraseña actualizada correctamente.', true, (int) $target['id']);
        }

        throw new RuntimeException('Acción no reconocida.');
    } catch (Throwable $e) {
        $failEdit = (int) ($_POST['id'] ?? 0);
        $isCreate = ((string) ($_POST['action'] ?? '')) === 'create_user';
        usuarios_redirect($e->getMessage(), false, $failEdit > 0 ? $failEdit : null, $isCreate);
    }
}

$usuarios = usuarios_list_panel();
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editUser = $editId > 0 ? usuario_find($editId) : null;
if ($editUser !== null && !auth_can_manage_user($user, $editUser)) {
    $flashErr = $flashErr ?? 'Solo puedes ver este usuario; no tienes permiso para editarlo.';
    $editUser = null;
    $editId = 0;
}
if ($editUser !== null) {
    $showNuevo = false;
}

$csrf = auth_csrf_token();
$isSuper = auth_has_role('super_admin');
$showAside = $editUser !== null || $showNuevo;

require __DIR__ . '/includes/admin_chrome_start.php';
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
      <p class="font-label-caps text-label-caps text-cyan-deep">Cuentas del panel</p>
      <h2 class="font-headline-lg text-2xl font-bold text-on-background">Usuarios</h2>
      <p class="font-body-md text-on-surface-variant mt-1 max-w-2xl">
        <?php if ($isSuper): ?>
          Puedes crear administradores y cuentas operativas, y editar a todos. El super usuario es único.
        <?php else: ?>
          Puedes crear y editar cuentas operativas (doctor, enfermería, recepción). No puedes gestionar admins ni al super usuario.
        <?php endif; ?>
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <?php if ($creatableRoles !== [] && !$showNuevo): ?>
        <a href="usuarios.php?nuevo=1" class="inline-flex items-center gap-2 bg-light-cyan text-white px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-cyan-deep transition-colors">
          <span class="material-symbols-outlined">person_add</span>
          Nuevo usuario
        </a>
      <?php endif; ?>
      <div class="inline-flex items-center gap-2 rounded-xl border border-light-cyan/30 bg-mist px-3 py-2 text-sm text-on-surface-variant">
        <span class="material-symbols-outlined text-cyan-deep text-xl">group</span>
        <span><?= count($usuarios) ?> usuario(s)</span>
      </div>
    </div>
  </div>

  <?php if ($flashOk): ?>
    <div class="rounded-2xl border border-mint bg-mint/40 text-teal px-4 py-3 text-sm font-bold flex items-center gap-2">
      <span class="material-symbols-outlined">check_circle</span>
      <?= htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
  <?php if ($flashErr): ?>
    <div class="rounded-2xl border border-error/30 bg-error-container/40 text-error px-4 py-3 text-sm font-bold flex items-center gap-2">
      <span class="material-symbols-outlined">error</span>
      <?= htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 <?= $showAside ? 'xl:grid-cols-5' : '' ?> gap-6">
    <section class="<?= $showAside ? 'xl:col-span-3' : '' ?> space-y-3">
      <?php foreach ($usuarios as $row): ?>
        <?php
          $canEdit = auth_can_manage_user($user, $row);
          $isSelfRow = (int) $row['id'] === (int) $user['id'];
          $display = usuario_display_name($row);
          $isActiveEdit = $editUser && (int) $editUser['id'] === (int) $row['id'];
        ?>
        <article class="bg-mist border rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 <?= $isActiveEdit ? 'border-light-cyan shadow-sm' : 'border-sage/50' ?>">
          <div class="w-12 h-12 rounded-full bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">
              <?= (string) $row['rol_slug'] === 'super_admin' ? 'shield_person' : ((string) $row['rol_slug'] === 'admin' ? 'admin_panel_settings' : 'person') ?>
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-0.5">
              <h3 class="font-bold text-on-background truncate"><?= htmlspecialchars($display, ENT_QUOTES, 'UTF-8') ?></h3>
              <?php if ($isSelfRow): ?>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-brand-yellow/40 text-on-secondary-fixed">Tú</span>
              <?php endif; ?>
              <?php if (!empty($row['activo'])): ?>
                <span class="text-xs bg-mint/50 text-teal px-2 py-0.5 rounded-full font-bold">Activo</span>
              <?php else: ?>
                <span class="text-xs bg-surface-container-high text-on-surface-variant px-2 py-0.5 rounded-full font-bold">Inactivo</span>
              <?php endif; ?>
            </div>
            <p class="text-sm text-on-surface-variant truncate"><?= htmlspecialchars((string) $row['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-xs text-on-surface-variant mt-1">
              <?= htmlspecialchars((string) $row['rol_nombre'], ENT_QUOTES, 'UTF-8') ?>
              <?php if (!empty($row['last_login_at'])): ?>
                · último acceso <?= htmlspecialchars((string) $row['last_login_at'], ENT_QUOTES, 'UTF-8') ?>
              <?php endif; ?>
            </p>
          </div>
          <div class="shrink-0">
            <?php if ($canEdit): ?>
              <a href="usuarios.php?edit=<?= (int) $row['id'] ?>"
                 class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl border border-light-cyan/40 text-cyan-deep font-bold text-sm hover:bg-light-cyan hover:text-white transition-colors">
                <span class="material-symbols-outlined text-lg">edit</span>
                <?= $isSelfRow ? 'Mi perfil' : 'Editar' ?>
              </a>
            <?php else: ?>
              <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-sage/50 text-on-surface-variant text-sm font-bold">
                <span class="material-symbols-outlined text-lg">lock</span>
                Solo lectura
              </span>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

    <?php if ($showNuevo): ?>
      <aside class="xl:col-span-2 space-y-6">
        <div class="bg-mist border border-sage/50 rounded-2xl p-6 space-y-4">
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined">person_add</span>
              </span>
              <div class="min-w-0">
                <h3 class="font-headline-md text-lg font-bold">Nuevo usuario</h3>
                <p class="text-xs text-on-surface-variant">
                  <?= $isSuper ? 'Puedes crear admins y roles operativos.' : 'Roles operativos del panel.' ?>
                </p>
              </div>
            </div>
            <a href="usuarios.php" class="text-on-surface-variant hover:text-cyan-deep" title="Cerrar">
              <span class="material-symbols-outlined">close</span>
            </a>
          </div>

          <form method="post" class="space-y-4" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
            <input type="hidden" name="action" value="create_user"/>

            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Rol</label>
              <select name="rol_slug" required class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan">
                <?php foreach ($creatableRoles as $rol): ?>
                  <option value="<?= htmlspecialchars((string) $rol['slug'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars((string) $rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Nombre</label>
              <input name="nombre" required class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-on-surface-variant">Apellido paterno</label>
                <input name="apellido_paterno" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
              </div>
              <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-on-surface-variant">Apellido materno</label>
                <input name="apellido_materno" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
              </div>
            </div>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Correo</label>
              <input type="email" name="email" required class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            </div>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Datos extras</label>
              <textarea name="datos_extras" rows="2" class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan" placeholder="Opcional"></textarea>
            </div>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Contraseña</label>
              <input type="password" name="password" required minlength="8" autocomplete="new-password"
                     class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            </div>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Confirmar contraseña</label>
              <input type="password" name="password2" required minlength="8" autocomplete="new-password"
                     class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            </div>
            <label class="flex items-center gap-3 rounded-xl border border-sage/40 bg-surface-container-lowest/50 px-3 py-2.5 text-sm cursor-pointer">
              <input type="checkbox" name="activo" value="1" checked class="rounded border-outline-variant text-light-cyan focus:ring-light-cyan"/>
              Cuenta activa
            </label>
            <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-5 py-2.5 rounded-xl font-bold hover:bg-cyan-deep transition-colors">
              <span class="material-symbols-outlined">person_add</span>
              Crear usuario
            </button>
          </form>
        </div>
      </aside>
    <?php elseif ($editUser): ?>
      <?php $isSelf = (int) $editUser['id'] === (int) $user['id']; ?>
      <aside class="xl:col-span-2 space-y-6">
        <div class="bg-mist border border-sage/50 rounded-2xl p-6 space-y-4">
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined">badge</span>
              </span>
              <div class="min-w-0">
                <h3 class="font-headline-md text-lg font-bold truncate">Datos personales</h3>
                <p class="text-xs text-on-surface-variant truncate"><?= htmlspecialchars((string) $editUser['rol_nombre'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </div>
            <a href="usuarios.php" class="text-on-surface-variant hover:text-cyan-deep" title="Cerrar">
              <span class="material-symbols-outlined">close</span>
            </a>
          </div>

          <form method="post" class="space-y-4" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
            <input type="hidden" name="action" value="save_profile"/>
            <input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>"/>

            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Nombre</label>
              <input name="nombre" required value="<?= htmlspecialchars((string) $editUser['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-on-surface-variant">Apellido paterno</label>
                <input name="apellido_paterno" value="<?= htmlspecialchars((string) ($editUser['apellido_paterno'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
              </div>
              <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-on-surface-variant">Apellido materno</label>
                <input name="apellido_materno" value="<?= htmlspecialchars((string) ($editUser['apellido_materno'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
              </div>
            </div>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Correo</label>
              <input type="email" name="email" required value="<?= htmlspecialchars((string) $editUser['email'], ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            </div>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Datos extras</label>
              <textarea name="datos_extras" rows="3" placeholder="Notas, teléfono interno, etc."
                        class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"><?= htmlspecialchars((string) ($editUser['datos_extras'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <?php if (!$isSelf): ?>
              <label class="flex items-center gap-3 rounded-xl border border-sage/40 bg-surface-container-lowest/50 px-3 py-2.5 text-sm cursor-pointer">
                <input type="checkbox" name="activo" value="1" class="rounded border-outline-variant text-light-cyan focus:ring-light-cyan" <?= !empty($editUser['activo']) ? 'checked' : '' ?>/>
                Cuenta activa (puede iniciar sesión)
              </label>
            <?php endif; ?>
            <button type="submit" class="inline-flex items-center gap-2 bg-light-cyan text-white px-5 py-2.5 rounded-xl font-bold hover:bg-cyan-deep transition-colors">
              <span class="material-symbols-outlined">save</span>
              Guardar datos
            </button>
          </form>
        </div>

        <div class="bg-mist border border-sage/50 rounded-2xl p-6 space-y-4">
          <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-light-cyan/15 text-cyan-deep flex items-center justify-center">
              <span class="material-symbols-outlined">lock_reset</span>
            </span>
            <div>
              <h3 class="font-headline-md text-lg font-bold"><?= $isSelf ? 'Cambiar mi contraseña' : 'Restablecer contraseña' ?></h3>
              <p class="text-xs text-on-surface-variant">
                <?= $isSelf
                  ? 'Confirma tu contraseña actual y define una nueva (mín. 8 caracteres).'
                  : 'Define una nueva contraseña para este usuario (mín. 8 caracteres).' ?>
              </p>
            </div>
          </div>

          <form method="post" class="space-y-4" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
            <input type="hidden" name="action" value="reset_password"/>
            <input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>"/>

            <?php if ($isSelf): ?>
              <div class="space-y-1">
                <label class="font-label-caps text-label-caps text-on-surface-variant">Contraseña actual</label>
                <input type="password" name="password_actual" required autocomplete="current-password"
                       class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
              </div>
            <?php endif; ?>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Nueva contraseña</label>
              <input type="password" name="password" required minlength="8" autocomplete="new-password"
                     class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            </div>
            <div class="space-y-1">
              <label class="font-label-caps text-label-caps text-on-surface-variant">Confirmar nueva contraseña</label>
              <input type="password" name="password2" required minlength="8" autocomplete="new-password"
                     class="w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3 py-2.5 focus:border-light-cyan focus:ring-1 focus:ring-light-cyan"/>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 border border-light-cyan/50 text-cyan-deep px-5 py-2.5 rounded-xl font-bold hover:bg-light-cyan hover:text-white transition-colors">
              <span class="material-symbols-outlined">password</span>
              <?= $isSelf ? 'Actualizar mi contraseña' : 'Guardar nueva contraseña' ?>
            </button>
          </form>
        </div>
      </aside>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/admin_chrome_end.php'; ?>
