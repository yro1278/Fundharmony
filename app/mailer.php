<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';

// Gmail SMTP Configuration
// IMPORTANT: To use Gmail SMTP, you need to:
// 1. Enable 2-Factor Authentication on your Gmail account
// 2. Generate an App Password: https://myaccount.google.com/apppasswords
// 3. Use the 16-character App Password (without spaces)

define('SMTP_GMAIL', 'tyronealariao06@gmail.com');
define('SMTP_APP_PASSWORD', 'fgpbywvrhuhtoqop');

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    
    try {
        // Enable verbose debug output (set to SMTP::DEBUG_SERVER for debugging)
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_GMAIL;
        $mail->Password   = SMTP_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Retry settings
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom(SMTP_GMAIL, 'FundHarmony');
        $mail->addAddress($to);
        $mail->addReplyTo(SMTP_GMAIL, 'FundHarmony Support');
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        
        // Set timeout settings
        $mail->Timeout = 30;
        
        $mail->send();
        return array('success' => true, 'message' => 'Email sent successfully');
    } catch (Exception $e) {
        $error_msg = $mail->ErrorInfo;
        error_log("Email Error: " . $error_msg);
        
        // More detailed error logging
        $log_error = date('Y-m-d H:i:s') . " - Email to: $to | Subject: $subject | Error: $error_msg\n";
        file_put_contents(__DIR__ . '/email_error_log.txt', $log_error, FILE_APPEND);
        
        return array('success' => false, 'message' => $error_msg);
    }
}

// Function to test email configuration
function testEmailConnection() {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_GMAIL;
        $mail->Password   = SMTP_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom(SMTP_GMAIL, 'FundHarmony Test');
        $mail->addAddress(SMTP_GMAIL); // Send test to own email
        $mail->Subject = 'FundHarmony Email Test';
        $mail->Body    = 'This is a test email from FundHarmony. If you received this, your email settings are working correctly!';
        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
