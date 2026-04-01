<?php
session_start();
require_once '../database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
    $account_number = isset($_POST['account_number']) ? $_POST['account_number'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    
    if ($customer_id && $account_number && $message) {
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
        
        $stmt = $conn->prepare("INSERT INTO loan_notifications (customer_id, account_number, message, notification_type) VALUES (?, ?, ?, 'reminder')");
        $stmt->bind_param("iss", $customer_id, $account_number, $message);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
