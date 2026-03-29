<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once 'database/db_connection.php';
require_once('tcpdf/tcpdf.php');

$account_number = isset($_GET['account_number']) ? intval($_GET['account_number']) : 0;
$user_id = $_SESSION['user_id'];

if (!$account_number) {
    die('Account number is required');
}

$sql = "SELECT 
a.account_number,
a.open_date,
a.loan_amount,
a.loan_balance,
a.loan_term,
a.interest,
a.overdue_interest,
a.due_date,
c.first_name,
c.middle_name,
c.surname,
c.gender,
c.date_of_birth,
c.nationality,
ct.customer_type_name,
act.account_type_name,
acs.account_status_name
FROM accounts a
INNER JOIN customers c ON a.customer = c.customer_number
LEFT JOIN customers_type ct ON c.customer_type = ct.customer_type_number
LEFT JOIN account_type act ON a.account_type = act.account_type_number
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.account_number = '$account_number'";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Query error: ' . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);

if(!$row) {
    die('Account not found or access denied');
}

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('FundHarmony');
$pdf->SetAuthor('FundHarmony - Microfinance Management System');
$pdf->SetTitle('Loan Account Details - ' . $row['account_number']);
$pdf->SetSubject('Loan Account Details');

$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(79, 70, 229);
$pdf->Cell(0, 10, 'FundHarmony', 0, true, 'C', 0);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(0, 10, 'LOAN ACCOUNT DETAILS', 0, true, 'C', 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(102, 102, 102);
$pdf->Cell(0, 8, 'Account No: ' . $row['account_number'], 0, true, 'C', 0);

$pdf->Ln(10);

$client_name = $row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['surname'];
$loan_amount = floatval($row['loan_amount'] ?? 0);
$interest = floatval($row['interest'] ?? 0);
$balance = floatval($row['loan_balance'] ?? $loan_amount + $interest);
$overdue = floatval($row['overdue_interest'] ?? 0);
$total_due = $balance + $overdue;
$term = intval($row['loan_term'] ?? 1);
$due_date = $row['due_date'] ? date('F d, Y', strtotime($row['due_date'])) : '-';
$days_remaining = '-';
if($row['due_date']) {
    $days_until = (strtotime($row['due_date']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
    if($days_until > 0) {
        $days_remaining = $days_until . ' days remaining';
    } elseif($days_until < 0) {
        $days_remaining = abs($days_until) . ' days overdue';
    } else {
        $days_remaining = 'Due today';
    }
}

if($due_date != '-' && strtotime($row['due_date']) < strtotime(date('Y-m-d')) && $balance > 0) {
    $status = 'Overdue';
} elseif($balance <= 0) {
    $status = 'Paid';
} else {
    $status = $row['account_status_name'] ?: 'N/A';
}

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Client Name:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $client_name, 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Gender:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $row['gender'] ?: 'N/A', 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Date of Birth:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $row['date_of_birth'] ? date('F d, Y', strtotime($row['date_of_birth'])) : 'N/A', 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Nationality:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $row['nationality'] ?: 'N/A', 0, 1, 'L', 0);

$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Client Type:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $row['customer_type_name'] ?: 'N/A', 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Loan Type:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $row['account_type_name'] ?: 'N/A', 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Loan Term:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $term . ' month(s)', 0, 1, 'L', 0);

$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Loan Amount:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, '₱' . number_format($loan_amount, 2), 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Interest:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, '₱' . number_format($interest, 2), 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Overdue Penalty:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(239, 68, 68);
$pdf->Cell(0, 8, '₱' . number_format($overdue, 2), 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Total Balance:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(79, 70, 229);
$pdf->Cell(0, 8, '₱' . number_format($total_due, 2), 0, 1, 'L', 0);

$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Due Date:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
if($days_remaining != '-' && strpos($days_remaining, 'overdue') !== false) {
    $pdf->SetTextColor(239, 68, 68);
} elseif($days_remaining == 'Due today') {
    $pdf->SetTextColor(245, 158, 11);
} else {
    $pdf->SetTextColor(85, 85, 85);
}
$pdf->Cell(0, 8, $due_date . ' (' . $days_remaining . ')', 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Account Status:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', 'B', 11);
if($status == 'Paid') {
    $pdf->SetTextColor(16, 185, 129);
} elseif($status == 'Overdue') {
    $pdf->SetTextColor(239, 68, 68);
} else {
    $pdf->SetTextColor(245, 158, 11);
}
$pdf->Cell(0, 8, $status, 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Account Open Date:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, date('F d, Y', strtotime($row['open_date'])), 0, 1, 'L', 0);

$pdf->Ln(20);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(136, 136, 136);
$pdf->Cell(0, 8, 'This is a computer-generated document. No signature required.', 0, true, 'C', 0);
$pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d H:i:s'), 0, true, 'C', 0);
$pdf->Cell(0, 8, 'FundHarmony Microfinance Management System', 0, true, 'C', 0);

ob_end_clean();
$pdf->Output('account_details_' . $row['account_number'] . '.pdf', 'I');
