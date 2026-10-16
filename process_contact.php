<?php
// Include Database Connection
include 'db.php'; 

// Include PHPMailer Classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and capture POST form data
    $name = $conn->real_escape_string($_POST['conName']);
    $email = $conn->real_escape_string($_POST['conEmail']);
    $mobile = $conn->real_escape_string($_POST['conPhone']);
    $whatsapp = isset($_POST['conWhatsapp']) ? $conn->real_escape_string($_POST['conWhatsapp']) : '';
    $service = $conn->real_escape_string($_POST['conSubject']);
    $message = $conn->real_escape_string($_POST['conMessage']);

    // 1. Save Inquiry into Database (for Admin Dashboard)
    $sql = "INSERT INTO contact_messages (name, email, mobile, whatsapp, service, message) 
            VALUES ('$name', '$email', '$mobile', '$whatsapp', '$service', '$message')";
    $conn->query($sql);

    // 2. Email Dispatch Process (PHPMailer)
    $mail = new PHPMailer(true);

    try {
        // Set to 0 for production / AJAX to prevent corrupting JSON response (set to 2 for console debugging)
        $mail->SMTPDebug = 0; 

        // SMTP Server Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        
        // Gmail SMTP Credentials (Username & App Password)
        $mail->Username   = 'applicationweb6@gmail.com'; 
        $mail->Password   = 'kxhqcoepkeutjuim'; 
        
        // Port 587 with TLS Encryption
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Send Notification Email to Admin
        $mail->setFrom('applicationweb6@gmail.com', 'IB Consultants Website');
        $mail->addAddress('applicationweb6@gmail.com');
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
        
        $mail->setFrom('applicationweb6@gmail.com', 'IB Consultants');
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
        // Return JSON error response if email dispatch fails
        echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
        exit();
    }
} else {
    // Reject invalid request methods
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}
?>