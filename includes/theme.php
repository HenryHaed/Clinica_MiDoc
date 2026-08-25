<?php
/**
 * Paleta MiDoc — colores editables desde Ajustes (CMS) + paleta derivada para Tailwind.
 */
declare(strict_types=1);

/**
 * Colores de marca editables (rueda de color en Ajustes).
 *
 * @return array<string, array{clave:string,etiqueta:string,ayuda:string,valor:string}>
 */
function theme_editable_defs(): array
{
    return [
        'cyan' => [
            'clave' => 'theme.cyan',
            'etiqueta' => 'Cian / azul principal',
            'ayuda' => 'Navbar, iconos, enlaces y acentos fríos',
            'valor' => '#003E67',
        ],
        'yellow' => [
            'clave' => 'theme.yellow',
            'etiqueta' => 'Amarillo (botones CTA)',
            'ayuda' => 'Botones principales y resaltes',
            'valor' => '#F9D423',
        ],
        'orange' => [
            'clave' => 'theme.orange',
            'etiqueta' => 'Naranja (acento)',
            'ayuda' => 'Hover de botones y detalles cálidos',
            'valor' => '#FF9100',
        ],
        'teal' => [
            'clave' => 'theme.teal',
            'etiqueta' => 'Teal / verde primario',
            'ayuda' => 'Títulos y color primario del sistema',
            'valor' => '#169A8A',
        ],
        'mint' => [
            'clave' => 'theme.mint',
            'etiqueta' => 'Menta',
            'ayuda' => 'Contenedores y estados suaves',
            'valor' => '#3DE2AD',
        ],
        'sage' => [
            'clave' => 'theme.sage',
            'etiqueta' => 'Sage (bordes)',
            'ayuda' => 'Bordes y superficies intermedias',
            'valor' => '#B2D9C2',
        ],
        'mist' => [
            'clave' => 'theme.mist',
            'etiqueta' => 'Fondo (mist)',
            'ayuda' => 'Fondo general de la web',
            'valor' => '#D9EEE1',
        ],
        'text' => [
            'clave' => 'theme.text',
            'etiqueta' => 'Texto principal',
            'ayuda' => 'Títulos y cuerpo principal',
            'valor' => '#14352F',
        ],
        'text_muted' => [
            'clave' => 'theme.text_muted',
            'etiqueta' => 'Texto secundario',
            'ayuda' => 'Subtítulos y textos atenuados',
            'valor' => '#3A5F56',
        ],
    ];
}

/**
 * @return array<string,string> key => #RRGGBB
 */
function theme_brand_defaults(): array
{
    $out = [];
    foreach (theme_editable_defs() as $key => $def) {
        $out[$key] = $def['valor'];
    }
    return $out;
}

function theme_normalize_hex(string $hex): ?string
{
    $hex = trim($hex);
    if (preg_match('/^#([0-9A-Fa-f]{6})$/', $hex, $m)) {
        return '#' . strtoupper($m[1]);
    }
    if (preg_match('/^#([0-9A-Fa-f]{3})$/', $hex, $m)) {
        $s = $m[1];
        return '#' . strtoupper($s[0] . $s[0] . $s[1] . $s[1] . $s[2] . $s[2]);
    }
    return null;
}

/**
 * @return array<string,string>
 */
function theme_resolve_brand(): array
{
    $brand = theme_brand_defaults();

    if (!function_exists('cms_get')) {
        $cmsPath = __DIR__ . '/cms.php';
        if (is_file($cmsPath)) {
            require_once $cmsPath;
        }
    }

    if (function_exists('cms_get')) {
        foreach (theme_editable_defs() as $key => $def) {
            $raw = trim((string) cms_get($def['clave'], $def['valor']));
            $norm = theme_normalize_hex($raw);
            if ($norm !== null) {
                $brand[$key] = $norm;
            }
        }
    }

    return $brand;
}

/**
 * Aclara u oscurece un hex (#RRGGBB). $ratio > 0 aclara hacia blanco.
 */
function theme_mix_hex(string $hex, string $with, float $ratio): string
{
    $a = theme_normalize_hex($hex) ?? '#000000';
    $b = theme_normalize_hex($with) ?? '#FFFFFF';
    $ratio = max(0.0, min(1.0, $ratio));

    $ar = hexdec(substr($a, 1, 2));
    $ag = hexdec(substr($a, 3, 2));
    $ab = hexdec(substr($a, 5, 2));
    $br = hexdec(substr($b, 1, 2));
    $bg = hexdec(substr($b, 3, 2));
    $bb = hexdec(substr($b, 5, 2));

    $r = (int) round($ar + ($br - $ar) * $ratio);
    $g = (int) round($ag + ($bg - $ag) * $ratio);
    $bch = (int) round($ab + ($bb - $ab) * $ratio);

    return sprintf('#%02X%02X%02X', $r, $g, $bch);
}

/**
 * @param array<string,string> $brand
 * @return array<string,string>
 */
function theme_build_palette(array $brand): array
{
    $cyan = $brand['cyan'] ?? '#003E67';
    $yellow = $brand['yellow'] ?? '#F9D423';
    $orange = $brand['orange'] ?? '#FF9100';
    $teal = $brand['teal'] ?? '#169A8A';
    $mint = $brand['mint'] ?? '#3DE2AD';
    $sage = $brand['sage'] ?? '#B2D9C2';
    $mist = $brand['mist'] ?? '#D9EEE1';
    $text = $brand['text'] ?? '#14352F';
    $muted = $brand['text_muted'] ?? '#3A5F56';

    $onYellow = theme_mix_hex($orange, '#000000', 0.55);
    $surfaceBright = theme_mix_hex($mist, '#FFFFFF', 0.55);
    $surfaceDim = theme_mix_hex($mist, $sage, 0.45);
    $surfaceLow = $mist;
    $surfaceLowest = theme_mix_hex($mist, '#FFFFFF', 0.7);
    $surfaceMid = theme_mix_hex($mist, $sage, 0.35);
    $surfaceHigh = $sage;
    $surfaceHighest = theme_mix_hex($sage, $teal, 0.25);

    return [
        'cyan' => $cyan,
        'light-cyan' => $cyan,
        'cyan-deep' => $cyan,
        'brand-yellow' => $yellow,
        'brand-orange' => $orange,
        'teal' => $teal,
        'mint' => $mint,
        'sage' => $sage,
        'mist' => $mist,

        'primary' => $teal,
        'primary-container' => $mint,
        'on-primary' => '#FFFFFF',
        'on-primary-container' => theme_mix_hex($teal, '#000000', 0.55),
        'primary-fixed' => theme_mix_hex($mint, '#FFFFFF', 0.35),
        'primary-fixed-dim' => $mint,
        'on-primary-fixed' => theme_mix_hex($teal, '#000000', 0.7),
        'on-primary-fixed-variant' => theme_mix_hex($teal, '#000000', 0.35),
        'surface-tint' => theme_mix_hex($teal, '#000000', 0.15),
        'inverse-primary' => $mint,

        'secondary' => $orange,
        'secondary-container' => $yellow,
        'on-secondary' => $onYellow,
        'on-secondary-fixed' => $onYellow,
        'on-secondary-container' => theme_mix_hex($orange, '#000000', 0.4),
        'on-secondary-fixed-variant' => theme_mix_hex($orange, '#000000', 0.3),
        'secondary-fixed' => $yellow,
        'secondary-fixed-dim' => $orange,

        'tertiary' => $cyan,
        'tertiary-container' => theme_mix_hex($cyan, '#FFFFFF', 0.45),
        'tertiary-fixed' => theme_mix_hex($cyan, '#FFFFFF', 0.6),
        'tertiary-fixed-dim' => theme_mix_hex($cyan, '#FFFFFF', 0.35),
        'on-tertiary' => '#FFFFFF',
        'on-tertiary-container' => theme_mix_hex($cyan, '#000000', 0.35),
        'on-tertiary-fixed' => theme_mix_hex($cyan, '#000000', 0.7),
        'on-tertiary-fixed-variant' => theme_mix_hex($cyan, '#000000', 0.4),

        'background' => $mist,
        'on-background' => $text,
        'surface' => $mist,
        'surface-bright' => $surfaceBright,
        'surface-dim' => $surfaceDim,
        'surface-container-lowest' => $surfaceLowest,
        'surface-container-low' => $surfaceLow,
        'surface-container' => $surfaceMid,
        'surface-container-high' => $surfaceHigh,
        'surface-container-highest' => $surfaceHighest,
        'surface-variant' => $surfaceMid,
        'on-surface' => $text,
        'on-surface-variant' => $muted,
        'outline' => theme_mix_hex($sage, $teal, 0.4),
        'outline-variant' => theme_mix_hex($sage, $mint, 0.35),
        'inverse-surface' => $text,
        'inverse-on-surface' => $mist,

        'error' => '#BA1A1A',
        'on-error' => '#FFFFFF',
        'error-container' => '#FFDAD6',
        'on-error-container' => '#93000A',
    ];
}

/**
 * @return array<string,string>
 */
function theme_colors(): array
{
    return theme_build_palette(theme_resolve_brand());
}

function theme_colors_json(): string
{
    $json = json_encode(theme_colors(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return is_string($json) ? $json : '{}';
}

function theme_color(string $key, string $fallback = '#000000'): string
{
    $colors = theme_colors();
    return $colors[$key] ?? $fallback;
}

/**
 * Convierte #RRGGBB a "r, g, b" para rgba() en CSS.
 */
function theme_hex_to_rgb_csv(string $hex): string
{
    $n = theme_normalize_hex($hex) ?? '#000000';
    return sprintf(
        '%d, %d, %d',
        hexdec(substr($n, 1, 2)),
        hexdec(substr($n, 3, 2)),
        hexdec(substr($n, 5, 2))
    );
}

function brand_logo_src(): string
{
    return 'assets/images/logo_midoc.png';
}

/**
 * Logo + nombre de marca (usar junto a títulos MiDoc).
 */
function brand_mark(string $label = 'MiDoc', string $imgClass = 'h-10 w-10 rounded-md object-cover shrink-0', string $textClass = ''): string
{
    $src = htmlspecialchars(brand_logo_src(), ENT_QUOTES, 'UTF-8');
    $labelSafe = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $text = $textClass !== '' ? $textClass : 'font-headline-lg text-2xl font-black text-cyan-deep tracking-tight';
    return '<span class="inline-flex items-center gap-2 min-w-0 max-w-full">'
        . '<img src="' . $src . '" alt="Logo MiDoc" class="' . htmlspecialchars($imgClass, ENT_QUOTES, 'UTF-8') . '" width="40" height="40"/>'
        . '<span class="' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '">' . $labelSafe . '</span>'
        . '</span>';
}
