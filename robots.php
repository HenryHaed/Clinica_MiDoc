<?php
/**
 * robots.txt dinámico (Sitemap con URL absoluta).
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$sitemap = seo_absolute_url('sitemap.php');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /login.php\n";
echo "Disallow: /recuperar.php\n";
echo "Disallow: /dashboard.php\n";
echo "Disallow: /ajustes.php\n";
echo "Disallow: /api/\n";
echo "Disallow: /config/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /sql/\n";
echo "Disallow: /storage/\n";
echo "Disallow: /src/\n";
echo "\n";
echo 'Sitemap: ' . $sitemap . "\n";
