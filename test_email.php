<?php
session_start();
require_once 'app/mailer.php';
require_once 'database/db_connection.php';

// Check if form is submitted
$test_result = '';
$test_error = '';

if (isset($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    
    $subject = 'FundHarmony - Email Test';
    $body = "
        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #667eea; text-align: center;'>FundHarmony</h2>
            <p style='font-size: 16px; color: #333;'>Hello!</p>
            <p style='font-size: 14px; color: #555;'>This is a test email from FundHarmony.</p>
            <p style='font-size: 14px; color: #555;'>If you received this email, your email settings are working correctly!</p>
            <p style='font-size: 12px; color: #888; margin-top: 20px;'>This is an automated test message.</p>
        </div>
    ";
    
    $result = sendEmail($test_email, $subject, $body);
    
    if ($result['success']) {
        $test_result = "✓ Test email sent successfully to: $test_email<br><br><strong>Check your inbox (and spam folder)!</strong>";
    } else {
        $test_error = "✗ Failed to send email.<br><br><strong>Error:</strong> " . $result['message'] . "<br><br><strong>Troubleshooting:</strong><br>1. Make sure 2-Factor Authentication is enabled on your Gmail<br>2. Generate a new App Password at: https://myaccount.google.com/apppasswords<br>3. Update the password in <code>app/mailer.php</code>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - FundHarmony</title>
    <link rel="stylesheet" href="assets/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
        .test-container h2 {
            color: #667eea;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            width: 100%;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
        .result-box {
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .info-box ol {
            margin: 0;
            padding-left: 20px;
        }
        .info-box a {
            color: #007bff;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h2>📧 Email Test - FundHarmony</h2>
        
        <div class="info-box">
            <h4>⚠️ Gmail App Password Setup (REQUIRED):</h4>
            <ol>
                <li>Go to <a href="https://myaccount.google.com/" target="_blank">Google Account</a></li>
                <li><strong>Enable 2-Factor Authentication</strong> (required!)</li>
                <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">App Passwords</a></li>
                <li>Select app: <strong>Mail</strong></li>
                <li>Select device: <strong>Other (Custom name)</strong> → type "FundHarmony"</li>
                <li>Copy the 16-character password (with spaces)</li>
                <li>Update in <code>app/mailer.php</code> line 17</li>
            </ol>
        </div>
        
        <?php if ($test_result): ?>
            <div class="result-box success"><?php echo $test_result; ?></div>
        <?php endif; ?>
        
        <?php if ($test_error): ?>
            <div class="result-box error"><?php echo $test_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="test_email"><strong>Enter email to test:</strong></label>
                <input type="email" class="form-control" id="test_email" name="test_email" required placeholder="your-email@gmail.com" value="<?php echo isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : ''; ?>">
            </div>
            <br>
            <button type="submit" name="test_email_btn" class="btn btn-primary">
                📤 Send Test Email
            </button>
        </form>
        
        <br>
        <a href="login.php" class="btn btn-secondary btn-block text-center" style="display:block; text-align:center; padding:12px; border-radius:5px; background:#6c757d; color:white; text-decoration:none;">← Back to Login</a>
    </div>
</body>
</html>
