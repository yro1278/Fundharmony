<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$today = date('Y-m-d');

$overdue_loans = mysqli_query($conn, "
    SELECT 
        a.*,
        c.first_name,
        c.middle_name,
        c.surname,
        c.email,
        c.phone,
        act.account_type_name,
        acs.account_status_name,
        DATEDIFF('$today', a.due_date) as days_overdue
    FROM accounts a
    INNER JOIN customers c ON a.customer = c.customer_number
    LEFT JOIN account_type act ON a.account_type = act.account_type_number
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE a.due_date < '$today' 
    AND a.loan_balance > 0 
    AND a.account_status NOT IN ('-3', '3', '4', '0')
    ORDER BY days_overdue DESC
");

$total_overdue = mysqli_num_rows($overdue_loans);

$total_overdue_amount = 0;
$total_penalty = 0;
while ($loan = mysqli_fetch_assoc($overdue_loans)) {
    $total_overdue_amount += floatval($loan['loan_balance'] ?? 0);
    $total_penalty += floatval($loan['penalty'] ?? 0) + floatval($loan['overdue_interest'] ?? 0);
}
mysqli_data_seek($overdue_loans, 0);

$overdue_by_type = mysqli_query($conn, "
    SELECT 
        act.account_type_name,
        COUNT(a.account_number) as count,
        COALESCE(SUM(a.loan_balance), 0) as total_balance
    FROM accounts a
    LEFT JOIN account_type act ON a.account_type = act.account_type_number
    WHERE a.due_date < '$today' 
    AND a.loan_balance > 0 
    AND a.account_status NOT IN ('-3', '3', '4', '0')
    GROUP BY act.account_type_name
    ORDER BY count DESC
");

$total_loans_all = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts WHERE account_status NOT IN ('-3', '3', '4', '0')"
));
$count_all_loans = $total_loans_all['count'] ?? 0;
$overdue_percentage = $count_all_loans > 0 ? ($total_overdue / $count_all_loans) * 100 : 0;

$monthly_overdue = [];
$monthly_labels = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $monthly_labels[] = $month_name;
    
    $overdue = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM accounts WHERE due_date < '$today' AND loan_balance > 0 AND account_status NOT IN ('-3', '3', '4', '0') AND DATE_FORMAT(due_date, '%Y-%m') <= '$month'"
    ));
    $monthly_overdue[] = intval($overdue['count'] ?? 0);
}

$late_trends = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    
    $new_late = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM accounts WHERE due_date < '$today' AND loan_balance > 0 AND account_status NOT IN ('-3', '3', '4', '0') AND DATE_FORMAT(due_date, '%Y-%m') = '$month'"
    ));
    
    $late_trends[] = intval($new_late['count'] ?? 0);
}

$contact_status_counts = [
    'contacted' => 0,
    'not_contacted' => 0
];
mysqli_data_seek($overdue_loans, 0);
while ($loan = mysqli_fetch_assoc($overdue_loans)) {
    if (!empty($loan['phone']) || !empty($loan['email'])) {
        $contact_status_counts['contacted']++;
    } else {
        $contact_status_counts['not_contacted']++;
    }
}
mysqli_data_seek($overdue_loans, 0);
?>

<body>
  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid" style="overflow-x: hidden;">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="overflow-x: hidden;">
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-exclamation-triangle text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-1">Overdue / Delinquent Loan Report</h1>
              <p class="text-muted mb-0">Borrowers with late payments</p>
            </div>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #ef4444;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Overdue Loans</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $total_overdue; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Overdue Amount</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_overdue_amount, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #dc2626;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Penalties</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_penalty, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #4f46e5;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Overdue Percentage</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo number_format($overdue_percentage, 1); ?>%</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Overdue Loans Percentage</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="overdueChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Late Payment Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue by Loan Type -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Overdue by Loan Type</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-2">Loan Type</th>
                                <th class="px-3 py-2 text-center">Overdue Count</th>
                                <th class="px-3 py-2 text-end">Total Balance</th>
                                <th class="px-3 py-2 text-end">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($overdue_by_type) > 0): ?>
                                <?php while($type = mysqli_fetch_assoc($overdue_by_type)): ?>
                                <tr>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($type['account_type_name'] ?? 'N/A'); ?></td>
                                    <td class="px-3 py-2 text-center"><?php echo $type['count']; ?></td>
                                    <td class="px-3 py-2 text-end">₱<?php echo number_format($type['total_balance'], 2); ?></td>
                                    <td class="px-3 py-2 text-end"><?php echo $total_overdue > 0 ? number_format(($type['count'] / $total_overdue) * 100, 1) : 0; ?>%</td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No overdue loans</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Overdue Loans Table -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Delinquent Borrowers</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-2 py-2">Borrower Name</th>
                                <th class="px-2 py-2">Loan ID</th>
                                <th class="px-2 py-2">Due Date</th>
                                <th class="px-2 py-2 text-center">Days Overdue</th>
                                <th class="px-2 py-2 text-end">Penalty</th>
                                <th class="px-2 py-2 text-end">Balance</th>
                                <th class="px-2 py-2">Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($overdue_loans) > 0): ?>
                                <?php while($loan = mysqli_fetch_assoc($overdue_loans)): ?>
                                <?php 
                                    $borrower_name = trim(($loan['first_name'] ?? '') . ' ' . ($loan['middle_name'] ? $loan['middle_name'] . ' ' : '') . ($loan['surname'] ?? ''));
                                    $days_overdue = intval($loan['days_overdue'] ?? 0);
                                    $penalty = floatval($loan['penalty'] ?? 0) + floatval($loan['overdue_interest'] ?? 0);
                                    
                                    $severity_class = '';
                                    $severity_badge = '';
                                    if ($days_overdue > 90) {
                                        $severity_class = 'text-danger fw-bold';
                                        $severity_badge = 'bg-danger';
                                    } elseif ($days_overdue > 30) {
                                        $severity_class = 'text-warning fw-bold';
                                        $severity_badge = 'bg-warning text-dark';
                                    } else {
                                        $severity_class = 'text-dark';
                                        $severity_badge = 'bg-info';
                                    }
                                ?>
                                <tr>
                                    <td class="px-2 py-2 <?php echo $severity_class; ?>"><?php echo htmlspecialchars($borrower_name); ?></td>
                                    <td class="px-2 py-2">#<?php echo $loan['account_number']; ?></td>
                                    <td class="px-2 py-2"><?php echo $loan['due_date'] ? date('M d, Y', strtotime($loan['due_date'])) : '-'; ?></td>
                                    <td class="px-2 py-2 text-center <?php echo $severity_class; ?>">
                                        <span class="badge <?php echo $severity_badge; ?>"><?php echo $days_overdue; ?> days</span>
                                    </td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($penalty, 2); ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($loan['loan_balance'], 2); ?></td>
                                    <td class="px-2 py-2">
                                        <?php if(!empty($loan['phone'])): ?>
                                            <span class="text-success" title="<?php echo htmlspecialchars($loan['phone']); ?>"><i class="fas fa-phone"></i></span>
                                        <?php endif; ?>
                                        <?php if(!empty($loan['email'])): ?>
                                            <span class="text-primary" title="<?php echo htmlspecialchars($loan['email']); ?>"><i class="fas fa-envelope"></i></span>
                                        <?php endif; ?>
                                        <?php if(empty($loan['phone']) && empty($loan['email'])): ?>
                                            <span class="text-muted"><i class="fas fa-minus"></i></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No overdue loans found.</td>
                                </tr>
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
    let overdueChart, trendChart;
    
    const overdueCtx = document.getElementById('overdueChart').getContext('2d');
    overdueChart = new Chart(overdueCtx, {
        type: 'doughnut',
        data: {
            labels: ['Overdue Loans', 'Current Loans'],
            datasets: [{
                data: [<?php echo $total_overdue; ?>, <?php echo max(0, $count_all_loans - $total_overdue); ?>],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(16, 185, 129, 0.8)'
                ],
                borderColor: [
                    'rgba(239, 68, 68, 1)',
                    'rgba(16, 185, 129, 1)'
                ],
                borderWidth: 1,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { animateRotate: true, animateScale: true, duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) bounceChart(overdueChart); },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            }
        }
    });
    
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($monthly_labels); ?>,
            datasets: [{
                label: 'Late Loans',
                data: <?php echo json_encode($late_trends); ?>,
                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                borderColor: 'rgba(239, 68, 68, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                pointRadius: 4,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) animateLine(trendChart); },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
    
    function bounceChart(chart) {
        chart.update({ duration: 800, easing: 'easeOutBounce' });
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
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
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
