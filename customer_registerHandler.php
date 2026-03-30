<?php
session_start();
require_once '../database/db_connection.php';

$first_name = $_POST['first_name'];
$surname = $_POST['surname'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$nationality = $_POST['nationality'];
$valid_id = $_POST['valid_id'];
$id_number = $_POST['id_number'];
$region = $_POST['region'];
$city = $_POST['city'];
$barangay = $_POST['barangay'];
$zip_code = $_POST['zip_code'];
$full_address = $_POST['full_address'];
$emergency_contact_name = $_POST['emergency_contact_name'] ?? '';
$emergency_contact_number = $_POST['emergency_contact_number'] ?? '';
$emergency_contact_relationship = $_POST['emergency_contact_relationship'] ?? '';
$password = $_POST['psw'];
$confirm_password = $_POST['confirm_psw'];

function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least 1 uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least 1 lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least 1 number";
    }
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors[] = "Password must contain at least 1 special character (!@#$%^&*)";
    }
    return $errors;
}

if ($password !== $confirm_password) {
    $_SESSION['register_error'] = "Passwords do not match";
    header('location: ../customer_register.php');
    exit();
}

$password_errors = validatePassword($password);
if (!empty($password_errors)) {
    $_SESSION['register_error'] = implode(". ", $password_errors) . ".";
    header('location: ../customer_register.php');
    exit();
}

// Fraud Detection
$fraud_warnings = [];

if (!empty($phone)) {
    $check_phone = mysqli_query($conn, "SELECT customer_number, first_name, surname FROM customers WHERE phone = '$phone'");
    if (mysqli_num_rows($check_phone) > 0) {
        while ($row = mysqli_fetch_assoc($check_phone)) {
            $fraud_warnings[] = "Same Phone Number found: " . $row['first_name'] . " " . $row['surname'];
        }
    }
}

if (!empty($first_name) && !empty($surname)) {
    $check_name = mysqli_query($conn, "SELECT customer_number, first_name, surname, phone FROM customers 
        WHERE LOWER(first_name) = LOWER('$first_name') AND LOWER(surname) = LOWER('$surname')");
    if (mysqli_num_rows($check_name) > 0) {
        while ($row = mysqli_fetch_assoc($check_name)) {
            $fraud_warnings[] = "Same Name found: " . $row['first_name'] . " " . $row['surname'] . " (Phone: " . ($row['phone'] ?? 'N/A') . ")";
        }
    }
}

if (!empty($full_address)) {
    $check_address = mysqli_query($conn, "SELECT customer_number, first_name, surname, phone FROM customers WHERE LOWER(full_address) = LOWER('$full_address')");
    if (mysqli_num_rows($check_address) > 0) {
        while ($row = mysqli_fetch_assoc($check_address)) {
            $fraud_warnings[] = "Same Address found: " . $row['first_name'] . " " . $row['surname'];
        }
    }
}

if (count($fraud_warnings) > 0) {
    $_SESSION['fraud_warning'] = $fraud_warnings;
}

$email = stripslashes(mysqli_real_escape_string($conn, $email));
$first_name = stripslashes(mysqli_real_escape_string($conn, $first_name));
$surname = stripslashes(mysqli_real_escape_string($conn, $surname));
$phone = stripslashes(mysqli_real_escape_string($conn, $phone));
$gender = stripslashes(mysqli_real_escape_string($conn, $gender));
$dob = stripslashes(mysqli_real_escape_string($conn, $dob));
$nationality = stripslashes(mysqli_real_escape_string($conn, $nationality));
$valid_id = stripslashes(mysqli_real_escape_string($conn, $valid_id));
$id_number = stripslashes(mysqli_real_escape_string($conn, $id_number));
$region = stripslashes(mysqli_real_escape_string($conn, $region));
$city = stripslashes(mysqli_real_escape_string($conn, $city));
$barangay = stripslashes(mysqli_real_escape_string($conn, $barangay));
$zip_code = stripslashes(mysqli_real_escape_string($conn, $zip_code));
$full_address = stripslashes(mysqli_real_escape_string($conn, $full_address));
$emergency_contact_name = stripslashes(mysqli_real_escape_string($conn, $emergency_contact_name));
$emergency_contact_number = stripslashes(mysqli_real_escape_string($conn, $emergency_contact_number));
$emergency_contact_relationship = stripslashes(mysqli_real_escape_string($conn, $emergency_contact_relationship));
$password = stripslashes(mysqli_real_escape_string($conn, $password));

$check_user = "SELECT * FROM customers WHERE email = '$email'";
$result_check = mysqli_query($conn, $check_user);

if (mysqli_num_rows($result_check) > 0) {
    $_SESSION['register_error'] = "Email already exists";
    header('location: ../customer_register.php');
    exit();
}

$customer_number = rand(100000000, 999999999);

while(mysqli_num_rows(mysqli_query($conn, "SELECT customer_number FROM customers WHERE customer_number = '$customer_number'")) > 0) {
    $customer_number = rand(100000000, 999999999);
}

$default_type = mysqli_fetch_assoc(mysqli_query($conn, "SELECT customer_type_number FROM customers_type LIMIT 1"));
$customer_type = $default_type ? $default_type['customer_type_number'] : 0;

$admin_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_number FROM users ORDER BY user_number ASC LIMIT 1"));
$user_id = $admin_user ? $admin_user['user_number'] : 0;

// Ensure emergency contact and ID columns exist
$cols = [
    'emergency_contact_name' => 'VARCHAR(100)', 
    'emergency_contact_number' => 'VARCHAR(20)', 
    'emergency_contact_relationship' => 'VARCHAR(50)',
    'valid_id' => 'VARCHAR(50)',
    'id_number' => 'VARCHAR(50)'
];
foreach ($cols as $col => $type) {
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE '$col'");
    if (mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, "ALTER TABLE customers ADD COLUMN $col $type DEFAULT NULL");
    }
}

$insert = "INSERT INTO customers (customer_number, user_id, customer_type, first_name, surname, gender, date_of_birth, nationality, email, phone, password, region, city, barangay, zip_code, full_address, emergency_contact_name, emergency_contact_number, emergency_contact_relationship, valid_id, id_number)
VALUES ('$customer_number', '$user_id', '$customer_type', '$first_name', '$surname', '$gender', '$dob', '$nationality', '$email', '$phone', '$password', '$region', '$city', '$barangay', '$zip_code', '$full_address', '$emergency_contact_name', '$emergency_contact_number', '$emergency_contact_relationship', '$valid_id', '$id_number')";

if (mysqli_query($conn, $insert)) {
    // Log activity
    logActivity($conn, $customer_number, $first_name . ' ' . $surname, 'User Registration', 'New user registered: ' . $first_name . ' ' . $surname . ' (ID: ' . $customer_number . ')', 'customer');
    
    setcookie('remember_username', '', time() - 3600, '/');
    setcookie('remember_password', '', time() - 3600, '/');
    $_SESSION['success'] = "Registration successful! Please login.";
    header('location: ../customer_login.php');
} else {
    $_SESSION['register_error'] = "Registration failed: " . mysqli_error($conn);
    header('location: ../customer_register.php');
}
