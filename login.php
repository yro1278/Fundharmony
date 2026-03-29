<?php 
session_start();
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

require_once 'include/head.php' ?>
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
    padding: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    justify-content: center;
    align-items: center;
    color: white;
    min-height: 400px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.login-left::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
    animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
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

.login-left .saved-profile {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 30px;
}

.login-left .saved-profile img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid white;
}

.login-left .saved-profile .profile-info {
    flex: 1;
}

.login-left .saved-profile .profile-info .name {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 3px;
}

.login-left .saved-profile .profile-info .email {
    font-size: 13px;
    opacity: 0.8;
}

.login-right {
    width: 50%;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-right h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
    animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.avatar-container {
    text-align: center;
    margin-bottom: 20px;
    animation: fadeIn 0.6s ease-out 0.3s both, float 3s ease-in-out 0.9s infinite;
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

.login-btn:disabled .btn-loader {
    color: white;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.login-btn:active {
    transform: translateY(0);
}

.checkbox-group {
    display: flex;
    align-items: center;
    margin: 15px 0;
    animation: fadeIn 0.6s ease-out 0.6s both;
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 8px;
    cursor: pointer;
    accent-color: #667eea;
}

.checkbox-group label {
    color: #666;
    font-size: 14px;
    cursor: pointer;
}

.forgot-password {
    text-align: center;
    margin-top: 15px;
    animation: fadeIn 0.6s ease-out 0.7s both;
}

.forgot-password a {
    color: #667eea;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.forgot-password a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.register-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    animation: fadeIn 0.6s ease-out 0.8s both;
}

.register-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.register-link a:hover {
    color: #764ba2;
}

.alert {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
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

.floating-shapes {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;
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

/* Dark Mode Styles */
body.dark-mode { background: #0f172a; color: #e2e8f0; }
body.dark-mode .login-right { background: #1e293b; }
body.dark-mode .login-right h2 { color: #e2e8f0; }
body.dark-mode .form-group label { color: #e2e8f0; }
body.dark-mode .form-group input { background: #334155; border-color: #475569; color: #f1f5f9; }
body.dark-mode .form-group input::placeholder { color: #94a3b8; }
body.dark-mode .input-group-text { background: #334155; border-color: #475569; color: #94a3b8; }
body.dark-mode .alert { color: #f1f5f9; }
body.dark-mode .alert-danger { background: #7f1d1d; color: #fca5a5; border-color: #991b1b; }
body.dark-mode .alert-warning { background: #78350f; color: #fcd34d; border-color: #92400e; }
body.dark-mode .alert-success { background: #064e3b; color: #6ee7b7; border-color: #065f46; }
body.dark-mode .forgot-password a { color: #818cf8; }
body.dark-mode .forgot-password a:hover { color: #a5b4fc; }
body.dark-mode .login-btn { background: #4f46e5; color: white; }
body.dark-mode .login-btn:hover { background: #4338ca; }
body.dark-mode .lockout-timer-display { background: #7f1d1d; color: #fca5a5; }
body.dark-mode .text-muted { color: #94a3b8 !important; }
body.dark-mode .avatar-container img { border-color: #4f46e5; }
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
                <h2><i class="fas fa-sign-in-alt"></i> Admin Login</h2>
            
            <?php if(isset($_SESSION['error'])): ?>
                <?php 
                    $error_msg = $_SESSION['error'];
                    $is_lockout_error = strpos($error_msg, 'locked') !== false || strpos($error_msg, 'Too many failed') !== false;
                ?>
                <div class="alert <?php echo $is_lockout_error ? 'alert-warning' : 'alert-danger'; ?>">
                    <i class="fas <?php echo $is_lockout_error ? 'fa-lock' : 'fa-exclamation-circle'; ?>"></i> <?php echo $error_msg; unset($_SESSION['error']); ?>
                </div>
                <?php 
                $lock_info = isset($_SESSION['locked_until']) ? $_SESSION['locked_until'] : null;
                if ($lock_info): 
                ?>
                <div class="lockout-timer-display" style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-clock"></i> Time remaining: <span id="lockout-countdown" data-locktime="<?php echo $lock_info; ?>"></span>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['register_success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['register_success']; unset($_SESSION['register_success']); ?>
                </div>
            <?php endif; ?>
            
            <div class="avatar-container">
                <img src="assets/img/logo.png" alt="Avatar">
            </div>
            
            <form action="app/loginHandler.php" method="post" autocomplete="off" id="loginForm">
                <div class="form-group">
                    <label for="uname"><i class="fas fa-envelope"></i> Email</label>
                    <div class="input-group">
                        <input type="email" placeholder="Enter Email" name="uname" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="psw"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-group">
                        <input type="password" placeholder="Enter Password" name="psw" id="password" required>
                        <span class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" name="login" class="login-btn" id="loginBtn" <?php echo $is_locked ? 'disabled' : ''; ?>>
                    <span class="btn-text"><i class="fas fa-sign-in-alt"></i> <?php echo $is_locked ? 'Account Locked' : 'Sign in'; ?></span>
                    <span class="btn-loader" style="display: none;"><i class="fas fa-circle-notch fa-spin"></i> Signing in...</span>
                </button>
                
                <div class="forgot-password">
                    <a href="forgot_password.php"><i class="fas fa-question-circle"></i> Forgot password?</a>
                </div>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php"><i class="fas fa-user-plus"></i> Register here</a>
            </div>
            </div>
        </div>
    </div>
    
    <script>
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
    
    document.getElementById('loginForm').addEventListener('submit', function() {
        var btn = document.getElementById('loginBtn');
        var btnText = btn.querySelector('.btn-text');
        var btnLoader = btn.querySelector('.btn-loader');
        
        btn.disabled = true;
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline-flex';
        btnLoader.style.alignItems = 'center';
        btnLoader.style.gap = '8px';
        
        btn.style.transform = 'scale(0.95)';
        btn.style.opacity = '0.8';
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const lockoutEl = document.getElementById('lockout-countdown');
        if (lockoutEl) {
            const lockTime = new Date(lockoutEl.dataset.locktime).getTime();
            function updateLockout() {
                const now = new Date().getTime();
                const remaining = lockTime - now;
                if (remaining > 0) {
                    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((remaining % (1000 * 60)) / 1000);
                    lockoutEl.textContent = minutes + 'm ' + (seconds < 10 ? '0' : '') + seconds + 's';
                } else {
                    lockoutEl.textContent = 'Expired';
                }
            }
            updateLockout();
            setInterval(updateLockout, 1000);
        }
    });
    </script>
</body>
</html>
