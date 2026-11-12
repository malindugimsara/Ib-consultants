<?php
/**
 * SEO & Schema Helper for IB Business Consultants
 * Provides standardized Meta Tags, Open Graph, Twitter Cards, Canonical URLs, and Schema.org JSON-LD markup.
 */

if (!function_exists('get_site_base_url')) {
    function get_site_base_url() {
        // Detect protocol
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'businessconsultant.lk';
        
        // If production domain
        if (strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false) {
            return "https://businessconsultant.lk";
        }
        
        // Localhost directory
        $script_dir = isset($_SERVER['PHP_SELF']) ? rtrim(dirname($_SERVER['PHP_SELF']), '/\\') : '/ib_consultants';
        return $protocol . $host . $script_dir;
    }
}

if (!function_exists('get_company_seo_info')) {
    function get_company_seo_info() {
        $base_url = get_site_base_url();
        return [
            'name' => 'IB Business Consultants',
            'legalName' => 'IB Business Consultants (Pvt) Ltd',
            'alternateName' => 'IB Consultants Sri Lanka',
            'description' => 'Practical Business, Profit Improvement, Manufacturing Process Optimization and Market Development Consulting for Sri Lankan SMEs and Manufacturing Businesses.',
            'url' => $base_url . '/index.php',
            'logo' => $base_url . '/assets/images/logos/logo-2.webp',
            'image' => $base_url . '/assets/images/logos/logo-2.webp',
            'telephone' => '+94711255610',
            'phoneFormatted' => '+94 71 125 5610',
            'whatsapp' => '+94 74 211 2767',
            'whatsappUrl' => 'https://wa.me/94742112767',
            'email' => 'info@businessconsultant.lk',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '93/1/B, Aggonawatte, Isipathana Mawatha',
                'addressLocality' => 'Horana',
                'addressRegion' => 'Western Province',
                'postalCode' => '12400',
                'addressCountry' => 'LK'
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => 6.7153,
                'longitude' => 80.0631
            ],
            'openingHours' => [
                'Mo-Fr 08:30-17:00',
                'Sa 08:30-14:00'
            ],
            'sameAs' => [
                'https://www.facebook.com/share/1Mi3aEoRHH/?mibextid=wwXIfr',
                'https://www.linkedin.com/company/ibconsultants/',
                'https://www.instagram.com/ibbusinessconsultants',
                'https://wa.me/94742112767'
            ]
        ];
    }
}

if (!function_exists('render_seo_tags')) {
    function render_seo_tags($config = []) {
        $base_url = get_site_base_url();
        $company = get_company_seo_info();

        $title = isset($config['title']) ? $config['title'] : 'IB Business Consultants | Top SME & Manufacturing Business Consulting in Sri Lanka';
        $description = isset($config['description']) ? $config['description'] : $company['description'];
        $keywords = isset($config['keywords']) ? $config['keywords'] : 'business consulting sri lanka, SME consulting, profit improvement, manufacturing consulting, Horana Colombo';
        
        $page_path = isset($config['canonical']) ? ltrim($config['canonical'], '/') : basename($_SERVER['PHP_SELF']);
        $canonical_url = $base_url . '/' . $page_path;
        
        $og_type = isset($config['og_type']) ? $config['og_type'] : 'website';
        $og_image = isset($config['og_image']) ? (strpos($config['og_image'], 'http') === 0 ? $config['og_image'] : $base_url . '/' . ltrim($config['og_image'], '/')) : $company['logo'];

        // Output tags
        ?>
    <!-- Primary SEO Meta Tags -->
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($title); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($keywords); ?>">
    <meta name="author" content="IB Business Consultants">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">

    <!-- Open Graph / Facebook / WhatsApp / LinkedIn -->
    <meta property="og:type" content="<?php echo htmlspecialchars($og_type); ?>">
    <meta property="og:locale" content="en_US">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="og:site_name" content="IB Business Consultants">
    <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($title); ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image); ?>">

    <!-- Place favicon.ico in the root directory -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logos/logo-2.webp">
    <link rel="apple-touch-icon" href="assets/images/logos/logo-2.webp">
    <meta name="theme-color" content="#091a32">

    <!-- Structured Data (JSON-LD) -->
    <?php
    $schemas = [];

    // 1. Organization / ProfessionalService Schema
    if (!empty($config['is_home'])) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            '@id' => $company['url'] . '#organization',
            'name' => $company['name'],
            'legalName' => $company['legalName'],
            'alternateName' => $company['alternateName'],
            'url' => $company['url'],
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $company['logo'],
                'width' => 250,
                'height' => 80
            ],
            'image' => $company['image'],
            'description' => $company['description'],
            'telephone' => $company['telephone'],
            'email' => $company['email'],
            'priceRange' => '$$',
            'address' => $company['address'],
            'geo' => $company['geo'],
            'openingHours' => $company['openingHours'],
            'sameAs' => $company['sameAs'],
            'areaServed' => [
                ['@type' => 'Country', 'name' => 'Sri Lanka'],
                ['@type' => 'City', 'name' => 'Colombo'],
                ['@type' => 'City', 'name' => 'Horana'],
                ['@type' => 'City', 'name' => 'Kandy'],
                ['@type' => 'City', 'name' => 'Galle'],
                ['@type' => 'City', 'name' => 'Kurunegala'],
                ['@type' => 'City', 'name' => 'Negombo'],
                ['@type' => 'City', 'name' => 'Gampaha']
            ],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Core Business Consultancy Services',
                'itemListElement' => [
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Business Health Check & Performance Assessment']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Profit Improvement & Cost Management']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Manufacturing Process Development & Cost Reduction']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Domestic Market Development & Distributor Management']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Export Market Development & International Sales']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Sales & Marketing Development']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Product Development, Pricing & Commercialization']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Business Process, Systems & Management Development']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Strategic Business Advisory & Growth Management']],
                    ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Business Transformation, KPI & Continuous Improvement']]
                ]
            ]
        ];

        // WebSite Schema
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $company['url'] . '#website',
            'url' => $company['url'],
            'name' => $company['name'],
            'description' => $company['description'],
            'publisher' => [
                '@id' => $company['url'] . '#organization'
            ]
        ];
    } else {
        // Lightweight Organization schema for subpages
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => $company['url'] . '#organization',
            'name' => $company['name'],
            'url' => $company['url'],
            'logo' => $company['logo'],
            'telephone' => $company['telephone'],
            'email' => $company['email'],
            'sameAs' => $company['sameAs']
        ];
    }

    // 2. Specific Service Schema
    if (!empty($config['service'])) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $config['service']['name'],
            'serviceType' => 'Business Consulting',
            'description' => !empty($config['service']['description']) ? $config['service']['description'] : $description,
            'provider' => [
                '@type' => 'ProfessionalService',
                'name' => $company['name'],
                'url' => $company['url']
            ],
            'areaServed' => [
                ['@type' => 'Country', 'name' => 'Sri Lanka'],
                ['@type' => 'City', 'name' => 'Colombo'],
                ['@type' => 'City', 'name' => 'Horana'],
                ['@type' => 'City', 'name' => 'Kandy'],
                ['@type' => 'City', 'name' => 'Galle'],
                ['@type' => 'City', 'name' => 'Kurunegala'],
                ['@type' => 'City', 'name' => 'Negombo'],
                ['@type' => 'City', 'name' => 'Gampaha']
            ],
            'url' => $canonical_url
        ];
    }

    // 3. ContactPage Schema
    if (!empty($config['is_contact'])) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'ContactPage',
            'name' => $title,
            'url' => $canonical_url,
            'description' => $description,
            'mainEntity' => [
                '@type' => 'ProfessionalService',
                'name' => $company['name'],
                'telephone' => $company['telephone'],
                'email' => $company['email'],
                'address' => $company['address']
            ]
        ];
    }

    // 4. AboutPage Schema
    if (!empty($config['is_about'])) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'AboutPage',
            'name' => $title,
            'url' => $canonical_url,
            'description' => $description,
            'mainEntity' => [
                '@id' => $company['url'] . '#organization'
            ]
        ];
    }

    // 5. Training / Courses Schema
    if (!empty($config['is_training'])) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOccupationalProgram',
            'name' => 'Corporate & SME Business Training Programs',
            'description' => $description,
            'provider' => [
                '@type' => 'Organization',
                'name' => $company['name'],
                'url' => $company['url']
            ],
            'url' => $canonical_url
        ];
    }

    // 6. Breadcrumbs Schema
    if (!empty($config['breadcrumbs'])) {
        $itemList = [];
        $pos = 1;
        foreach ($config['breadcrumbs'] as $crumb) {
            $crumb_url = strpos($crumb['url'], 'http') === 0 ? $crumb['url'] : $base_url . '/' . ltrim($crumb['url'], '/');
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $pos,
                'name' => $crumb['name'],
                'item' => $crumb_url
            ];
            $pos++;
        }
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemList
        ];
    }

    foreach ($schemas as $schema) {
        echo "\n    <script type=\"application/ld+json\">\n    " . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n    </script>";
    }
    echo "\n";
    }
}

