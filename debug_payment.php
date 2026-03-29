<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>Debugging Payment Issue</h2><hr>";

echo "<h3>Step 1: Check Account Status Table:</h3>";
$statuses = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1' cellpadding='5'><tr><th>Number</th><th>Name</th></tr>";
while ($row = mysqli_fetch_assoc($statuses)) {
    echo "<tr><td>" . $row['account_status_number'] . "</td><td>" . $row['account_status_name'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Step 2: Check ALL Accounts (no filter):</h3>";
$accounts = mysqli_query($conn, "SELECT a.account_number, a.account_status, a.customer, a.loan_amount, a.loan_balance, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
ORDER BY a.account_number DESC");

echo "<table border='1' cellpadding='5'><tr><th>Account #</th><th>Status #</th><th>Status Name</th><th>Customer #</th><th>Amount</th><th>Balance</th></tr>";
while ($row = mysqli_fetch_assoc($accounts)) {
    $status = $row['account_status_name'] ?? 'NULL';
    echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['account_status'] . "</td><td><b>$status</b></td><td>" . $row['customer'] . "</td><td>" . $row['loan_amount'] . "</td><td>" . $row['loan_balance'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Step 3: Fix ALL Account Statuses:</h3>";

$fixes = [
    ['old' => -2, 'new' => 1],
    ['old' => 0, 'new' => 4],
    ['old' => NULL, 'new' => 4, 'name' => 'NULL to Pending'],
];

foreach ($fixes as $fix) {
    if (isset($fix['old']) && $fix['old'] === NULL) {
        $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts WHERE account_status IS NULL OR account_status = 0");
    } else {
        $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts WHERE account_status = " . $fix['old']);
    }
    $cnt = mysqli_fetch_assoc($check)['cnt'];
    if ($cnt > 0) {
        if (isset($fix['old']) && $fix['old'] === NULL) {
            mysqli_query($conn, "UPDATE accounts SET account_status = " . $fix['new'] . " WHERE account_status IS NULL OR account_status = 0");
        } else {
            mysqli_query($conn, "UPDATE accounts SET account_status = " . $fix['new'] . " WHERE account_status = " . $fix['old']);
        }
        echo "✓ Fixed $cnt accounts: " . ($fix['name'] ?? "status " . $fix['old'] . " → " . $fix['new']) . "<br>";
    }
}

echo "<h3>Step 4: Set ALL accounts to Active (status=1) if they have loan balance > 0:</h3>";
$all_accounts = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts WHERE loan_balance > 0 OR loan_amount > 0");
$cnt = mysqli_fetch_assoc($all_accounts)['cnt'];
if ($cnt > 0) {
    mysqli_query($conn, "UPDATE accounts SET account_status = 1 WHERE (loan_balance > 0 OR loan_amount > 0) AND account_status != 1");
    echo "✓ Set $cnt accounts to Active status<br>";
}

echo "<h3>Step 5: After Fix - Accounts with Active/Approved:</h3>";
$active = mysqli_query($conn, "SELECT a.account_number, a.loan_amount, a.loan_balance, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE acs.account_status_name IN ('Active', 'Approved') AND (a.loan_balance > 0 OR a.loan_amount > 0)
ORDER BY a.account_number DESC");

if (mysqli_num_rows($active) > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>Account #</th><th>Status</th><th>Amount</th><th>Balance</th></tr>";
    while ($row = mysqli_fetch_assoc($active)) {
        echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['account_status_name'] . "</td><td>" . $row['loan_amount'] . "</td><td>" . $row['loan_balance'] . "</td></tr>";
    }
    echo "</table>";
    echo "<h3 style='color:green'>✓ Payment should work now!</h3>";
} else {
    echo "<p style='color:red'>No active accounts for payment found!</p>";
}

mysqli_close($conn);
?>
