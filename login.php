<?php
/**
 * Login — diseño compartido + auth seguro (bcrypt / PDO / CSRF)
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

auth_start();

if (auth_check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['_csrf'] ?? '');
    $emailValue = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!auth_csrf_validate($token)) {
        $error = 'Sesión de formulario inválida. Recarga la página e inténtalo de nuevo.';
    } else {
        $result = auth_attempt($emailValue, $password);
        if ($result['ok']) {
            header('Location: dashboard.php');
            exit;
        }
        $error = $result['message'];
    }
}

$csrf = auth_csrf_token();
$pageTitleAuth = 'MiDoc - Inicio de Sesión';
$authHeading = 'Bienvenido de nuevo';
$authSubheading = 'Ingresa tus credenciales para acceder al sistema.';

ob_start();
?>
<?php if ($error !== ''): ?>
<p class="mb-4 font-body-md text-body-md text-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<form class="space-y-6" method="post" action="login.php" autocomplete="on">
<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"/>
<div>
<label class="block font-label-caps text-label-caps text-on-surface-variant mb-2 uppercase" for="email">Correo</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">mail</span>
<input class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors font-body-md text-on-surface" id="email" name="email" placeholder="tu@correo.com" type="email" required autocomplete="username" value="<?= htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8') ?>"/>
</div>
</div>
<div>
<div class="flex flex-wrap justify-between items-center gap-2 mb-2">
<label class="block font-label-caps text-label-caps text-on-surface-variant uppercase" for="password">Contraseña</label>
<a class="font-label-caps text-label-caps text-primary hover:text-primary-container transition-colors" href="recuperar.php">¿Olvidaste tu contraseña?</a>
</div>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">lock</span>
<input class="w-full pl-12 pr-12 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors font-body-md text-on-surface" id="password" name="password" placeholder="••••••••" type="password" required autocomplete="current-password"/>
<button type="button" id="toggle-password" class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 inline-flex items-center justify-center rounded-lg text-outline hover:text-primary hover:bg-surface-container-high transition-colors" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar contraseña">
<span class="material-symbols-outlined text-[22px]" id="toggle-password-icon">visibility</span>
</button>
</div>
</div>
<div class="pt-4">
<button class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-brand-yellow text-on-secondary-fixed font-body-lg text-body-lg font-medium rounded-lg hover:bg-brand-orange hover:text-white transition-colors active:scale-[0.98]" type="submit">
<span>Acceder</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">arrow_forward</span>
</button>
</div>
</form>
<?php
$authBody = ob_get_clean();
$authScripts = <<<'HTML'
<script>
(function () {
  var input = document.getElementById('password');
  var btn = document.getElementById('toggle-password');
  var icon = document.getElementById('toggle-password-icon');
  if (!input || !btn || !icon) return;
  btn.addEventListener('click', function () {
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.textContent = show ? 'visibility_off' : 'visibility';
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    btn.setAttribute('title', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
  });
})();
</script>
HTML;

require __DIR__ . '/includes/auth_shell.php';
