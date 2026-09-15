<?php
/**
 * Sitemap XML para Google Search Console.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$home = seo_home_url();
$now = gmdate('Y-m-d');

$urls = [
    ['loc' => $home, 'priority' => '1.0', 'changefreq' => 'weekly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?= htmlspecialchars($u['loc'], ENT_QUOTES, 'UTF-8') ?></loc>
    <lastmod><?= htmlspecialchars($now, ENT_QUOTES, 'UTF-8') ?></lastmod>
    <changefreq><?= htmlspecialchars($u['changefreq'], ENT_QUOTES, 'UTF-8') ?></changefreq>
    <priority><?= htmlspecialchars($u['priority'], ENT_QUOTES, 'UTF-8') ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
