<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>Fixing All Database Issues</h2><hr>";

echo "<h3>1. Adding Account Statuses:</h3>";
$statuses = [
    ['name' => 'Active', 'number' => 1],
    ['name' => 'Approved', 'number' => 2],
    ['name' => 'Declined', 'number' => -1],
    ['name' => 'Pending', 'number' => 4],
    ['name' => 'Partial', 'number' => 5],
    ['name' => 'Due Date', 'number' => 6],
    ['name' => 'Up to Date', 'number' => 7],
    ['name' => 'Closed', 'number' => -3],
];

foreach ($statuses as $status) {
    $check = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = '" . $status['name'] . "'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (" . $status['number'] . ", '" . $status['name'] . "')");
        echo "✓ Added: " . $status['name'] . "<br>";
    } else {
        echo "✓ Already exists: " . $status['name'] . "<br>";
    }
}

echo "<h3>2. Adding user_confirmed column:</h3>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'user_confirmed'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN user_confirmed TINYINT(1) DEFAULT 0 AFTER account_status");
    echo "✓ Added user_confirmed column<br>";
} else {
    echo "✓ user_confirmed column already exists<br>";
}

echo "<h3>3. Fixing old status numbers (-2 to 1):</h3>";
$fix_accounts = mysqli_query($conn, "SELECT account_number, account_status FROM accounts WHERE account_status = -2");
$count = mysqli_num_rows($fix_accounts);
if ($count > 0) {
    mysqli_query($conn, "UPDATE accounts SET account_status = 1 WHERE account_status = -2");
    echo "✓ Fixed $count accounts<br>";
} else {
    echo "✓ No accounts need fixing<br>";
}

echo "<h3>Current Account Statuses:</h3>";
$result = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1' cellpadding='5'><tr><th>Number</th><th>Name</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>" . $row['account_status_number'] . "</td><td>" . $row['account_status_name'] . "</td></tr>";
}
echo "</table>";

mysqli_close($conn);
echo "<h2 style='color:green'>All fixes applied successfully!</h2>";
?>
