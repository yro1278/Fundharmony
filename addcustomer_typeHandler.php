<?php
session_start();
include_once '../database/db_connection.php';
$success_msg = 'New customer type successfully created';

if (isset($_POST['add_customer_type'])) {

    $customer_type_number = mysqli_real_escape_string($conn, $_POST['customer_type_number']);
    $customer_type_name = mysqli_real_escape_string($conn, $_POST['customer_type_name']);
    $customer_type_description = mysqli_real_escape_string($conn, $_POST['customer_type_description'] ?? '');

    $insert = "INSERT INTO customers_type 
    (customer_type_number, customer_type_name, customer_type_description)
  	 VALUES
    ('$customer_type_number','$customer_type_name', '$customer_type_description')";

    if (mysqli_query($conn, $insert)) {
        // Log activity
        $admin_username = $_SESSION['admin'] ?? 'admin';
        $admin_user_id = $_SESSION['user_id'] ?? null;
        logActivity($conn, $admin_user_id, $admin_username, 'Add Customer Type', 'Added new customer type: ' . $customer_type_name . ' (ID: ' . $customer_type_number . ')', 'admin');
        
        header('Location: ../addcustomer_type.php');
        $_SESSION['success_msg'] = $success_msg;
    } else {
        echo "Error: " . $insert . " " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
