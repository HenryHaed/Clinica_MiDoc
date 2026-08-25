<?php
/**
 * Recuperar contraseña — mismo layout que login (código por correo SMTP).
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/password_reset.php';

auth_start();

if (auth_check()) {
    header('Location: dashboard.php');
    exit;
}

$step = (string) ($_GET['step'] ?? 'email');
if (!in_array($step, ['email', 'code', 'password', 'done'], true)) {
    $step = 'email';
}

$session = $_SESSION['pwreset'] ?? null;
if ($step === 'password' && !(is_array($session) && !empty($session['verified']))) {
    $step = 'email';
}
if ($step === 'code' && is_array($session) && !empty($session['verified'])) {
    $step = 'password';
}

$error = '';
$okMsg = '';
$emailValue = is_array($session) ? (string) ($session['email'] ?? '') : '';

if (isset($_GET['restart'])) {
    password_reset_clear();
    header('Location: recuperar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_csrf'] ?? '');
    $action = (string) ($_POST['action'] ?? '');

    if (!auth_csrf_validate($token)) {
        $error = 'Sesión de formulario inválida. Recarga la página e inténtalo de nuevo.';
    } elseif ($action === 'request_code') {
        $emailValue = trim((string) ($_POST['email'] ?? ''));
        $result = password_reset_request($emailValue);
        if ($result['ok']) {
            $okMsg = $result['message'];
            $step = $result['step'] ?? 'code';
            header('Location: recuperar.php?step=code');
            exit;
        }
        $error = $result['message'];
        $step = 'email';
    } elseif ($action === 'verify_code') {
        $emailValue = trim((string) ($_POST['email'] ?? $emailValue));
        $code = (string) ($_POST['code'] ?? '');
        $result = password_reset_verify_code($emailValue, $code);
        if ($result['ok']) {
            header('Location: recuperar.php?step=password');
            exit;
        }
        $error = $result['message'];
        $step = 'code';
    } elseif ($action === 'set_password') {
        $password = (string) ($_POST['password'] ?? '');
        $password2 = (string) ($_POST['password2'] ?? '');
        $result = password_reset_set_password($password, $password2);
        if ($result['ok']) {
            header('Location: recuperar.php?step=done');
            exit;
        }
        $error = $result['message'];
        $step = $result['step'] ?? 'password';
        if ($step === 'email') {
            header('Location: recuperar.php?step=email');
            exit;
        }
    }
}

$csrf = auth_csrf_token();

if ($step === 'email') {
    $pageTitleAuth = 'MiDoc - Recuperar contraseña';
    $authHeading = '¿Olvidaste tu contraseña?';
    $authSubheading = 'Ingresa el correo de tu cuenta y te enviaremos un código de verificación.';
} elseif ($step === 'code') {
    $pageTitleAuth = 'MiDoc - Verificar código';
    $authHeading = 'Revisa tu correo';
    $authSubheading = 'Escribe el código de 6 dígitos que enviamos a tu bandeja.';
} elseif ($step === 'password') {
    $pageTitleAuth = 'MiDoc - Nueva contraseña';
    $authHeading = 'Nueva contraseña';
    $authSubheading = 'Elige una contraseña segura de al menos 8 caracteres.';
} else {
    $pageTitleAuth = 'MiDoc - Contraseña actualizada';
    $authHeading = 'Listo';
    $authSubheading = 'Tu contraseña se actualizó correctamente.';
}

ob_start();
?>
<?php if ($error !== ''): ?>
<p class="mb-4 font-body-md text-body-md text-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<?php if ($okMsg !== ''): ?>
<p class="mb-4 font-body-md text-body-md text-primary" role="status"><?= htmlspecialchars($okMsg, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($step === 'email'): ?>
<form class="space-y-6" method="post" action="recuperar.php" autocomplete="on">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
  <input type="hidden" name="action" value="request_code"/>
  <div>
    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-2 uppercase" for="email">Correo</label>
    <div class="relative">
      <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">mail</span>
      <input class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors font-body-md text-on-surface" id="email" name="email" placeholder="tu@correo.com" type="email" required autocomplete="username" value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>"/>
    </div>
  </div>
  <div class="pt-2">
    <button class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-brand-yellow text-on-secondary-fixed font-body-lg text-body-lg font-medium rounded-lg hover:bg-brand-orange hover:text-white transition-colors active:scale-[0.98]" type="submit">
      <span>Enviar código</span>
      <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">send</span>
    </button>
  </div>
  <p class="text-center"><a href="login.php" class="font-label-caps text-label-caps text-primary hover:underline">← Volver al inicio de sesión</a></p>
</form>

<?php elseif ($step === 'code'): ?>
<form class="space-y-6" method="post" action="recuperar.php?step=code" autocomplete="one-time-code">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
  <input type="hidden" name="action" value="verify_code"/>
  <div>
    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-2 uppercase" for="email">Correo</label>
    <div class="relative">
      <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">mail</span>
      <input class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors font-body-md text-on-surface" id="email" name="email" type="email" required value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>" <?= $emailValue !== '' ? 'readonly' : '' ?>/>
    </div>
  </div>
  <div>
    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-2 uppercase" for="code">Código de 6 dígitos</label>
    <div class="relative">
      <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">pin</span>
      <input class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors font-body-md text-on-surface tracking-[0.35em] text-center text-xl font-bold" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required autocomplete="one-time-code"/>
    </div>
    <p class="mt-2 text-sm text-on-surface-variant">El código caduca en <?= (int) PASSWORD_RESET_TTL_MINUTES ?> minutos.</p>
  </div>
  <div class="pt-2 space-y-3">
    <button class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-brand-yellow text-on-secondary-fixed font-body-lg text-body-lg font-medium rounded-lg hover:bg-brand-orange hover:text-white transition-colors active:scale-[0.98]" type="submit">
      <span>Verificar código</span>
      <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified</span>
    </button>
    <a href="recuperar.php?restart=1" class="block text-center font-label-caps text-label-caps text-on-surface-variant hover:text-primary">Usar otro correo / reenviar</a>
    <p class="text-center"><a href="login.php" class="font-label-caps text-label-caps text-primary hover:underline">← Volver al inicio de sesión</a></p>
  </div>
</form>

<?php elseif ($step === 'password'): ?>
<form class="space-y-6" method="post" action="recuperar.php?step=password" autocomplete="off">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
  <input type="hidden" name="action" value="set_password"/>
  <div>
    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-2 uppercase" for="password">Nueva contraseña</label>
    <div class="relative">
      <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">lock</span>
      <input class="w-full pl-12 pr-12 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors font-body-md text-on-surface" id="password" name="password" type="password" required minlength="8" autocomplete="new-password"/>
      <button type="button" class="js-toggle-pass absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 inline-flex items-center justify-center rounded-lg text-outline hover:text-primary hover:bg-surface-container-high transition-colors" data-target="password" aria-label="Mostrar contraseña">
        <span class="material-symbols-outlined text-[22px]">visibility</span>
      </button>
    </div>
  </div>
  <div>
    <label class="block font-label-caps text-label-caps text-on-surface-variant mb-2 uppercase" for="password2">Confirmar contraseña</label>
    <div class="relative">
      <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">lock</span>
      <input class="w-full pl-12 pr-12 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors font-body-md text-on-surface" id="password2" name="password2" type="password" required minlength="8" autocomplete="new-password"/>
      <button type="button" class="js-toggle-pass absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 inline-flex items-center justify-center rounded-lg text-outline hover:text-primary hover:bg-surface-container-high transition-colors" data-target="password2" aria-label="Mostrar contraseña">
        <span class="material-symbols-outlined text-[22px]">visibility</span>
      </button>
    </div>
  </div>
  <div class="pt-2">
    <button class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-brand-yellow text-on-secondary-fixed font-body-lg text-body-lg font-medium rounded-lg hover:bg-brand-orange hover:text-white transition-colors active:scale-[0.98]" type="submit">
      <span>Guardar contraseña</span>
      <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">save</span>
    </button>
  </div>
</form>

<?php else: ?>
<div class="space-y-6">
  <div class="rounded-xl border border-primary/25 bg-primary-container/20 px-4 py-4 text-on-surface">
    <p class="font-body-md">Ya puedes entrar al panel con tu nueva contraseña.</p>
  </div>
  <a href="login.php" class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-brand-yellow text-on-secondary-fixed font-body-lg text-body-lg font-medium rounded-lg hover:bg-brand-orange hover:text-white transition-colors">
    <span>Ir a iniciar sesión</span>
    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">login</span>
  </a>
</div>
<?php endif; ?>
<?php
$authBody = ob_get_clean();

$authScripts = '';
if ($step === 'password') {
    $authScripts = <<<'HTML'
<script>
(function () {
  document.querySelectorAll('.js-toggle-pass').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-target');
      var input = id ? document.getElementById(id) : null;
      var icon = btn.querySelector('.material-symbols-outlined');
      if (!input || !icon) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      icon.textContent = show ? 'visibility_off' : 'visibility';
      btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
  });
})();
</script>
HTML;
}

require __DIR__ . '/includes/auth_shell.php';
