<?php
session_start();
require_once '../database/db_connection.php';

$fullname = $_POST['fullname'];
$email = $_POST['email'];
$contact = $_POST['contact'];
$username = $_POST['username'];
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
    header('location: ../register.php');
    exit();
}

$password_errors = validatePassword($password);
if (!empty($password_errors)) {
    $_SESSION['register_error'] = implode(". ", $password_errors) . ".";
    header('location: ../register.php');
    exit();
}

$username = stripslashes(mysqli_real_escape_string($conn, $username));
$email = stripslashes(mysqli_real_escape_string($conn, $email));
$password = stripslashes(mysqli_real_escape_string($conn, $password));

$check_user = "SELECT * FROM users WHERE username = '$username'";
$result_check = mysqli_query($conn, $check_user);
$count_check = mysqli_num_rows($result_check);

if ($count_check > 0) {
    $_SESSION['register_error'] = "Username already exists";
    header('location: ../register.php');
    exit();
}

$user_number = rand(10000, 99999);
$check_user_number = "SELECT user_number FROM users WHERE user_number = '$user_number'";
while (mysqli_num_rows(mysqli_query($conn, $check_user_number)) > 0) {
    $user_number = rand(10000, 99999);
    $check_user_number = "SELECT user_number FROM users WHERE user_number = '$user_number'";
}

$check_fullname = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'fullname'");
$check_contact = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'contact_number'");

$columns = "user_number, username, password, email";
$values = "'$user_number', '$username', '$password', '$email'";

if (mysqli_num_rows($check_fullname) > 0) {
    $columns .= ", fullname";
    $values .= ", '$fullname'";
}
if (mysqli_num_rows($check_contact) > 0) {
    $columns .= ", contact_number";
    $values .= ", '$contact'";
}

$check_role = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
$insert = "";
if (mysqli_num_rows($check_role) > 0) {
    $columns .= ", role";
    $values .= ", 'admin'";
    $insert = "INSERT INTO users ($columns) VALUES ($values)";
} else {
    $insert = "INSERT INTO users ($columns) VALUES ($values)";
}

$result = mysqli_query($conn, $insert);
$error = mysqli_error($conn);
$errno = mysqli_errno($conn);
$affected = mysqli_affected_rows($conn);

$log_file = dirname(__FILE__) . '/../register_debug.log';
file_put_contents($log_file, 
    date('Y-m-d H:i:s') . " | user_number: $user_number | username: $username | email: $email\n" .
    "Query: $insert\n" .
    "Error: $error | Errno: $errno | Affected: $affected\n" .
    "Result: " . ($result ? 'true' : 'false') . "\n\n", 
    FILE_APPEND);

if ($errno > 0) {
    $_SESSION['register_error'] = "Database error $errno: $error";
    header('location: ../register.php');
    exit();
}

if (!$result || $affected < 1) {
    $_SESSION['register_error'] = "Registration failed: Query executed but affected $affected rows. The user may already exist or there may be a duplicate key issue.";
    header('location: ../register.php');
    exit();
}

if (function_exists('logActivity')) {
    logActivity($conn, $user_number, $username, 'Admin Registration', 'New admin account registered: ' . $username, 'admin');
}
setcookie('remember_username', '', time() - 3600, '/');
setcookie('remember_password', '', time() - 3600, '/');
$_SESSION['register_success'] = "Registration successful! Please login.";
header('location: ../login.php');
