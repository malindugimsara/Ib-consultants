<?php
include_once 'db.php'; 

// ලිපි සියල්ල ලබාගැනීම
$blog_query = "SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY created_at DESC";
$blog_result = $conn->query($blog_query);

// ලිපි සියල්ල Array එකකට දැමීම (පහසුවෙන් පාවිච්චි කිරීමට)
$articles = [];
if($blog_result && $blog_result->num_rows > 0) {
    while($row = $blog_result->fetch_assoc()) {
        $articles[] = $row;
    }
}

// පළමු ලිපිය Featured එක ලෙස ගැනීම
$featured_article = isset($articles[0]) ? $articles[0] : null;
?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- SEO Optimization -->
    <meta name="description"
        content="Actionable business insights, profit improvement strategies, manufacturing process optimization, and export expansion guides for Sri Lankan SMEs and factories from IB Business Consultants.">
    <meta name="keywords" content="Sri Lanka business blog, SME consulting insights, manufacturing efficiency Sri Lanka, profit improvement guide, export advisory Horana Colombo">
    <title>Business Insights & Blog | IB Business Consultants - SME & Manufacturing Experts</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logos/logo-2.webp">

    <!-- CSS here -->
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
                        <img src="assets/images/logos/logo-2.webp" alt="IB Consultants Logo" class="ib-preloader-logo-img">
                    </div>
                </div>

                <!-- Animated Text & Subtitle -->
                <div class="ib-preloader-text-group">
                    <div id="tj-weave-anim" class="tj-preloader-text">IB CONSULTANTS</div>
                    <div class="ib-preloader-subtext">Business & Profit Growth Advisory</div>
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
                                    <h1 class="tj-page-title text-white">Insights &amp; Articles</h1>
                                    <div class="tj-page-link">
                                        <span><i class="tji-home"></i></span>
                                        <span>
                                            <a href="index.php">Home</a>
                                        </span>
                                        <span><i class="tji-arrow-right"></i></span>
                                        <span>
                                            <span class="custom-breadcrumb-active">Blog &amp; Insights</span>
                                        </span>
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

                        <?php if($featured_article): ?>
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
                                                <img src="<?php echo htmlspecialchars($featured_article['image_path']); ?>" alt="Featured Article" class="ib-featured-thumb" style="border-radius: 10px">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="ib-featured-content">
                                                <div class="ib-meta-row">
                                                    <span class="ib-meta-category"><?php echo htmlspecialchars($featured_article['category_name']); ?></span>
                                                    <span>•</span>
                                                    <span><i class="fa-regular fa-calendar-days"></i> <?php echo date('F d, Y', strtotime($featured_article['created_at'])); ?></span>
                                                    <span>•</span>
                                                    <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($featured_article['read_time']); ?></span>
                                                </div>
                                                <h2 class="ib-featured-title">
                                                    <a href="javascript:void(0);" onclick="openArticleModal('art-<?php echo $featured_article['id']; ?>')" data-article-id="art-<?php echo $featured_article['id']; ?>">
                                                        <?php echo htmlspecialchars($featured_article['title']); ?>
                                                    </a>
                                                </h2>
                                                <p class="ib-featured-excerpt">
                                                    <?php echo htmlspecialchars($featured_article['excerpt']); ?>
                                                </p>
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                                    <div class="ib-author-card">
                                                        <img src="assets/images/service/Founder.jpeg" alt="Author" class="ib-author-img">
                                                        <div class="ib-author-info">
                                                            <div class="ib-author-name"><?php echo htmlspecialchars($featured_article['author_name']); ?></div>
                                                            <div class="ib-author-role">Lead Business Consultant</div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="tj-primary-btn" onclick="openArticleModal('art-<?php echo $featured_article['id']; ?>')" data-article-id="art-<?php echo $featured_article['id']; ?>">
                                                        <span class="btn-text topic-color2"><span>Read Full Article</span></span>
                                                        <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                                    </button>
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
                                        <button class="ib-filter-btn" data-filter="manufacturing">Manufacturing Excellence</button>
                                        <button class="ib-filter-btn" data-filter="profit">Profit &amp; Cost Control</button>
                                        <button class="ib-filter-btn" data-filter="export">Export &amp; Global Trade</button>
                                        <button class="ib-filter-btn" data-filter="strategy">SME Strategy &amp; KPIs</button>
                                    </div>
                                    <div class="ib-search-box">
                                        <input type="text" id="blog-search-input" placeholder="Search by topic, keyword..." autocomplete="off">
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
                                    // සියලුම ලිපි මෙහි පෙන්වයි
                                    foreach($articles as $art): 
                                    ?>
                                    <!-- Dynamic Article Card -->
                                    <div class="col-md-6 blog-card-wrapper" data-category="<?php echo htmlspecialchars($art['category_slug']); ?>" data-keywords="<?php echo htmlspecialchars($art['keywords']); ?>">
                                        <div class="ib-blog-card wow fadeInUp" data-wow-delay=".2s">
                                            <div class="ib-blog-card-thumb-wrap">
                                                <span class="ib-card-category-badge"><?php echo htmlspecialchars($art['category_name']); ?></span>
                                                <img src="<?php echo htmlspecialchars($art['image_path']); ?>" alt="Article" class="ib-blog-card-thumb">
                                            </div>
                                            <div class="ib-blog-card-body">
                                                <div class="ib-card-meta">
                                                    <span><i class="fa-regular fa-calendar-days"></i> <?php echo date('M d, Y', strtotime($art['created_at'])); ?></span>
                                                    <span>•</span>
                                                    <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($art['read_time']); ?></span>
                                                </div>
                                                <h3 class="ib-blog-card-title">
                                                    <a href="javascript:void(0);" onclick="openArticleModal('art-<?php echo $art['id']; ?>')" data-article-id="art-<?php echo $art['id']; ?>">
                                                        <?php echo htmlspecialchars($art['title']); ?>
                                                    </a>
                                                </h3>
                                                <p class="ib-blog-card-excerpt">
                                                    <?php echo htmlspecialchars($art['excerpt']); ?>
                                                </p>
                                                <div class="ib-blog-card-footer">
                                                    <div class="ib-author-card">
                                                        <img src="assets/images/service/Founder.jpeg" alt="Author" class="ib-author-img" style="width: 32px; height: 32px;">
                                                        <span class="ib-author-name" style="font-size: 13px;">Dammika B.</span>
                                                    </div>
                                                    <button class="ib-read-more-btn" onclick="openArticleModal('art-<?php echo $art['id']; ?>')" data-article-id="art-<?php echo $art['id']; ?>">
                                                        Read Article <i class="fa-solid fa-arrow-right-long"></i>
                                                    </button>
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
                                <div class="ib-blog-sidebar">
                                    <div class="ib-sidebar-widget ib-author-widget wow fadeInUp" data-wow-delay=".2s">
                                        <img src="assets/images/service/Founder.jpeg" alt="Dammika Bandara" class="ib-author-avatar-lg">
                                        <h5>Dammika Bandara</h5>
                                        <span class="ib-author-subtitle">Lead Business Consultant</span>
                                        <p>Over 20+ years of commercial, manufacturing, international market development, shipping coordination, and profit engineering experience helping Sri Lankan SMEs build profitable, resilient enterprises.</p>
                                        <div class="mt-3">
                                            <a href="about.php" class="ib-read-more-btn justify-content-center">View Consultant Profile <i class="fa-solid fa-arrow-right-long"></i></a>
                                        </div>
                                    </div>

                                    <div class="ib-advisory-cta-card wow fadeInUp" data-wow-delay=".35s">
                                        <span class="badge mb-2 px-3 py-1 text-uppercase" style="background: #d4af37; color: #0a1f44; font-weight: 700; font-size: 11px;">Direct Expert Advisory</span>
                                        <h4>Facing Operational or Profit Bottlenecks?</h4>
                                        <p>Get a structured diagnostic consultation for your manufacturing or SME business. Discuss practical interventions directly with our Lead Consultant.</p>
                                        <div class="ib-cta-action-btns">
                                            <a href="tel:+94711255610" class="ib-cta-call-btn"><i class="fa-solid fa-phone"></i> Call: 071 125 5610</a>
                                            <a href="https://wa.me/94742112767" target="_blank" class="ib-cta-wa-btn"><i class="fa-brands fa-whatsapp"></i> WhatsApp: 074 211 2767</a>
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

    <!-- start: Article Full Reader Modal (Direct child of <body>, OUTSIDE #smooth-wrapper so position: fixed attaches to viewport!) -->
    <div id="ib-article-modal" class="ib-article-modal-backdrop" onclick="handleBackdropClick(event)">
        <div class="ib-article-modal-dialog" onclick="event.stopPropagation()">
            
            <!-- Sticky Top Header (Always accessible during reading) -->
            <div class="ib-modal-sticky-header">
                <button type="button" class="ib-modal-back-btn" onclick="closeArticleModal()" title="Back to Articles">
                    <i class="fa-solid fa-arrow-left"></i> <span>Back</span>
                </button>
                <span class="ib-modal-header-tag" id="modal-header-tag">Article</span>
                <button type="button" class="ib-modal-close-icon-btn" onclick="closeArticleModal()" aria-label="Close Article" title="Close Article">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <!-- Reading Progress Bar -->
                <div class="ib-reading-progress-track">
                    <div id="ib-reading-progress-bar" class="ib-reading-progress-fill"></div>
                </div>
            </div>

            <!-- Scrollable Article Content Container -->
            <div class="ib-modal-scroll-area" id="ib-modal-scroll-area">
                <div class="ib-modal-img-wrap">
                    <img id="modal-article-img" src="assets/images/blog/blog-1.webp" alt="Article Cover" class="ib-modal-hero-img">
                </div>

                <div class="ib-modal-body">
                    <div class="ib-modal-meta">
                        <span id="modal-article-cat" class="ib-modal-category">Manufacturing Excellence</span>
                        <span class="d-none d-sm-inline">•</span>
                        <span id="modal-article-date"><i class="fa-regular fa-calendar-days"></i> October 12, 2026</span>
                        <span>•</span>
                        <span id="modal-article-readtime"><i class="fa-regular fa-clock"></i> 6 min read</span>
                    </div>

                    <h2 id="modal-article-title" class="ib-modal-title">Article Title</h2>
                    
                    <div class="ib-author-card mb-4 pb-3 border-bottom">
                        <img src="assets/images/service/Founder.jpeg"
                            alt="Dammika Bandara" class="ib-author-img">
                        <div class="ib-author-info">
                            <div class="ib-author-name">Dammika Bandara</div>
                            <div class="ib-author-role">Lead Business Consultant | IB Business Consultants</div>
                        </div>
                    </div>

                    <div id="modal-article-content" class="ib-modal-text">
                        <!-- Dynamic Content Injected Here -->
                    </div>

                    <!-- Modal CTA Footer -->
                    <div class="ib-modal-consult-footer">
                        <div class="ib-modal-consult-info">
                            <h6>Need practical implementation in your business?</h6>
                            <p>Talk directly with Dammika Bandara for tailored diagnostics &amp; execution strategies.</p>
                        </div>
                        <div class="ib-modal-action-btns">
                            <a href="tel:+94711255610" class="tj-primary-btn ib-btn-call">
                                <i class="fa-solid fa-phone me-1"></i> <span class="btn-text topic-color2"><span>Call 071 125 5610</span></span>
                            </a>
                            <a href="https://wa.me/94742112767" target="_blank" class="ib-cta-wa-btn ib-btn-wa">
                                <i class="fa-brands fa-whatsapp"></i> <span>WhatsApp Us</span>
                            </a>
                        </div>
                    </div>

                    <!-- Bottom Dismiss Bar -->
                    <div class="text-center mt-4 pt-2">
                        <button type="button" class="ib-modal-bottom-close-btn" onclick="closeArticleModal()">
                            <i class="fa-solid fa-arrow-left me-2"></i> Finished Reading — Return to Articles
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- end: Article Full Reader Modal -->

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

    <!-- Blog Interactive Data & Scripts -->
    <script>
        // Database එකෙන් එන ලිපි JavaScript Object එකක් බවට පත් කිරීම
    const IB_ARTICLES = {
        <?php foreach($articles as $art): ?>
        'art-<?php echo $art['id']; ?>': {
            title: <?php echo json_encode($art['title']); ?>,
            category: <?php echo json_encode($art['category_name']); ?>,
            date: "<?php echo date('F d, Y', strtotime($art['created_at'])); ?>",
            readTime: <?php echo json_encode($art['read_time']); ?>,
            img: <?php echo json_encode($art['image_path']); ?>,
            content: <?php echo json_encode($art['content']); ?>
        },
        <?php endforeach; ?>
    };

        // Open Article Reader Modal
        window.openArticleModal = function (artId) {
            const article = (typeof IB_ARTICLES !== 'undefined' && IB_ARTICLES[artId]) ? IB_ARTICLES[artId] : null;
            if (!article) {
                console.warn('Article data not found for id:', artId);
                return;
            }

            const modal = document.getElementById('ib-article-modal');
            if (!modal) {
                console.error('Modal element #ib-article-modal not found');
                return;
            }

            // CRITICAL: Ensure modal is direct child of <body> so GSAP ScrollSmoother transforms never clip it!
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            const imgEl = document.getElementById('modal-article-img');
            if (imgEl) imgEl.src = article.img;
            const catEl = document.getElementById('modal-article-cat');
            if (catEl) catEl.textContent = article.category;
            const headerTag = document.getElementById('modal-header-tag');
            if (headerTag) headerTag.textContent = article.category;
            const dateEl = document.getElementById('modal-article-date');
            if (dateEl) dateEl.innerHTML = '<i class="fa-regular fa-calendar-days"></i> ' + article.date;
            const readTimeEl = document.getElementById('modal-article-readtime');
            if (readTimeEl) readTimeEl.innerHTML = '<i class="fa-regular fa-clock"></i> ' + article.readTime;
            const titleEl = document.getElementById('modal-article-title');
            if (titleEl) titleEl.textContent = article.title;
            const contentEl = document.getElementById('modal-article-content');
            if (contentEl) contentEl.innerHTML = article.content;

            // Direct inline enforcement + class addition ensures immediate visibility
            modal.classList.add('show');
            modal.style.setProperty('display', 'flex', 'important');
            modal.style.setProperty('visibility', 'visible', 'important');
            modal.style.setProperty('opacity', '1', 'important');
            modal.style.setProperty('position', 'fixed', 'important');
            modal.style.setProperty('top', '0', 'important');
            modal.style.setProperty('left', '0', 'important');
            modal.style.setProperty('width', '100vw', 'important');
            modal.style.setProperty('height', '100vh', 'important');
            modal.style.setProperty('z-index', '999999999', 'important');

            document.documentElement.classList.add('ib-modal-open');
            document.body.classList.add('ib-modal-open');

            // Reset scroll position & progress bar to top
            const scrollArea = document.getElementById('ib-modal-scroll-area');
            if (scrollArea) {
                scrollArea.scrollTop = 0;
            }
            const progressBar = document.getElementById('ib-reading-progress-bar');
            if (progressBar) {
                progressBar.style.width = '0%';
            }
        };

        var openArticleModal = window.openArticleModal;

        // Close Article Reader Modal
        window.closeArticleModal = function (event) {
            if (event && event.stopPropagation) {
                event.stopPropagation();
            }
            const modal = document.getElementById('ib-article-modal');
            if (modal) {
                modal.classList.remove('show');
                modal.style.setProperty('display', 'none', 'important');
                modal.style.setProperty('visibility', 'hidden', 'important');
                modal.style.setProperty('opacity', '0', 'important');
            }
            document.documentElement.classList.remove('ib-modal-open');
            document.body.classList.remove('ib-modal-open');
        };

        var closeArticleModal = window.closeArticleModal;

        // Backdrop click handler
        window.handleBackdropClick = function (event) {
            if (event && event.target && event.target.id === 'ib-article-modal') {
                window.closeArticleModal(event);
            }
        };

        var handleBackdropClick = window.handleBackdropClick;

        // Global Delegated Click Listener for maximum reliability
        $(document).on('click', '[data-article-id]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const artId = $(this).attr('data-article-id');
            if (artId && window.openArticleModal) {
                window.openArticleModal(artId);
            }
        });

        // Keyboard ESC key close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.closeArticleModal();
            }
        });

        // Reading Progress tracking
        $(function () {
            const scrollArea = document.getElementById('ib-modal-scroll-area');
            const progressBar = document.getElementById('ib-reading-progress-bar');
            if (scrollArea && progressBar) {
                scrollArea.addEventListener('scroll', function () {
                    const scrollableDist = scrollArea.scrollHeight - scrollArea.clientHeight;
                    if (scrollableDist > 0) {
                        const progress = (scrollArea.scrollTop / scrollableDist) * 100;
                        progressBar.style.width = Math.min(100, Math.max(0, progress)) + '%';
                    }
                }, { passive: true });
            }
        });

        // Filter and Search Logic
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

            // Sidebar Category Links click
            $('.ib-category-link').on('click', function () {
                const filter = $(this).data('filter');
                $('#category-filters .ib-filter-btn').removeClass('active');
                $('#category-filters .ib-filter-btn[data-filter="' + filter + '"]').addClass('active');
                currentFilter = filter;
                applyFilters();

                // Smooth scroll to articles grid
                $('html, body').animate({
                    scrollTop: $('#category-filters').offset().top - 120
                }, 400);
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
        });
    </script>

</body>

</html>
