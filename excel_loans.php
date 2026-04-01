<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="loan_report_'.date('Y-m-d').'.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
echo '<table border="1">';
echo '<tr style="background:#4f46e5;color:white;font-weight:bold;">';
echo '<th>Loan ID</th><th>Customer Name</th><th>Loan Type</th><th>Amount</th><th>Interest Rate</th><th>Date Released</th><th>Due Date</th><th>Status</th>';
echo '</tr>';

$loans = mysqli_query($conn, "
    SELECT 
        a.account_number,
        a.loan_amount,
        a.interest,
        a.open_date,
        a.due_date,
        CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.surname) as customer_name,
        act.account_type_name,
        acs.account_status_name
    FROM accounts a
    INNER JOIN customers c ON a.customer = c.customer_number
    LEFT JOIN account_type act ON a.account_type = act.account_type_number
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE a.open_date BETWEEN '$date_from' AND '$date_to'
    ORDER BY a.open_date DESC
");

while($row = mysqli_fetch_assoc($loans)) {
    echo '<tr>';
    echo '<td>#'.$row['account_number'].'</td>';
    echo '<td>'.htmlspecialchars(trim($row['customer_name'])).'</td>';
    echo '<td>'.($row['account_type_name'] ?? 'N/A').'</td>';
    echo '<td>'.$row['loan_amount'].'</td>';
    echo '<td>'.$row['interest'].'%</td>';
    echo '<td>'.$row['open_date'].'</td>';
    echo '<td>'.($row['due_date'] ?? 'N/A').'</td>';
    echo '<td>'.($row['account_status_name'] ?? 'N/A').'</td>';
    echo '</tr>';
}

echo '</table></body></html>';
