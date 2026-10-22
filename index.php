<!doctype html>
<html class="no-js" lang="en-LK">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags for Sri Lanka -->
    <title>IB Business Consultants | Top SME & Manufacturing Business Consulting in Sri Lanka</title>
    <meta name="description"
        content="IB Business Consultants provides practical business consulting, profitability improvement, and market development for SMEs and manufacturing businesses in Sri Lanka.">
    <meta name="keywords"
        content="management trainee programs in sri lanka, Best Management Trainee Programs in Sri Lanka, business consulting firms in sri lanka, business consultant sri lanka, Best accountant services for small business sri lanka, Business Consultant Sri Lanka, SME Consulting, Profit Improvement, Manufacturing Consulting, IB Business Consultants Horana">
    <meta name="author" content="IB Business Consultants">
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


    <?php
    include_once 'db.php';
    $slider_query = "SELECT * FROM hero_slider WHERE is_published = 1 ORDER BY id ASC";
    $slider_result = $conn->query($slider_query);
    // client review
    $reviews_query = "SELECT * FROM client_reviews WHERE is_published = 1 ORDER BY display_order ASC, id DESC";
    $reviews_result = $conn->query($reviews_query);
    ?>


</head>

<body>
    <div class="body-overlay"></div>

    <!-- Preloader Start -->
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
    <!-- Preloader end -->

    <?php include 'header.php'; ?>

    <!-- back to top start -->
    <div id="tj-back-to-top" class="back-to-top-bg"><span id="tj-back-to-top-percentage"
            class="back-to-top-text"></span></div>
    <!-- back to top end -->

    <div id="smooth-wrapper">
        <div id="smooth-content">
            <main id="primary" class="site-main">



                <!-- start: Hero Banner Slider -->
                <section class="tj-slider-section overflow-hidden">
                    <div class="swiper hero-slider h-100">
                        <div class="swiper-wrapper h-100 mt-lg-custom">

                            <?php
                            if ($slider_result && $slider_result->num_rows > 0):
                                while ($slide = $slider_result->fetch_assoc()):
                                    ?>

                                    <div class="swiper-slide tj-slider-item">
                                        <div class="slider-bg-image"
                                            style="background-image: url('<?php echo $slide['image_path']; ?>');"></div>
                                        <div class="container relative z-3 mt-lg-custom">
                                            <div class="row">
                                                <div class="col-12 col-md-10 col-lg-8 col-xl-8">
                                                    <div class="slider-wrapper text-center text-lg-start">
                                                        <div class="slider-content">
                                                            <h1 class="slider-title text-white">
                                                                <?php
                                                                $slide_title = $slide['title'];
                                                                if (strpos($slide_title, '<span') === false) {
                                                                    $words = explode(' ', trim($slide_title));
                                                                    if (count($words) > 1) {
                                                                        $last_word = array_pop($words);
                                                                        $slide_title = implode(' ', $words) . ' <span>' . $last_word . '</span>';
                                                                    }
                                                                }
                                                                echo $slide_title;
                                                                ?>
                                                            </h1>
                                                            <div class="slider-desc text-white">
                                                                <?php echo $slide['description']; ?>
                                                            </div>
                                                            <div
                                                                class="slider-btn d-flex justify-content-center justify-content-lg-start mt-4">
                                                                <a class="tj-primary-btn" href="contact.php">
                                                                    <span class="btn-text topic-color2"><span>Get a
                                                                            Consultation</span></span>
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
                                    <?php
                                endwhile;
                            else:
                                ?>

                                <div class="swiper-slide tj-slider-item">
                                    <div class="slider-bg-image"
                                        style="background-image: url('assets/images/hero/slider-1.jpg');"></div>
                                    <div class="container relative z-3 mt-lg-custom">
                                        <div class="row">
                                            <div class="col-12 col-md-10 col-lg-8 col-xl-8">
                                                <div class="slider-wrapper text-center text-lg-start">
                                                    <div class="slider-content">
                                                        <h1 class="slider-title text-white">Expertise For Your
                                                            <span>Growth.</span>
                                                        </h1>
                                                        <div class="slider-desc text-white">Practical Business & Profit
                                                            Improvement Consulting for Sri Lankan SMEs and Manufacturing
                                                            Businesses.</div>
                                                        <div
                                                            class="slider-btn d-flex justify-content-center justify-content-lg-start mt-4">
                                                            <a class="tj-primary-btn" href="contact.php">
                                                                <span class="btn-text topic-color2"><span>Get a
                                                                        Consultation</span></span>
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

                        </div>

                        <div class="hero-navigation d-inline-flex wow fadeIn" data-wow-delay="1.5s">
                            <div class="slider-prev">
                                <span class="anim-icon">
                                    <i class="tji-arrow-left"></i>
                                    <i class="tji-arrow-left"></i>
                                </span>
                            </div>
                            <div class="slider-next">
                                <span class="anim-icon">
                                    <i class="tji-arrow-right"></i>
                                    <i class="tji-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Hero Banner Slider -->

                <section class="tj-client-section section-gap client-section-custom"
                    aria-label="Our Core Industries and Business Focus in Sri Lanka">

                    <div class="container-fluid client-container">
                        <div class="row">
                            <div class="col-12">

                                <!-- SEO Optimized Heading -->
                                <div class="text-center wow fadeIn" data-wow-delay=".3s"
                                    style="margin-bottom: 40px; padding: 0 15px;">

                                    <!-- Main Heading (Mobile Responsive Fix) -->
                                    <h2
                                        style="color: var(--primary-navy) !important; font-weight: 700; margin-bottom: 12px; display: block !important; font-size: 30px; line-height: 1.3;">
                                        Empowering Key <span
                                            style="color: var(--accent-gold) !important; font-weight: 900; font-size: 1.2em;">Industries</span>
                                    </h2>

                                    <!-- Subtitle -->
                                    <p
                                        style="color: var(--text-black); font-size: 16px; font-weight: 500; margin-top: 0;">
                                        We provide tailored consulting solutions across diverse sectors in Sri Lanka.
                                    </p>

                                </div>

                                <!-- Industry/Trust Badges Slider (Replacing Client Logos) -->
                                <div class="swiper client-slider client-slider-1 wow fadeIn" data-wow-delay=".5s">
                                    <div class="swiper-wrapper p-3 client-swiper-custom">

                                        <!-- Item 1: SME Development -->
                                        <div class="swiper-slide client-item">
                                            <div class="industry-badge-custom">
                                                <i class="fa-solid fa-briefcase"></i>
                                                <span>SME Development</span>
                                            </div>
                                        </div>

                                        <!-- Item 2: Manufacturing -->
                                        <div class="swiper-slide client-item">
                                            <div class="industry-badge-custom">
                                                <i class="fa-solid fa-industry"></i>
                                                <span>Manufacturing Sector</span>
                                            </div>
                                        </div>

                                        <!-- Item 3: Retail & Distribution -->
                                        <div class="swiper-slide client-item">
                                            <div class="industry-badge-custom">
                                                <i class="fa-solid fa-store"></i>
                                                <span>Retail & Distribution</span>
                                            </div>
                                        </div>

                                        <!-- Item 4: Export Business -->
                                        <div class="swiper-slide client-item">
                                            <div class="industry-badge-custom">
                                                <i class="fa-solid fa-ship"></i>
                                                <span>Export Businesses</span>
                                            </div>
                                        </div>

                                        <!-- Item 5: FMCG Sector -->
                                        <div class="swiper-slide client-item">
                                            <div class="industry-badge-custom">
                                                <i class="fa-solid fa-boxes-stacked"></i>
                                                <span>FMCG Sector</span>
                                            </div>
                                        </div>

                                        <!-- Item 6: Corporate Strategy -->
                                        <div class="swiper-slide client-item">
                                            <div class="industry-badge-custom">
                                                <i class="fa-solid fa-chart-line"></i>
                                                <span>Corporate Strategy</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- start: About Summary Section -->
                <section class="tj-about-section section-gap-index about-section-custom">
                    <div class="container">
                        <!-- Top Row: Company Overview & Core Benefits -->
                        <div class="row mb-5 align-items-center">

                            <!-- Left Side: Company Overview -->
                            <div class="col-lg-6 col-md-12 wow fadeInLeft" data-wow-duration="1.2s"
                                data-wow-delay="0.2s">
                                <div class="about-content">
                                    <div class="sec-heading style-2 mb-4">
                                        <span class="sub-title">Company Overview</span>
                                        <h2 class="sec-title title-highlight custom-mobile-title">Your Trusted Partner
                                            for <br>Sustainable Business Growth.</h2>
                                    </div>

                                    <p class="overview-text-justify custom-about-text mb-3">
                                        At IB Consultants, we believe that every business has the potential to grow with
                                        the right guidance and a clear direction. We work closely with our clients to
                                        identify challenges, uncover new opportunities, and develop strategies that
                                        deliver lasting value. By combining practical industry knowledge with a
                                        personalized approach, we help businesses make informed decisions, improve
                                        efficiency, and build a strong foundation for sustainable growth in an
                                        increasingly competitive market.
                                    </p>

                                    <!-- Website Positioning Statement -->
                                    <div class="ib-positioning-quote mb-4">
                                        <i class="fa-solid fa-quote-left"></i>
                                        <p>“Practical, results-driven business consulting backed by hands on industry
                                            experience in sales, marketing, international business, manufacturing,
                                            accounting, finance, cost management, customer relations, negotiation,
                                            and market development.”
                                        </p>
                                    </div>

                                    <div class="d-flex flex-wrap gap-3 align-items-center">
                                        <a class="tj-primary-btn index-learn-more" href="about.php"
                                            aria-label="Learn more about IB Consultants company overview">
                                            <span class="btn-text topic-color2"><span>Learn More</span></span>
                                            <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                        </a>
                                        <!-- <a class="ib-company-profile-card-btn"
                                            href="assets/docs/IB-Consultants-Company-Profile.pdf"
                                            download="IB-Consultants-Company-Profile.pdf"
                                            aria-label="Download IB Consultants Company Profile">
                                            <span class="ib-btn-file-icon">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </span>
                                            <span class="ib-btn-text-group">
                                                <span class="ib-btn-main-text">Download Company Profile</span>
                                                <span class="ib-btn-sub-text"><i class="fa-solid fa-circle-check"></i>
                                                    Official Corporate Overview</span>
                                            </span>
                                            <span class="ib-btn-download-circle">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </span>
                                        </a> -->
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Core Benefits -->
                            <div class="col-lg-6 col-md-12 mt-5 mt-lg-0 wow fadeInRight" data-wow-duration="1.2s"
                                data-wow-delay="0.4s">
                                <div class="core-benefits-box">
                                    <h3 class="benefits-title">Why Choose IB Consultants?</h3>
                                    <ul class="benefits-list">
                                        <li>
                                            <div class="icon-wrap"><i class="tji-complete"></i></div>
                                            <div class="content-wrap">
                                                <h4 class="benefit-heading">Practical Solutions</h4>
                                                <p class="benefit-p">Actionable, result-oriented strategies tailored for
                                                    your business
                                                    rather than just theoretical advice.</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon-wrap"><i class="tji-complete"></i></div>
                                            <div class="content-wrap">
                                                <h4 class="benefit-heading">SME & Manufacturing Expertise</h4>
                                                <p class="benefit-p">Deep understanding of the local Sri Lankan market,
                                                    specifically for
                                                    SMEs and manufacturing sectors.</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="icon-wrap"><i class="tji-complete"></i></div>
                                            <div class="content-wrap">
                                                <h4 class="benefit-heading">Continuous Improvement</h4>
                                                <p class="benefit-p">We establish clear KPIs and ongoing management
                                                    reviews to ensure
                                                    long-term, sustainable growth.</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                        <!-- Bottom Row: 3 Step Cards -->
                        <div class="row row-gap-4 mt-4">

                            <!-- Card 01  -->
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.5s">
                                <div class="custom-feature-card h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="icon-box-rounded">
                                            <i class="tji-complete"></i>
                                        </div>
                                        <span class="step-num">01.</span>
                                    </div>
                                    <h4 class="feature-title">Understand & Analyse</h4>
                                    <p class="feature-text">We begin by understanding your business, diagnosing current
                                        conditions, and analyzing core challenges.</p>
                                </div>
                            </div>

                            <!-- Card 02  -->
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.7s">
                                <div class="custom-feature-card h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="icon-box-rounded">
                                            <i class="tji-worldwide"></i>
                                        </div>
                                        <span class="step-num">02.</span>
                                    </div>
                                    <h4 class="feature-title">Strategize & Implement</h4>
                                    <p class="feature-text">We turn analysis into practical action plans and actively
                                        support execution to ensure your goals are met.</p>
                                </div>
                            </div>

                            <!-- Card 03  -->
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.9s">
                                <div class="custom-feature-card h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="icon-box-rounded">
                                            <i class="tji-growth"></i>
                                        </div>
                                        <span class="step-num">03.</span>
                                    </div>
                                    <h4 class="feature-title">Measure & Improve</h4>
                                    <p class="feature-text">We review progress using clear KPIs to establish an
                                        improvement culture and ensure long-term success.</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
                <!-- end: About Summary Section -->

                <!-- start: Countup Section -->
                <div class="tj-countup-section countup-overlap-section">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="countup-wrap countup-video-card">
                                    <!-- Background Video inside floating card -->
                                    <video autoplay muted loop playsinline class="countup-bg-video">
                                        <source src="assets/video/countup-bg.webm" type="video/webm">
                                        <source src="assets/video/countup-bg.mp4" type="video/mp4">
                                    </video>

                                    <!-- Dark Luxury Navy Overlay inside floating card -->
                                    <div class="countup-overlay"></div>

                                    <div class="countup-item">
                                        <div class="inline-content">
                                            <span class="odometer countup-number" data-count="15"></span>
                                            <span class="count-plus">+</span>
                                        </div>
                                        <span class="count-text">Years Experience</span>
                                        <span class="count-separator"
                                            data-bg-image="assets/images/shape/separator.svg"></span>
                                    </div>

                                    <div class="countup-item">
                                        <div class="inline-content">
                                            <span class="odometer countup-number" data-count="150"></span>
                                            <span class="count-plus">+</span>
                                        </div>
                                        <span class="count-text">Projects Completed</span>
                                        <span class="count-separator"
                                            data-bg-image="assets/images/shape/separator.svg"></span>
                                    </div>

                                    <div class="countup-item">
                                        <div class="inline-content">
                                            <span class="odometer countup-number" data-count="50"></span>
                                            <span class="count-plus">+</span>
                                        </div>
                                        <span class="count-text">Happy Customers</span>
                                        <span class="count-separator"
                                            data-bg-image="assets/images/shape/separator.svg"></span>
                                    </div>

                                    <div class="countup-item">
                                        <div class="inline-content">
                                            <span class="odometer countup-number" data-count="8"></span>
                                            <span class="count-plus">+</span>
                                        </div>
                                        <span class="count-text">Expert Team Members</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end: Countup Section -->

                <!-- Main Services Section -->
                <section class="tj-service-section overflow-hidden section-gap section-gap-x custom-mobile-padding">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="sec-heading text-center mb-4 mt-4">
                                    <span class="sub-title text-white wow fadeInUp" data-wow-delay=".3s">
                                        <i class="tji-box"></i>Our Main Services
                                    </span>
                                    <h2 class="sec-title text-white title-anim custom-service-title">
                                        Select a Service to Explore&nbsp;<span>More.</span>
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4 gy-4">

                            <!-- Consultancy Services Card -->
                            <div class="col-xl-5 col-lg-5 col-md-6 col-12 wow fadeInUp" data-wow-delay=".4s">
                                <a href="consultancy-service.php"
                                    class="service-item style-1 main-service-card custom-mobile-card d-block no-text-decoration">
                                    <div class="service-img">
                                        <img src="assets/images/service/service-1.webp" alt="Consultancy Services"
                                            class="img-fluid">
                                    </div>
                                    <div class="service-icon">
                                        <i class="tji-service-1"></i>
                                    </div>
                                    <div class="service-content">
                                        <h3 class="title">Consultancy Services</h3>
                                        <p class="desc text desc-text-white" style="color: #ffffff !important;">Expert
                                            guidance to develop
                                            customized strategies, improve operations, and build long-term value.</p>
                                        <span class="text-btn">
                                            <span class="btn-text <span class=" btn-text topic-color2"><span>More
                                                    Details</span></span>
                                            <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- Training and Programs Card -->
                            <div class="col-xl-5 col-lg-5 col-md-6 col-12 wow fadeInUp" data-wow-delay=".5s">
                                <a href="training-programs.php"
                                    class="service-item style-1 main-service-card custom-mobile-card d-block no-text-decoration">
                                    <div class="service-img">
                                        <img src="assets/images/service/service-2.webp" alt="Training and Programs"
                                            class="img-fluid">
                                    </div>
                                    <div class="service-icon">
                                        <i class="tji-service-4"></i>
                                    </div>
                                    <div class="service-content">
                                        <h3 class="title">Training & <br class="d-none d-md-block"> Programs</h3>
                                        <p class="desc desc-text-white" style="color: #ffffff !important;">Empower your
                                            employees with the
                                            skills, knowledge, and tools they need through our training.</p>
                                        <span class="text-btn">
                                            <span class="btn-text <span class=" btn-text topic-color2"><span>More
                                                    Details</span></span>
                                            <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                                        </span>
                                    </div>
                                </a>
                            </div>

                        </div>
                    </div>
                    <div class="bg-shape-1"><img src="assets/images/shape/pattern-2.svg" alt=""></div>
                    <div class="bg-shape-2"><img src="assets/images/shape/pattern-3.svg" alt=""></div>
                </section>

                <!-- start: Testimonial Section -->
                <section class="tj-testimonial-section-2 section-gap ib-testimonial-section-modern">
                    <div class="container">
                        <!-- Section Heading -->
                        <div class="row">
                            <div class="col-12">
                                <div class="sec-heading text-center mb-5">
                                    <span class="sub-title wow fadeInUp" data-wow-delay=".1s">
                                        <i class="tji-box"></i> CLIENT ENDORSEMENTS
                                    </span>
                                    <h2 class="sec-title title-anim topic-color">
                                        Proven Results. <span>Real Impact.</span>
                                    </h2>
                                    <p class="desc wow fadeInUp" data-wow-delay=".2s"
                                        style="max-width: 640px; margin: 12px auto 0; color: #5a6578;">
                                        Real feedback from Sri Lankan SME founders, CEOs, and factory directors who
                                        transformed their operations with IB Consultants.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row row-gap-4 align-items-center">
                            <!-- Left Column: Trust Matrix & Credibility Hub -->
                            <div class="col-lg-5">
                                <div class="ib-trust-matrix-card wow fadeInLeft" data-wow-delay=".3s">
                                    <div class="ib-trust-media">
                                        <img data-speed=".8" src="assets/images/testimonial/testimonial-img.webp"
                                            alt="IB Consultants Client Trust" class="ib-trust-img">
                                        <div class="ib-trust-overlay"></div>
                                        <div class="ib-trust-top-pill">
                                            <i class="fa-solid fa-shield-halved"></i>
                                            <span>On-The-Ground Implementation</span>
                                        </div>
                                        <div class="ib-trust-caption">
                                            <h3>Empowering Sri Lankan SMEs <br><span>Into Market Leaders</span></h3>
                                        </div>
                                    </div>

                                    <!-- Floating Trust Hub Scorecard -->
                                    <div class="ib-trust-hub-card wow fadeInUp" data-wow-delay=".5s">
                                        <div class="ib-trust-rating-row">
                                            <div class="ib-trust-rating-score">
                                                <span class="ib-score-num">4.9</span>
                                                <span class="ib-score-out-of">/ 5.0</span>
                                            </div>
                                            <div class="ib-trust-stars-wrap">
                                                <div class="ib-trust-stars">
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                </div>
                                                <span class="ib-trust-subtext">Over 98% Client Satisfaction Rate</span>
                                            </div>
                                        </div>

                                        <div class="ib-trust-divider"></div>

                                        <div class="ib-trust-stats-grid">
                                            <div class="ib-trust-stat-box">
                                                <span class="ib-stat-val">150+</span>
                                                <span class="ib-stat-lbl">Projects Completed</span>
                                            </div>
                                            <div class="ib-trust-stat-box">
                                                <span class="ib-stat-val">15+</span>
                                                <span class="ib-stat-lbl">Years Experience</span>
                                            </div>
                                            <div class="ib-trust-stat-box">
                                                <span class="ib-stat-val">100%</span>
                                                <span class="ib-stat-lbl">Practical Focus</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Bespoke Executive Testimonial Slider -->
                            <div class="col-lg-7">
                                <div class="ib-testimonial-showcase wow fadeInRight" data-wow-delay=".4s">
                                    <div class="swiper testimonial-slider-2">
                                        <div class="swiper-wrapper">

                                            <?php
                                            if ($reviews_result && $reviews_result->num_rows > 0):
                                                while ($rev = $reviews_result->fetch_assoc()):
                                                    ?>
                                                    <div class="swiper-slide">
                                                        <div class="ib-testimonial-card">
                                                            <div class="ib-card-top-bar">
                                                                <span class="ib-sector-tag">
                                                                    <i
                                                                        class="<?php echo htmlspecialchars($rev['sector_icon']); ?>"></i>
                                                                    <?php echo htmlspecialchars($rev['sector_name']); ?>
                                                                </span>
                                                                <div class="ib-card-stars">
                                                                    <?php for ($i = 0; $i < $rev['stars']; $i++) {
                                                                        echo '<i class="fa-solid fa-star"></i> ';
                                                                    } ?>
                                                                </div>
                                                            </div>

                                                            <div class="ib-quote-content">
                                                                <div class="ib-quote-icon">
                                                                    <i class="fa-solid fa-quote-left"></i>
                                                                </div>
                                                                <p class="ib-quote-text">
                                                                    “<?php echo htmlspecialchars($rev['quote_text']); ?>”
                                                                </p>
                                                            </div>

                                                            <div class="ib-impact-tag">
                                                                <i class="fa-solid fa-chart-line"></i>
                                                                <span><strong>Key Impact:</strong>
                                                                    <?php echo htmlspecialchars($rev['impact_text']); ?></span>
                                                            </div>

                                                            <div class="ib-author-footer">
                                                                <div class="ib-author-monogram">
                                                                    <?php echo htmlspecialchars($rev['author_monogram']); ?>
                                                                </div>
                                                                <div class="ib-author-info">
                                                                    <h4 class="ib-author-name">
                                                                        <?php echo htmlspecialchars($rev['author_name']); ?>
                                                                    </h4>
                                                                    <span
                                                                        class="ib-author-company"><?php echo htmlspecialchars($rev['author_company']); ?></span>
                                                                </div>
                                                                <div class="ib-verified-badge">
                                                                    <i class="fa-solid fa-circle-check"></i>
                                                                    <span>Verified Client</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                endwhile;
                                            endif;
                                            ?>

                                        </div>

                                        <!-- Custom Navigation & Pagination Footer -->
                                        <div class="ib-slider-controls">
                                            <div class="swiper-pagination-area ib-pagination-custom"></div>
                                            <div class="ib-nav-buttons">
                                                <button class="slider-prev ib-nav-btn" aria-label="Previous Slide">
                                                    <i class="fa-solid fa-arrow-left"></i>
                                                </button>
                                                <button class="slider-next ib-nav-btn" aria-label="Next Slide">
                                                    <i class="fa-solid fa-arrow-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Testimonial Section -->

                <!-- start: Maquee Area -->
                <section class="h5-maquee z-2 custom-marquee-colors ">
                    <!-- Right to Left Marquee -->
                    <div class="h5-maquee-inner">
                        <div class="swiper h5-maquee-slider">
                            <div class="swiper-wrapper h5-maquee-slider-wrapper">

                                <!-- Item 1 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Business Health Check</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 2 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Profit Improvement</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 3 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Manufacturing Efficiency</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 4 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Market Expansion</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 5 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Corporate Training</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 6 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Operational Excellence</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Left to Right Marquee (RTL) -->
                    <div dir="rtl" class="h5-maquee-inner h5-maquee-inner-rtl">
                        <div class="swiper h5-maquee-slider">
                            <div class="swiper-wrapper h5-maquee-slider-wrapper">

                                <!-- Item 1 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Strategic Planning</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 2 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Cost Reduction</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 3 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Process Optimization</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 4 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">SME Development</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 5 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Leadership Growth</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item 6 -->
                                <div class="swiper-slide h5-maquee-slider-item">
                                    <div class="marquee-box">
                                        <div class="marquee-icon-wrapper">
                                            <div class="marquee-icon"><i class="tji-star"></i></div>
                                        </div>
                                        <div class="marquee-title">
                                            <h4 class="title">Result Oriented</h4>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </section>
                <!-- end: Maquee Area -->

                <!-- start: Call to Action (CTA) Section -->
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
                <!-- end: Call to Action (CTA) Section -->

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