<?php
require_once 'database/db_connection.php';

$check_closed = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Closed'");
if (mysqli_num_rows($check_closed) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (-3, 'Closed')");
    echo "Added 'Closed' status to account_status table.<br>";
} else {
    echo "'Closed' status already exists.<br>";
}

mysqli_query($conn, "UPDATE accounts SET account_status = -3 WHERE loan_balance <= 0");
$affected = mysqli_affected_rows($conn);
echo "Updated $affected fully paid loans to Closed status.";

echo "<br><br><a href='loan_approvals.php'>Go to Loan Approvals</a>";
