<?php
include_once 'db.php'; 
$core_query = "SELECT * FROM core_services WHERE is_published = 1 ORDER BY display_order ASC, id ASC";
$core_result = $conn->query($core_query);
?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Practical Business, Profitability, Market Development & Growth Solutions for SMEs and Manufacturing Businesses by IB Business Consultants.">

    <!-- Site Title -->
    <title>Core Consultancy Services | IB Business Consultants</title>

    <!-- Place favicon.ico in the root directory -->
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

    <?php include 'header.php'; ?>

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

    <!-- back to top start -->
    <div id="tj-back-to-top"><span id="tj-back-to-top-percentage"></span></div>
    <!-- back to top end -->

    <div id="smooth-wrapper">
        <div id="smooth-content">
            <main id="primary" class="site-main">
                <div class="space-for-header"></div>
                <!-- start: Breadcrumb Section -->
                <section class="tj-page-header section-gap-x custom-page-header"
                    data-bg-image="assets/images/bg/consultancy.webp">

                    <!-- Dark Overlay (මෙය ඔබේ CSS එකෙන් පාලනය වේ) -->
                    <div class="custom-page-overlay"></div>

                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="tj-page-header-content text-center">
                                    <h1 class="tj-page-title text-white">Consultancy Services</h1>
                                    <div class="tj-page-link">
                                        <span><i class="tji-home"></i></span>
                                        <span>
                                            <a href="index.php">Home</a>
                                        </span>
                                        <span><i class="tji-arrow-right"></i></span>
                                        <span>
                                            <span class="custom-breadcrumb-active">Consultancy Services</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Breadcrumb Section -->

                <!-- start: Service Sticky Section (For Service Page) -->
                <section class="tj-service-section service-2 section-gap slidebar-stickiy-container test2">
                    <div class="container">
                        <div class="row">
                            <!-- Left Sticky Column -->
                            <div class="col-lg-4">
                                <div class="content-wrap slidebar-stickiy">
                                    <div class="sec-heading style-2">
                                        <span class="sub-title wow fadeInUp" data-wow-delay=".3s">Core
                                            Services</span>
                                        <h2 class="sec-title text-white text-anim">Select a Solution for Your
                                            <span>Business.</span>
                                        </h2>
                                    </div>
                                    <!-- Button changed to Contact since user is already on Services page -->
                                    <div class="wow fadeInUp" data-wow-delay=".6s">
                                        <a class="tj-primary-btn" href="contact.php">
                                            <span class="btn-text topic-color2">Schedule a
                                                Consultation</span>
                                            <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Scrollable Column -->
                            <div class="col-lg-8">
                                <div class="service-wrapper-2">

                                    <?php
                                    if ($core_result && $core_result->num_rows > 0):
                                        $count = 1;
                                        while ($core = $core_result->fetch_assoc()):
                                            $num_formatted = str_pad($count, 2, '0', STR_PAD_LEFT) . '.';

                                            // Database එකේ ඇති ලැයිස්තුව (List Items) අලුත් පේළියෙන් (Newline) කඩා ගැනීම
                                            $list_array = explode("\n", trim($core['list_items']));
                                            ?>

                                            <!-- Dynamic Service Item -->
                                            <div class="service-item-wrapper tj-fadein-right-on-scroll">
                                                <div class="service-item style-2" style="background-color: #dfecec !important;">
                                                    <div class="title-area">
                                                        <div class="service-icon"><i
                                                                class="<?php echo htmlspecialchars($core['icon_class']); ?>"></i>
                                                        </div>
                                                        <h4 class="title">
                                                            <a href="<?php echo htmlspecialchars($core['page_link']); ?>">
                                                                <?php echo $num_formatted; ?>
                                                                <?php echo htmlspecialchars($core['title']); ?>
                                                            </a>
                                                        </h4>
                                                    </div>
                                                    <div class="service-content">
                                                        <p class="desc">
                                                            <?php echo htmlspecialchars($core['description']); ?>
                                                        </p>
                                                        <ul class="list-items">
                                                            <?php foreach ($list_array as $item):
                                                                if (trim($item) != ''): // හිස් පේළි මඟහැරීම
                                                                    ?>
                                                                    <li><i
                                                                            class="tji-list"></i><?php echo htmlspecialchars(trim($item)); ?>
                                                                    </li>
                                                                <?php
                                                                endif;
                                                            endforeach;
                                                            ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php
                                            $count++;
                                        endwhile;
                                    else:
                                        ?>
                                        <p>No services available at the moment.</p>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Background Shapes -->
                    <div class="bg-shape-1">
                        <img src="assets/images/shape/pattern-2.svg" alt="Pattern">
                    </div>
                    <div class="bg-shape-2">
                        <img src="assets/images/shape/pattern-3.svg" alt="Pattern">
                    </div>
                    <div class="bg-shape-3">
                        <img src="assets/images/shape/shape-blur.svg" alt="Blur">
                    </div>
                </section>
                <!-- end: Service Sticky Section -->

                <!-- start: Blog Section -->
                <section class="tj-blog-section section-gap slidebar-stickiy-container">
                    <div class="container">
                        <div class="row row-gap-5">
                            <div class="col-lg-8">
                                <div class="post-details-wrapper">
                                    <div class="blog-images wow fadeInUp" data-wow-delay=".1s">
                                        <img src="assets/images/service/service-details.jpg" alt="Consultancy Services">
                                    </div>
                                    <h2 class="title title-anim topic-color">Practical Business, Profitability & Growth
                                        Solutions.
                                    </h2>
                                    <div class="blog-text">
                                        <p class="wow fadeInUp" data-wow-delay=".3s">
                                            IB Consultants supports business owners and management teams to improve
                                            processes, reduce costs, increase profitability, develop domestic and
                                            international markets, strengthen distribution channels and build
                                            sustainable management systems.
                                        </p>
                                        <p class="wow fadeInUp" data-wow-delay=".3s">
                                            We do not focus only on recommendations. Our approach is designed to move
                                            from diagnosis to practical implementation, KPI measurement, and continuous
                                            improvement. We partner with Sri Lankan SMEs and manufacturing businesses to
                                            transform challenges into measurable growth.
                                        </p>

                                        <h4 class="wow fadeInUp mt-4 mb-3" data-wow-delay=".3s">Our Proven Approach:
                                        </h4>
                                        <ul class="wow fadeInUp" data-wow-delay=".3s">
                                            <li><span><i class="tji-check"></i></span><strong>Diagnose:</strong> Assess
                                                the current condition and identify critical issues.</li>
                                            <li><span><i class="tji-check"></i></span><strong>Plan:</strong> Develop
                                                structured strategies and 90-day roadmaps.</li>
                                            <li><span><i class="tji-check"></i></span><strong>Implement:</strong> Work
                                                on-the-ground to execute operational changes.</li>
                                            <li><span><i class="tji-check"></i></span><strong>Measure:</strong> Track
                                                progress through strict financial and operational KPIs.</li>
                                            <li><span><i class="tji-check"></i></span><strong>Improve:</strong>
                                                Establish a culture of continuous business transformation.</li>
                                        </ul>

                                        <div class="images-wrap mt-4">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="image-box wow fadeInUp" data-wow-delay=".3s">
                                                        <img src="assets/images/service/service1.jpg"
                                                            alt="Business Strategy Implementation">
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="image-box wow fadeInUp" data-wow-delay=".5s">
                                                        <img src="assets/images/service/service2.jpg"
                                                            alt="KPI Measurement">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h3 class="wow fadeInUp topic-color" data-wow-delay=".3s">Our Value Proposition
                                        </h3>
                                        <p class="wow fadeInUp" data-wow-delay=".3s">
                                            We bridge the gap between analysis and action. Our consultancy relationships
                                            are built on practical expertise, enabling decision-makers to act with
                                            greater clarity, discipline, and confidence.
                                        </p>

                                        <div class="details-content-box mt-4">
                                            <div class="service-details-item custom-feature-card wow fadeInUp"
                                                data-wow-delay=".2s">
                                                <span class="number">01.</span>
                                                <h6 class="title">Clear <br>Diagnosis</h6>
                                                <div class="desc">
                                                    <p>We deeply analyze your business model, cost structures, and
                                                        operational workflows to identify exactly what is holding your
                                                        business back.</p>
                                                </div>
                                            </div>
                                            <div class="service-details-item custom-feature-card wow fadeInUp"
                                                data-wow-delay=".4s">
                                                <div class="service-number">
                                                    <span class="number">02.</span>
                                                    <h6 class="title">Practical <br>Implementation</h6>
                                                    <div class="desc">
                                                        <p>Theoretical reports have limited value. We actively support
                                                            management teams to implement process improvements directly
                                                            on the factory floor and in the boardroom.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="service-details-item custom-feature-card wow fadeInUp"
                                                data-wow-delay=".6s">
                                                <div class="service-number">
                                                    <span class="number">03.</span>
                                                    <h6 class="title">Continuous <br>Improvement</h6>
                                                    <div class="desc">
                                                        <p>We establish robust KPI dashboards and monthly review systems
                                                            to ensure sustained execution and stronger accountability.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h3 class="wow fadeInUp mt-5" data-wow-delay=".3s">Frequently Asked Questions
                                        </h3>
                                        <div class="accordion tj-faq style-2" id="faqOne">
                                            <div class="accordion-item active wow fadeInUp" data-wow-delay=".3s">
                                                <button class=" faq-title" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#faq-1" aria-expanded="true">What types of
                                                    businesses do you consult?</button>
                                                <div id="faq-1" class="collapse show" data-bs-parent="#faqOne">
                                                    <div class="accordion-body faq-text">
                                                        <p>Our core expertise is focused on Sri Lankan Small and Medium
                                                            Enterprises (SMEs) and manufacturing businesses that are
                                                            looking to scale, optimize costs, or expand their markets.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item wow fadeInUp" data-wow-delay=".3s">
                                                <button class="faq-title collapsed" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#faq-2"
                                                    aria-expanded="false">Do you help implement your
                                                    recommendations?</button>
                                                <div id="faq-2" class="collapse" data-bs-parent="#faqOne">
                                                    <div class="accordion-body faq-text">
                                                        <p>Absolutely. Our key value proposition is moving from
                                                            diagnosis to practical implementation. We work alongside
                                                            your management teams to execute changes and monitor results
                                                            through KPIs.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item wow fadeInUp" data-wow-delay=".3s">
                                                <button class="faq-title collapsed" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#faq-3"
                                                    aria-expanded="false">How long does a consulting engagement
                                                    last?</button>
                                                <div id="faq-3" class="collapse" data-bs-parent="#faqOne">
                                                    <div class="accordion-body faq-text">
                                                        <p>It depends on the project scope. We typically start with a
                                                            Business Health Check leading to a 90-day prioritized
                                                            improvement roadmap, followed by monthly continuous
                                                            improvement advisory.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-lg-4">
                                <div class="tj-main-sidebar slidebar-stickiy">
                                    <div class="tj-sidebar-widget service-categories wow fadeInUp" data-wow-delay=".1s">
                                        <h4 class="widget-title topic-color">Core Services</h4>
                                        <ul style="font-size: 14px;">
                                            <li><a href="business-health-check.php">01. Business Health Check &
                                                    Performance Assessment<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="manufacturing-process-development.php">02. Manufacturing
                                                    Process Development & Cost Reduction<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="profit-improvement.php">03. Profit Improvement & Cost
                                                    Management<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="domestic-market-development.php">04. Domestic Market
                                                    Development & Distributor Mgt<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="sales-marketing-development.php">05. Sales & Marketing
                                                    Development<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="export-market-development.php">06. Export Market Development &
                                                    International Sales<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="business-process-systems.php">07. Business Process, Systems &
                                                    Mgt Development<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="strategic-business-advisory.php">08. Strategic Business
                                                    Advisory & Growth Mgt<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="product-development-pricing.php">09. Product Development,
                                                    Pricing & Commercialization<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                            <li><a href="business-transformation-kpi.php">10. Business Transformation,
                                                    KPI & Continuous Advisory<span class="icon"><i
                                                            class="tji-arrow-right"></i></span></a></li>
                                        </ul>
                                    </div>
                                    <div class="tj-sidebar-widget widget-feature-item wow fadeInUp"
                                        data-wow-delay=".3s">
                                        <div class="feature-box">
                                            <div class="feature-content">
                                                <h2 class="title">Expert</h2>
                                                <span>Consultancy</span>
                                                <a class="read-more feature-contact" href="tel:+94711255610">
                                                    <i class="tji-phone-3"></i>
                                                    <span>071 125 5610</span>
                                                </a>
                                            </div>
                                            <div class="feature-images">
                                                <img src="assets/images/service/Founder.jpeg" alt="Consultancy Contact">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Blog Section -->

                <!-- start: Cta Section -->
                <section class="tj-cta-section">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="custom-cta-area">

                                    <div class="custom-cta-content">
                                        <span class="sub-title wow fadeInUp" data-wow-delay=".3s"
                                            style="color: var(--accent-gold); font-weight: 600; margin-bottom: 10px; display: block; font-size: 14px;">
                                            <i class="tji-box" style="margin-right: 5px;"></i> GET IN TOUCH
                                        </span>
                                        <h2 class="title title-anim"
                                            style="color: #ffffff; font-size: 34px !important; font-weight: 700; line-height: 1.25; margin-bottom: 15px;">
                                            Let’s Build Your Future Together.
                                        </h2>
                                        <p class="cta-desc wow fadeInUp" data-wow-delay=".4s"
                                            style="color: rgba(255, 255, 255, 0.85); font-size: 16px; font-weight: 400; margin-bottom: 30px; line-height: 1.5; max-width: 480px;">
                                            Partner with us to implement practical strategies, optimize operations, and
                                            achieve sustainable growth for your SME or manufacturing business in Sri
                                            Lanka.
                                        </p>
                                        <div class="cta-btn wow fadeInUp" data-wow-delay=".5s">
                                            <a class="tj-primary-btn btn-dark" href="contact.php">
                                                <span class="btn-text topic-color2"><span>Get Started Now</span></span>
                                                <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="custom-cta-img">
                                        <img src="assets/images/cta/cta-bg.webp"
                                            alt="Building the Future with IB Consultants Sri Lanka">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Cta Section -->
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

</body>

</html>