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
header('Content-Disposition: attachment; filename="payment_report_'.date('Y-m-d').'.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
echo '<table border="1">';
echo '<tr style="background:#10b981;color:white;font-weight:bold;">';
echo '<th>Payment ID</th><th>Loan ID</th><th>Customer Name</th><th>Amount</th><th>Payment Date</th><th>Payment Method</th><th>Remarks</th>';
echo '</tr>';

$payments = mysqli_query($conn, "
    SELECT 
        p.payment_id,
        p.account_number,
        p.payment_amount,
        p.payment_date,
        p.payment_method,
        p.remarks,
        CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.surname) as customer_name
    FROM payments p
    INNER JOIN accounts a ON p.account_number = a.account_number
    INNER JOIN customers c ON a.customer = c.customer_number
    WHERE p.payment_date BETWEEN '$date_from' AND '$date_to'
    ORDER BY p.payment_date DESC
");

while($row = mysqli_fetch_assoc($payments)) {
    echo '<tr>';
    echo '<td>'.$row['payment_id'].'</td>';
    echo '<td>#'.$row['account_number'].'</td>';
    echo '<td>'.htmlspecialchars(trim($row['customer_name'])).'</td>';
    echo '<td>'.$row['payment_amount'].'</td>';
    echo '<td>'.$row['payment_date'].'</td>';
    echo '<td>'.($row['payment_method'] ?? 'N/A').'</td>';
    echo '<td>'.($row['remarks'] ?? '').'</td>';
    echo '</tr>';
}

echo '</table></body></html>';
