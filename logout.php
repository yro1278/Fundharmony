<?php
session_start();
require_once 'database/db_connection.php';

// Get admin info before destroying session
$admin_username = $_SESSION['admin'] ?? 'admin';
$admin_user_id = $_SESSION['user_id'] ?? null;

// Log the logout activity FIRST before destroying session
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$username_escaped = mysqli_real_escape_string($conn, $admin_username);
$action = 'Admin Logout';
$description = 'Admin logged out';
$user_type = 'admin';
$current_timestamp = date('Y-m-d H:i:s');

// Direct insert - bypass function check
$insert_query = "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
    VALUES ('" . intval($admin_user_id) . "', '$username_escaped', '$user_type', '$action', '$description', '$ip_address', '$current_timestamp')";

$result = mysqli_query($conn, $insert_query);
if (!$result) {
    error_log("Failed to insert logout log: " . mysqli_error($conn));
}

setcookie('remember_username', '', time() - 3600, '/');
setcookie('remember_password', '', time() - 3600, '/');

session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        // Clear localStorage on logout
        localStorage.setItem('openMenus', '[]');
        localStorage.setItem('sidebarState', 'expanded');
        localStorage.setItem('theme', 'light');
    </script>
    <meta http-equiv="refresh" content="0;url=login.php">
</head>
<body>
    <p>Logging out...</p>
    <script>window.location.href = 'login.php';</script>
</body>
</html>
