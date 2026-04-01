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
header('Content-Disposition: attachment; filename="customer_report_'.date('Y-m-d').'.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
echo '<table border="1">';
echo '<tr style="background:#6366f1;color:white;font-weight:bold;">';
echo '<th>Customer ID</th><th>First Name</th><th>Middle Name</th><th>Surname</th><th>Email</th><th>Phone</th><th>Gender</th><th>Date Registered</th>';
echo '</tr>';

$customers = mysqli_query($conn, "
    SELECT customer_number, first_name, middle_name, surname, email, phone, gender, created_at
    FROM customers 
    WHERE created_at BETWEEN '$date_from' AND '$date_to'
    ORDER BY created_at DESC
");

while($row = mysqli_fetch_assoc($customers)) {
    echo '<tr>';
    echo '<td>'.$row['customer_number'].'</td>';
    echo '<td>'.htmlspecialchars($row['first_name']).'</td>';
    echo '<td>'.htmlspecialchars($row['middle_name'] ?? '').'</td>';
    echo '<td>'.htmlspecialchars($row['surname']).'</td>';
    echo '<td>'.htmlspecialchars($row['email'] ?? '').'</td>';
    echo '<td>'.htmlspecialchars($row['phone'] ?? '').'</td>';
    echo '<td>'.htmlspecialchars($row['gender'] ?? '').'</td>';
    echo '<td>'.$row['created_at'].'</td>';
    echo '</tr>';
}

echo '</table></body></html>';
