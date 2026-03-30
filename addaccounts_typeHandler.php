<?php
session_start();
require_once '../database/db_connection.php';
$acc_success_msg = 'New type of account successfully created';

if(isset($_POST['add_account_type'])){

   $account_type_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['account_type_number']));
   $account_type_name = stripcslashes(mysqli_real_escape_string($conn, $_POST['account_type_name']));

   $add_account = "INSERT INTO account_type 
   (account_type_number, account_type_name)
   VALUES 
   ('$account_type_number', '$account_type_name')";

   if (mysqli_query($conn, $add_account)) {
     logActivity($conn, $_SESSION['user_id'] ?? null, $_SESSION['admin'] ?? 'admin', 'Add Account Type', 'Added new account type: ' . $account_type_name . ' (ID: ' . $account_type_number . ')', 'admin');
     $_SESSION['acc_success_msg'] = $acc_success_msg;
     header('Location:../addaccount_type.php');
   }else {
      echo 'Something went wrong during adding type of account try againg later';
   }
}
