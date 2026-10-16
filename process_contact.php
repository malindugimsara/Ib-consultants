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

        // Send Automated "Thank You" Confirmation Email to Customer
        $mail->clearAllRecipients();
        $mail->clearReplyTos();

        $mail->setFrom($env['SMTP_FROM_EMAIL'], 'IB Consultants');
        $mail->addAddress($email);

        $mail->Subject = "Thank you for contacting IB Consultants";
        $customer_body = "Dear $name,\n\n";
        $customer_body .= "Thank you for reaching out to IB Consultants. We have successfully received your inquiry regarding '$service'.\n\n";
        $customer_body .= "Our senior consulting team will review your requirement and get back to you within 24 hours.\n\n";
        $customer_body .= "Best Regards,\nIB Consultants\n";

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