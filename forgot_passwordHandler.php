<?php
session_start();
require_once '../database/db_connection.php';
require_once 'mailer.php';

function generateOTP($length = 6) {
    return str_pad(strval(rand(0, pow(10, $length) - 1)), $length, '0', STR_PAD_LEFT);
}

function generateToken($length = 32) {
    $bytes = openssl_random_pseudo_bytes($length);
    return bin2hex($bytes);
}

if (isset($_POST['request_reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $check_admin = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $check_customer = mysqli_query($conn, "SELECT * FROM customers WHERE email = '$email'");
    
    $user_type = '';
    $user_row = null;
    
    if (mysqli_num_rows($check_admin) > 0) {
        $user_type = 'admin';
        $user_row = mysqli_fetch_assoc($check_admin);
    } elseif (mysqli_num_rows($check_customer) > 0) {
        $user_type = 'customer';
        $user_row = mysqli_fetch_assoc($check_customer);
    } else {
        $_SESSION['forgot_error'] = "Email not found in our records.";
        header('location: ../forgot_password.php');
        exit();
    }
    
    $otp = generateOTP();
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    mysqli_query($conn, "DELETE FROM password_reset_tokens WHERE email = '$email'");
    
    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, user_type, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $otp, $user_type, $expires_at);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['otp_email'] = $email;
    
    $subject = "Your Password Reset OTP - FundHarmony";
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 20px; }
            .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .otp-code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 5px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 20px 0; }
            .footer { text-align: center; color: #888; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2 style='color: #333;'>Password Reset OTP</h2>
            <p>You requested to reset your password. Use the OTP below to verify your identity:</p>
            <div class='otp-code'>$otp</div>
            <p><strong>This OTP will expire in 10 minutes.</strong></p>
            <p>If you did not request a password reset, please ignore this email.</p>
            <div class='footer'>
                <p>FundHarmony - Loan Management System</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($email, $subject, $body);
    
    $_SESSION['forgot_success'] = "OTP has been sent to your email address. Please enter the OTP to reset your password.";
    
    header('location: ../forgot_password.php?page=otp');
    exit();
}

if (isset($_POST['verify_otp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $otp = mysqli_real_escape_string($conn, $_POST['otp']);
    
    $now = date('Y-m-d H:i:s');
    $check = mysqli_query($conn, "SELECT * FROM password_reset_tokens WHERE email = '$email' AND token = '$otp' AND expires_at > '$now'");
    
    if (mysqli_num_rows($check) == 0) {
        $_SESSION['forgot_error'] = "Invalid or expired OTP. Please try again.";
        header('location: ../forgot_password.php?page=otp&email=' . urlencode($email));
        exit();
    }
    
    $token = generateToken(32);
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    mysqli_query($conn, "DELETE FROM password_reset_tokens WHERE email = '$email'");
    
    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, user_type, expires_at) VALUES (?, ?, 'reset', ?)");
    $stmt->bind_param("sss", $email, $token, $expires_at);
    $stmt->execute();
    $stmt->close();
    
    header('location: ../forgot_password.php?page=reset&email=' . urlencode($email) . '&token=' . $token);
    exit();
}
