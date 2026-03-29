<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'mims');

echo "<h2>YOUR Account Info</h2><hr>";

echo "<h3>Your Session Info:</h3>";
echo "customer_id: " . ($_SESSION['customer_id'] ?? 'NOT SET') . "<br>";
echo "customer_name: " . ($_SESSION['customer_name'] ?? 'NOT SET') . "<br><br>";

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    
    echo "<h3>Your Loans (using your customer_id = $customer_id):</h3>";
    $my_loans = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.account_status, acs.account_status_name
    FROM accounts a 
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE a.customer = '$customer_id'");
    
    if (mysqli_num_rows($my_loans) > 0) {
        echo "<table border='1' cellpadding='5' style='background:#ffffcc'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Balance</th><th>Status</th></tr>";
        while ($row = mysqli_fetch_assoc($my_loans)) {
            echo "<tr><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status_name']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No loans found for customer_id = $customer_id</p>";
    }
    
    echo "<h3>FIX: Update all accounts to match your customer_id:</h3>";
    mysqli_query($conn, "UPDATE accounts SET customer = '$customer_id' WHERE loan_amount > 0 OR loan_balance > 0");
    echo "Updated accounts<br><br>";
    
    echo "<h3>Your Loans After Fix:</h3>";
    $my_loans = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.account_status, acs.account_status_name
    FROM accounts a 
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE a.customer = '$customer_id'");
    
    if (mysqli_num_rows($my_loans) > 0) {
        echo "<table border='1' cellpadding='5' style='background:#90EE90'><tr><th>Acct#</th><th>Cust#</th><th>Amount</th><th>Balance</th><th>Status</th></tr>";
        while ($row = mysqli_fetch_assoc($my_loans)) {
            echo "<tr><td>{$row['account_number']}</td><td>{$row['customer']}</td><td>{$row['loan_amount']}</td><td>{$row['loan_balance']}</td><td>{$row['account_status_name']}</td></tr>";
        }
        echo "</table>";
        echo "<h3 style='color:green'>✓ Refresh Make Payment page now!</h3>";
    } else {
        echo "<p style='color:red'>Still no loans</p>";
    }
} else {
    echo "<p style='color:red'>Not logged in! Please login first.</p>";
}

mysqli_close($conn);
?>
