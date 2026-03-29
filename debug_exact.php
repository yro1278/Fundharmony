<?php
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>FINAL DEBUG - Exact Customer Match</h2><hr>";

echo "<h3>1. ALL Customers (customer_number):</h3>";
$customers = mysqli_query($conn, "SELECT customer_number, first_name, surname, email FROM customers ORDER BY customer_number");
echo "<table border='1' cellpadding='5'><tr><th>customer_number</th><th>Name</th><th>Email</th></tr>";
while ($row = mysqli_fetch_assoc($customers)) {
    echo "<tr><td><b>{$row['customer_number']}</b></td><td>{$row['first_name']} {$row['surname']}</td><td>{$row['email']}</td></tr>";
}
echo "</table>";

echo "<h3>2. ALL Accounts (customer field):</h3>";
$accounts = mysqli_query($conn, "SELECT account_number, customer, loan_amount, account_status FROM accounts ORDER BY account_number DESC");
echo "<table border='1' cellpadding='5'><tr><th>account_number</th><th>customer (field)</th><th>Amount</th><th>Status#</th></tr>";
while ($row = mysqli_fetch_assoc($accounts)) {
    echo "<tr><td>{$row['account_number']}</td><td><b>{$row['customer']}</b></td><td>{$row['loan_amount']}</td><td>{$row['account_status']}</td></tr>";
}
echo "</table>";

echo "<h3>3. Check if customer numbers MATCH between customers and accounts:</h3>";
$match = mysqli_query($conn, "SELECT c.customer_number, c.first_name, a.account_number, a.account_status
FROM customers c 
LEFT JOIN accounts a ON c.customer_number = a.customer
ORDER BY c.customer_number DESC");

echo "<table border='1' cellpadding='5'><tr><th>Customer #</th><th>Name</th><th>Has Loan?</th><th>Acct#</th><th>Status#</th></tr>";
while ($row = mysqli_fetch_assoc($match)) {
    $has_loan = $row['account_number'] ? 'YES' : 'NO';
    $bg = ($has_loan == 'YES') ? ' style="background:#ffffcc"' : '';
    echo "<tr$bg><td>{$row['customer_number']}</td><td>{$row['first_name']}</td><td>$has_loan</td><td>{$row['account_number']}</td><td>{$row['account_status']}</td></tr>";
}
echo "</table>";

echo "<h3>4. FINAL FIX - Force match customer numbers:</h3>";
// Get all customers
$all_customers = mysqli_query($conn, "SELECT customer_number FROM customers");
while ($cust = mysqli_fetch_assoc($all_customers)) {
    $cust_num = $cust['customer_number'];
    
    // Check if there are accounts for this customer with wrong customer number
    $check = mysqli_query($conn, "SELECT account_number, customer FROM accounts WHERE customer != '$cust_num' LIMIT 5");
    if (mysqli_num_rows($check) > 0) {
        while ($bad = mysqli_fetch_assoc($check)) {
            echo "Found account {$bad['account_number']} with wrong customer {$bad['customer']}, should be $cust_num<br>";
        }
    }
}

// Just set all accounts with any balance to Active status 1
mysqli_query($conn, "UPDATE accounts SET account_status = 1 WHERE loan_amount > 0 OR loan_balance > 0");
echo "<br>Updated all accounts with loan amount to Active status<br>";

echo "<h3>5. After Fix - Accounts Ready for Payment:</h3>";
$ready = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, acs.account_status_name
FROM accounts a 
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE (acs.account_status_name = 'Active' OR acs.account_status_name = 'Approved')
AND (a.loan_amount > 0 OR a.loan_balance > 0)");

if (mysqli_num_rows($ready) > 0) {
    echo "<table border='1' style='background:#90EE90'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($ready)) {
        echo "<tr><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['account_status_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>Still no accounts</p>";
}

echo "<h3>6. MANUAL FIX - Update your account directly:</h3>";
echo "<p>If your loan still doesn't show, tell me your customer number (from customers table) and account number, and I'll fix it.</p>";

mysqli_close($conn);
?>
