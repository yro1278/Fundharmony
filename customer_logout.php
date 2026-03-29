<?php
session_start();
require_once 'database/db_connection.php';

// Get customer info before destroying session
$customer_name = $_SESSION['customer_name'] ?? 'customer';
$customer_id = $_SESSION['customer_id'] ?? null;

// Log the logout activity FIRST before destroying session
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$username_escaped = mysqli_real_escape_string($conn, $customer_name);
$action = 'User Logout';
$description = 'User logged out';
$user_type = 'customer';
$current_timestamp = date('Y-m-d H:i:s');

// Direct insert - bypass function check
$insert_query = "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
    VALUES ('" . intval($customer_id) . "', '$username_escaped', '$user_type', '$action', '$description', '$ip_address', '$current_timestamp')";

$result = mysqli_query($conn, $insert_query);
if (!$result) {
    error_log("Failed to insert customer logout log: " . mysqli_error($conn));
}

setcookie('remember_username', '', time() - 3600, '/');
setcookie('remember_password', '', time() - 3600, '/');

session_unset();
session_destroy();
header('Location: customer_login.php');
exit();
