<?php
session_start();

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    header('Location: login.php');
    exit();
}

require_once 'database/db_connection.php';

$email = $_SESSION['otp_email'];
$time_left = $_SESSION['otp_expiry'] - time();
if ($time_left <= 0) {
    session_unset();
    $_SESSION['error'] = "OTP has expired. Please login again.";
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['otp_user_id'] ?? $_SESSION['otp_customer_id'] ?? null;
$user_type = isset($_SESSION['otp_user_id']) ? 'admin' : 'customer';

$has_qr = false;
if ($user_id) {
    if ($user_type === 'admin') {
        $result = mysqli_query($conn, "SELECT qr_code_enabled FROM users WHERE user_number = '$user_id'");
    } else {
        $result = mysqli_query($conn, "SELECT qr_code_enabled FROM customers WHERE customer_number = '$user_id'");
    }
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $has_qr = $row['qr_code_enabled'] == 1;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'generate_download') {
        $action = 'generate_download';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'send_email') {
        $action = 'send_email';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'verify') {
        $action = 'verify';
    } elseif (isset($_FILES['qr_image'])) {
        $action = 'upload_verify';
    } else {
        header('Location: verify_qr.php');
        exit();
    }
    
    if ($action === 'upload_verify') {
        header('Content-Type: application/json');
        
        if (!isset($_FILES['qr_image']) || $_FILES['qr_image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No image uploaded']);
            exit();
        }
        
        $file = $_FILES['qr_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid format. Use JPG or PNG.']);
            exit();
        }
        
        $upload_dir = __DIR__ . '/uploads/qr_verify/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = 'verify_' . time() . '.' . $ext;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $imageData = file_get_contents($filepath);
            $base64 = base64_encode($imageData);
            @unlink($filepath);
            
            echo json_encode([
                'success' => true,
                'image' => 'data:image/' . $ext . ';base64,' . $base64,
                'message' => 'Image uploaded. Verification requires QR code to contain valid token.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save image']);
        }
        exit();
    }
    
    if ($action === 'generate_download') {
        require_once('tcpdf/tcpdf.php');
        
        $token = bin2hex(random_bytes(32));
        $hashed_token = hash('sha256', $token);
        
        if ($user_type === 'admin') {
            mysqli_query($conn, "UPDATE users SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE user_number = '$user_id'");
            $user_result = mysqli_query($conn, "SELECT * FROM users WHERE user_number = '$user_id'");
        } else {
            mysqli_query($conn, "UPDATE customers SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE customer_number = '$user_id'");
            $user_result = mysqli_query($conn, "SELECT * FROM customers WHERE customer_number = '$user_id'");
        }
        
        $user = mysqli_fetch_assoc($user_result);
        $user_name = $user_type === 'admin' ? $user['username'] : ($user['first_name'] . ' ' . $user['surname']);
        
        $qr_content = json_encode([
            'token' => $token,
            'user_id' => $user_id,
            'email' => $email,
            'type' => $user_type,
            'app' => 'FundHarmony'
        ]);
        
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'ISO-8859-1', false);
        $pdf->SetCreator('FundHarmony');
        $pdf->SetAuthor('FundHarmony');
        $pdf->SetTitle('Your Login QR Code');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        
        $style = [
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
            'module_width' => 1,
            'module_height' => 1
        ];
        
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(102, 126, 234);
        $pdf->Cell(0, 12, 'FundHarmony', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 8, 'Your Unique Login QR Code', 0, 1, 'C');
        
        $pdf->Ln(5);
        
        $pdf->write2DBarcode($qr_content, 'QRCODE,H', 75, 50, 60, 60, $style, 'N');
        
        $pdf->Ln(3);
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 8, 'Account: ' . $user_name, 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(102, 126, 234);
        $pdf->Cell(0, 6, $email, 0, 1, 'C');
        
        $pdf->Ln(3);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(102, 102, 102);
        $pdf->Cell(0, 5, 'Scan this QR code during login for quick access', 0, 1, 'C');
        
        $pdf->Output('FundHarmony_QR_Code.pdf', 'D');
        exit();
    }
    
    if ($action === 'verify') {
        header('Content-Type: application/json');
        
        $scanned_token = $_POST['token'] ?? '';
        
        if (empty($scanned_token)) {
            echo json_encode(['success' => false, 'message' => 'No QR code detected']);
            exit();
        }
        
        $decoded = json_decode($scanned_token, true);
        
        if ($decoded && isset($decoded['user_id'])) {
            $qr_user_id = intval($decoded['user_id']);
            $qr_type = $decoded['type'] ?? '';
            
            if ($qr_user_id == $user_id && ($qr_type === $user_type)) {
                if ($qr_type === 'admin') {
                    $result = mysqli_query($conn, "SELECT * FROM users WHERE user_number = '$qr_user_id' AND qr_code_enabled = 1");
                } else {
                    $result = mysqli_query($conn, "SELECT * FROM customers WHERE customer_number = '$qr_user_id' AND qr_code_enabled = 1");
                }
                
                if (mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    
                    $_SESSION['admin'] = $user['username'] ?? $user['first_name'] . ' ' . $user['surname'];
                    $_SESSION['admin_name'] = $user['username'] ?? $user['first_name'] . ' ' . $user['surname'];
                    $_SESSION['user_id'] = $user['user_number'] ?? $user['customer_number'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'] ?? 'admin';
                    $_SESSION['customer_id'] = $user['customer_number'] ?? null;
                    $_SESSION['customer_name'] = $user['first_name'] . ' ' . $user['surname'];
                    $_SESSION['customer_email'] = $user['email'];
                    
                    clearLoginAttempts($conn, $user['email']);
                    
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
                    $current_timestamp = date('Y-m-d H:i:s');
                    
                    if ($qr_type === 'admin') {
                        mysqli_query($conn, "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
                            VALUES ('$qr_user_id', '" . mysqli_real_escape_string($conn, $user['username']) . "', 'admin', 'Admin Login', 'Admin logged in successfully (QR code verified)', '$ip_address', '$current_timestamp')");
                        $redirect = 'dashboard.php';
                    } else {
                        mysqli_query($conn, "INSERT INTO activity_logs (user_id, username, user_type, action, description, ip_address, created_at) 
                            VALUES ('$qr_user_id', '" . mysqli_real_escape_string($conn, $user['first_name'] . ' ' . $user['surname']) . "', 'customer', 'User Login', 'User logged in successfully (QR code verified)', '$ip_address', '$current_timestamp')");
                        $redirect = 'customer_dashboard.php';
                    }
                    
                    $remember = $_SESSION['otp_remember'] ?? '';
                    if ($remember == 'on') {
                        setcookie('remember_username', $user['email'], time() + (86400 * 30), "/");
                        setcookie('remember_password', $user['password'], time() + (86400 * 30), "/");
                    }
                    
                    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry'], 
                          $_SESSION['otp_user_id'], $_SESSION['otp_username'], $_SESSION['otp_role'], 
                          $_SESSION['otp_customer_id'], $_SESSION['otp_customer_name'],
                          $_SESSION['otp_type'], $_SESSION['otp_remember']);
                    $_SESSION['fresh_login'] = true;
                    
                    echo json_encode(['success' => true, 'redirect' => $redirect]);
                    exit();
                }
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid or expired QR code']);
        exit();
    }
    
    if ($action === 'send_email') {
        header('Content-Type: application/json');
        
        require_once('tcpdf/tcpdf.php');
        
        $user_result = $user_type === 'admin' 
            ? mysqli_query($conn, "SELECT * FROM users WHERE user_number = '$user_id'")
            : mysqli_query($conn, "SELECT * FROM customers WHERE customer_number = '$user_id'");
        $user = mysqli_fetch_assoc($user_result);
        $user_name = $user_type === 'admin' ? $user['username'] : ($user['first_name'] . ' ' . $user['surname']);
        
        $token = bin2hex(random_bytes(32));
        $hashed_token = hash('sha256', $token);
        
        if ($user_type === 'admin') {
            mysqli_query($conn, "UPDATE users SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE user_number = '$user_id'");
        } else {
            mysqli_query($conn, "UPDATE customers SET qr_code = '$hashed_token', qr_code_enabled = 1 WHERE customer_number = '$user_id'");
        }
        
        $pdf_path = generateAndSendQR($conn, $user_id, $user_type, $user_name, $email, $token);
        
        if ($pdf_path === true) {
            echo json_encode(['success' => true, 'message' => 'QR Code sent to your email! Check your inbox.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try downloading the PDF instead.']);
        }
        exit();
    }
}

function generateAndSendQR($conn, $user_id, $user_type, $user_name, $email, $token) {
    require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
    require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
    
    define('SMTP_GMAIL', 'tyronealariao06@gmail.com');
    define('SMTP_APP_PASSWORD', 'fgpbywvrhuhtoqop');
    
    $qr_content = json_encode([
        'token' => $token,
        'user_id' => $user_id,
        'email' => $email,
        'type' => $user_type,
        'app' => 'FundHarmony'
    ]);
    
    $qrcode = new TCPDF2DBarcode($qr_content, 'QRCODE,H');
    $qr_image_data = $qrcode->getBarcodePNGData(10, 10, array(0, 0, 0));
    
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'ISO-8859-1', false);
    $pdf->SetCreator('FundHarmony');
    $pdf->SetAuthor('FundHarmony');
    $pdf->SetTitle('Your Login QR Code');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();
    
    $style = [
        'border' => 2,
        'vpadding' => 'auto',
        'hpadding' => 'auto',
        'fgcolor' => [0, 0, 0],
        'bgcolor' => [255, 255, 255],
        'module_width' => 1,
        'module_height' => 1
    ];
    
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->Cell(0, 12, 'FundHarmony', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 14);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(0, 8, 'Your Unique Login QR Code', 0, 1, 'C');
    
    $pdf->Ln(5);
    $pdf->write2DBarcode($qr_content, 'QRCODE,H', 75, 50, 60, 60, $style, 'N');
    $pdf->Ln(3);
    
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(0, 8, 'Account: ' . $user_name, 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->Cell(0, 6, $email, 0, 1, 'C');
    
    $pdf->Ln(3);
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(102, 102, 102);
    $pdf->Cell(0, 5, 'Scan this QR code during login for quick access', 0, 1, 'C');
    
    $upload_dir = __DIR__ . '/uploads/qr_codes/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = 'QR_' . $user_id . '_' . time() . '.pdf';
    $filepath = $upload_dir . $filename;
    $pdf->Output($filepath, 'F');
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_GMAIL;
        $mail->Password   = SMTP_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
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
        $mail->addStringEmbeddedImage($qr_image_data, 'qrcode_' . time(), 'qrcode.png', 'base64', 'image/png');
        $mail->addAttachment(__DIR__ . '/' . $filepath, 'FundHarmony_QR_Code.pdf');
        
        $mail->isHTML(true);
        $mail->Subject = 'FundHarmony - Your QR Code for Login';
        $cid = 'qrcode_' . time();
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #667eea; text-align: center;'>FundHarmony</h2>
                <p style='font-size: 16px; color: #333;'>Hello $user_name!</p>
                <p style='font-size: 14px; color: #555;'>Your unique QR code for login has been generated. You can use this QR code during login instead of entering the OTP code.</p>
                <div style='text-align: center;'>
                    <img src='cid:$cid' alt='QR Code' style='width: 200px; height: 200px; border: 2px solid #667eea; border-radius: 10px; margin: 20px 0;'>
                </div>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 14px; font-weight: bold; text-align: center; padding: 15px; border-radius: 10px; margin: 20px 0;'>
                    <i class='fas fa-qrcode'></i> Scan this QR code to login
                </div>
                <p style='font-size: 12px; color: #888;'>If you didn't request this QR code, please ignore this email or contact support.</p>
                <p style='font-size: 12px; color: #888;'>Important: Keep your QR code safe and do not share it with anyone.</p>
            </div>
        ";
        $mail->AltBody = strip_tags($mail->Body);
        
        $mail->Timeout = 30;
        $mail->send();
        
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

require_once 'include/head.php';
?>
<style>
* { font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
body { font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
.login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative; overflow: hidden; }
.login-container::before { content: ''; position: absolute; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); animation: pulse 4s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { transform: scale(1); opacity: 0.5; } 50% { transform: scale(1.1); opacity: 0.3; } }
.login-box { background: white; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 40px; width: 480px; max-width: 95%; position: relative; z-index: 1; animation: slideUp 0.6s ease-out; }
@keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
.login-box h2 { text-align: center; color: #333; margin-bottom: 10px; font-weight: 600; }
.login-box > p { text-align: center; color: #666; margin-bottom: 25px; font-size: 14px; }
.email-display { text-align: center; color: #667eea; font-weight: 600; margin-bottom: 20px; padding: 10px; background: #f0f4ff; border-radius: 8px; }
.alert { padding: 12px 15px; border-radius: 10px; margin-bottom: 20px; }
.alert-danger { background: #fee; color: #c33; border: 1px solid #fcc; }
.alert-success { background: #efe; color: #3c3; border: 1px solid #cfc; }
.alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
.btn { width: 100%; padding: 14px; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 10px; }
.btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4); }
.btn-secondary { background: white; color: #667eea; border: 2px solid #667eea; }
.btn-secondary:hover { background: #f0f4ff; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.back-link { text-align: center; margin-top: 20px; font-size: 14px; }
.back-link a { color: #667eea; text-decoration: none; font-weight: 600; }
.timer { text-align: center; margin-top: 15px; font-size: 14px; color: #666; }
.timer span { color: #667eea; font-weight: 600; }
.qr-options { display: flex; gap: 10px; margin: 20px 0; }
.qr-option { flex: 1; padding: 20px 15px; border: 2px solid #e0e0e0; border-radius: 12px; cursor: pointer; text-align: center; transition: all 0.3s ease; background: white; }
.qr-option:hover { border-color: #667eea; background: #f8f9fa; }
.qr-option.active { border-color: #667eea; background: #f0f4ff; }
.qr-option i { font-size: 28px; color: #667eea; margin-bottom: 8px; }
.qr-option span { display: block; font-size: 13px; color: #666; font-weight: 500; }
.qr-scanner-container { display: none; margin-bottom: 20px; }
.qr-scanner-container.active { display: block; }
#qr-reader { width: 100%; border-radius: 10px; overflow: hidden; border: 2px solid #e0e0e0; }
.upload-container { display: none; margin-bottom: 20px; }
.upload-container.active { display: block; }
.upload-area { border: 2px dashed #e0e0e0; border-radius: 10px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; background: #f8f9fa; }
.upload-area:hover { border-color: #667eea; background: #f0f4ff; }
.upload-area i { font-size: 48px; color: #667eea; margin-bottom: 10px; }
.upload-area p { margin: 0; color: #666; font-size: 14px; }
.upload-area input { display: none; }
.preview-container { display: none; margin-bottom: 20px; text-align: center; }
.preview-container.active { display: block; }
.preview-container img { max-width: 200px; max-height: 200px; border-radius: 10px; border: 2px solid #667eea; margin-bottom: 10px; }
.qr-success { text-align: center; padding: 20px; }
.qr-success i { font-size: 60px; color: #22c55e; margin-bottom: 15px; }
.qr-success h3 { color: #333; margin-bottom: 10px; }
.download-section { background: #f0f4ff; border: 2px dashed #667eea; border-radius: 12px; padding: 25px; margin-bottom: 20px; text-align: center; }
.download-section h4 { margin: 0 0 10px; color: #333; font-size: 16px; }
.download-section p { margin: 0 0 15px; color: #666; font-size: 13px; }
.no-qr-section { background: #fee; border: 2px solid #fcc; border-radius: 12px; padding: 25px; margin-bottom: 20px; text-align: center; }
.no-qr-section h4 { margin: 0 0 10px; color: #c33; font-size: 16px; }
.no-qr-section p { margin: 0 0 15px; color: #666; font-size: 13px; }
.divider { display: flex; align-items: center; margin: 20px 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e0e0e0; }
.divider span { padding: 0 15px; color: #999; font-size: 12px; }
body.dark-mode { background: linear-gradient(135deg, #1e1e3f 0%, #2d1b4e 100%); }
body.dark-mode .login-box { background: #1e293b; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
body.dark-mode .login-box h2 { color: #f1f5f9; }
body.dark-mode .login-box > p { color: #94a3b8; }
body.dark-mode .email-display { background: #334155; color: #818cf8; }
body.dark-mode .qr-option { border-color: #475569; background: #1e293b; }
body.dark-mode .qr-option:hover { border-color: #818cf8; background: #334155; }
body.dark-mode .qr-option.active { border-color: #818cf8; background: #334155; }
body.dark-mode .qr-option i { color: #818cf8; }
body.dark-mode .qr-option span { color: #94a3b8; }
body.dark-mode .upload-area { border-color: #475569; background: #1e293b; }
body.dark-mode .upload-area:hover { border-color: #818cf8; background: #334155; }
body.dark-mode .download-section { background: #334155; border-color: #818cf8; }
body.dark-mode .download-section h4 { color: #f1f5f9; }
body.dark-mode .download-section p { color: #94a3b8; }
body.dark-mode .back-link { color: #94a3b8; }
body.dark-mode .back-link a { color: #818cf8; }
body.dark-mode .timer { color: #94a3b8; }
body.dark-mode .timer span { color: #818cf8; }
</style>

<body>
    <div class="login-container">
        <div class="login-box" id="main-box">
            <h2><i class="fas fa-qrcode"></i> QR Code Login</h2>
            <p>Scan your QR code to verify your identity</p>
            
            <div class="email-display"><?php echo htmlspecialchars($email); ?></div>
            
            <div id="message-area"></div>
            
            <?php if ($has_qr): ?>
                <div class="download-section">
                    <h4><i class="fas fa-qrcode"></i> Get Your QR Code</h4>
                    <p>Download the QR code as PDF or have it sent to your email for saving.</p>
                    <button class="btn btn-primary" onclick="downloadQR()" id="download-btn">
                        <i class="fas fa-download"></i> Download QR Code (PDF)
                    </button>
                    <button class="btn btn-secondary" onclick="sendToGmail()" id="send-email-btn">
                        <i class="fas fa-envelope"></i> Send to My Email
                    </button>
                </div>
                
                <div class="divider"><span>OR SCAN FROM SAVED IMAGE</span></div>
                
                <div class="upload-container active">
                    <label class="upload-area">
                        <i class="fas fa-image"></i>
                        <p>Upload your saved QR code image</p>
                        <p style="font-size: 12px; color: #999;">Supports: JPG, PNG</p>
                        <input type="file" name="qr_image" id="qr-image" accept="image/*">
                    </label>
                </div>
                
                <div class="preview-container" id="preview-container" style="display: none; text-align: center; margin-top: 15px;">
                    <p id="preview-status"></p>
                </div>
                
                <div id="qr-reader" style="display: none;"></div>
            <?php else: ?>
                <div class="no-qr-section">
                    <h4><i class="fas fa-exclamation-triangle"></i> No QR Code Found</h4>
                    <p>You need to generate a QR code first from your dashboard before you can use QR code login.</p>
                    <a href="my_qr_code.php" class="btn btn-primary" style="text-decoration: none;">
                        <i class="fas fa-qrcode"></i> Generate QR Code
                    </a>
                </div>
                
                <p style="text-align: center; color: #666; font-size: 13px; margin-top: 15px;">
                    Or go back to <a href="verify_otp.php" style="color: #667eea; font-weight: 600;">OTP Verification</a>
                </p>
            <?php endif; ?>
            
            <div class="back-link">
                <a href="verify_otp.php"><i class="fas fa-arrow-left"></i> Back to OTP Verification</a>
            </div>
            
            <div class="timer">
                OTP expires in: <span id="countdown"><?php echo floor($time_left / 60); ?>:<?php echo str_pad($time_left % 60, 2, '0'); ?></span>
            </div>
        </div>
        
        <div class="login-box" id="success-box" style="display: none;">
            <div class="qr-success">
                <i class="fas fa-check-circle"></i>
                <h3>Verification Successful!</h3>
                <p>Redirecting you to your dashboard...</p>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    })();
    
    function downloadQR() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'verify_qr.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'generate_download';
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
    
    document.getElementById('qr-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        document.getElementById('preview-container').style.display = 'block';
        document.getElementById('preview-status').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scanning QR code...';
        document.getElementById('preview-status').style.color = '#667eea';
        
        const html5QrCode = new Html5Qrcode("qr-reader");
        
        html5QrCode.scanFile(file, false)
        .then(result => {
            document.getElementById('preview-status').innerHTML = '<i class="fas fa-check-circle"></i> QR Code detected! Verifying...';
            document.getElementById('preview-status').style.color = '#22c55e';
            verifyQRCode(result);
        })
        .catch(err => {
            document.getElementById('preview-status').innerHTML = '<i class="fas fa-times-circle"></i> Could not read QR code';
            document.getElementById('preview-status').style.color = '#dc3545';
            showMessage('Could not read QR code from image. Try a clearer image or different format.', 'danger');
        });
    });
    
    function sendToGmail() {
        const btn = document.getElementById('send-email-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        fetch('verify_qr.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=send_email'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('QR Code sent to your Gmail!', 'success');
            } else {
                showMessage(data.message || 'Failed to send email', 'danger');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-envelope"></i> Send to Gmail';
        })
        .catch(error => {
            showMessage('Error: ' + error, 'danger');
        });
    }
    
    function showCamera() {
        document.getElementById('camera-section').style.display = 'block';
        document.getElementById('upload-section').style.display = 'none';
        document.getElementById('camera-btn').classList.add('active');
        document.getElementById('upload-btn').classList.remove('active');
        document.getElementById('preview-container').style.display = 'none';
        startCamera();
    }
    
    function showUpload() {
        stopCamera();
        document.getElementById('camera-section').style.display = 'none';
        document.getElementById('upload-section').style.display = 'block';
        document.getElementById('camera-btn').classList.remove('active');
        document.getElementById('upload-btn').classList.add('active');
        document.getElementById('preview-container').style.display = 'none';
    }
    
    function startCamera() {
        if (html5QrCode) return;
        html5QrCode = new Html5Qrcode("qr-reader");
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                const cameraId = cameras[cameras.length - 1].id;
                html5QrCode.start(cameraId, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, onScanFailure)
                .then(() => { document.getElementById('stop-camera-btn').style.display = 'block'; })
                .catch(err => { showMessage('Camera error: ' + err, 'danger'); });
            }
        }).catch(err => { showMessage('Could not access camera', 'danger'); });
    }
    
    function stopCamera() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => { html5QrCode = null; document.getElementById('stop-camera-btn').style.display = 'none'; }).catch(err => {});
        }
    }
    
    function onScanSuccess(decodedText) {
        stopCamera();
        verifyQRCode(decodedText);
    }
    
    function onScanFailure(error) {}
    
    function verifyQRCode(token) {
        showMessage('Verifying QR code...', 'info');
        fetch('verify_qr.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=verify&token=' + encodeURIComponent(token)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('main-box').style.display = 'none';
                document.getElementById('success-box').style.display = 'block';
                setTimeout(() => { window.location.href = data.redirect; }, 1500);
            } else {
                showMessage(data.message, 'danger');
                startCamera();
            }
        })
        .catch(error => { showMessage('Error: ' + error, 'danger'); startCamera(); });
    }
    
    function showMessage(message, type) {
        const messageArea = document.getElementById('message-area');
        const iconClass = type === 'danger' ? 'exclamation-circle' : (type === 'success' ? 'check-circle' : 'info-circle');
        const alertClass = type === 'danger' ? 'alert-danger' : (type === 'success' ? 'alert-success' : 'alert-warning');
        messageArea.innerHTML = '<div class="alert ' + alertClass + '"><i class="fas fa-' + iconClass + '"></i> ' + message + '</div>';
        if (type !== 'info') {
            setTimeout(() => { messageArea.innerHTML = ''; }, 5000);
        }
    }
    
    let timeLeft = <?php echo $time_left; ?>;
    const countdown = document.getElementById('countdown');
    const timer = setInterval(() => {
        timeLeft--;
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        countdown.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        if (timeLeft <= 0) {
            clearInterval(timer);
            countdown.textContent = 'Expired';
            countdown.style.color = '#ef4444';
            stopCamera();
        }
    }, 1000);
    
    window.addEventListener('beforeunload', () => { stopCamera(); });
    </script>
</body>
</html>
