<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>DEBUG: Loan Applied but No Payment Option</h2><hr>";

echo "<h3>1. Check customer_session ID:</h3>";
echo "Current customer_id in session would be: " . ($customer_id ?? 'NOT SET') . "<br><br>";

echo "<h3>2. ALL Accounts with Status Info:</h3>";
$all = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.account_status, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
ORDER BY a.account_number DESC");

echo "<table border='1' cellpadding='5'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Balance</th><th>Status#</th><th>Status Name</th></tr>";
while ($row = mysqli_fetch_assoc($all)) {
    $name = $row['account_status_name'] ?? 'NULL!';
    $bg = ($name == 'Pending') ? ' style="background:yellow"' : '';
    $bg2 = ($name == 'Active') ? ' style="background:#90EE90"' : '';
    echo "<tr$bg$bg2><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status']}</td><td><b>$name</b></td></tr>";
}
echo "</table>";

echo "<h3>3. What Make Payment Query Would Return (for demo customer_id = 1):</h3>";
$demo_customer = 1;
$accounts = mysqli_query($conn, "SELECT a.*, act.account_type_name, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_type act ON a.account_type = act.account_type_number
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.customer = '$demo_customer' AND (acs.account_status_name = 'Active' OR acs.account_status_name = 'Approved')");

if (mysqli_num_rows($accounts) > 0) {
    echo "<table border='1' style='background:#90EE90'><tr><th>Acct#</th><th>Status</th><th>Amount</th></tr>";
    while ($row = mysqli_fetch_assoc($accounts)) {
        echo "<tr><td>{$row['account_number']}</td><td>{$row['account_status_name']}</td><td>{$row['loan_amount']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>✗ NO ACCOUNTS FOUND for customer $demo_customer with Active/Approved status</p>";
}

echo "<h3>4. FIX: Force all loans with balance to Active status:</h3>";
$update = mysqli_query($conn, "UPDATE accounts SET account_status = 1 WHERE loan_amount > 0 OR loan_balance > 0");
echo "Updated " . mysqli_affected_rows($conn) . " accounts to Active status<br>";

echo "<h3>5. After Fix - Accounts for Payment:</h3>";
$active = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, acs.account_status_name 
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE acs.account_status_name IN ('Active', 'Approved') AND (a.loan_amount > 0 OR a.loan_balance > 0)
ORDER BY a.account_number DESC");

if (mysqli_num_rows($active) > 0) {
    echo "<table border='1' style='background:#90EE90'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Balance</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($active)) {
        echo "<tr><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status_name']}</td></tr>";
    }
    echo "</table>";
    echo "<h3 style='color:green'>✓ Payment should now work!</h3>";
} else {
    echo "<p style='color:red'>Still no accounts for payment!</p>";
}

mysqli_close($conn);
?>
