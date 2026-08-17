<?php
/**
 * Chrome compartido del panel admin (head + sidebar desktop + header).
 * Requiere: auth iniciado, $user, $navActive, $pageTitleAdmin (opcional), $roleSubtitle (opcional)
 */
declare(strict_types=1);

require_once __DIR__ . '/theme.php';

assert(isset($user) && is_array($user));

$nombre = $user['nombre'];
$rolSlug = $user['rol_slug'];
$rolNombre = $user['rol_nombre'];
$email = $user['email'];
$navActive = $navActive ?? 'dashboard';

$roleSubtitles = [
    'super_admin' => 'Control total del sistema',
    'admin' => 'Gestión operativa',
    'doctor' => 'Área clínica',
    'enfermero' => 'Apoyo clínico',
    'recepcion' => 'Atención al paciente',
];
$roleSubtitle = $roleSubtitle ?? ($roleSubtitles[$rolSlug] ?? 'Panel');

/** @var array<int, array{href:string,icon:string,label:string,cap:string,key:string,short?:string}> */
$navItems = [
    ['href' => 'dashboard.php', 'icon' => 'dashboard', 'label' => 'Dashboard', 'short' => 'Inicio', 'cap' => 'dashboard', 'key' => 'dashboard'],
    ['href' => '#', 'icon' => 'calendar_today', 'label' => 'Citas', 'short' => 'Citas', 'cap' => 'citas', 'key' => 'citas'],
    ['href' => '#', 'icon' => 'group', 'label' => 'Pacientes', 'short' => 'Pacientes', 'cap' => 'pacientes', 'key' => 'pacientes'],
    ['href' => '#', 'icon' => 'medical_services', 'label' => 'Clínico', 'short' => 'Clínico', 'cap' => 'clinico', 'key' => 'clinico'],
    ['href' => 'ajustes.php?tab=contenido', 'icon' => 'web', 'label' => 'CMS Landing', 'short' => 'CMS', 'cap' => 'cms', 'key' => 'cms'],
    ['href' => '#', 'icon' => 'manage_accounts', 'label' => 'Usuarios', 'short' => 'Usuarios', 'cap' => 'usuarios', 'key' => 'usuarios'],
    ['href' => 'ajustes.php', 'icon' => 'settings', 'label' => 'Ajustes del sistema', 'short' => 'Ajustes', 'cap' => 'config', 'key' => 'config'],
];

$allowedNav = [];
foreach ($navItems as $item) {
    if (auth_can($item['cap'])) {
        $allowedNav[] = $item;
    }
}

$preferOrder = ['dashboard', 'citas', 'pacientes', 'clinico', 'config', 'cms', 'usuarios'];
usort($allowedNav, static function ($a, $b) use ($preferOrder) {
    $ia = array_search($a['key'], $preferOrder, true);
    $ib = array_search($b['key'], $preferOrder, true);
    $ia = $ia === false ? 99 : $ia;
    $ib = $ib === false ? 99 : $ib;
    return $ia <=> $ib;
});

if (count($allowedNav) <= 5) {
    $adminBottomItems = $allowedNav;
    $adminMoreItems = [];
} else {
    $adminBottomItems = array_slice($allowedNav, 0, 4);
    $adminMoreItems = array_slice($allowedNav, 4);
}

$pageTitleAdmin = $pageTitleAdmin ?? 'MiDoc Admin';

if (!function_exists('admin_item_active')) {
    function admin_item_active(string $navActive, array $item): bool
    {
        return $navActive === $item['key']
            || ($navActive === 'ajustes' && (
                ($item['key'] === 'cms' && (string) ($_GET['tab'] ?? '') === 'contenido')
                || ($item['key'] === 'config' && (string) ($_GET['tab'] ?? '') !== 'contenido')
            ));
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
<meta name="theme-color" content="#D9EEE1"/>
<title><?= htmlspecialchars($pageTitleAdmin, ENT_QUOTES, 'UTF-8') ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Lexend:wght@500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
                }
            }
        }
</script>
<style>
  .admin-bottom-nav { padding-bottom: max(0.35rem, env(safe-area-inset-bottom, 0px)); }
  .admin-bottom-nav a.is-active { color: #003E67; }
  .admin-bottom-nav a.is-active .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 500; }
</style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex antialiased overflow-x-hidden">
<nav class="hidden lg:flex w-64 h-full flex-col border-r border-sage/50 fixed left-0 top-0 bottom-0 z-40 bg-mist font-body-md text-body-md text-primary">
<div class="p-6 border-b border-outline-variant">
<h1 class="font-headline-md text-headline-md font-bold text-primary"><?= brand_mark('MiDoc Admin', 'h-9 w-9 rounded-md object-cover shrink-0', 'font-headline-md text-headline-md font-bold text-primary') ?></h1>
<p class="text-on-surface-variant font-label-caps text-label-caps mt-1"><?= htmlspecialchars($roleSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<div class="flex-1 py-6 flex flex-col gap-2 px-4 overflow-y-auto">
<?php foreach ($allowedNav as $item): ?>
  <?php $isActive = admin_item_active($navActive, $item); ?>
  <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 <?= $isActive ? 'text-primary font-bold border-r-4 border-primary bg-surface-container-high' : 'text-on-surface-variant hover:bg-surface-container-highest' ?>" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
    <span class="material-symbols-outlined"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
    <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
  </a>
<?php endforeach; ?>
</div>
<div class="p-4 border-t border-outline-variant">
<a class="flex items-center gap-3 px-4 py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-highest transition-colors mb-2" href="index.php" target="_blank">
<span class="material-symbols-outlined">public</span>
<span>Ver sitio</span>
</a>
<a class="flex items-center gap-3 px-4 py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-highest transition-colors" href="dashboard.php?logout=1">
<span class="material-symbols-outlined">logout</span>
<span>Cerrar sesión</span>
</a>
</div>
</nav>

<div class="flex-1 w-full lg:ml-64 flex flex-col min-h-screen">
<header class="flex justify-between items-center w-full px-4 sm:px-6 lg:px-8 py-3 h-14 lg:h-16 border-b border-sage/40 bg-mist/90 backdrop-blur-md fixed top-0 left-0 right-0 lg:left-64 z-30 font-label-caps text-label-caps">
<div class="flex items-center gap-3 min-w-0">
  <img src="<?= htmlspecialchars(brand_logo_src(), ENT_QUOTES, 'UTF-8') ?>" alt="MiDoc" class="lg:hidden h-8 w-8 rounded-md object-cover shrink-0"/>
  <h2 class="font-headline-md text-base sm:text-lg lg:text-headline-md font-bold text-primary truncate"><?= htmlspecialchars($headerTitle ?? 'Panel', ENT_QUOTES, 'UTF-8') ?></h2>
</div>
<div class="flex items-center gap-2 sm:gap-4 shrink-0">
  <div class="hidden md:block text-right">
    <p class="font-body-md text-sm font-bold text-on-background normal-case tracking-normal"><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></p>
    <p class="text-on-surface-variant"><?= htmlspecialchars($rolNombre, ENT_QUOTES, 'UTF-8') ?></p>
  </div>
  <span class="hidden sm:inline-flex items-center rounded-full bg-primary-container/25 text-primary px-2.5 py-1 font-label-caps text-label-caps uppercase"><?= htmlspecialchars($rolSlug, ENT_QUOTES, 'UTF-8') ?></span>
  <a href="dashboard.php?logout=1" class="lg:hidden w-11 h-11 inline-flex items-center justify-center rounded-full text-on-surface-variant hover:bg-surface-container-high" aria-label="Cerrar sesión">
    <span class="material-symbols-outlined">logout</span>
  </a>
</div>
</header>
<main class="flex-1 mt-14 lg:mt-16 p-4 sm:p-6 lg:p-section-padding-desktop pb-28 lg:pb-section-padding-desktop">
<div class="max-w-container-max mx-auto space-y-6 lg:space-y-8">
