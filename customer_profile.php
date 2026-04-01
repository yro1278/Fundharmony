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

if(isset($_POST['update'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $surname = mysqli_real_escape_string($conn, $_POST['surname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
    
    mysqli_query($conn, "UPDATE customers SET first_name='$first_name', surname='$surname', phone='$phone', nationality='$nationality' WHERE customer_number='$customer_id'");
    
    $_SESSION['customer_name'] = $first_name . ' ' . $surname;
    $success = "Profile updated successfully!";
}

$customer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT c.*, u.username, u.email as admin_email FROM customers c LEFT JOIN users u ON c.user_id = u.user_number WHERE c.customer_number = '$customer_id'"));
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
.profile-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: background-color 0.3s, box-shadow 0.3s;
}
body.dark-mode .profile-card {
    background: #1e293b;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
    text-align: center;
    color: white;
}
.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 50px;
    color: #667eea;
    margin: 0 auto 15px;
}
.form-group { margin-bottom: 20px; }
.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
}
body.dark-mode .form-group label {
    color: #e2e8f0;
}
.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s;
}
.form-group input:focus {
    outline: none;
    border-color: #667eea;
}
body.dark-mode .form-group input {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
}
.btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}
.sidebar { position: fixed; top: 0; left: -260px; width: 260px; height: 100vh; background: white; box-shadow: 2px 0 15px rgba(0,0,0,0.1); z-index: 1050; transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto; }
.sidebar.show { left: 0; }
.sidebar-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; display: none; opacity: 0; transition: opacity 0.3s ease; }
.sidebar-overlay.show { display: block; opacity: 1; }
.sidebar-header { padding: 20px; background: transparent; border-bottom: 1px solid #e5e7eb; }
body.dark-mode .sidebar-header { background: transparent; border-bottom-color: #334155; }
.sidebar-header .brand { font-weight: 700; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
.sidebar-menu { padding: 15px; }
.sidebar-menu a { display: flex; align-items: center; padding: 12px 15px; color: #555; text-decoration: none; border-radius: 10px; margin-bottom: 5px; font-weight: 500; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
.sidebar-menu a::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: -1; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: translateX(5px); }
.sidebar-menu a:hover::before { width: 100%; }
.sidebar-menu a i { transition: transform 0.3s ease; }
.sidebar-menu a { display: flex; align-items: center; }
.sidebar-menu a:hover i, .sidebar-menu a.active i { transform: scale(1.1); }
.sidebar-menu a:hover, .sidebar-menu a.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
.link-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 13px; margin-right: 12px; flex-shrink: 0; }
.link-icon i { width: auto; margin-right: 0; }
.topbar { position: fixed; top: 0; left: 0; right: 0; height: 60px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: space-between; padding: 0 25px; z-index: 1030; transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.topbar-brand { position: absolute; left: 50%; transform: translateX(-50%); font-weight: 700; font-size: 1.3rem; background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; display: flex; align-items: center; }
.topbar-brand i { background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.topbar.sidebar-open { left: 260px; }
.menu-toggle { background: none; border: none; font-size: 1.25rem; color: #555; cursor: pointer; padding: 8px; border-radius: 8px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.menu-toggle:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; transform: scale(1.1); }
.main-content { margin-left: 0; padding: 80px 25px 25px; transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.main-content.sidebar-open { margin-left: 260px; }
.theme-toggle-btn { background: none; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #555; font-weight: 500; }

/* Dark Mode Sidebar */
body.dark-mode .sidebar { background: #1e293b !important; }
body.dark-mode .sidebar-menu a { color: #e2e8f0 !important; }
body.dark-mode .sidebar-menu a:hover, body.dark-mode .sidebar-menu a.active { background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%); color: white !important; }
body.dark-mode .topbar { background: #1e293b !important; }
body.dark-mode .menu-toggle { color: #e2e8f0 !important; }
body.dark-mode .theme-toggle-btn { color: #e2e8f0 !important; }
body.dark-mode .topbar-brand { 
    background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 30px rgba(129, 140, 248, 0.5);
    filter: drop-shadow(0 0 8px rgba(129, 140, 248, 0.4));
}
body.dark-mode .sidebar-brand span { 
    background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%); 
    -webkit-background-clip: text; 
    -webkit-text-fill-color: transparent; 
    text-shadow: 0 0 30px rgba(129, 140, 248, 0.5);
    filter: drop-shadow(0 0 8px rgba(129, 140, 248, 0.4));
}
body.dark-mode .sidebar-header .brand-logo {
    box-shadow: 0 6px 25px rgba(129, 140, 248, 0.6);
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

.user-dropdown-btn .fa-chevron-down {
  font-size: 10px;
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

body.dark-mode .user-dropdown-content a.logout { color: #f87171 !important; }
body.dark-mode .user-dropdown-content a.logout:hover { background: #7f1d1d; color: #fca5a5 !important; }

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

.sidebar-user-actions a.active {
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
    color: #f87171;
}

/* Sidebar scroll */
.sidebar {
    overflow-y: auto;
}

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

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
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
            <?php if(!$has_active_loan): ?><a href="customer_apply_loan.php"><div class="link-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"><i class="fas fa-file-signature"></i></div> Apply for Loan</a><?php else: ?><a href="#" style="opacity:0.5;cursor:not-allowed;" title="You have an existing loan application"><div class="link-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"><i class="fas fa-file-signature"></i></div> Apply for Loan <i class="fas fa-lock fa-xs"></i></a><?php endif; ?>
            <a href="customer_my_loans.php"><div class="link-icon" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);"><i class="fas fa-coins"></i></div> My Loans</a>
            <a href="customer_make_payment.php"><div class="link-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"><i class="fas fa-money-bill-wave"></i></div> Make Payment</a>
            <a href="customer_payment_history.php"><div class="link-icon" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);"><i class="fas fa-history"></i></div> Payment History</a>
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
                <a href="customer_profile.php" class="active"><i class="fas fa-id-card"></i> My Profile</a>
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
    <div class="main-content page-content" id="mainContent">
        <h4 class="mb-4">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; margin-right: 10px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                <i class="fas fa-id-card text-white"></i>
            </span>
            My Profile
        </h4>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar"><i class="fas fa-user"></i></div>
                        <h3><?php echo $_SESSION['customer_name']; ?></h3>
                        <p><?php echo $_SESSION['customer_email']; ?></p>
                    </div>
                    <div class="p-4">
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Number</label>
                                        <input type="text" value="<?php echo $customer['customer_number']; ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" value="<?php echo $customer['email']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" value="<?php echo $customer['first_name']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Surname</label>
                                        <input type="text" name="surname" value="<?php echo $customer['surname']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" value="<?php echo $customer['phone']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nationality</label>
                                        <input type="text" name="nationality" value="<?php echo $customer['nationality']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <input type="text" value="<?php echo $customer['gender'] == 'M' ? 'Male' : 'Female'; ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="text" value="<?php echo !empty($customer['date_of_birth']) ? date('F j, Y', strtotime($customer['date_of_birth'])) : 'Not set'; ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Nationality</label>
                                <input type="text" value="<?php echo $customer['nationality']; ?>" disabled>
                            </div>
                            <hr>
                            <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Region</label>
                                        <input type="text" value="<?php echo $customer['region']; ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>City/Municipality</label>
                                        <input type="text" value="<?php echo $customer['city']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Barangay</label>
                                        <input type="text" value="<?php echo $customer['barangay']; ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Zip Code</label>
                                        <input type="text" value="<?php echo $customer['zip_code']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Full Address</label>
                                <input type="text" value="<?php echo $customer['full_address']; ?>" disabled>
                            </div>
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
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
    
    // Apply saved theme preference
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    })();
    </script>
</body>
</html>
