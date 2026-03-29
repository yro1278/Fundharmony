<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>Adding user_confirmed column to accounts table</h2>";

$result = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'user_confirmed'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN user_confirmed TINYINT(1) DEFAULT 0 AFTER account_status");
    echo "Added user_confirmed column successfully!<br>";
} else {
    echo "user_confirmed column already exists.<br>";
}

echo "<h3>All Account Statuses:</h3>";
$result = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1' cellpadding='5'><tr><th>Number</th><th>Name</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>" . $row['account_status_number'] . "</td><td>" . $row['account_status_name'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Fixing accounts with old status -2 to new Active status 1:</h3>";
$fix_accounts = mysqli_query($conn, "SELECT account_number, account_status FROM accounts WHERE account_status = -2");
$count = mysqli_num_rows($fix_accounts);
if ($count > 0) {
    mysqli_query($conn, "UPDATE accounts SET account_status = 1 WHERE account_status = -2");
    echo "Fixed $count accounts (changed status from -2 to 1/Active)";
} else {
    echo "No accounts with status -2 found";
}

echo "<h2>Done!</h2>";
mysqli_close($conn);
?>
