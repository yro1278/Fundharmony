<?php
session_start();
require_once '../database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'generate':
            generateQRCode($conn);
            break;
        case 'verify':
            verifyQRCode($conn);
            break;
        case 'disable':
            disableQRCode($conn);
            break;
        case 'check':
            checkQRStatus($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function generateQRCode($conn) {
    header('Content-Type: application/json');
    
    $user_id = $_SESSION['otp_user_id'] ?? $_SESSION['otp_customer_id'] ?? null;
    $user_type = isset($_SESSION['otp_user_id']) ? 'admin' : 'customer';
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit();
    }
    
    $token = generateQRToken();
    $hashed_token = hashQRToken($token);
    
    if ($user_type === 'admin') {
        mysqli_query($conn, "UPDATE users SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE user_number = '$user_id'");
    } else {
        mysqli_query($conn, "UPDATE customers SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE customer_number = '$user_id'");
    }
    
    $_SESSION['qr_setup_token'] = $token;
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'message' => 'QR Code generated successfully'
    ]);
    exit();
}

function verifyQRCode($conn) {
    header('Content-Type: application/json');
    
    $scanned_token = $_POST['token'] ?? '';
    
    if (empty($scanned_token)) {
        echo json_encode(['success' => false, 'message' => 'No QR code detected']);
        exit();
    }
    
    $decoded = json_decode($scanned_token, true);
    
    if ($decoded && isset($decoded['token']) && isset($decoded['user_id'])) {
        $qr_user_id = intval($decoded['user_id']);
        $qr_email = $decoded['email'] ?? '';
        $qr_type = $decoded['type'] ?? '';
        
        if ($qr_type === 'admin' || $qr_type === 'customer') {
            if ($qr_type === 'admin') {
                $result = mysqli_query($conn, "SELECT * FROM users WHERE user_number = '$qr_user_id' AND qr_code_enabled = 1");
            } else {
                $result = mysqli_query($conn, "SELECT * FROM customers WHERE customer_number = '$qr_user_id' AND qr_code_enabled = 1");
            }
            
            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                
                if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
                    echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
                    exit();
                }
                
                if ($user['email'] !== $_SESSION['otp_email']) {
                    echo json_encode(['success' => false, 'message' => 'QR code does not match the logged in user.']);
                    exit();
                }
                
                if ($qr_type === 'admin') {
                    $_SESSION['admin'] = $user['username'];
                    $_SESSION['admin_name'] = $user['username'];
                    $_SESSION['user_id'] = $user['user_number'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'] ?? 'admin';
                    
                    clearLoginAttempts($conn, $user['email']);
                    
                    $admin_user_id = intval($user['user_number']);
                    $admin_username = mysqli_real_escape_string($conn, $user['username']);
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
                    $current_timestamp = date('Y-m-d H:i:s');
                    mysqli_query($conn, "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
                        VALUES ('$admin_user_id', '$admin_username', 'admin', 'Admin Login', 'Admin logged in successfully (QR code verified)', '$ip_address', '$current_timestamp')");
                    
                    $redirect = $user['role'] === 'customer' ? 'customer_dashboard.php' : 'dashboard.php';
                } else {
                    $_SESSION['customer_id'] = $user['customer_number'];
                    $_SESSION['customer_name'] = $user['first_name'] . ' ' . $user['surname'];
                    $_SESSION['customer_email'] = $user['email'];
                    
                    $check_last = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'last_login'");
                    if (mysqli_num_rows($check_last) == 0) {
                        mysqli_query($conn, "ALTER TABLE customers ADD COLUMN last_login DATETIME DEFAULT NULL");
                    }
                    mysqli_query($conn, "UPDATE customers SET last_login = NOW() WHERE customer_number = '" . $user['customer_number'] . "'");
                    
                    $customer_id = intval($user['customer_number']);
                    $customer_name = mysqli_real_escape_string($conn, $user['first_name'] . ' ' . $user['surname']);
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
                    $current_timestamp = date('Y-m-d H:i:s');
                    mysqli_query($conn, "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
                        VALUES ('$customer_id', '$customer_name', 'customer', 'User Login', 'User logged in successfully (QR code verified)', '$ip_address', '$current_timestamp')");
                    
                    $redirect = 'customer_dashboard.php';
                }
                
                $remember = $_SESSION['otp_remember'] ?? '';
                if ($remember == 'on') {
                    $password = $user['password'];
                    setcookie('remember_username', $user['email'], time() + (86400 * 30), "/");
                    setcookie('remember_password', $password, time() + (86400 * 30), "/");
                }
                
                unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry'], 
                      $_SESSION['otp_user_id'], $_SESSION['otp_username'], $_SESSION['otp_role'], 
                      $_SESSION['otp_remember'], $_SESSION['qr_method']);
                $_SESSION['fresh_login'] = true;
                
                echo json_encode([
                    'success' => true,
                    'redirect' => $redirect,
                    'message' => 'Login successful'
                ]);
                exit();
            }
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid or expired QR code']);
    exit();
}

function disableQRCode($conn) {
    header('Content-Type: application/json');
    
    $user_id = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
    $user_type = isset($_SESSION['user_id']) ? 'admin' : 'customer';
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }
    
    if ($user_type === 'admin') {
        mysqli_query($conn, "UPDATE users SET qr_code = NULL, qr_code_enabled = 0 WHERE user_number = '$user_id'");
    } else {
        mysqli_query($conn, "UPDATE customers SET qr_code = NULL, qr_code_enabled = 0 WHERE customer_number = '$user_id'");
    }
    
    echo json_encode(['success' => true, 'message' => 'QR Code disabled successfully']);
    exit();
}

function checkQRStatus($conn) {
    header('Content-Type: application/json');
    
    $user_id = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
    $user_type = isset($_SESSION['user_id']) ? 'admin' : 'customer';
    
    if (!$user_id) {
        echo json_encode(['enabled' => false]);
        exit();
    }
    
    if ($user_type === 'admin') {
        $result = mysqli_query($conn, "SELECT qr_code_enabled FROM users WHERE user_number = '$user_id'");
    } else {
        $result = mysqli_query($conn, "SELECT qr_code_enabled FROM customers WHERE customer_number = '$user_id'");
    }
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(['enabled' => (bool)$row['qr_code_enabled']]);
    } else {
        echo json_encode(['enabled' => false]);
    }
    exit();
}
