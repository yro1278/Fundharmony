<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>Fixing User Payment Issue</h2><hr>";

echo "<h3>Current Account Status Table:</h3>";
$statuses = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1' cellpadding='5'><tr><th>Number</th><th>Name</th></tr>";
while ($row = mysqli_fetch_assoc($statuses)) {
    echo "<tr><td>" . $row['account_status_number'] . "</td><td>" . $row['account_status_name'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Accounts and their current status:</h3>";
$accounts = mysqli_query($conn, "SELECT a.account_number, a.account_status, a.customer, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
ORDER BY a.account_number DESC LIMIT 20");

echo "<table border='1' cellpadding='5'><tr><th>Account #</th><th>Status Number</th><th>Status Name</th><th>Customer #</th></tr>";
while ($row = mysqli_fetch_assoc($accounts)) {
    $status_name = $row['account_status_name'] ?? 'NULL/MISSING';
    echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['account_status'] . "</td><td>" . $status_name . "</td><td>" . $row['customer'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Fixing accounts with wrong status:</h3>";

$fix_statuses = [
    ['old' => -2, 'new' => 1, 'name' => 'Active'],
    ['old' => -1, 'new' => -1, 'name' => 'Declined'],
    ['old' => 0, 'new' => 4, 'name' => 'Pending'],
    ['old' => 1, 'new' => 1, 'name' => 'Active (already correct)'],
    ['old' => 2, 'new' => 2, 'name' => 'Approved (already correct)'],
];

foreach ($fix_statuses as $fix) {
    if ($fix['old'] != $fix['new']) {
        $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts WHERE account_status = " . $fix['old']);
        $cnt = mysqli_fetch_assoc($check)['cnt'];
        if ($cnt > 0) {
            mysqli_query($conn, "UPDATE accounts SET account_status = " . $fix['new'] . " WHERE account_status = " . $fix['old']);
            echo "✓ Fixed $cnt accounts: status " . $fix['old'] . " → " . $fix['new'] . " (" . $fix['name'] . ")<br>";
        }
    }
}

echo "<h3>Updated Accounts:</h3>";
$accounts = mysqli_query($conn, "SELECT a.account_number, a.account_status, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
ORDER BY a.account_number DESC LIMIT 20");

echo "<table border='1' cellpadding='5'><tr><th>Account #</th><th>Status Number</th><th>Status Name</th></tr>";
while ($row = mysqli_fetch_assoc($accounts)) {
    $status_name = $row['account_status_name'] ?? 'NULL/MISSING';
    echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['account_status'] . "</td><td>" . $status_name . "</td></tr>";
}
echo "</table>";

echo "<h3>Accounts eligible for payment (Active or Approved):</h3>";
$eligible = mysqli_query($conn, "SELECT a.account_number, a.loan_amount, a.loan_balance, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE acs.account_status_name IN ('Active', 'Approved')
ORDER BY a.account_number DESC");

if (mysqli_num_rows($eligible) > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>Account #</th><th>Loan Amount</th><th>Balance</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($eligible)) {
        echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['loan_amount'] . "</td><td>" . $row['loan_balance'] . "</td><td>" . $row['account_status_name'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>No accounts with Active or Approved status found!</p>";
}

mysqli_close($conn);
echo "<h2 style='color:green'>Done! Try making a payment now.</h2>";
?>
