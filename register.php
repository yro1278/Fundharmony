<?php require_once 'include/head.php' ?>
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
    color: white;
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
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-box h2 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
    font-weight: 600;
    animation: fadeIn 0.6s ease-out 0.2s both;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.avatar-container {
    text-align: center;
    margin-bottom: 20px;
    animation: fadeIn 0.6s ease-out 0.3s both;
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
    animation: fadeIn 0.6s ease-out 0.4s both;
}

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
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.input-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    background: white;
}

.input-group input::placeholder {
    color: #aaa;
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
    transition: all 0.3s ease;
    animation: fadeIn 0.6s ease-out 0.5s both;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}

.login-btn:active {
    transform: translateY(0);
}

.login-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    animation: fadeIn 0.6s ease-out 0.7s both;
}

.login-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.login-link a:hover {
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

.password-requirements {
    font-size: 0.75rem;
    color: #666;
}

.password-rules li {
    color: #dc3545;
}

.password-rules li.text-success {
    color: #198754 !important;
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
body.dark-mode .alert { color: #f1f5f9; }
body.dark-mode .alert-danger { background: #7f1d1d; color: #fca5a5; border-color: #991b1b; }
body.dark-mode .alert-success { background: #064e3b; color: #6ee7b7; border-color: #065f46; }
body.dark-mode .login-btn { background: #4f46e5; color: white; }
body.dark-mode .login-btn:hover { background: #4338ca; }
body.dark-mode .password-requirements { background: #1e293b; color: #e2e8f0; border-color: #475569; }
body.dark-mode .password-requirements li { color: #94a3b8; }
body.dark-mode .password-requirements li.valid { color: #10b981; }
body.dark-mode .password-requirements li.invalid { color: #f87171; }
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
                <h1>FundHarmony</h1>
                <p>A secure microfinance platform that helps users manage loans and payments easily.</p>
            </div>
            <div class="login-right">
                <h2><i class="fas fa-user-plus"></i> Admin Registration</h2>
            
            <?php if(isset($_SESSION['register_error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['register_success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['register_success']; unset($_SESSION['register_success']); ?>
                </div>
            <?php endif; ?>
            
            <div class="avatar-container">
                <img src="assets/img/logo.png" alt="Avatar">
            </div>
            
            <form action="app/registerHandler.php" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="fullname"><i class="fas fa-user"></i> Full Name</label>
                    <div class="input-group">
                        <input type="text" placeholder="Enter Full Name" name="fullname" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                    <div class="input-group">
                        <input type="email" placeholder="Enter Email Address" name="email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contact"><i class="fas fa-phone"></i> Contact Number</label>
                    <div class="input-group">
                        <input type="text" placeholder="Enter Contact Number" name="contact" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username"><i class="fas fa-user-tag"></i> Username</label>
                    <div class="input-group">
                        <input type="text" placeholder="Enter Username" name="username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="psw"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-group">
                        <input type="password" placeholder="Enter Password (min 12 chars, uppercase, lowercase, number, special char)" name="psw" id="password" required minlength="12">
                        <span class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </span>
                    </div>
                    <div id="password-requirements" class="password-requirements mt-2">
                        <small class="d-block mb-1">Password must contain:</small>
                        <ul class="password-rules ps-3 mb-0">
                            <li id="req-length" class="text-danger"><i class="fas fa-times"></i> At least 12 characters</li>
                            <li id="req-upper" class="text-danger"><i class="fas fa-times"></i> At least 1 uppercase letter</li>
                            <li id="req-lower" class="text-danger"><i class="fas fa-times"></i> At least 1 lowercase letter</li>
                            <li id="req-number" class="text-danger"><i class="fas fa-times"></i> At least 1 number</li>
                            <li id="req-special" class="text-danger"><i class="fas fa-times"></i> At least 1 special character (!@#$%^&*)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_psw"><i class="fas fa-lock"></i> Confirm Password</label>
                    <div class="input-group">
                        <input type="password" placeholder="Confirm Password" name="confirm_psw" id="confirm_password" required>
                        <span class="toggle-password" onclick="toggleConfirmPassword()">
                            <i class="fas fa-eye" id="eye-icon-confirm"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" name="register" class="login-btn">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
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
    
    function toggleConfirmPassword() {
        var passwordInput = document.getElementById('confirm_password');
        var eyeIcon = document.getElementById('eye-icon-confirm');
        
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
    
    document.getElementById('password').addEventListener('input', function() {
        var password = this.value;
        
        var hasLength = password.length >= 12;
        var hasUpper = /[A-Z]/.test(password);
        var hasLower = /[a-z]/.test(password);
        var hasNumber = /[0-9]/.test(password);
        var hasSpecial = /[!@#$%^&*]/.test(password);
        
        updateRequirement('req-length', hasLength);
        updateRequirement('req-upper', hasUpper);
        updateRequirement('req-lower', hasLower);
        updateRequirement('req-number', hasNumber);
        updateRequirement('req-special', hasSpecial);
        
        var allValid = hasLength && hasUpper && hasLower && hasNumber && hasSpecial;
        document.querySelector('.login-btn').disabled = !allValid;
        if (!allValid) {
            document.querySelector('.login-btn').title = 'Please meet all password requirements';
        } else {
            document.querySelector('.login-btn').title = '';
        }
    });
    
    function updateRequirement(id, valid) {
        var element = document.getElementById(id);
        if (valid) {
            element.classList.remove('text-danger');
            element.classList.add('text-success');
            element.querySelector('i').classList.remove('fa-times');
            element.querySelector('i').classList.add('fa-check');
        } else {
            element.classList.remove('text-success');
            element.classList.add('text-danger');
            element.querySelector('i').classList.remove('fa-check');
            element.querySelector('i').classList.add('fa-times');
        }
    }
    
    document.querySelector('form').addEventListener('submit', function(e) {
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirm_password').value;
        
        var hasLength = password.length >= 12;
        var hasUpper = /[A-Z]/.test(password);
        var hasLower = /[a-z]/.test(password);
        var hasNumber = /[0-9]/.test(password);
        var hasSpecial = /[!@#$%^&*]/.test(password);
        
        if (!hasLength || !hasUpper || !hasLower || !hasNumber || !hasSpecial) {
            e.preventDefault();
            alert('Please meet all password requirements before registering.');
            return false;
        }
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match.');
            return false;
        }
    });
    </script>
</body>
</html>
