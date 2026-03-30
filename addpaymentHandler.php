<?php
session_start();
require_once '../database/db_connection.php';

$user_id = $_SESSION['user_id'];

$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
if(mysqli_num_rows($check_table) == 0) {
    $create_table = "CREATE TABLE IF NOT EXISTS `payments` (
      `payment_number` int(10) NOT NULL,
      `user_id` int(10) DEFAULT NULL,
      `account_number` int(10) NOT NULL,
      `payment_amount` decimal(10,2) NOT NULL,
      `payment_date` date NOT NULL,
      `payment_method` varchar(50) NOT NULL,
      `notes` text,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`payment_number`)
    )";
    mysqli_query($conn, $create_table);
}

if(isset($_POST['add_payment'])) {
    $account_number = $_POST['account_number'];
    $payment_amount = floatval($_POST['payment_amount']);
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    // Generate unique payment number with collision check
    do {
        $payment_number = rand(100000, 999999);
        $check_duplicate = mysqli_query($conn, "SELECT payment_number FROM payments WHERE payment_number = '$payment_number'");
    } while (mysqli_num_rows($check_duplicate) > 0);
    
    // Check if payment with same account, amount and date already exists to prevent duplicates
    $check_existing = mysqli_query($conn, "SELECT payment_number FROM payments WHERE account_number = '$account_number' AND payment_amount = '$payment_amount' AND payment_date = '$payment_date' LIMIT 1");
    if (mysqli_num_rows($check_existing) > 0) {
        $_SESSION['error_msg'] = "A payment with these details already exists!";
        header('location: ../addpayment.php');
        exit();
    }
    
    $account = mysqli_fetch_assoc(mysqli_query($conn, "SELECT loan_balance, overdue_interest FROM accounts WHERE account_number = '$account_number'"));
    $loan_balance = floatval($account['loan_balance'] ?? 0);
    $overdue_interest = floatval($account['overdue_interest'] ?? 0);
    
    $total_due = $loan_balance + $overdue_interest;
    $new_balance = $total_due - $payment_amount;
    if ($new_balance < 0) $new_balance = 0;
    
    $sql = "INSERT INTO payments (user_id, payment_number, account_number, payment_amount, payment_date, payment_method, notes) 
            VALUES ('$user_id', '$payment_number', '$account_number', '$payment_amount', '$payment_date', '$payment_method', '$notes')";
    
    if(mysqli_query($conn, $sql)) {
        mysqli_query($conn, "UPDATE accounts SET loan_balance = '$new_balance', overdue_interest = 0 WHERE account_number = '$account_number'");
        
    if ($new_balance <= 0) {
        mysqli_query($conn, "UPDATE accounts SET account_status = -3 WHERE account_number = '$account_number'");
    } else {
        $account_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT loan_amount, interest, loan_term, open_date FROM accounts WHERE account_number = '$account_number'"));
        $loan_amount = floatval($account_info['loan_amount'] ?? 0);
        $interest = floatval($account_info['interest'] ?? 0);
        $loan_term = intval($account_info['loan_term'] ?? 1);
        $open_date = $account_info['open_date'] ?? date('Y-m-d');
        
        $total_due = $loan_amount + $interest;
        $monthly_payment = $loan_term > 0 ? $total_due / $loan_term : $total_due;
        
        $start_date = new DateTime($open_date);
        $current_date = new DateTime();
        $months_passed = (($current_date->format('Y') - $start_date->format('Y')) * 12) + ($current_date->format('n') - $start_date->format('n'));
        
        $next_due_month = $start_date->format('Y-m-') . str_pad($start_date->format('d'), 2, '0', STR_PAD_LEFT);
        $next_due_month = date('Y-m-d', strtotime("+$months_passed months", strtotime($open_date)));
        
        $total_paid_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(payment_amount) as total_paid FROM payments WHERE account_number = '$account_number'"));
        $total_paid = floatval($total_paid_result['total_paid'] ?? 0);
        
        $expected_payment_for_month = $monthly_payment * ($months_passed + 1);
        
        if ($total_paid >= $expected_payment_for_month) {
            mysqli_query($conn, "UPDATE accounts SET account_status = 7 WHERE account_number = '$account_number'");
        } elseif ($total_paid > 0) {
            mysqli_query($conn, "UPDATE accounts SET account_status = 5 WHERE account_number = '$account_number'");
        } else {
            mysqli_query($conn, "UPDATE accounts SET account_status = 6 WHERE account_number = '$account_number'");
        }
    }
        
        $admin_username = $_SESSION['admin'] ?? 'admin';
        $admin_user_id = $_SESSION['user_id'] ?? null;
        logActivity($conn, $admin_user_id, $admin_username, 'Record Payment', 'Recorded payment of ₱' . number_format($payment_amount, 2) . ' for account ID: ' . $account_number, 'admin');
        
        $_SESSION['success_msg'] = "Payment recorded successfully!";
        header('location: ../addpayment.php');
    } else {
        $_SESSION['success_msg'] = "Error recording payment: " . mysqli_error($conn);
        header('location: ../addpayment.php');
    }
} else {
    header('location: ../addpayment.php');
}
