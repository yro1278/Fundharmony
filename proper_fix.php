<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>PROPER FIX - Handle Foreign Key</h2><hr>";

echo "<h3>1. Current Account Status Table:</h3>";
$statuses = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1' cellpadding='5'><tr><th>Number</th><th>Name</th></tr>";
while ($row = mysqli_fetch_assoc($statuses)) {
    echo "<tr><td>{$row['account_status_number']}</td><td>{$row['account_status_name']}</td></tr>";
}
echo "</table>";

echo "<h3>2. ALL Accounts Before Fix:</h3>";
$all = mysqli_query($conn, "SELECT account_number, customer, loan_amount, account_status FROM accounts ORDER BY account_number DESC");
echo "<table border='1' cellpadding='5'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Status#</th></tr>";
while ($row = mysqli_fetch_assoc($all)) {
    echo "<tr><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['account_status']}</td></tr>";
}
echo "</table>";

echo "<h3>3. Adding Missing Statuses (with IDs 1-7):</h3>";
$needed = [
    [1, 'Active'],
    [2, 'Approved'],
    [3, 'Rejected'],
    [4, 'Pending'],
    [5, 'Partial'],
    [6, 'Due Date'],
    [7, 'Up to Date'],
    [-1, 'Declined'],
    [-3, 'Closed'],
];

foreach ($needed as $s) {
    $check = mysqli_query($conn, "SELECT * FROM account_status WHERE account_status_number = {$s[0]}");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES ({$s[0]}, '{$s[1]}')");
        echo "✓ Added: {$s[1]} ({$s[0]})<br>";
    }
}

echo "<h3>4. Accounts After Adding Statuses:</h3>";
$all2 = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.account_status, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
ORDER BY a.account_number DESC");
echo "<table border='1' cellpadding='5'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Status#</th><th>Status Name</th></tr>";
while ($row = mysqli_fetch_assoc($all2)) {
    $name = $row['account_status_name'] ?? 'NULL!';
    $bg = ($name == 'Active' || $name == 'Up to Date') ? ' style="background:#90EE90"' : '';
    echo "<tr$bg><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['account_status']}</td><td><b>$name</b></td></tr>";
}
echo "</table>";

echo "<h3>5. Ready for Payment (Your customer_id = 417803011):</h3>";
$ready = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.customer = '417803011' AND (
    acs.account_status_name = 'Active' OR 
    acs.account_status_name = 'Approved' OR
    acs.account_status_name = 'Up to Date' OR
    acs.account_status_name = 'Due Date' OR
    acs.account_status_name = 'Partial'
) AND (a.loan_balance > 0 OR a.loan_amount > 0)");

if (mysqli_num_rows($ready) > 0) {
    echo "<table border='1' style='background:#90EE90'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Balance</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($ready)) {
        echo "<tr><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status_name']}</td></tr>";
    }
    echo "</table>";
    echo "<h3 style='color:green'>✓ DONE! Try Make Payment now!</h3>";
} else {
    echo "<p>No accounts for payment - possibly all loans are fully paid (balance = 0)</p>";
}

mysqli_close($conn);
?>
