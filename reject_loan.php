<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../database/db_connection.php';
require_once 'mailer.php';

$account_number = isset($_POST['account_number']) ? $_POST['account_number'] : '';
$reject_notes = isset($_POST['reject_notes']) ? $_POST['reject_notes'] : '';

if ($account_number) {
    $rejected_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Rejected'"));
    $new_status = $rejected_status['account_status_number'] ?? 3;
    
    mysqli_query($conn, "UPDATE accounts SET account_status = '$new_status', reject_notes = '$reject_notes' WHERE account_number = '$account_number'");
    mysqli_query($conn, "UPDATE loan_requirements SET status = 'rejected' WHERE account_number = '$account_number'");
    
    $account = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, c.email, c.first_name, c.surname, act.account_type_name 
        FROM accounts a 
        LEFT JOIN customers c ON a.customer = c.customer_number 
        LEFT JOIN account_type act ON a.account_type = act.account_type_number 
        WHERE a.account_number = '$account_number'"));
    
    if ($account && !empty($account['email'])) {
        $customer_name = trim(($account['first_name'] ?? '') . ' ' . ($account['surname'] ?? ''));
        $loan_type = $account['account_type_name'] ?? 'Loan';
        $loan_amount = $account['loan_amount'] ?? 0;
        $reason = !empty($reject_notes) ? $reject_notes : 'No specific reason provided.';
        
        $subject = "Your Loan Application Has Been Rejected - FundHarmony";
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee; }
                .header h2 { color: #dc3545; margin: 0; }
                .content { padding: 20px 0; }
                .loan-details { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .loan-details p { margin: 5px 0; }
                .reason { background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; }
                .reason h4 { margin: 0 0 10px 0; color: #856404; }
                .footer { text-align: center; color: #888; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2><i class='fas fa-times-circle'></i> Loan Rejected</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>$customer_name</strong>,</p>
                    <p>We regret to inform you that your loan application has been rejected.</p>
                    
                    <div class='loan-details'>
                        <p><strong>Loan Type:</strong> $loan_type</p>
                        <p><strong>Amount:</strong> ₱" . number_format($loan_amount, 2) . "</p>
                        <p><strong>Account Number:</strong> $account_number</p>
                    </div>
                    
                    <div class='reason'>
                        <h4>Reason for Rejection:</h4>
                        <p>" . nl2br(htmlspecialchars($reason)) . "</p>
                    </div>
                    
                    <p>If you have any questions or would like to apply again, please contact our support team.</p>
                </div>
                <div class='footer'>
                    <p>FundHarmony - Loan Management System</p>
                    <p>This is an automated notification. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        sendEmail($account['email'], $subject, $body);
    }
    
    $admin_username = $_SESSION['admin'] ?? 'admin';
    $admin_user_id = $_SESSION['user_id'] ?? null;
    logActivity($conn, $admin_user_id, $admin_username, 'Reject Loan', 'Rejected loan account ID: ' . $account_number . ' - Reason: ' . $reject_notes, 'admin');
    
    $_SESSION['success_msg'] = "Loan rejected!";
}

header('Location: ../loan_approvals.php');
exit();
