<?php
/**
 * Dynamic XML Sitemap Generator
 * Outputs valid XML Sitemap for search engines and can write to sitemap.xml.
 */

include_once 'db.php';
include_once 'seo_helper.php';

$base_url = get_site_base_url();

$pages = [
    [
        'loc' => $base_url . '/index.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'daily',
        'priority' => '1.0'
    ],
    [
        'loc' => $base_url . '/about.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.85'
    ],
    [
        'loc' => $base_url . '/consultancy-service.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'weekly',
        'priority' => '0.90'
    ],
    [
        'loc' => $base_url . '/business-health-check.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/profit-improvement.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/manufacturing-process-development.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/domestic-market-development.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/export-market-development.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/sales-marketing-development.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/product-development-pricing.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/business-process-systems.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/strategic-business-advisory.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/business-transformation-kpi.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ],
    [
        'loc' => $base_url . '/training-programs.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'weekly',
        'priority' => '0.85'
    ],
    [
        'loc' => $base_url . '/blog.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'daily',
        'priority' => '0.90'
    ],
    [
        'loc' => $base_url . '/contact.php',
        'lastmod' => date('Y-m-d'),
        'changefreq' => 'monthly',
        'priority' => '0.80'
    ]
];

// Add published blog posts dynamically
if (isset($conn) && $conn) {
    $blog_res = $conn->query("SELECT slug, title, created_at FROM blog_posts WHERE is_published = 1 ORDER BY id DESC");
    if ($blog_res) {
        while ($b = $blog_res->fetch_assoc()) {
            $slug = !empty($b['slug']) ? $b['slug'] : 'post';
            $date = !empty($b['created_at']) ? date('Y-m-d', strtotime($b['created_at'])) : date('Y-m-d');
            $pages[] = [
                'loc' => $base_url . '/blog-details.php?slug=' . urlencode($slug),
                'lastmod' => $date,
                'changefreq' => 'monthly',
                'priority' => '0.70'
            ];
        }
    }
}

// Build XML string
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
$xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
$xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
$xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

foreach ($pages as $p) {
    $xml .= "    <url>\n";
    $xml .= "        <loc>" . htmlspecialchars($p['loc']) . "</loc>\n";
    $xml .= "        <lastmod>" . $p['lastmod'] . "</lastmod>\n";
    $xml .= "        <changefreq>" . $p['changefreq'] . "</changefreq>\n";
    $xml .= "        <priority>" . $p['priority'] . "</priority>\n";
    $xml .= "    </url>\n";
}

$xml .= '</urlset>';

// Save static sitemap.xml to root
@file_put_contents(__DIR__ . '/sitemap.xml', $xml);

// If accessed via web browser or crawler
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/xml; charset=utf-8');
    echo $xml;
} else {
    echo "Sitemap generated successfully with " . count($pages) . " URLs.\n";
}
