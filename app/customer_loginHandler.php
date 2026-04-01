<?php
session_start();
require_once '../database/db_connection.php';
require_once 'mailer.php';

$email = $_POST['email'];
$password = $_POST['psw'];
$remember = isset($_POST['remember']) ? $_POST['remember'] : '';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$email = stripslashes(mysqli_real_escape_string($conn, $email));
$password = stripslashes(mysqli_real_escape_string($conn, $password));

$lock_check = checkLoginLock($conn, $email, $ip_address);
if ($lock_check['locked']) {
    $_SESSION['error'] = "Too many failed attempts. Account locked for " . $lock_check['minutes'] . " minute(s). Please try again later.";
    $_SESSION['locked_until'] = $lock_check['locked_until'] ?? date('Y-m-d H:i:s', strtotime('+5 minutes'));
    header('location: ../customer_login.php');
    exit();
}

$access = "SELECT * FROM customers WHERE email = '$email' AND password = '$password'";
$result = mysqli_query($conn, $access);
$row = mysqli_fetch_assoc($result);
$count = mysqli_num_rows($result);

if ($count == 1) {
    $is_active = isset($row['is_active']) ? $row['is_active'] : 1;
    if (!$is_active) {
        $deactivated_date = isset($row['deactivated_date']) ? $row['deactivated_date'] : null;
        $days_deactivated = 0;
        if($deactivated_date) {
            $days_deactivated = floor((strtotime(date('Y-m-d')) - strtotime($deactivated_date)) / (60 * 60 * 24));
        }
        $_SESSION['error'] = "Your account has been deactivated for $days_deactivated day(s). Please contact the admin within 30 days: fundharmonycustomerservice@gmail.com / 09777698003";
        header('location: ../customer_login.php');
        exit();
    }
    
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_expiry'] = time() + 300;
    $_SESSION['otp_customer_id'] = $row['customer_number'];
    $_SESSION['otp_customer_name'] = $row['first_name'] . ' ' . $row['surname'];
    $_SESSION['otp_type'] = 'customer';
    
    $subject = 'FundHarmony - Your OTP Code';
    $body = "
        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #667eea; text-align: center;'>FundHarmony</h2>
            <p style='font-size: 16px; color: #333;'>Hello!</p>
            <p style='font-size: 14px; color: #555;'>Your One-Time Password (OTP) for login is:</p>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 32px; font-weight: bold; text-align: center; padding: 15px; border-radius: 10px; letter-spacing: 5px; margin: 20px 0;'>$otp</div>
            <p style='font-size: 12px; color: #888;'>This OTP will expire in 5 minutes.</p>
            <p style='font-size: 12px; color: #888;'>If you didn't request this, please ignore this email.</p>
        </div>
    ";
    
    $result = sendEmail($email, $subject, $body);
    
    if (!isset($result['success']) || !$result['success']) {
        $_SESSION['error'] = "Failed to send OTP email. Error: " . ($result['message'] ?? 'Unknown error');
        header('location: ../customer_login.php');
        exit();
    }
    
    header('location: ../verify_otp.php');
    exit();
} else {
    $attempt_result = recordFailedAttempt($conn, $email, $ip_address);
    
    if ($attempt_result['locked']) {
        logActivity($conn, null, $email, 'Account Locked', 'Account locked after 5 failed login attempts', 'customer');
        $_SESSION['error'] = "Too many failed attempts. Account locked for 5 minutes. Please try again later.";
        $_SESSION['locked_until'] = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    } else {
        $remaining = 5 - $attempt_result['attempts'];
        logActivity($conn, null, $email, 'Failed Login', 'Failed login attempt - ' . $attempt_result['attempts'] . ' attempt(s)', 'customer');
        $_SESSION['error'] = "Invalid email or password. $remaining attempt(s) remaining.";
    }
    header('location: ../customer_login.php');
}
