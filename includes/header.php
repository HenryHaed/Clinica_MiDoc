<?php
/**
 * Head — Tailwind CDN + paleta MiDoc + SEO (Google)
 */
require_once __DIR__ . '/theme.php';
require_once __DIR__ . '/seo.php';

$pageTitle = $pageTitle ?? 'MiDoc - Cirugía General y Pediátrica | Dr. Hernán Caihuara';
$pageDescription = $pageDescription ?? 'Clínica MiDoc. Dr. Hernán Caihuara Salas, Cirujano General Adultos y Cirujano Pediatra en Clínica Saint Jude. Agenda tu cita.';
$pageKeywords = $pageKeywords ?? 'MiDoc, cirugía general, cirugía pediátrica, Dr Hernán Caihuara, Clínica Saint Jude, cirujano, laparoscópica';
$pageRobots = $pageRobots ?? 'index, follow, max-image-preview:large';
$canonicalUrl = $canonicalUrl ?? seo_absolute_url('index.php');
$ogImage = $ogImage ?? seo_absolute_url(brand_logo_src());
$ogType = $ogType ?? 'website';
$siteName = $siteName ?? 'MiDoc';
$seoJsonLd = $seoJsonLd ?? null;
$basePath = $basePath ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
  <meta name="theme-color" content="<?= htmlspecialchars(theme_color('mist', '#D9EEE1'), ENT_QUOTES, 'UTF-8') ?>"/>
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta name="keywords" content="<?= htmlspecialchars($pageKeywords, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta name="author" content="MiDoc — Dr. Hernán Caihuara Salas"/>
  <meta name="robots" content="<?= htmlspecialchars($pageRobots, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta name="googlebot" content="<?= htmlspecialchars($pageRobots, ENT_QUOTES, 'UTF-8') ?>"/>
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>"/>

  <meta property="og:locale" content="es_BO"/>
  <meta property="og:type" content="<?= htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta property="og:site_name" content="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>"/>

  <meta name="twitter:card" content="summary_large_image"/>
  <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>"/>
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>"/>

  <link rel="icon" href="<?= htmlspecialchars(seo_absolute_url(brand_logo_src()), ENT_QUOTES, 'UTF-8') ?>" type="image/png"/>
  <link rel="apple-touch-icon" href="<?= htmlspecialchars(seo_absolute_url(brand_logo_src()), ENT_QUOTES, 'UTF-8') ?>"/>

  <?php if (is_array($seoJsonLd) && $seoJsonLd !== []): ?>
  <script type="application/ld+json"><?= json_encode($seoJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
  <?php endif; ?>

  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: <?= theme_colors_json() ?>,
          borderRadius: {
            "DEFAULT": "0.5rem",
            "lg": "0.75rem",
            "xl": "1rem",
            "full": "9999px"
          },
          fontFamily: {
            "headline-lg": ["Lexend"],
            "display-lg-mobile": ["Lexend"],
            "body-md": ["Inter"],
            "label-md": ["Inter"],
            "label-sm": ["Inter"],
            "headline-md": ["Lexend"],
            "display-lg": ["Lexend"],
            "body-lg": ["Inter"]
          }
        }
      }
    }
  </script>
  <style>
    /* Solo ocultar reveals cuando main.js añadió .js (si el script falla, todo se ve) */
    .js .reveal {
      opacity: 0;
      will-change: transform, opacity;
      transition:
        opacity 0.45s cubic-bezier(0.22, 1, 0.36, 1),
        transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .js .reveal-up { transform: translateY(36px); }
    .js .reveal-left { transform: translateX(-48px); }
    .js .reveal-right { transform: translateX(48px); }
    .js .reveal.is-visible {
      opacity: 1;
      transform: translate(0, 0);
    }
    .js .reveal-delay-1 { transition-delay: 0.06s; }
    .js .reveal-delay-2 { transition-delay: 0.12s; }
    .js .reveal-delay-3 { transition-delay: 0.18s; }
    .js .reveal-delay-4 { transition-delay: 0.24s; }
    .js .reveal-delay-5 { transition-delay: 0.3s; }
    html {
      scroll-behavior: smooth;
      scroll-padding-top: 4.5rem;
      scroll-padding-bottom: 5.75rem;
    }
    .landing-bottom-nav {
      padding-bottom: max(0.35rem, env(safe-area-inset-bottom, 0px));
    }
    .landing-bottom-nav .js-land-nav.is-active {
      color: <?= htmlspecialchars(theme_color('cyan', '#003E67'), ENT_QUOTES, 'UTF-8') ?>;
    }
    .landing-bottom-nav .js-land-nav.is-active .material-symbols-outlined {
      font-variation-settings: 'FILL' 1, 'wght' 500;
    }
    /* Contención móvil: evita anchos desproporcionados / scroll horizontal */
    html, body {
      max-width: 100%;
      overflow-x: hidden;
    }
    img:not([class*="object-cover"]),
    svg,
    video {
      max-width: 100%;
      height: auto;
    }
    .landing-shell {
      width: 100%;
      max-width: 100%;
      min-width: 0;
    }
    .landing-break {
      overflow-wrap: anywhere;
      word-break: break-word;
    }
    .landing-bottom-nav a {
      min-width: 0;
    }
    .landing-bottom-nav .material-symbols-outlined {
      line-height: 1;
    }
    .ubicacion-modal[hidden] {
      display: none !important;
    }
    .ubicacion-modal.is-open {
      display: flex !important;
    }
    body.ubicacion-modal-open {
      overflow: hidden;
    }
    @media (max-width: 767px) {
      .landing-bottom-nav .nav-cita-btn {
        width: 3.25rem;
        height: 3.25rem;
      }
      .landing-bottom-nav .nav-cita-btn .material-symbols-outlined {
        font-size: 1.5rem;
      }
    }
    .brand-hero-overlay {
      background:
        linear-gradient(
          to right,
          rgba(<?= theme_hex_to_rgb_csv(theme_color('mist')) ?>, 0.78) 0%,
          rgba(255, 255, 255, 0.42) 22%,
          rgba(255, 255, 255, 0.22) 48%,
          rgba(<?= theme_hex_to_rgb_csv(theme_color('mist')) ?>, 0.38) 100%
        ),
        linear-gradient(
          to right,
          rgba(<?= theme_hex_to_rgb_csv(theme_color('cyan')) ?>, 0.38) 0%,
          rgba(<?= theme_hex_to_rgb_csv(theme_color('brand-yellow')) ?>, 0.28) 26%,
          rgba(<?= theme_hex_to_rgb_csv(theme_color('brand-orange')) ?>, 0.26) 40%,
          rgba(<?= theme_hex_to_rgb_csv(theme_color('teal')) ?>, 0.26) 58%,
          rgba(<?= theme_hex_to_rgb_csv(theme_color('mint')) ?>, 0.22) 78%,
          rgba(<?= theme_hex_to_rgb_csv(theme_color('sage')) ?>, 0.20) 100%
        );
    }
    @media (prefers-reduced-motion: reduce) {
      .js .reveal,
      .js .reveal-up,
      .js .reveal-left,
      .js .reveal-right {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
  </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased overflow-x-hidden w-full max-w-full pb-[5.25rem] md:pb-0">
