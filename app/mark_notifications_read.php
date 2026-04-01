<?php
session_start();
require_once '../database/db_connection.php';

if (isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);
    
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'loan_notifications'");
    if (mysqli_num_rows($table_check) > 0) {
        mysqli_query($conn, "UPDATE loan_notifications SET is_read = 1 WHERE customer_id = '$customer_id'");
    }
    
    header('Location: ../customer_dashboard.php');
    exit();
} else {
    header('Location: ../customer_dashboard.php');
    exit();
}
?>
