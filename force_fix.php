<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>FIX: Approved Loan Not Showing in Payment</h2><hr>";

echo "<h3>1. Check current statuses in database:</h3>";
$statuses = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1' cellpadding='5'><tr><th>Number</th><th>Name</th></tr>";
while ($row = mysqli_fetch_assoc($statuses)) {
    echo "<tr><td>{$row['account_status_number']}</td><td>{$row['account_status_name']}</td></tr>";
}
echo "</table>";

echo "<h3>2. ALL Accounts:</h3>";
$all = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.account_status, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
ORDER BY a.account_number DESC");

echo "<table border='1' cellpadding='5'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Status#</th><th>Status Name</th></tr>";
while ($row = mysqli_fetch_assoc($all)) {
    $name = $row['account_status_name'] ?? 'NULL - FIX NEEDED!';
    $bg = '';
    if ($name == 'Active' || $name == 'Approved') $bg = ' style="background:#90EE90"';
    echo "<tr$bg><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['account_status']}</td><td><b>$name</b></td></tr>";
}
echo "</table>";

echo "<h3>3. FIXING ALL ISSUES NOW:</h3>";

// Add all statuses
$statuses_to_add = [
    [1, 'Active'],
    [2, 'Approved'],
    [-1, 'Declined'],
    [4, 'Pending'],
    [3, 'Rejected'],
    [5, 'Partial'],
    [6, 'Due Date'],
    [7, 'Up to Date'],
    [-3, 'Closed'],
];

foreach ($statuses_to_add as $s) {
    $check = mysqli_query($conn, "SELECT * FROM account_status WHERE account_status_number = {$s[0]}");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES ({$s[0]}, '{$s[1]}')");
        echo "Added: {$s[1]}<br>";
    }
}

// Fix accounts with bad status numbers
$fixes = [
    ['old' => -2, 'new' => 1, 'name' => 'Active'],
    ['old' => 0, 'new' => 1, 'name' => 'Active'],
    ['old' => NULL, 'new' => 1, 'name' => 'Active'],
];

foreach ($fixes as $fix) {
    if ($fix['old'] === NULL) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts WHERE account_status IS NULL OR account_status = 0");
    } else {
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts WHERE account_status = {$fix['old']}");
    }
    $cnt = mysqli_fetch_assoc($result)['cnt'];
    if ($cnt > 0) {
        if ($fix['old'] === NULL) {
            mysqli_query($conn, "UPDATE accounts SET account_status = {$fix['new']} WHERE account_status IS NULL OR account_status = 0");
        } else {
            mysqli_query($conn, "UPDATE accounts SET account_status = {$fix['new']} WHERE account_status = {$fix['old']}");
        }
        echo "✓ Fixed $cnt accounts: " . ($fix['old'] ?? 'NULL') . " → {$fix['new']} ({$fix['name']})<br>";
    }
}

echo "<h3>4. FINAL RESULT - Accounts ready for payment:</h3>";
$ready = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE (acs.account_status_name = 'Active' OR acs.account_status_name = 'Approved')
AND (a.loan_amount > 0 OR a.loan_balance > 0)
ORDER BY a.account_number DESC");

if (mysqli_num_rows($ready) > 0) {
    echo "<table border='1' style='background:#90EE90'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Balance</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($ready)) {
        echo "<tr><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status_name']}</td></tr>";
    }
    echo "</table>";
    echo "<h3 style='color:green'>✓ DONE! Try Make Payment now!</h3>";
} else {
    echo "<p style='color:red'>Still no accounts for payment</p>";
}

mysqli_close($conn);
?>
