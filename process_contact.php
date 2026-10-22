<?php
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
    $name = strip_tags(trim($_POST['conName']));
    $email = filter_var(trim($_POST['conEmail']), FILTER_SANITIZE_EMAIL);
    $mobile = strip_tags(trim($_POST['conPhone']));
    $whatsapp = isset($_POST['conWhatsapp']) ? strip_tags(trim($_POST['conWhatsapp'])) : '';
    $service = strip_tags(trim($_POST['conSubject']));
    $message = strip_tags(trim($_POST['conMessage']));

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address format.']);
        exit();
    }


    // SQL Injection protection
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, mobile, whatsapp, service, message) VALUES (?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        // "ssssss" is a string of 6 characters
        $stmt->bind_param("ssssss", $name, $email, $mobile, $whatsapp, $service, $message);
        $stmt->execute();
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred.']);
        exit();
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

        // Gmail SMTP Credentials from .env
        $mail->Username = $env['SMTP_USER'];
        $mail->Password = $env['SMTP_PASS'];

        // Port 587 with TLS Encryption
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) $env['SMTP_PORT'];

        // Send Notification Email to Admin
        $mail->setFrom($env['SMTP_FROM_EMAIL'], $env['SMTP_FROM_NAME']);
        $mail->addAddress($env['SMTP_USER']);
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

        // Send Automated "Thank You" Confirmation Email to Customer (With Logo)
        $mail->clearAllRecipients();
        $mail->clearReplyTos();

        $mail->setFrom($env['SMTP_FROM_EMAIL'], 'IB Consultants');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Thank you for contacting IB Consultants";

        $customer_body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: "Plus Jakarta Sans", Arial, sans-serif; background-color: #f4f6fa; margin: 0; padding: 0; color: #1e293b; }
                .email-container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(9, 26, 50, 0.08); border-top: 4px solid #d4af37; }
                .email-header { background: #091a32; padding: 25px 20px; text-align: center; color: #ffffff; }
                .email-logo { max-width: 90px; height: auto; margin-bottom: 12px; display: block; margin-left: auto; margin-right: auto; }
                .email-header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 1px; color: #ffffff; }
                .email-header p { margin: 4px 0 0 0; font-size: 12px; color: #d4af37; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; }
                .email-body { padding: 35px 30px; line-height: 1.6; font-size: 15px; color: #334155; }
                .highlight-box { background: #f8fafc; border-left: 4px solid #091a32; padding: 15px 20px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #091a32; }
                .contact-info { margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #64748b; }
                .contact-info a { color: #091a32; text-decoration: none; font-weight: 600; }
                .email-footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <!-- Company Official Logo -->
                    <img src="https://ibconsultants.ct.ws/assets/images/logos/logo-2.webp" alt="IB Consultants Logo" class="email-logo">
                    <h1>IB CONSULTANTS</h1>
                    <p>Business & Profit Growth Advisory</p>
                </div>
                <div class="email-body">
                    <p>Dear <strong>' . htmlspecialchars($name) . '</strong>,</p>
                    
                    <p>Thank you for reaching out to <strong>IB Consultants</strong>. We have successfully received your inquiry regarding <strong>"' . htmlspecialchars($service) . '"</strong>.</p>
                    
                    <div class="highlight-box">
                        <strong>What happens next?</strong><br>
                        Our senior consulting team will review your requirement and get back to you within <strong>24 hours</strong> to discuss actionable strategies for your business.
                    </div>
                    
                    <p>If you have any urgent operational queries in the meantime, feel free to contact us directly:</p>
                    
                    <div class="contact-info">
                        📞 Hotline / Call: <a href="tel:+94711255610">+94 71 125 5610</a><br>
                        💬 WhatsApp: <a href="https://wa.me/94742112767" target="_blank">+94 74 211 2767</a><br>
                        🌐 Website: <a href="https://ibconsultants.ct.ws" target="_blank">www.ibconsultants.lk</a>
                    </div>
                </div>
                <div class="email-footer">
                    &copy; ' . date('Y') . ' IB Business Consultants. Horana, Sri Lanka.<br>This is an automated confirmation email. Please do not reply directly to this message.
                </div>
            </div>
        </body>
        </html>';

        $mail->Body = $customer_body;
        $mail->send();

        // Return JSON response for AJAX form submission
        echo json_encode(['status' => 'success']);
        exit();

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
        exit();
    }
} else {
    // Reject invalid request methods
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}
?>