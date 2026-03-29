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

require_once 'app/auto_notifications.php';

$today = date('Y-m-d');
$current_year = date('Y');

$total_loans_released = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(loan_amount), 0) as total FROM accounts WHERE account_status NOT IN (0, 4)"
));
$total_loan_released = $total_loans_released['total'] ?? 0;

$total_payments_collected = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments"
));
$payments_collected = $total_payments_collected['total'] ?? 0;

$total_interest_earned = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(interest), 0) as total FROM accounts"
));
$interest_earned = $total_interest_earned['total'] ?? 0;

$outstanding_balance = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(loan_balance), 0) as total FROM accounts WHERE account_status NOT IN (0, 3, 4)"
));
$outstanding = $outstanding_balance['total'] ?? 0;

$monthly_income = [];
$monthly_labels = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M', strtotime("-$i months"));
    $monthly_labels[] = $month_name;
    
    $income = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month'"
    ));
    $monthly_income[] = floatval($income['total'] ?? 0);
}

$loan_type_stats = mysqli_query($conn, "
    SELECT 
        at.account_type_name,
        COUNT(a.account_number) as count,
        COALESCE(SUM(a.loan_amount), 0) as total_amount
    FROM accounts a
    LEFT JOIN account_type at ON a.account_type = at.account_type_number
    WHERE a.account_status NOT IN (0, 4)
    GROUP BY at.account_type_name
    ORDER BY total_amount DESC
    LIMIT 5
");

$status_stats = mysqli_query($conn, "
    SELECT 
        acs.account_status_name,
        COUNT(a.account_number) as count
    FROM accounts a
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    GROUP BY acs.account_status_name
");

$statuses_list = [];
$status_counts = [];
while($s = mysqli_fetch_assoc($status_stats)) {
    $statuses_list[] = $s['account_status_name'];
    $status_counts[] = $s['count'];
}

$monthly_loans = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $loan = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(loan_amount), 0) as total FROM accounts WHERE DATE_FORMAT(open_date, '%Y-%m') = '$month'"
    ));
    $monthly_loans[] = floatval($loan['total'] ?? 0);
}

$loan_types_list = [];
$loan_type_amounts = [];
$loan_type_colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
while($lt = mysqli_fetch_assoc($loan_type_stats)) {
    $loan_types_list[] = $lt['account_type_name'] ?? 'Unknown';
    $loan_type_amounts[] = floatval($lt['total_amount'] ?? 0);
}

$recent_payments = mysqli_query($conn, "
    SELECT p.*, c.first_name, c.surname, a.account_number
    FROM payments p
    LEFT JOIN accounts a ON p.account_number = a.account_number
    LEFT JOIN customers c ON a.customer = c.customer_number
    ORDER BY p.payment_date DESC
    LIMIT 5
");

$recent_loans = mysqli_query($conn, "
    SELECT a.*, c.first_name, c.surname, act.account_type_name, acs.account_status_name
    FROM accounts a
    LEFT JOIN customers c ON a.customer = c.customer_number
    LEFT JOIN account_type act ON a.account_type = act.account_type_number
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    ORDER BY a.open_date DESC
    LIMIT 5
");

$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count FROM accounts 
    WHERE due_date < '$today' AND loan_balance > 0 AND account_status NOT IN (3, 4)
"));
$overdue_loans = $overdue_count['count'] ?? 0;
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
body.dark-mode .session-timer {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
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

  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="padding-bottom: 10px;">
        <div class="page-title-section mt-3">
          <div class="header-banner p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h1 class="mb-2" style="font-size: 2rem; font-weight: 700;"><i class="fas fa-university me-2"></i>FundHarmony Admin</h1>
            <h5 class="mb-2" style="font-weight: 500;"><i class="fas fa-handshake me-2"></i>Welcome to FundHarmony!</h5>
            <p class="mb-0" style="font-size: 0.95rem; opacity: 0.95;">This platform allows clients to apply for loans, track repayments, and manage their financial activities online.</p>
          </div>
        </div>
  
        <!-- Stats Cards -->
        <div class="row">
          <?php
          $sql = "SELECT count(*) AS total FROM customers";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary">
              <div class="inner">
                <h3><?php echo $data['total']; ?></h3>
                <p>Total Clients</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
              <a href="managecustomer.php" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          
          <?php
          $sql = "SELECT count(*) AS total_account FROM accounts a
              LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
              WHERE acs.account_status_name IN ('Active', 'Approved', 'Partial', 'Up to Date', 'Due Date')";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          ?>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success">
              <div class="inner">
                <h3><?php echo $data['total_account'];?></h3>
                <p>Active Loans</p>
              </div>
              <div class="icon">
                <i class="fas fa-money-check"></i>
              </div>
              <a href="manageaccount.php" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          
          <?php
          $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
          $payments_count = 0;
          if(mysqli_num_rows($check_table) > 0) {
              $sql = "SELECT count(*) AS total_payments FROM payments";
              $result = mysqli_query($conn, $sql);
              $data = mysqli_fetch_assoc($result);
              $payments_count = isset($data['total_payments']) ? $data['total_payments'] : 0;
          }
          ?>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning">
              <div class="inner">
                <h3><?php echo $payments_count;?></h3>
                <p>Total Payments</p>
              </div>
              <div class="icon">
                <i class="fas fa-hand-holding-usd"></i>
              </div>
              <a href="managepayment.php" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          
          <?php
          $sql = "SELECT SUM(payment_amount) AS total_collected FROM payments";
          $result = mysqli_query($conn, $sql);
          $data = mysqli_fetch_assoc($result);
          $total_collected = $data['total_collected'] ?? 0;
          ?>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info">
              <div class="inner">
                <h3><?php echo number_format($total_collected, 0); ?></h3>
                <p>Total Collected</p>
              </div>
              <div class="icon">
                <i class="fas fa-chart-line"></i>
              </div>
              <a href="pdfpayment.php" class="small-box-footer" target="_blank">
                View Report <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-3">
          <div class="col-md-12">
            <div class="card border-0 shadow-sm">
              <div class="card-header py-3 header-purple">
                <h5 class="mb-0 fw-bold text-white"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-3">
                    <a href="addcustomer.php" class="btn btn-primary w-100 py-3">
                      <i class="fas fa-user-plus fa-lg me-2"></i> Add Client
                    </a>
                  </div>
                  <div class="col-md-3">
                    <a href="openaccount.php" class="btn btn-success w-100 py-3">
                      <i class="fas fa-file-invoice-dollar fa-lg me-2"></i> New Loan
                    </a>
                  </div>
                  <div class="col-md-3">
                    <a href="addpayment.php" class="btn btn-warning w-100 py-3 text-white">
                      <i class="fas fa-money-bill fa-lg me-2"></i> Payment
                    </a>
                  </div>
                  <div class="col-md-3">
                    <a href="managecustomer.php" class="btn btn-info w-100 py-3 text-white">
                      <i class="fas fa-users-cog fa-lg me-2"></i> Manage Clients
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Financial Summary -->
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-transparent border-0 py-3">
                <h5 class="mb-0 fw-bold" style="color: #4f46e5;"><i class="fas fa-chart-pie me-2"></i>Financial Overview</h5>
              </div>
              <div class="card-body">
                <div class="row g-3 mb-4">
                  <div class="col-md-2">
                    <div class="card bg-primary text-white">
                      <div class="card-body text-center">
                        <h6 class="mb-1" style="opacity: 0.8;">Loans Released</h6>
                        <h5 class="mb-0">₱<?php echo number_format($total_loan_released, 0); ?></h5>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="card bg-success text-white">
                      <div class="card-body text-center">
                        <h6 class="mb-1" style="opacity: 0.8;">Collected</h6>
                        <h5 class="mb-0">₱<?php echo number_format($payments_collected, 0); ?></h5>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="card bg-warning text-white">
                      <div class="card-body text-center">
                        <h6 class="mb-1" style="opacity: 0.8;">Interest</h6>
                        <h5 class="mb-0">₱<?php echo number_format($interest_earned, 0); ?></h5>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="card bg-info text-white">
                      <div class="card-body text-center">
                        <h6 class="mb-1" style="opacity: 0.8;">Outstanding</h6>
                        <h5 class="mb-0">₱<?php echo number_format($outstanding, 0); ?></h5>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="card bg-danger text-white">
                      <div class="card-body text-center">
                        <h6 class="mb-1" style="opacity: 0.8;">Overdue</h6>
                        <h5 class="mb-0"><?php echo $overdue_loans; ?></h5>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                      <div class="card-body text-center">
                        <h6 class="mb-1" style="opacity: 0.8;">Net Income</h6>
                        <h5 class="mb-0">₱<?php echo number_format($payments_collected - $total_loan_released, 0); ?></h5>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row g-3">
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-header py-2 header-purple">
                        <h6 class="mb-0 fw-bold text-white"><i class="fas fa-chart-bar me-2"></i>Monthly Collections</h6>
                      </div>
                      <div class="card-body">
                        <canvas id="incomeChart" height="150"></canvas>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-header py-2 header-green">
                        <h6 class="mb-0 fw-bold text-white"><i class="fas fa-chart-line me-2"></i>Loans Released</h6>
                      </div>
                      <div class="card-body">
                        <canvas id="loanChart" height="150"></canvas>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-header py-2 header-orange">
                        <h6 class="mb-0 fw-bold text-white"><i class="fas fa-chart-pie me-2"></i>Loans by Status</h6>
                      </div>
                      <div class="card-body">
                        <canvas id="statusChart" height="150"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Loan Types & Recent Activity -->
                <div class="row g-3 mt-2">
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-header py-2 header-pink">
                        <h6 class="mb-0 fw-bold text-white"><i class="fas fa-layer-group me-2"></i>Loans by Type</h6>
                      </div>
                      <div class="card-body">
                        <canvas id="typeChart" height="150"></canvas>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-header py-2 header-cyan">
                        <h6 class="mb-0 fw-bold text-white"><i class="fas fa-money-bill-wave me-2"></i>Recent Payments</h6>
                      </div>
                      <div class="card-body pt-0">
                        <div class="recent-list">
                          <?php 
                          mysqli_data_seek($recent_payments, 0);
                          while($rp = mysqli_fetch_assoc($recent_payments)): 
                          ?>
                          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                              <div class="fw-bold" style="font-size:13px;"><?php echo $rp['first_name'].' '.$rp['surname']; ?></div>
                              <small class="text-muted"><?php echo date('M d, Y', strtotime($rp['payment_date'])); ?></small>
                            </div>
                            <div class="text-success fw-bold">₱<?php echo number_format($rp['payment_amount'],0); ?></div>
                          </div>
                          <?php endwhile; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card border-0 bg-light">
                      <div class="card-header py-2 header-lightpurple">
                        <h6 class="mb-0 fw-bold text-white"><i class="fas fa-file-invoice-dollar me-2"></i>Recent Loans</h6>
                      </div>
                      <div class="card-body pt-0">
                        <div class="recent-list">
                          <?php 
                          mysqli_data_seek($recent_loans, 0);
                          while($rl = mysqli_fetch_assoc($recent_loans)): 
                          ?>
                          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                              <div class="fw-bold" style="font-size:13px;"><?php echo $rl['first_name'].' '.$rl['surname']; ?></div>
                              <small class="text-muted"><?php echo $rl['account_type_name']; ?></small>
                            </div>
                            <div class="text-primary fw-bold">₱<?php echo number_format($rl['loan_amount'],0); ?></div>
                          </div>
                          <?php endwhile; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
          var incomeChart, loanChart, statusChart, typeChart;
          
          var incomeCtx = document.getElementById('incomeChart').getContext('2d');
          incomeChart = new Chart(incomeCtx, {
            type: 'bar',
            data: {
              labels: <?php echo json_encode($monthly_labels); ?>,
              datasets: [{
                label: 'Collections',
                data: <?php echo json_encode($monthly_income); ?>,
                backgroundColor: '#4f46e5',
                borderRadius: 5,
                hoverBackgroundColor: '#6366f1'
              }]
            },
            options: { 
              responsive: true, 
              scales: { y: { beginAtZero: true } },
              animation: { duration: 1500, easing: 'easeOutQuart' },
              onClick: function(e, elements) { if(elements.length > 0) animateBarChart(incomeChart); },
              plugins: {
                tooltip: { 
                  backgroundColor: '#1a1a2e',
                  titleColor: '#fff',
                  bodyColor: '#fff',
                  padding: 12,
                  cornerRadius: 8,
                  displayColors: false
                }
              }
            }
          });
          
          var loanCtx = document.getElementById('loanChart').getContext('2d');
          loanChart = new Chart(loanCtx, {
            type: 'line',
            data: {
              labels: <?php echo json_encode($monthly_labels); ?>,
              datasets: [{
                label: 'Loans Released',
                data: <?php echo json_encode($monthly_loans); ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#10b981',
                pointRadius: 5,
                pointHoverRadius: 8
              }]
            },
            options: { 
              responsive: true, 
              scales: { y: { beginAtZero: true } },
              animation: { duration: 1500, easing: 'easeOutQuart' },
              onClick: function(e, elements) { if(elements.length > 0) animateLineChart(loanChart); },
              plugins: {
                tooltip: { 
                  backgroundColor: '#1a1a2e',
                  titleColor: '#fff',
                  bodyColor: '#fff',
                  padding: 12,
                  cornerRadius: 8
                }
              }
            }
          });
          
          var statusCtx = document.getElementById('statusChart').getContext('2d');
          var statusData = [<?php echo implode(', ', $status_counts); ?>];
          statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
              labels: <?php echo json_encode($statuses_list); ?>,
              datasets: [{ 
                data: statusData, 
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'],
                hoverOffset: 15
              }]
            },
            options: { 
              responsive: true,
              animation: { animateRotate: true, animateScale: true, duration: 1500, easing: 'easeOutQuart' },
              onClick: function(e, elements) { if(elements.length > 0) animateDoughnutChart(statusChart); },
              plugins: {
                legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } },
                tooltip: { 
                  backgroundColor: '#1a1a2e',
                  titleColor: '#fff',
                  bodyColor: '#fff',
                  padding: 12,
                  cornerRadius: 8
                }
              }
            }
          });
          
          var typeCtx = document.getElementById('typeChart').getContext('2d');
          typeChart = new Chart(typeCtx, {
            type: 'pie',
            data: {
              labels: <?php echo json_encode($loan_types_list); ?>,
              datasets: [{ 
                data: <?php echo json_encode($loan_type_amounts); ?>, 
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                hoverOffset: 15
              }]
            },
            options: { 
              responsive: true,
              animation: { animateRotate: true, animateScale: true, duration: 1500, easing: 'easeOutQuart' },
              onClick: function(e, elements) { if(elements.length > 0) animatePieChart(typeChart); },
              plugins: {
                legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } },
                tooltip: { 
                  backgroundColor: '#1a1a2e',
                  titleColor: '#fff',
                  bodyColor: '#fff',
                  padding: 12,
                  cornerRadius: 8
                }
              }
            }
          });
          
          function animateBarChart(chart) {
            var originalData = chart.data.datasets[0].data.slice();
            var randomData = originalData.map(function(val) { return val * (0.5 + Math.random() * 0.5); });
            chart.data.datasets[0].data = randomData;
            chart.update('none');
            
            setTimeout(function() {
              chart.data.datasets[0].data = originalData;
              chart.update({ duration: 800, easing: 'easeOutElastic' });
            }, 200);
          }
          
          function animateLineChart(chart) {
            var originalData = chart.data.datasets[0].data.slice();
            var randomData = originalData.map(function(val) { return val * (0.7 + Math.random() * 0.6); });
            chart.data.datasets[0].data = randomData;
            chart.update('none');
            
            setTimeout(function() {
              chart.data.datasets[0].data = originalData;
              chart.update({ duration: 1000, easing: 'easeOutElastic' });
            }, 200);
          }
          
          function animateDoughnutChart(chart) {
            chart.update({
              duration: 800,
              easing: 'easeOutBounce'
            });
          }
          
          function animatePieChart(chart) {
            chart.update({
              duration: 800,
              easing: 'easeOutBounce'
            });
          }
        });
        </script>
        
        <!-- Quick Access Reports -->
        <div class="row mt-4 mb-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 animate-card">
              <div class="card-header py-3 header-cyan">
                <h5 class="mb-0 fw-bold text-white"><i class="fas fa-history me-2"></i>Transactions</h5>
              </div>
              <div class="card-body text-center d-flex align-items-center justify-content-center">
                <a href="loan_transactions.php" class="btn btn-cyan w-100 py-3">
                  <i class="fas fa-arrow-right me-2"></i> View Transactions
                </a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 animate-card">
              <div class="card-header py-3 header-orange">
                <h5 class="mb-0 fw-bold text-white"><i class="fas fa-calendar me-2"></i>Custom Report</h5>
              </div>
              <div class="card-body text-center d-flex align-items-center justify-content-center">
                <a href="custom_reports.php" class="btn btn-orange w-100 py-3">
                  <i class="fas fa-arrow-right me-2"></i> Create Report
                </a>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 animate-card">
              <div class="card-header py-3 header-purple">
                <h5 class="mb-0 fw-bold text-white"><i class="fas fa-chart-pie me-2"></i>Financial Summary</h5>
              </div>
              <div class="card-body text-center d-flex align-items-center justify-content-center">
                <a href="financial_summary.php" class="btn btn-purple w-100 py-3">
                  <i class="fas fa-arrow-right me-2"></i> View Summary
                </a>
              </div>
            </div>
          </div>
        </div>
        
      </main>
    </div>
  </div>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes fadeInRight {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
.animate-card {
    animation: fadeInUp 0.5s ease-out forwards;
    opacity: 0;
}
.animate-card:nth-child(1) { animation-delay: 0.1s; }
.animate-card:nth-child(2) { animation-delay: 0.2s; }
.animate-card:nth-child(3) { animation-delay: 0.3s; }
.animate-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
}
.small-box {
    transition: all 0.3s ease;
}
.small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.btn {
    transition: all 0.3s ease;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.card {
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-3px);
}
.header-purple { background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); }
.header-green { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
.header-orange { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
.header-pink { background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%); }
.header-cyan { background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%); }
.header-lightpurple { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); }
.btn-cyan { background: #06b6d4; color: white; border: none; }
.btn-cyan:hover { background: #0891b2; color: white; }
.btn-orange { background: #f59e0b; color: white; border: none; }
.btn-orange:hover { background: #d97706; color: white; }
.btn-purple { background: #4f46e5; color: white; border: none; }
.btn-purple:hover { background: #4338ca; color: white; }

html.dark-mode-bg {
    background: #0f172a !important;
}
body.dark-mode {
    background: #0f172a !important;
    color: #e2e8f0 !important;
    min-height: 100vh;
}
body.dark-mode .container-fluid {
    background: #0f172a !important;
    min-height: 100vh;
}
body.dark-mode .row {
    background: transparent;
}
body.dark-mode .small-box {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-color: #334155;
    min-height: 120px;
}
body.dark-mode .page-title-section h1,
body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
    color: #f1f5f9 !important;
}
body.dark-mode p, body.dark-mode span, body.dark-mode div, body.dark-mode strong {
    color: #e2e8f0 !important;
}
body.dark-mode .text-muted {
    color: #94a3b8 !important;
}
body.dark-mode .small-box .inner h3 {
    color: #f1f5f9 !important;
}
body.dark-mode .small-box .inner p {
    color: #94a3b8 !important;
}
body.dark-mode .small-box-footer {
    color: #94a3b8 !important;
}
body.dark-mode .btn-outline-secondary {
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .btn-outline-secondary:hover {
    background: #334155 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .bg-gradient-primary {
    background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%) !important;
}
body.dark-mode .bg-gradient-success {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
}
body.dark-mode .bg-gradient-info {
    background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%) !important;
}
body.dark-mode .bg-gradient-warning {
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
}
body.dark-mode .card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .card-header {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .card.bg-light {
    background: #1e293b !important;
}
body.dark-mode .border-bottom {
    border-color: #334155 !important;
}
body.dark-mode .recent-list .fw-bold {
    color: #f1f5f9 !important;
}
body.dark-mode .card-header h6 {
    color: #f1f5f9 !important;
}
body.dark-mode .card-header h6 i {
    color: #f1f5f9 !important;
}
body.dark-mode .bg-light {
    background: #1e293b !important;
}
body.dark-mode .btn-outline-primary {
    border-color: #4f46e5 !important;
    color: #4f46e5 !important;
}
body.dark-mode .btn-outline-primary:hover {
    background: #4f46e5 !important;
    color: #fff !important;
}
body.dark-mode .btn-outline-success {
    border-color: #10b981 !important;
    color: #10b981 !important;
}
body.dark-mode .btn-outline-success:hover {
    background: #10b981 !important;
    color: #fff !important;
}
body.dark-mode .btn-outline-warning {
    border-color: #f59e0b !important;
    color: #f59e0b !important;
}
body.dark-mode .btn-outline-warning:hover {
    background: #f59e0b !important;
    color: #fff !important;
}
body.dark-mode .btn-outline-info {
    border-color: #06b6d4 !important;
    color: #06b6d4 !important;
}
body.dark-mode .btn-outline-info:hover {
    background: #06b6d4 !important;
    color: #fff !important;
}
body.dark-mode .btn-outline-danger {
    border-color: #ef4444 !important;
    color: #ef4444 !important;
}
body.dark-mode .btn-outline-danger:hover {
    background: #ef4444 !important;
    color: #fff !important;
}
body.dark-mode .btn-outline-secondary {
    border-color: #64748b !important;
    color: #94a3b8 !important;
}
body.dark-mode .btn-outline-secondary:hover {
    background: #475569 !important;
    color: #fff !important;
}
body.dark-mode .card-header h5 {
    color: #f1f5f9 !important;
}
body.dark-mode .card-header h5 i {
    color: #f1f5f9 !important;
}
body.dark-mode .bg-primary {
    background: #4338ca !important;
    background-color: #4338ca !important;
}
body.dark-mode .bg-success {
    background: #059669 !important;
    background-color: #059669 !important;
}
body.dark-mode .bg-warning {
    background: #d97706 !important;
    background-color: #d97706 !important;
}
body.dark-mode .bg-info {
    background: #0891b2 !important;
    background-color: #0891b2 !important;
}
body.dark-mode .bg-danger {
    background: #dc2626 !important;
    background-color: #dc2626 !important;
}
body.dark-mode .bg-secondary {
    background: #475569 !important;
    background-color: #475569 !important;
}
body.dark-mode .card-header h6,
body.dark-mode .card-header h6 i,
body.dark-mode .card-header .text-white {
    color: #fff !important;
}
body.dark-mode .header-purple {
    background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%) !important;
}
body.dark-mode .header-green {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%) !important;
}
body.dark-mode .header-orange {
    background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
}
body.dark-mode .header-pink {
    background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%) !important;
}
body.dark-mode .header-cyan {
    background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%) !important;
}
body.dark-mode .header-lightpurple {
    background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%) !important;
}
body.dark-mode .card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .card.bg-primary {
    background: linear-gradient(135deg, #4338ca, #6366f1) !important;
}
body.dark-mode .card.bg-success {
    background: linear-gradient(135deg, #059669, #10b981) !important;
}
body.dark-mode .card.bg-warning {
    background: linear-gradient(135deg, #d97706, #f59e0b) !important;
}
body.dark-mode .card.bg-info {
    background: linear-gradient(135deg, #0891b2, #06b6d4) !important;
}
body.dark-mode .card.bg-danger {
    background: linear-gradient(135deg, #dc2626, #ef4444) !important;
}
body.dark-mode .card.bg-secondary {
    background: linear-gradient(135deg, #475569, #64748b) !important;
}
body.dark-mode .btn-cyan,
body.dark-mode .btn-orange,
body.dark-mode .btn-purple {
    background: #334155 !important;
    color: #fff !important;
    border: 1px solid #475569 !important;
}
body.dark-mode .btn-cyan:hover,
body.dark-mode .btn-orange:hover,
body.dark-mode .btn-purple:hover {
    background: #475569 !important;
    color: #fff !important;
}
body.dark-mode .card-body p {
    color: #94a3b8 !important;
}
body.dark-mode .animate-card {
    animation: fadeInUp 0.5s ease-out forwards;
}
</style>

<script>
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
        clearTimeout(resetTimer);
        resetTimer = setTimeout(logout, time * 1000);
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
