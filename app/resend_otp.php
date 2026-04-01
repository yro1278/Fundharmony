<?php
session_start();
require_once '../database/db_connection.php';
require_once 'mailer.php';

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_SESSION['otp_resend_limit']) && time() - $_SESSION['otp_resend_limit'] < 60) {
    $_SESSION['error'] = "Please wait 60 seconds before requesting another OTP.";
    header('Location: ../verify_otp.php');
    exit();
}

$email = $_SESSION['otp_email'];
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = time() + 300;
$_SESSION['otp_resend_limit'] = time();

$subject = 'FundHarmony - Your New OTP Code';
$body = "
    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
        <h2 style='color: #667eea; text-align: center;'>FundHarmony</h2>
        <p style='font-size: 16px; color: #333;'>Hello!</p>
        <p style='font-size: 14px; color: #555;'>Your new One-Time Password (OTP) for login is:</p>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 32px; font-weight: bold; text-align: center; padding: 15px; border-radius: 10px; letter-spacing: 5px; margin: 20px 0;'>$otp</div>
        <p style='font-size: 12px; color: #888;'>This OTP will expire in 5 minutes.</p>
        <p style='font-size: 12px; color: #888;'>If you didn't request this, please ignore this email.</p>
    </div>
";

$result = sendEmail($email, $subject, $body);

if ($result['success']) {
    $_SESSION['success'] = "New OTP has been sent to your email.";
} else {
    $_SESSION['error'] = "Failed to send OTP email: " . $result['message'];
}
header('Location: ../verify_otp.php');
