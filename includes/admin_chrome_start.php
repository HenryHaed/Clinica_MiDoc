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

/**
 * Ítems listos (enlaces reales) vs próximos (sin módulo aún).
 *
 * @var list<array{href:string,icon:string,label:string,short:string,key:string,ready:bool,cap?:string,caps_any?:list<string>}>
 */
$navItems = [
    [
        'href' => 'dashboard.php',
        'icon' => 'dashboard',
        'label' => 'Dashboard',
        'short' => 'Inicio',
        'cap' => 'dashboard',
        'key' => 'dashboard',
        'ready' => true,
    ],
    [
        'href' => 'ajustes.php',
        'icon' => 'settings',
        'label' => 'Ajustes del sistema',
        'short' => 'Ajustes',
        'caps_any' => ['cms', 'config'],
        'key' => 'ajustes',
        'ready' => true,
    ],
    [
        'href' => '#',
        'icon' => 'calendar_today',
        'label' => 'Citas',
        'short' => 'Citas',
        'cap' => 'citas',
        'key' => 'citas',
        'ready' => false,
    ],
    [
        'href' => '#',
        'icon' => 'group',
        'label' => 'Pacientes',
        'short' => 'Pacientes',
        'cap' => 'pacientes',
        'key' => 'pacientes',
        'ready' => false,
    ],
    [
        'href' => '#',
        'icon' => 'medical_services',
        'label' => 'Clínico',
        'short' => 'Clínico',
        'cap' => 'clinico',
        'key' => 'clinico',
        'ready' => false,
    ],
    [
        'href' => 'usuarios.php',
        'icon' => 'manage_accounts',
        'label' => 'Usuarios',
        'short' => 'Usuarios',
        'cap' => 'usuarios',
        'key' => 'usuarios',
        'ready' => true,
    ],
];

$allowedReady = [];
$allowedSoon = [];
foreach ($navItems as $item) {
    $can = false;
    if (!empty($item['caps_any']) && is_array($item['caps_any'])) {
        foreach ($item['caps_any'] as $cap) {
            if (auth_can((string) $cap)) {
                $can = true;
                break;
            }
        }
    } elseif (!empty($item['cap'])) {
        $can = auth_can((string) $item['cap']);
    }
    if (!$can) {
        continue;
    }
    if (!empty($item['ready'])) {
        $allowedReady[] = $item;
    } else {
        $allowedSoon[] = $item;
    }
}

// Desktop: solo módulos funcionales
$allowedNav = $allowedReady;

// Móvil: Ajustes siempre visible en la barra; el resto sin función va a «Más»
$adminBottomItems = $allowedReady;
$adminMoreItems = $allowedSoon;

$pageTitleAdmin = $pageTitleAdmin ?? 'MiDoc Admin';

if (!function_exists('admin_item_active')) {
    function admin_item_active(string $navActive, array $item): bool
    {
        if ($navActive === $item['key']) {
            return true;
        }
        // Página de ajustes marca el ítem Ajustes
        return $navActive === 'ajustes' && ($item['key'] === 'ajustes' || $item['key'] === 'config');
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
<meta name="theme-color" content="<?= htmlspecialchars(theme_color('mist', '#D9EEE1'), ENT_QUOTES, 'UTF-8') ?>"/>
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
  .admin-bottom-nav a.is-active { color: <?= htmlspecialchars(theme_color('cyan', '#003E67'), ENT_QUOTES, 'UTF-8') ?>; }
  .admin-bottom-nav a.is-active .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 500; }
  .admin-shell-main {
    width: 100%;
    max-width: 1120px;
    margin-left: auto;
    margin-right: auto;
  }
  @media (min-width: 1536px) {
    .admin-shell-main { max-width: 1280px; }
  }
</style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex antialiased overflow-x-hidden">
<aside class="hidden lg:flex w-64 xl:w-72 shrink-0 h-full flex-col border-r border-sage/50 fixed left-0 top-0 bottom-0 z-40 bg-mist">
<div class="px-5 py-5 border-b border-sage/50">
  <div class="mb-1"><?= brand_mark('MiDoc Admin', 'h-9 w-9 rounded-md object-cover shrink-0', 'font-headline-md text-lg xl:text-xl font-bold text-primary') ?></div>
  <p class="text-on-surface-variant font-label-caps text-label-caps mt-2 leading-relaxed"><?= htmlspecialchars($roleSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<div class="flex-1 py-4 flex flex-col gap-1 px-3 overflow-y-auto">
<?php foreach ($allowedNav as $item): ?>
  <?php $isActive = admin_item_active($navActive, $item); ?>
  <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= $isActive ? 'text-primary font-bold bg-surface-container-high border border-primary/20' : 'text-on-surface-variant hover:bg-surface-container-highest border border-transparent' ?>" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
    <span class="material-symbols-outlined shrink-0"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
    <span class="text-sm leading-snug"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
  </a>
<?php endforeach; ?>
<?php if ($allowedSoon !== []): ?>
  <p class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/80">Próximamente</p>
  <?php foreach ($allowedSoon as $item): ?>
    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-on-surface-variant/70 border border-transparent opacity-80" title="Módulo aún no disponible">
      <span class="material-symbols-outlined shrink-0"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
      <span class="text-sm leading-snug flex-1"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
      <span class="text-[9px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded bg-surface-container">Pronto</span>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>
<div class="p-3 border-t border-sage/50 space-y-1">
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-on-surface-variant hover:bg-surface-container-highest transition-colors" href="index.php" target="_blank" rel="noopener noreferrer">
<span class="material-symbols-outlined">public</span>
<span class="text-sm font-bold">Ver sitio</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-on-surface-variant hover:bg-surface-container-highest transition-colors" href="dashboard.php?logout=1">
<span class="material-symbols-outlined">logout</span>
<span class="text-sm font-bold">Cerrar sesión</span>
</a>
</div>
</aside>

<div class="flex-1 w-full lg:ml-64 xl:ml-72 flex flex-col min-h-screen min-w-0">
<header class="flex justify-between items-center w-full px-4 sm:px-6 xl:px-8 py-3 h-14 lg:h-16 border-b border-sage/40 bg-mist/95 backdrop-blur-md fixed top-0 left-0 right-0 lg:left-64 xl:left-72 z-30">
<div class="flex items-center gap-3 min-w-0">
  <img src="<?= htmlspecialchars(brand_logo_src(), ENT_QUOTES, 'UTF-8') ?>" alt="MiDoc" class="lg:hidden h-8 w-8 rounded-md object-cover shrink-0"/>
  <h2 class="font-headline-md text-base sm:text-lg xl:text-xl font-bold text-primary truncate"><?= htmlspecialchars($headerTitle ?? 'Panel', ENT_QUOTES, 'UTF-8') ?></h2>
</div>
<div class="flex items-center gap-2 sm:gap-3 shrink-0">
  <div class="hidden md:block text-right max-w-[12rem] xl:max-w-[16rem]">
    <p class="font-body-md text-sm font-bold text-on-background truncate"><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></p>
    <p class="text-xs text-on-surface-variant truncate"><?= htmlspecialchars($rolNombre, ENT_QUOTES, 'UTF-8') ?></p>
  </div>
  <span class="hidden sm:inline-flex items-center rounded-full bg-primary-container/25 text-primary px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"><?= htmlspecialchars($rolSlug, ENT_QUOTES, 'UTF-8') ?></span>
  <a href="dashboard.php?logout=1" class="lg:hidden w-11 h-11 inline-flex items-center justify-center rounded-full text-on-surface-variant hover:bg-surface-container-high" aria-label="Cerrar sesión">
    <span class="material-symbols-outlined">logout</span>
  </a>
</div>
</header>
<main class="flex-1 mt-14 lg:mt-16 px-4 sm:px-6 xl:px-8 py-5 sm:py-6 lg:py-8 pb-28 lg:pb-10 min-w-0">
<div class="admin-shell-main space-y-5 lg:space-y-6">
