<?php
include_once 'database/db_connection.php';

// Keep only these loan types
$keep_types = ['Educational Loan', 'Business Loan', 'Emergency Loan', 'Personal Loan'];

// Get all current loan types
$all_types = mysqli_query($conn, "SELECT account_type_number, account_type_name FROM account_type");

echo "<h3>Current Loan Types:</h3>";
while ($type = mysqli_fetch_assoc($all_types)) {
    echo "- " . $type['account_type_name'] . " (ID: " . $type['account_type_number'] . ")<br>";
}

// Delete loan types that are not in the keep list
foreach ($keep_types as $type) {
    $check = mysqli_query($conn, "SELECT account_type_number FROM account_type WHERE account_type_name = '$type'");
    if (mysqli_num_rows($check) == 0) {
        // Insert missing types
        $type_number = strtolower(str_replace(' ', '_', $type));
        $type_number = preg_replace('/[^a-z0-9_]/', '', $type_number);
        $type_number = substr($type_number, 0, 20);
        
        $insert = "INSERT INTO account_type (account_type_number, account_type_name, registration_date) 
                   VALUES ('$type_number', '$type', NOW())";
        if (mysqli_query($conn, $insert)) {
            echo "<br>Added: $type<br>";
        } else {
            echo "<br>Error adding $type: " . mysqli_error($conn) . "<br>";
        }
    }
}

// Now delete any types that are NOT in the keep list
$all_types = mysqli_query($conn, "SELECT account_type_number, account_type_name FROM account_type");
while ($type = mysqli_fetch_assoc($all_types)) {
    if (!in_array($type['account_type_name'], $keep_types)) {
        $delete = "DELETE FROM account_type WHERE account_type_number = '" . $type['account_type_number'] . "'";
        if (mysqli_query($conn, $delete)) {
            echo "<br>Deleted: " . $type['account_type_name'] . "<br>";
        }
    }
}

echo "<h3>Done! Loan types cleaned up.</h3>";
echo "<a href='manageaccount_type.php'>Go back to Manage Account Types</a>";
?>
