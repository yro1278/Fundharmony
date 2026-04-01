<?php
session_start();
require_once 'include/head.php';
require_once 'database/db_connection.php';

$conn = $conn;
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
.sidebar-menu a { display: flex; align-items: center; padding: 12px 15px; color: #555; text-decoration: none; border-radius: 10px; margin-bottom: 5px; font-weight: 500; transition: all 0.3s; position: relative; overflow: hidden; }
.sidebar-menu a::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; transition: width 0.3s; z-index: -1; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: translateX(5px); }
.sidebar-menu a i { transition: transform 0.3s ease; }
.sidebar-menu a:hover i, .sidebar-menu a.active i { transform: scale(1.1); }
.sidebar-menu a:hover, .sidebar-menu a.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
.link-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 13px; margin-right: 12px; flex-shrink: 0; }
.link-icon i { width: auto; margin-right: 0; }
.topbar { position: fixed; top: 0; left: 0; right: 0; height: 60px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: space-between; padding: 0 25px; z-index: 1030; transition: left 0.4s; }
.topbar-brand { position: absolute; left: 50%; transform: translateX(-50%); font-weight: 700; font-size: 1.3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.topbar.sidebar-open { left: 260px; }
.menu-toggle { background: none; border: none; font-size: 1.25rem; color: #555; cursor: pointer; padding: 8px; border-radius: 8px; transition: all 0.3s; }
.menu-toggle:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; transform: scale(1.1); }
.main-content { margin-left: 0; padding: 80px 25px 25px; transition: margin-left 0.4s; }
.main-content.sidebar-open { margin-left: 260px; }
.page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 25px; }
.card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; transition: all 0.3s ease; }
.card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
.card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; padding: 12px 20px; }
.table { margin-bottom: 0; }
.table th { background: #f8f9fa; border-top: none; }
.badge-success { background: #10b981; }
.badge-warning { background: #f59e0b; }
.badge-danger { background: #ef4444; }
.badge-secondary { background: #64748b; }
.sidebar-user-section { position: absolute; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e7eb; padding: 15px; }
.sidebar-user-profile { display: flex; align-items: center; padding: 10px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 10px; }
.sidebar-user-icon { width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; }
.sidebar-user-icon i { color: white; font-size: 22px; }
.sidebar-user-name { font-weight: 600; color: #1f2937; font-size: 14px; }
.sidebar-user-email { font-size: 12px; color: #6b7280; }
.sidebar-user-actions { display: flex; gap: 5px; margin-top: 10px; }
.sidebar-user-actions a { flex: 1; display: flex; align-items: center; justify-content: center; padding: 10px; color: #555; text-decoration: none; font-weight: 500; font-size: 12px; border-radius: 8px; transition: all 0.2s; }
.sidebar-user-actions a:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }
.sidebar-user-actions a.text-danger:hover { background: #fee2e2; color: #dc2626; }
body.dark-mode { background: #0f172a !important; color: #e2e8f0 !important; }
body.dark-mode .main-content { background: #0f172a !important; }
body.dark-mode .topbar { background: #1e293b !important; }
body.dark-mode .sidebar { background: #1e293b !important; }
body.dark-mode .card { background: #1e293b !important; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode p { color: #e2e8f0 !important; }
body.dark-mode .text-muted { color: #94a3b8 !important; }
body.dark-mode .table { color: #e2e8f0; background: #1e293b; }
body.dark-mode .table th { background: #334155; color: #e2e8f0; }
body.dark-mode .table td { border-color: #334155; color: #e2e8f0; }
body.dark-mode .sidebar-user-section { background: #1e293b; border-top-color: #334155; }
body.dark-mode .sidebar-user-profile { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
body.dark-mode .sidebar-user-name { color: #f1f5f9; }
body.dark-mode .sidebar-user-email { color: #94a3b8; }

/* Page Load Animations */
.page-content {
    animation: fadeInUp 0.5s ease-out forwards;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.card {
    animation: slideInUp 0.5s ease-out forwards;
    opacity: 0;
}
.card:nth-child(1) { animation-delay: 0.1s; }
.card:nth-child(2) { animation-delay: 0.2s; }
</style>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.querySelector('.sidebar-overlay').classList.toggle('show');
    document.getElementById('mainContent').classList.toggle('sidebar-open');
    document.getElementById('topbar').classList.toggle('sidebar-open');
}
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    document.documentElement.classList.toggle('dark-mode-bg');
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.card, .page-header').forEach(function(el, i) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        setTimeout(function() {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, i * 100);
    });
});
</script>

<body>
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand" style="justify-content: center; padding: 20px 0; gap: 12px;">
            <div class="brand-logo" style="width: 45px; height: 45px; background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);">
                <i class="fas fa-hand-holding-usd" style="font-size: 22px; color: white;"></i>
            </div>
            <span style="font-size: 22px; font-weight: 800; background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">FundHarmony</span>
        </div>
    </div>
    <div class="sidebar-menu">
        <a href="customer_dashboard.php"><div class="link-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"><i class="fas fa-grip-horizontal"></i></div> Dashboard</a>
        <a href="customer_my_loans.php"><div class="link-icon" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);"><i class="fas fa-coins"></i></div> My Loans</a>
        <a href="customer_make_payment.php"><div class="link-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"><i class="fas fa-money-bill-wave"></i></div> Make Payment</a>
        <a href="customer_payment_history.php"><div class="link-icon" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);"><i class="fas fa-history"></i></div> Payment History</a>
    </div>
    <div class="sidebar-user-section">
        <div class="sidebar-user-profile">
            <div class="sidebar-user-icon"><i class="fas fa-user-circle"></i></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Customer'); ?></div>
                <div class="sidebar-user-email"><?php echo htmlspecialchars($_SESSION['customer_email'] ?? 'customer@email.com'); ?></div>
            </div>
        </div>
        <div class="sidebar-user-actions">
            <a href="customer_profile.php"><i class="fas fa-id-card"></i> Profile</a>
            <a href="customer_logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>
<div class="topbar" id="topbar">
    <button class="menu-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <div class="topbar-brand">FundHarmony</div>
    <button class="menu-toggle" onclick="toggleTheme()"><i class="fas fa-moon"></i></button>
</div>
<div class="main-content page-content" id="mainContent">
    <div class="page-header">
        <h2>
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; margin-right: 12px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                <i class="fas fa-user-circle text-white" style="font-size: 24px;"></i>
            </span>
            My Account
        </h2>
        <p class="mb-0">View your account information and loan details</p>
    </div>

<?php
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
?>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-info-circle me-2"></i>Account Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-id-card me-2"></i>Customer ID:</strong> <?php echo $customer_id; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-user me-2"></i>Name:</strong> <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'NOT SET'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-money-check-alt me-2"></i>Your Loans
        </div>
        <div class="card-body">
<?php
    $my_loans = mysqli_query($conn, "SELECT a.account_number, a.customer, a.loan_amount, a.loan_balance, a.account_status, acs.account_status_name
    FROM accounts a 
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE a.customer = '$customer_id'");
    
    if (mysqli_num_rows($my_loans) > 0) {
        echo "<div class='table-responsive'><table class='table table-hover'><thead><tr><th>Account #</th><th>Amount</th><th>Balance</th><th>Status</th></tr></thead><tbody>";
        while ($row = mysqli_fetch_assoc($my_loans)) {
            $statusClass = '';
            if($row['account_status_name'] == 'Active' || $row['account_status_name'] == 'Up to Date') $statusClass = 'badge-success';
            elseif($row['account_status_name'] == 'Pending') $statusClass = 'badge-warning';
            elseif($row['account_status_name'] == 'Rejected' || $row['account_status_name'] == 'Declined') $statusClass = 'badge-danger';
            else $statusClass = 'badge-secondary';
            
            echo "<tr><td>{$row['account_number']}</td><td>₱".number_format($row['loan_amount'], 2)."</td><td>₱".number_format($row['loan_balance'], 2)."</td><td><span class='badge {$statusClass}'>{$row['account_status_name']}</span></td></tr>";
        }
        echo "</tbody></table></div>";
    } else {
        echo "<p class='text-muted'>No loans found for your account.</p>";
    }
?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-wrench me-2"></i>Account Fix
        </div>
        <div class="card-body">
            <p>Click below to update accounts to match your customer ID:</p>
            <?php
            mysqli_query($conn, "UPDATE accounts SET customer = '$customer_id' WHERE loan_amount > 0 OR loan_balance > 0");
            ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>Accounts updated successfully! Please refresh the Make Payment page.
            </div>
        </div>
    </div>
<?php
} else {
?>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Not Logged In</h5>
                <p>Please login first to view your account information.</p>
                <a href="customer_login.php" class="btn btn-primary">Login</a>
            </div>
        </div>
    </div>
<?php
}

mysqli_close($conn);
?>
</div>
</body>
