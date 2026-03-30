<?php
require_once __DIR__ . '/../database/db_connection.php';

function createNotificationsTable($conn) {
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'loan_notifications'");
    if (mysqli_num_rows($table_check) == 0) {
        $create_table = "CREATE TABLE IF NOT EXISTS loan_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            account_number VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            notification_type VARCHAR(20) DEFAULT 'reminder',
            is_read TINYINT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_id),
            INDEX idx_read (is_read)
        )";
        mysqli_query($conn, $create_table);
    }
}

function notificationExists($conn, $customer_id, $account_number, $notification_type, $days_offset = null) {
    $sql = "SELECT id FROM loan_notifications 
            WHERE customer_id = '$customer_id' 
            AND account_number = '$account_number' 
            AND notification_type = '$notification_type'";
    
    if ($days_offset !== null) {
        $sql .= " AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL $days_offset DAY)";
    }
    
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

function sendAutoNotification($conn, $customer_id, $account_number, $message, $notification_type) {
    $stmt = $conn->prepare("INSERT INTO loan_notifications (customer_id, account_number, message, notification_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $customer_id, $account_number, $message, $notification_type);
    $stmt->execute();
    $stmt->close();
}

createNotificationsTable($conn);

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$week_later = date('Y-m-d', strtotime('+7 days'));
$yesterday = date('Y-m-d', strtotime('-1 day'));

$loans = mysqli_query($conn, "
    SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.loan_term, a.open_date,
           c.first_name, c.surname, c.phone, c.email,
           at.account_type_name
    FROM accounts a
    INNER JOIN customers c ON a.customer = c.customer_number
    LEFT JOIN account_type at ON a.account_type = at.account_type_number
    WHERE a.loan_balance > 0 
    AND a.account_status NOT IN (3, 4, -3)
");

while ($loan = mysqli_fetch_assoc($loans)) {
    $account_number = $loan['account_number'];
    $customer_id = $loan['customer'];
    $fullname = $loan['first_name'] . ' ' . $loan['surname'];
    $loan_type = $loan['account_type_name'] ?? 'Loan';
    $loan_amount = number_format($loan['loan_amount'], 2);
    $balance = number_format($loan['loan_balance'], 2);
    
    $open_date = new DateTime($loan['open_date']);
    $loan_term = intval($loan['loan_term']);
    $due_date = $open_date->modify("+$loan_term months")->format('Y-m-d');
    
    $days_until_due = (strtotime($due_date) - strtotime($today)) / (60 * 60 * 24);
    
    if ($days_until_due < 0) {
        $overdue_days = abs($days_until_due);
        $type = 'overdue';
        
        if (!notificationExists($conn, $customer_id, $account_number, 'overdue_alert')) {
            $message = "⚠️ OVERDUE ALERT: Your $loan_type (Account #$account_number) is overdue by $overdue_days day(s)! Total Balance: ₱$balance. Please contact us immediately to avoid further penalties.";
            sendAutoNotification($conn, $customer_id, $account_number, $message, 'overdue_alert');
        }
        
        if ($overdue_days == 7 && !notificationExists($conn, $customer_id, $account_number, 'overdue_7days', 0)) {
            $message = "⚠️ 7-Day Overdue Notice: Your $loan_type (Account #$account_number) is now 7 days overdue. Please make payment immediately to avoid additional fees.";
            sendAutoNotification($conn, $customer_id, $account_number, $message, 'overdue_7days');
        }
        
        if ($overdue_days == 30 && !notificationExists($conn, $customer_id, $account_number, 'overdue_30days', 0)) {
            $message = "⚠️ 30-Day Overdue Warning: Your $loan_type (Account #$account_number) is now 30 days overdue. Immediate action required to prevent legal proceedings.";
            sendAutoNotification($conn, $customer_id, $account_number, $message, 'overdue_30days');
        }
    }
    elseif ($days_until_due == 0) {
        $type = 'due_today';
        
        if (!notificationExists($conn, $customer_id, $account_number, 'due_today')) {
            $message = "📢 DUE TODAY: Your $loan_type payment (Account #$account_number) is due today! Amount: ₱$balance. Please make your payment on or before close of business.";
            sendAutoNotification($conn, $customer_id, $account_number, $message, 'due_today');
        }
    }
    elseif ($days_until_due == 1) {
        $type = 'due_tomorrow';
        
        if (!notificationExists($conn, $customer_id, $account_number, 'due_tomorrow')) {
            $message = "⏰ REMINDER: Your $loan_type payment (Account #$account_number) is due tomorrow! Amount: ₱$balance. Please prepare your payment.";
            sendAutoNotification($conn, $customer_id, $account_number, $message, 'due_tomorrow');
        }
    }
    elseif ($days_until_due == 3) {
        $type = 'due_3days';
        
        if (!notificationExists($conn, $customer_id, $account_number, 'due_3days')) {
            $message = "📅 Payment Reminder: Your $loan_type (Account #$account_number) is due in 3 days. Amount: ₱$balance. Don't miss your due date!";
            sendAutoNotification($conn, $customer_id, $account_number, $message, 'due_3days');
        }
    }
    elseif ($days_until_due == 7) {
        $type = 'due_7days';
        
        if (!notificationExists($conn, $customer_id, $account_number, 'due_7days')) {
            $message = "📅 Upcoming Payment: Your $loan_type (Account #$account_number) is due in 7 days. Amount: ₱$balance. Plan ahead!";
            sendAutoNotification($conn, $customer_id, $account_number, $message, 'due_7days');
        }
    }
}


