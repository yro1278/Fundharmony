<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['admin']) && !isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'database/db_connection.php';
require_once 'include/head.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';

define('SMTP_GMAIL', 'tyronealariao06@gmail.com');
define('SMTP_APP_PASSWORD', 'fgpbywvrhuhtoqop');

$user_id = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
$user_type = isset($_SESSION['user_id']) ? 'admin' : 'customer';
$user_name = $_SESSION['admin_name'] ?? $_SESSION['customer_name'] ?? '';
$user_email = $_SESSION['email'] ?? $_SESSION['customer_email'] ?? '';

if ($user_type === 'admin') {
    $result = mysqli_query($conn, "SELECT * FROM users WHERE user_number = '$user_id'");
} else {
    $result = mysqli_query($conn, "SELECT * FROM customers WHERE customer_number = '$user_id'");
}

$user = mysqli_fetch_assoc($result);
$qr_enabled = $user['qr_code_enabled'] ?? false;
$qr_exists = !empty($user['qr_code']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'generate':
            $token = generateQRToken();
            $hashed_token = hashQRToken($token);
            
            if ($user_type === 'admin') {
                mysqli_query($conn, "UPDATE users SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE user_number = '$user_id'");
            } else {
                mysqli_query($conn, "UPDATE customers SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE customer_number = '$user_id'");
            }
            
            require_once 'app/mailer.php';
            require_once('tcpdf/tcpdf.php');
            
            $pdf_path = generateQRPDF($conn, $user_id, $user_type, $user_name, $user_email, $token);
            
            $qr_content = json_encode([
                'token' => $token,
                'user_id' => $user_id,
                'email' => $user_email,
                'type' => $user_type,
                'app' => 'FundHarmony'
            ]);
            
            if ($pdf_path) {
                $email_result = sendQRCodeEmail($user_email, $user_name, $pdf_path, $qr_content);
                
                if ($email_result['success']) {
                    echo json_encode([
                        'success' => true, 
                        'token' => $token, 
                        'message' => 'QR Code sent to your Gmail!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => true, 
                        'token' => $token, 
                        'message' => 'QR Code generated but email failed: ' . $email_result['message']
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to generate QR code']);
            }
            break;
            
        case 'resend':
            require_once 'app/mailer.php';
            require_once('tcpdf/tcpdf.php');
            
            $token = generateQRToken();
            $hashed_token = hashQRToken($token);
            
            if ($user_type === 'admin') {
                mysqli_query($conn, "UPDATE users SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE user_number = '$user_id'");
            } else {
                mysqli_query($conn, "UPDATE customers SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE customer_number = '$user_id'");
            }
            
            $pdf_path = generateQRPDF($conn, $user_id, $user_type, $user_name, $user_email, $token);
            
            $qr_content = json_encode([
                'token' => $token,
                'user_id' => $user_id,
                'email' => $user_email,
                'type' => $user_type,
                'app' => 'FundHarmony'
            ]);
            
            if ($pdf_path) {
                $email_result = sendQRCodeEmail($user_email, $user_name, $pdf_path, $qr_content);
                
                if ($email_result['success']) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'QR Code sent to your email!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Failed to send email: ' . $email_result['message']
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to generate PDF']);
            }
            break;
            
        case 'disable':
            if ($user_type === 'admin') {
                mysqli_query($conn, "UPDATE users SET qr_code = NULL, qr_code_enabled = 0 WHERE user_number = '$user_id'");
            } else {
                mysqli_query($conn, "UPDATE customers SET qr_code = NULL, qr_code_enabled = 0 WHERE customer_number = '$user_id'");
            }
            
            $log_msg = $user_type === 'admin' ? 'Admin disabled QR code login' : 'Customer disabled QR code login';
            logActivity($conn, $user_id, $user_name, 'QR Code Disabled', $log_msg, $user_type);
            
            echo json_encode(['success' => true, 'message' => 'QR Code disabled successfully']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

function generateQRPDF($conn, $user_id, $user_type, $user_name, $user_email, $token) {
    class QRWithLogo extends TCPDF {
        public $logo_path = 'assets/img/logo.png';
        
        public function Header() {
            if (file_exists($this->logo_path)) {
                $this->Image($this->logo_path, 85, 5, 40, 20, 'PNG');
            }
        }
    }
    
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
    
    $upload_dir = 'uploads/qr_codes/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = 'QR_' . $user_id . '_' . time() . '.pdf';
    $filepath = $upload_dir . $filename;
    
    $pdf->Output(__DIR__ . '/' . $filepath, 'F');
    
    return $filepath;
}

function sendQRCodeEmail($email, $name, $pdf_path, $qr_content = '') {
    $full_path = __DIR__ . '/' . $pdf_path;
    
    $qr_image_cid = '';
    $qr_image_html = '';
    
    if (!empty($qr_content)) {
        require_once('tcpdf/tcpdf.php');
        $qr_image_data = generateQRImageData($qr_content);
        if ($qr_image_data) {
            $qr_image_cid = 'qrcode_' . time();
            $qr_image_html = '<img src="cid:' . $qr_image_cid . '" alt="QR Code" style="width: 200px; height: 200px; border: 2px solid #667eea; border-radius: 10px; margin: 20px 0;">';
        }
    }
    
    $subject = 'FundHarmony - Your QR Code for Login';
    $body = "
        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #667eea; text-align: center;'>FundHarmony</h2>
            <p style='font-size: 16px; color: #333;'>Hello $name!</p>
            <p style='font-size: 14px; color: #555;'>Your unique QR code for login has been generated. You can use this QR code during login instead of entering the OTP code.</p>
            <div style='text-align: center;'>
                $qr_image_html
            </div>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 14px; font-weight: bold; text-align: center; padding: 15px; border-radius: 10px; margin: 20px 0;'>
                <i class='fas fa-qrcode'></i> Scan this QR code to login
            </div>
            <p style='font-size: 12px; color: #888;'>If you didn't request this QR code, please ignore this email or contact support.</p>
            <p style='font-size: 12px; color: #888;'>Important: Keep your QR code safe and do not share it with anyone.</p>
        </div>
    ";
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_GMAIL;
        $mail->Password   = SMTP_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom(SMTP_GMAIL, 'FundHarmony');
        $mail->addAddress($email);
        $mail->addReplyTo(SMTP_GMAIL, 'FundHarmony Support');
        
        if (!empty($qr_image_cid)) {
            $mail->addStringEmbeddedImage($qr_image_data, $qr_image_cid, 'qrcode.png', 'base64', 'image/png');
        }
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        
        if (file_exists($full_path)) {
            $mail->addAttachment($full_path, 'FundHarmony_QR_Code.pdf');
        }
        
        $mail->Timeout = 30;
        $mail->send();
        
        return array('success' => true, 'message' => 'Email sent successfully');
    } catch (Exception $e) {
        $error_msg = $mail->ErrorInfo;
        error_log("Email Error: " . $error_msg);
        return array('success' => false, 'message' => $error_msg);
    }
}

function generateQRImageData($qr_content) {
    require_once('tcpdf/tcpdf.php');
    
    $qrcode = new TCPDF2DBarcode($qr_content, 'QRCODE,H');
    $data = $qrcode->getBarcodePNGData(10, 10, array(0, 0, 0));
    
    return $data;
}
?>
<style>
* {
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

body {
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

.main-content {
    max-width: 600px;
    margin: 0 auto;
}

.page-header {
    background: white;
    padding: 25px 30px;
    border-radius: 15px 15px 0 0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.page-header h1 {
    margin: 0;
    color: #333;
    font-size: 24px;
    font-weight: 600;
}

.page-header p {
    margin: 5px 0 0;
    color: #666;
    font-size: 14px;
}

.content-card {
    background: white;
    padding: 30px;
    border-radius: 0 0 15px 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.info-box {
    background: #f0f4ff;
    border-left: 4px solid #667eea;
    padding: 15px;
    border-radius: 0 8px 8px 0;
    margin-bottom: 25px;
}

.info-box h4 {
    margin: 0 0 5px;
    color: #333;
    font-size: 14px;
}

.info-box p {
    margin: 0;
    color: #666;
    font-size: 13px;
}

.qr-display {
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 15px;
    margin-bottom: 25px;
}

.qr-display img {
    max-width: 200px;
    margin-bottom: 15px;
}

.qr-display .qr-placeholder {
    width: 200px;
    height: 200px;
    margin: 0 auto 15px;
    background: #e9ecef;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: #adb5bd;
}

.qr-display p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.btn-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    flex: 1;
    min-width: 140px;
    padding: 14px 20px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
}

.btn-secondary:hover {
    background: #f0f4ff;
}

.btn-danger {
    background: #fee;
    color: #dc3545;
    border: 2px solid #dc3545;
}

.btn-danger:hover {
    background: #dc3545;
    color: white;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.alert {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 15px;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.back-link {
    margin-top: 25px;
    text-align: center;
}

.back-link a {
    color: #667eea;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.back-link a:hover {
    text-decoration: underline;
}

body.dark-mode {
    background: linear-gradient(135deg, #1e1e3f 0%, #2d1b4e 100%);
}

body.dark-mode .page-header,
body.dark-mode .content-card {
    background: #1e293b;
}

body.dark-mode .page-header h1 {
    color: #f1f5f9;
}

body.dark-mode .page-header p {
    color: #94a3b8;
}

body.dark-mode .info-box {
    background: #334155;
    border-color: #818cf8;
}

body.dark-mode .info-box h4 {
    color: #f1f5f9;
}

body.dark-mode .info-box p {
    color: #94a3b8;
}

body.dark-mode .qr-display {
    background: #334155;
}

body.dark-mode .qr-display .qr-placeholder {
    background: #475569;
    color: #94a3b8;
}

body.dark-mode .qr-display p {
    color: #94a3b8;
}

body.dark-mode .btn-secondary {
    background: #1e293b;
    color: #818cf8;
    border-color: #818cf8;
}

body.dark-mode .btn-secondary:hover {
    background: #334155;
}

body.dark-mode .back-link a {
    color: #818cf8;
}
</style>

<body>
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-qrcode"></i> QR Code Login</h1>
            <p>Manage your QR code for quick and secure login</p>
        </div>
        
        <div class="content-card">
            <?php if(isset($_SESSION['qr_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['qr_message_type']; ?>">
                    <?php echo $_SESSION['qr_message']; unset($_SESSION['qr_message'], $_SESSION['qr_message_type']); ?>
                </div>
            <?php endif; ?>
            
            <div class="status-badge <?php echo $qr_enabled ? 'active' : 'inactive'; ?>">
                <i class="fas fa-<?php echo $qr_enabled ? 'check-circle' : 'times-circle'; ?>"></i>
                QR Code <?php echo $qr_enabled ? 'Active' : 'Inactive'; ?>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> How it works</h4>
                <p>Generate a unique QR code for your account. The QR code will be sent to your email. During login, you can scan your QR code with your phone's camera or upload your saved QR image for instant verification.</p>
            </div>
            
            <div class="email-info" style="background: #fff3cd; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 25px;">
                <h4 style="margin: 0 0 5px; color: #856404; font-size: 14px;"><i class="fas fa-envelope" style="color: #f59e0b;"></i> QR Code will be sent to:</h4>
                <p style="margin: 0; color: #856404; font-size: 15px; font-weight: 600;"><?php echo htmlspecialchars($user_email); ?></p>
            </div>
            
            <div class="qr-display" id="qr-display">
                <?php if ($qr_enabled && $qr_exists): ?>
                    <div class="qr-placeholder" style="background: #d4edda; color: #155724;">
                        <i class="fas fa-check-circle" style="font-size: 48px;"></i>
                    </div>
                    <p style="color: #155724; font-weight: 600;">Your QR code has been sent to your email!</p>
                    <p style="font-size: 12px; color: #666;">Check your inbox (and spam folder) for the QR code PDF.</p>
                <?php else: ?>
                    <div class="qr-placeholder">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <p>No QR code generated yet</p>
                <?php endif; ?>
            </div>
            
            <div class="btn-group" id="btn-group">
                <?php if ($qr_enabled && $qr_exists): ?>
                    <button class="btn btn-primary" onclick="resendEmail()">
                        <i class="fas fa-envelope"></i> Resend to Email
                    </button>
                    <button class="btn btn-secondary" onclick="regenerateQR()">
                        <i class="fas fa-sync-alt"></i> Regenerate New
                    </button>
                    <button class="btn btn-danger" onclick="disableQR()">
                        <i class="fas fa-trash"></i> Disable
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="generateQR()">
                        <i class="fas fa-plus"></i> Generate & Send to Email
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="back-link">
                <?php if ($user_type === 'admin'): ?>
                    <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                <?php else: ?>
                    <a href="customer_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    function generateQR() {
        const btn = event.target.closest('.btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating & Sending...';
        
        fetch('my_qr_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=generate'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'QR Code generated and sent to your email! Check your inbox.');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus"></i> Generate & Send to Email';
            }
        })
        .catch(error => {
            showAlert('danger', 'Error: ' + error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i> Generate & Send to Email';
        });
    }
    
    function resendEmail() {
        const btn = event.target.closest('.btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        fetch('my_qr_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=resend'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'QR Code sent to your email! Check your inbox.');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-envelope"></i> Resend to Email';
            }
        })
        .catch(error => {
            showAlert('danger', 'Error: ' + error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-envelope"></i> Resend to Email';
        });
    }
    
    function regenerateQR() {
        if (!confirm('Are you sure you want to regenerate your QR code? Your old QR code will stop working and a new one will be sent to your email.')) {
            return;
        }
        
        const btn = event.target.closest('.btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Regenerating...';
        
        fetch('my_qr_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=generate'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'QR Code regenerated and sent to your email!');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt"></i> Regenerate New';
            }
        })
        .catch(error => {
            showAlert('danger', 'Error: ' + error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync-alt"></i> Regenerate New';
        });
    }
    
    function disableQR() {
        if (!confirm('Are you sure you want to disable QR code login? You will need to use OTP to login.')) {
            return;
        }
        
        const btn = event.target.closest('.btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Disabling...';
        
        fetch('my_qr_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=disable'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'QR Code disabled successfully.');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash"></i> Disable';
            }
        })
        .catch(error => {
            showAlert('danger', 'Error: ' + error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Disable';
        });
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type;
        alertDiv.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
        
        const contentCard = document.querySelector('.content-card');
        contentCard.insertBefore(alertDiv, contentCard.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    </script>
</body>
</html>
