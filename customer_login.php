<?php 
session_start();
if (isset($_SESSION['customer_id'])) {
    header('Location: customer_dashboard.php');
    exit();
}
require_once 'include/head.php';
require_once 'database/db_connection.php';

if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $_SESSION['error'] = "Session expired due to inactivity. Please login again.";
}

$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$lock_check = checkLoginLock($conn, '', $ip_address);
$is_locked = $lock_check['locked'];
$lock_minutes = $lock_check['minutes'] ?? 0;
if ($is_locked) {
    $_SESSION['error'] = "Too many failed attempts. Account locked for " . $lock_minutes . " minute(s). Please try again later.";
}
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
    padding: 20px;
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
.login-wrapper {
    display: flex;
    width: 900px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    position: relative;
    z-index: 1;
    animation: slideUp 0.6s ease-out;
}

.login-left {
    width: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 50px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    min-height: 400px;
    text-align: center;
}

.login-left h1 {
    font-size: 32px;
    margin-bottom: 20px;
    font-weight: 700;
}

.login-left p {
    font-size: 15px;
    line-height: 1.7;
    opacity: 0.9;
}

.login-right {
    width: 50%;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
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
.login-box h2,
.login-right h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
    width: 100%;
}
.avatar-container {
    text-align: center;
    margin-bottom: 20px;
    width: 100%;
    opacity: 0;
    transform: scale(0.8);
    animation: scaleIn 0.5s ease forwards;
    animation-delay: 0.2s;
}
@keyframes scaleIn {
    to { opacity: 1; transform: scale(1); }
}
.avatar-container img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid #667eea;
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.4);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
    font-size: 14px;
}
.input-group {
    position: relative;
    opacity: 0;
    transform: translateY(10px);
    animation: fadeInUp 0.5s ease forwards;
}
.input-group:nth-child(1) { animation-delay: 0.1s; }
.input-group:nth-child(2) { animation-delay: 0.2s; }
.input-group:nth-child(3) { animation-delay: 0.3s; }
@keyframes fadeInUp {
    to { opacity: 1; transform: translateY(0); }
}
.input-group input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8f9fa;
}
.input-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    background: white;
}
.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #667eea;
    z-index: 10;
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
.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}
.login-btn:active {
    transform: translateY(0) scale(0.98);
}
.login-btn.loading {
    pointer-events: none;
    opacity: 0.8;
}
.login-btn.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}
.register-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    width: 100%;
    color: #4b5563;
    font-size: 14px;
}
.register-link a {
    color: #4f46e5;
    text-decoration: none;
    font-weight: 600;
}
.register-link a:hover {
    color: #3730a3;
    text-decoration: underline;
}
.alert {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
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
body.dark-mode .login-wrapper {
    background: #1e293b;
}
body.dark-mode .login-right {
    background: #1e293b;
}
body.dark-mode .login-box {
    background: #1e293b;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}
body.dark-mode .login-box h2,
body.dark-mode .login-right h2 {
    color: #f1f5f9;
}
body.dark-mode .form-group label {
    color: #e2e8f0;
}
body.dark-mode .input-group input {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
}
body.dark-mode .input-group input:focus {
    background: #475569;
    border-color: #667eea;
}
body.dark-mode .register-link {
    border-top-color: #334155;
}
body.dark-mode .register-link a {
    color: #818cf8;
}
body.dark-mode .register-link a.text-success {
    color: #34d399 !important;
}
</style>

<body>
    <div class="login-container">
        <div class="login-wrapper">
            <div class="login-left">
                <div>
                    <h1>FundHarmony</h1>
                    <p>A secure microfinance platform that helps users manage loans and payments easily.</p>
                </div>
            </div>
            <div class="login-right">
                <h2><i class="fas fa-sign-in-alt"></i> User Login</h2>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <div class="avatar-container">
                <img src="assets/img/logo.png" alt="Avatar">
            </div>
            
            <form action="app/customer_loginHandler.php" method="post" autocomplete="off" style="width: 100%; max-width: 350px;">
                <?php
                $saved_email = isset($_COOKIE['remember_username']) ? $_COOKIE['remember_username'] : '';
                $saved_password = isset($_COOKIE['remember_password']) ? $_COOKIE['remember_password'] : '';
                $remember_checked = !empty($saved_email) ? 'checked' : '';
                ?>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <div class="input-group">
                        <input type="email" placeholder="Enter Email" name="email" value="<?php echo htmlspecialchars($saved_email); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="psw"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-group">
                        <input type="password" placeholder="Enter Password" name="psw" id="password" value="<?php echo htmlspecialchars($saved_password); ?>" required>
                        <span class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" name="login" class="login-btn" <?php echo $is_locked ? 'disabled' : ''; ?>>
                    <i class="fas fa-sign-in-alt"></i> <?php echo $is_locked ? 'Account Locked' : 'Login'; ?>
                </button>
                
                <div class="remember-me" style="display: flex; align-items: center; margin-top: 15px;">
                    <input type="checkbox" id="remember" name="remember" style="margin-right: 8px; accent-color: #667eea;" <?php echo $remember_checked; ?>>
                    <label for="remember" style="color: #666; font-size: 14px;">Remember me</label>
                </div>
            </form>
            
            <div class="forgot-password" style="text-align: center; margin-top: 15px;">
                <a href="forgot_password.php"><i class="fas fa-question-circle"></i> Forgot password?</a>
            </div>
            
            <div class="register-link">
                Don't have an account? <a href="customer_register.php">Register here</a>
            </div>
            </div>
        </div>
    </div>
    
    <script>
    // Dark mode disabled for user login page
    
    // Login button loading animation
    document.querySelector('form').addEventListener('submit', function(e) {
        const btn = document.querySelector('.login-btn');
        if (!btn.disabled) {
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        }
    });
    
    function togglePassword() {
        var passwordInput = document.getElementById('password');
        var eyeIcon = document.getElementById('eye-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
