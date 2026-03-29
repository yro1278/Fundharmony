<?php 
session_start();
require_once 'database/db_connection.php';
require_once 'include/head.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'request';

$error = '';
$success = '';

if (isset($_SESSION['forgot_error'])) {
    $error = $_SESSION['forgot_error'];
    unset($_SESSION['forgot_error']);
}
if (isset($_SESSION['forgot_success'])) {
    $success = $_SESSION['forgot_success'];
    unset($_SESSION['forgot_success']);
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
}

.login-box {
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    padding: 40px;
    width: 400px;
    position: relative;
    z-index: 1;
}

.login-box h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
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

.form-group label i {
    margin-right: 5px;
    color: #667eea;
}

.input-group {
    position: relative;
}

.input-group input {
    width: 100%;
    padding: 12px 15px;
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
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}

.login-link {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.login-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.alert {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 20px;
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

.back-link {
    text-align: center;
    margin-top: 15px;
}

.back-link a {
    color: #667eea;
    text-decoration: none;
}
</style>

<body>
    <div class="login-container">
        <div class="login-box">
            <h2><i class="fas fa-key"></i> Reset Password</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if($page == 'request'): ?>
            <form action="app/forgot_passwordHandler.php" method="post">
                <p class="text-muted mb-3">Enter your email address to reset your password.</p>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>
                
                <button type="submit" name="request_reset" class="login-btn">
                    <i class="fas fa-paper-plane"></i> Send OTP
                </button>
                
                <div class="back-link">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </form>
            
            <?php elseif($page == 'otp'): ?>
            <?php 
            $otp_email = isset($_GET['email']) ? $_GET['email'] : ($_SESSION['otp_email'] ?? '');
            ?>
            <form action="app/forgot_passwordHandler.php" method="post">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($otp_email); ?>">
                
                <p class="text-muted mb-3">Enter the 6-digit OTP sent to your email.</p>
                
                <div class="form-group">
                    <label for="otp"><i class="fas fa-shield-alt"></i> OTP Code</label>
                    <input type="text" name="otp" id="otp" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" required pattern="[0-9]{6}">
                </div>
                
                <button type="submit" name="verify_otp" class="login-btn">
                    <i class="fas fa-check-circle"></i> Verify OTP
                </button>
                
                <div class="back-link">
                    <a href="forgot_password.php"><i class="fas fa-arrow-left"></i> Request New OTP</a>
                </div>
            </form>
            
<?php elseif($page == 'reset'): ?>
            <?php 
            $token = $_GET['token'] ?? '';
            $email = $_GET['email'] ?? '';
            
            if(empty($token) || empty($email)) {
                echo '<p class="text-danger">Invalid reset link.</p>';
                echo '<div class="back-link"><a href="login.php">Back to Login</a></div>';
            } else {
                $email_escaped = mysqli_real_escape_string($conn, $email);
                $token_escaped = mysqli_real_escape_string($conn, $token);
                
                $now = date('Y-m-d H:i:s');
                $check = mysqli_query($conn, "SELECT * FROM password_reset_tokens WHERE email = '$email_escaped' AND token = '$token_escaped' AND expires_at > '$now'");
                
                if(mysqli_num_rows($check) == 0) {
                    echo '<p class="text-danger">Invalid or expired reset link.</p>';
                    echo '<div class="back-link"><a href="login.php">Back to Login</a></div>';
                } else {
                    $token_data = mysqli_fetch_assoc($check);
            ?>
            <form action="app/reset_passwordHandler.php" method="post">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <p class="text-muted mb-3">Create your new password below.</p>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password (min 12 chars)" required minlength="12">
                    <div id="password-requirements" class="password-requirements mt-2">
                        <small class="d-block mb-1">Password must contain:</small>
                        <ul class="password-rules ps-3 mb-0" style="font-size: 0.75rem;">
                          <li id="req-length" class="text-danger"><i class="fas fa-times"></i> At least 12 characters</li>
                          <li id="req-upper" class="text-danger"><i class="fas fa-times"></i> At least 1 uppercase letter</li>
                          <li id="req-lower" class="text-danger"><i class="fas fa-times"></i> At least 1 lowercase letter</li>
                          <li id="req-number" class="text-danger"><i class="fas fa-times"></i> At least 1 number</li>
                          <li id="req-special" class="text-danger"><i class="fas fa-times"></i> At least 1 special character (!@#$%^&*)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password" required>
                </div>
                
                <button type="submit" name="reset_password" class="login-btn" id="submit-btn" disabled>
                    <i class="fas fa-save"></i> Reset Password
                </button>
                
                <div class="back-link">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </form>
            <?php } } endif; ?>
        </div>
    </div>
    
    <script>
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
        document.getElementById('submit-btn').disabled = !allValid;
    });
    
    function updateRequirement(id, valid) {
        var element = document.getElementById(id);
        if (!element) return;
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
            alert('Please meet all password requirements.');
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
