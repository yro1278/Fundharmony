<?php
session_start();
include_once '../database/db_connection.php';
$success_msg = 'New Account created successfully';

function checkFraud($conn, $customer_id, $gov_id_number, $phone, $first_name, $surname, $full_address) {
    $fraud_flags = [];
    
    if (!empty($gov_id_number)) {
        $check_id = mysqli_query($conn, "SELECT c.customer_number, c.first_name, c.surname, c.full_address FROM customers c 
            INNER JOIN customers c2 ON c.gov_id_number = c2.gov_id_number 
            WHERE c2.customer_number = '$customer_id' AND c.gov_id_number = '$gov_id_number' AND c.customer_number != '$customer_id'");
        if (mysqli_num_rows($check_id) > 0) {
            while ($row = mysqli_fetch_assoc($check_id)) {
                $fraud_flags[] = "⚠️ Same Government ID found on another account (Customer: " . $row['first_name'] . " " . $row['surname'] . ")";
            }
        }
    }
    
    if (!empty($phone)) {
        $check_phone = mysqli_query($conn, "SELECT customer_number, first_name, surname FROM customers 
            WHERE phone = '$phone' AND customer_number != '$customer_id'");
        if (mysqli_num_rows($check_phone) > 0) {
            while ($row = mysqli_fetch_assoc($check_phone)) {
                $fraud_flags[] = "⚠️ Same Phone Number found on another account (Customer: " . $row['first_name'] . " " . $row['surname'] . ")";
            }
        }
    }
    
    if (!empty($first_name) && !empty($surname)) {
        $check_name = mysqli_query($conn, "SELECT customer_number, first_name, surname, phone, full_address FROM customers 
            WHERE LOWER(first_name) = LOWER('$first_name') AND LOWER(surname) = LOWER('$surname') 
            AND customer_number != '$customer_id'");
        if (mysqli_num_rows($check_name) > 0) {
            while ($row = mysqli_fetch_assoc($check_name)) {
                $fraud_flags[] = "⚠️ Same Name found on another account: " . $row['first_name'] . " " . $row['surname'] . " (Phone: " . ($row['phone'] ?? 'N/A') . ")";
            }
        }
    }
    
    if (!empty($full_address)) {
        $check_address = mysqli_query($conn, "SELECT customer_number, first_name, surname, phone FROM customers 
            WHERE LOWER(full_address) = LOWER('$full_address') AND customer_number != '$customer_id'");
        if (mysqli_num_rows($check_address) > 0) {
            while ($row = mysqli_fetch_assoc($check_address)) {
                $fraud_flags[] = "⚠️ Same Address found on another account (Customer: " . $row['first_name'] . " " . $row['surname'] . " - Phone: " . ($row['phone'] ?? 'N/A') . ")";
            }
        }
    }
    
    $check_unpaid = mysqli_query($conn, "SELECT account_number, loan_amount, loan_balance, account_status FROM accounts 
        WHERE customer = '$customer_id' AND loan_balance > 0 AND account_status NOT IN (3, 4, -3)");
    if (mysqli_num_rows($check_unpaid) > 0) {
        while ($row = mysqli_fetch_assoc($check_unpaid)) {
            $fraud_flags[] = "⚠️ Customer has UNPAID LOAN: Account #" . $row['account_number'] . " - Balance: ₱" . number_format($row['loan_balance'], 2);
        }
    }
    
    return $fraud_flags;
}


   $user_id = $_SESSION['user_id'];
   
   $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
   
   $check_exists = mysqli_query($conn, "SELECT account_number FROM accounts WHERE account_number = '$account_number'");
   if (mysqli_num_rows($check_exists) > 0) {
       $_SESSION['error_msg'] = "Account number $account_number already exists";
       header('Location: ../openaccount.php');
       exit();
   }
   
   $customer = mysqli_real_escape_string($conn, $_POST['customer']);
   $account_type = mysqli_real_escape_string($conn, $_POST['account_type'] ?? '1');
   $account_status = mysqli_real_escape_string($conn, $_POST['account_status'] ?? '4');
   $loan_amount = mysqli_real_escape_string($conn, $_POST['loan_amount']);
   $loan_term = intval($_POST['loan_term'] ?? 1);
   
   // Interest computation values from the form
   $computed_interest = floatval($_POST['computed_interest'] ?? 0);
   $computed_total = floatval($_POST['computed_total'] ?? $loan_amount);
   $monthly_payment = floatval($_POST['monthly_payment'] ?? 0);
   
   // Calculate due date based on loan term
   $due_date = date('Y-m-d', strtotime("+{$loan_term} months"));
   
   $disbursement_method = mysqli_real_escape_string($conn, $_POST['disbursement_method']);
   $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name'] ?? '');
   $disbursement_account = mysqli_real_escape_string($conn, $_POST['disbursement_account'] ?? '');
   $disbursement_account_name = mysqli_real_escape_string($conn, $_POST['disbursement_account_name'] ?? '');
   $branch = mysqli_real_escape_string($conn, $_POST['branch'] ?? '');
   $ewallet_type = mysqli_real_escape_string($conn, $_POST['ewallet_type'] ?? '');
   $ewallet_number = mysqli_real_escape_string($conn, $_POST['ewallet_number'] ?? '');
   $ewallet_account_name = mysqli_real_escape_string($conn, $_POST['ewallet_account_name'] ?? '');
   $pickup_location = mysqli_real_escape_string($conn, $_POST['pickup_location'] ?? '');
   
    if(isset($_POST['update_account'])){
        $update = "UPDATE accounts set customer ='$customer', account_type='$account_type', account_status='$account_status', loan_amount='$loan_amount', loan_term='$loan_term', disbursement_method='$disbursement_method', bank_name='$bank_name', disbursement_account='$disbursement_account', disbursement_account_name='$disbursement_account_name', branch='$branch', ewallet_type='$ewallet_type', ewallet_number='$ewallet_number', ewallet_account_name='$ewallet_account_name', pickup_location='$pickup_location' where account_number='$account_number' ";
        if (mysqli_query($conn, $update)) {
            $admin_username = $_SESSION['admin'] ?? 'admin';
            $admin_user_id = $_SESSION['user_id'] ?? null;
            logActivity($conn, $admin_user_id, $admin_username, 'Update Account', 'Updated account ID: ' . $account_number, 'admin');
            
            $_SESSION['success_msg'] = "Account has been updated successfully.";
            header('Location: ../updateaccount.php?account_number='.$account_number);
        } else {
            echo "Error: " . $insert . " " . mysqli_error($conn);
        }
    }else{
        // Ensure gov_id_number column exists
        $check_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'gov_id_number'");
        if (mysqli_num_rows($check_col) == 0) {
            mysqli_query($conn, "ALTER TABLE customers ADD COLUMN gov_id_number VARCHAR(50) DEFAULT ''");
        }
        
        // Get customer info for fraud detection
        $customer_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT first_name, surname, phone, gov_id_number, full_address FROM customers WHERE customer_number = '$customer'"));
        $first_name = $customer_info['first_name'] ?? '';
        $surname = $customer_info['surname'] ?? '';
        $phone = $customer_info['phone'] ?? '';
        $gov_id_number = $customer_info['gov_id_number'] ?? '';
        $full_address = $customer_info['full_address'] ?? '';
        
        // Run fraud detection
        $fraud_flags = checkFraud($conn, $customer, $gov_id_number, $phone, $first_name, $surname, $full_address);
        
        if (count($fraud_flags) > 0) {
            $_SESSION['fraud_warning'] = $fraud_flags;
            $_SESSION['fraud_customer'] = $customer;
        }
        
        // Check if customer has pending balance before creating new loan
        $check_pending = mysqli_query($conn, "SELECT account_number, loan_balance, account_type FROM accounts WHERE customer = '$customer' AND loan_balance > 0 AND account_status NOT IN (3, 4)");
        
        if (mysqli_num_rows($check_pending) > 0) {
            $pending_loan = mysqli_fetch_assoc($check_pending);
            $_SESSION['error_msg'] = "Cannot create new loan. Customer has a pending balance of ₱" . number_format($pending_loan['loan_balance'], 2) . " from account #" . $pending_loan['account_number'] . ". Please settle the existing loan first.";
            header('Location: ../openaccount.php');
            exit();
        }
        
        $insert = "INSERT INTO accounts
        (user_id, account_number, customer, account_type, account_status, loan_amount, loan_term, loan_balance, interest, due_date, disbursement_method, bank_name, disbursement_account, disbursement_account_name, branch, ewallet_type, ewallet_number, ewallet_account_name, pickup_location)
        VALUES
        ('$user_id', '$account_number','$customer', '$account_type', '$account_status', '$loan_amount', '$loan_term', '$computed_total', '$computed_interest', '$due_date', '$disbursement_method', '$bank_name', '$disbursement_account', '$disbursement_account_name', '$branch', '$ewallet_type', '$ewallet_number', '$ewallet_account_name', '$pickup_location')";

if (mysqli_query($conn, $insert)) {
            $admin_username = $_SESSION['admin'] ?? 'admin';
            $admin_user_id = $_SESSION['user_id'] ?? null;
            logActivity($conn, $admin_user_id, $admin_username, 'Create Account', 'Created new loan account ID: ' . $account_number . ' for customer: ' . $customer, 'admin');
            
            $_SESSION['success_msg'] = $success_msg;
            header('Location: ../openaccount.php');
        } else {
            echo "Error: " . $insert . " " . mysqli_error($conn);
        }
    }
    
    mysqli_close($conn);


