<?php
$images = [
    'health-check-1.jpg',
    'health-check-2.jpg',
    'process-systems-1.jpg',
    'process-systems-2.jpg',
    'transformation-kpi-1.jpg',
    'transformation-kpi-2.jpg',
    'domestic-market-1.jpg',
    'domestic-market-2.jpg',
    'export-market-1.jpg',
    'export-market-2.jpg',
    'manufacturing-1.jpg',
    'manufacturing-2.jpg',
    'product-pricing-1.webp',
    'product-pricing-2.webp',
    'profit-improvement-1.jpg',
    'profit-improvement-2.webp',
    'sales-marketing-1.webp',
    'sales-marketing-2.jpg',
    'strategic-advisory-1.webp',
    'strategic-advisory-2.webp'
];

$all_ok = true;
foreach ($images as $img) {
    $url = 'http://localhost:8080/ib_consultants/assets/images/service/' . $img;
    $headers = @get_headers($url);
    $status = $headers ? $headers[0] : 'FAILED';
    echo "$img => $status\n";
    if (strpos($status, '200') === false) {
        $all_ok = false;
    }
}

if ($all_ok) {
    echo "\n=== ALL 20 UNIQUE IMAGES LOAD WITH HTTP 200 OK! ===\n";
} else {
    echo "\n=== SOME IMAGES FAILED! ===\n";
}
