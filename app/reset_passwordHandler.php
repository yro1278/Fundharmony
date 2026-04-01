<?php
session_start();
require_once '../database/db_connection.php';

function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least 1 uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least 1 lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least 1 number";
    }
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors[] = "Password must contain at least 1 special character (!@#$%^&*)";
    }
    return $errors;
}

if (isset($_POST['reset_password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $token = mysqli_real_escape_string($conn, $_POST['token']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $_SESSION['forgot_error'] = "Passwords do not match.";
        header('location: ../forgot_password.php?page=reset&email=' . urlencode($email) . '&token=' . $token);
        exit();
    }
    
    $password_errors = validatePassword($password);
    if (!empty($password_errors)) {
        $_SESSION['forgot_error'] = implode(". ", $password_errors) . ".";
        header('location: ../forgot_password.php?page=reset&email=' . urlencode($email) . '&token=' . $token);
        exit();
    }
    
    $now = date('Y-m-d H:i:s');
    $check_query = "SELECT * FROM password_reset_tokens WHERE email = '$email' AND token = '$token' AND expires_at > '$now'";
    $check_token = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_token) == 0) {
        $_SESSION['forgot_error'] = "Invalid or expired reset link.";
        header('location: ../forgot_password.php');
        exit();
    }
    
    $password = mysqli_real_escape_string($conn, $password);
    
    $update_admin = mysqli_query($conn, "UPDATE users SET password = '$password' WHERE email = '$email'");
    $update_customer = mysqli_query($conn, "UPDATE customers SET password = '$password' WHERE email = '$email'");
    
    mysqli_query($conn, "DELETE FROM password_reset_tokens WHERE email = '$email'");
    
    // Determine which type of user reset their password
    $admin_check = mysqli_query($conn, "SELECT user_number, username FROM users WHERE email = '$email'");
    if (mysqli_num_rows($admin_check) > 0) {
        $admin = mysqli_fetch_assoc($admin_check);
        logActivity($conn, $admin['user_number'], $admin['username'], 'Password Reset', 'Admin password reset successfully', 'admin');
    } else {
        $customer_check = mysqli_query($conn, "SELECT customer_number, first_name, surname FROM customers WHERE email = '$email'");
        if (mysqli_num_rows($customer_check) > 0) {
            $customer = mysqli_fetch_assoc($customer_check);
            $customer_name = $customer['first_name'] . ' ' . $customer['surname'];
            logActivity($conn, $customer['customer_number'], $customer_name, 'Password Reset', 'User password reset successfully', 'customer');
        }
    }
    
    $_SESSION['forgot_success'] = "Password has been reset successfully! You can now login with your new password.";
    header('location: ../login.php');
    exit();
} else {
    header('location: ../forgot_password.php');
    exit();
}
