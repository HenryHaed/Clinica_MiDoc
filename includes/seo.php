<?php
/**
 * Helpers SEO / URL canónica.
 */
declare(strict_types=1);

function seo_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $dir = '';
    }
    return $scheme . '://' . $host . $dir;
}

function seo_absolute_url(string $path = ''): string
{
    $base = rtrim(seo_base_url(), '/');
    if ($path === '' || $path === '/') {
        return $base . '/';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return $base . '/' . ltrim($path, '/');
}
