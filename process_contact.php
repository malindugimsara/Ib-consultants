<?php
ob_start();

function sendJsonResponse($data) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

// Include Database Connection
include 'db.php';
$env = parse_ini_file(__DIR__ . '/.env');

// Include PHPMailer Classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // XSS protection
    $name = strip_tags(trim($_POST['conName'] ?? ''));
    $email = filter_var(trim($_POST['conEmail'] ?? ''), FILTER_SANITIZE_EMAIL);
    $mobile = strip_tags(trim($_POST['conPhone'] ?? ''));
    $whatsapp = isset($_POST['conWhatsapp']) ? strip_tags(trim($_POST['conWhatsapp'])) : '';
    $service = strip_tags(trim($_POST['conSubject'] ?? 'General Inquiry'));
    $message = strip_tags(trim($_POST['conMessage'] ?? ''));

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(['status' => 'error', 'message' => 'Invalid email address format.']);
    }

    // SQL Injection protection (Graceful database logging)
    if (isset($conn) && $conn && !$conn->connect_error) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, mobile, whatsapp, service, message) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssss", $name, $email, $mobile, $whatsapp, $service, $message);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 2. Email Dispatch Process (PHPMailer)
    $mail = new PHPMailer(true);

    try {
        // Set to 0 for production / AJAX to prevent corrupting JSON response (set to 2 for console debugging)
        $mail->SMTPDebug = 0;

        // SMTP Server Configuration
        $mail->isSMTP();
        $mail->Host = $env['SMTP_HOST'];
        $mail->SMTPAuth = true;

        // SMTP Credentials from .env
        $mail->Username = $env['SMTP_USER'];
        $mail->Password = $env['SMTP_PASS'];

        // Port & Encryption Handling (Port 465 uses SSL/SMTPS, Port 587 uses STARTTLS)
        $port = (int) ($env['SMTP_PORT'] ?? 465);
        $mail->Port = $port;
        if ($port === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        // SSL Certificate Verification Options (Ensures compatibility across hosting environments)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Send Notification Email to Admin
        $adminEmail = !empty($env['ADMIN_EMAIL']) ? $env['ADMIN_EMAIL'] : (!empty($env['SMTP_USER']) ? $env['SMTP_USER'] : 'info@businessconsultant.lk');
        $fromEmail = !empty($env['SMTP_FROM_EMAIL']) ? $env['SMTP_FROM_EMAIL'] : $env['SMTP_USER'];
        $fromName = !empty($env['SMTP_FROM_NAME']) ? $env['SMTP_FROM_NAME'] : 'IB Consultants Website';

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($adminEmail);
        $mail->addReplyTo($email, $name);

        $mail->isHTML(false);
        $mail->Subject = "New Website Inquiry: " . $service;
        $admin_body = "Hello IB Consultants,\n\n";
        $admin_body .= "You have received a new inquiry from your website.\n\n";
        $admin_body .= "Business / Name: $name\n";
        $admin_body .= "Email Address: $email\n";
        $admin_body .= "Mobile Number: $mobile\n";
        $admin_body .= "WhatsApp Number: $whatsapp\n";
        $admin_body .= "Service Required: $service\n\n";
        $admin_body .= "Requirement / Message:\n$message\n";

        $mail->Body = $admin_body;
        $mail->send();

        // 3. Send Automated "Thank You" Confirmation Email to Customer (Dedicated Clean Instance)
        $customerMail = new PHPMailer(true);
        $customerMail->SMTPDebug = 0;
        $customerMail->isSMTP();
        $customerMail->Host = $env['SMTP_HOST'];
        $customerMail->SMTPAuth = true;
        $customerMail->Username = $env['SMTP_USER'];
        $customerMail->Password = $env['SMTP_PASS'];
        $customerMail->Port = $port;
        $customerMail->SMTPSecure = ($port === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $customerMail->SMTPOptions = $mail->SMTPOptions;
        $customerMail->CharSet = 'UTF-8';
        $customerMail->Encoding = 'base64';

        $customerMail->setFrom($fromEmail, 'IB Consultants');
        $customerMail->addAddress($email, $name);
        $customerMail->addReplyTo($adminEmail, 'IB Consultants');

        $customerMail->isHTML(true);
        $customerMail->Subject = "Thank you for contacting IB Consultants";

        // Embed Company Logo
        $logoEmailPath = __DIR__ . '/assets/images/logos/logo-email.png';
        $logoFallbackPath = __DIR__ . '/assets/images/logos/logo-2.png';
        $logoPath = file_exists($logoEmailPath) ? $logoEmailPath : $logoFallbackPath;

        $logoSrc = 'https://businessconsultant.lk/assets/images/logos/logo-email.png';
        if (file_exists($logoPath)) {
            $customerMail->addEmbeddedImage($logoPath, 'ib_logo', 'logo.png');
            $logoSrc = 'cid:ib_logo';
        }

        $customer_body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: "Plus Jakarta Sans", Arial, sans-serif; background-color: #f4f6fa; margin: 0; padding: 0; color: #1e293b; }
                .email-container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(9, 26, 50, 0.08); border-top: 4px solid #d4af37; }
                .email-header { background: #091a32; padding: 28px 20px 22px; text-align: center; color: #ffffff; }
                .email-logo { max-width: 120px; width: 120px; height: auto; margin-bottom: 12px; display: block; margin-left: auto; margin-right: auto; }
                .email-header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 1px; color: #ffffff; }
                .email-header p { margin: 4px 0 0 0; font-size: 12px; color: #d4af37; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; }
                .email-body { padding: 35px 30px; line-height: 1.6; font-size: 15px; color: #334155; }
                .highlight-box { background: #f8fafc; border-left: 4px solid #091a32; padding: 15px 20px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #091a32; }
                .contact-info { margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #64748b; line-height: 1.8; }
                .contact-info a { color: #091a32; text-decoration: none; font-weight: 600; }
                .email-footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; line-height: 1.5; }
            </style>
        </head>
        <body style="font-family: \'Plus Jakarta Sans\', Arial, Helvetica, sans-serif; background-color: #f4f6fa; margin: 0; padding: 0; color: #1e293b;">
            <div class="email-container" style="max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(9, 26, 50, 0.08); border-top: 4px solid #d4af37;">
                <div class="email-header" style="background-color: #091a32; padding: 28px 20px 22px; text-align: center; color: #ffffff;">
                    <!-- Company Official Logo -->
                    <img src="' . $logoSrc . '" alt="IB Consultants Logo" class="email-logo" width="120" style="max-width: 120px; width: 120px; height: auto; margin: 0 auto 12px auto; display: block; border: 0; outline: none; text-decoration: none;">
                    <h1 style="margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 1px; color: #ffffff;">IB CONSULTANTS</h1>
                    <p style="margin: 4px 0 0 0; font-size: 12px; color: #d4af37; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Business &amp; Profit Growth Advisory</p>
                </div>
                <div class="email-body" style="padding: 35px 30px; line-height: 1.6; font-size: 15px; color: #334155;">
                    <p>Dear <strong>' . htmlspecialchars($name) . '</strong>,</p>
                    
                    <p>Thank you for reaching out to <strong>IB Consultants</strong>. We have successfully received your inquiry regarding <strong>"' . htmlspecialchars($service) . '"</strong>.</p>
                    
                    <div class="highlight-box" style="background-color: #f8fafc; border-left: 4px solid #091a32; padding: 15px 20px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #091a32;">
                        <strong>What happens next?</strong><br>
                        Our senior consulting team will review your requirement and get back to you within <strong>24 hours</strong> to discuss actionable strategies for your business.
                    </div>
                    
                    <p>If you have any urgent operational queries in the meantime, feel free to contact us directly:</p>
                    
                    <div class="contact-info" style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #64748b; line-height: 1.8;">
                        Hotline / Call: <a href="tel:+94711255610" style="color: #091a32; text-decoration: none; font-weight: 600;">+94 71 125 5610</a><br>
                        WhatsApp: <a href="https://wa.me/94742112767" target="_blank" style="color: #091a32; text-decoration: none; font-weight: 600;">+94 74 211 2767</a><br>
                        Email: <a href="mailto:info@businessconsultant.lk" style="color: #091a32; text-decoration: none; font-weight: 600;">info@businessconsultant.lk</a><br>
                        Website: <a href="https://businessconsultant.lk" target="_blank" style="color: #091a32; text-decoration: none; font-weight: 600;">www.businessconsultant.lk</a>
                    </div>
                </div>
                <div class="email-footer" style="background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; line-height: 1.5;">
                    &copy; ' . date('Y') . ' IB Business Consultants. Horana, Sri Lanka.<br>This is an automated confirmation email. Please do not reply directly to this message.
                </div>
            </div>
        </body>
        </html>';

        $customerMail->Body = $customer_body;
        $customerMail->AltBody = "Dear " . $name . ",\n\n"
            . "Thank you for reaching out to IB Consultants. We have successfully received your inquiry regarding '" . $service . "'.\n\n"
            . "Our senior consulting team will review your requirement and get back to you within 24 hours to discuss actionable strategies for your business.\n\n"
            . "If you have any urgent operational queries in the meantime, feel free to contact us directly:\n"
            . "Hotline: +94 71 125 5610\n"
            . "WhatsApp: +94 74 211 2767\n"
            . "Email: info@businessconsultant.lk\n"
            . "Website: https://businessconsultant.lk\n\n"
            . "Best Regards,\n"
            . "IB Business Consultants - Horana, Sri Lanka";

        $customerMail->send();
        // Return JSON response for AJAX form submission
        sendJsonResponse(['status' => 'success']);

    } catch (Exception $e) {
        sendJsonResponse(['status' => 'error', 'message' => $mail->ErrorInfo ?: $e->getMessage()]);
    }
} else {
    // Reject invalid request methods
    sendJsonResponse(['status' => 'error', 'message' => 'Invalid request']);
}
?>