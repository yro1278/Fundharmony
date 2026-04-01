<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../database/db_connection.php';
require_once 'mailer.php';

$customer_number = isset($_GET['customer_number']) ? mysqli_real_escape_string($conn, $_GET['customer_number']) : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$deactivate_reason = isset($_GET['reason']) ? mysqli_real_escape_string($conn, $_GET['reason']) : '';

if ($customer_number && in_array($action, ['activate', 'deactivate'])) {
    // Ensure is_active column exists
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'is_active'");
    if(mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, "ALTER TABLE customers ADD COLUMN is_active TINYINT(1) DEFAULT 1");
    }
    
    // Ensure deactivated_date column exists
    $check_date = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'deactivated_date'");
    if(mysqli_num_rows($check_date) == 0) {
        mysqli_query($conn, "ALTER TABLE customers ADD COLUMN deactivated_date DATE DEFAULT NULL");
    }
    
    $is_active = ($action === 'activate') ? 1 : 0;
    $deactivated_date = ($action === 'deactivate') ? date('Y-m-d') : NULL;
    mysqli_query($conn, "UPDATE customers SET is_active = '$is_active', deactivated_date = '$deactivated_date' WHERE customer_number = '$customer_number'");
    
    // Get customer info for email
    $customer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM customers WHERE customer_number = '$customer_number'"));
    
    if ($action === 'deactivate' && $customer && !empty($customer['email'])) {
        $customer_name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['surname'] ?? ''));
        $reason = !empty($deactivate_reason) ? $deactivate_reason : 'Violation of terms and conditions';
        
        $subject = "Your FundHarmony Account Has Been Deactivated";
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee; }
                .header h2 { color: #dc3545; margin: 0; }
                .content { padding: 20px 0; }
                .reason { background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 15px 0; }
                .contact-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .footer { text-align: center; color: #888; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2><i class='fas fa-user-times'></i> Account Deactivated</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>$customer_name</strong>,</p>
                    <p>We regret to inform you that your FundHarmony account has been <strong>deactivated</strong>.</p>
                    
                    <div class='reason'>
                        <strong>Reason for Deactivation:</strong><br>
                        " . nl2br(htmlspecialchars($reason)) . "
                    </div>
                    
                    <div class='contact-info'>
                        <p><strong>Contact Us:</strong></p>
                        <p>Email: fundharmonycustomerservice@gmail.com</p>
                        <p>Phone: 09777698003</p>
                    </div>
                    
                    <p>If you believe this was a mistake or would like to appeal this decision, please contact our support team within 30 days.</p>
                </div>
                <div class='footer'>
                    <p>FundHarmony - Loan Management System</p>
                    <p>This is an automated notification. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        sendEmail($customer['email'], $subject, $body);
    }
    
    if ($action === 'activate' && $customer && !empty($customer['email'])) {
        $customer_name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['surname'] ?? ''));
        
        $subject = "Your FundHarmony Account Has Been Reactivated";
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee; }
                .header h2 { color: #28a745; margin: 0; }
                .content { padding: 20px 0; }
                .footer { text-align: center; color: #888; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2><i class='fas fa-user-check'></i> Account Reactivated</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>$customer_name</strong>,</p>
                    <p>Good news! Your FundHarmony account has been <strong>reactivated</strong>.</p>
                    <p>You can now log in and access all features of your account.</p>
                </div>
                <div class='footer'>
                    <p>FundHarmony - Loan Management System</p>
                    <p>This is an automated notification. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        sendEmail($customer['email'], $subject, $body);
    }
    
    // Log activity
    $admin_username = $_SESSION['admin'] ?? 'admin';
    $admin_user_id = $_SESSION['user_id'] ?? null;
    logActivity($conn, $admin_user_id, $admin_username, ucfirst($action) . ' Customer', ucfirst($action) . 'd customer ID: ' . $customer_number, 'admin');
    
    if ($action === 'deactivate') {
        $_SESSION['success_msg'] = "Account deactivated successfully. User has been notified via email.";
    } else {
        $_SESSION['success_msg'] = "Account activated successfully. User has been notified via email.";
    }
}

header('Location: ../managecustomer.php');
exit();
