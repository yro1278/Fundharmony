<?php
session_start();
if (!isset($_SESSION['admin']) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'database/db_connection.php';
require_once('tcpdf/tcpdf.php');

$account_number = isset($_GET['account_number']) ? $_GET['account_number'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "1=1";
if($account_number) {
    $where .= " AND a.account_number = '$account_number'";
}
if($date_from) {
    $where .= " AND a.open_date >= '$date_from'";
}
if($date_to) {
    $where .= " AND a.open_date <= '$date_to'";
}
if($status_filter) {
    $where .= " AND a.account_status = '$status_filter'";
}

$sql = "SELECT DISTINCT a.account_number, a.open_date, a.loan_amount, a.loan_balance, a.loan_term, 
a.interest, a.overdue_interest, a.due_date, c.first_name, c.middle_name, c.surname, 
act.account_type_name, acs.account_status_name
FROM accounts a
LEFT JOIN customers c ON a.customer = c.customer_number
LEFT JOIN account_type act ON a.account_type = act.account_type_number
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE $where
GROUP BY a.account_number
ORDER BY a.open_date DESC";

$result = mysqli_query($conn, $sql);

$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('FundHarmony');
$pdf->SetAuthor('FundHarmony - Microfinance Management System');
$pdf->SetTitle('Loan Report');
$pdf->SetSubject('Loan Report');
$pdf->AddPage();

$filter_text = "All Loans";
if($date_from || $date_to) {
    $filter_text = "From: " . ($date_from ? $date_from : 'Beginning') . " To: " . ($date_to ? $date_to : 'Present');
}
if($status_filter) {
    $filter_text .= " | Status Filter Applied";
}

$html = '
<style>
    h1 { text-align: center; color: #4f46e5; font-size: 22px; margin-bottom: 5px; }
    .subtitle { text-align: center; color: #666; margin-bottom: 15px; }
    .filter-info { text-align: center; color: #888; font-size: 11px; margin-bottom: 15px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #4f46e5; color: white; padding: 8px; text-align: center; font-weight: bold; font-size: 10px; }
    td { padding: 6px; border-bottom: 1px solid #eee; font-size: 9px; text-align: center; }
    tr:nth-child(even) { background: #f8fafc; }
    .text-center { text-align: center; }
</style>

<h1>Loan Report</h1>
<p class="subtitle">FundHarmony - Microfinance Management System</p>
<p class="filter-info">Generated on: ' . date('Y-m-d H:i:s') . '<br>' . $filter_text . '</p>

<table border="1" cellpadding="3">
<tr>
    <th>#</th>
    <th>Account No.</th>
    <th>Client Name</th>
    <th>Loan Type</th>
    <th>Term</th>
    <th>Loan Amount</th>
    <th>Interest</th>
    <th>Overdue</th>
    <th>Total Balance</th>
    <th>Due Date</th>
    <th>Status</th>
    <th>Open Date</th>
</tr>
';

$num = 1;
$hasData = false;

if($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $fullname = $row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['surname'];
        $loanType = $row['account_type_name'] ? $row['account_type_name'] : 'N/A';
        $loan_amount = floatval($row['loan_amount'] ?? 0);
        $interest = floatval($row['interest'] ?? 0);
        $balance = floatval($row['loan_balance'] ?? $loan_amount + $interest);
        $overdue = floatval($row['overdue_interest'] ?? 0);
        $total_due = $balance + $overdue;
        $term = intval($row['loan_term'] ?? 1);
        $due_date = $row['due_date'] ? date('M d, Y', strtotime($row['due_date'])) : '-';
        
        if($due_date != '-' && strtotime($row['due_date']) < strtotime(date('Y-m-d')) && $balance > 0) {
            $status = 'Overdue';
        } elseif($balance <= 0) {
            $status = 'Paid';
        } else {
            $status = $row['account_status_name'] ? $row['account_status_name'] : 'N/A';
        }
        
        $openDate = $row['open_date'] ? date('M d, Y', strtotime($row['open_date'])) : '-';
        
        $html .= '<tr>
        <td>'.$num.'</td>
        <td>' . $row['account_number'] . '</td>
        <td>' . $fullname . '</td>
        <td>' . $loanType . '</td>
        <td>' . $term . ' month(s)</td>
        <td>₱' . number_format($loan_amount, 2) . '</td>
        <td>₱' . number_format($interest, 2) . '</td>
        <td>₱' . number_format($overdue, 2) . '</td>
        <td>₱' . number_format($total_due, 2) . '</td>
        <td>' . $due_date . '</td>
        <td>' . $status . '</td>
        <td>' . $openDate . '</td>
        </tr>';
        $num++;
        $hasData = true;
    }
}

if(!$hasData) {
    $html .= '<tr><td colspan="12" class="text-center" style="padding:20px;color:#666;">No loan records found</td></tr>';
}

$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('loan_report_' . date('Ymd') . '.pdf', 'I');
