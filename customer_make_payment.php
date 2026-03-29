<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}
require_once 'include/head.php';
require_once 'database/db_connection.php';

$customer_id = $_SESSION['customer_id'];

$check_active = mysqli_query($conn, "SELECT a.account_number FROM accounts a LEFT JOIN account_status acs ON a.account_status = acs.account_status_number WHERE a.customer = '$customer_id' AND acs.account_status_name IN ('Active', 'Partial', 'Up to Date', 'Due Date') AND a.loan_balance > 0 LIMIT 1");
$has_active_loan = mysqli_num_rows($check_active) > 0;

// Check if customer account is active
$customer_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active FROM customers WHERE customer_number = '$customer_id'"));
$is_account_active = isset($customer_check['is_active']) ? $customer_check['is_active'] : 1;

if(!$is_account_active) {
    $_SESSION['loan_error'] = "Your account has been deactivated. Please contact the admin for assistance.";
    header('Location: customer_my_loans.php');
    exit();
}

$accounts = mysqli_query($conn, "SELECT a.*, act.account_type_name, acs.account_status_name
FROM accounts a
LEFT JOIN account_type act ON a.account_type = act.account_type_number
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.customer = '$customer_id' AND (acs.account_status_name IN ('Active', 'Up to Date', 'Due Date', 'Partial', 'Approved'))");

if(isset($_POST['pay'])) {
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    $payment_amount = floatval($_POST['payment_amount']);
    $payment_date = date('Y-m-d');
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Generate unique payment number with collision check
    do {
        $payment_number = rand(100000, 999999);
        $check_duplicate = mysqli_query($conn, "SELECT payment_number FROM payments WHERE payment_number = '$payment_number'");
    } while (mysqli_num_rows($check_duplicate) > 0);
    
    // Verify loan belongs to customer
    $loan_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.account_number, acs.account_status_name FROM accounts a LEFT JOIN account_status acs ON a.account_status = acs.account_status_number WHERE a.account_number = '$account_number' AND a.customer = '$customer_id'"));
    if (!$loan_check || $loan_check['account_status_name'] === 'Approved') {
        $error = "You must confirm your loan before making a payment. Please go to My Loans page.";
    } else {
        $loan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT loan_balance, loan_amount, overdue_interest FROM accounts WHERE account_number = '$account_number'"));
        $current_balance = floatval($loan['loan_balance'] ?? $loan['loan_amount'] ?? 0);
        $overdue_interest = floatval($loan['overdue_interest'] ?? 0);
        $total_due = $current_balance + $overdue_interest;
        
        if($total_due > 0 && $payment_amount > $total_due) {
            $error = "Payment amount cannot exceed the total balance (including overdue) of ₱" . number_format($total_due, 2);
        } else {
            $new_balance = $current_balance - $payment_amount;
            if ($new_balance < 0) $new_balance = 0;
            
            // Process bank/ewallet details for notes
            $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name'] ?? '');
            $bank_account = mysqli_real_escape_string($conn, $_POST['bank_account'] ?? '');
            $reference_number = mysqli_real_escape_string($conn, $_POST['reference_number'] ?? '');
            $ewallet_type = mysqli_real_escape_string($conn, $_POST['ewallet_type'] ?? '');
            $ewallet_number = mysqli_real_escape_string($conn, $_POST['ewallet_number'] ?? '');
            
            $payment_details = '';
            if($payment_method == 'Bank Transfer') {
                $payment_details = "Bank: $bank_name, Account: $bank_account, Ref: $reference_number";
            } elseif($payment_method == 'E-Wallet') {
                $payment_details = "Type: $ewallet_type, Number: $ewallet_number, Ref: $reference_number";
            }
            
            $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
            $notes = $notes . ($payment_details ? ' | ' . $payment_details : '');
            
            $user_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM customers WHERE customer_number = '$customer_id'"))['user_id'] ?? 0;
            
            // Insert payment ONCE
            $insert_result = mysqli_query($conn, "INSERT INTO payments (payment_number, account_number, payment_amount, payment_date, payment_method, notes, user_id) VALUES ('$payment_number', '$account_number', '$payment_amount', '$payment_date', '$payment_method', '$notes', '$user_id')");
            
            if (!$insert_result) {
                $error = "Payment failed: " . mysqli_error($conn);
            } else {
                // Payment inserted successfully, now update balance
                mysqli_query($conn, "UPDATE accounts SET loan_balance = '$new_balance', overdue_interest = 0 WHERE account_number = '$account_number'");
                
                $closed_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Closed'"));
                $closed_id = $closed_status['account_status_number'] ?? null;
                
                if ($new_balance <= 0 && $closed_id) {
                    mysqli_query($conn, "UPDATE accounts SET account_status = '$closed_id' WHERE account_number = '$account_number'");
                } elseif ($new_balance <= 0) {
                    mysqli_query($conn, "UPDATE accounts SET account_status = 2 WHERE account_number = '$account_number'");
                } else {
                    $partial_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Partial'"));
                    $partial_id = $partial_status['account_status_number'] ?? 5;
                    
                    $up_to_date_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Up to Date'"));
                    $up_to_date_id = $up_to_date_status['account_status_number'] ?? 7;
                    
                    $due_date_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Due Date'"));
                    $due_date_id = $due_date_status['account_status_number'] ?? 6;
                    
                    $account_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT loan_amount, interest, loan_term, open_date FROM accounts WHERE account_number = '$account_number'"));
                    $loan_amount = floatval($account_info['loan_amount'] ?? 0);
                    $interest = floatval($account_info['interest'] ?? 0);
                    $loan_term = intval($account_info['loan_term'] ?? 1);
                    $open_date = $account_info['open_date'] ?? date('Y-m-d');
                    
                    $total_due_calc = $loan_amount + $interest;
                    $monthly_payment = $loan_term > 0 ? $total_due_calc / $loan_term : $total_due_calc;
                    
                    $start_date = new DateTime($open_date);
                    $current_date = new DateTime();
                    $months_passed = (($current_date->format('Y') - $start_date->format('Y')) * 12) + ($current_date->format('n') - $start_date->format('n'));
                    
                    $total_paid_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(payment_amount) as total_paid FROM payments WHERE account_number = '$account_number'"));
                    $total_paid = floatval($total_paid_result['total_paid'] ?? 0);
                    
                    $expected_payment_for_month = $monthly_payment * ($months_passed + 1);
                    
                    if ($total_paid >= $expected_payment_for_month && $up_to_date_id) {
                        mysqli_query($conn, "UPDATE accounts SET account_status = '$up_to_date_id' WHERE account_number = '$account_number'");
                    } elseif ($total_paid > 0 && $partial_id) {
                        mysqli_query($conn, "UPDATE accounts SET account_status = '$partial_id' WHERE account_number = '$account_number'");
                    } elseif ($due_date_id) {
                        mysqli_query($conn, "UPDATE accounts SET account_status = '$due_date_id' WHERE account_number = '$account_number'");
                    }
                }
                
                // Log activity
                $customer_name = $_SESSION['customer_name'] ?? 'customer';
                logActivity($conn, $customer_id, $customer_name, 'User Payment', 'Made payment of ₱' . number_format($payment_amount, 2) . ' for account ID: ' . $account_number, 'customer');
                
                $success = "Payment of ₱" . number_format($payment_amount, 2) . " submitted successfully!";
            }
        }
    }
}
?>
<style>
body { background: #f0f2f5; font-family: 'Segoe UI', system-ui, sans-serif; }
body.dark-mode { background: #0f172a !important; }
.sidebar { position: fixed; top: 0; left: -260px; width: 260px; height: 100vh; background: white; box-shadow: 2px 0 15px rgba(0,0,0,0.1); z-index: 1050; transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto; }
.sidebar.show { left: 0; }
.sidebar-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; display: none; opacity: 0; transition: opacity 0.3s ease; }
.sidebar-overlay.show { display: block; opacity: 1; }
.sidebar-header { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.sidebar-header .brand { font-weight: 700; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
.sidebar-menu { padding: 15px; }
.sidebar-menu a { display: flex; align-items: center; padding: 12px 15px; color: #555; text-decoration: none; border-radius: 10px; margin-bottom: 5px; font-weight: 500; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
.sidebar-menu a::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: -1; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: translateX(5px); }
.sidebar-menu a:hover::before { width: 100%; }
.sidebar-menu a i { width: 25px; margin-right: 10px; }
.topbar { position: fixed; top: 0; left: 0; right: 0; height: 60px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: space-between; padding: 0 25px; z-index: 1030; transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.topbar-brand { position: absolute; left: 50%; transform: translateX(-50%); font-weight: 700; font-size: 1.3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; display: flex; align-items: center; }
.topbar-brand i { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.topbar.sidebar-open { left: 260px; }
.menu-toggle { background: none; border: none; font-size: 1.25rem; color: #555; cursor: pointer; padding: 8px; border-radius: 8px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.menu-toggle:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; transform: scale(1.1); }
.main-content { margin-left: 0; padding: 80px 25px 25px; transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1); min-height: 100vh; }
.main-content.sidebar-open { margin-left: 260px; }
.pay-card { background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); padding: 30px; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; }
.form-group { margin-bottom: 18px; }
.form-group label { font-weight: 600; color: #333; margin-bottom: 8px; display: block; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: white; color: #333; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
.btn-primary { background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; padding: 14px 30px; border-radius: 10px; font-weight: 600; width: 100%; }
.alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
.btn-secondary { background: #6c757d; border: none; padding: 14px 30px; border-radius: 10px; font-weight: 600; width: 100%; color: white; }
.theme-toggle-btn { background: none; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #555; font-weight: 500; }
.theme-toggle-btn:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }

/* Dark Mode */
body.dark-mode { background: #0f172a !important; color: #e2e8f0 !important; }
body.dark-mode .main-content { background: #0f172a !important; }
body.dark-mode .topbar { background: #1e293b !important; }
body.dark-mode .sidebar { background: #1e293b !important; }
body.dark-mode .sidebar-menu a { color: #e2e8f0 !important; }
body.dark-mode .sidebar-menu a:hover, body.dark-mode .sidebar-menu a.active { background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%); color: white !important; }
body.dark-mode .pay-card { 
    background: #1e293b !important; 
    color: #e2e8f0 !important; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.3); 
    border: 1px solid #334155;
}
body.dark-mode .form-group label { color: #e2e8f0 !important; }
body.dark-mode .form-group input, 
body.dark-mode .form-group select, 
body.dark-mode .form-group textarea { 
    background: #334155 !important; 
    color: #f1f5f9 !important; 
    border-color: #475569 !important; 
}
body.dark-mode .form-group select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}
body.dark-mode h4, body.dark-mode .text-muted, body.dark-mode p, body.dark-mode span { color: #e2e8f0 !important; }
body.dark-mode .alert-success { background: #064e3b !important; color: #6ee7b7 !important; }
body.dark-mode .alert-danger { background: #7f1d1d !important; color: #fca5a5 !important; }
body.dark-mode .btn-secondary { background: #475569 !important; color: white !important; }
body.dark-mode .theme-toggle-btn { color: #e2e8f0 !important; }
body.dark-mode .topbar-brand { color: #e2e8f0 !important; }
body.dark-mode .menu-toggle { color: #e2e8f0 !important; }
body.dark-mode #currentBalance { background: #334155 !important; color: #f1f5f9 !important; }
body.dark-mode .sidebar-overlay { background: rgba(0,0,0,0.6) !important; }
body.dark-mode html { background: #0f172a !important; }
body.dark-mode { min-height: 100vh; }

/* Sidebar User Section */
.sidebar-user-section { position: absolute; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e7eb; padding: 15px; }
.sidebar-user-profile { display: flex; align-items: center; padding: 10px; margin-bottom: 10px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 10px; }
.sidebar-user-icon { width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; }
.sidebar-user-icon i { color: white; font-size: 22px; }
.sidebar-user-info { overflow: hidden; }
.sidebar-user-name { font-weight: 600; color: #1f2937; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar-user-email { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar-user-actions { display: flex; gap: 5px; }
.sidebar-user-actions a { flex: 1; display: flex; align-items: center; justify-content: center; padding: 10px; color: #555; text-decoration: none; font-weight: 500; font-size: 12px; border-radius: 8px; transition: all 0.2s; }
.sidebar-user-actions a:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }
.sidebar-user-actions a.text-danger:hover { background: #fee2e2; color: #dc2626; }
.sidebar-user-actions a i { margin-right: 5px; }
.sidebar { overflow-y: auto; }
body.dark-mode .sidebar-user-section { background: #1e293b; border-top-color: #334155; }
body.dark-mode .sidebar-user-profile { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
body.dark-mode .sidebar-user-name { color: #f1f5f9; }
body.dark-mode .sidebar-user-email { color: #94a3b8; }
body.dark-mode .sidebar-user-actions a { color: #e2e8f0; }
body.dark-mode .sidebar-user-actions a:hover { background: #334155; color: #818cf8; }
body.dark-mode .sidebar-user-actions a.text-danger:hover { background: #7f1d1d; color: #fca5a5; }
body.dark-mode .sidebar-user-actions a.text-danger { color: #f87171; }
</style>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">FundHarmony</div>
        </div>
        <div class="sidebar-menu">
            <a href="customer_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <?php if(!$has_active_loan): ?><a href="customer_apply_loan.php"><i class="fas fa-file-signature"></i> Apply for Loan</a><?php else: ?><a href="#" style="opacity:0.5;cursor:not-allowed;" title="You have an existing loan application"><i class="fas fa-file-signature"></i> Apply for Loan <i class="fas fa-lock fa-xs"></i></a><?php endif; ?>
            <a href="customer_my_loans.php"><i class="fas fa-money-check-alt"></i> My Loans</a>
            <a href="customer_make_payment.php" class="active"><i class="fas fa-credit-card"></i> Make Payment</a>
            <a href="customer_payment_history.php"><i class="fas fa-history"></i> Payment History</a>
        </div>
        <!-- User Profile Section at bottom of sidebar -->
        <div class="sidebar-user-section">
            <div class="sidebar-user-profile">
                <div class="sidebar-user-icon"><i class="fas fa-user-circle"></i></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Customer'); ?></div>
                    <div class="sidebar-user-email"><?php echo htmlspecialchars($_SESSION['customer_email'] ?? 'customer@email.com'); ?></div>
                </div>
            </div>
            <div class="sidebar-user-actions">
                <a href="customer_profile.php"><i class="fas fa-id-card"></i> My Profile</a>
                <a href="my_qr_code.php"><i class="fas fa-qrcode"></i> My QR Code</a>
                <a href="customer_logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Log out</a>
            </div>
        </div>
    </div>

    <!-- Topbar -->
    <div class="topbar" id="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <div class="topbar-brand">FundHarmony</div>
        <div></div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <h4 class="mb-4"><i class="fas fa-credit-card me-2"></i>Make Payment</h4>
        
        <?php if(isset($success)): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="pay-card">
            <form method="post" onsubmit="return validateFormSubmit()">
                <div class="form-group">
                    <label>Select Loan Account</label>
                    <?php if(mysqli_num_rows($accounts) > 0): ?>
                    <select name="account_number" id="accountSelect" onchange="updateBalance()" required>
                        <option value="">-- Select Account --</option>
                        <?php while($acc = mysqli_fetch_assoc($accounts)): 
                            $balance = floatval($acc['loan_balance'] ?? $acc['loan_amount']);
                            $overdue = floatval($acc['overdue_interest'] ?? 0);
                            $total = $balance + $overdue;
                            if($total > 0):
                        ?>
                            <option value="<?php echo $acc['account_number']; ?>" data-balance="<?php echo $balance; ?>" data-overdue="<?php echo $overdue; ?>">
                                <?php echo $acc['account_number']; ?> - <?php echo $acc['account_type_name']; ?> | Total Due: ₱<?php echo number_format($total, 2); ?>
                            </option>
                        <?php endif; endwhile; ?>
                    </select>
                    <?php else: ?>
                    <p class="text-muted">No active loans found.</p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group" id="balanceDisplay" style="display:none;">
                    <label>Current Balance</label>
                    <input type="text" id="currentBalance" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label>Payment Amount (₱)</label>
                    <input type="number" name="payment_amount" id="paymentAmount" step="0.01" placeholder="Enter amount" required oninput="validatePaymentAmount()">
                    <small id="amountWarning" class="text-danger" style="display:none;"></small>
                </div>
                
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" id="paymentMethod" onchange="toggleFields()" required>
                        <option value="">-- Select Method --</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
                
                <div id="bankFields" style="display:none;">
                    <div class="form-group"><label>Bank Name</label><input type="text" name="bank_name" placeholder="Bank name"></div>
                    <div class="form-group"><label>Account Number</label><input type="text" name="bank_account" placeholder="Account number"></div>
                    <div class="form-group"><label>Reference Number</label><input type="text" name="reference_number" placeholder="Reference number"></div>
                </div>
                
                <div id="ewalletFields" style="display:none;">
                    <div class="form-group">
                        <label>E-Wallet Type</label>
                        <select name="ewallet_type">
                            <option value="GCash">GCash</option>
                            <option value="PayMaya">PayMaya</option>
                            <option value="ShopeePay">ShopeePay</option>
                        </select>
                    </div>
                    <div class="form-group"><label>E-Wallet Number</label><input type="text" name="ewallet_number" placeholder="E-wallet number"></div>
                    <div class="form-group"><label>Reference Number</label><input type="text" name="reference_number" placeholder="Reference number"></div>
                </div>
                
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Additional notes..."></textarea>
                </div>
                
                <button type="submit" name="pay" class="btn-primary">Submit Payment</button>
            </form>
        </div>
    </div>

    <script>
    var currentTotalDue = 0;
    
    function updateBalance() {
        var select = document.getElementById('accountSelect');
        var option = select.options[select.selectedIndex];
        var balance = option.getAttribute('data-balance') || 0;
        var overdue = option.getAttribute('data-overdue') || 0;
        currentTotalDue = parseFloat(balance) + parseFloat(overdue);
        
        document.getElementById('balanceDisplay').style.display = select.value ? 'block' : 'none';
        document.getElementById('currentBalance').value = '₱' + currentTotalDue.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        validatePaymentAmount();
    }
    
    function validatePaymentAmount() {
        var amountInput = document.getElementById('paymentAmount');
        var warning = document.getElementById('amountWarning');
        var amount = parseFloat(amountInput.value);
        
        if (amount > 0 && currentTotalDue > 0 && amount > currentTotalDue) {
            warning.textContent = 'Payment amount (₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2}) + ') exceeds the total balance due (₱' + currentTotalDue.toLocaleString('en-US', {minimumFractionDigits: 2}) + '). Please enter a valid amount.';
            warning.style.display = 'block';
            amountInput.setCustomValidity('Payment amount exceeds balance');
        } else {
            warning.style.display = 'none';
            amountInput.setCustomValidity('');
        }
    }
    
    function validateFormSubmit() {
        var amountInput = document.getElementById('paymentAmount');
        var amount = parseFloat(amountInput.value);
        
        if (amount > 0 && currentTotalDue > 0 && amount > currentTotalDue) {
            alert('Invalid Payment Amount!\n\nThe amount you entered (₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2}) + ') exceeds your total balance due (₱' + currentTotalDue.toLocaleString('en-US', {minimumFractionDigits: 2}) + ').\n\nPlease enter a valid payment amount not exceeding your balance.');
            return false;
        }
        return true;
    }
    
    function toggleFields() {
        var method = document.getElementById('paymentMethod').value;
        document.getElementById('bankFields').style.display = method == 'Bank Transfer' ? 'block' : 'none';
        document.getElementById('ewalletFields').style.display = method == 'E-Wallet' ? 'block' : 'none';
    }
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const mainContent = document.getElementById('mainContent');
        const topbar = document.getElementById('topbar');
        
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        mainContent.classList.toggle('sidebar-open');
        topbar.classList.toggle('sidebar-open');
    }
    function toggleTheme() {
        const body = document.body; const html = document.documentElement;
        const icon = document.getElementById('theme-icon');
        
        body.classList.toggle('dark-mode');
        html.classList.toggle('dark-mode-bg');
        
        if (body.classList.contains('dark-mode')) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            localStorage.setItem('theme', 'dark');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            localStorage.setItem('theme', 'light');
        }
    }
    (function() {
        const savedTheme = localStorage.getItem('theme');
        const icon = document.getElementById('theme-icon');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            document.documentElement.classList.add('dark-mode-bg');
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    })();
    </script>
</body>
</html>
