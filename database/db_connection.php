<?php
date_default_timezone_set('Asia/Manila');

$conn = new mysqli('localhost', 'root', '', 'mims')or die(mysqli_connect_error($conn));
mysqli_query($conn, "SET time_zone = '+08:00'");

$check_customer_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'user_id'");
if (mysqli_num_rows($check_customer_col) == 0) {
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN user_id INT(10) DEFAULT NULL");
}

$check_account_col = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'user_id'");
if (mysqli_num_rows($check_account_col) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN user_id INT(10) DEFAULT NULL");
}

$check_payments_table = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
if (mysqli_num_rows($check_payments_table) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `payments` (
      `payment_number` int(10) NOT NULL,
      `user_id` int(10) DEFAULT NULL,
      `account_number` int(10) NOT NULL,
      `payment_amount` decimal(10,2) NOT NULL,
      `payment_date` date NOT NULL,
      `payment_method` varchar(50) NOT NULL,
      `notes` text,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`payment_number`)
    )");
} else {
    $check_payments_col = mysqli_query($conn, "SHOW COLUMNS FROM payments LIKE 'user_id'");
    if (mysqli_num_rows($check_payments_col) == 0) {
        mysqli_query($conn, "ALTER TABLE payments ADD COLUMN user_id INT(10) DEFAULT NULL");
    }
}

$first_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_number FROM users ORDER BY user_number ASC LIMIT 1"));
if ($first_user) {
    $default_user_id = $first_user['user_number'];
    mysqli_query($conn, "UPDATE customers SET user_id = '$default_user_id' WHERE user_id IS NULL");
    mysqli_query($conn, "UPDATE accounts SET user_id = '$default_user_id' WHERE user_id IS NULL");
    mysqli_query($conn, "UPDATE payments SET user_id = '$default_user_id' WHERE user_id IS NULL");
}

$loan_types = ['Educational Loan', 'Business Loan', 'Emergency Loan', 'Personal Loan'];
foreach ($loan_types as $type) {
    $check_type = mysqli_query($conn, "SELECT account_type_number FROM account_type WHERE account_type_name = '$type'");
    if (mysqli_num_rows($check_type) == 0) {
        $type_number = rand(100000000, 999999999);
        mysqli_query($conn, "INSERT INTO account_type (account_type_number, account_type_name) VALUES ('$type_number', '$type')");
    }
}

// Check if customers_type table exists and create if not
$check_customers_type_table = mysqli_query($conn, "SHOW TABLES LIKE 'customers_type'");
if (mysqli_num_rows($check_customers_type_table) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `customers_type` (
      `customer_type_number` int(10) NOT NULL,
      `customer_type_name` varchar(100) NOT NULL,
      `customer_type_description` text,
      `registration_date` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`customer_type_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} else {
    // Table exists, check if customer_type_description column exists
    $check_desc_col = mysqli_query($conn, "SHOW COLUMNS FROM customers_type LIKE 'customer_type_description'");
    if (mysqli_num_rows($check_desc_col) == 0) {
        mysqli_query($conn, "ALTER TABLE customers_type ADD COLUMN customer_type_description text");
    }
}

// Add default customer types - ensure they exist
$default_customer_types = [
    ['id' => 1, 'name' => 'Student', 'desc' => 'Student customer'],
    ['id' => 2, 'name' => 'Employee', 'desc' => 'Employed customer'],
    ['id' => 3, 'name' => 'Self-Employed', 'desc' => 'Self-employed customer'],
    ['id' => 4, 'name' => 'Business Owner', 'desc' => 'Business owner customer'],
    ['id' => 5, 'name' => 'OFW', 'desc' => 'Overseas Filipino Worker'],
    ['id' => 6, 'name' => 'Senior Citizen', 'desc' => 'Senior citizen customer'],
    ['id' => 7, 'name' => 'Pensioner', 'desc' => 'Pensioner customer'],
    ['id' => 8, 'name' => 'Unemployed', 'desc' => 'Unemployed customer']
];

foreach ($default_customer_types as $type) {
    $check_exists = mysqli_query($conn, "SELECT customer_type_number FROM customers_type WHERE customer_type_number = '{$type['id']}'");
    if (mysqli_num_rows($check_exists) == 0) {
        mysqli_query($conn, "INSERT INTO customers_type (customer_type_number, customer_type_name, customer_type_description) VALUES ('{$type['id']}', '{$type['name']}', '{$type['desc']}')");
    }
}

// Check if customer_type column exists and its structure
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'customer_type'");
if (mysqli_num_rows($check_col) > 0) {
    // Column exists, check if it has foreign key constraint
    $col_info = mysqli_fetch_assoc($check_col);
    
    // Drop foreign key if exists and modify to allow NULL
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
    mysqli_query($conn, "ALTER TABLE customers MODIFY COLUMN customer_type INT NULL");
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
} else {
    // Add column
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN customer_type INT NULL");
}

$check_email_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'email'");
if (mysqli_num_rows($check_email_col) == 0) {
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN email VARCHAR(100) DEFAULT NULL");
}

$check_phone_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'phone'");
if (mysqli_num_rows($check_phone_col) == 0) {
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN phone VARCHAR(50) DEFAULT NULL");
}

$check_password_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'password'");
if (mysqli_num_rows($check_password_col) == 0) {
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN password VARCHAR(255) DEFAULT NULL");
}

$check_status_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'status'");
if (mysqli_num_rows($check_status_col) == 0) {
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN status TINYINT(1) DEFAULT 1");
}

$check_loan_amount = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'loan_amount'");
if (mysqli_num_rows($check_loan_amount) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN loan_amount DECIMAL(15,2) DEFAULT 0");
}

$check_balance = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'loan_balance'");
if (mysqli_num_rows($check_balance) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN loan_balance DECIMAL(15,2) DEFAULT 0");
}

$check_interest = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'interest'");
if (mysqli_num_rows($check_interest) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN interest DECIMAL(15,2) DEFAULT 0");
}

$check_term = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'loan_term'");
if (mysqli_num_rows($check_term) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN loan_term INT DEFAULT 1");
}

$check_due = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'due_date'");
if (mysqli_num_rows($check_due) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN due_date DATE DEFAULT NULL");
}

$check_overdue = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'overdue_interest'");
if (mysqli_num_rows($check_overdue) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN overdue_interest DECIMAL(15,2) DEFAULT 0");
}

$check_penalty = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'penalty'");
if (mysqli_num_rows($check_penalty) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN penalty DECIMAL(15,2) DEFAULT 0");
}

$check_disburse = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'disbursement_method'");
if (mysqli_num_rows($check_disburse) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN disbursement_method VARCHAR(50) DEFAULT NULL");
}
$check_bank = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'bank_name'");
if (mysqli_num_rows($check_bank) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN bank_name VARCHAR(100) DEFAULT NULL");
}
$check_account = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'disbursement_account'");
if (mysqli_num_rows($check_account) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN disbursement_account VARCHAR(50) DEFAULT NULL");
}
$check_acc_name = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'disbursement_account_name'");
if (mysqli_num_rows($check_acc_name) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN disbursement_account_name VARCHAR(100) DEFAULT NULL");
}
$check_branch = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'branch'");
if (mysqli_num_rows($check_branch) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN branch VARCHAR(100) DEFAULT NULL");
}
$check_ewallet_type = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'ewallet_type'");
if (mysqli_num_rows($check_ewallet_type) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN ewallet_type VARCHAR(50) DEFAULT NULL");
}
$check_ewallet_num = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'ewallet_number'");
if (mysqli_num_rows($check_ewallet_num) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN ewallet_number VARCHAR(50) DEFAULT NULL");
}
$check_ewallet_name = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'ewallet_account_name'");
if (mysqli_num_rows($check_ewallet_name) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN ewallet_account_name VARCHAR(100) DEFAULT NULL");
}
$check_pickup = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'pickup_location'");
if (mysqli_num_rows($check_pickup) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN pickup_location VARCHAR(100) DEFAULT NULL");
}

$check_reject_notes = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'reject_notes'");
if (mysqli_num_rows($check_reject_notes) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN reject_notes TEXT DEFAULT NULL");
}

$check_approval_date = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'approval_date'");
if (mysqli_num_rows($check_approval_date) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN approval_date DATE DEFAULT NULL");
}

$check_release_date = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'release_date'");
if (mysqli_num_rows($check_release_date) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN release_date DATE DEFAULT NULL");
}

$check_region = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'region'");
if (mysqli_num_rows($check_region) == 0) {
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN region VARCHAR(100) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN city VARCHAR(100) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN municipality VARCHAR(100) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN barangay VARCHAR(100) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN zip_code VARCHAR(20) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN full_address VARCHAR(255) DEFAULT NULL");
} else {
    $check_municipality = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'municipality'");
    if (mysqli_num_rows($check_municipality) == 0) {
        mysqli_query($conn, "ALTER TABLE customers ADD COLUMN municipality VARCHAR(100) DEFAULT NULL");
    }
}

$check_requirements = mysqli_query($conn, "SHOW TABLES LIKE 'loan_requirements'");
if (mysqli_num_rows($check_requirements) == 0) {
    mysqli_query($conn, "CREATE TABLE loan_requirements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_number INT NOT NULL,
        customer_number INT NOT NULL,
        requirement_type VARCHAR(100) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

$check_pending = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Pending'");
if (mysqli_num_rows($check_pending) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (4, 'Pending')");
}

$check_rejected = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Rejected'");
if (mysqli_num_rows($check_rejected) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (3, 'Rejected')");
}

$check_partial = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Partial'");
if (mysqli_num_rows($check_partial) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (5, 'Partial')");
}

$check_due_date = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Due Date'");
if (mysqli_num_rows($check_due_date) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (6, 'Due Date')");
}

$check_up_to_date = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Up to Date'");
if (mysqli_num_rows($check_up_to_date) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (7, 'Up to Date')");
}

$check_role_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
if (mysqli_num_rows($check_role_col) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN role ENUM('admin', 'customer') DEFAULT 'admin'");
    mysqli_query($conn, "UPDATE users SET role = 'admin' WHERE username = 'admin'");
}

function calculateOverdueInterest($conn) {
    $daily_rate = 50;
    $today = date('Y-m-d');
    
    $approved_st = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Approved'"));
    $approved_st_id = $approved_st['account_status_number'] ?? 0;
    $excl = $approved_st_id ? "AND account_status != '$approved_st_id'" : "";
    
    $active_accounts = mysqli_query($conn, "SELECT account_number, due_date, loan_balance, overdue_interest FROM accounts
        WHERE due_date IS NOT NULL
        AND due_date < '$today'
        AND (loan_balance IS NULL OR loan_balance > 0)
        $excl");
    
    while ($account = mysqli_fetch_assoc($active_accounts)) {
        $due_date = $account['due_date'];
        $days_overdue = (strtotime($today) - strtotime($due_date)) / (60 * 60 * 24);
        
        if ($days_overdue > 0) {
            $new_overdue = $days_overdue * $daily_rate;
            mysqli_query($conn, "UPDATE accounts SET overdue_interest = '$new_overdue' WHERE account_number = '" . $account['account_number'] . "'");
        }
    }
}

function updateLoanStatus($conn) {
    $partial_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Partial'"));
    $due_date_status = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Due Date'");
    $up_to_date_status = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Up to Date'");
    $closed_status = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Closed'");
    
    $partial_id = $partial_status['account_status_number'] ?? 5;
    $due_date_id = mysqli_fetch_assoc($due_date_status)['account_status_number'] ?? 6;
    $up_to_date_id = mysqli_fetch_assoc($up_to_date_status)['account_status_number'] ?? 7;
    $closed_id = mysqli_fetch_assoc($closed_status)['account_status_number'] ?? -3;
    
    $today = date('Y-m-d');
    
    // Get approved status id to exclude unconfirmed loans
    $approved_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Approved'"));
    $approved_id = $approved_status['account_status_number'] ?? null;
    
    $exclude_approved = $approved_id ? "AND a.account_status != '$approved_id'" : "";
    
    $accounts = mysqli_query($conn, "SELECT a.account_number, a.loan_amount, a.interest, a.loan_balance, a.open_date, a.loan_term,
        (SELECT SUM(payment_amount) FROM payments WHERE account_number = a.account_number) as total_paid
        FROM accounts a
        WHERE (a.account_status IN (-2, 5, 6, 7) OR (a.account_status = -2 AND a.loan_balance > 0))
        $exclude_approved");
    
    while ($account = mysqli_fetch_assoc($accounts)) {
        $loan_amount = floatval($account['loan_amount'] ?? 0);
        $interest = floatval($account['interest'] ?? 0);
        $loan_balance = floatval($account['loan_balance'] ?? $loan_amount);
        $total_paid = floatval($account['total_paid'] ?? 0);
        $original_total = $loan_amount + $interest;
        $loan_term = intval($account['loan_term'] ?? 1);
        $open_date = $account['open_date'];
        
        if ($loan_balance <= 0) {
            mysqli_query($conn, "UPDATE accounts SET account_status = '$closed_id' WHERE account_number = '" . $account['account_number'] . "'");
            continue;
        }
        
        $monthly_payment = $loan_term > 0 ? $original_total / $loan_term : 0;
        
        if ($total_paid > 0 && $total_paid < $original_total) {
            mysqli_query($conn, "UPDATE accounts SET account_status = '$partial_id' WHERE account_number = '" . $account['account_number'] . "'");
        } elseif ($open_date && $monthly_payment > 0) {
            $start_date = new DateTime($open_date);
            $current_date = new DateTime();
            $months_passed = (($current_date->format('Y') - $start_date->format('Y')) * 12) + ($current_date->format('n') - $start_date->format('n'));
            
            $expected_paid = $monthly_payment * min($months_passed, $loan_term);
            
            if ($total_paid >= $expected_paid) {
                mysqli_query($conn, "UPDATE accounts SET account_status = '$up_to_date_id' WHERE account_number = '" . $account['account_number'] . "'");
            } else {
                mysqli_query($conn, "UPDATE accounts SET account_status = '$due_date_id' WHERE account_number = '" . $account['account_number'] . "'");
            }
        }
    }
}

calculateOverdueInterest($conn);
updateLoanStatus($conn);

$check_activity_logs = mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'");
if (mysqli_num_rows($check_activity_logs) == 0) {
    mysqli_query($conn, "CREATE TABLE activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        username VARCHAR(100) DEFAULT NULL,
        user_type ENUM('admin', 'customer') DEFAULT NULL,
        action VARCHAR(255) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at),
        INDEX idx_user_id (user_id)
    )");
} else {
    // Check if user_type column exists, if not add it
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM activity_logs LIKE 'user_type'");
    if (mysqli_num_rows($check_column) == 0) {
        mysqli_query($conn, "ALTER TABLE activity_logs ADD COLUMN user_type ENUM('admin', 'customer') DEFAULT NULL AFTER username");
    }
    // Also check for ip_address column
    $check_ip = mysqli_query($conn, "SHOW COLUMNS FROM activity_logs LIKE 'ip_address'");
    if (mysqli_num_rows($check_ip) == 0) {
        mysqli_query($conn, "ALTER TABLE activity_logs ADD COLUMN ip_address VARCHAR(45) DEFAULT NULL");
    }
}

function logActivity($conn, $user_id, $username, $action, $description = '', $user_type = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $username = mysqli_real_escape_string($conn, $username);
    $action = mysqli_real_escape_string($conn, $action);
    $description = mysqli_real_escape_string($conn, $description);
    $user_type = mysqli_real_escape_string($conn, $user_type);
    
    // Use PHP's current timestamp for accuracy
    $current_timestamp = date('Y-m-d H:i:s');
    
    mysqli_query($conn, "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
        VALUES ('$user_id', '$username', '$user_type', '$action', '$description', '$ip_address', '$current_timestamp')");
}

$check_login_attempts = mysqli_query($conn, "SHOW TABLES LIKE 'login_attempts'");
if (mysqli_num_rows($check_login_attempts) == 0) {
    mysqli_query($conn, "CREATE TABLE login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempts INT DEFAULT 1,
        locked_until TIMESTAMP NULL DEFAULT NULL,
        last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_ip (ip_address)
    )");
}

function checkLoginLock($conn, $username, $ip_address) {
    $username = mysqli_real_escape_string($conn, $username);
    $ip_address = mysqli_real_escape_string($conn, $ip_address);
    
    $result = mysqli_query($conn, "SELECT * FROM login_attempts WHERE username = '$username' OR ip_address = '$ip_address'");
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['locked_until'] && strtotime($row['locked_until']) > time()) {
            $remaining = strtotime($row['locked_until']) - time();
            $minutes = ceil($remaining / 60);
            return ['locked' => true, 'remaining' => $remaining, 'minutes' => $minutes];
        }
    }
    return ['locked' => false];
}

function recordFailedAttempt($conn, $username, $ip_address) {
    $username = mysqli_real_escape_string($conn, $username);
    $ip_address = mysqli_real_escape_string($conn, $ip_address);
    
    $result = mysqli_query($conn, "SELECT * FROM login_attempts WHERE username = '$username'");
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $new_attempts = $row['attempts'] + 1;
        
        if ($new_attempts >= 5) {
            $locked_until = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            mysqli_query($conn, "UPDATE login_attempts SET attempts = '$new_attempts', locked_until = '$locked_until', last_attempt = NOW() WHERE username = '$username'");
            return ['locked' => true, 'attempts' => $new_attempts];
        } else {
            mysqli_query($conn, "UPDATE login_attempts SET attempts = '$new_attempts', last_attempt = NOW() WHERE username = '$username'");
            return ['locked' => false, 'attempts' => $new_attempts];
        }
    } else {
        $locked_until = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        mysqli_query($conn, "INSERT INTO login_attempts (username, ip_address, attempts, locked_until) VALUES ('$username', '$ip_address', 1, NULL)");
        return ['locked' => false, 'attempts' => 1];
    }
}

function clearLoginAttempts($conn, $username) {
    $username = mysqli_real_escape_string($conn, $username);
    mysqli_query($conn, "DELETE FROM login_attempts WHERE username = '$username'");
}

$check_password_tokens = mysqli_query($conn, "SHOW TABLES LIKE 'password_reset_tokens'");
if (mysqli_num_rows($check_password_tokens) == 0) {
    mysqli_query($conn, "CREATE TABLE password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(64) NOT NULL,
        user_type ENUM('admin', 'customer') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        INDEX idx_email (email)
    )");
}

$check_qr_code = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'qr_code'");
if (mysqli_num_rows($check_qr_code) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN qr_code VARCHAR(64) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN qr_code_enabled TINYINT(1) DEFAULT 0");
}

$check_qr_code_customer = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'qr_code'");
if (mysqli_num_rows($check_qr_code_customer) == 0) {
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN qr_code VARCHAR(64) DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN qr_code_enabled TINYINT(1) DEFAULT 0");
}

function generateQRToken() {
    return bin2hex(random_bytes(32));
}

function verifyQRToken($stored_token, $entered_token) {
    return hash_equals($stored_token, hash('sha256', $entered_token));
}

function hashQRToken($token) {
    return hash('sha256', $token);
}
?>