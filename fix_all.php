<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>COMPLETE FIX for Payment Issue</h2><hr>";

echo "<h3>Step 1: Ensure ALL statuses exist:</h3>";
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
    $check = mysqli_query($conn, "SELECT * FROM account_status WHERE account_status_number = $s[0] AND account_status_name = '$s[1]'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES ($s[0], '$s[1]')");
        echo "Added: $s[1] ($s[0])<br>";
    }
}

echo "<h3>Step 2: Fix ALL accounts to have proper status:</h3>";

$update_fixes = [
    // Fix NULL or 0 to 4 (Pending)
    "UPDATE accounts SET account_status = 4 WHERE account_status IS NULL OR account_status = 0",
    // Fix -2 to 1 (Active)
    "UPDATE accounts SET account_status = 1 WHERE account_status = -2",
    // Fix -1 to -1 (Declined - correct)
    // Fix 1 to 1 (Active - correct)
    // Fix 2 to 2 (Approved - correct) 
    // Fix 3 to 3 (Rejected - correct)
    // Fix 4 to 4 (Pending - correct)
    // Fix 5 to 5 (Partial - correct)
    // Fix 6 to 6 (Due Date - correct)
    // Fix 7 to 7 (Up to Date - correct)
    // Fix -3 to -3 (Closed - correct)
    // Fix -4 to 5 (Partial)
    "UPDATE accounts SET account_status = 5 WHERE account_status = -4",
    // Fix -5 to 7 (Up to Date)
    "UPDATE accounts SET account_status = 7 WHERE account_status = -5",
    // Fix -6 to 6 (Due Date)
    "UPDATE accounts SET account_status = 6 WHERE account_status = -6",
];

foreach ($update_fixes as $sql) {
    mysqli_query($conn, $sql);
    $affected = mysqli_affected_rows($conn);
    if ($affected > 0) {
        echo "Executed: $sql (affected: $affected)<br>";
    }
}

echo "<h3>Step 3: Show all accounts after fix:</h3>";
$all = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.account_status, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
ORDER BY a.account_number DESC");

echo "<table border='1' cellpadding='5'><tr><th>Account #</th><th>Cust #</th><th>Amount</th><th>Balance</th><th>Status #</th><th>Status Name</th></tr>";
while ($row = mysqli_fetch_assoc($all)) {
    $name = $row['account_status_name'] ?? 'NULL';
    $bg = ($name == 'Active') ? ' bgcolor="#90EE90"' : '';
    echo "<tr$bg><td>" . $row['account_number'] . "</td><td>" . $row['customer'] . "</td><td>" . $row['loan_amount'] . "</td><td>" . $row['loan_balance'] . "</td><td>" . $row['account_status'] . "</td><td><b>$name</b></td></tr>";
}
echo "</table>";

echo "<h3>Step 4: Accounts ELIGIBLE for payment (Active OR Approved with balance > 0):</h3>";
$eligible = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE (acs.account_status_name = 'Active' OR acs.account_status_name = 'Approved')
AND (a.loan_balance > 0 OR a.loan_amount > 0)
ORDER BY a.account_number DESC");

if (mysqli_num_rows($eligible) > 0) {
    echo "<table border='1' cellpadding='5' style='background:#90EE90'><tr><th>Account #</th><th>Customer #</th><th>Amount</th><th>Balance</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($eligible)) {
        echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['customer'] . "</td><td>" . $row['loan_amount'] . "</td><td>" . $row['loan_balance'] . "</td><td>" . $row['account_status_name'] . "</td></tr>";
    }
    echo "</table>";
    echo "<h3 style='color:green'>✓ Payment should now work!</h3>";
} else {
    echo "<p style='color:red'>No eligible accounts found for payment.</p>";
}

mysqli_close($conn);
?>
