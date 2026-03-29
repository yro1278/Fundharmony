<?php
session_start();
if (!isset($_SESSION['admin']) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$method_filter = isset($_GET['method']) ? $_GET['method'] : '';

$where = "1=1";
if($date_from) {
    $where .= " AND p.payment_date >= '$date_from'";
}
if($date_to) {
    $where .= " AND p.payment_date <= '$date_to'";
}
if($method_filter) {
    $where .= " AND p.payment_method = '$method_filter'";
}

$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
$has_payments = false;
if(mysqli_num_rows($check_table) > 0) {
    $check_data = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM payments p WHERE $where");
    $data = mysqli_fetch_assoc($check_data);
    $has_payments = $data['cnt'] > 0;
}

require_once('tcpdf/tcpdf.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('FundHarmony - Microfinance Management System');
$pdf->SetTitle('Payment Report');
$pdf->SetSubject('Payment Report');

$pdf->AddPage();

$filter_text = "All Payments";
if($date_from || $date_to) {
    $filter_text = "From: " . ($date_from ? $date_from : 'Beginning') . " To: " . ($date_to ? $date_to : 'Present');
}
if($method_filter) {
    $filter_text .= " | Method: " . $method_filter;
}

$html = '
<style>
    h1 { text-align: center; color: #4f46e5; font-size: 24px; margin-bottom: 5px; }
    .subtitle { text-align: center; color: #666; margin-bottom: 20px; }
    .filter-info { text-align: center; color: #888; font-size: 12px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #4f46e5; color: white; padding: 12px; text-align: left; font-weight: bold; }
    td { padding: 10px; border-bottom: 1px solid #eee; }
    tr:nth-child(even) { background: #f8fafc; }
    .total-row { background: #1e293b !important; color: white; font-weight: bold; }
    .amount { text-align: right; }
    .text-center { text-align: center; }
</style>

<h1>Payment Report</h1>
<p class="subtitle">FundHarmony - Microfinance Management System</p>
<p class="filter-info">Generated on: ' . date('Y-m-d H:i:s') . '<br>' . $filter_text . '</p>

<table border="1" cellpadding="5">
<tr>
    <th>#</th>
    <th>Payment No.</th>
    <th>Client Name</th>
    <th>Account No.</th>
    <th>Amount</th>
    <th>Date</th>
    <th>Method</th>
</tr>
';

if($has_payments) {
    $sql = "SELECT p.*, c.first_name, c.surname, a.account_type 
    FROM payments p 
    LEFT JOIN accounts a ON p.account_number = a.account_number 
    LEFT JOIN customers c ON a.customer = c.customer_number 
    WHERE $where
    ORDER BY p.payment_date DESC";
    $result = mysqli_query($conn, $sql);

    $total = 0;
    $num = 1;
    while($row = mysqli_fetch_assoc($result)) {
        $client = isset($row['first_name']) ? $row['first_name'] . ' ' . $row['surname'] : 'N/A';
        $html .= '<tr>
        <td>'.$num.'</td>
        <td>#' . str_pad($row['payment_number'], 6, '0', STR_PAD_LEFT) . '</td>
        <td>' . $client . '</td>
        <td>' . $row['account_number'] . '</td>
        <td class="amount">' . number_format($row['payment_amount'], 2) . '</td>
        <td>' . date('M d, Y', strtotime($row['payment_date'])) . '</td>
        <td>' . $row['payment_method'] . '</td>
        </tr>';
        $total += $row['payment_amount'];
        $num++;
    }

    $html .= '<tr class="total-row">
    <td colspan="4" class="text-center">TOTAL</td>
    <td class="amount">' . number_format($total, 2) . '</td>
    <td colspan="2"></td>
    </tr>';
} else {
    $html .= '<tr><td colspan="7" class="text-center" style="padding:20px;color:#666;">No payment records found</td></tr>';
}

$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('payment_report_' . date('Ymd') . '.pdf', 'I');
