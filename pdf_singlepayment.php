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

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if (!$payment_id) {
    die('Payment ID is required');
}

$sql = "SELECT p.*, c.first_name, c.surname, a.account_number
FROM payments p 
LEFT JOIN accounts a ON p.account_number = a.account_number 
LEFT JOIN customers c ON a.customer = c.customer_number 
WHERE p.payment_number = $payment_id AND (p.user_id = '$user_id' OR p.user_id IS NULL)";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Query error: ' . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);

if(!$row) {
    die('Payment not found');
}

if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION', 'P');
if (!defined('PDF_UNIT')) define('PDF_UNIT', 'mm');
if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT', 'A4');
if (!defined('PDF_CREATOR')) define('PDF_CREATOR', 'FundHarmony');

require_once('tcpdf/tcpdf.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('FundHarmony - Microfinance Management System');
$pdf->SetTitle('Payment Receipt - ' . $row['payment_number']);
$pdf->SetSubject('Payment Receipt');

$pdf->AddPage();

$client_name = isset($row['first_name']) ? $row['first_name'] . ' ' . $row['surname'] : 'N/A';

$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(79, 70, 229);
$pdf->Cell(0, 10, 'FundHarmony', 0, true, 'C', 0);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(0, 10, 'PAYMENT RECEIPT', 0, true, 'C', 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(102, 102, 102);
$pdf->Cell(0, 8, 'Receipt No: #' . str_pad($row['payment_number'], 6, '0', STR_PAD_LEFT), 0, true, 'C', 0);

$pdf->Ln(15);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Client Name:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $client_name, 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Account Number:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $row['account_number'], 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Payment Date:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, date('F d, Y', strtotime($row['payment_date'])), 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Payment Method:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $row['payment_method'], 0, 1, 'L', 0);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 8, 'Notes:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(85, 85, 85);
$pdf->Cell(0, 8, $row['notes'] ?: 'None', 0, 1, 'L', 0);

$pdf->Ln(10);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(79, 70, 229);
$pdf->Cell(60, 10, 'Amount Paid:', 0, 0, 'L', 0);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, number_format($row['payment_amount'], 2), 0, 1, 'L', 0);

$pdf->Ln(20);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(136, 136, 136);
$pdf->Cell(0, 8, 'This is a computer-generated receipt. No signature required.', 0, true, 'C', 0);
$pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d H:i:s'), 0, true, 'C', 0);
$pdf->Cell(0, 8, 'FundHarmony Microfinance Management System', 0, true, 'C', 0);

ob_end_clean();
$pdf->Output('payment_receipt_' . $row['payment_number'] . '.pdf', 'I');
