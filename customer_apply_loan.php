<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}
require_once 'include/head.php';
require_once 'database/db_connection.php';

$customer_id = $_SESSION['customer_id'];

$check_unpaid = mysqli_query($conn, "SELECT a.account_number, acs.account_status_name FROM accounts a
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE a.customer = '$customer_id'
    AND acs.account_status_name IN ('Active', 'Pending', 'Approved', 'Partial', 'Up to Date', 'Due Date')
    LIMIT 1");

$has_unpaid_loan = mysqli_num_rows($check_unpaid) > 0;

// Check if customer account is active
$customer_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active FROM customers WHERE customer_number = '$customer_id'"));
$is_account_active = isset($customer_check['is_active']) ? $customer_check['is_active'] : 1;

if(!$is_account_active) {
    $_SESSION['loan_error'] = "Your account has been deactivated. Please contact the admin for assistance.";
    header('Location: customer_my_loans.php');
    exit();
}

if($has_unpaid_loan) {
    $_SESSION['loan_error'] = "You already have a loan application. Please wait for admin approval or rejection before applying for a new loan.";
    header('Location: customer_my_loans.php');
    exit();
}

$loan_types = mysqli_query($conn, "SELECT * FROM account_type WHERE LOWER(account_type_name) != 'other'");

$min_amount = 1000;
$max_amount = 1000000;

if(isset($_POST['apply'])) {
    $account_type = mysqli_real_escape_string($conn, $_POST['account_type']);
    $loan_amount = floatval($_POST['loan_amount']);
    $loan_term = intval($_POST['loan_term'] ?? 1);
    $disbursement_method = mysqli_real_escape_string($conn, $_POST['disbursement_method']);
    $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name'] ?? '');
    $disbursement_account = mysqli_real_escape_string($conn, $_POST['account_number_disburse'] ?? '');
    $disbursement_account_name = mysqli_real_escape_string($conn, $_POST['account_name_disburse'] ?? '');
    $branch = mysqli_real_escape_string($conn, $_POST['branch'] ?? '');
    $ewallet_type = mysqli_real_escape_string($conn, $_POST['ewallet_type'] ?? '');
    $ewallet_number = mysqli_real_escape_string($conn, $_POST['ewallet_number'] ?? '');
    $ewallet_account_name = mysqli_real_escape_string($conn, $_POST['ewallet_account_name'] ?? '');
    $pickup_location = mysqli_real_escape_string($conn, $_POST['pickup_location'] ?? '');
    
    // Get loan type name
    $loan_type_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_type_name FROM account_type WHERE account_type_number = '$account_type'"));
    $loan_type_name = $loan_type_row['account_type_name'] ?? '';
    
    // Define upload requirements based on loan type
    $upload_requirements = [];
    $loan_type_lower = strtolower($loan_type_name);
    
    if (strpos($loan_type_lower, 'emergency') !== false) {
        $upload_requirements = [
            'valid_id' => 'Valid ID',
            'active_member' => 'Proof of Active Membership'
        ];
    } elseif (strpos($loan_type_lower, 'personal') !== false) {
        $upload_requirements = [
            'valid_id' => 'Valid ID',
            'proof_of_income' => 'Proof of Income',
            'good_payment_history' => 'Good Payment History'
        ];
    } elseif (strpos($loan_type_lower, 'educational') !== false || strpos($loan_type_lower, 'education') !== false) {
        $upload_requirements = [
            'valid_id' => 'Valid ID',
            'proof_of_enrollment' => 'Proof of Enrollment / School ID',
            'active_member' => 'Active Member Account',
            'parents_id' => 'Valid ID of Parents/Guardian',
            'parents_income' => 'Proof of Income of Parents'
        ];
    } elseif (strpos($loan_type_lower, 'business') !== false) {
        $upload_requirements = [
            'valid_id' => 'Valid ID',
            'business_permit' => 'Business Permit',
            'proof_of_income' => 'Proof of Income'
        ];
    } else {
        // Default requirements
        $upload_requirements = [
            'valid_id' => 'Valid ID',
            'proof_of_income' => 'Proof of Income',
            'bank_statement' => 'Bank Statement'
        ];
    }
    
    if($loan_amount < $min_amount || $loan_amount > $max_amount) {
        $error = "Loan amount must be between " . number_format($min_amount) . " and " . number_format($max_amount);
    } elseif(empty($loan_term)) {
        $error = "Please select a loan term";
    } elseif(empty($disbursement_method)) {
        $error = "Please select a disbursement method";
    } else {
        $status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Pending' LIMIT 1"));
        $account_status = $status['account_status_number'] ?? 1;
        
        do {
            $account_number = rand(1000000, 9999999);
            $check = mysqli_query($conn, "SELECT account_number FROM accounts WHERE account_number = '$account_number'");
        } while(mysqli_num_rows($check) > 0);
        
        $user_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM customers WHERE customer_number = '$customer_id'"))['user_id'] ?? 0;
        
        $insert = "INSERT INTO accounts (account_number, user_id, customer, account_type, account_status, loan_amount, loan_term, disbursement_method, bank_name, disbursement_account, disbursement_account_name, branch, ewallet_type, ewallet_number, ewallet_account_name, pickup_location) 
                   VALUES ('$account_number', '$user_id', '$customer_id', '$account_type', '$account_status', '$loan_amount', '$loan_term', '$disbursement_method', '$bank_name', '$disbursement_account', '$disbursement_account_name', '$branch', '$ewallet_type', '$ewallet_number', '$ewallet_account_name', '$pickup_location')";
        
        if(mysqli_query($conn, $insert)) {
            $upload_dir = 'uploads/requirements/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $all_uploaded = true;
            $uploaded_count = 0;
            $required_count = count($upload_requirements);
            
            foreach($upload_requirements as $field_name => $req_label) {
                // Handle text input (contact_info)
                if ($field_name === 'contact_info' && isset($_POST[$field_name]) && !empty($_POST[$field_name])) {
                    $contact_value = mysqli_real_escape_string($conn, $_POST[$field_name]);
                    mysqli_query($conn, "INSERT INTO loan_requirements (account_number, customer_number, requirement_type, file_path, status) 
                        VALUES ('$account_number', '$customer_id', '$req_label', '$contact_value', 'pending')");
                    $uploaded_count++;
                }
                // Handle file uploads
                elseif(isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == 0) {
                    $ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
                    $filename = $account_number . '_' . $field_name . '_' . time() . '.' . $ext;
                    $target = $upload_dir . $filename;
                    
                    if(move_uploaded_file($_FILES[$field_name]['tmp_name'], $target)) {
                        mysqli_query($conn, "INSERT INTO loan_requirements (account_number, customer_number, requirement_type, file_path, status) 
                            VALUES ('$account_number', '$customer_id', '$req_label', '$target', 'pending')");
                        $uploaded_count++;
                    }
                } else {
                    $all_uploaded = false;
                }
            }
            
            if($all_uploaded && $uploaded_count == $required_count) {
                $success = "Loan application submitted! Account Number: $account_number. All requirements uploaded. Waiting for admin approval.";
            } else {
                $success = "Loan application submitted! Account Number: $account_number. ($uploaded_count of $required_count requirements uploaded)";
            }
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>
<style>
body { background: #f8f9fa; }
.navbar-brand { font-weight: 700; color: #4f46e5 !important; }
.apply-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    padding: 40px;
    max-width: 700px;
    margin: 0 auto;
}
body.dark-mode .apply-card {
    background: #1e293b !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}
.form-group { margin-bottom: 20px; }
.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
}
body.dark-mode .form-group label {
    color: #e2e8f0 !important;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #667eea;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 14px 30px;
    border-radius: 10px;
    font-weight: 600;
    width: 100%;
}
.alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
.alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
body.dark-mode .alert-success { background: #064e3b !important; color: #6ee7b7 !important; }
body.dark-mode .alert-danger { background: #7f1d1d !important; color: #fca5a5 !important; }
.req-label { font-weight: 600; color: #555; margin-bottom: 5px; }
.req-desc { font-size: 12px; color: #888; margin-bottom: 8px; }
.page-title {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 15px 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.page-title .brand {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
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
.main-content { margin-left: 0; padding: 80px 25px 25px; transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.main-content.sidebar-open { margin-left: 260px; }

/* Dark Mode Sidebar */
body.dark-mode .sidebar { background: #1e293b !important; }
body.dark-mode .sidebar-menu a { color: #e2e8f0 !important; }
body.dark-mode .sidebar-menu a:hover, body.dark-mode .sidebar-menu a.active { background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%); color: white !important; }
body.dark-mode .topbar { background: #1e293b !important; }
body.dark-mode .menu-toggle { color: #e2e8f0 !important; }
body.dark-mode .theme-toggle-btn { color: #e2e8f0 !important; }
body.dark-mode .topbar-brand { color: #e2e8f0 !important; }

/* User Dropdown in Topbar */
.user-dropdown {
  position: relative;
}

.user-dropdown-btn {
  background: transparent;
  border: none;
  color: #555;
  font-weight: 500;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.user-dropdown-btn:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.user-dropdown-content {
  display: none;
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 8px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  min-width: 220px;
  overflow: hidden;
  z-index: 100000;
}

.user-dropdown-content.show { display: block; }

.user-dropdown-content a {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  cursor: pointer;
}

.user-dropdown-content a:hover { background: #f3f4f6; color: #667eea; }
.user-dropdown-content a.logout { color: #dc2626; }
.user-dropdown-content a.logout:hover { background: #fee2e2; }
.user-dropdown-content a i { width: 20px; margin-right: 12px; }
.user-dropdown-content .divider { height: 1px; background: #e5e7eb; margin: 4px 0; }

.user-dropdown-profile {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

.user-dropdown-profile .profile-icon {
  width: 40px; height: 40px; border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex; align-items: center; justify-content: center;
  margin-right: 12px; flex-shrink: 0;
}

.user-dropdown-profile .profile-icon i { color: white; font-size: 16px; }
.user-dropdown-profile .profile-info { overflow: hidden; }
.user-dropdown-profile .profile-name { font-weight: 600; color: #1f2937; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
.user-dropdown-profile .profile-email { font-size: 11px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }

body.dark-mode .user-dropdown-content { background: #1e293b; border: 1px solid #334155; }
body.dark-mode .user-dropdown-content a { color: #e2e8f0; }
body.dark-mode .user-dropdown-content a:hover { background: #334155; color: #818cf8; }
body.dark-mode .user-dropdown-content .divider { background: #334155; }
body.dark-mode .user-dropdown-profile { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
body.dark-mode .user-dropdown-content a.logout { color: #f87171 !important; }
body.dark-mode .user-dropdown-content a.logout:hover { background: #7f1d1d; color: #fca5a5 !important; }
body.dark-mode .user-dropdown-profile .profile-name { color: #f1f5f9; }
body.dark-mode .user-dropdown-profile .profile-email { color: #94a3b8; }
body.dark-mode .user-dropdown-btn { color: #e2e8f0; }

/* Sidebar User Section */
.sidebar-user-section { position: absolute; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e7eb; padding: 15px; }
.sidebar-user-profile { display: flex; align-items: center; padding: 10px; margin-bottom: 10px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 10px; }
.sidebar-user-icon { width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; }
.sidebar-user-icon i { color: white; font-size: 22px; }
.sidebar-user-info { overflow: hidden; }
.sidebar-user-name { font-weight: 600; color: #1f2937; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar-user-email { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar-user-actions { display: flex; gap: 5px; }
.sidebar-user-actions a { flex: 1; display: flex; align-items: center; justify-content: center; padding: 10px; color: #555; text-decoration: none; font-weight: 500; font-size: 12px; border-radius: 8px; transition: all 0.2s; cursor: pointer; }
.sidebar-user-actions a:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }
.sidebar-user-actions a.text-danger { color: #dc2626; }
.sidebar-user-actions a.text-danger:hover { background: #fee2e2; color: #dc2626; }
.sidebar-user-actions a i { margin-right: 5px; }
.sidebar { overflow-y: auto; }
body.dark-mode .sidebar-user-section { background: #1e293b; border-top-color: #334155; }
body.dark-mode .sidebar-user-profile { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
body.dark-mode .sidebar-user-name { color: #f1f5f9; }
body.dark-mode .sidebar-user-email { color: #94a3b8; }
body.dark-mode .sidebar-user-actions a { color: #e2e8f0; }
body.dark-mode .sidebar-user-actions a:hover { background: #334155; color: #818cf8; }
body.dark-mode .sidebar-user-actions a.text-danger { color: #f87171; }
body.dark-mode .sidebar-user-actions a.text-danger:hover { background: #7f1d1d; color: #fca5a5; }
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
            <a href="customer_apply_loan.php" class="active"><i class="fas fa-file-signature"></i> Apply for Loan</a>
            <a href="customer_my_loans.php"><i class="fas fa-money-check-alt"></i> My Loans</a>
            <a href="customer_make_payment.php"><i class="fas fa-credit-card"></i> Make Payment</a>
            <a href="customer_payment_history.php"><i class="fas fa-history"></i> Payment History</a>
        </div>
        <!-- User Profile Section at bottom of sidebar -->
        <div class="sidebar-user-section">
            <div class="sidebar-user-profile">
                <div class="sidebar-user-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
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
        <h4 class="mb-4"><i class="fas fa-file-signature me-2"></i>Apply for Loan</h4>
        
        <div class="apply-card">
            <h3 class="text-center mb-4"><i class="fas fa-file-signature me-2"></i>Apply for Loan</h3>
            
            <?php if(isset($success)): ?>
                <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Select Loan Type</label>
                    <select name="account_type" id="loanType" onchange="updateLimits(); updateRequirements();" required>
                        <option value="">-- Select Loan Type --</option>
                        <?php while($type = mysqli_fetch_assoc($loan_types)): ?>
                            <option value="<?php echo $type['account_type_number']; ?>" data-min="1000" data-max="1000000"><?php echo $type['account_type_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Dynamic Requirements Section -->
                <div id="requirementsSection" class="requirements-section" style="display:none;">
                    <div class="requirements-card">
                        <h5><i class="fas fa-clipboard-list me-2"></i>Requirements for <span id="selectedLoanType"></span></h5>
                        <ul id="requirementsList" class="requirements-list">
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Loan Amount</label>
                    <input type="number" name="loan_amount" id="loanAmount" min="<?php echo $min_amount; ?>" max="<?php echo $max_amount; ?>" placeholder="Enter loan amount" required oninput="calculateTotal()">
                    <small class="text-muted">Minimum: <?php echo number_format($min_amount); ?> - Maximum: <?php echo number_format($max_amount); ?></small>
                </div>
                
                <div class="form-group">
                    <label>Loan Term (Payment Duration)</label>
                    <select name="loan_term" id="loanTerm" onchange="calculateTotal()" required>
                        <option value="">-- Select Term --</option>
                        <option value="1">1 Month</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                        <option value="18">18 Months</option>
                        <option value="24">24 Months</option>
                    </select>
                    <small class="text-muted">Longer terms have higher interest rates</small>
                </div>
                
                <div id="paymentSummary" style="display:none;" class="payment-summary">
                    <div class="summary-card">
                        <h5><i class="fas fa-calculator me-2"></i>Payment Summary</h5>
                        <p>Loan Amount: <strong>₱<span id="summaryAmount">0</span></strong></p>
                        <p>Monthly Interest: <strong><span id="summaryRate">0</span></strong></p>
                        <p>Total Interest: <strong>₱<span id="summaryInterest">0</span></strong></p>
                        <p>Total to Pay: <strong>₱<span id="summaryTotal">0</span></strong></p>
                        <p>Monthly Payment: <strong>₱<span id="summaryMonthly">0</span></strong></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" value="<?php echo $_SESSION['customer_name']; ?>" disabled>
                </div>
                
                <hr>
                <h5 class="mb-3"><i class="fas fa-university me-2"></i>Disbursement Information</h5>
                <p class="text-muted mb-3">Choose how you want to receive your loan</p>
                
                <div class="form-group">
                    <label>Disbursement Method</label>
                    <select name="disbursement_method" id="disbursementMethod" onchange="toggleDisbursementFields()" required>
                        <option value="">-- Select Method --</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="E-Wallet">E-Wallet (GCash, PayMaya, etc.)</option>
                        <option value="Cash">Cash Pickup</option>
                    </select>
                </div>
                
                <div id="bankFields" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bank Name</label>
                                <input type="text" name="bank_name" placeholder="Enter bank name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Account Number</label>
                                <input type="text" name="account_number_disburse" placeholder="Enter account number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Account Name</label>
                                <input type="text" name="account_name_disburse" placeholder="Enter account name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Branch</label>
                                <input type="text" name="branch" placeholder="Enter branch">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="ewalletFields" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>E-Wallet Type</label>
                                <select name="ewallet_type">
                                    <option value="GCash">GCash</option>
                                    <option value="PayMaya">PayMaya</option>
                                    <option value="ShopeePay">ShopeePay</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>E-Wallet Number</label>
                                <input type="text" name="ewallet_number" placeholder="Enter e-wallet number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Account Name</label>
                                <input type="text" name="ewallet_account_name" placeholder="Enter account name">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="cashFields" style="display:none;">
                    <div class="form-group">
                        <label>Pickup Location</label>
                        <select name="pickup_location">
                            <option value="Main Branch">Main Branch</option>
                            <option value="Branch A">Branch A</option>
                            <option value="Branch B">Branch B</option>
                        </select>
                    </div>
                </div>
                
                <hr>
                <h5 class="mb-3"><i class="fas fa-file-upload me-2"></i>Upload Requirements</h5>
                <p class="text-muted mb-3">Please upload the following documents (PDF, JPG, PNG)</p>
                
                <div id="uploadRequirementsContainer">
                    <!-- Dynamic upload fields will appear here -->
                    <p class="text-muted">Please select a loan type first to see the required documents.</p>
                </div>
                
                <button type="submit" name="apply" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Submit Application with Requirements
                </button>
            </form>
        </div>
    </div>
</body>
<script>
function updateLimits() {
    var select = document.getElementById('loanType');
    var option = select.options[select.selectedIndex];
    var min = option.getAttribute('data-min') || 1000;
    var max = option.getAttribute('data-max') || 1000000;
    
    document.getElementById('loanAmount').min = min;
    document.getElementById('loanAmount').max = max;
    document.querySelector('.form-group small').textContent = 'Minimum: ' + Number(min).toLocaleString() + ' - Maximum: ' + Number(max).toLocaleString();
}

function updateRequirements() {
    var select = document.getElementById('loanType');
    var option = select.options[select.selectedIndex];
    var loanTypeName = option.text.trim();
    var requirementsSection = document.getElementById('requirementsSection');
    var requirementsList = document.getElementById('requirementsList');
    var selectedLoanType = document.getElementById('selectedLoanType');
    var uploadContainer = document.getElementById('uploadRequirementsContainer');
    
    // Define requirements for each loan type (display)
    var requirements = {
        'Emergency Loan': ['Valid ID', 'Active Member'],
        'Personal Loan': ['Valid ID', 'Proof of Income', 'Good Payment History'],
        'Educational Loan': ['Valid ID', 'Proof of Enrollment / School ID', 'Active Member Account', 'Valid ID of Parents/Guardian', 'Proof of Income of Parents', 'Contact Information'],
        'Business Loan': ['Valid ID', 'Business Permit', 'Proof of Income']
    };
    
    // Define upload fields for each loan type
    var uploadFields = {
        'Emergency Loan': [
            { name: 'valid_id', label: 'Valid ID', desc: 'Upload a valid government-issued ID (Passport, Driver\'s License, etc.)' },
            { name: 'active_member', label: 'Proof of Active Membership', desc: 'Upload proof of active membership (member ID, membership certificate)' }
        ],
        'Personal Loan': [
            { name: 'valid_id', label: 'Valid ID', desc: 'Upload a valid government-issued ID (Passport, Driver\'s License, etc.)' },
            { name: 'proof_of_income', label: 'Proof of Income', desc: 'Upload pay slip, employment letter, or business permit' },
            { name: 'good_payment_history', label: 'Good Payment History', desc: 'Upload credit report or payment history from previous loans' }
        ],
        'Educational Loan': [
            { name: 'valid_id', label: 'Valid ID', desc: 'Upload a valid government-issued ID (Passport, Driver\'s License, etc.)' },
            { name: 'proof_of_enrollment', label: 'Proof of Enrollment / School ID', desc: 'Upload enrollment certificate or school/university ID' },
            { name: 'active_member', label: 'Active Member Account', desc: 'Upload proof of active membership / member ID' },
            { name: 'parents_id', label: 'Valid ID of Parents/Guardian', desc: 'Upload valid ID of parent or guardian' },
            { name: 'parents_income', label: 'Proof of Income of Parents', desc: 'Upload proof of income of parent or guardian' },
            { name: 'contact_info', label: 'Contact Information', desc: 'Enter contact information (phone number, email, address)', type: 'text' }
        ],
        'Business Loan': [
            { name: 'valid_id', label: 'Valid ID', desc: 'Upload a valid government-issued ID (Passport, Driver\'s License, etc.)' },
            { name: 'business_permit', label: 'Business Permit', desc: 'Upload business permit or mayor\'s permit' },
            { name: 'proof_of_income', label: 'Proof of Income', desc: 'Upload business financial statements or income tax return' }
        ]
    };
    
    // Find matching loan type (case-insensitive)
    var matchedRequirements = null;
    var matchedUploads = null;
    for (var key in requirements) {
        if (key.toLowerCase() === loanTypeName.toLowerCase()) {
            matchedRequirements = requirements[key];
            matchedUploads = uploadFields[key];
            loanTypeName = key;
            break;
        }
    }
    
    if (matchedRequirements && loanTypeName) {
        // Update requirements display
        selectedLoanType.textContent = loanTypeName;
        requirementsList.innerHTML = '';
        
        matchedRequirements.forEach(function(req) {
            var li = document.createElement('li');
            li.innerHTML = '<i class="fas fa-check-circle"></i>' + req;
            requirementsList.appendChild(li);
        });
        
        requirementsSection.style.display = 'block';
        
        // Update upload fields
        if (matchedUploads) {
            uploadContainer.innerHTML = '';
            matchedUploads.forEach(function(field) {
                var div = document.createElement('div');
                div.className = 'form-group';
                
                if (field.type === 'text') {
                    // Render text input for contact info
                    div.innerHTML = 
                        '<label class="req-label">' + field.label + '</label>' +
                        '<p class="req-desc">' + field.desc + '</p>' +
                        '<input type="text" name="' + field.name + '" placeholder="Enter ' + field.label + '" required>';
                } else {
                    // Render file input for document uploads
                    div.innerHTML = 
                        '<label class="req-label">' + field.label + '</label>' +
                        '<p class="req-desc">' + field.desc + '</p>' +
                        '<input type="file" name="' + field.name + '" accept=".pdf,.jpg,.jpeg,.png" required>';
                }
                uploadContainer.appendChild(div);
            });
        }
    } else {
        requirementsSection.style.display = 'none';
        uploadContainer.innerHTML = '<p class="text-muted">Please select a loan type first to see the required documents.</p>';
    }
}

function toggleDisbursementFields() {
    var method = document.getElementById('disbursementMethod').value;
    document.getElementById('bankFields').style.display = (method == 'Bank Transfer') ? 'block' : 'none';
    document.getElementById('ewalletFields').style.display = (method == 'E-Wallet') ? 'block' : 'none';
    document.getElementById('cashFields').style.display = (method == 'Cash') ? 'block' : 'none';
    
    // Set required attributes
    var bankInputs = document.querySelectorAll('#bankFields input, #bankFields select');
    var ewalletInputs = document.querySelectorAll('#ewalletFields input, #ewalletFields select');
    var cashInputs = document.querySelectorAll('#cashFields select');
    
    bankInputs.forEach(function(input) {
        input.required = (method == 'Bank Transfer');
    });
    ewalletInputs.forEach(function(input) {
        input.required = (method == 'E-Wallet');
    });
    cashInputs.forEach(function(input) {
        input.required = (method == 'Cash');
    });
}
</script>
<style>
body {
    background: #f0f2f5;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    transition: background-color 0.3s, color 0.3s;
}
body.dark-mode {
    background: #0f172a;
    color: #e2e8f0;
}
body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
    color: #f1f5f9 !important;
}
body.dark-mode .main-content {
    background: #0f172a !important;
}
body.dark-mode p, body.dark-mode span, body.dark-mode div, body.dark-mode strong {
    color: #e2e8f0 !important;
}
.navbar {
    position: relative;
    z-index: 1000;
    background: #fff !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: background-color 0.3s, box-shadow 0.3s;
}
body.dark-mode .navbar {
    background: #1e293b !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}
.navbar-brand {
    font-weight: 700;
    font-size: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.navbar-brand i {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.nav-link {
    color: #555 !important;
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    border-radius: 8px;
    transition: all 0.3s;
}
.nav-link:hover {
    color: #667eea !important;
    background: rgba(102, 126, 234, 0.1);
}
body.dark-mode .nav-link {
    color: #e2e8f0 !important;
}
body.dark-mode .nav-link:hover {
    background: rgba(102, 126, 234, 0.2);
    color: #818cf8 !important;
}
.card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    margin-bottom: 20px;
    transition: all 0.3s;
}
body.dark-mode .card {
    background: #1e293b;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0 !important;
    padding: 15px 20px;
}
.form-label {
    font-weight: 500;
    color: #374151;
}
body.dark-mode .form-label {
    color: #e2e8f0;
}
body.dark-mode .form-control {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
}
body.dark-mode .form-select {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
}
body.dark-mode .form-select option {
    background: #1e293b;
    color: #f1f5f9;
}
body.dark-mode .form-group input, body.dark-mode .form-group select {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .text-muted {
    color: #94a3b8 !important;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}
.btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}

/* Requirements Section */
.requirements-section {
    margin: 20px 0;
}

.requirements-card {
    background: #f8f9fa;
    border: 2px solid #667eea;
    border-radius: 12px;
    padding: 20px;
}

.requirements-card h5 {
    color: #667eea;
    margin-bottom: 15px;
    font-weight: 600;
}

.requirements-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.requirements-list li {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
    font-weight: 500;
}

.requirements-list li:last-child {
    border-bottom: none;
}

.requirements-list li i {
    color: #10b981;
    margin-right: 10px;
    font-size: 16px;
}

body.dark-mode .requirements-card {
    background: #1e293b;
    border-color: #818cf8;
}

body.dark-mode .requirements-card h5 {
    color: #818cf8;
}

body.dark-mode .requirements-list li {
    color: #e2e8f0;
    border-color: #334155;
}

body.dark-mode .requirements-list li i {
    color: #34d399;
}

.payment-summary {
    margin: 20px 0;
}
body.dark-mode .payment-summary {
    background: transparent;
}
.summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
}
.summary-card h5 {
    margin-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.3);
    padding-bottom: 10px;
}
.summary-card p {
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
}
body.dark-mode .summary-card {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}
body.dark-mode .summary-card p, body.dark-mode .summary-card h5, body.dark-mode .summary-card span {
    color: #f1f5f9 !important;
}
</style>
<script>
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

function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userDropdown');
    const btn = document.querySelector('.user-dropdown-btn');
    if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

function calculateTotal() {
    var amount = parseFloat(document.getElementById('loanAmount').value) || 0;
    var term = parseFloat(document.getElementById('loanTerm').value) || 0;
    var summary = document.getElementById('paymentSummary');
    
    // Interest rates by loan type (monthly)
    var interestRates = {
        'Emergency Loan': 2.0,
        'Educational Loan': 1.5,
        'Personal Loan': 3.0,
        'Business Loan': 4.0
    };
    
    // Get loan type
    var loanTypeSelect = document.getElementById('loanType');
    var loanType = loanTypeSelect ? loanTypeSelect.options[loanTypeSelect.selectedIndex].text.trim() : '';
    var monthlyRate = interestRates[loanType] || 1.5;
    
    // Try to find matching rate (case-insensitive)
    for (var key in interestRates) {
        if (key.toLowerCase() === loanType.toLowerCase()) {
            monthlyRate = interestRates[key];
            break;
        }
    }
    
    if (amount > 0 && term > 0) {
        var interestRate = monthlyRate * term;
        var interest = (amount / 100) * interestRate;
        var total = amount + interest;
        var monthly = total / term;
        
        document.getElementById('summaryAmount').textContent = amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryRate').textContent = monthlyRate.toFixed(2) + '%/mo';
        document.getElementById('summaryInterest').textContent = interest.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryTotal').textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('summaryMonthly').textContent = monthly.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        summary.style.display = 'block';
    } else {
        summary.style.display = 'none';
    }
}

(function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }
})();
</script>
</html>
