<?php
/**
 * Paleta MiDoc — banner clínico (cian → amarillo → naranja → teal → menta → sage).
 * Fondos tintados (sin blanco puro) para un look más cálido y amable con niños.
 */
declare(strict_types=1);

/**
 * @return array<string,string>
 */
function theme_colors(): array
{
    return [
        'cyan' => '#003E67',
        'light-cyan' => '#003E67',
        'cyan-deep' => '#003E67',
        'brand-yellow' => '#F9D423',
        'brand-orange' => '#FF9100',
        'teal' => '#169A8A',
        'mint' => '#3DE2AD',
        'sage' => '#B2D9C2',
        'mist' => '#D9EEE1',

        'primary' => '#169A8A',
        'primary-container' => '#3DE2AD',
        'on-primary' => '#ffffff',
        'on-primary-container' => '#083832',
        'primary-fixed' => '#7EEFD0',
        'primary-fixed-dim' => '#3DE2AD',
        'on-primary-fixed' => '#04221E',
        'on-primary-fixed-variant' => '#0C4F47',
        'surface-tint' => '#128277',
        'inverse-primary' => '#3DE2AD',

        'secondary' => '#FF9100',
        'secondary-container' => '#F9D423',
        'on-secondary' => '#3D2200',
        'on-secondary-fixed' => '#3D2200',
        'on-secondary-container' => '#5C3A00',
        'on-secondary-fixed-variant' => '#7A4A00',
        'secondary-fixed' => '#F9D423',
        'secondary-fixed-dim' => '#FF9100',

        'tertiary' => '#003E67',
        'tertiary-container' => '#4A7A9A',
        'tertiary-fixed' => '#8AADC2',
        'tertiary-fixed-dim' => '#5A87A8',
        'on-tertiary' => '#ffffff',
        'on-tertiary-container' => '#053A48',
        'on-tertiary-fixed' => '#022028',
        'on-tertiary-fixed-variant' => '#0A4A5C',

        'background' => '#D9EEE1',
        'on-background' => '#14352F',
        'surface' => '#D9EEE1',
        'surface-bright' => '#EEF7F1',
        'surface-dim' => '#C5E4D3',
        'surface-container-lowest' => '#F4FBF7',
        'surface-container-low' => '#D9EEE1',
        'surface-container' => '#CDE8DA',
        'surface-container-high' => '#B2D9C2',
        'surface-container-highest' => '#9BCFB6',
        'surface-variant' => '#CDE8DA',
        'on-surface' => '#14352F',
        'on-surface-variant' => '#3A5F56',
        'outline' => '#5E8F80',
        'outline-variant' => '#8FCBB0',
        'inverse-surface' => '#14352F',
        'inverse-on-surface' => '#D9EEE1',

        'error' => '#ba1a1a',
        'on-error' => '#ffffff',
        'error-container' => '#ffdad6',
        'on-error-container' => '#93000a',
    ];
}

function theme_colors_json(): string
{
    $json = json_encode(theme_colors(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return is_string($json) ? $json : '{}';
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
    return '<span class="inline-flex items-center gap-2.5">'
        . '<img src="' . $src . '" alt="Logo MiDoc" class="' . htmlspecialchars($imgClass, ENT_QUOTES, 'UTF-8') . '" width="40" height="40"/>'
        . '<span class="' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '">' . $labelSafe . '</span>'
        . '</span>';
}
