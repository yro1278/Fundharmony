<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>COMPLETE FIX</h2><hr>";

echo "<h3>1. Account Status Table:</h3>";
$statuses = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1'><tr><th>#</th><th>Name</th></tr>";
while ($row = mysqli_fetch_assoc($statuses)) {
    echo "<tr><td>{$row['account_status_number']}</td><td>{$row['account_status_name']}</td></tr>";
}
echo "</table>";

echo "<h3>2. Your Accounts (customer 417803011):</h3>";
$my_accounts = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.account_status, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.customer = '417803011'
ORDER BY a.account_number DESC");

echo "<table border='1'><tr><th>Acct#</th><th>Amount</th><th>Balance</th><th>Status#</th><th>Status</th></tr>";
while ($row = mysqli_fetch_assoc($my_accounts)) {
    $name = $row['account_status_name'] ?? 'NULL';
    $bg = '';
    if ($name == 'Active' || $name == 'Up to Date') $bg = ' style="background:#90EE90"';
    echo "<tr$bg><td>{$row['account_number']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status']}</td><td>$name</td></tr>";
}
echo "</table>";

echo "<h3>3. What Make Payment sees (with balance > 0):</h3>";
$accounts = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.customer = '417803011' AND (
    acs.account_status_name = 'Active' OR 
    acs.account_status_name = 'Approved' OR
    acs.account_status_name = 'Up to Date' OR
    acs.account_status_name = 'Due Date' OR
    acs.account_status_name = 'Partial'
) AND (a.loan_balance > 0 OR a.loan_amount > 0)");

if (mysqli_num_rows($accounts) > 0) {
    echo "<table border='1' style='background:#90EE90'><tr><th>Acct#</th><th>Amount</th><th>Balance</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($accounts)) {
        echo "<tr><td>{$row['account_number']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status_name']}</td></tr>";
    }
    echo "</table>";
    echo "<h3 style='color:green'>✓ Should work now!</h3>";
} else {
    echo "<p style='color:red'>All loans appear to have 0 balance (fully paid)</p>";
    echo "<h3>Check if balance is 0:</h3>";
    $zero = mysqli_query($conn, "SELECT loan_balance FROM accounts WHERE customer = '417803011'");
    while ($z = mysqli_fetch_assoc($zero)) {
        echo "Balance: " . $z['loan_balance'] . "<br>";
    }
}

mysqli_close($conn);
?>
