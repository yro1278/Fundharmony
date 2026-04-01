<?php
session_start();
require_once '../database/db_connection.php';
require_once 'mailer.php';

$email = $_POST['uname'];
$password = $_POST['psw'];
$remember = isset($_POST['remember']) ? $_POST['remember'] : '';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$email = stripslashes(mysqli_real_escape_string($conn, $email));
$password = stripslashes(mysqli_real_escape_string($conn, $password));

$lock_check = checkLoginLock($conn, $email, $ip_address);
if ($lock_check['locked']) {
    $_SESSION['error'] = "Too many failed attempts. Account locked for " . $lock_check['minutes'] . " minute(s). Please try again later.";
    $_SESSION['locked_until'] = $lock_check['locked_until'] ?? date('Y-m-d H:i:s', strtotime('+5 minutes'));
    header('location: ../login.php');
    exit();
}

$access = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
$result = mysqli_query($conn, $access);
$row = mysqli_fetch_assoc($result);
$count = mysqli_num_rows($result);

if ($count == 1) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_expiry'] = time() + 300;
    $_SESSION['otp_user_id'] = $row['user_number'];
    $_SESSION['otp_username'] = $row['username'];
    $_SESSION['otp_role'] = $row['role'] ?? 'admin';
    $_SESSION['otp_remember'] = $remember;
    
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
    
    $email_result = sendEmail($email, $subject, $body);
    
    if (!isset($email_result['success']) || !$email_result['success']) {
        $_SESSION['error'] = "Failed to send OTP email. Error: " . ($email_result['message'] ?? 'Unknown error');
        header('location: ../login.php');
        exit();
    }
    
    header('location: ../verify_otp.php');
    exit();
} else {
    $customer_access = "SELECT * FROM customers WHERE email = '$email' AND password = '$password'";
    $customer_result = mysqli_query($conn, $customer_access);
    $customer_row = mysqli_fetch_assoc($customer_result);
    $customer_count = mysqli_num_rows($customer_result);
    
    if ($customer_count == 1) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email'] = $email;
        $_SESSION['otp_expiry'] = time() + 300;
        $_SESSION['otp_customer_id'] = $customer_row['customer_number'];
        $_SESSION['otp_customer_name'] = $customer_row['first_name'] . ' ' . $customer_row['surname'];
        $_SESSION['otp_type'] = 'customer';
        $_SESSION['otp_remember'] = $remember;
        
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
        
        $cust_result = sendEmail($email, $subject, $body);
        
        if (!isset($cust_result['success']) || !$cust_result['success']) {
            $_SESSION['error'] = "Failed to send OTP email. Error: " . ($cust_result['message'] ?? 'Unknown error');
            header('location: ../login.php');
            exit();
        }
        
        header('location: ../verify_otp.php');
        exit();
    } else {
        $attempt_result = recordFailedAttempt($conn, $email, $ip_address);
        
        if ($attempt_result['locked']) {
            logActivity($conn, null, $email, 'Account Locked', 'Account locked after 5 failed login attempts', 'admin');
            $_SESSION['error'] = "Too many failed attempts. Account locked for 5 minutes. Please try again later.";
        } else {
            $remaining = 5 - $attempt_result['attempts'];
            logActivity($conn, null, $email, 'Failed Login', 'Failed login attempt - ' . $attempt_result['attempts'] . ' attempt(s)', 'admin');
            $_SESSION['error'] = "Invalid email or password. $remaining attempt(s) remaining.";
        }
        header('location: ../login.php');
    }
}
