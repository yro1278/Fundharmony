<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>COMPLETE Debug: Loan Application</h2><hr>";

echo "<h3>Step 1: Check Account Status Table:</h3>";
$statuses = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_number");
echo "<table border='1' cellpadding='5'><tr><th>Number</th><th>Name</th></tr>";
while ($row = mysqli_fetch_assoc($statuses)) {
    echo "<tr><td>" . $row['account_status_number'] . "</td><td>" . $row['account_status_name'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Step 2: ALL Accounts (raw data):</h3>";
$all = mysqli_query($conn, "SELECT * FROM accounts ORDER BY account_number DESC");
echo "<table border='1' cellpadding='4'><tr><th>Acct#</th><th>Cust#</th><th>Status#</th><th>Amount</th><th>Type#</th></tr>";
while ($row = mysqli_fetch_assoc($all)) {
    echo "<tr><td>" . $row['account_number'] . "</td><td>" . $row['customer'] . "</td><td>" . $row['account_status'] . "</td><td>" . $row['loan_amount'] . "</td><td>" . $row['account_type'] . "</td></tr>";
}
echo "</table>";

echo "<h3>Step 3: Check account_type table (for loan types):</h3>";
$types = mysqli_query($conn, "SELECT * FROM account_type");
if (mysqli_num_rows($types) == 0) {
    echo "✗ NO LOAN TYPES! Adding default types...<br>";
    $loan_types = ['Educational Loan', 'Business Loan', 'Emergency Loan', 'Personal Loan'];
    foreach ($loan_types as $type) {
        $type_number = rand(100000000, 999999999);
        mysqli_query($conn, "INSERT INTO account_type (account_type_number, account_type_name) VALUES ('$type_number', '$type')");
        echo "Added: $type<br>";
    }
} else {
    echo "✓ Loan types exist:<br>";
    while ($row = mysqli_fetch_assoc($types)) {
        echo "- " . $row['account_type_name'] . " (ID: " . $row['account_type_number'] . ")<br>";
    }
}

echo "<h3>Step 4: Fix ALL issues at once:</h3>";

// Fix statuses
$fix_statuses = [
    ['name' => 'Active', 'num' => 1],
    ['name' => 'Approved', 'num' => 2],
    ['name' => 'Declined', 'num' => -1],
    ['name' => 'Pending', 'num' => 4],
    ['name' => 'Rejected', 'num' => 3],
    ['name' => 'Partial', 'num' => 5],
    ['name' => 'Due Date', 'num' => 6],
    ['name' => 'Up to Date', 'num' => 7],
    ['name' => 'Closed', 'num' => -3],
];

foreach ($fix_statuses as $s) {
    $check = mysqli_query($conn, "SELECT * FROM account_status WHERE account_status_name = '{$s['name']}'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES ({$s['num']}, '{$s['name']}')");
        echo "✓ Added status: {$s['name']}<br>";
    }
}

// Fix loan types
$loan_types = ['Educational Loan', 'Business Loan', 'Emergency Loan', 'Personal Loan'];
foreach ($loan_types as $type) {
    $check = mysqli_query($conn, "SELECT * FROM account_type WHERE account_type_name = '$type'");
    if (mysqli_num_rows($check) == 0) {
        $type_number = rand(100000000, 999999999);
        mysqli_query($conn, "INSERT INTO account_type (account_type_number, account_type_name) VALUES ('$type_number', '$type')");
        echo "✓ Added loan type: $type<br>";
    }
}

// Fix accounts with invalid status
$bad_statuses = mysqli_query($conn, "SELECT DISTINCT account_status FROM accounts WHERE account_status NOT IN (SELECT account_status_number FROM account_status)");
while ($row = mysqli_fetch_assoc($bad_statuses)) {
    $bad = $row['account_status'];
    mysqli_query($conn, "UPDATE accounts SET account_status = 4 WHERE account_status = $bad");
    echo "✓ Fixed accounts with invalid status $bad → 4 (Pending)<br>";
}

// Fix accounts with invalid type
$bad_types = mysqli_query($conn, "SELECT DISTINCT account_type FROM accounts WHERE account_type NOT IN (SELECT account_type_number FROM account_type)");
while ($row = mysqli_fetch_assoc($bad_types)) {
    $bad = $row['account_type'];
    $first_type = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_type_number FROM account_type LIMIT 1"));
    if ($first_type) {
        $new_type = $first_type['account_type_number'];
        mysqli_query($conn, "UPDATE accounts SET account_type = '$new_type' WHERE account_type = '$bad'");
        echo "✓ Fixed accounts with invalid type $bad → $new_type<br>";
    }
}

echo "<h3>Step 5: Final Account List (with proper joins):</h3>";
$final = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_term, 
ac.account_type_name, acs.account_status_name
FROM accounts a 
LEFT JOIN account_type ac ON a.account_type = ac.account_type_number
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
ORDER BY a.account_number DESC");

echo "<table border='1' cellpadding='5' style='background:#e0ffe0'><tr><th>Acct#</th><th>Cust#</th><th>Type</th><th>Status</th><th>Amount</th><th>Term</th></tr>";
while ($row = mysqli_fetch_assoc($final)) {
    $type = $row['account_type_name'] ?? 'NULL!';
    $status = $row['account_status_name'] ?? 'NULL!';
    echo "<tr><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>$type</td><td>$status</td><td>{$row['loan_amount']}</td><td>{$row['loan_term']}</td></tr>";
}
echo "</table>";

mysqli_close($conn);
echo "<h2 style='color:green'>All fixes applied!</h2>";
?>
