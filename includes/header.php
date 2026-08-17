<?php
/**
 * Head — Tailwind CDN + paleta MiDoc (theme.php)
 */
require_once __DIR__ . '/theme.php';

$pageTitle = $pageTitle ?? 'MiDoc - Salud Primordial';
$pageDescription = $pageDescription ?? 'Dr. Hernán Caihuara Salas. Cirujano General y Cirujano Pediatra. Atención médica de excelencia en Clínica Saint Jude.';
$basePath = $basePath ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
  <meta name="theme-color" content="#D9EEE1"/>
  <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

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
      color: #003E67;
    }
    .landing-bottom-nav .js-land-nav.is-active .material-symbols-outlined {
      font-variation-settings: 'FILL' 1, 'wght' 500;
    }
    .brand-hero-overlay {
      background:
        linear-gradient(
          to right,
          rgba(217, 238, 225, 0.78) 0%,
          rgba(255, 255, 255, 0.42) 22%,
          rgba(255, 255, 255, 0.22) 48%,
          rgba(217, 238, 225, 0.38) 100%
        ),
        linear-gradient(
          to right,
          rgba(0, 62, 103, 0.38) 0%,
          rgba(249, 212, 35, 0.28) 26%,
          rgba(255, 145, 0, 0.26) 40%,
          rgba(22, 154, 138, 0.26) 58%,
          rgba(61, 226, 173, 0.22) 78%,
          rgba(178, 217, 194, 0.20) 100%
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
<body class="bg-background text-on-background font-body-md antialiased overflow-x-hidden pb-[4.5rem] md:pb-0">
