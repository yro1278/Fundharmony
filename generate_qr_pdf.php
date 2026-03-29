<?php
session_start();
require_once 'database/db_connection.php';

if (!isset($_SESSION['admin']) && !isset($_SESSION['customer_id'])) {
    http_response_code(403);
    exit('Access denied. Please login first.');
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
$user_type = isset($_SESSION['user_id']) ? 'admin' : 'customer';

if ($user_type === 'admin') {
    $result = mysqli_query($conn, "SELECT * FROM users WHERE user_number = '$user_id'");
} else {
    $result = mysqli_query($conn, "SELECT * FROM customers WHERE customer_number = '$user_id'");
}

$user = mysqli_fetch_assoc($result);

if (!$user || empty($user['qr_code']) || !$user['qr_code_enabled']) {
    http_response_code(404);
    exit('QR Code not found. Please generate a QR code first.');
}

require_once('tcpdf/tcpdf.php');

class QRWithLogo extends TCPDF {
    public $logo_path = 'assets/img/logo.png';
    
    public function Header() {
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, 85, 5, 40, 20, 'PNG');
        }
    }
}

$user_name = $user_type === 'admin' ? $user['username'] : ($user['first_name'] . ' ' . $user['surname']);
$user_email = $user['email'];
$qr_token = 'FHQR_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(8));

$pdf = new QRWithLogo('L', 'mm', 'A4', true, 'ISO-8859-1', false);
$pdf->SetCreator('FundHarmony');
$pdf->SetAuthor('FundHarmony');
$pdf->SetTitle('Your Login QR Code');
$pdf->SetSubject('QR Code for Login');
$pdf->SetMargins(10, 30, 10);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(102, 126, 234);
$pdf->Cell(0, 15, 'FundHarmony', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 14);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(0, 10, 'Your Unique Login QR Code', 0, 1, 'C');

$pdf->Ln(10);

$qr_content = json_encode([
    'token' => $qr_token,
    'user_id' => $user_id,
    'email' => $user_email,
    'type' => $user_type,
    'app' => 'FundHarmony'
]);

$style = [
    'border' => 2,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => [102, 126, 234],
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
];

$pdf->write2DBarcode($qr_content, 'QRCODE,L', 70, 60, 70, 70, $style, 'N');

$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(0, 8, 'Account: ' . $user_name, 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(102, 126, 234);
$pdf->Cell(0, 6, $user_email, 0, 1, 'C');

$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(102, 102, 102);
$pdf->MultiCell(0, 5, 'This QR code is unique to your account. Keep it safe and do not share it with anyone. Scan this code during login for quick access.', 0, 'C');

$pdf->Ln(5);

$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(0, 5, 'Generated on: ' . date('F d, Y H:i:s'), 0, 1, 'C');

$pdf->Output('FundHarmony_QR_Code.pdf', 'I');
