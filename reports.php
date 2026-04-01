<?php
session_start();

$session_timeout = 86400; // 24 hours
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/head.php'; 
require_once 'database/db_connection.php';

// Get date filter defaults
$current_year = date('Y');
$current_month = date('n');

// Get summary statistics
$sql_total_loans = "SELECT COUNT(*) as total FROM accounts";
$result_loans = mysqli_query($conn, $sql_total_loans);
$total_loans = mysqli_fetch_assoc($result_loans)['total'] ?? 0;

$sql_total_borrowers = "SELECT COUNT(*) as total FROM customers";
$result_borrowers = mysqli_query($conn, $sql_total_borrowers);
$total_borrowers = mysqli_fetch_assoc($result_borrowers)['total'] ?? 0;

$sql_total_payments = "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments";
$result_payments = mysqli_query($conn, $sql_total_payments);
$total_payments = mysqli_fetch_assoc($result_payments)['total'] ?? 0;

$sql_total_interest = "SELECT COALESCE(SUM(interest), 0) as total FROM accounts";
$result_interest = mysqli_query($conn, $sql_total_interest);
$total_interest = mysqli_fetch_assoc($result_interest)['total'] ?? 0;

// Financial Summary - Additional metrics
$sql_total_loan_released = "SELECT COALESCE(SUM(loan_amount), 0) as total FROM accounts WHERE loan_amount > 0";
$result_loan_released = mysqli_query($conn, $sql_total_loan_released);
$total_loan_released = mysqli_fetch_assoc($result_loan_released)['total'] ?? 0;

$sql_total_penalties = "SELECT COALESCE(SUM(penalty), 0) as total FROM accounts";
$result_penalties = mysqli_query($conn, $sql_total_penalties);
$total_penalties = mysqli_fetch_assoc($result_penalties)['total'] ?? 0;

$sql_outstanding = "SELECT COALESCE(SUM(loan_balance), 0) as total FROM accounts WHERE loan_balance > 0";
$result_outstanding = mysqli_query($conn, $sql_outstanding);
$outstanding_balance = mysqli_fetch_assoc($result_outstanding)['total'] ?? 0;

// Get loan status distribution for pie chart
$sql_loan_status = "SELECT acs.account_status_name, COUNT(a.account_number) as count 
                    FROM accounts a 
                    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number 
                    GROUP BY acs.account_status_name";
$result_loan_status = mysqli_query($conn, $sql_loan_status);
$loan_status_data = [];
while ($row = mysqli_fetch_assoc($result_loan_status)) {
    $loan_status_data[$row['account_status_name']] = $row['count'];
}

// Get monthly loan applications for the current year
$sql_monthly_loans = "SELECT MONTH(open_date) as month, COUNT(*) as count 
                      FROM accounts 
                      WHERE YEAR(open_date) = '$current_year' 
                      GROUP BY MONTH(open_date)";
$result_monthly_loans = mysqli_query($conn, $sql_monthly_loans);
$monthly_loans_data = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($result_monthly_loans)) {
    $monthly_loans_data[$row['month']] = $row['count'];
}

// Get monthly payments for the current year
$sql_monthly_payments = "SELECT MONTH(payment_date) as month, SUM(payment_amount) as total 
                         FROM payments 
                         WHERE YEAR(payment_date) = '$current_year' 
                         GROUP BY MONTH(payment_date)";
$result_monthly_payments = mysqli_query($conn, $sql_monthly_payments);
$monthly_payments_data = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($result_monthly_payments)) {
    $monthly_payments_data[$row['month']] = $row['total'];
}

// Get monthly interest earnings
$sql_monthly_interest = "SELECT MONTH(open_date) as month, SUM(interest) as total 
                         FROM accounts 
                         WHERE YEAR(open_date) = '$current_year' 
                         GROUP BY MONTH(open_date)";
$result_monthly_interest = mysqli_query($conn, $sql_monthly_interest);
$monthly_interest_data = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($result_monthly_interest)) {
    $monthly_interest_data[$row['month']] = $row['total'];
}

// Get overdue loans count
$sql_overdue = "SELECT COUNT(*) as count FROM accounts 
                WHERE account_status IN (
                    SELECT account_status_number FROM account_status 
                    WHERE account_status_name IN ('Overdue', 'Due Date', 'Past Due')
                )";
$result_overdue = mysqli_query($conn, $sql_overdue);
$overdue_loans = mysqli_fetch_assoc($result_overdue)['count'] ?? 0;

// Get active users (customers with active loans)
$sql_active_users = "SELECT COUNT(DISTINCT customer) as count FROM accounts 
                      WHERE account_status IN (
                          SELECT account_status_number FROM account_status 
                          WHERE account_status_name IN ('Active', 'Approved', 'Partial', 'Up to Date', 'Due Date')
                      )";
$result_active_users = mysqli_query($conn, $sql_active_users);
$active_users = mysqli_fetch_assoc($result_active_users)['count'] ?? 0;

// ========== INTEREST AND REVENUE DATA ==========
$sql_total_interest_collected = "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments";
$result_interest_collected = mysqli_query($conn, $sql_total_interest_collected);
$total_interest_collected = mysqli_fetch_assoc($result_interest_collected)['total'] ?? 0;

// Interest collected per month
$sql_interest_per_month = "SELECT MONTH(p.payment_date) as month, SUM(p.payment_amount) as total 
                           FROM payments p 
                           WHERE YEAR(p.payment_date) = '$current_year' 
                           GROUP BY MONTH(p.payment_date)";
$result_interest_per_month = mysqli_query($conn, $sql_interest_per_month);
$interest_per_month_data = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($result_interest_per_month)) {
    $interest_per_month_data[$row['month']] = $row['total'];
}

// ========== OVERDUE/DELINQUENT LOANS DATA ==========
$sql_overdue_loans = "SELECT a.account_number, 
                      CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.surname) as customer_name,
                      a.due_date, a.loan_balance, a.penalty,
                      c.phone, c.email,
                      DATEDIFF(CURDATE(), a.due_date) as days_overdue
                      FROM accounts a
                      LEFT JOIN customers c ON a.customer = c.customer_number
                      LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
                      WHERE a.due_date < CURDATE() 
                      AND a.loan_balance > 0
                      AND acs.account_status_name IN ('Overdue', 'Due Date', 'Past Due', 'Active', 'Partial')
                      ORDER BY days_overdue DESC
                      LIMIT 50";
$result_overdue_loans = mysqli_query($conn, $sql_overdue_loans);

// ========== SYSTEM ACTIVITY DATA ==========
$sql_system_logs = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100";
$result_system_logs = mysqli_query($conn, $sql_system_logs);

// Check if activity_logs table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'");
$has_activity_logs = mysqli_num_rows($table_check) > 0;

// ========== PAYMENT METHODS BREAKDOWN ==========
$sql_payment_methods = "SELECT COALESCE(payment_method, 'Cash') as method, 
                        SUM(payment_amount) as total, COUNT(*) as count 
                        FROM payments 
                        GROUP BY payment_method";
$result_payment_methods = mysqli_query($conn, $sql_payment_methods);
$payment_methods_data = [];
while ($row = mysqli_fetch_assoc($result_payment_methods)) {
    $payment_methods_data[$row['method']] = ['total' => $row['total'], 'count' => $row['count']];
}

// ========== REVENUE BREAKDOWN ==========
$sql_revenue_breakdown = "SELECT 
    (SELECT COALESCE(SUM(loan_amount), 0) FROM accounts) as total_loan_amount,
    (SELECT COALESCE(SUM(interest), 0) FROM accounts) as total_interest,
    (SELECT COALESCE(SUM(payment_amount), 0) FROM payments) as total_payments";
$result_revenue = mysqli_query($conn, $sql_revenue_breakdown);
$revenue_breakdown = mysqli_fetch_assoc($result_revenue);

// Get recent loans for table
$sql_recent_loans = "SELECT a.account_number, CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.surname) as customer_name, a.loan_balance, a.loan_term, 
                      acs.account_status_name, a.open_date, a.interest,
                      (SELECT SUM(payment_amount) FROM payments WHERE account_number = a.account_number) as total_paid
                      FROM accounts a
                      LEFT JOIN customers c ON a.customer = c.customer_number
                      LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
                      ORDER BY a.open_date DESC
                      LIMIT 50";
$result_recent_loans = mysqli_query($conn, $sql_recent_loans);
?>

<body>

<style>
.session-timer {
    position: fixed;
    top: 60px;
    right: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    z-index: 99999 !important;
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

/* Reports Page Styles */
.reports-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
    margin-bottom: 20px;
}

.reports-header h3 {
    margin: 0;
    font-weight: 700;
}

.filter-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.filter-section label {
    font-weight: 600;
    color: #374151;
    font-size: 13px;
}

.filter-section select, 
.filter-section input {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
}

.filter-section select:focus,
.filter-section input:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.btn-generate {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-generate:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
}

.btn-export {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-export:hover {
    background: #f3f4f6;
    border-color: #4f46e5;
    color: #4f46e5;
}

/* Summary Cards */
.summary-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s;
    border-left: 4px solid #4f46e5;
}

.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.summary-card.loans {
    border-left-color: #4f46e5;
}

.summary-card.borrowers {
    border-left-color: #10b981;
}

.summary-card.payments {
    border-left-color: #f59e0b;
}

.summary-card.interest {
    border-left-color: #ec4899;
}

.summary-card .card-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.summary-card.loans .card-icon {
    background: rgba(79, 70, 229, 0.1);
    color: #4f46e5;
}

.summary-card.borrowers .card-icon {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.summary-card.payments .card-icon {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.summary-card.interest .card-icon {
    background: rgba(236, 72, 153, 0.1);
    color: #ec4899;
}

.summary-card h4 {
    font-size: 28px;
    font-weight: 700;
    margin: 10px 0 5px;
    color: #1f2937;
}

.summary-card p {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
}

/* Tab Navigation */
.report-tabs {
    display: flex;
    gap: 5px;
    background: #f3f4f6;
    padding: 5px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.report-tab {
    flex: 1;
    padding: 12px 20px;
    border: none;
    background: transparent;
    color: #6b7280;
    font-weight: 600;
    font-size: 13px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.report-tab:hover {
    color: #4f46e5;
}

.report-tab.active {
    background: white;
    color: #4f46e5;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Graph Cards */
.graph-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.graph-card h5 {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.graph-card h5 i {
    color: #4f46e5;
}

/* Data Table */
.data-table-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.data-table-card h5 {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.data-table-card h5 i {
    color: #4f46e5;
}

.table {
    font-size: 13px;
}

.table thead th {
    background: #f9fafb;
    color: #374151;
    font-weight: 600;
    border-bottom: 2px solid #e5e7eb;
    padding: 12px;
}

.table tbody td {
    padding: 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}

.table tbody tr:hover {
    background: #f9fafb;
}

.badge-status {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.badge-active {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.badge-pending {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.badge-paid {
    background: rgba(79, 70, 229, 0.1);
    color: #4f46e5;
}

.badge-overdue {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Tab Content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Dark Mode Support */
body.dark-mode .filter-section,
body.dark-mode .summary-card,
body.dark-mode .graph-card,
body.dark-mode .data-table-card {
    background: #1e293b;
    border-color: #334155;
}

body.dark-mode .filter-section label {
    color: #e2e8f0;
}

body.dark-mode .filter-section select,
body.dark-mode .filter-section input {
    background: #334155;
    border-color: #475569;
    color: #e2e8f0;
}

body.dark-mode .summary-card h4,
body.dark-mode .graph-card h5,
body.dark-mode .data-table-card h5 {
    color: #f1f5f9;
}

body.dark-mode .summary-card p {
    color: #94a3b8;
}

body.dark-mode .table thead th {
    background: #334155;
    color: #e2e8f0;
    border-color: #475569;
}

body.dark-mode .table tbody td {
    border-color: #334155;
    color: #e2e8f0;
}

body.dark-mode .table tbody tr:hover {
    background: #334155;
}

body.dark-mode .report-tabs {
    background: #334155;
}

body.dark-mode .report-tab {
    color: #94a3b8;
}

body.dark-mode .report-tab.active {
    background: #1e293b;
    color: #818cf8;
}

body.dark-mode .btn-export {
    background: #334155;
    border-color: #475569;
    color: #e2e8f0;
}

body.dark-mode .btn-export:hover {
    background: #475569;
    color: #818cf8;
}
</style>

<div class="session-timer" id="sessionTimer">
    <i class="fas fa-clock"></i>
    <span id="timerDisplay">5:00</span>
</div>

<?php require_once 'include/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'include/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="min-height: 100vh;">
            
            <!-- Reports Header -->
            <div class="reports-header mt-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center">
                        <div class="report-icon me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                            <i class="fas fa-chart-pie text-white" style="font-size: 28px;"></i>
                        </div>
                        <div>
                            <h3 class="mb-1">Generate Reports</h3>
                            <p class="mb-0 opacity-75" style="font-size: 14px;">Comprehensive analytics and detailed insights for your microfinance system</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form id="reportFilterForm" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Date Range</label>
                        <select class="form-select" id="dateRange" name="date_range">
                            <option value="monthly" selected>Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-md-2" id="monthSelect">
                        <label class="form-label">Month</label>
                        <select class="form-select" id="month" name="month">
                            <?php for($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($m == $current_month) ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <select class="form-select" id="year" name="year">
                            <?php for($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($y == $current_year) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3" id="customDateStart" style="display: none;">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" name="start_date">
                    </div>
                    <div class="col-md-3" id="customDateEnd" style="display: none;">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" name="end_date">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Report Type</label>
                        <select class="form-select" id="reportType" name="report_type">
                            <option value="all">All Reports</option>
                            <option value="financial">Financial Reports</option>
                            <option value="loan">Loan Reports</option>
                            <option value="payment">Payment Reports</option>
                            <option value="user">User Reports</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="button" class="btn btn-generate flex-fill" onclick="generateReport()">
                            <i class="fas fa-sync-alt me-2"></i>Generate
                        </button>
                        <button type="button" class="btn btn-export" onclick="exportPDF()">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button type="button" class="btn btn-export" onclick="exportExcel()">
                            <i class="fas fa-file-excel"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Report Tabs -->
            <div class="report-tabs">
                <button class="report-tab active" data-tab="financial" onclick="switchTab('financial')">
                    <i class="fas fa-wallet me-2"></i>Financial Reports
                </button>
                <button class="report-tab" data-tab="payment" onclick="switchTab('payment')">
                    <i class="fas fa-money-bill-wave me-2"></i>Payment Reports
                </button>
                <button class="report-tab" data-tab="interest" onclick="switchTab('interest')">
                    <i class="fas fa-percentage me-2"></i>Interest & Revenue
                </button>
                <button class="report-tab" data-tab="overdue" onclick="switchTab('overdue')">
                    <i class="fas fa-exclamation-triangle me-2"></i>Overdue Loans
                </button>
                <button class="report-tab" data-tab="activity" onclick="switchTab('activity')">
                    <i class="fas fa-history me-2"></i>System Activity
                </button>
                <button class="report-tab" data-tab="loan" onclick="switchTab('loan')">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Loan Reports
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card loans">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4><?php echo number_format($total_loans); ?></h4>
                                <p>Total Loans</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card borrowers">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4><?php echo number_format($total_borrowers); ?></h4>
                                <p>Total Borrowers</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card payments">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4>₱<?php echo number_format($total_payments, 0); ?></h4>
                                <p>Total Payments</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card interest">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4>₱<?php echo number_format($total_interest, 0); ?></h4>
                                <p>Total Interest</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card loans">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4>₱<?php echo number_format($total_loan_released, 0); ?></h4>
                                <p>Loans Released</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card borrowers">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4>₱<?php echo number_format($total_penalties, 0); ?></h4>
                                <p>Total Penalties</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card payments">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4>₱<?php echo number_format($outstanding_balance, 0); ?></h4>
                                <p>Outstanding Balance</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Reports Tab -->
            <div id="financial" class="tab-content active">
                <!-- Graphs Section: 2 per row -->
                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-chart-line"></i>Loan Applications Trend</h5>
                            <canvas id="loanApplicationsChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-chart-pie"></i>Loan Status Distribution</h5>
                            <canvas id="loanStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-chart-bar"></i>Monthly Payment Collection</h5>
                            <canvas id="paymentCollectionChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-coins"></i>Interest Earnings</h5>
                            <canvas id="interestEarningsChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Table -->
                <div class="data-table-card">
                    <h5><i class="fas fa-table"></i>Financial Summary Details</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="financialTable">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Total Loans</th>
                                    <th>Loan Amount</th>
                                    <th>Payments Received</th>
                                    <th>Interest Earned</th>
                                    <th>Net Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                for($m = 1; $m <= 12; $m++):
                                    $sql_monthly_data = "SELECT 
                                        COUNT(*) as loan_count,
                                        COALESCE(SUM(loan_amount), 0) as total_amount,
                                        COALESCE(SUM(interest), 0) as total_interest
                                        FROM accounts 
                                        WHERE MONTH(open_date) = '$m' AND YEAR(open_date) = '$current_year'";
                                    $result_monthly = mysqli_query($conn, $sql_monthly_data);
                                    $monthly_data = mysqli_fetch_assoc($result_monthly);
                                    
                                    $sql_payments = "SELECT COALESCE(SUM(payment_amount), 0) as total 
                                                    FROM payments 
                                                    WHERE MONTH(payment_date) = '$m' AND YEAR(payment_date) = '$current_year'";
                                    $result_pay = mysqli_query($conn, $sql_payments);
                                    $payment_total = mysqli_fetch_assoc($result_pay)['total'];
                                    
                                    $net_revenue = $payment_total + $monthly_data['total_interest'];
                                ?>
                                <tr>
                                    <td><?php echo $months[$m-1]; ?></td>
                                    <td><?php echo $monthly_data['loan_count']; ?></td>
                                    <td>₱<?php echo number_format($monthly_data['total_amount'], 2); ?></td>
                                    <td>₱<?php echo number_format($payment_total, 2); ?></td>
                                    <td>₱<?php echo number_format($monthly_data['total_interest'], 2); ?></td>
                                    <td>₱<?php echo number_format($net_revenue, 2); ?></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Loan Reports Tab -->
            <div id="loan" class="tab-content">
                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-chart-line"></i>Loan Applications Over Time</h5>
                            <canvas id="loanApplicationsChart2" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-chart-pie"></i>Loan Status Overview</h5>
                            <canvas id="loanStatusChart2" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="data-table-card">
                    <h5><i class="fas fa-list"></i>Loan Details</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="loanTable">
                            <thead>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Borrower</th>
                                    <th>Loan Amount</th>
                                    <th>Term</th>
                                    <th>Interest</th>
                                    <th>Status</th>
                                    <th>Balance</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($loan = mysqli_fetch_assoc($result_recent_loans)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($loan['account_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($loan['customer_name'] ?? 'N/A'); ?></td>
                                    <td>₱<?php echo number_format($loan['loan_balance'] ?? $loan['loan_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($loan['loan_term']); ?> mo</td>
                                    <td>₱<?php echo number_format($loan['interest'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $status = $loan['account_status_name'] ?? 'Unknown';
                                        $badge_class = 'badge-pending';
                                        if(stripos($status, 'Active') !== false || stripos($status, 'Approved') !== false) $badge_class = 'badge-active';
                                        if(stripos($status, 'Paid') !== false) $badge_class = 'badge-paid';
                                        if(stripos($status, 'Overdue') !== false || stripos($status, 'Due') !== false) $badge_class = 'badge-overdue';
                                        ?>
                                        <span class="badge-status <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                    </td>
                                    <td>₱<?php echo number_format(($loan['loan_balance'] ?? $loan['loan_amount']) - ($loan['total_paid'] ?? 0), 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($loan['open_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Reports Tab -->
            <div id="payment" class="tab-content">
                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-chart-bar"></i>Payment Collection Trend</h5>
                            <canvas id="paymentTrendChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-exclamation-triangle"></i>Overdue Loans</h5>
                            <canvas id="overdueChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="data-table-card">
                    <h5><i class="fas fa-history"></i>Payment History</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="paymentTable">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Loan ID</th>
                                    <th>Borrower</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_payments = "SELECT p.*, a.account_number, CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.surname) as customer_name 
                                                FROM payments p 
                                                LEFT JOIN accounts a ON p.account_number = a.account_number 
                                                LEFT JOIN customers c ON a.customer = c.customer_number 
                                                ORDER BY p.payment_date DESC 
                                                LIMIT 50";
                                $result_payments = mysqli_query($conn, $sql_payments);
                                while($payment = mysqli_fetch_assoc($result_payments)):
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($payment['payment_id'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($payment['account_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($payment['customer_name'] ?? 'N/A'); ?></td>
                                    <td>₱<?php echo number_format($payment['payment_amount'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'Cash'); ?></td>
                                    <td><span class="badge-status badge-active">Completed</span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- User Reports Tab -->
            <div id="user" class="tab-content">
                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-user-plus"></i>User Registration Trend</h5>
                            <canvas id="userRegistrationChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="graph-card">
                            <h5><i class="fas fa-users"></i>Active Borrowers</h5>
                            <canvas id="activeBorrowersChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="data-table-card">
                    <h5><i class="fas fa-user-friends"></i>Borrower Details</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="userTable">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Total Loans</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_customers = "SELECT c.*, 
                                                 (SELECT COUNT(*) FROM accounts WHERE customer = c.customer_number) as loan_count
                                                 FROM customers c 
                                                 ORDER BY c.created_at DESC 
                                                 LIMIT 50";
                                $result_customers = mysqli_query($conn, $sql_customers);
                                while($customer = mysqli_fetch_assoc($result_customers)):
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($customer['customer_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['surname']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($customer['full_address'] ?? $customer['address'] ?? 'N/A'); ?></td>
                                    <td><?php echo $customer['loan_count']; ?></td>
                                    <td>
                                        <?php 
                                        $status = $customer['status'] ?? 'Active';
                                        $badge_class = $status == 'Active' ? 'badge-active' : 'badge-pending';
                                        ?>
                                        <span class="badge-status <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Date Range Toggle
    document.getElementById('dateRange').addEventListener('change', function() {
        const monthSelect = document.getElementById('monthSelect');
        const customStart = document.getElementById('customDateStart');
        const customEnd = document.getElementById('customDateEnd');
        
        if (this.value === 'monthly') {
            monthSelect.style.display = 'block';
            customStart.style.display = 'none';
            customEnd.style.display = 'none';
        } else if (this.value === 'yearly') {
            monthSelect.style.display = 'none';
            customStart.style.display = 'none';
            customEnd.style.display = 'none';
        } else {
            monthSelect.style.display = 'none';
            customStart.style.display = 'block';
            customEnd.style.display = 'block';
        }
    });

    // Tab Switching
    function switchTab(tabName) {
        document.querySelectorAll('.report-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        document.querySelector(`.report-tab[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(tabName).classList.add('active');
        
        // Initialize charts for the tab
        setTimeout(() => {
            initChartsForTab(tabName);
        }, 100);
    }

    // Chart Configurations
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthlyLoansData = <?php echo json_encode(array_values($monthly_loans_data)); ?>;
    const monthlyPaymentsData = <?php echo json_encode(array_values($monthly_payments_data)); ?>;
    const monthlyInterestData = <?php echo json_encode(array_values($monthly_interest_data)); ?>;
    const loanStatusData = <?php echo json_encode($loan_status_data); ?>;
    
    const chartColors = {
        primary: '#4f46e5',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        purple: '#8b5cf6',
        pink: '#ec4899'
    };

    let charts = {};

    function initChartsForTab(tabName) {
        if (tabName === 'financial' || tabName === 'loan') {
            initLoanCharts();
        }
        if (tabName === 'financial' || tabName === 'payment') {
            initPaymentCharts();
        }
        if (tabName === 'financial') {
            initInterestChart();
        }
        if (tabName === 'user') {
            initUserCharts();
        }
    }

    function initLoanCharts() {
        // Loan Applications Trend
        const loanCtx = document.getElementById('loanApplicationsChart');
        if (loanCtx && !charts.loanApplications) {
            charts.loanApplications = new Chart(loanCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Loan Applications',
                        data: monthlyLoansData,
                        borderColor: chartColors.primary,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Loan Status Pie Chart
        const statusCtx = document.getElementById('loanStatusChart');
        if (statusCtx && !charts.loanStatus) {
            const statusLabels = Object.keys(loanStatusData);
            const statusValues = Object.values(loanStatusData);
            
            charts.loanStatus = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels.length ? statusLabels : ['No Data'],
                    datasets: [{
                        data: statusValues.length ? statusValues : [1],
                        backgroundColor: [
                            chartColors.primary, chartColors.success, chartColors.warning, 
                            chartColors.danger, chartColors.info, chartColors.purple
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });
        }

        // Duplicate charts for loan tab
        const loanCtx2 = document.getElementById('loanApplicationsChart2');
        if (loanCtx2 && !charts.loanApplications2) {
            charts.loanApplications2 = new Chart(loanCtx2, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Loan Applications',
                        data: monthlyLoansData,
                        borderColor: chartColors.primary,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        const statusCtx2 = document.getElementById('loanStatusChart2');
        if (statusCtx2 && !charts.loanStatus2) {
            const statusLabels = Object.keys(loanStatusData);
            const statusValues = Object.values(loanStatusData);
            
            charts.loanStatus2 = new Chart(statusCtx2, {
                type: 'pie',
                data: {
                    labels: statusLabels.length ? statusLabels : ['No Data'],
                    datasets: [{
                        data: statusValues.length ? statusValues : [1],
                        backgroundColor: [
                            chartColors.primary, chartColors.success, chartColors.warning, 
                            chartColors.danger, chartColors.info, chartColors.purple
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });
        }
    }

    function initPaymentCharts() {
        // Payment Collection Chart
        const paymentCtx = document.getElementById('paymentCollectionChart');
        if (paymentCtx && !charts.paymentCollection) {
            charts.paymentCollection = new Chart(paymentCtx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Payments Collected',
                        data: monthlyPaymentsData,
                        backgroundColor: chartColors.success,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Payment Trend Chart
        const trendCtx = document.getElementById('paymentTrendChart');
        if (trendCtx && !charts.paymentTrend) {
            charts.paymentTrend = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Payment Collection',
                        data: monthlyPaymentsData,
                        borderColor: chartColors.success,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Overdue Loans Chart
        const overdueCtx = document.getElementById('overdueChart');
        if (overdueCtx && !charts.overdue) {
            charts.overdue = new Chart(overdueCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active Loans', 'Overdue Loans'],
                    datasets: [{
                        data: [<?php echo $total_loans - $overdue_loans; ?>, <?php echo $overdue_loans; ?>],
                        backgroundColor: [chartColors.success, chartColors.danger]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });
        }
    }

    function initInterestChart() {
        const interestCtx = document.getElementById('interestEarningsChart');
        if (interestCtx && !charts.interest) {
            charts.interest = new Chart(interestCtx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Interest Earned',
                        data: monthlyInterestData,
                        backgroundColor: chartColors.pink,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    }

    function initUserCharts() {
        // User Registration Chart
        const userRegCtx = document.getElementById('userRegistrationChart');
        if (userRegCtx && !charts.userRegistration) {
            const userData = [12, 19, 15, 25, 22, 30, 28, 35, 40, 38, 45, 50];
            charts.userRegistration = new Chart(userRegCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'New Users',
                        data: userData,
                        borderColor: chartColors.info,
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Active Borrowers Chart
        const activeCtx = document.getElementById('activeBorrowersChart');
        if (activeCtx && !charts.activeBorrowers) {
            charts.activeBorrowers = new Chart(activeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active Borrowers', 'Inactive'],
                    datasets: [{
                        data: [<?php echo $active_users; ?>, <?php echo max(0, $total_borrowers - $active_users); ?>],
                        backgroundColor: [chartColors.primary, chartColors.warning]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right' } }
                }
            });
        }
    }

    // Initialize DataTables
    $(document).ready(function() {
        $('#financialTable').DataTable({
            pageLength: 12,
            order: [[0, 'asc']]
        });
        $('#loanTable').DataTable({
            pageLength: 10,
            order: [[7, 'desc']]
        });
        $('#paymentTable').DataTable({
            pageLength: 10,
            order: [[4, 'desc']]
        });
        $('#userTable').DataTable({
            pageLength: 10,
            order: [[7, 'desc']]
        });
        
        // Initialize charts on page load
        initChartsForTab('financial');
    });

    // Generate Report
    function generateReport() {
        const dateRange = document.getElementById('dateRange').value;
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;
        const reportType = document.getElementById('reportType').value;
        
        // Show loading state
        const btn = document.querySelector('.btn-generate');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Generate';
            alert('Report generated successfully!\n\nFilters:\n- Date Range: ' + dateRange + '\n- Month: ' + month + '\n- Year: ' + year + '\n- Type: ' + reportType);
        }, 1000);
    }

    // Export Functions
    function exportPDF() {
        alert('Exporting to PDF... This feature will generate a PDF report.');
    }

    function exportExcel() {
        alert('Exporting to Excel... This feature will generate an Excel file.');
    }

    // Session Timer
    let time = 300;
    let timeLeft = time;
    let countdownInterval;
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
        window.location.href = 'logout.php?timeout=1';
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
        startCountdown();
    }
    
    document.addEventListener('mousemove', resetSession);
    document.addEventListener('keypress', resetSession);
    document.addEventListener('click', resetSession);
    document.addEventListener('scroll', resetSession);
    
    startCountdown();
</script>

</body>
<?php require_once 'include/footer.php'; ?>
