<?php
session_start();

$session_timeout = 86400; // 24 hours
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    header('Location: customer_login.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}
require_once 'include/head.php';
require_once 'database/db_connection.php';

$customer_id = $_SESSION['customer_id'];

$check_active = mysqli_query($conn, "SELECT a.account_number FROM accounts a 
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number 
    WHERE a.customer = '$customer_id' 
    AND acs.account_status_name IN ('Active', 'Partial', 'Up to Date', 'Due Date')
    AND a.loan_balance > 0
    LIMIT 1");
$has_active_loan = mysqli_num_rows($check_active) > 0;

// Check if customer account is active
$customer_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active, deactivated_date FROM customers WHERE customer_number = '$customer_id'"));
$is_account_active = isset($customer_check['is_active']) ? $customer_check['is_active'] : 1;
?>
<style>
html.dark-mode-bg {
    background: #0f172a !important;
}
body {
    background: #f0f2f5;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    transition: background-color 0.3s, color 0.3s;
    min-height: 100vh;
}
body.dark-mode {
    background: #0f172a;
    color: #e2e8f0;
}
body.dark-mode .main-content {
    background: #0f172a;
    min-height: 100vh;
}
body.dark-mode h2, 
body.dark-mode h3, 
body.dark-mode h4, 
body.dark-mode h5,
body.dark-mode p,
body.dark-mode .text-muted {
    color: #e2e8f0 !important;
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: -260px;
    width: 260px;
    height: 100vh;
    background: white;
    box-shadow: 2px 0 20px rgba(0,0,0,0.1);
    z-index: 1050;
    transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto;
}
body.dark-mode .sidebar {
    background: #1e293b;
    box-shadow: 2px 0 20px rgba(0,0,0,0.3);
}
.sidebar.show {
    left: 0;
}
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.sidebar-overlay.show {
    display: block;
    opacity: 1;
}
.sidebar-header {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.sidebar-header .brand {
    font-weight: 700;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}
.sidebar-menu {
    padding: 15px;
}
.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #555;
    text-decoration: none;
    border-radius: 10px;
    margin-bottom: 5px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    position: relative;
    overflow: hidden;
}

.sidebar-menu a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: -1;
}

.sidebar-menu a:hover {
    transform: translateX(5px);
}

.sidebar-menu a:hover::before {
    width: 100%;
}
body.dark-mode .sidebar-menu a {
    color: #e2e8f0 !important;
}
body.dark-mode .sidebar-menu a:hover, body.dark-mode .sidebar-menu a.active {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    color: white !important;
}
.sidebar-menu a i {
    width: 25px;
    margin-right: 10px;
}
.sidebar-menu a:hover, .sidebar-menu a.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

/* Topbar */
.topbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 1030;
    transition: background-color 0.3s, box-shadow 0.3s, left 0.3s ease;
}
body.dark-mode .topbar {
    background: #1e293b;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}
.topbar.sidebar-open {
    left: 260px;
    transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.menu-toggle {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #555;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
body.dark-mode .menu-toggle {
    color: #e2e8f0;
}
.menu-toggle:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    transform: scale(1.1);
}
.topbar-brand {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    font-weight: 700;
    font-size: 1.3rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: flex;
    align-items: center;
}
.topbar-brand i {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.topbar-actions {
    display: flex;
    align-items: center;
    gap: 5px;
}
.theme-toggle-btn {
    background: none;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    color: #555;
    font-weight: 500;
    transition: all 0.2s;
}
body.dark-mode .theme-toggle-btn {
    color: #e2e8f0;
}

body.dark-mode .lockout-timer {
    background: #7f1d1d !important;
    color: #fca5a5 !important;
}

.theme-toggle-btn:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

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

.user-dropdown-content.show {
  display: block;
}

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

.user-dropdown-content a:hover {
  background: #f3f4f6;
  color: #667eea;
}

.user-dropdown-content a.logout {
  color: #dc2626;
}

.user-dropdown-content a.logout:hover {
  background: #fee2e2;
}

.user-dropdown-content a i {
  width: 20px;
  margin-right: 12px;
}

.user-dropdown-content .divider {
  height: 1px;
  background: #e5e7eb;
  margin: 4px 0;
}

/* User Profile Section in Dropdown */
.user-dropdown-profile {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

.user-dropdown-profile .profile-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  flex-shrink: 0;
}

.user-dropdown-profile .profile-icon i {
  color: white;
  font-size: 16px;
}

.user-dropdown-profile .profile-info {
  overflow: hidden;
}

.user-dropdown-profile .profile-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 130px;
}

.user-dropdown-profile .profile-email {
  font-size: 11px;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 130px;
}

/* Dark mode for dropdown */
body.dark-mode .user-dropdown-content {
  background: #1e293b;
  border: 1px solid #334155;
}

body.dark-mode .user-dropdown-content a {
  color: #e2e8f0;
}

body.dark-mode .user-dropdown-content a:hover {
  background: #334155;
  color: #818cf8;
}

body.dark-mode .user-dropdown-content .divider {
  background: #334155;
}

body.dark-mode .user-dropdown-profile {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

body.dark-mode .user-dropdown-profile .profile-name {
  color: #f1f5f9;
}

body.dark-mode .user-dropdown-profile .profile-email {
  color: #94a3b8;
}

body.dark-mode .user-dropdown-btn {
  color: #e2e8f0;
}

body.dark-mode .user-dropdown-content a.logout { color: #f87171; }
body.dark-mode .user-dropdown-content a.logout:hover { background: #7f1d1d; color: #fca5a5; }

/* Sidebar User Section */
.sidebar-user-section {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 15px;
}

.sidebar-user-profile {
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 10px;
}

.sidebar-user-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
}

.sidebar-user-icon i {
    color: white;
    font-size: 22px;
}

.sidebar-user-info {
    overflow: hidden;
}

.sidebar-user-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-user-email {
    font-size: 12px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-user-actions {
    display: flex;
    gap: 5px;
}

.sidebar-user-actions a {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    color: #555;
    text-decoration: none;
    font-weight: 500;
    font-size: 12px;
    border-radius: 8px;
    transition: all 0.2s;
}

.sidebar-user-actions a:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.sidebar-user-actions a.text-danger:hover {
    background: #fee2e2;
    color: #dc2626;
}

.sidebar-user-actions a i {
    margin-right: 5px;
}

.sidebar {
    overflow-y: auto;
}

/* Dark mode for sidebar user section */
body.dark-mode .sidebar-user-section {
    background: #1e293b;
    border-top-color: #334155;
}

body.dark-mode .sidebar-user-profile {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

body.dark-mode .sidebar-user-name {
    color: #f1f5f9;
}

body.dark-mode .sidebar-user-email {
    color: #94a3b8;
}

body.dark-mode .sidebar-user-actions a {
    color: #e2e8f0;
}

body.dark-mode .sidebar-user-actions a:hover {
    background: #334155;
    color: #818cf8;
}

body.dark-mode .sidebar-user-actions a.text-danger:hover {
    background: #7f1d1d;
    color: #fca5a5;
}

body.dark-mode .sidebar-user-actions a.text-danger {
    color: #f87171 !important;
}

/* Main Content */
.main-content {
    margin-left: 0;
    padding: 80px 20px 20px;
    transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.main-content.sidebar-open {
    margin-left: 260px;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 20px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}
.page-header h2 {
    font-weight: 600;
    margin-bottom: 5px;
}
.page-header p {
    opacity: 0.9;
    font-size: 1rem;
}
.stat-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
    height: 100%;
}
body.dark-mode .stat-card {
    background: #1e293b;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    border: 1px solid #334155;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}
.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.stat-icon.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.stat-icon.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
.stat-icon.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}
body.dark-mode .stat-number { color: #f1f5f9; }
.stat-label {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}
body.dark-mode .stat-label { color: #94a3b8; }
.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
body.dark-mode .section-title { color: #f1f5f9; }
.section-title::after {
    content: '';
    flex: 1;
    height: 2px;
    background: linear-gradient(to right, #e5e7eb, transparent);
}
body.dark-mode .section-title::after {
    background: linear-gradient(to right, #334155, transparent);
}
.action-card {
    background: white;
    border-radius: 16px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    text-decoration: none;
    color: #374151;
    border: 2px solid transparent;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
body.dark-mode .action-card {
    background: #1e293b;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    border: 2px solid #334155;
    color: #e2e8f0;
}
.action-card:hover {
    transform: translateY(-5px);
    border-color: #667eea;
    color: #667eea;
    box-shadow: 0 12px 40px rgba(102, 126, 234, 0.2);
}
.action-icon {
    font-size: 36px;
    margin-bottom: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.action-title {
    font-weight: 600;
    font-size: 15px;
}
.dashboard-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    height: 100%;
    text-align: center;
}
body.dark-mode .dashboard-card {
    background: #1e293b;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.card-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    text-align: center;
}
body.dark-mode .card-title {
    color: #e2e8f0;
    border-bottom-color: #334155;
}
.quick-action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 25px 20px;
    border-radius: 12px;
    text-decoration: none;
    color: #555;
    transition: all 0.3s;
    background: #f8f9fa;
    text-align: center;
    width: 100%;
    max-width: 300px;
}
.quick-actions-row.horizontal .quick-action-item {
    padding: 20px 15px;
    max-width: 160px;
}
body.dark-mode .quick-action-item {
    background: #334155;
    color: #e2e8f0;
}
.quick-action-item:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-3px);
}
.quick-actions-row {
    display: flex;
    flex-direction: column;
    gap: 15px;
    align-items: center;
    width: 100%;
}
.quick-actions-row.horizontal {
    flex-direction: row;
    justify-content: center;
    gap: 25px;
}
.quick-actions-row.horizontal .quick-action-item {
    padding: 40px 35px;
    max-width: 280px;
    flex: 1;
}
.quick-actions-row.horizontal .action-icon-small {
    width: 80px;
    height: 80px;
    font-size: 32px;
    margin-bottom: 18px;
}
.quick-actions-row.horizontal .action-text {
    font-size: 28px;
}
.quick-actions-row.horizontal .action-subtext {
    font-size: 17px;
}
.quick-actions-row.horizontal .quick-action-item {
    max-width: 180px;
}
.action-icon-small {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    margin-bottom: 15px;
}
.action-text {
    font-weight: 600;
    font-size: 18px;
}
.action-subtext {
    font-size: 13px;
    color: #888;
    font-weight: 500;
    margin-top: 5px;
}
body.dark-mode .action-subtext {
    color: #aaa;
}
.stat-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 25px 15px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s;
    text-align: center;
}
body.dark-mode .stat-box {
    background: #334155;
}
.stat-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.stat-icon-box {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
    color: white;
}
.stat-icon-box.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.stat-icon-box.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.stat-icon-box.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}
.stat-info {
    text-align: center;
    width: 100%;
}
.stat-info .stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}
body.dark-mode .stat-info .stat-number {
    color: #e2e8f0;
}
.stat-info .stat-label {
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}
body.dark-mode .quick-action-item {
    background: #1e293b !important;
    color: #e2e8f0 !important;
}
body.dark-mode .action-text {
    color: #e2e8f0 !important;
}
body.dark-mode .action-subtext {
    color: #94a3b8 !important;
}
</style>

<body>

<style>
.session-timer {
    position: fixed;
    top: 70px;
    right: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    z-index: 9999 !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    display: flex !important;
    align-items: center;
    gap: 6px;
    visibility: visible !important;
}
.session-timer i {
    font-size: 12px;
}
.session-timer.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}
.session-timer.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>

<div class="session-timer" id="sessionTimer">
    <i class="fas fa-clock"></i>
    <span id="timerDisplay">5:00</span>
</div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                FundHarmony
            </div>
        </div>
        <div class="sidebar-menu">
            <a href="customer_dashboard.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <?php if($is_account_active): ?>
            <?php if(!$has_active_loan): ?>
            <a href="customer_apply_loan.php">
                <i class="fas fa-file-signature"></i> Apply for Loan
            </a>
            <?php else: ?>
            <a href="#" style="opacity:0.5;cursor:not-allowed;" title="You have an existing loan application">
                <i class="fas fa-file-signature"></i> Apply for Loan <i class="fas fa-lock fa-xs"></i>
            </a>
            <?php endif; ?>
            <a href="customer_my_loans.php">
                <i class="fas fa-money-check-alt"></i> My Loans
            </a>
            <a href="customer_make_payment.php">
                <i class="fas fa-credit-card"></i> Make Payment
            </a>
            <?php else: ?>
            <a href="#" style="opacity:0.5;cursor:not-allowed;" title="Account deactivated">
                <i class="fas fa-file-signature"></i> Apply for Loan <i class="fas fa-lock fa-xs"></i>
            </a>
            <a href="#" style="opacity:0.5;cursor:not-allowed;" title="Account deactivated">
                <i class="fas fa-money-check-alt"></i> My Loans <i class="fas fa-lock fa-xs"></i>
            </a>
            <a href="#" style="opacity:0.5;cursor:not-allowed;" title="Account deactivated">
                <i class="fas fa-credit-card"></i> Make Payment <i class="fas fa-lock fa-xs"></i>
            </a>
            <?php endif; ?>
            <a href="customer_payment_history.php">
                <i class="fas fa-history"></i> Payment History
            </a>
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
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-brand">
            FundHarmony
        </div>
        <div class="topbar-actions">
            <?php 
            $lock_info = isset($_SESSION['locked_until']) ? $_SESSION['locked_until'] : null;
            if ($lock_info): 
            ?>
            <div class="lockout-timer" style="background: #dc3545; color: white; padding: 5px 12px; border-radius: 8px; font-size: 12px; display: flex; align-items: center; gap: 5px; font-weight: 500;">
                <i class="fas fa-clock"></i> <span id="lockout-countdown" data-locktime="<?php echo $lock_info; ?>"></span>
            </div>
            <?php endif; ?>
            <button class="theme-toggle-btn" onclick="toggleTheme()">
                <i class="fas fa-moon" id="theme-icon"></i>
                <span id="theme-text" class="d-none d-md-inline">Dark</span>
            </button>
        </div>
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
            <p class="mb-0">Your account has been deactivated by the admin. You can still view your Payment History and My Profile.</p>
            <hr>
            <p class="mb-1"><strong>Days deactivated:</strong> <?php echo $days_deactivated; ?> day(s)</p>
            <p class="mb-0"><strong>Contact admin for reactivation (within 30 days):</strong> <br>
            <i class="fas fa-envelope me-1"></i> fundharmonycustomerservice@gmail.com <br>
            <i class="fas fa-phone me-1"></i> 09777698003</p>
        </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h2><i class="fas fa-user-circle me-2"></i>Welcome back, <?php echo $_SESSION['customer_name']; ?>!</h2>
            <p class="mb-0"><?php echo $is_account_active ? 'Track your loans and manage payments' : 'View your account information'; ?></p>
        </div>

        <?php
        $table_exists = false;
        $unread_notifications = [];
        $notif_count = 0;
        
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'loan_notifications'");
        if (mysqli_num_rows($table_check) > 0) {
            $table_exists = true;
            $notif_query = mysqli_query($conn, "SELECT * FROM loan_notifications WHERE customer_id = '$customer_id' AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
            $notif_count = mysqli_num_rows($notif_query);
            while ($notif = mysqli_fetch_assoc($notif_query)) {
                $unread_notifications[] = $notif;
            }
        }
        
        if ($notif_count > 0):
        ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; border: none; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
            <i class="fas fa-exclamation-triangle me-2 fs-4"></i>
            <div class="flex-grow-1">
                <strong><i class="fas fa-bell me-1"></i> IMPORTANT PAYMENT REMINDER</strong>
                <?php foreach($unread_notifications as $notif): ?>
                <div class="mt-2 p-2 bg-white bg-opacity-10 rounded" style="font-size: 13px;">
                    <strong>Account: <?php echo htmlspecialchars($notif['account_number']); ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($notif['message'])); ?>
                    <small class="d-block mt-1 opacity-75">Received: <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></small>
                </div>
                <?php endforeach; ?>
                <a href="app/mark_notifications_read.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-sm btn-warning mt-2" style="color: #000; font-weight: 600;">Mark as Read</a>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $loan_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts WHERE customer = '$customer_id'"));
        $active_loans = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts a 
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number 
    WHERE a.customer = '$customer_id' 
    AND acs.account_status_name IN ('Active', 'Partial', 'Up to Date', 'Due Date')
    AND a.loan_balance > 0"));
        $total_paid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments p INNER JOIN accounts a ON p.account_number = a.account_number WHERE a.customer = '$customer_id'"));
        ?>

        <div class="row g-4">
            <!-- Left Side: Quick Actions -->
            <div class="col-md-5">
                <div class="dashboard-card">
                    <h5 class="card-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    <?php if($is_account_active): ?>
                    <div class="quick-actions-row">
                        <a href="customer_my_loans.php" class="quick-action-item">
                            <div class="action-icon-small"><i class="fas fa-list"></i></div>
                            <span class="action-text">View Loans</span>
                        </a>
                        <a href="customer_payment_history.php" class="quick-action-item">
                            <div class="action-icon-small"><i class="fas fa-history"></i></div>
                            <span class="action-text">Payment History</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Your account is deactivated. Please contact support for assistance.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Side: Loan Stats -->
            <div class="col-md-7">
                <div class="dashboard-card">
                    <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Loan Summary</h5>
                    <div class="quick-actions-row horizontal">
                        <div class="quick-action-item" style="cursor: default;">
                            <div class="action-icon-small" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"><i class="fas fa-file-invoice-dollar"></i></div>
                            <span class="action-text"><?php echo $loan_count['cnt']; ?></span>
                            <span class="action-subtext">Total Loans</span>
                        </div>
                        <div class="quick-action-item" style="cursor: default;">
                            <div class="action-icon-small" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"><i class="fas fa-check-circle"></i></div>
                            <span class="action-text"><?php echo $active_loans['cnt']; ?></span>
                            <span class="action-subtext">Active Loans</span>
                        </div>
                        <div class="quick-action-item" style="cursor: default;">
                            <div class="action-icon-small" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"><i class="fas fa-money-bill-wave"></i></div>
                            <span class="action-text">₱<?php echo number_format($total_paid['total'], 0); ?></span>
                            <span class="action-subtext">Total Paid</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

    function toggleTheme() {
        const body = document.body;
        const html = document.documentElement;
        const icon = document.getElementById('theme-icon');
        const text = document.getElementById('theme-text');
        
        body.classList.toggle('dark-mode');
        html.classList.toggle('dark-mode-bg');
        
        if (body.classList.contains('dark-mode')) {
            html.style.backgroundColor = '#0f172a';
            body.style.backgroundColor = '#0f172a';
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            text.textContent = 'Light';
            localStorage.setItem('theme', 'dark');
        } else {
            html.style.backgroundColor = '#f8fafc';
            body.style.backgroundColor = '#f8fafc';
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            text.textContent = 'Dark';
            localStorage.setItem('theme', 'light');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        const icon = document.getElementById('theme-icon');
        const text = document.getElementById('theme-text');
        
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            document.documentElement.classList.add('dark-mode-bg');
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            text.textContent = 'Light';
        }
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let time = 300; // 5 minutes
        let timeLeft = time;
        let countdownInterval;
        let resetTimer;
        const timerDisplay = document.getElementById('timerDisplay');
        const sessionTimer = document.getElementById('sessionTimer');
        
        function updateDisplay() {
            if (!timerDisplay || !sessionTimer) return;
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            timerDisplay.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            
            sessionTimer.classList.remove('warning', 'danger');
            if (timeLeft <= 60) {
                sessionTimer.classList.add('danger');
            } else if (timeLeft <= 120) {
                sessionTimer.classList.add('warning');
            }
        }
        
        function logout() {
            clearInterval(countdownInterval);
            window.location.href = 'customer_logout.php?timeout=1';
        }
        
        function startCountdown() {
            timeLeft = time;
            updateDisplay();
            clearInterval(countdownInterval);
            countdownInterval = setInterval(() => {
                timeLeft--;
                updateDisplay();
                if (timeLeft <= 0) {
                    logout();
                }
            }, 1000);
        }
        
        function resetSession() {
            clearTimeout(resetTimer);
            resetTimer = setTimeout(logout, time * 1000);
            startCountdown();
        }
        
        document.addEventListener('mousemove', resetSession);
        document.addEventListener('keypress', resetSession);
        document.addEventListener('click', resetSession);
        document.addEventListener('scroll', resetSession);
        
        startCountdown();
        
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
                    lockoutEl.parentElement.style.display = 'none';
                }
            }
            updateLockout();
            setInterval(updateLockout, 1000);
        }
    });
    </script>
</body>
</html>
