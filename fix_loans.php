<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>Debug: Loan Application Issue</h2><hr>";

echo "<h3>Step 1: Check Pending Status Exists:</h3>";
$pending = mysqli_query($conn, "SELECT * FROM account_status WHERE account_status_name = 'Pending'");
if (mysqli_num_rows($pending) > 0) {
    $p = mysqli_fetch_assoc($pending);
    echo "✓ Pending status exists: number = " . $p['account_status_number'] . "<br>";
} else {
    echo "✗ Pending status NOT FOUND! Adding now...<br>";
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (4, 'Pending')");
    echo "Added Pending status with number 4<br>";
}

echo "<h3>Step 2: Show ALL accounts in database:</h3>";
$all = mysqli_query($conn, "SELECT * FROM accounts ORDER BY account_number DESC");
echo "<table border='1' cellpadding='5'><tr><th>Account #</th><th>Customer #</th><th>Status #</th><th>Loan Amount</th><th>Created</th></tr>";
while ($row = mysqli_fetch_assoc($all)) {
    echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['customer'] . "</td><td>" . $row['account_status'] . "</td><td>" . $row['loan_amount'] . "</td><td>" . $row['open_date'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Step 3: Check loans with customer join (like My Loans page):</h3>";
$with_join = mysqli_query($conn, "SELECT a.*, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number 
ORDER BY a.account_number DESC");

echo "<table border='1' cellpadding='5'><tr><th>Account #</th><th>Customer #</th><th>Status #</th><th>Status Name</th><th>Amount</th></tr>";
while ($row = mysqli_fetch_assoc($with_join)) {
    $name = $row['account_status_name'] ?? 'NULL - PROBLEM!';
    $bg = ($row['account_status_name'] === NULL) ? ' style="background:#ffcccc"' : '';
    echo "<tr$bg><td>" . $row['account_number'] . "</td><td>" . $row['customer'] . "</td><td>" . $row['account_status'] . "</td><td>$name</td><td>" . $row['loan_amount'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Step 4: Fix accounts with NULL status name:</h3>";
$null_status = mysqli_query($conn, "SELECT DISTINCT account_status FROM accounts WHERE account_status NOT IN (SELECT account_status_number FROM account_status)");
if (mysqli_num_rows($null_status) > 0) {
    while ($row = mysqli_fetch_assoc($null_status)) {
        $bad_status = $row['account_status'];
        echo "Found accounts with status $bad_status that doesn't exist in account_status table<br>";
        mysqli_query($conn, "UPDATE accounts SET account_status = 4 WHERE account_status = $bad_status");
        echo "✓ Updated those accounts to Pending (4)<br>";
    }
} else {
    echo "✓ No accounts with invalid status<br>";
}

echo "<h3>Step 5: After fix - show all loans:</h3>";
$fixed = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number 
ORDER BY a.account_number DESC");

echo "<table border='1' cellpadding='5' style='background:#ccffcc'><tr><th>Account #</th><th>Customer #</th><th>Amount</th><th>Status</th></tr>";
while ($row = mysqli_fetch_assoc($fixed)) {
    echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['customer'] . "</td><td>" . $row['loan_amount'] . "</td><td>" . ($row['account_status_name'] ?? 'NULL') . "</td></tr>";
}
echo "</table>";

mysqli_close($conn);
echo "<h2 style='color:green'>Done! Loans should now appear in My Loans.</h2>";
?>
