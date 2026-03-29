<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}
error_reporting(0);
ini_set('display_errors', 0);
require_once 'include/head.php';
require_once 'database/db_connection.php';

$customer_id = $_SESSION['customer_id'];

$check_active = mysqli_query($conn, "SELECT a.account_number FROM accounts a LEFT JOIN account_status acs ON a.account_status = acs.account_status_number WHERE a.customer = '$customer_id' AND acs.account_status_name IN ('Active', 'Partial', 'Up to Date', 'Due Date') AND a.loan_balance > 0 LIMIT 1");
$has_active_loan = mysqli_num_rows($check_active) > 0;

$payments = mysqli_query($conn, "SELECT p.*, a.account_type, a.loan_amount, a.loan_balance, a.customer
FROM payments p 
INNER JOIN accounts a ON p.account_number = a.account_number 
WHERE a.customer = '$customer_id' 
ORDER BY p.payment_date DESC");
?>
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
.table thead th {
    background: #f8f9fa;
    border-bottom: 2px solid #e0e0e0;
    font-weight: 600;
    color: #333;
}
body.dark-mode .table thead th {
    background: #1e293b;
    color: #94a3b8;
}
body.dark-mode .table {
    color: #e2e8f0;
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
.topbar-brand { position: absolute; left: 50%; transform: translateX(-50%); font-weight: 700; font-size: 1.3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.topbar.sidebar-open { left: 260px; }
.menu-toggle { background: none; border: none; font-size: 1.25rem; color: #555; cursor: pointer; padding: 8px; border-radius: 8px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.menu-toggle:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; transform: scale(1.1); }
.main-content { margin-left: 0; padding: 80px 25px 25px; transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.main-content.sidebar-open { margin-left: 260px; }
.theme-toggle-btn { background: none; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #555; font-weight: 500; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.theme-toggle-btn:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; transform: scale(1.1); }

/* Dark Mode Sidebar */
body.dark-mode .sidebar { background: #1e293b !important; }
body.dark-mode .sidebar-menu a { color: #e2e8f0 !important; }
body.dark-mode .sidebar-menu a:hover, body.dark-mode .sidebar-menu a.active { background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%); color: white !important; }
body.dark-mode .topbar { background: #1e293b !important; }
body.dark-mode .menu-toggle { color: #e2e8f0 !important; }
body.dark-mode .theme-toggle-btn { color: #e2e8f0 !important; }
body.dark-mode .topbar-brand { color: #e2e8f0 !important; }

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

.topbar { position: fixed; top: 0; left: 0; right: 0; height: 60px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: space-between; padding: 0 25px; z-index: 1030; transition: left 0.3s ease; }
.topbar.sidebar-open { left: 260px; }
.topbar-brand { position: absolute; left: 50%; transform: translateX(-50%); font-weight: 700; font-size: 1.3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; display: flex; align-items: center; }
.topbar-brand i { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.menu-toggle { background: none; border: none; font-size: 1.25rem; color: #555; cursor: pointer; padding: 8px; border-radius: 8px; }
.menu-toggle:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }
.main-content { margin-left: 0; padding: 80px 25px 25px; transition: margin-left 0.3s ease; }
.main-content.sidebar-open { margin-left: 260px; }
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
            <a href="customer_make_payment.php"><i class="fas fa-credit-card"></i> Make Payment</a>
            <a href="customer_payment_history.php" class="active"><i class="fas fa-history"></i> Payment History</a>
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
        <h4 class="mb-4"><i class="fas fa-history me-2"></i>Payment History</h4>
        
        <?php if(mysqli_num_rows($payments) > 0): ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Payment No.</th>
                                <th>Account</th>
                                <th>Amount Paid</th>
                                <th>Remaining Balance</th>
                                <th>Method</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($pay = mysqli_fetch_assoc($payments)): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($pay['payment_date'])); ?></td>
                                    <td>#<?php echo str_pad($pay['payment_number'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo $pay['account_number']; ?></td>
                                    <td><strong>₱<?php echo number_format($pay['payment_amount'], 2); ?></strong></td>
                                    <td><?php echo isset($pay['loan_balance']) ? '₱' . number_format($pay['loan_balance'], 2) : '-'; ?></td>
                                    <td><?php echo $pay['payment_method']; ?></td>
                                    <td><?php echo $pay['notes'] ?: '-'; ?></td>
                                    <td>
                                        <a href="customer_receipt.php?id=<?php echo $pay['payment_number']; ?>" 
                                           class="btn btn-sm btn-outline-primary" target="_blank" title="Print Receipt">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                    <h4>No Payments Found</h4>
                    <p>You haven't made any payments yet.</p>
                    <a href="customer_make_payment.php" class="btn btn-primary">Make a Payment</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    })();
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
    </script>
</body>
</html>
