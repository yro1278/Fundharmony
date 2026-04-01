<?php
session_start();
require_once '../database/db_connection.php';

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['otp_email'];
$stored_otp = $_SESSION['otp'];
$otp_expiry = $_SESSION['otp_expiry'];

if (time() > $otp_expiry) {
    session_unset();
    $_SESSION['error'] = "OTP has expired. Please login again.";
    header('Location: ../login.php');
    exit();
}

$entered_otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];

if ($entered_otp != $stored_otp) {
    $_SESSION['error'] = "Invalid OTP. Please try again.";
    header('Location: ../verify_otp.php');
    exit();
}

if (isset($_SESSION['otp_user_id'])) {
    $_SESSION['admin'] = $_SESSION['otp_username'];
    $_SESSION['admin_name'] = $_SESSION['otp_username'];
    $_SESSION['user_id'] = $_SESSION['otp_user_id'];
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $_SESSION['otp_role'];
    
    clearLoginAttempts($conn, $email);
    
    // Direct insert for admin login activity
    $admin_user_id = intval($_SESSION['otp_user_id']);
    $admin_username = mysqli_real_escape_string($conn, $_SESSION['otp_username']);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $current_timestamp = date('Y-m-d H:i:s');
    mysqli_query($conn, "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
        VALUES ('$admin_user_id', '$admin_username', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '$ip_address', '$current_timestamp')");
    
    $remember = $_SESSION['otp_remember'] ?? '';
    if($remember == 'on') {
        $access = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $access);
        $row = mysqli_fetch_assoc($result);
        $password = $row['password'];
        setcookie('remember_username', $email, time() + (86400 * 30), "/");
        setcookie('remember_password', $password, time() + (86400 * 30), "/");
    }
    
    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry'], $_SESSION['otp_user_id'], $_SESSION['otp_username'], $_SESSION['otp_role'], $_SESSION['otp_remember']);
    $_SESSION['fresh_login'] = true;
    
    if ($_SESSION['role'] == 'customer') {
        header('location: ../customer_dashboard.php');
    } else {
        header('location: ../welcome.php');
    }
} elseif (isset($_SESSION['otp_customer_id'])) {
    $_SESSION['customer_id'] = $_SESSION['otp_customer_id'];
    $_SESSION['customer_name'] = $_SESSION['otp_customer_name'];
    $_SESSION['customer_email'] = $email;
    
    $check_last = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'last_login'");
    if(mysqli_num_rows($check_last) == 0) {
        mysqli_query($conn, "ALTER TABLE customers ADD COLUMN last_login DATETIME DEFAULT NULL");
    }
    mysqli_query($conn, "UPDATE customers SET last_login = NOW() WHERE customer_number = '".$_SESSION['otp_customer_id']."'");
    
    // Direct insert for customer login activity
    $customer_id = intval($_SESSION['otp_customer_id']);
    $customer_name = mysqli_real_escape_string($conn, $_SESSION['otp_customer_name']);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $current_timestamp = date('Y-m-d H:i:s');
    mysqli_query($conn, "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
        VALUES ('$customer_id', '$customer_name', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '$ip_address', '$current_timestamp')");
    
    $remember = $_SESSION['otp_remember'] ?? '';
    if($remember == 'on') {
        $access = "SELECT * FROM customers WHERE email = '$email'";
        $result = mysqli_query($conn, $access);
        $row = mysqli_fetch_assoc($result);
        $password = $row['password'];
        setcookie('remember_username', $email, time() + (86400 * 30), "/");
        setcookie('remember_password', $password, time() + (86400 * 30), "/");
    }
    
    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry'], $_SESSION['otp_customer_id'], $_SESSION['otp_customer_name'], $_SESSION['otp_type'], $_SESSION['otp_remember']);
    $_SESSION['fresh_login'] = true;
    
    header('location: ../customer_dashboard.php');
} else {
    session_unset();
    $_SESSION['error'] = "Session expired. Please login again.";
    header('Location: ../login.php');
}
