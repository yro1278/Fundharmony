<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$today = date('Y-m-d');
$current_year = date('Y');
$current_month = date('m');

$total_loans_released = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(loan_amount), 0) as total FROM accounts WHERE account_status NOT IN (0, 4)"
));
$total_loan_released = $total_loans_released['total'] ?? 0;

$total_applications = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts"
));
$count_applications = $total_applications['count'] ?? 0;

$total_payments_collected = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments"
));
$payments_collected = $total_payments_collected['total'] ?? 0;

$total_interest_earned = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(interest), 0) as total FROM accounts"
));
$interest_earned = $total_interest_earned['total'] ?? 0;

$total_penalties = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(penalty), 0) as total FROM accounts"
));
$penalties_collected = $total_penalties['total'] ?? 0;

$total_overdue_interest = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(overdue_interest), 0) as total FROM accounts"
));
$overdue_interest = $total_overdue_interest['total'] ?? 0;

$outstanding_balance = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(loan_balance), 0) as total FROM accounts WHERE account_status NOT IN (0, 3, 4)"
));
$outstanding = $outstanding_balance['total'] ?? 0;

$net_income = ($payments_collected + $overdue_interest) - $total_loan_released;

$monthly_income = [];
$monthly_labels = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $monthly_labels[] = $month_name;
    
    $income = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month'"
    ));
    $monthly_income[] = floatval($income['total'] ?? 0);
}

$monthly_interest = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    
    $interest = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(interest), 0) as total FROM accounts WHERE DATE_FORMAT(open_date, '%Y-%m') = '$month'"
    ));
    $monthly_interest[] = floatval($interest['total'] ?? 0);
}

$loan_type_stats = mysqli_query($conn, "
    SELECT 
        at.account_type_name,
        COUNT(a.account_number) as count,
        COALESCE(SUM(a.loan_amount), 0) as total_amount,
        COALESCE(SUM(a.interest), 0) as total_interest
    FROM accounts a
    LEFT JOIN account_type at ON a.account_type = at.account_type_number
    WHERE a.account_status NOT IN (0, 4)
    GROUP BY at.account_type_name
    ORDER BY total_amount DESC
");

$status_stats = mysqli_query($conn, "
    SELECT 
        acs.account_status_name,
        COUNT(a.account_number) as count,
        COALESCE(SUM(a.loan_balance), 0) as total_balance
    FROM accounts a
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    GROUP BY acs.account_status_name
");
?>

<body>
  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid" style="overflow-x: hidden;">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="overflow-x: hidden;">
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chart-line text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-1">Financial Summary Report</h1>
              <p class="text-muted mb-0">Overall financial performance of the system</p>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #4f46e5;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Loans Released</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_loan_released, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #8B5CF6;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Loan Applications</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_applications; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #10b981;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Payments Collected</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($payments_collected, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Interest Earned</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($interest_earned, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #ef4444;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Penalties</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($penalties_collected + $overdue_interest, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #0ea5e9;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Outstanding Balance</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($outstanding, 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Financial Summary -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-1"><i class="fas fa-wallet me-2"></i>Net Financial Summary</h4>
                                <p class="mb-0 opacity-75">Total Payments + Overdue Interest - Total Loans Released</p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h2 class="mb-0">₱<?php echo number_format($net_income, 2); ?></h2>
                                <small class="opacity-75">Net Income</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Monthly Income (Payments Collected)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="incomeChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Interest Earnings by Month</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="interestChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Stats -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Loans by Type</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 py-2">Loan Type</th>
                                        <th class="px-3 py-2 text-center">Count</th>
                                        <th class="px-3 py-2 text-end">Total Amount</th>
                                        <th class="px-3 py-2 text-end">Total Interest</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($loan_type_stats) > 0): ?>
                                        <?php while($type = mysqli_fetch_assoc($loan_type_stats)): ?>
                                        <tr>
                                            <td class="px-3 py-2"><?php echo htmlspecialchars($type['account_type_name'] ?? 'N/A'); ?></td>
                                            <td class="px-3 py-2 text-center"><?php echo $type['count']; ?></td>
                                            <td class="px-3 py-2 text-end">₱<?php echo number_format($type['total_amount'], 2); ?></td>
                                            <td class="px-3 py-2 text-end">₱<?php echo number_format($type['total_interest'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No loan data available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Loans by Status</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2 text-center">Count</th>
                                        <th class="px-3 py-2 text-end">Total Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($status_stats) > 0): ?>
                                        <?php while($status = mysqli_fetch_assoc($status_stats)): ?>
                                        <tr>
                                            <td class="px-3 py-2">
                                                <?php 
                                                $status_name = $status['account_status_name'] ?? 'Unknown';
                                                $badge_class = '';
                                                if($status_name == 'Active') $badge_class = 'bg-success';
                                                elseif($status_name == 'Closed' || $status_name == 'Fully Paid') $badge_class = 'bg-primary';
                                                elseif($status_name == 'Pending') $badge_class = 'bg-warning text-dark';
                                                elseif($status_name == 'Rejected' || $status_name == 'Declined') $badge_class = 'bg-danger';
                                                elseif($status_name == 'Partial') $badge_class = 'bg-info';
                                                elseif($status_name == 'Approved') $badge_class = 'bg-secondary';
                                                else $badge_class = 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $status_name; ?></span>
                                            </td>
                                            <td class="px-3 py-2 text-center"><?php echo $status['count']; ?></td>
                                            <td class="px-3 py-2 text-end">₱<?php echo number_format($status['total_balance'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No status data available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      </main>
    </div>
  </div>

  <?php require_once 'include/footer.php'; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    let incomeChart, interestChart;
    
    const incomeLabels = <?php echo json_encode($monthly_labels); ?>;
    const incomeData = <?php echo json_encode($monthly_income); ?>;
    const interestData = <?php echo json_encode($monthly_interest); ?>;
    
    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    incomeChart = new Chart(incomeCtx, {
        type: 'bar',
        data: {
            labels: incomeLabels,
            datasets: [{
                label: 'Payments Collected',
                data: incomeData,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: '#34d399'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) animateBar(incomeChart); },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(value) { return '₱' + value.toLocaleString(); } } }
            }
        }
    });
    
    const interestCtx = document.getElementById('interestChart').getContext('2d');
    interestChart = new Chart(interestCtx, {
        type: 'line',
        data: {
            labels: incomeLabels,
            datasets: [{
                label: 'Interest Earned',
                data: interestData,
                backgroundColor: 'rgba(245, 158, 11, 0.2)',
                borderColor: 'rgba(245, 158, 11, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(245, 158, 11, 1)',
                pointRadius: 4,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) animateLine(interestChart); },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(value) { return '₱' + value.toLocaleString(); } } }
            }
        }
    });
    
    function animateBar(chart) {
        var orig = chart.data.datasets[0].data.slice();
        var rand = orig.map(function(v) { return v * (0.5 + Math.random() * 0.5); });
        chart.data.datasets[0].data = rand;
        chart.update('none');
        setTimeout(function() {
            chart.data.datasets[0].data = orig;
            chart.update({ duration: 800, easing: 'easeOutElastic' });
        }, 200);
    }
    
    function animateLine(chart) {
        var orig = chart.data.datasets[0].data.slice();
        var rand = orig.map(function(v) { return v * (0.7 + Math.random() * 0.6); });
        chart.data.datasets[0].data = rand;
        chart.update('none');
        setTimeout(function() {
            chart.data.datasets[0].data = orig;
            chart.update({ duration: 1000, easing: 'easeOutElastic' });
        }, 200);
    }
  </script>
  
  <style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes scaleIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    
    .summary-card, .card { opacity: 0; animation: fadeInUp 0.5s ease-out forwards; transition: box-shadow 0.3s ease; }
    .summary-card:hover, .card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .table tbody tr { transition: all 0.2s ease; opacity: 0; animation: fadeIn 0.3s ease-out forwards; }
    .table tbody tr:hover { background-color: rgba(79, 70, 229, 0.05); transform: scale(1.01); }
    canvas { opacity: 0; animation: scaleIn 0.6s ease-out forwards; }
    .btn, .badge { transition: all 0.2s ease; }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .badge:hover { transform: scale(1.1); }
    
    .report-icon {
      flex-shrink: 0;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
      animation: slideInLeft 0.5s ease-out forwards;
    }
    .report-icon i {
      width: auto;
      height: auto;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.6s ease-out 0.2s forwards;
      opacity: 0;
    }
    .page-title-section h1 {
      animation: slideInUp 0.5s ease-out 0.1s forwards;
      opacity: 0;
    }
    @keyframes slideInLeft {
      from { opacity: 0; transform: translateX(-30px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  </style>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.summary-card, .card').forEach((el, i) => el.style.animationDelay = (i * 0.1) + 's');
      document.querySelectorAll('.table tbody tr').forEach((el, i) => el.style.animationDelay = (i * 0.03) + 's');
      document.querySelectorAll('canvas').forEach((el, i) => el.style.animationDelay = (0.3 + i * 0.15) + 's');
    });
  </script>
</body>
</html>
