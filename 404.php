<?php
http_response_code(404);
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <meta name="theme-color" content="#091a32">

    <!-- Site Title -->
    <title>404 - Page Not Found | IB Business Consultants</title>

    <!-- Place favicon.ico in the root directory -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/logos/logo-2.webp">
    <link rel="apple-touch-icon" href="assets/images/logos/logo-2.webp">

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

    <style>
        .ib-404-section {
            padding: 120px 0 100px;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            min-height: 75vh;
            display: flex;
            align-items: center;
        }
        .ib-404-number {
            font-size: clamp(90px, 15vw, 170px);
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #091a32 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            letter-spacing: -2px;
        }
        .ib-404-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 50px 30px;
            box-shadow: 0 10px 40px rgba(9, 26, 50, 0.06);
            border: 1px solid #e2e8f0;
            max-width: 700px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="body-overlay"></div>

    <?php include 'header.php'; ?>

    <main id="primary" class="site-main">
        <section class="ib-404-section text-center">
            <div class="container">
                <div class="ib-404-card wow fadeInUp" data-wow-delay=".2s">
                    <div class="ib-404-number">404</div>
                    <h2 class="mb-3" style="color: var(--primary-navy); font-weight: 800;">Page Not Found</h2>
                    <p class="text-muted mb-4" style="font-size: 16px; max-width: 520px; margin: 0 auto;">
                        The page you are looking for might have been moved, renamed, or is temporarily unavailable. Let us help you find what you need.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="index.php" class="tj-primary-btn">
                            <span class="btn-text topic-color2"><span>Back to Home</span></span>
                            <span class="btn-icon"><i class="tji-arrow-right-long"></i></span>
                        </a>
                        <a href="consultancy-service.php" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                            <span>Our Services</span>
                            <i class="fa-solid fa-briefcase"></i>
                        </a>
                        <a href="contact.php" class="btn btn-outline-dark px-4 py-2" style="border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                            <span>Contact Us</span>
                            <i class="fa-solid fa-headset"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <!-- JS here -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>

