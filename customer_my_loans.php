<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}
require_once 'include/head.php';
require_once 'database/db_connection.php';

$customer_id = $_SESSION['customer_id'];

// Check if customer account is active
$customer_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active, deactivated_date FROM customers WHERE customer_number = '$customer_id'"));
$is_account_active = isset($customer_check['is_active']) ? $customer_check['is_active'] : 1;

// Check if customer has a pending/active loan (to restrict apply for loan access)
$check_active = mysqli_query($conn, "SELECT a.account_number FROM accounts a
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE a.customer = '$customer_id'
    AND acs.account_status_name IN ('Active', 'Partial', 'Up to Date', 'Due Date')
    AND a.loan_balance > 0 LIMIT 1");
$has_active_loan = mysqli_num_rows($check_active) > 0;

$loans = mysqli_query($conn, "SELECT a.*, at.account_type_name, acs.account_status_name
FROM accounts a
LEFT JOIN account_type at ON a.account_type = at.account_type_number
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE a.customer = '$customer_id'
ORDER BY a.open_date DESC");
?>
<style>
body { background: #f0f2f5; font-family: 'Segoe UI', system-ui, sans-serif; }
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
.theme-toggle-btn { background: none; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #555; font-weight: 500; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.theme-toggle-btn:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; transform: scale(1.1); }
.page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 25px; }
.stat-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.stat-number { font-size: 24px; font-weight: 700; color: #1f2937; }
.stat-label { color: #6b7280; font-size: 13px; }
.badge-active { background: #10b981; }
.badge-pending { background: #f59e0b; }
.badge-paid { background: #3b82f6; }
.badge-inactive { background: #ef4444; }
.btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
.btn-primary:hover { background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%); }
.btn-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; }
.btn-secondary { background: #64748b; border: none; }
.btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; color: white; }
.card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
.card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0 !important; padding: 12px 20px; }

/* Dark Mode */
body.dark-mode { background: #0f172a !important; color: #e2e8f0 !important; }
body.dark-mode .main-content { background: #0f172a !important; }
body.dark-mode .topbar { background: #1e293b !important; }
body.dark-mode .sidebar { background: #1e293b !important; }
body.dark-mode .sidebar-menu a { color: #e2e8f0 !important; }
body.dark-mode .sidebar-menu a:hover, body.dark-mode .sidebar-menu a.active { background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%); color: white !important; }
body.dark-mode .card { background: #1e293b !important; color: #e2e8f0 !important; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
body.dark-mode .card-header { background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%) !important; }
body.dark-mode h4, body.dark-mode .text-muted, body.dark-mode p, body.dark-mode span, body.dark-mode div, body.dark-mode strong { color: #e2e8f0 !important; }
body.dark-mode .alert-danger { background: #7f1d1d !important; color: #fca5a5 !important; }
body.dark-mode .alert-warning { background: #78350f !important; color: #fcd34d !important; }
body.dark-mode .badge { color: white !important; }
body.dark-mode .theme-toggle-btn { color: #e2e8f0 !important; }
body.dark-mode .topbar-brand { color: #e2e8f0 !important; }
body.dark-mode .menu-toggle { color: #e2e8f0 !important; }
body.dark-mode .stat-number { color: #e2e8f0 !important; }
body.dark-mode .stat-label { color: #94a3b8 !important; }

/* User Dropdown */
.user-dropdown { position: relative; }
.user-dropdown-btn { background: transparent; border: none; color: #555; font-weight: 500; padding: 8px 12px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
.user-dropdown-btn:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }
.user-dropdown-content { display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); min-width: 220px; overflow: hidden; z-index: 100000; }
.user-dropdown-content.show { display: block; }
.user-dropdown-content a { display: flex; align-items: center; padding: 12px 20px; color: #374151; text-decoration: none; font-weight: 500; transition: all 0.2s; cursor: pointer; }
.user-dropdown-content a:hover { background: #f3f4f6; color: #667eea; }
.user-dropdown-content a.logout { color: #dc2626; }
.user-dropdown-content a.logout:hover { background: #fee2e2; }
.user-dropdown-content a i { width: 20px; margin-right: 12px; }
.user-dropdown-content .divider { height: 1px; background: #e5e7eb; margin: 4px 0; }
.user-dropdown-profile { display: flex; align-items: center; padding: 16px 20px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); }
.user-dropdown-profile .profile-icon { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; }
.user-dropdown-profile .profile-icon i { color: white; font-size: 16px; }
.user-dropdown-profile .profile-info { overflow: hidden; }
.user-dropdown-profile .profile-name { font-weight: 600; color: #1f2937; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
.user-dropdown-profile .profile-email { font-size: 11px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
body.dark-mode .user-dropdown-content { background: #1e293b; border: 1px solid #334155; }
body.dark-mode .user-dropdown-content a { color: #e2e8f0; }
body.dark-mode .user-dropdown-content a:hover { background: #334155; color: #818cf8; }
body.dark-mode .user-dropdown-content .divider { background: #334155; }
body.dark-mode .user-dropdown-profile { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
body.dark-mode .user-dropdown-profile .profile-name { color: #f1f5f9; }
body.dark-mode .user-dropdown-profile .profile-email { color: #94a3b8; }
body.dark-mode .user-dropdown-btn { color: #e2e8f0; }
body.dark-mode .user-dropdown-content a.logout { color: #f87171 !important; }
body.dark-mode .user-dropdown-content a.logout:hover { background: #7f1d1d; color: #fca5a5 !important; }

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
            <?php if(!$has_active_loan): ?>
            <a href="customer_apply_loan.php"><i class="fas fa-file-signature"></i> Apply for Loan</a>
            <?php else: ?>
            <a href="#" class="text-muted" style="opacity:0.5;cursor:not-allowed;" title="You have an existing loan application"><i class="fas fa-file-signature"></i> Apply for Loan <small><i class="fas fa-lock fa-xs"></i></small></a>
            <?php endif; ?>
            <a href="customer_my_loans.php" class="active"><i class="fas fa-money-check-alt"></i> My Loans</a>
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
        <?php if(!$is_account_active): ?>
        <?php
        $deactivated_date = isset($customer_check['deactivated_date']) ? $customer_check['deactivated_date'] : null;
        $days_deactivated = 0;
        if($deactivated_date) {
            $days_deactivated = floor((strtotime(date('Y-m-d')) - strtotime($deactivated_date)) / (60 * 60 * 24));
        }
        ?>
        <div class="alert alert-danger mb-4">
            <h5><i class="fas fa-ban me-2"></i>Account Deactivated</h5>
            <p class="mb-0">Your account has been deactivated. You can view your loan history but cannot apply for new loans or make payments.</p>
            <hr>
            <p class="mb-1"><strong>Days deactivated:</strong> <?php echo $days_deactivated; ?> day(s)</p>
            <p class="mb-0"><strong>Contact admin (within 30 days):</strong> fundharmonycustomerservice@gmail.com | 09777698003</p>
        </div>
        <?php endif; ?>
        
        <h4 class="mb-4"><i class="fas fa-money-check-alt me-2"></i>My Loans</h4>
        
        <?php if(isset($_SESSION['loan_msg'])): ?>
        <div class="alert alert-<?php echo $_SESSION['loan_msg_type'] ?? 'info'; ?> alert-dismissible fade show">
            <?php echo $_SESSION['loan_msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['loan_msg'], $_SESSION['loan_msg_type']); endif; ?>
        
        <?php if(isset($_SESSION['loan_error'])): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['loan_error']; unset($_SESSION['loan_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if(mysqli_num_rows($loans) > 0): ?>
            <?php while($loan = mysqli_fetch_assoc($loans)): ?>
                <?php
                $loan_amount = floatval($loan['loan_amount'] ?? 0);
                $loan_term = intval($loan['loan_term'] ?? 1);
                $interest = floatval($loan['interest'] ?? 0);
                $loan_balance = floatval($loan['loan_balance'] ?? ($loan_amount + $interest));
                $loan_type_name = $loan['account_type_name'] ?? '';
                $interestRates = [
                    'Emergency Loan' => 2.0,
                    'Educational Loan' => 1.5,
                    'Personal Loan' => 3.0,
                    'Business Loan' => 4.0
                ];
                $baseRate = $interestRates[$loan_type_name] ?? 1.5;
                $monthly_interest = ($loan_amount * $baseRate) / 100;
                $due_date = isset($loan['due_date']) ? $loan['due_date'] : null;
                $is_overdue = $due_date && strtotime($due_date) < strtotime(date('Y-m-d'));
                ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Account #<?php echo $loan['account_number']; ?></span>
                        <span class="badge <?php
                            if($loan['account_status_name'] == 'Active') echo 'bg-success';
                            elseif($loan['account_status_name'] == 'Approved') echo 'bg-primary';
                            elseif($loan['account_status_name'] == 'Declined') echo 'bg-secondary';
                            elseif($loan['account_status_name'] == 'Rejected') echo 'bg-danger';
                            elseif($loan['account_status_name'] == 'Pending') echo 'bg-warning text-dark';
                            elseif($loan['account_status_name'] == 'Partial') echo 'bg-success';
                            elseif($loan['account_status_name'] == 'Paid' || $loan['account_status_name'] == 'Completed') echo 'bg-info';
                            else echo 'bg-secondary';
                        ?>"><?php echo $loan['account_status_name'] == 'Partial' ? 'Active' : $loan['account_status_name']; ?></span>
                    </div>
                    <div class="card-body">
                        <?php if($loan['account_status_name'] == 'Rejected' && !empty($loan['reject_notes'])): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-times-circle me-2"></i>Loan Rejected</h6>
                            <p class="mb-0">Reason: <?php echo htmlspecialchars($loan['reject_notes']); ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-3"><p><strong>Loan Type:</strong><br><?php echo $loan['account_type_name'] ?: 'N/A'; ?></p></div>
                            <div class="col-md-3"><p><strong>Open Date:</strong><br><?php echo date('M d, Y', strtotime($loan['open_date'])); ?></p></div>
                            <div class="col-md-3"><p><strong>Loan Term:</strong><br><?php echo $loan_term; ?> Month(s)</p></div>
                            <div class="col-md-3"><p><strong>Loan Amount:</strong><br>₱<?php echo number_format($loan_amount, 2); ?></p></div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3"><p><strong>Due Date:</strong><br><?php echo $due_date ? date('M d, Y', strtotime($due_date)) : 'N/A'; ?></p></div>
                            <div class="col-md-3"><p><strong>Monthly Interest:</strong><br><span class="text-danger">₱<?php echo number_format($monthly_interest, 2); ?> (<?php echo $baseRate; ?>%)</span></p></div>
                            <div class="col-md-3"><p><strong>Total Interest:</strong><br>₱<?php echo number_format($interest, 2); ?></p></div>
                            <div class="col-md-3"><p><strong>Total to Pay:</strong><br>₱<?php echo number_format($loan_amount + $interest, 2); ?></p></div>
                        </div>
                        <?php if($is_overdue && $loan_balance > 0): ?>
                        <div class="alert alert-warning mt-2"><strong>Overdue!</strong> This loan is past due date.</div>
                        <?php endif; ?>
                        
                        <?php if($loan['account_status_name'] == 'Approved'): ?>
                        <div class="alert alert-info mt-2">
                            <h6><i class="fas fa-envelope me-2"></i>Loan Approval Notification</h6>
                            <p class="mb-2">Your loan has been approved by the admin. Please confirm if you want to proceed with payments, or decline if you no longer want this loan.</p>
                            <div class="d-flex gap-2">
                                <a href="app/confirm_loan.php?account_number=<?php echo $loan['account_number']; ?>&action=confirm" class="btn btn-success btn-sm" onclick="return confirm('Confirm this loan? You will be able to make payments after confirmation.')">
                                    <i class="fas fa-check me-1"></i> Confirm Loan
                                </a>
                                <a href="app/confirm_loan.php?account_number=<?php echo $loan['account_number']; ?>&action=decline" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to decline this loan?')">
                                    <i class="fas fa-times me-1"></i> Decline Loan
                                </a>
                            </div>
                        </div>
                        <?php elseif($loan['account_status_name'] == 'Active'): ?>
                        <div class="alert alert-success mt-2"><i class="fas fa-check-circle me-2"></i>Loan confirmed. You can now make payments.</div>
                        <?php endif; ?>
                        
                        <?php if($loan_balance > 0 && $loan['account_status_name'] == 'Active'): ?>
                        <div class="mt-3">
                            <a href="customer_make_payment.php" class="btn btn-success">Make Payment</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h4>No Loans Found</h4>
                <p>You haven't applied for any loans yet.</p>
                <?php if(!$has_active_loan): ?>
                <a href="customer_apply_loan.php" class="btn btn-primary">Apply for Loan</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
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
    function toggleTheme() {
        const body = document.body; const html = document.documentElement;
        const icon = document.getElementById('theme-icon');
        
        body.classList.toggle('dark-mode');
        html.classList.toggle('dark-mode-bg');
        
        if (body.classList.contains('dark-mode')) {
            html.style.backgroundColor = '#0f172a';
            body.style.backgroundColor = '#0f172a';
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            localStorage.setItem('theme', 'dark');
        } else {
            html.style.backgroundColor = '#f8fafc';
            body.style.backgroundColor = '#f8fafc';
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
