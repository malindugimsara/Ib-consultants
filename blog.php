<?php
include_once 'db.php';

// Helper slugify function
function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'article-' . time() : $text;
}

// Fetch all published articles
$blog_query = "SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY created_at DESC";
$blog_result = $conn->query($blog_query);

// Populate articles array for easy access and rendering
$articles = [];
if ($blog_result && $blog_result->num_rows > 0) {
    while ($row = $blog_result->fetch_assoc()) {
        if (empty($row['slug'])) {
            $row['slug'] = slugify($row['title']);
        }
        $articles[] = $row;
    }
}

// Select the latest article as the featured article
$featured_article = isset($articles[0]) ? $articles[0] : null;

// Canonical URL calculation
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$canonical_url = $base_url . "/blog.php";
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Optimization -->
    <title>Business Insights &amp; Blog | IB Business Consultants - SME &amp; Manufacturing Experts</title>
    <meta name="description"
        content="Actionable business insights, profit improvement strategies, manufacturing process optimization, and export expansion guides for Sri Lankan SMEs and factories from IB Business Consultants.">
    <meta name="keywords"
        content="Sri Lanka business blog, SME consulting insights, manufacturing efficiency Sri Lanka, profit improvement guide, export advisory Horana Colombo">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn preview card) -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="IB Business Consultants">
    <meta property="og:title" content="Business Insights &amp; Blog | IB Business Consultants">
    <meta property="og:description"
        content="Actionable business insights, profit improvement strategies, manufacturing process optimization, and export expansion guides for Sri Lankan SMEs.">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:image" content="<?php echo $base_url; ?>/assets/images/logos/logo-2.webp">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Business Insights &amp; Blog | IB Business Consultants">
    <meta name="twitter:description"
        content="Actionable business insights, profit improvement strategies, manufacturing process optimization, and export expansion guides for Sri Lankan SMEs.">
    <meta name="twitter:image" content="<?php echo $base_url; ?>/assets/images/logos/logo-2.webp">

    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logos/logo-2.webp">

    <!-- Schema.org Blog / CollectionPage JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Blog",
      "name": "IB Business Consultants Insights Blog",
      "url": "<?php echo $canonical_url; ?>",
      "description": "Actionable business insights, profit improvement strategies, manufacturing process optimization, and export expansion guides for Sri Lankan SMEs.",
      "publisher": {
        "@type": "Organization",
        "name": "IB Business Consultants",
        "url": "<?php echo $base_url; ?>/index.php",
        "logo": {
          "@type": "ImageObject",
          "url": "<?php echo $base_url; ?>/assets/images/logos/logo-2.webp"
        }
      }
    }
    </script>

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome-pro.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/bexon-icons.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/swiper.min.css">
    <link rel="stylesheet" href="assets/css/venobox.min.css">
    <link rel="stylesheet" href="assets/css/odometer-theme-default.css">
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body>
    <div class="body-overlay"></div>

    <!-- start: Preloader -->
    <div class="tj-preloader is-loading">
        <div class="tj-preloader-inner">
            <div class="ib-preloader-content">
                <!-- Glowing Rotating Ring + Logo -->
                <div class="ib-preloader-logo-wrapper">
                    <div class="ib-preloader-ring ib-ring-outer"></div>
                    <div class="ib-preloader-ring ib-ring-inner"></div>
                    <div class="ib-preloader-logo-circle">
                        <img src="assets/images/logos/logo-2.webp" alt="IB Consultants Logo"
                            class="ib-preloader-logo-img">
                    </div>
                </div>

                <!-- Animated Text & Subtitle -->
                <div class="ib-preloader-text-group">
                    <div id="tj-weave-anim" class="tj-preloader-text">IB CONSULTANTS</div>
                    <div class="ib-preloader-subtext">Business &amp; Profit Growth Advisory</div>
                </div>

                <!-- Sleek Gold Progress Bar -->
                <div class="ib-preloader-bar-wrap">
                    <div class="ib-preloader-bar-line"></div>
                </div>
            </div>
        </div>
        <div class="tj-preloader-overlay"></div>
    </div>
    <!-- end: Preloader -->

    <?php include 'header.php'; ?>

    <div id="tj-back-to-top"><span id="tj-back-to-top-percentage"></span></div>

    <div id="smooth-wrapper">
        <div id="smooth-content">
            <main id="primary" class="site-main">

                <div class="space-for-header"></div>

                <!-- start: Breadcrumb Section -->
                <section class="tj-page-header section-gap-x custom-page-header"
                    data-bg-image="assets/images/bg/blog.webp">

                    <!-- Dark Overlay -->
                    <div class="custom-page-overlay"></div>

                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="tj-page-header-content text-center">
                                    <h1 class="tj-page-title">Business Insights &amp; Articles</h1>
                                    <div class="tj-page-link">
                                        <span><i class="tji-home"></i></span>
                                        <span><a href="index.php">Home</a></span>
                                        <span><i class="tji-arrow-right"></i></span>
                                        <span><span>Insights Blog</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Breadcrumb Section -->

                <!-- start: Blog Section -->
                <section class="ib-blog-section">
                    <div class="container">

                        <?php if ($featured_article):
                            $feat_url = "blog-details.php?slug=" . urlencode($featured_article['slug']);
                            ?>
                            <!-- Featured Spotlight Article -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="ib-featured-article wow fadeInUp" data-wow-delay=".2s">
                                        <div class="row g-0 align-items-center">
                                            <div class="col-lg-6">
                                                <div class="ib-featured-thumb-wrap">
                                                    <span class="ib-featured-badge">
                                                        <i class="fa-solid fa-star"></i> Featured Insight
                                                    </span>
                                                    <a href="<?php echo $feat_url; ?>"
                                                        style="display: block; width: 100%; height: 100%;">
                                                        <img src="<?php echo htmlspecialchars($featured_article['image_path']); ?>"
                                                            alt="<?php echo htmlspecialchars($featured_article['title']); ?>"
                                                            class="ib-featured-thumb" style="border-radius: 10px">
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="ib-featured-content">
                                                    <div class="ib-meta-row">
                                                        <span
                                                            class="ib-meta-category"><?php echo htmlspecialchars($featured_article['category_name']); ?></span>
                                                        <span>•</span>
                                                        <span><i class="fa-regular fa-calendar-days text-warning me-1"></i>
                                                            <?php echo date('F d, Y', strtotime($featured_article['created_at'])); ?></span>
                                                        <span>•</span>
                                                        <span><i class="fa-regular fa-clock text-warning me-1"></i>
                                                            <?php echo htmlspecialchars($featured_article['read_time']); ?></span>
                                                    </div>
                                                    <h2 class="ib-featured-title">
                                                        <a href="<?php echo $feat_url; ?>">
                                                            <?php echo htmlspecialchars($featured_article['title']); ?>
                                                        </a>
                                                    </h2>
                                                    <p class="ib-featured-excerpt">
                                                        <?php echo htmlspecialchars($featured_article['excerpt']); ?>
                                                    </p>
                                                    <div
                                                        class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                                        <div class="ib-author-card">
                                                            <img src="assets/images/service/Founder.webp" alt="Author"
                                                                class="ib-author-img">
                                                            <div class="ib-author-info">
                                                                <div class="ib-author-name">
                                                                    <?php echo htmlspecialchars($featured_article['author_name'] ?? 'Dammika Bandara'); ?>
                                                                </div>
                                                                <div class="ib-author-role">Lead Business Consultant</div>
                                                            </div>
                                                        </div>
                                                        <a href="<?php echo $feat_url; ?>" class="tj-primary-btn">
                                                            <span class="btn-text topic-color2"><span>Read Full
                                                                    Article</span></span>
                                                            <span class="btn-icon"><i
                                                                    class="tji-arrow-right-long"></i></span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Filter and Search Bar -->
                        <div class="row">
                            <div class="col-12">
                                <div class="ib-blog-filter-bar wow fadeInUp" data-wow-delay=".25s">
                                    <div class="ib-filter-pills" id="category-filters">
                                        <button class="ib-filter-btn active" data-filter="all">All Articles</button>
                                        <button class="ib-filter-btn" data-filter="manufacturing">Manufacturing
                                            Excellence</button>
                                        <button class="ib-filter-btn" data-filter="profit">Profit &amp; Cost
                                            Control</button>
                                        <button class="ib-filter-btn" data-filter="export">Export &amp; Global
                                            Trade</button>
                                        <button class="ib-filter-btn" data-filter="strategy">SME Strategy &amp;
                                            KPIs</button>
                                    </div>
                                    <div class="ib-search-box">
                                        <input type="text" id="blog-search-input"
                                            placeholder="Search by topic, keyword..." autocomplete="off">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content & Sidebar Grid -->
                        <div class="row">
                            <!-- Left: Article Cards Grid -->
                            <div class="col-lg-8">
                                <div class="row g-4" id="blog-articles-grid">

                                    <?php
                                    // Render all article cards dynamically
                                    foreach ($articles as $art):
                                        $art_url = "blog-details.php?slug=" . urlencode($art['slug']);
                                        ?>
                                        <!-- Dynamic Article Card -->
                                        <div class="col-md-6 blog-card-wrapper"
                                            data-category="<?php echo htmlspecialchars($art['category_slug']); ?>"
                                            data-keywords="<?php echo htmlspecialchars($art['keywords']); ?>">
                                            <div class="ib-blog-card wow fadeInUp" data-wow-delay=".2s">
                                                <div class="ib-blog-card-thumb-wrap">
                                                    <span
                                                        class="ib-card-category-badge"><?php echo htmlspecialchars($art['category_name']); ?></span>
                                                    <a href="<?php echo $art_url; ?>"
                                                        style="display: block; width: 100%; height: 100%;">
                                                        <img src="<?php echo htmlspecialchars($art['image_path']); ?>"
                                                            alt="<?php echo htmlspecialchars($art['title']); ?>"
                                                            class="ib-blog-card-thumb">
                                                    </a>
                                                </div>
                                                <div class="ib-blog-card-body">
                                                    <div class="ib-card-meta">
                                                        <span><i class="fa-regular fa-calendar-days text-warning me-1"></i>
                                                            <?php echo date('M d, Y', strtotime($art['created_at'])); ?></span>
                                                        <span>•</span>
                                                        <span><i class="fa-regular fa-clock text-warning me-1"></i>
                                                            <?php echo htmlspecialchars($art['read_time']); ?></span>
                                                    </div>
                                                    <h3 class="ib-blog-card-title">
                                                        <a href="<?php echo $art_url; ?>">
                                                            <?php echo htmlspecialchars($art['title']); ?>
                                                        </a>
                                                    </h3>
                                                    <p class="ib-blog-card-excerpt">
                                                        <?php echo htmlspecialchars($art['excerpt']); ?>
                                                    </p>
                                                    <div class="ib-blog-card-footer">
                                                        <div class="ib-author-card">
                                                            <img src="assets/images/service/Founder.webp" alt="Author"
                                                                class="ib-author-img" style="width: 32px; height: 32px;">
                                                            <span class="ib-author-name"
                                                                style="font-size: 13px;"><?php echo htmlspecialchars($art['author_name'] ?? 'Dammika B.'); ?></span>
                                                        </div>
                                                        <a href="<?php echo $art_url; ?>" class="ib-read-more-btn">
                                                            Read Article <i class="fa-solid fa-arrow-right-long"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                </div>

                                <!-- Empty State -->
                                <div id="blog-empty-state" class="ib-empty-state d-none">
                                    <i class="fa-regular fa-newspaper"></i>
                                    <h4>No Articles Found</h4>
                                    <p>We couldn't find any articles matching your search query.</p>
                                    <button type="button" class="tj-primary-btn" onclick="resetBlogFilters()">
                                        <span class="btn-text topic-color2"><span>Reset All Filters</span></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Right: Sticky Sidebar -->
                            <div class="col-lg-4">
                                <div class="ib-blog-sidebar sticky-top" style="top: 100px;">
                                    <div class="ib-sidebar-widget ib-author-widget wow fadeInUp" data-wow-delay=".2s">
                                        <img src="assets/images/service/Founder.webp" alt="Dammika Bandara"
                                            class="ib-author-avatar-lg">
                                        <h5>Dammika Bandara</h5>
                                        <span class="ib-author-subtitle">Lead Business Consultant</span>
                                        <p>Over 20+ years of commercial, manufacturing, international market
                                            development, shipping coordination, and profit engineering experience
                                            helping Sri Lankan SMEs build profitable, resilient enterprises.</p>
                                        <div class="mt-3">
                                            <a href="about.php" class="ib-read-more-btn justify-content-center">View
                                                Consultant Profile <i class="fa-solid fa-arrow-right-long"></i></a>
                                        </div>
                                    </div>

                                    <div class="ib-advisory-cta-card wow fadeInUp" data-wow-delay=".35s">
                                        <span class="badge mb-2 px-3 py-1 text-uppercase"
                                            style="background: #d4af37; color: #0a1f44; font-weight: 700; font-size: 11px;">Direct
                                            Expert Advisory</span>
                                        <h4>Facing Operational or Profit Bottlenecks?</h4>
                                        <p>Get a structured diagnostic consultation for your manufacturing or SME
                                            business. Discuss practical interventions directly with our Lead Consultant.
                                        </p>
                                        <div class="ib-cta-action-btns">
                                            <a href="tel:+94711255610" class="ib-cta-call-btn"><i
                                                    class="fa-solid fa-phone"></i> Call: 071 125 5610</a>
                                            <a href="https://wa.me/94742112767" target="_blank" class="ib-cta-wa-btn"><i
                                                    class="fa-brands fa-whatsapp"></i> WhatsApp: 074 211 2767</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
                <!-- end: Blog Section -->

            </main>

            <?php include 'footer.php'; ?>

        </div>
    </div>

    <!-- JS here -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/gsap.min.js"></script>
    <script src="assets/js/ScrollSmoother.js"></script>
    <script src="assets/js/gsap-scroll-to-plugin.min.js"></script>
    <script src="assets/js/gsap-scroll-trigger.min.js"></script>
    <script src="assets/js/gsap-split-text.min.js"></script>
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <script src="assets/js/swiper.min.js"></script>
    <script src="assets/js/odometer.min.js"></script>
    <script src="assets/js/venobox.min.js"></script>
    <script src="assets/js/appear.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/meanmenu.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/custom.js"></script>

    <!-- Filter and Search Logic -->
    <script>
        $(function () {
            let currentFilter = 'all';
            let currentSearch = '';

            function applyFilters() {
                let visibleCount = 0;
                const cards = $('.blog-card-wrapper');

                cards.each(function () {
                    const card = $(this);
                    const category = card.data('category');
                    const keywords = (card.data('keywords') + ' ' + card.text()).toLowerCase();

                    const matchesCategory = (currentFilter === 'all' || category === currentFilter);
                    const matchesSearch = (!currentSearch || keywords.indexOf(currentSearch) !== -1);

                    if (matchesCategory && matchesSearch) {
                        card.fadeIn(250);
                        visibleCount++;
                    } else {
                        card.fadeOut(200);
                    }
                });

                if (visibleCount === 0) {
                    $('#blog-empty-state').removeClass('d-none').fadeIn(300);
                } else {
                    $('#blog-empty-state').addClass('d-none');
                }
            }

            // Category Filter Buttons click
            $('#category-filters').on('click', '.ib-filter-btn', function () {
                $('#category-filters .ib-filter-btn').removeClass('active');
                $(this).addClass('active');
                currentFilter = $(this).data('filter');
                applyFilters();
            });

            // Live Search Input with debouncing
            let searchTimeout;
            $('#blog-search-input').on('keyup input', function () {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim().toLowerCase();
                searchTimeout = setTimeout(function () {
                    currentSearch = query;
                    applyFilters();
                }, 150);
            });

            // Reset filters
            window.resetBlogFilters = function () {
                $('#blog-search-input').val('');
                currentSearch = '';
                currentFilter = 'all';
                $('#category-filters .ib-filter-btn').removeClass('active');
                $('#category-filters .ib-filter-btn[data-filter="all"]').addClass('active');
                applyFilters();
            };

            // URL Search Params Auto-Trigger
            const urlParams = new URLSearchParams(window.location.search);
            const catParam = urlParams.get('category');
            const searchParam = urlParams.get('search');
            if (catParam) {
                const btn = $('#category-filters .ib-filter-btn[data-filter="' + catParam + '"]');
                if (btn.length) {
                    btn.trigger('click');
                }
            }
            if (searchParam) {
                $('#blog-search-input').val(searchParam).trigger('input');
            }
        });
    </script>

</body>

</html>