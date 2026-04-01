<?php
session_start();

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['otp_email'];
$time_left = $_SESSION['otp_expiry'] - time();
if ($time_left <= 0) {
    session_unset();
    $_SESSION['error'] = "OTP has expired. Please login again.";
    header('Location: login.php');
    exit();
}

require_once 'include/head.php';
?>
<style>
* {
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

body {
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}
.login-container::before {
    content: '';
    position: absolute;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
    animation: pulse 4s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.3; }
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
.login-box {
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    padding: 40px;
    width: 400px;
    position: relative;
    z-index: 1;
    animation: slideUp 0.6s ease-out;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
.login-box h2 {
    text-align: center;
    color: #333;
    margin-bottom: 10px;
    font-weight: 600;
}
.login-box h2 .header-badge {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 25px;
    border-radius: 25px;
    font-size: 18px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.login-box h2 .title-text {
    animation: fadeInLeft 0.5s ease-out 0.2s both;
}
@keyframes fadeInLeft {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
}
.login-box p {
    text-align: center;
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}
.login-box .email-display {
    text-align: center;
    color: #667eea;
    font-weight: 600;
    margin-bottom: 20px;
    padding: 10px;
    background: #f0f4ff;
    border-radius: 8px;
    animation: slideIn 0.5s ease-out 0.2s both;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}
.login-box .email-display:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    transform: scale(1.02);
    transition: all 0.3s ease;
}
.otp-inputs {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
    animation: fadeIn 0.5s ease-out 0.3s both;
}
.otp-inputs input {
    width: 50px;
    height: 50px;
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #fafafa;
}
.otp-inputs input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15), 0 4px 12px rgba(102, 126, 234, 0.2);
    transform: scale(1.05);
    background: #fff;
}
.otp-inputs input.typing {
    border-color: #764ba2;
    box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.15);
    animation: typePop 0.2s ease-out;
}
@keyframes typePop {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
.otp-inputs input.filled {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}
.login-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}
.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}
.login-btn:hover::before {
    left: 100%;
}
.login-btn:active {
    transform: translateY(0) scale(0.98);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}
.login-btn:disabled {
    background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
.login-btn .btn-loader {
    display: none;
}
.login-btn.loading .btn-text {
    display: none;
}
.login-btn.loading .btn-loader {
    display: inline-block;
}
.login-btn.loading {
    pointer-events: none;
}
.resend-link {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: #666;
}
.resend-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
}
.resend-link a:hover {
    color: #764ba2;
    text-shadow: 0 0 8px rgba(102, 126, 234, 0.4);
}
.resend-link a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
}
.resend-link a:hover::after {
    width: 100%;
}
.timer {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
    color: #666;
    animation: fadeIn 0.5s ease-out 0.3s both;
}
.timer span {
    color: #667eea;
    font-weight: 600;
    transition: color 0.3s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.alert {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    animation: slideDown 0.4s ease-out;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.alert-danger {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}
.alert-success {
    background: #efe;
    color: #3c3;
    border: 1px solid #cfc;
}
body.dark-mode {
    background: linear-gradient(135deg, #1e1e3f 0%, #2d1b4e 100%);
}
body.dark-mode .login-box {
    background: #1e293b;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}
body.dark-mode .login-box h2 {
    color: #f1f5f9;
}
body.dark-mode .login-box h2 .header-badge { box-shadow: 0 4px 15px rgba(79, 70, 229, 0.5); }
body.dark-mode .login-box p {
    color: #94a3b8;
}
body.dark-mode .login-box .email-display {
    background: #334155;
    color: #818cf8;
}
body.dark-mode .otp-inputs input {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
}
body.dark-mode .otp-inputs input:focus {
    border-color: #667eea;
}
body.dark-mode .resend-link {
    color: #94a3b8;
}
body.dark-mode .resend-link a {
    color: #818cf8;
}
body.dark-mode .timer {
    color: #94a3b8;
}
body.dark-mode .timer span {
    color: #818cf8;
}
body.dark-mode .qr-link {
    border-color: #334155;
    animation: fadeIn 0.5s ease-out 0.5s both;
}
body.dark-mode .qr-link a {
    color: #818cf8;
    transition: all 0.3s ease;
}
body.dark-mode .qr-link a:hover {
    color: #a5b4fc;
    text-shadow: 0 0 8px rgba(129, 140, 248, 0.4);
    transform: translateX(3px);
}
</style>

<body>
    <div class="login-container">
        <div class="login-box">
            <h2><span class="header-badge">Verify OTP</span></h2>
            <p>We've sent a 6-digit code to your email</p>
            
            <div class="email-display"><?php echo $email; ?></div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['email_error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Email Error: <?php echo $_SESSION['email_error']; unset($_SESSION['email_error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <form action="app/verify_otp_handler.php" method="post" id="otpForm">
                <div class="otp-inputs">
                    <input type="text" name="otp1" maxlength="1" class="otp-input" autofocus>
                    <input type="text" name="otp2" maxlength="1" class="otp-input">
                    <input type="text" name="otp3" maxlength="1" class="otp-input">
                    <input type="text" name="otp4" maxlength="1" class="otp-input">
                    <input type="text" name="otp5" maxlength="1" class="otp-input">
                    <input type="text" name="otp6" maxlength="1" class="otp-input">
                </div>
                
                <button type="submit" name="verify_otp" class="login-btn" id="verifyBtn">
                    <span class="btn-text"><i class="fas fa-check"></i> Verify</span>
                    <span class="btn-loader"><i class="fas fa-circle-notch fa-spin"></i> Verifying...</span>
                </button>
            </form>
            
            <div class="timer">
                Time remaining: <span id="countdown"><?php echo floor($time_left / 60); ?>:<?php echo str_pad($time_left % 60, 2, '0'); ?></span>
            </div>
            
            <div class="resend-link">
                Didn't receive the code? <a href="app/resend_otp.php">Resend OTP</a>
            </div>
            
            <div class="qr-link" style="text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
                <a href="verify_qr.php" style="color: #667eea; text-decoration: none; font-weight: 600; font-size: 14px;">
                    <i class="fas fa-qrcode"></i> Login with QR Code instead
                </a>
            </div>
        </div>
    </div>
    
    <script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    })();
    
    const inputs = document.querySelectorAll('.otp-input');
    const form = document.getElementById('otpForm');
    const verifyBtn = document.getElementById('verifyBtn');
    
    form.addEventListener('submit', function() {
        verifyBtn.classList.add('loading');
        verifyBtn.disabled = true;
    });
    
    inputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            if (this.value.length === 1) {
                this.classList.add('filled');
                this.classList.add('typing');
                setTimeout(() => this.classList.remove('typing'), 200);
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
        });
        
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace') {
                this.classList.remove('filled');
                if (this.value === '') {
                    if (index > 0) {
                        inputs[index - 1].focus();
                    }
                }
            }
        });
        
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text');
            if (/^\d{6}$/.test(pasteData)) {
                inputs.forEach((input, i) => {
                    input.value = pasteData[i];
                    input.classList.add('filled');
                });
                inputs[5].focus();
            }
        });
        
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        input.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    let timeLeft = <?php echo $time_left; ?>;
    const countdown = document.getElementById('countdown');
    
    const timer = setInterval(() => {
        timeLeft--;
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        countdown.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        
        if (timeLeft <= 60 && timeLeft > 30) {
            countdown.style.color = '#f59e0b';
        } else if (timeLeft <= 30 && timeLeft > 0) {
            countdown.style.color = '#ef4444';
            countdown.style.animation = 'pulse 1s ease-in-out infinite';
        }
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            countdown.textContent = 'Expired';
            countdown.style.color = '#ef4444';
            verifyBtn.disabled = true;
            verifyBtn.classList.add('expired');
            verifyBtn.querySelector('.btn-text').innerHTML = '<i class="fas fa-times"></i> Expired';
        }
    }, 1000);
    </script>
</body>
</html>
