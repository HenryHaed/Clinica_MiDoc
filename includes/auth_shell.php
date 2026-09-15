<?php
/**
 * Shell visual compartido login / recuperar (imagen + panel derecho).
 * Variables: $pageTitleAuth, $authHeading, $authSubheading, $authBody (HTML), $authScripts (opcional)
 */
declare(strict_types=1);

require_once __DIR__ . '/theme.php';

$pageTitleAuth = $pageTitleAuth ?? 'MiDoc';
$authHeading = $authHeading ?? '';
$authSubheading = $authSubheading ?? '';
$authBody = $authBody ?? '';
$authScripts = $authScripts ?? '';
$year = (int) date('Y');
$loginSideImage = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCDO8ecdkiysR148fbWhEbCpNwykHabRAkpChBWV5vD5-KmhdbAB3PGuql9acwT4C-4GSESRInaSv4aL12Q8mgwN-kqtRUIzrRyFKRiz8hcO4Gxg1lCLjo5NvEy52DoBLW0-tLhqIRjcva8rzvCbJF1ZTUM3t9RFMKcFXu7d8WleVypEm1VjS_HwR6DJbdrXwTK9Jx2MntwEPLEnhKoXVubqjehkC9xMMObUg-PkVZhNigQmsfd2LTs';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
<meta name="theme-color" content="<?= htmlspecialchars(theme_color('mist', '#D9EEE1'), ENT_QUOTES, 'UTF-8') ?>"/>
<title><?= htmlspecialchars($pageTitleAuth, ENT_QUOTES, 'UTF-8') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="assets/css/style.css"/>
<style>
<?= theme_css_variables_block() ?>
</style>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
</style>
</head>
<body class="bg-background text-on-background font-body-md min-h-[100svh] flex items-center justify-center m-0 p-0 overflow-x-hidden overflow-y-auto">
<main class="w-full min-h-[100svh] flex flex-col md:flex-row bg-surface">
<section class="hidden md:flex w-full md:w-[60%] min-h-[100svh] relative overflow-hidden bg-surface-container-low">
<img alt="" class="absolute inset-0 w-full h-full object-cover" src="<?= htmlspecialchars($loginSideImage, ENT_QUOTES, 'UTF-8') ?>"/>
<div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(to right, rgba(217,238,225,0.45) 0%, rgba(249,212,35,0.22) 30%, rgba(255,145,0,0.20) 48%, rgba(22,154,138,0.22) 70%, rgba(217,238,225,0.40) 100%);"></div>
</section>
<section class="w-full md:w-[40%] min-h-[100svh] flex flex-col justify-center px-5 py-10 sm:px-section-padding-mobile md:px-section-padding-desktop bg-mist z-10 relative md:shadow-[-10px_0_30px_rgba(22,154,138,0.10)]">
<div class="max-w-md w-full mx-auto">
<header class="mb-8 md:mb-12">
<a href="index.php" class="inline-block mb-2 hover:opacity-90"><?= brand_mark('MiDoc', 'h-10 w-10 rounded-md object-cover shrink-0', 'font-headline-md text-headline-md font-bold text-primary tracking-tight') ?></a>
<h2 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-on-surface mb-3 md:mb-4"><?= htmlspecialchars($authHeading, ENT_QUOTES, 'UTF-8') ?></h2>
<p class="font-body-md text-body-md text-on-surface-variant"><?= htmlspecialchars($authSubheading, ENT_QUOTES, 'UTF-8') ?></p>
</header>
<?= $authBody ?>
<footer class="mt-12 pt-8 border-t border-outline-variant/30 text-center">
<p class="font-label-caps text-label-caps text-outline">© <?= $year ?> MiDoc Systems. Todos los derechos reservados.</p>
<p class="mt-3"><a href="index.php" class="font-label-caps text-label-caps text-primary hover:underline">← Volver al sitio</a></p>
</footer>
</div>
</section>
</main>
<?= $authScripts ?>
</body>
</html>
