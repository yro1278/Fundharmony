<?php 
session_start();
require_once '../database/db_connection.php';
$user_id = $_SESSION['user_id'];
 
$deletecustomer = "DELETE FROM customers WHERE customer_number = '".$_GET['customer_number']."' AND user_id = '$user_id'";
if (mysqli_query($conn, $deletecustomer)) {
  $admin_username = $_SESSION['admin'] ?? 'admin';
  $admin_user_id = $_SESSION['user_id'] ?? null;
  logActivity($conn, $admin_user_id, $admin_username, 'Delete Customer', 'Deleted customer ID: ' . $_GET['customer_number'], 'admin');
  
  header('Location: ../managecustomer.php');

}else {
  echo 'Something went wrong when deleting please try again';
  mysqli_close($conn);
}
