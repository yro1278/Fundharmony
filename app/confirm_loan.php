<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../customer_login.php');
    exit();
}

require_once '../database/db_connection.php';
require_once 'mailer.php';

$account_number = isset($_GET['account_number']) ? mysqli_real_escape_string($conn, $_GET['account_number']) : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($account_number && in_array($action, ['confirm', 'decline'])) {
    $customer_id = $_SESSION['customer_id'];
    $loan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, c.first_name, c.surname, c.email, acs.account_status_name 
        FROM accounts a 
        LEFT JOIN customers c ON a.customer = c.customer_number 
        LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
        WHERE a.account_number = '$account_number' AND a.customer = '$customer_id'"));
    
    if ($loan && $loan['account_status_name'] === 'Approved') {
        if ($action === 'confirm') {
            // Get Active status
            $active_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Active'"));
            $active_id = $active_status['account_status_number'] ?? 1;
            mysqli_query($conn, "UPDATE accounts SET account_status = '$active_id' WHERE account_number = '$account_number'");
            
            // Log activity
            $customer_name = ($loan['first_name'] ?? '') . ' ' . ($loan['surname'] ?? '');
            logActivity($conn, $customer_id, $customer_name, 'Confirm Loan', 'User confirmed loan account ID: ' . $account_number, 'customer');
            
            $_SESSION['loan_msg'] = "Loan confirmed successfully! You can now make payments.";
            $_SESSION['loan_msg_type'] = 'success';
        } elseif ($action === 'decline') {
            // Ensure Declined status exists
            $declined_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Declined'"));
            if (!$declined_status) {
                $max_st = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(account_status_number) as max_num FROM account_status"));
                $new_num = ($max_st['max_num'] ?? 0) + 1;
                mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES ('$new_num', 'Declined')");
                $declined_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Declined'"));
            }
            $declined_id = $declined_status['account_status_number'];
            mysqli_query($conn, "UPDATE accounts SET account_status = '$declined_id' WHERE account_number = '$account_number'");
            
            // Log activity for declined loan
            $customer_name = ($loan['first_name'] ?? '') . ' ' . ($loan['surname'] ?? '');
            $loan_amount = number_format($loan['loan_amount'] ?? 0, 2);
            logActivity($conn, $customer_id, $customer_name, 'Decline Loan', 'User declined loan account ID: ' . $account_number . ' - Amount: ₱' . $loan_amount, 'customer');
            
            $subject = "Loan Declined by Customer - FundHarmony";
            $body = "
            <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width:600px;margin:0 auto;padding:20px;'>
                    <div style='background:#6c757d;color:white;padding:20px;text-align:center;'>
                        <h2>Loan Declined by Customer</h2>
                    </div>
                    <div style='background:#f9f9f9;padding:20px;'>
                        <p>A customer has declined their approved loan.</p>
                        <div style='background:white;padding:15px;border-radius:5px;'>
                            <p><strong>Customer Name:</strong> $customer_name</p>
                            <p><strong>Account Number:</strong> $account_number</p>
                            <p><strong>Loan Amount:</strong> ₱$loan_amount</p>
                            <p><strong>Status:</strong> Declined by Customer</p>
                        </div>
                        <p>Please review this in your loan approvals page.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            sendEmail('tyronealariao05@gmail.com', $subject, $body);
            
            $_SESSION['loan_msg'] = "Loan declined. The admin has been notified.";
            $_SESSION['loan_msg_type'] = 'warning';
        }
    } else {
        $_SESSION['loan_msg'] = "This loan is not in 'Approved' status or access denied.";
        $_SESSION['loan_msg_type'] = 'danger';
    }
}

header('Location: ../customer_my_loans.php');
exit();
