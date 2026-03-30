<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../database/db_connection.php';
require_once 'mailer.php';

$account_number = isset($_GET['account_number']) ? $_GET['account_number'] : '';

if ($account_number) {
    // Ensure 'Approved' status exists
    $check_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Approved'"));
    if (!$check_approved) {
        $max_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(account_status_number) as max_num FROM account_status"));
        $new_num = ($max_status['max_num'] ?? 0) + 1;
        mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES ('$new_num', 'Approved')");
        $check_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Approved'"));
    }
    $new_status = $check_approved['account_status_number'];
    
    $loan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, c.first_name, c.surname, c.email, at.account_type_name FROM accounts a LEFT JOIN customers c ON a.customer = c.customer_number LEFT JOIN account_type at ON a.account_type = at.account_type_number WHERE a.account_number = '$account_number'"));
    $loan_amount = floatval($loan['loan_amount'] ?? 0);
    $loan_term = intval($loan['loan_term'] ?? 1);
    $open_date = $loan['open_date'] ?? date('Y-m-d');
    $customer_email = $loan['email'] ?? '';
    $customer_name = ($loan['first_name'] ?? '') . ' ' . ($loan['surname'] ?? '');
    $loan_type_name = $loan['account_type_name'] ?? '';
    
    $interestRates = [
        'Emergency Loan' => 2.0,
        'Educational Loan' => 1.5,
        'Personal Loan' => 3.0,
        'Business Loan' => 4.0
    ];
    
    $baseRate = $interestRates[$loan_type_name] ?? 1.5;
    $interestRate = $baseRate * $loan_term;
    $interest = ($loan_amount / 100) * $interestRate;
    $total_with_interest = $loan_amount + $interest;
    $monthly_interest = ($loan_amount * $baseRate) / 100;
    
    $due_date = date('Y-m-d', strtotime("+$loan_term months", strtotime($open_date)));
    $today = date('Y-m-d');
    
    mysqli_query($conn, "UPDATE accounts SET account_status = '$new_status', loan_balance = '$total_with_interest', interest = '$interest', due_date = '$due_date', approval_date = '$today' WHERE account_number = '$account_number'");
    mysqli_query($conn, "UPDATE loan_requirements SET status = 'approved' WHERE account_number = '$account_number'");
    
    if (!empty($customer_email)) {
        $subject = "Your Loan Has Been Approved - FundHarmony";
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Loan Approved!</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>$customer_name</strong>,</p>
                    <p>Great news! Your loan application has been approved by the admin. Here are your loan details:</p>
                    <div class='details'>
                        <p><strong>Account Number:</strong> $account_number</p>
                        <p><strong>Loan Amount:</strong> ₱" . number_format($loan_amount, 2) . "</p>
                        <p><strong>Loan Term:</strong> $loan_term month(s)</p>
                        <p><strong>Monthly Interest:</strong> " . $baseRate . "% (₱" . number_format($monthly_interest, 2) . ")</p>
                        <p><strong>Total Interest:</strong> ₱" . number_format($interest, 2) . "</p>
                        <p><strong>Total to Pay:</strong> ₱" . number_format($total_with_interest, 2) . "</p>
                        <p><strong>Due Date:</strong> " . date('M d, Y', strtotime($due_date)) . "</p>
                    </div>
                    <p>Please log in to your account to confirm your loan. You need to confirm within 7 days.</p>
                    <a href='http://localhost/mims/customer_my_loans.php' class='btn'>Confirm My Loan</a>
                </div>
                <div class='footer'>
                    <p>FundHarmony - Your Trusted Lending Partner</p>
                </div>
            </div>
        </body>
        </html>
        ";
        sendEmail($customer_email, $subject, $body);
    }
    
    $admin_username = $_SESSION['admin'] ?? 'admin';
    $admin_user_id = $_SESSION['user_id'] ?? null;
    logActivity($conn, $admin_user_id, $admin_username, 'Approve Loan', 'Approved loan account ID: ' . $account_number . ' - Amount: ₱' . number_format($loan_amount, 2), 'admin');
    
    $_SESSION['success_msg'] = "Loan approved successfully! " . $baseRate . "% monthly interest (₱" . number_format($interest, 2) . " total) added for $loan_term month term. Due date: " . date('M d, Y', strtotime($due_date)) . ". An email notification has been sent to the customer.";
}

header('Location: ../loan_approvals.php');
exit();
