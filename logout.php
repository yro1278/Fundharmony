<?php
session_start();
require_once 'database/db_connection.php';

$admin_username = $_SESSION['admin'] ?? 'admin';
$admin_user_id = $_SESSION['user_id'] ?? null;

$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$username_escaped = mysqli_real_escape_string($conn, $admin_username);
$action = 'Admin Logout';
$description = 'Admin logged out';
$user_type = 'admin';
$current_timestamp = date('Y-m-d H:i:s');

$insert_query = "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
    VALUES ('" . intval($admin_user_id) . "', '$username_escaped', '$user_type', '$action', '$description', '$ip_address', '$current_timestamp')";

mysqli_query($conn, $insert_query);

setcookie('remember_username', '', time() - 3600, '/');
setcookie('remember_password', '', time() - 3600, '/');

session_unset();
session_destroy();

header('Location: login.php');
exit;
