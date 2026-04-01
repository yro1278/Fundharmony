<?php
session_start();
include_once '../database/db_connection.php';
$success_msg = 'New customer created successfully';

// Function to generate a unique customer number
function generateUniqueCustomerNumber($conn) {
    $max_attempts = 10;
    $attempt = 0;
    
    do {
        // Generate a 9-digit random number starting with a non-zero digit
        $customer_number = mt_rand(100000000, 999999999);
        
        // Check if this number already exists
        $check = mysqli_query($conn, "SELECT customer_number FROM customers WHERE customer_number = '$customer_number'");
        $exists = mysqli_num_rows($check) > 0;
        
        $attempt++;
        
        // If number doesn't exist or we've tried enough times, use it
        if (!$exists || $attempt >= $max_attempts) {
            break;
        }
    } while ($exists);
    
    return $customer_number;
}

if (isset($_POST['addcustomer'])) {

    // Auto-generate customer number if not provided
    $customer_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['customer_number']));
    
    // If customer number is empty, generate one server-side
    if (empty($customer_number)) {
        $customer_number = generateUniqueCustomerNumber($conn);
    } else {
        // Check if the provided number already exists
        $check_exists = mysqli_query($conn, "SELECT customer_number FROM customers WHERE customer_number = '$customer_number'");
        if(mysqli_num_rows($check_exists) > 0) {
            // Generate a new unique number
            $customer_number = generateUniqueCustomerNumber($conn);
        }
    }
    
    $customer_type = stripcslashes(mysqli_real_escape_string($conn, $_POST['customer_type'] ?? ''));
    $first_name = stripcslashes(mysqli_real_escape_string($conn, $_POST['first_name'] ?? ''));
    $middle_name = stripcslashes(mysqli_real_escape_string($conn, $_POST['middle_name'] ?? ''));
    $surname = stripcslashes(mysqli_real_escape_string($conn, $_POST['surname'] ?? ''));
    $gender = stripcslashes(mysqli_real_escape_string($conn, $_POST['gender'] ?? ''));
    $date_of_birth = stripcslashes(mysqli_real_escape_string($conn, $_POST['date_of_birth'] ?? ''));
    $nationality = stripcslashes(mysqli_real_escape_string($conn, $_POST['nationality'] ?? ''));
    $email = stripcslashes(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $phone = stripcslashes(mysqli_real_escape_string($conn, $_POST['phone'] ?? ''));
    $region = stripcslashes(mysqli_real_escape_string($conn, $_POST['region'] ?? ''));
    $city = stripcslashes(mysqli_real_escape_string($conn, $_POST['city'] ?? ''));
    $barangay = stripcslashes(mysqli_real_escape_string($conn, $_POST['barangay'] ?? ''));
    $zip_code = stripcslashes(mysqli_real_escape_string($conn, $_POST['zip_code'] ?? ''));
    $full_address = stripcslashes(mysqli_real_escape_string($conn, $_POST['full_address'] ?? ''));
    $password = stripcslashes(mysqli_real_escape_string($conn, $_POST['password'] ?? ''));
    $confirm_password = stripcslashes(mysqli_real_escape_string($conn, $_POST['confirm_password'] ?? ''));
    
    // Emergency contact fields
    $emergency_contact_name = stripcslashes(mysqli_real_escape_string($conn, $_POST['emergency_contact_name'] ?? ''));
    $emergency_contact_number = stripcslashes(mysqli_real_escape_string($conn, $_POST['emergency_contact_number'] ?? ''));
    $emergency_contact_relationship = stripcslashes(mysqli_real_escape_string($conn, $_POST['emergency_contact_relationship'] ?? ''));
    $gov_id_type = mysqli_real_escape_string($conn, $_POST['gov_id_type'] ?? '');
    $gov_id_number = mysqli_real_escape_string($conn, $_POST['gov_id_number'] ?? '');
    
    if($password !== $confirm_password) {
        $_SESSION['error_msg'] = "Passwords do not match";
        header('Location: ../addcustomer.php');
        exit();
    }
    
    if(empty($password)) {
        $_SESSION['error_msg'] = "Password is required";
        header('Location: ../addcustomer.php');
        exit();
    }
    
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
    
    $password_errors = validatePassword($password);
    if (!empty($password_errors)) {
        $_SESSION['error_msg'] = implode(". ", $password_errors) . ".";
        header('Location: ../addcustomer.php');
        exit();
    }
    
    // Fraud Detection Checks
    $fraud_warnings = [];
    
    if (!empty($phone)) {
        $check_phone = mysqli_query($conn, "SELECT customer_number, first_name, surname FROM customers WHERE phone = '$phone'");
        if (mysqli_num_rows($check_phone) > 0) {
            while ($row = mysqli_fetch_assoc($check_phone)) {
                $fraud_warnings[] = "⚠️ Same Phone Number found: " . $row['first_name'] . " " . $row['surname'] . " (ID: " . $row['customer_number'] . ")";
            }
        }
    }
    
    if (!empty($gov_id_number)) {
        $check_id = mysqli_query($conn, "SELECT customer_number, first_name, surname FROM customers WHERE gov_id_number = '$gov_id_number' AND gov_id_number != ''");
        if (mysqli_num_rows($check_id) > 0) {
            while ($row = mysqli_fetch_assoc($check_id)) {
                $fraud_warnings[] = "⚠️ Same Government ID found: " . $row['first_name'] . " " . $row['surname'] . " (ID: " . $row['customer_number'] . ")";
            }
        }
    }
    
    if (!empty($email)) {
        $check_email = mysqli_query($conn, "SELECT customer_number, first_name, surname FROM customers WHERE email = '$email' AND email != ''");
        if (mysqli_num_rows($check_email) > 0) {
            while ($row = mysqli_fetch_assoc($check_email)) {
                $fraud_warnings[] = "⚠️ Same Email found: " . $row['first_name'] . " " . $row['surname'] . " (ID: " . $row['customer_number'] . ")";
            }
        }
    }
    
    if (!empty($first_name) && !empty($surname)) {
        $check_name = mysqli_query($conn, "SELECT customer_number, first_name, surname, phone, email FROM customers 
            WHERE LOWER(first_name) = LOWER('$first_name') AND LOWER(surname) = LOWER('$surname')");
        if (mysqli_num_rows($check_name) > 0) {
            while ($row = mysqli_fetch_assoc($check_name)) {
                $fraud_warnings[] = "⚠️ Same Name found: " . $row['first_name'] . " " . $row['surname'] . " (Phone: " . ($row['phone'] ?? 'N/A') . ", Email: " . ($row['email'] ?? 'N/A') . ")";
            }
        }
    }
    
    if (!empty($full_address)) {
        $check_address = mysqli_query($conn, "SELECT customer_number, first_name, surname, phone FROM customers WHERE LOWER(full_address) = LOWER('$full_address')");
        if (mysqli_num_rows($check_address) > 0) {
            while ($row = mysqli_fetch_assoc($check_address)) {
                $fraud_warnings[] = "⚠️ Same Address found: " . $row['first_name'] . " " . $row['surname'] . " (Phone: " . ($row['phone'] ?? 'N/A') . ")";
            }
        }
    }
    
    // Store fraud warnings in session to display
    if (count($fraud_warnings) > 0) {
        $_SESSION['fraud_warning'] = $fraud_warnings;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $check_exists = mysqli_query($conn, "SELECT customer_number FROM customers WHERE customer_number = '$customer_number'");
    if(mysqli_num_rows($check_exists) > 0) {
        $_SESSION['error_msg'] = "Customer number already exists. Please use a different number.";
        header('Location: ../addcustomer.php');
        exit();
    }
    
    // Ensure emergency contact and government ID columns exist
    $cols = [
        'emergency_contact_name' => 'VARCHAR(100)', 
        'emergency_contact_number' => 'VARCHAR(20)', 
        'emergency_contact_relationship' => 'VARCHAR(50)',
        'gov_id_type' => 'VARCHAR(50)',
        'gov_id_number' => 'VARCHAR(50)'
    ];
    foreach ($cols as $col => $type) {
        $check_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE '$col'");
        if (mysqli_num_rows($check_col) == 0) {
            mysqli_query($conn, "ALTER TABLE customers ADD $col $type");
        }
    }
    
    $insert = "INSERT INTO customers
    (user_id, customer_number, customer_type, first_name, middle_name, surname, gender, date_of_birth, nationality, email, phone, region, city, barangay, zip_code, full_address, password, emergency_contact_name, emergency_contact_number, emergency_contact_relationship, gov_id_type, gov_id_number)
	 VALUES
    ('$user_id', '$customer_number', '$customer_type', '$first_name', '$middle_name', '$surname', '$gender', '$date_of_birth', '$nationality', '$email', '$phone', '$region', '$city', '$barangay', '$zip_code', '$full_address', '$password', '$emergency_contact_name', '$emergency_contact_number', '$emergency_contact_relationship', '$gov_id_type', '$gov_id_number')";

if (mysqli_query($conn, $insert)) {
        $admin_username = $_SESSION['admin'] ?? 'admin';
        $admin_user_id = $_SESSION['user_id'] ?? null;
        logActivity($conn, $admin_user_id, $admin_username, 'Add Customer', 'Created new customer: ' . $first_name . ' ' . $surname . ' (ID: ' . $customer_number . ')', 'admin');
        
        header('Location: ../addcustomer.php');
        $_SESSION['success_msg'] = $success_msg;
    } else {
        echo "Error: " . $insert . " " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
