<?php
session_start();
require_once '../database/db_connection.php';
$success_del = 'One account status hass been successful deleted';

$delete_status = "DELETE FROM account_status WHERE account_status_number = '".$_GET['account_status_number']."'";

if (mysqli_query($conn, $delete_status)) {
   logActivity($conn, $_SESSION['user_id'] ?? null, $_SESSION['admin'] ?? 'admin', 'Delete Account Status', 'Deleted account status ID: ' . $_GET['account_status_number'], 'admin');
   $_SESSION['success_del'] = $success_del;
   header('Location: ../manageaccount_status.php');
}else {
   echo 'Something went wrong can\'t Delete ';
   mysqli_close($conn);
}