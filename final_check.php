<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>FINAL FIX</h2><hr>";

echo "<h3>1. Your Accounts:</h3>";
$my = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.account_status, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.customer = '417803011'");

echo "<table border='1' cellpadding='5'><tr><th>Acct#</th><th>Amount</th><th>Balance</th><th>Status#</th><th>Status Name</th></tr>";
while ($row = mysqli_fetch_assoc($my)) {
    $bg = ($row['account_status_name'] == 'Active' || $row['account_status_name'] == 'Up to Date') ? ' style="background:#90EE90"' : '';
    echo "<tr$bg><td>{$row['account_number']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status']}</td><td>{$row['account_status_name']}</td></tr>";
}
echo "</table>";

echo "<h3>2. Status Table:</h3>";
$st = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1' cellpadding='5'><tr><th>Number</th><th>Name</th></tr>";
while ($s = mysqli_fetch_assoc($st)) {
    echo "<tr><td>{$s['account_status_number']}</td><td>{$s['account_status_name']}</td></tr>";
}
echo "</table>";

echo "<h3>3. Accounts with balance > 0 and proper status:</h3>";
$ready = mysqli_query($conn, "SELECT a.account_number, a.loan_amount, a.loan_balance, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.customer = '417803011' AND (
    acs.account_status_name IN ('Active','Approved','Up to Date','Due Date','Partial')
) AND (a.loan_balance > 0 OR a.loan_amount > 0)");

if (mysqli_num_rows($ready) > 0) {
    echo "<table border='1' style='background:#90EE90'><tr><th>Acct#</th><th>Amount</th><th>Balance</th><th>Status</th></tr>";
    while ($r = mysqli_fetch_assoc($ready)) {
        echo "<tr><td>{$r['account_number']}</td><td>{$r['loan_amount']}</td><td>{$r['loan_balance']}</td><td>{$r['account_status_name']}</td></tr>";
    }
    echo "</table>";
    echo "<h3 style='color:green'>✓ Try Make Payment now!</h3>";
} else {
    echo "<p style='color:red'>No accounts with balance > 0 found</p>";
}

mysqli_close($conn);
?>
