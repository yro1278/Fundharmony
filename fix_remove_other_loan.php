<?php
require_once 'database/db_connection.php';

$result = mysqli_query($conn, "DELETE FROM account_type WHERE LOWER(account_type_name) = 'other'");

if ($result) {
    echo "Removed 'Other' loan type from database.";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
