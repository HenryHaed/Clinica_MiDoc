<?php
/**
 * Login — diseño login.html + auth seguro (bcrypt / PDO / CSRF)
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/theme.php';

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

$year = (int) date('Y');
$csrf = auth_csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
<meta name="theme-color" content="#D9EEE1"/>
<title>MiDoc - Inicio de Sesión</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": <?= theme_colors_json() ?>,
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "section-padding-desktop": "80px",
                      "section-padding-mobile": "32px",
                      "gutter": "24px",
                      "container-max": "1280px",
                      "unit": "8px"
              },
              "fontFamily": {
                      "body-md": ["Inter"],
                      "body-lg": ["Inter"],
                      "label-caps": ["Inter"],
                      "headline-md": ["Lexend"],
                      "display-lg-mobile": ["Lexend"],
                      "display-lg": ["Lexend"]
              },
              "fontSize": {
                      "body-md": ["16px", { "lineHeight": "1.5", "fontWeight": "400" }],
                      "body-lg": ["18px", { "lineHeight": "1.6", "fontWeight": "400" }],
                      "label-caps": ["12px", { "lineHeight": "1", "letterSpacing": "0.1em", "fontWeight": "700" }],
                      "headline-md": ["24px", { "lineHeight": "1.3", "fontWeight": "500" }],
                      "display-lg-mobile": ["32px", { "lineHeight": "1.2", "fontWeight": "600" }],
                      "display-lg": ["48px", { "lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "600" }]
              }
      },
          },
        }
</script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
</style>
</head>
<body class="bg-background text-on-background font-body-md min-h-[100svh] flex items-center justify-center m-0 p-0 overflow-x-hidden overflow-y-auto">
<main class="w-full min-h-[100svh] flex flex-col md:flex-row bg-surface">
<section class="hidden md:flex w-full md:w-[60%] min-h-[100svh] relative overflow-hidden bg-surface-container-low">
<img alt="" class="absolute inset-0 w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCDO8ecdkiysR148fbWhEbCpNwykHabRAkpChBWV5vD5-KmhdbAB3PGuql9acwT4C-4GSESRInaSv4aL12Q8mgwN-kqtRUIzrRyFKRiz8hcO4Gxg1lCLjo5NvEy52DoBLW0-tLhqIRjcva8rzvCbJF1ZTUM3t9RFMKcFXu7d8WleVypEm1VjS_HwR6DJbdrXwTK9Jx2MntwEPLEnhKoXVubqjehkC9xMMObUg-PkVZhNigQmsfd2LTs"/>
<div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(to right, rgba(217,238,225,0.45) 0%, rgba(249,212,35,0.22) 30%, rgba(255,145,0,0.20) 48%, rgba(22,154,138,0.22) 70%, rgba(217,238,225,0.40) 100%);"></div>
</section>
<section class="w-full md:w-[40%] min-h-[100svh] flex flex-col justify-center px-5 py-10 sm:px-section-padding-mobile md:px-section-padding-desktop bg-mist z-10 relative md:shadow-[-10px_0_30px_rgba(22,154,138,0.10)]">
<div class="max-w-md w-full mx-auto">
<header class="mb-8 md:mb-12">
<a href="index.php" class="inline-block mb-2 hover:opacity-90"><?= brand_mark('MiDoc', 'h-10 w-10 rounded-md object-cover shrink-0', 'font-headline-md text-headline-md font-bold text-primary tracking-tight') ?></a>
<h2 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-on-surface mb-3 md:mb-4">Bienvenido de nuevo</h2>
<p class="font-body-md text-body-md text-on-surface-variant">Ingresa tus credenciales para acceder al sistema.</p>
</header>
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
<a class="font-label-caps text-label-caps text-primary hover:text-primary-container transition-colors" href="#">¿Olvidaste tu contraseña?</a>
</div>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">lock</span>
<input class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors font-body-md text-on-surface" id="password" name="password" placeholder="••••••••" type="password" required autocomplete="current-password"/>
</div>
</div>
<div class="pt-4">
<button class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-brand-yellow text-on-secondary-fixed font-body-lg text-body-lg font-medium rounded-lg hover:bg-brand-orange hover:text-white transition-colors active:scale-[0.98]" type="submit">
<span>Acceder</span>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">arrow_forward</span>
</button>
</div>
</form>
<footer class="mt-12 pt-8 border-t border-outline-variant/30 text-center">
<p class="font-label-caps text-label-caps text-outline">© <?= $year ?> MiDoc Systems. Todos los derechos reservados.</p>
<p class="mt-3"><a href="index.php" class="font-label-caps text-label-caps text-primary hover:underline">← Volver al sitio</a></p>
</footer>
</div>
</section>
</main>
</body>
</html>
