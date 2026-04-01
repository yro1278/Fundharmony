<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../database/db_connection.php';

$account_number = isset($_GET['account_number']) ? $_GET['account_number'] : '';
$user_id = $_SESSION['user_id'];

if ($account_number) {
    $delete = "DELETE FROM accounts WHERE account_number = '$account_number' AND user_id = '$user_id'";
    
    if (mysqli_query($conn, $delete)) {
        $admin_username = $_SESSION['admin'] ?? 'admin';
        $admin_user_id = $_SESSION['user_id'] ?? null;
        logActivity($conn, $admin_user_id, $admin_username, 'Delete Account', 'Deleted account ID: ' . $account_number, 'admin');
        
        $_SESSION['success_msg'] = "Account deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting account: " . mysqli_error($conn);
    }
}

header('Location: ../manageaccount.php');
exit();
