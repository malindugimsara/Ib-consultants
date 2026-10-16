<?php
include_once 'db.php';


$prog_query = "SELECT * FROM certificate_programs WHERE is_published = 1 ORDER BY display_order ASC, id DESC";
$prog_result = $conn->query($prog_query);

$workshop_query = "SELECT * FROM business_workshops WHERE is_published = 1 ORDER BY display_order ASC, id ASC";
$workshop_result = $conn->query($workshop_query);
$workshops = [];
if ($workshop_result && $workshop_result->num_rows > 0) {
    while ($w = $workshop_result->fetch_assoc()) {
        $workshops[] = $w;
    }
}

$dip_query = "SELECT * FROM diploma_programs WHERE is_published = 1 ORDER BY display_order ASC, id ASC";
$dip_result = $conn->query($dip_query);
?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="Enroll in practical business workshops, certificate programs, and diploma courses for SME owners, managers, and professionals with IB Consultants in Sri Lanka.">
    <meta name="keywords"
        content="Business Training Sri Lanka, Corporate Workshops, Certificate in Business Management, SME Training, Digital Marketing Course, Manufacturing Training">

    <!-- Site Title -->
    <title>Training & Programs | Business Workshops & Certificates | IB Consultants</title>

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
                    data-bg-image="assets/images/bg/training.webp">

                    <!-- Dark Overlay -->
                    <div class="custom-page-overlay"></div>

                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="tj-page-header-content text-center">
                                    <h1 class="tj-page-title text-white">Training & Programs</h1>
                                    <div class="tj-page-link">
                                        <span><i class="tji-home"></i></span>
                                        <span>
                                            <a href="index.php">Home</a>
                                        </span>
                                        <span><i class="tji-arrow-right"></i></span>
                                        <span>
                                            <span class="custom-breadcrumb-active">Training & Programs</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Breadcrumb Section -->

                <!-- start: Training Intro Section -->
                <section class="tj-about-section section-gap pb-0 ">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ib-philosophy-video-card">
                                    <!-- Background Video inside floating card -->
                                    <video autoplay muted loop playsinline class="ib-philosophy-bg-video">
                                        <source src="assets/video/our-philosophy-bg.mp4" type="video/mp4">
                                    </video>

                                    <!-- Dark Luxury Navy Gradient Overlay -->
                                    <div class="ib-philosophy-overlay"></div>

                                    <!-- Philosophy Card Content -->
                                    <div class="ib-philosophy-content">
                                        <span class="sub-title ib-philosophy-subtitle wow fadeInUp"
                                            data-wow-delay=".3s">
                                            <i class="tji-box"></i>Our Philosophy
                                        </span>
                                        <h2 class="sec-title ib-philosophy-title wow fadeInUp" data-wow-delay=".4s">
                                            Practical Business Education for <span>Professionals.</span>
                                        </h2>
                                        <p class="desc ib-philosophy-desc wow fadeInUp" data-wow-delay=".5s">
                                            We provide practical business education for SME owners, managers,
                                            executives,
                                            entrepreneurs, and professionals. Our programmes are designed to combine
                                            business knowledge with practical tools, real business examples, hands-on
                                            exercises, case studies, and implementation activities so you can apply what
                                            you
                                            learn immediately.
                                        </p>
                                        <div class="wow fadeInUp" data-wow-delay=".6s">
                                            <a href="https://wa.me/94742112767" target="_blank"
                                                class="tj-primary-btn ib-philosophy-btn">
                                                <span class="btn-text topic-color2"><span>Enquire Now</span></span>
                                                <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Training Intro Section -->

                <!-- start: Featured Programs Section (Stack Layout) -->
                <section class="tj-service-section-5 section-gap tj-contact-section-2 test2">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="sec-heading style-4 text-center">
                                    <span class="sub-title wow fadeInUp" data-wow-delay=".3s"><i
                                            class="tji-box"></i>Recommended Programs</span>
                                    <h2 class="sec-title title-anim topic-color">Our Flagship Training & Certificates.
                                    </h2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="service-wrapper">

                                    <?php
                                    if ($prog_result && $prog_result->num_rows > 0):
                                        $count = 1; // Counter for numbering
                                        while ($prog = $prog_result->fetch_assoc()):
                                            // Format number as 01., 02., etc.
                                            $num_formatted = str_pad($count, 2, '0', STR_PAD_LEFT) . '.';
                                            ?>

                                            <!-- Dynamic Program Item -->
                                            <div class="service-item style-5 service-stack">
                                                <div class="service-content-area">
                                                    <div class="service-icon">
                                                        <i class="<?php echo htmlspecialchars($prog['icon_class']); ?>"></i>
                                                    </div>
                                                    <div class="service-content">
                                                        <span class="no"><?php echo $num_formatted; ?></span>
                                                        <h3 class="title">
                                                            <a
                                                                href="contact.php"><?php echo htmlspecialchars($prog['title']); ?></a>
                                                        </h3>
                                                        <p class="desc"><?php echo htmlspecialchars($prog['description']); ?>
                                                        </p>
                                                        <a class="tj-primary-btn" href="contact.php">
                                                            <span class="btn-text topic-color2"><span>Enquire Now</span></span>
                                                            <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="service-img">
                                                    <img src="<?php echo htmlspecialchars($prog['image_path']); ?>"
                                                        alt="<?php echo htmlspecialchars($prog['title']); ?>">
                                                </div>
                                            </div>

                                            <?php
                                            $count++;
                                        endwhile;
                                    else:
                                        ?>
                                        <p class="text-center">New programs will be updated soon.</p>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-shape-1"><img src="assets/images/shape/pattern-2.svg" alt=""></div>
                    <div class="bg-shape-2"><img src="assets/images/shape/pattern-3.svg" alt=""></div>
                </section>
                <!-- end: Featured Programs Section -->

                <!-- start: Practical Business Workshops (Interactive Detail Drawer List) -->
                <section id="services" class="h5-service-section overflow-hidden section-gap ib-workshops-section">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="sec-heading-wrap style-3 mb-4">
                                    <span class="sub-title wow fadeInUp" data-wow-delay=".2s">
                                        <i class="tji-box"></i>Corporate & Public Workshops
                                    </span>
                                    <div class="heading-wrap-content">
                                        <div class="sec-heading style-3">
                                            <h2 class="sec-title text-anim topic-color">
                                                Practical Business <span>Workshops.</span>
                                            </h2>
                                            <p class="text-muted mt-2 mb-0" style="font-size: 15px; max-width: 650px;">
                                                Interactive one-day workshops designed for business owners, executives,
                                                and management teams. Click any workshop below to review the target
                                                audience, curriculum topics, and measurable outcomes.
                                            </p>
                                        </div>
                                        <div class="btn-area wow fadeInUp" data-wow-delay=".4s">
                                            <a class="tj-primary-btn" href="contact.php">
                                                <span class="btn-text topic-color2"><span>Request a
                                                        Workshop</span></span>
                                                <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Filter Tabs -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="ib-workshop-filters wow fadeInUp" data-wow-delay=".3s">
                                    <button type="button" class="ib-filter-pill active" data-filter="all">All Workshops (<?php echo count($workshops); ?>)</button>
                                    <button type="button" class="ib-filter-pill" data-filter="strategy">Strategy &
                                        Management</button>
                                    <button type="button" class="ib-filter-pill" data-filter="finance">Finance &
                                        Costing</button>
                                    <button type="button" class="ib-filter-pill"
                                        data-filter="manufacturing">Manufacturing</button>
                                    <button type="button" class="ib-filter-pill" data-filter="sales">Sales &
                                        Distribution</button>
                                    <button type="button" class="ib-filter-pill" data-filter="export">Export &
                                        Trade</button>
                                    <button type="button" class="ib-filter-pill" data-filter="digital">Digital &
                                        AI</button>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion List Container -->
                        <div class="row">
                            <div class="col-12">
                                <div class="ib-workshop-accordion-wrap wow fadeInUp" data-wow-delay=".4s">

                                    <?php if (!empty($workshops)): 
                                        $workshop_idx = 0;
                                        foreach ($workshops as $item): 
                                            $workshop_idx++;
                                            $num_str = str_pad($item['display_order'] > 0 ? $item['display_order'] : $workshop_idx, 2, '0', STR_PAD_LEFT);
                                            $is_first = ($workshop_idx === 1);
                                            $topics_arr = preg_split('/\r\n|\r|\n/', trim($item['key_topics']));
                                            $subject_param = urlencode($item['title'] . ' Workshop');
                                    ?>
                                        <!-- Workshop <?php echo $num_str; ?> -->
                                        <div class="ib-workshop-drawer <?php echo $is_first ? 'active' : ''; ?>" data-category="<?php echo htmlspecialchars($item['category_slug']); ?>">
                                            <div class="ib-drawer-header" role="button" tabindex="0">
                                                <div class="ib-header-left">
                                                    <span class="ib-drawer-num"><?php echo $num_str; ?></span>
                                                    <div class="ib-header-info">
                                                        <h4 class="ib-drawer-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                                        <div class="ib-drawer-tags">
                                                            <span class="ib-tag-badge">
                                                                <i class="<?php echo htmlspecialchars($item['category_icon']); ?>"></i> 
                                                                <?php echo htmlspecialchars($item['category_name']); ?>
                                                            </span>
                                                            <span class="ib-time-badge">
                                                                <i class="fa-regular fa-clock"></i>
                                                                <?php echo htmlspecialchars($item['duration']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ib-header-right">
                                                    <span class="ib-drawer-action-text">Explore Details</span>
                                                    <div class="ib-drawer-arrow"><i class="fa-solid fa-chevron-down"></i></div>
                                                </div>
                                            </div>
                                            <div class="ib-drawer-content">
                                                <div class="ib-drawer-grid">
                                                    <!-- Target Audience -->
                                                    <div class="ib-drawer-col audience-col">
                                                        <div class="ib-content-card">
                                                            <div class="ib-card-head">
                                                                <div class="ib-card-icon"><i class="fa-solid fa-users"></i></div>
                                                                <h5>Target Audience</h5>
                                                            </div>
                                                            <p><?php echo nl2br(htmlspecialchars($item['target_audience'])); ?></p>
                                                        </div>
                                                    </div>
                                                    <!-- Key Topics -->
                                                    <div class="ib-drawer-col topics-col">
                                                        <div class="ib-content-card">
                                                            <div class="ib-card-head">
                                                                <div class="ib-card-icon"><i class="fa-solid fa-list-check"></i></div>
                                                                <h5>Key Topics Covered</h5>
                                                            </div>
                                                            <ul class="ib-topic-bullets">
                                                                <?php 
                                                                foreach ($topics_arr as $topic_line):
                                                                    $topic_line = trim($topic_line);
                                                                    if (!empty($topic_line)):
                                                                        $topic_line = ltrim($topic_line, "-*• \t");
                                                                ?>
                                                                    <li><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($topic_line); ?></li>
                                                                <?php 
                                                                    endif;
                                                                endforeach; 
                                                                ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <!-- Expected Outcome -->
                                                    <div class="ib-drawer-col outcome-col">
                                                        <div class="ib-content-card">
                                                            <div class="ib-card-head">
                                                                <div class="ib-card-icon"><i class="fa-solid fa-bullseye"></i></div>
                                                                <h5>Expected Outcome</h5>
                                                            </div>
                                                            <p><?php echo nl2br(htmlspecialchars($item['expected_outcome'])); ?></p>
                                                            <a href="contact.php?subject=<?php echo $subject_param; ?>" class="ib-enquire-cta">
                                                                <span>Enquire for Workshop</span>
                                                                <i class="fa-solid fa-arrow-right"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                        <div class="text-center py-5">
                                            <p class="text-muted">No workshops are currently published. Please check back soon.</p>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: 10 Practical Business Workshops -->

                <!-- start: Advanced Diploma-Level Programmes (Award Section) -->
                <section class="tj-award section-gap tj-contact-section-2 test2">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="sec-heading sec-heading-centered style-2 style-6">
                                    <span class="sub-title wow fadeInUp" data-wow-delay=".3s"><i
                                            class="tji-box"></i>Long-Term Development</span>
                                    <h2 class="sec-title title-anim topic-color">Advanced Diploma-Level Programmes</h2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="tj-award-wrapper tj-active-bg-wrapper">

                                    <?php
                                    if ($dip_result && $dip_result->num_rows > 0):
                                        $count = 1;
                                        while ($dip = $dip_result->fetch_assoc()):
                                            $num_formatted = str_pad($count, 2, '0', STR_PAD_LEFT) . '.';
                                            ?>
                                            <!-- Dynamic Diploma Item -->
                                            <div class="tj-award-item tj-active-bg-item wow fadeInUp" data-wow-delay=".3s"
                                                onclick="window.location.href='contact.php'" role="button" tabindex="0">
                                                <div class="tj-award-item-inner">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-md-2 tj-award-img-wrapper">
                                                            <h6 class="tj-award-index"><?php echo $num_formatted; ?></h6>
                                                        </div>
                                                        <div class="col-md-7 col-lg-7 tj-award-title-wrapper">
                                                            <h5 class="tj-award-title"><?php echo $dip['title']; ?></h5>
                                                        </div>
                                                        <div class="col-md-3 tj-award-date-wrapper text-md-end">
                                                            <h6 class="tj-award-result">Duration</h6>
                                                            <h6 class="tj-award-date">
                                                                <?php echo htmlspecialchars($dip['duration']); ?> <i
                                                                    class="fa-solid fa-arrow-right-long ib-diploma-arrow"></i>
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            $count++;
                                        endwhile;
                                    else:
                                        ?>
                                        <p class="text-center mt-4">No diploma programs available at the moment.</p>
                                    <?php endif; ?>

                                    <span class="active-bg"></span>
                                </div>
                                <div class="text-center mt-5">
                                    <a class="tj-primary-btn" href="contact.php">
                                        <span class="btn-text topic-color2">Request Full
                                            Syllabus</span></span>
                                        <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-shape-1"><img src="assets/images/shape/pattern-2.svg" alt=""></div>
                    <div class="bg-shape-2"><img src="assets/images/shape/pattern-3.svg" alt=""></div>
                </section>
                <!-- end: Advanced Diploma-Level Programmes -->

                <!-- start: FAQs / Delivery Model -->
                <section class="h7-faq section-gap slidebar-stickiy-container bg-light test2">
                    <div class="container">
                        <div class="row justify-content-between">
                            <!-- Left Sticky Sidebar -->
                            <div class="col-12 col-lg-4">
                                <div class="sec-heading style-2 style-7 slidebar-stickiy">
                                    <span class="sub-title wow fadeInUp" data-wow-delay=".3s"><i class="tji-box"></i>
                                        Common Queries</span>
                                    <h2 class="sec-title text-anim topic-color">Find answers to common questions</h2>
                                    <a class="number" href="tel:+94711255610">
                                        <span class="call-icon"><i class="tji-phone"></i></span>
                                    </a>
                                </div>
                            </div>

                            <!-- Right FAQ Accordion -->
                            <div class="col-12 col-lg-8">
                                <div class="accordion tj-faq style-2 h7-faq-wrapper" id="faqTwo">

                                    <!-- FAQ 1 -->
                                    <div class="accordion-item active wow fadeInUp" data-wow-delay=".3s">
                                        <button class="faq-title" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faqTwo-1" aria-expanded="true">
                                            Who should attend these programs?
                                        </button>
                                        <div id="faqTwo-1" class="collapse show" data-bs-parent="#faqTwo">
                                            <div class="accordion-body faq-text">
                                                <p>Our programs are specifically designed for SME owners, company
                                                    directors, functional managers, sales executives, entrepreneurs, and
                                                    professionals who are looking to implement practical improvements in
                                                    their organizations.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- FAQ 2 -->
                                    <div class="accordion-item wow fadeInUp" data-wow-delay=".3s">
                                        <button class="faq-title collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faqTwo-2" aria-expanded="false">
                                            What is the Delivery Model and duration?
                                        </button>
                                        <div id="faqTwo-2" class="collapse" data-bs-parent="#faqTwo">
                                            <div class="accordion-body faq-text">
                                                <p>We offer multiple delivery models based on your needs: Short
                                                    Workshops (1 day), Intensive Workshops (2-3 days), Certificate
                                                    Programmes (4-8 weeks), and Professional Diplomas (3-6 months).
                                                    Delivery includes interactive classes, practical exercises, and case
                                                    studies.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- FAQ 3 -->
                                    <div class="accordion-item wow fadeInUp" data-wow-delay=".3s">
                                        <button class="faq-title collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faqTwo-3" aria-expanded="false">
                                            Do participants receive a certificate?
                                        </button>
                                        <div id="faqTwo-3" class="collapse" data-bs-parent="#faqTwo">
                                            <div class="accordion-body faq-text">
                                                <p>Yes. Participants who successfully complete the requirements will
                                                    receive an "IB Consultants Professional Training Certificate" or a
                                                    "Certificate of Completion," acknowledging their practical business
                                                    education and capability.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- FAQ 4 -->
                                    <div class="accordion-item wow fadeInUp" data-wow-delay=".3s">
                                        <button class="faq-title collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faqTwo-4" aria-expanded="false">
                                            Do you offer In-House Corporate Training?
                                        </button>
                                        <div id="faqTwo-4" class="collapse" data-bs-parent="#faqTwo">
                                            <div class="accordion-body faq-text">
                                                <p>Yes. While we host public workshops with open registration, we also
                                                    provide customized in-house corporate workshops tailored
                                                    specifically for your company's management teams and operational
                                                    requirements.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- FAQ 5 -->
                                    <div class="accordion-item wow fadeInUp" data-wow-delay=".3s">
                                        <button class="faq-title collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faqTwo-5" aria-expanded="false">
                                            How do I register for a program?
                                        </button>
                                        <div id="faqTwo-5" class="collapse" data-bs-parent="#faqTwo">
                                            <div class="accordion-body faq-text">
                                                <p>Getting started is easy! Simply reach out to us through our contact
                                                    page or give us a call at 071 125 5610, and our team will guide you
                                                    through the upcoming batch schedules and registration process.</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                <!-- end: FAQs / Delivery Model -->

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
                                        <h2 class="title title-anim custom-cta-title"
                                            style="color: #ffffff; font-weight: 700; line-height: 1.25; margin-bottom: 15px;">
                                            Let’s Build Your Future Together.
                                        </h2>
                                        <p class="cta-desc custom-cta-desc wow fadeInUp" data-wow-delay=".4s"
                                            style="color: rgba(255, 255, 255, 0.85); font-weight: 400; margin-bottom: 30px; line-height: 1.5; max-width: 480px;">
                                            Partner with us to implement practical strategies, optimize operations, and
                                            achieve sustainable growth for your SME or manufacturing business in Sri
                                            Lanka.
                                        </p>
                                        <div class="cta-btn wow fadeInUp" data-wow-delay=".5s">
                                            <a class="tj-primary-btn btn-dark" href="contact.php">
                                                <span class="btn-text topic-color2"><span>Get
                                                        Started
                                                        Now</span></span>
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