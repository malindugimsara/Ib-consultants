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

// 1. Fetch requested article by slug or ID
$article = null;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!empty($slug)) {
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE slug = ? AND is_published = 1 LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $article = $res->fetch_assoc();
        }
        $stmt->close();
    }
}

if (!$article && $id > 0) {
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ? AND is_published = 1 LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $article = $res->fetch_assoc();
        }
        $stmt->close();
    }
}

// Fallback: If not found, redirect to blog main page
if (!$article) {
    header("Location: blog.php");
    exit;
}

// 2. Compute SEO & URL Metadata
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

$current_slug = !empty($article['slug']) ? $article['slug'] : slugify($article['title']);
$canonical_url = $base_url . "/blog-details.php?slug=" . urlencode($current_slug);
$article_image_url = !empty($article['image_path']) ? $base_url . "/" . ltrim($article['image_path'], '/') : $base_url . "/assets/images/logos/logo-2.webp";

$page_title = htmlspecialchars($article['title']) . " | IB Business Consultants";
$meta_desc = !empty($article['excerpt']) ? htmlspecialchars(mb_substr(strip_tags($article['excerpt']), 0, 160)) : "Actionable business insights, profit improvement strategies, manufacturing process optimization from IB Business Consultants.";
$meta_keywords = !empty($article['keywords']) ? htmlspecialchars($article['keywords']) . ", IB Consultants, Sri Lanka business advisory" : "Sri Lanka business blog, SME consulting, profit improvement, export advisory";
$author_name = !empty($article['author_name']) ? htmlspecialchars($article['author_name']) : "Dammika Bandara";

// 3. Fetch Related Articles (from same category or recent, excluding current)
$related_articles = [];
$current_id = (int) $article['id'];
$cat_slug = $conn->real_escape_string($article['category_slug']);
$related_res = $conn->query("SELECT * FROM blog_posts WHERE is_published = 1 AND id != $current_id ORDER BY (category_slug = '$cat_slug') DESC, created_at DESC LIMIT 3");
if ($related_res && $related_res->num_rows > 0) {
    while ($r = $related_res->fetch_assoc()) {
        $related_articles[] = $r;
    }
}

// 4. Fetch Previous and Next Articles for Navigation
$prev_article = null;
$next_article = null;

$prev_res = $conn->query("SELECT id, title, slug FROM blog_posts WHERE is_published = 1 AND id < $current_id ORDER BY id DESC LIMIT 1");
if ($prev_res && $prev_res->num_rows > 0) {
    $prev_article = $prev_res->fetch_assoc();
}

$next_res = $conn->query("SELECT id, title, slug FROM blog_posts WHERE is_published = 1 AND id > $current_id ORDER BY id ASC LIMIT 1");
if ($next_res && $next_res->num_rows > 0) {
    $next_article = $next_res->fetch_assoc();
}

// 5. Fetch Sidebar Categories with counts
$categories_res = $conn->query("SELECT category_slug, category_name, COUNT(*) as post_count FROM blog_posts WHERE is_published = 1 GROUP BY category_slug, category_name");
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags -->
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_desc; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">
    <meta name="author" content="<?php echo $author_name; ?>">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn preview card) -->
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="IB Business Consultants">
    <meta property="og:title" content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta property="og:description" content="<?php echo $meta_desc; ?>">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:image" content="<?php echo $article_image_url; ?>">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta property="article:published_time" content="<?php echo date('c', strtotime($article['created_at'])); ?>">
    <meta property="article:author" content="<?php echo $author_name; ?>">
    <meta property="article:section" content="<?php echo htmlspecialchars($article['category_name']); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta name="twitter:description" content="<?php echo $meta_desc; ?>">
    <meta name="twitter:image" content="<?php echo $article_image_url; ?>">

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logos/logo-2.webp">

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

    <!-- Structured Data (Schema.org JSON-LD for BlogPosting & Breadcrumbs) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "BlogPosting",
          "@id": "<?php echo $canonical_url; ?>#article",
          "isPartOf": {
            "@type": "WebPage",
            "@id": "<?php echo $canonical_url; ?>",
            "url": "<?php echo $canonical_url; ?>",
            "name": "<?php echo htmlspecialchars($article['title']); ?>",
            "headline": "<?php echo htmlspecialchars($article['title']); ?>"
          },
          "headline": "<?php echo htmlspecialchars($article['title']); ?>",
          "description": "<?php echo $meta_desc; ?>",
          "image": "<?php echo $article_image_url; ?>",
          "datePublished": "<?php echo date('c', strtotime($article['created_at'])); ?>",
          "dateModified": "<?php echo date('c', strtotime($article['created_at'])); ?>",
          "author": {
            "@type": "Person",
            "name": "<?php echo $author_name; ?>",
            "jobTitle": "Lead Business Consultant",
            "url": "<?php echo $base_url; ?>/about.php"
          },
          "publisher": {
            "@type": "Organization",
            "name": "IB Business Consultants",
            "url": "<?php echo $base_url; ?>/index.php",
            "logo": {
              "@type": "ImageObject",
              "url": "<?php echo $base_url; ?>/assets/images/logos/logo-2.webp"
            }
          },
          "articleSection": "<?php echo htmlspecialchars($article['category_name']); ?>",
          "keywords": "<?php echo $meta_keywords; ?>"
        },
        {
          "@type": "BreadcrumbList",
          "@id": "<?php echo $canonical_url; ?>#breadcrumb",
          "itemListElement": [
            {
              "@type": "ListItem",
              "position": 1,
              "name": "Home",
              "item": "<?php echo $base_url; ?>/index.php"
            },
            {
              "@type": "ListItem",
              "position": 2,
              "name": "Business Insights Blog",
              "item": "<?php echo $base_url; ?>/blog.php"
            },
            {
              "@type": "ListItem",
              "position": 3,
              "name": "<?php echo htmlspecialchars($article['category_name']); ?>",
              "item": "<?php echo $base_url; ?>/blog.php?category=<?php echo urlencode($article['category_slug']); ?>"
            },
            {
              "@type": "ListItem",
              "position": 4,
              "name": "<?php echo htmlspecialchars($article['title']); ?>"
            }
          ]
        }
      ]
    }
    </script>

    <!-- Article Page Specific Styling -->
    <style>
        /* Top Reading Progress Bar */
        .ib-top-progress-track {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: transparent;
            z-index: 999999;
        }

        .ib-top-progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #d4af37 0%, #b89127 100%);
            transition: width 0.1s ease;
        }

        /* Article Container */
        .ib-article-page-section {
            padding: 70px 0 100px;
            background-color: #f7f9fa;
        }

        .ib-article-main-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 45px 50px;
            box-shadow: 0 10px 40px rgba(10, 31, 68, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.06);
            margin-bottom: 40px;
        }

        @media (max-width: 767px) {
            .ib-article-main-card {
                padding: 26px 20px;
                border-radius: 14px;
            }
        }

        /* Hero Image */
        .ib-article-hero-wrap {
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 35px;
            box-shadow: 0 12px 30px rgba(10, 31, 68, 0.08);
            border: 1px solid #eef2f7;
            max-height: 480px;
        }

        .ib-article-hero-img {
            width: 100%;
            height: 100%;
            max-height: 480px;
            object-fit: cover;
            display: block;
        }

        /* Meta Row */
        .ib-article-meta-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 18px;
            font-size: 13.5px;
            color: #64748b;
        }

        .ib-article-badge-cat {
            background: #0a1f44;
            color: #d4af37;
            font-weight: 700;
            font-size: 11.5px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .ib-article-h1-title {
            font-size: clamp(26px, 3.2vw, 38px) !important;
            font-weight: 800 !important;
            color: #0a1f44 !important;
            line-height: 1.3 !important;
            letter-spacing: -0.5px;
            margin-bottom: 24px !important;
        }

        /* Author Row */
        .ib-article-author-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding: 16px 0;
            border-top: 1px solid #eef2f7;
            border-bottom: 1px solid #eef2f7;
            margin-bottom: 35px;
        }

        /* Article Rich Content Styling */
        .ib-article-content-body {
            font-size: 16px;
            line-height: 1.85;
            color: #334155;
        }

        .ib-article-content-body h2 {
            font-size: 26px !important;
            font-weight: 800 !important;
            color: #0a1f44 !important;
            margin: 40px 0 16px 0 !important;
            letter-spacing: -0.3px;
        }

        .ib-article-content-body h3 {
            font-size: 21px !important;
            font-weight: 700 !important;
            color: #0a1f44 !important;
            margin: 30px 0 14px 0 !important;
        }

        .ib-article-content-body h4 {
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #0a1f44 !important;
            margin: 24px 0 12px 0 !important;
        }

        .ib-article-content-body p {
            margin-bottom: 22px;
            font-size: 16px;
            line-height: 1.85;
            color: #334155;
        }

        .ib-article-content-body ul,
        .ib-article-content-body ol {
            margin: 20px 0 28px 24px;
            padding: 0;
        }

        .ib-article-content-body li {
            margin-bottom: 10px;
            line-height: 1.7;
        }

        .ib-article-content-body blockquote {
            border-left: 4px solid #d4af37;
            padding: 18px 24px;
            background: #fdfbf7;
            border-radius: 0 12px 12px 0;
            margin: 30px 0;
            font-style: italic;
            color: #0a1f44;
            font-size: 17px;
            font-weight: 500;
        }

        .ib-article-content-body img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 25px 0;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }

        /* Social Share Bar */
        .ib-share-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 22px;
            margin: 45px 0 35px 0;
        }

        .ib-share-label {
            font-size: 13.5px;
            font-weight: 700;
            color: #0a1f44;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ib-share-btns {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .ib-share-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .ib-share-wa {
            background: #25D366;
            color: #ffffff !important;
        }

        .ib-share-wa:hover {
            background: #20ba59;
            transform: translateY(-2px);
        }

        .ib-share-in {
            background: #0077b5;
            color: #ffffff !important;
        }

        .ib-share-in:hover {
            background: #006097;
            transform: translateY(-2px);
        }

        .ib-share-fb {
            background: #1877f2;
            color: #ffffff !important;
        }

        .ib-share-fb:hover {
            background: #0d65d9;
            transform: translateY(-2px);
        }

        .ib-share-copy {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #334155 !important;
        }

        .ib-share-copy:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            transform: translateY(-2px);
        }

        /* Post Navigation (Prev / Next) */
        .ib-post-nav-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 35px;
            padding-top: 30px;
            border-top: 1px solid #eef2f7;
        }

        @media (max-width: 575px) {
            .ib-post-nav-bar {
                grid-template-columns: 1fr;
            }
        }

        .ib-post-nav-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            text-decoration: none;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .ib-post-nav-box:hover {
            background: #ffffff;
            border-color: #d4af37;
            box-shadow: 0 8px 20px rgba(10, 31, 68, 0.06);
            transform: translateY(-2px);
        }

        .ib-nav-direction {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #b89127;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ib-nav-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #0a1f44;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Related Articles Card Section */
        .ib-related-section {
            margin-top: 50px;
        }

        .ib-related-title {
            font-size: 24px;
            font-weight: 800;
            color: #0a1f44;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ib-related-title i {
            color: #d4af37;
            font-size: 20px;
        }

        /* Toast Popup */
        #ib-copy-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #0a1f44;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            border: 1px solid #d4af37;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            font-size: 13.5px;
            font-weight: 600;
            z-index: 999999;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        #ib-copy-toast.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>

<body>
    <!-- Top Reading Progress Fill -->
    <div class="ib-top-progress-track">
        <div id="ib-reading-progress-fill" class="ib-top-progress-fill"></div>
    </div>

    <div class="body-overlay"></div>

    <!-- start: Preloader -->
    <div class="tj-preloader is-loading">
        <div class="tj-preloader-inner">
            <div class="ib-preloader-content">
                <div class="ib-preloader-logo-wrapper">
                    <div class="ib-preloader-ring ib-ring-outer"></div>
                    <div class="ib-preloader-ring ib-ring-inner"></div>
                    <div class="ib-preloader-logo-circle">
                        <img src="assets/images/logos/logo-2.webp" alt="IB Consultants Logo"
                            class="ib-preloader-logo-img">
                    </div>
                </div>
                <div class="ib-preloader-text-group">
                    <div id="tj-weave-anim" class="tj-preloader-text">IB CONSULTANTS</div>
                    <div class="ib-preloader-subtext">Business &amp; Profit Growth Advisory</div>
                </div>
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
                    <div class="custom-page-overlay"></div>
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="tj-page-header-content text-center">
                                    <h1 class="tj-page-title"
                                        style="font-size: clamp(24px, 3.2vw, 36px); max-width: 900px; margin: 0 auto 12px auto;">
                                        <?php echo htmlspecialchars($article['title']); ?></h1>
                                    <div class="tj-page-link">
                                        <span><i class="tji-home"></i></span>
                                        <span><a href="index.php">Home</a></span>
                                        <span><i class="tji-arrow-right"></i></span>
                                        <span><a href="blog.php">Blog</a></span>
                                        <span><i class="tji-arrow-right"></i></span>
                                        <span><span><?php echo htmlspecialchars($article['category_name']); ?></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Breadcrumb Section -->

                <!-- start: Article Page Body Section -->
                <section class="ib-article-page-section">
                    <div class="container">
                        <div class="row row-gap-5">

                            <!-- Left: Article Content -->
                            <div class="col-lg-8">
                                <article class="ib-article-main-card">

                                    <!-- Meta Row -->
                                    <div class="ib-article-meta-bar">
                                        <span class="ib-article-badge-cat">
                                            <i class="fa-solid fa-tag"></i>
                                            <?php echo htmlspecialchars($article['category_name']); ?>
                                        </span>
                                        <span>•</span>
                                        <span><i class="fa-regular fa-calendar-days text-warning me-1"></i>
                                            <?php echo date('F d, Y', strtotime($article['created_at'])); ?></span>
                                        <span>•</span>
                                        <span><i class="fa-regular fa-clock text-warning me-1"></i>
                                            <?php echo htmlspecialchars($article['read_time']); ?></span>
                                    </div>

                                    <!-- Main Title -->
                                    <h1 class="ib-article-h1-title"><?php echo htmlspecialchars($article['title']); ?>
                                    </h1>

                                    <!-- Author Bio Bar -->
                                    <div class="ib-article-author-row">
                                        <div class="ib-author-card">
                                            <img src="assets/images/service/Founder.webp" alt="Dammika Bandara"
                                                class="ib-author-img">
                                            <div class="ib-author-info">
                                                <div class="ib-author-name"><?php echo $author_name; ?></div>
                                                <div class="ib-author-role">Lead Business Consultant | IB Business
                                                    Consultants</div>
                                            </div>
                                        </div>

                                        <div class="d-none d-sm-block">
                                            <a href="#consultation-cta" class="btn btn-sm btn-outline-dark"
                                                style="border-radius: 20px; font-weight: 600; font-size: 12.5px;">
                                                <i class="fa-solid fa-comments me-1"></i> Request Advisory
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Cover Image -->
                                    <?php if (!empty($article['image_path'])): ?>
                                        <div class="ib-article-hero-wrap">
                                            <img src="<?php echo htmlspecialchars($article['image_path']); ?>"
                                                alt="<?php echo htmlspecialchars($article['title']); ?>"
                                                class="ib-article-hero-img">
                                        </div>
                                    <?php endif; ?>

                                    <!-- Article Content Body -->
                                    <div class="ib-article-content-body">
                                        <?php
                                        // Output rich content
                                        echo $article['content'];
                                        ?>
                                    </div>

                                    <!-- Social Share Bar -->
                                    <?php
                                    $share_url_encoded = urlencode($canonical_url);
                                    $share_title_encoded = urlencode($article['title'] . " - IB Business Consultants");
                                    ?>
                                    <div class="ib-share-bar">
                                        <span class="ib-share-label">
                                            <i class="fa-solid fa-share-nodes text-warning"></i> Share this insight:
                                        </span>
                                        <div class="ib-share-btns">
                                            <!-- WhatsApp -->
                                            <a href="https://api.whatsapp.com/send?text=<?php echo $share_title_encoded . '%20' . $share_url_encoded; ?>"
                                                target="_blank" class="ib-share-btn ib-share-wa"
                                                title="Share on WhatsApp">
                                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                            </a>
                                            <!-- LinkedIn -->
                                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url_encoded; ?>"
                                                target="_blank" class="ib-share-btn ib-share-in"
                                                title="Share on LinkedIn">
                                                <i class="fa-brands fa-linkedin-in"></i> LinkedIn
                                            </a>
                                            <!-- Facebook -->
                                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url_encoded; ?>"
                                                target="_blank" class="ib-share-btn ib-share-fb"
                                                title="Share on Facebook">
                                                <i class="fa-brands fa-facebook-f"></i> Facebook
                                            </a>
                                            <!-- Copy Link -->
                                            <button type="button" class="ib-share-btn ib-share-copy"
                                                onclick="copyArticleUrl()" title="Copy Link to Clipboard">
                                                <i class="fa-solid fa-link"></i> <span id="copy-btn-text">Copy
                                                    Link</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Direct Advisory CTA Box -->
                                    <div class="ib-modal-consult-footer mt-4" id="consultation-cta"
                                        style="border-radius: 14px;">
                                        <div class="ib-modal-consult-info">
                                            <h6 style="color: #ffffff; font-weight: 700;">Facing Operational or Profit
                                                Bottlenecks?</h6>
                                            <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">Get a structured
                                                diagnostic consultation for your business. Discuss practical
                                                interventions directly with our Lead Consultant.</p>
                                        </div>
                                        <div class="ib-modal-action-btns">
                                            <a href="tel:+94711255610" class="tj-primary-btn ib-btn-call">
                                                <i class="fa-solid fa-phone me-1"></i> <span
                                                    class="btn-text topic-color2"><span>Call 071 125 5610</span></span>
                                            </a>
                                            <a href="https://wa.me/94742112767?text=<?php echo urlencode('Hello, I was reading your article "' . $article['title'] . '" and would like to discuss a consultation.'); ?>"
                                                target="_blank" class="ib-cta-wa-btn ib-btn-wa">
                                                <i class="fa-brands fa-whatsapp"></i> <span>WhatsApp Us</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Previous & Next Navigation -->
                                    <div class="ib-post-nav-bar">
                                        <?php if ($prev_article): ?>
                                            <a href="blog-details.php?slug=<?php echo urlencode(!empty($prev_article['slug']) ? $prev_article['slug'] : slugify($prev_article['title'])); ?>"
                                                class="ib-post-nav-box">
                                                <span class="ib-nav-direction"><i class="fa-solid fa-arrow-left"></i>
                                                    Previous Article</span>
                                                <span
                                                    class="ib-nav-title"><?php echo htmlspecialchars($prev_article['title']); ?></span>
                                            </a>
                                        <?php else: ?>
                                            <div class="ib-post-nav-box" style="opacity: 0.5; pointer-events: none;">
                                                <span class="ib-nav-direction"><i class="fa-solid fa-arrow-left"></i>
                                                    Previous</span>
                                                <span class="ib-nav-title">You are at the earliest article</span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($next_article): ?>
                                            <a href="blog-details.php?slug=<?php echo urlencode(!empty($next_article['slug']) ? $next_article['slug'] : slugify($next_article['title'])); ?>"
                                                class="ib-post-nav-box text-sm-end">
                                                <span class="ib-nav-direction justify-content-sm-end">Next Article <i
                                                        class="fa-solid fa-arrow-right"></i></span>
                                                <span
                                                    class="ib-nav-title"><?php echo htmlspecialchars($next_article['title']); ?></span>
                                            </a>
                                        <?php else: ?>
                                            <div class="ib-post-nav-box text-sm-end"
                                                style="opacity: 0.5; pointer-events: none;">
                                                <span class="ib-nav-direction justify-content-sm-end">Next <i
                                                        class="fa-solid fa-arrow-right"></i></span>
                                                <span class="ib-nav-title">You are at the latest article</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                </article>

                                <!-- Related Insights -->
                                <?php if (!empty($related_articles)): ?>
                                    <div class="ib-related-section">
                                        <h3 class="ib-related-title">
                                            <i class="fa-solid fa-newspaper"></i> Related Business Insights
                                        </h3>
                                        <div class="row g-4">
                                            <?php foreach ($related_articles as $rel):
                                                $rel_slug = !empty($rel['slug']) ? $rel['slug'] : slugify($rel['title']);
                                                $rel_url = "blog-details.php?slug=" . urlencode($rel_slug);
                                                ?>
                                                <div class="col-md-4">
                                                    <div class="ib-blog-card h-100">
                                                        <div class="ib-blog-card-thumb-wrap" style="height: 180px;">
                                                            <span class="ib-card-category-badge"
                                                                style="font-size: 10px; padding: 3px 8px;">
                                                                <?php echo htmlspecialchars($rel['category_name']); ?>
                                                            </span>
                                                            <img src="<?php echo htmlspecialchars($rel['image_path']); ?>"
                                                                alt="<?php echo htmlspecialchars($rel['title']); ?>"
                                                                class="ib-blog-card-thumb">
                                                        </div>
                                                        <div class="ib-blog-card-body p-3">
                                                            <span class="text-muted" style="font-size: 11px;">
                                                                <i class="fa-regular fa-calendar-days text-warning"></i>
                                                                <?php echo date('M d, Y', strtotime($rel['created_at'])); ?>
                                                            </span>
                                                            <h5 class="ib-blog-card-title mt-1 mb-2"
                                                                style="font-size: 15px; line-height: 1.35;">
                                                                <a href="<?php echo $rel_url; ?>"
                                                                    style="color: #0a1f44; text-decoration: none;">
                                                                    <?php echo htmlspecialchars($rel['title']); ?>
                                                                </a>
                                                            </h5>
                                                            <a href="<?php echo $rel_url; ?>" class="ib-read-more-btn mt-auto"
                                                                style="font-size: 12px;">
                                                                Read Insight <i class="fa-solid fa-arrow-right-long"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>

                            <!-- Right: Sidebar -->
                            <div class="col-lg-4">
                                <div class="ib-blog-sidebar sticky-top" style="top: 100px; z-index: 10;">

                                    <!-- Search Widget -->
                                    <div class="ib-sidebar-widget mb-4"
                                        style="background: #ffffff; border-radius: 14px; padding: 22px; border: 1px solid #eef2f7; box-shadow: 0 4px 15px rgba(10,31,68,0.03);">
                                        <h5
                                            style="color: #0a1f44; font-weight: 700; margin-bottom: 14px; font-size: 16px;">
                                            Search Insights</h5>
                                        <form action="blog.php" method="GET">
                                            <div class="ib-search-box w-100" style="max-width: 100%;">
                                                <input type="text" name="search"
                                                    placeholder="Search by topic, keyword..." autocomplete="off">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Categories Widget -->
                                    <?php if ($categories_res && $categories_res->num_rows > 0): ?>
                                        <div class="ib-sidebar-widget mb-4"
                                            style="background: #ffffff; border-radius: 14px; padding: 22px; border: 1px solid #eef2f7; box-shadow: 0 4px 15px rgba(10,31,68,0.03);">
                                            <h5
                                                style="color: #0a1f44; font-weight: 700; margin-bottom: 14px; font-size: 16px;">
                                                Categories</h5>
                                            <div class="d-flex flex-column gap-2">
                                                <?php while ($cat = $categories_res->fetch_assoc()): ?>
                                                    <a href="blog.php?category=<?php echo urlencode($cat['category_slug']); ?>"
                                                        class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none"
                                                        style="background: #f8fafc; color: #1e293b; font-size: 13.5px; font-weight: 600; border: 1px solid #e2e8f0; transition: all 0.2s ease;">
                                                        <span><i class="fa-solid fa-chevron-right text-warning me-2"
                                                                style="font-size: 10px;"></i><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                                        <span
                                                            class="badge bg-light text-dark border"><?php echo $cat['post_count']; ?></span>
                                                    </a>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Lead Consultant Profile Widget -->
                                    <div class="ib-sidebar-widget ib-author-widget mb-4"
                                        style="background: #ffffff; border-radius: 14px; padding: 26px 22px; border: 1px solid #eef2f7; text-align: center; box-shadow: 0 4px 15px rgba(10,31,68,0.03);">
                                        <img src="assets/images/service/Founder.webp" alt="Dammika Bandara"
                                            class="ib-author-avatar-lg"
                                            style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #d4af37; margin-bottom: 12px;">
                                        <h5
                                            style="color: #0a1f44; font-weight: 800; margin-bottom: 4px; font-size: 17px;">
                                            Dammika Bandara</h5>
                                        <span class="ib-author-subtitle"
                                            style="color: #b89127; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 12px;">Lead
                                            Business Consultant</span>
                                        <p
                                            style="font-size: 13px; line-height: 1.6; color: #64748b; margin-bottom: 18px;">
                                            Over 20+ years of manufacturing, SME process re-engineering, and profit
                                            engineering experience helping businesses build profitable, resilient
                                            operations.</p>
                                        <a href="about.php" class="tj-primary-btn w-100 justify-content-center"
                                            style="border-radius: 8px; font-size: 13px; padding: 8px 16px;">
                                            <span class="btn-text topic-color2"><span>View Consultant
                                                    Profile</span></span>
                                        </a>
                                    </div>

                                    <!-- Quick WhatsApp / Phone Box -->
                                    <div class="ib-advisory-cta-card"
                                        style="background: linear-gradient(135deg, #071738 0%, #0a1f44 100%); border-radius: 14px; padding: 26px 22px; border: 1.5px solid rgba(212,175,55,0.4); box-shadow: 0 10px 30px rgba(10,31,68,0.15);">
                                        <span class="badge mb-2 px-3 py-1 text-uppercase"
                                            style="background: #d4af37; color: #0a1f44; font-weight: 700; font-size: 11px;">Direct
                                            Advisory</span>
                                        <h4
                                            style="color: #ffffff; font-weight: 800; font-size: 18px; margin-bottom: 10px;">
                                            Need Strategic Help?</h4>
                                        <p
                                            style="color: rgba(255,255,255,0.8); font-size: 13px; line-height: 1.6; margin-bottom: 18px;">
                                            Get practical implementation support tailored directly to your production
                                            floor and management systems.</p>
                                        <div class="d-flex flex-column gap-2">
                                            <a href="tel:+94711255610"
                                                class="btn btn-outline-light d-flex align-items-center justify-content-center gap-2 py-2"
                                                style="border-radius: 8px; font-size: 13px; font-weight: 700;">
                                                <i class="fa-solid fa-phone text-warning"></i> Call: 071 125 5610
                                            </a>
                                            <a href="https://wa.me/94742112767" target="_blank"
                                                class="btn btn-success d-flex align-items-center justify-content-center gap-2 py-2"
                                                style="border-radius: 8px; font-size: 13px; font-weight: 700;">
                                                <i class="fa-brands fa-whatsapp"></i> WhatsApp Us
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </section>
                <!-- end: Article Page Body Section -->

            </main>

            <?php include 'footer.php'; ?>

        </div>
    </div>

    <!-- Floating Toast for Copy Link Feedback -->
    <div id="ib-copy-toast">
        <i class="fa-solid fa-circle-check text-warning fs-5"></i>
        <span>Article link copied to clipboard!</span>
    </div>

    <!-- JS Dependencies -->
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

    <!-- Reading Progress Tracker & Copy Link Script -->
    <script>
        // Reading Progress Bar
        window.addEventListener('scroll', function () {
            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            const fill = document.getElementById('ib-reading-progress-fill');
            if (fill) {
                fill.style.width = Math.min(100, Math.max(0, scrolled)) + '%';
            }
        });

        // Copy Article URL
        function copyArticleUrl() {
            const url = "<?php echo $canonical_url; ?>";
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(showToast);
            } else {
                const temp = document.createElement('input');
                document.body.appendChild(temp);
                temp.value = url;
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                showToast();
            }
        }

        function showToast() {
            const toast = document.getElementById('ib-copy-toast');
            if (toast) {
                toast.classList.add('show');
                setTimeout(function () {
                    toast.classList.remove('show');
                }, 3000);
            }
        }
    </script>
</body>

</html>