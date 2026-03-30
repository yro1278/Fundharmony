<?php
session_start();
require_once '../database/db_connection.php';
$success_msg = 'Account status created successfully ';

if (isset($_POST['add_account_status'])) {

    $account_status_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['account_status_number']));
    $account_status_name = stripcslashes(mysqli_real_escape_string($conn, $_POST['account_status_name']));

    $add_account = "INSERT INTO account_status
   (account_status_number, account_status_name)
   VALUES
   ('$account_status_number', '$account_status_name')";

    if (mysqli_query($conn, $add_account)) {
        logActivity($conn, $_SESSION['user_id'] ?? null, $_SESSION['admin'] ?? 'admin', 'Add Account Status', 'Added new account status: ' . $account_status_name . ' (ID: ' . $account_status_number . ')', 'admin');
        $_SESSION['success_msg'] = $success_msg;
        header('Location:../addaccount_status.php');
    } else {
        echo 'Something went wrong during adding type of account try againg later';
    }
}
