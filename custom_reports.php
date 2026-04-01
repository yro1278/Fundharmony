<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$today = date('Y-m-d');

$range_type = isset($_GET['range_type']) ? $_GET['range_type'] : 'monthly';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : $today;

if ($range_type == 'daily') {
    $date_from = $today;
    $date_to = $today;
} elseif ($range_type == 'weekly') {
    $date_from = date('Y-m-d', strtotime('-7 days'));
    $date_to = $today;
} elseif ($range_type == 'monthly') {
    $date_from = date('Y-m-01');
    $date_to = $today;
} elseif ($range_type == 'yearly') {
    $date_from = date('Y-01-01');
    $date_to = $today;
}

$loan_stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_loans,
        COALESCE(SUM(loan_amount), 0) as total_amount,
        COALESCE(SUM(interest), 0) as total_interest
    FROM accounts 
    WHERE account_status NOT IN (0, 4)
    AND open_date >= '$date_from' 
    AND open_date <= '$date_to'
"));
$count_loans = $loan_stats['total_loans'] ?? 0;
$total_loan_amount = $loan_stats['total_amount'] ?? 0;
$total_interest = $loan_stats['total_interest'] ?? 0;

$payment_stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_payments,
        COALESCE(SUM(payment_amount), 0) as total_collected
    FROM payments 
    WHERE payment_date >= '$date_from' 
    AND payment_date <= '$date_to'
"));
$count_payments = $payment_stats['total_payments'] ?? 0;
$total_collected = $payment_stats['total_collected'] ?? 0;

$customer_stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total_customers
    FROM customers 
    WHERE registration_date >= '$date_from' 
    AND registration_date <= '$date_to'
"));
$count_customers = $customer_stats['total_customers'] ?? 0;

$overdue_stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as count,
        COALESCE(SUM(loan_balance), 0) as balance
    FROM accounts 
    WHERE due_date < '$today' 
    AND loan_balance > 0 
    AND account_status NOT IN ('-3', '3', '4', '0')
    AND due_date >= '$date_from' 
    AND due_date <= '$date_to'
"));
$count_overdue = $overdue_stats['count'] ?? 0;
$overdue_balance = $overdue_stats['balance'] ?? 0;

$loans = mysqli_query($conn, "
    SELECT 
        a.*,
        c.first_name,
        c.middle_name,
        c.surname,
        act.account_type_name,
        acs.account_status_name
    FROM accounts a
    INNER JOIN customers c ON a.customer = c.customer_number
    LEFT JOIN account_type act ON a.account_type = act.account_type_number
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE a.open_date >= '$date_from' AND a.open_date <= '$date_to'
    ORDER BY a.open_date DESC
");

$payments = mysqli_query($conn, "
    SELECT 
        p.*,
        c.first_name,
        c.middle_name,
        c.surname,
        a.loan_amount
    FROM payments p
    INNER JOIN accounts a ON p.account_number = a.account_number
    INNER JOIN customers c ON a.customer = c.customer_number
    WHERE p.payment_date >= '$date_from' AND p.payment_date <= '$date_to'
    ORDER BY p.payment_date DESC
");

$daily_breakdown = [];
$current = strtotime($date_from);
$end = strtotime($date_to);
while ($current <= $end) {
    $day = date('Y-m-d', $current);
    $day_name = date('M d', $current);
    
    $loan_count = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count, COALESCE(SUM(loan_amount), 0) as amount FROM accounts WHERE open_date = '$day' AND account_status NOT IN (0, 4)"
    ));
    
    $payment_amount = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(payment_amount), 0) as amount FROM payments WHERE payment_date = '$day'"
    ));
    
    $daily_breakdown[] = [
        'date' => $day_name,
        'loans' => intval($loan_count['count'] ?? 0),
        'loan_amount' => floatval($loan_count['amount'] ?? 0),
        'payments' => floatval($payment_amount['amount'] ?? 0)
    ];
    
    $current = strtotime('+1 day', $current);
}
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
              <i class="fas fa-calendar-check text-white" style="font-size: 24px;"></i>
            </div>
            <div>
              <h1 class="mb-1">Custom Date Range Reports</h1>
              <p class="text-muted mb-0">Generate reports based on selected date range</p>
            </div>
          </div>
        </div>

        <!-- Date Range Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Quick Select</label>
                        <select name="range_type" class="form-select" id="rangeType" onchange="toggleCustomDates()">
                            <option value="daily" <?php echo $range_type == 'daily' ? 'selected' : ''; ?>>Today</option>
                            <option value="weekly" <?php echo $range_type == 'weekly' ? 'selected' : ''; ?>>This Week</option>
                            <option value="monthly" <?php echo $range_type == 'monthly' ? 'selected' : ''; ?>>This Month</option>
                            <option value="yearly" <?php echo $range_type == 'yearly' ? 'selected' : ''; ?>>This Year</option>
                            <option value="custom" <?php echo $range_type == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-3 custom-dates" style="<?php echo $range_type == 'custom' ? '' : 'display:none;'; ?>">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-3 custom-dates" style="<?php echo $range_type == 'custom' ? '' : 'display:none;'; ?>">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-2" style="<?php echo $range_type == 'custom' ? 'display:none;' : ''; ?>">
                        <label class="form-label">&nbsp;</label>
                        <input type="hidden" name="date_from" value="<?php echo $date_from; ?>">
                        <input type="hidden" name="date_to" value="<?php echo $date_to; ?>">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Generate</button>
                    </div>
                    <div class="col-md-2 custom-dates" style="<?php echo $range_type == 'custom' ? '' : 'display:none;'; ?>">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Generate</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #4f46e5;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Loans Released</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_loans; ?></h4>
                        <small class="text-muted">₱<?php echo number_format($total_loan_amount, 2); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #10b981;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Payments Collected</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_payments; ?></h4>
                        <small class="text-muted">₱<?php echo number_format($total_collected, 2); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Interest Earned</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_interest, 2); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #0ea5e9;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">New Borrowers</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_customers; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Daily Loans & Payments</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Daily Amounts</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="amountChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loans Table -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Loans (<?php echo $count_loans; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-2 py-2">Loan ID</th>
                                <th class="px-2 py-2">Borrower</th>
                                <th class="px-2 py-2">Type</th>
                                <th class="px-2 py-2 text-end">Amount</th>
                                <th class="px-2 py-2">Date</th>
                                <th class="px-2 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($loans) > 0): ?>
                                <?php while($loan = mysqli_fetch_assoc($loans)): ?>
                                <tr>
                                    <td class="px-2 py-2">#<?php echo $loan['account_number']; ?></td>
                                    <td class="px-2 py-2"><?php echo htmlspecialchars(trim(($loan['first_name'] ?? '') . ' ' . ($loan['surname'] ?? ''))); ?></td>
                                    <td class="px-2 py-2"><?php echo htmlspecialchars($loan['account_type_name'] ?? 'N/A'); ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($loan['loan_amount'], 2); ?></td>
                                    <td class="px-2 py-2"><?php echo $loan['open_date'] ? date('M d, Y', strtotime($loan['open_date'])) : '-'; ?></td>
                                    <td class="px-2 py-2">
                                        <?php 
                                        $status = $loan['account_status_name'];
                                        $badge = $status == 'Active' ? 'bg-success' : ($status == 'Closed' ? 'bg-primary' : ($status == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary'));
                                        ?>
                                        <span class="badge <?php echo $badge; ?>"><?php echo $status ?? 'N/A'; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-3 text-muted">No loans in this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payments (<?php echo $count_payments; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-2 py-2">Payment ID</th>
                                <th class="px-2 py-2">Borrower</th>
                                <th class="px-2 py-2">Loan ID</th>
                                <th class="px-2 py-2 text-end">Amount</th>
                                <th class="px-2 py-2">Date</th>
                                <th class="px-2 py-2">Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($payments) > 0): ?>
                                <?php while($payment = mysqli_fetch_assoc($payments)): ?>
                                <tr>
                                    <td class="px-2 py-2">#<?php echo $payment['payment_number']; ?></td>
                                    <td class="px-2 py-2"><?php echo htmlspecialchars(trim(($payment['first_name'] ?? '') . ' ' . ($payment['surname'] ?? ''))); ?></td>
                                    <td class="px-2 py-2">#<?php echo $payment['account_number']; ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($payment['payment_amount'], 2); ?></td>
                                    <td class="px-2 py-2"><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                    <td class="px-2 py-2"><?php echo htmlspecialchars($payment['payment_method'] ?? '-'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-3 text-muted">No payments in this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

      </main>
    </div>
  </div>

  <?php require_once 'include/footer.php'; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    function toggleCustomDates() {
        const rangeType = document.getElementById('rangeType').value;
        const customFields = document.querySelectorAll('.custom-dates');
        const hiddenFields = document.querySelectorAll('input[type="hidden"][name="date_from"], input[type="hidden"][name="date_to"]');
        
        if (rangeType === 'custom') {
            customFields.forEach(f => f.style.display = '');
            hiddenFields.forEach(f => f.parentElement.style.display = 'none');
        } else {
            customFields.forEach(f => f.style.display = 'none');
            hiddenFields.forEach(f => f.parentElement.style.display = '');
        }
    }
    
    let dailyLineChart, amountBarChart;
    
    const dailyData = <?php echo json_encode(array_slice($daily_breakdown, 0, 30)); ?>;
    const dailyLabels = dailyData.map(d => d.date);
    const loanCounts = dailyData.map(d => d.loans);
    const paymentAmounts = dailyData.map(d => d.payments);
    const loanAmounts = dailyData.map(d => d.loan_amount);
    
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    dailyLineChart = new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: 'Loans',
                    data: loanCounts,
                    borderColor: 'rgba(79, 70, 229, 1)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                    pointRadius: 4,
                    pointHoverRadius: 8
                },
                {
                    label: 'Payments',
                    data: paymentAmounts,
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1',
                    pointRadius: 4,
                    pointHoverRadius: 8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) animateLineMulti(dailyLineChart); },
            interaction: { mode: 'index', intersect: false },
            plugins: { 
                legend: { position: 'bottom' },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'Loans' } },
                y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Payments' } }
            }
        }
    });
    
    const amountCtx = document.getElementById('amountChart').getContext('2d');
    amountBarChart = new Chart(amountCtx, {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: 'Loan Amount',
                    data: loanAmounts,
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderRadius: 4,
                    hoverBackgroundColor: '#818cf8'
                },
                {
                    label: 'Payment Amount',
                    data: paymentAmounts,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderRadius: 4,
                    hoverBackgroundColor: '#34d399'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) animateBarMulti(amountBarChart); },
            plugins: { 
                legend: { position: 'bottom' },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            }
        }
    });
    
    function animateLineMulti(chart) {
        var orig0 = chart.data.datasets[0].data.slice();
        var orig1 = chart.data.datasets[1].data.slice();
        var rand0 = orig0.map(function(v) { return v * (0.6 + Math.random() * 0.8); });
        var rand1 = orig1.map(function(v) { return v * (0.6 + Math.random() * 0.8); });
        chart.data.datasets[0].data = rand0;
        chart.data.datasets[1].data = rand1;
        chart.update('none');
        setTimeout(function() {
            chart.data.datasets[0].data = orig0;
            chart.data.datasets[1].data = orig1;
            chart.update({ duration: 1000, easing: 'easeOutElastic' });
        }, 200);
    }
    
    function animateBarMulti(chart) {
        var orig0 = chart.data.datasets[0].data.slice();
        var orig1 = chart.data.datasets[1].data.slice();
        var rand0 = orig0.map(function(v) { return v * (0.5 + Math.random() * 0.5); });
        var rand1 = orig1.map(function(v) { return v * (0.5 + Math.random() * 0.5); });
        chart.data.datasets[0].data = rand0;
        chart.data.datasets[1].data = rand1;
        chart.update('none');
        setTimeout(function() {
            chart.data.datasets[0].data = orig0;
            chart.data.datasets[1].data = orig1;
            chart.update({ duration: 800, easing: 'easeOutElastic' });
        }, 200);
    }
  </script>
            }
        }
    });
    
    const amountCtx = document.getElementById('amountChart').getContext('2d');
    new Chart(amountCtx, {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: 'Loan Amount',
                    data: loanAmounts,
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderRadius: 2
                },
                {
                    label: 'Payment Amount',
                    data: paymentAmounts,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderRadius: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
            }
        }
    });
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
    .form-select, .form-control { transition: all 0.2s ease; }
    .form-select:focus, .form-control:focus { box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); }
    
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
