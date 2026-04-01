<?php
/**
 * Simple PHP Mailer Test Script
 * This script tests if PHPMailer is working correctly with Gmail SMTP
 */

// Include PHPMailer classes
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';
require_once 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Configuration - UPDATE THESE VALUES
$smtp_username = 'tyronealariao06@gmail.com';  // Your Gmail address
$smtp_password = 'lhgo dtdd mgrg frau';         // Your Gmail App Password (16 characters)
$recipient_email = 'tyronealariao06@gmail.com'; // Email to send test to

$error = '';
$success = false;

if (isset($_POST['send_test'])) {
    $recipient_email = $_POST['recipient_email'] ?? $smtp_username;
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;      // Enable verbose debug output
        $mail->isSMTP();                              // Set mailer to use SMTP
        $mail->Host       = 'smtp.gmail.com';        // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                    // Enable SMTP authentication
        $mail->Username   = $smtp_username;           // SMTP username
        $mail->Password   = $smtp_password;           // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption, `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                     // TCP port to connect to
        $mail->CharSet    = 'UTF-8';
        
        // Recipients
        $mail->setFrom($smtp_username, 'FundHarmony Test');
        $mail->addAddress($recipient_email);         // Add a recipient
        
        // Content
        $mail->isHTML(true);                         // Set email format to HTML
        $mail->Subject = 'FundHarmony - OTP Test Email';
        $mail->Body    = '
        <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
            <h2 style="color: #667eea; text-align: center;">FundHarmony</h2>
            <p style="font-size: 16px; color: #333;">Hello!</p>
            <p style="font-size: 14px; color: #555;">This is a test email from FundHarmony.</p>
            <p style="font-size: 14px; color: #555;">If you received this email, your email settings are working correctly!</p>
            <p style="font-size: 12px; color: #888; margin-top: 20px;">OTP Code: <strong>123456</strong></p>
        </div>
        ';
        $mail->AltBody = 'This is a test email from FundHarmony. If you received this, your email settings are working correctly!';
        
        $mail->send();
        $success = true;
    } catch (Exception $e) {
        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Mailer Test - FundHarmony</title>
    <link rel="stylesheet" href="assets/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .mailer-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        h2 {
            color: #667eea;
            text-align: center;
            margin-bottom: 25px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .btn-send:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="mailer-box">
        <h2>📧 Simple PHP Mailer Test</h2>
        
        <?php if ($success): ?>
            <div class="alert-success">
                <strong>✓ Success!</strong> Email sent successfully!<br>
                Check your inbox (and spam folder) at: <?php echo htmlspecialchars($recipient_email); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-danger">
                <strong>✗ Error!</strong><br>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>SMTP Username (Gmail):</label>
                <input type="text" value="<?php echo $smtp_username; ?>" disabled>
            </div>
            
            <div class="form-group">
                <label>Recipient Email:</label>
                <input type="email" name="recipient_email" value="<?php echo htmlspecialchars($recipient_email); ?>" required>
            </div>
            
            <button type="submit" name="send_test" class="btn-send">
                📤 Send Test Email
            </button>
        </form>
        
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; font-size: 13px;">
            <strong>⚠️ Important:</strong><br>
            1. Enable 2FA on your Gmail: <a href="https://myaccount.google.com/" target="_blank">Google Account</a><br>
            2. Generate App Password: <a href="https://myaccount.google.com/apppasswords" target="_blank">App Passwords</a><br>
            3. Update password in code above if needed
        </div>
        
        <br>
        <a href="login.php" style="display:block; text-align:center; color:#666;">← Back to Login</a>
    </div>
</body>
</html>
