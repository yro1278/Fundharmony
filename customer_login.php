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


@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.5); }
    to { opacity: 1; transform: scale(1); }
}

.login-left h1 {
    font-size: 32px;
    margin-bottom: 20px;
    font-weight: 700;
    animation: slideRight 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
}

@keyframes slideRight {
    from { opacity: 0; transform: translateX(-30px); }
    to { opacity: 1; transform: translateX(0); }
}

.login-left p {
    font-size: 15px;
    line-height: 1.7;
    opacity: 0;
    animation: fadeIn 0.8s ease-out 0.5s both;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
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
.login-right h2 .header-badge,
.login-box h2 .header-badge {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 25px;
    border-radius: 25px;
    font-size: 18px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}
.avatar-container {
    text-align: center;
    margin-bottom: 20px;
    animation: fadeIn 0.6s ease-out 0.3s both, float 3s ease-in-out 0.9s infinite;
}
.avatar-icon {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 4px solid #667eea;
    box-shadow: 0 0 25px rgba(102, 126, 234, 0.5), 0 0 50px rgba(102, 126, 234, 0.25);
    animation: float 3s ease-in-out infinite, glow 2s ease-in-out infinite alternate;
}
.avatar-icon i {
    font-size: 40px;
    color: white;
}
.avatar-container img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid #667eea;
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.4), 0 0 40px rgba(102, 126, 234, 0.2);
    transition: all 0.4s ease;
    animation: float 3s ease-in-out infinite, glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from {
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.4), 0 0 40px rgba(102, 126, 234, 0.2);
    }
    to {
        box-shadow: 0 0 30px rgba(102, 126, 234, 0.6), 0 0 60px rgba(102, 126, 234, 0.3);
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.login-right h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
    animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
}

.form-group {
    margin-bottom: 20px;
    animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) 0.4s both;
}

.form-group:nth-of-type(1) { animation-delay: 0.4s; }
.form-group:nth-of-type(2) { animation-delay: 0.5s; }
.form-group:nth-of-type(3) { animation-delay: 0.6s; }

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
    font-size: 14px;
}
.form-group label i {
    margin-right: 5px;
    color: #667eea;
}
.input-group {
    position: relative;
}
.input-group input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
}
.input-group input::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.5s ease;
}
.input-group input:focus::before {
    left: 100%;
}
.input-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15), 0 8px 25px rgba(102, 126, 234, 0.2);
    background: white;
    transform: translateY(-2px);
}

.input-group input::placeholder {
    color: #aaa;
    transition: all 0.3s ease;
}

.input-group input:focus::placeholder {
    opacity: 0.6;
    transform: translateX(5px);
}
.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #667eea;
    z-index: 10;
    transition: all 0.3s ease;
}
.toggle-password:hover {
    color: #764ba2;
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
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeIn 0.6s ease-out 0.5s both;
    position: relative;
    overflow: hidden;
    z-index: 1;
}
.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    z-index: -1;
    transition: transform 0.4s ease;
    transform: scaleX(0);
    transform-origin: left;
}
.login-btn:hover::before {
    transform: scaleX(1);
}
.login-btn:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5), 0 8px 15px rgba(0, 0, 0, 0.1);
}
.login-btn:active {
    transform: translateY(-1px) scale(0.98);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}
.login-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
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
body.dark-mode .avatar-icon {
    border-color: #4f46e5;
    box-shadow: 0 0 25px rgba(79, 70, 229, 0.5), 0 0 50px rgba(79, 70, 229, 0.25);
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
body.dark-mode .login-box h2 .header-badge,
body.dark-mode .login-right h2 .header-badge {
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.5);
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
.floating-shapes {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;
    pointer-events: none;
}
.floating-shapes li {
    position: absolute;
    list-style: none;
    width: 20px;
    height: 20px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    animation: floatShapes 25s linear infinite;
}
.floating-shapes li:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
.floating-shapes li:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
.floating-shapes li:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
.floating-shapes li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
.floating-shapes li:nth-child(5) { left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
.floating-shapes li:nth-child(6) { left: 75%; width: 110px; height: 110px; animation-delay: 3s; }

@keyframes floatShapes {
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; }
}
</style>

<body>
    <div class="login-container">
        <ul class="floating-shapes">
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
        <div class="login-wrapper">
            <div class="login-left">
                <div>
                    <h1>FundHarmony</h1>
                    <p>A secure microfinance platform that helps users manage loans and payments easily.</p>
                </div>
            </div>
            <div class="login-right">
                <h2><span class="header-badge">User Login</span></h2>
            
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
                <div class="avatar-icon">
                    <i class="fas fa-user"></i>
                </div>
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
