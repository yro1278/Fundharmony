<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$today = date('Y-m-d');

$where = "p.payment_number IS NOT NULL";
if ($search) {
    $where .= " AND (c.first_name LIKE '%$search%' OR c.surname LIKE '%$search%' OR c.middle_name LIKE '%$search%' OR p.account_number LIKE '%$search%' OR p.payment_number LIKE '%$search%')";
}
if ($status_filter !== '') {
    if ($status_filter == 'paid') {
        $where .= " AND (a.loan_balance IS NULL OR a.loan_balance <= 0 OR a.account_status = '-3')";
    } elseif ($status_filter == 'partial') {
        $where .= " AND a.loan_balance > 0 AND a.account_status NOT IN ('-3', '3', '4')";
    } elseif ($status_filter == 'late') {
        $where .= " AND a.due_date < '$today' AND a.loan_balance > 0";
    }
}
if ($date_from !== '') {
    $where .= " AND p.payment_date >= '$date_from'";
}
if ($date_to !== '') {
    $where .= " AND p.payment_date <= '$date_to'";
}

$payments = mysqli_query($conn, "
    SELECT 
        p.*,
        c.first_name,
        c.middle_name,
        c.surname,
        a.loan_amount,
        a.interest,
        a.loan_balance,
        a.due_date,
        acs.account_status_name,
        (SELECT SUM(payment_amount) FROM payments WHERE account_number = p.account_number) as total_paid_for_loan,
        (SELECT loan_amount + interest FROM accounts WHERE account_number = p.account_number) as total_expected
    FROM payments p
    INNER JOIN accounts a ON p.account_number = a.account_number
    INNER JOIN customers c ON a.customer = c.customer_number
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE $where
    ORDER BY p.payment_date DESC, p.payment_number DESC
");

$total_payments = mysqli_num_rows($payments);
$total_amount = 0;
while ($pay = mysqli_fetch_assoc($payments)) {
    $total_amount += floatval($pay['payment_amount'] ?? 0);
}
mysqli_data_seek($payments, 0);

$total_collected = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments"
));
$total_collected_amount = $total_collected['total'] ?? 0;

$paid_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(DISTINCT a.account_number) as count FROM accounts a WHERE (a.loan_balance IS NULL OR a.loan_balance <= 0 OR a.account_status = '-3')"
));
$count_paid = $paid_count['count'] ?? 0;

$partial_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts a WHERE a.loan_balance > 0 AND a.account_status NOT IN ('-3', '3', '4', '0')"
));
$count_partial = $partial_count['count'] ?? 0;

$late_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts a WHERE a.due_date < '$today' AND a.loan_balance > 0 AND a.account_status NOT IN ('-3', '3', '4', '0')"
));
$count_late = $late_count['count'] ?? 0;

$monthly_payments = [];
$monthly_labels = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $monthly_labels[] = $month_name;
    
    $pay = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(payment_amount), 0) as total, COUNT(*) as count FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month'"
    ));
    $monthly_payments[] = [
        'amount' => floatval($pay['total'] ?? 0),
        'count' => intval($pay['count'] ?? 0)
    ];
}

$monthly_trends = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    
    $paid_loan = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(DISTINCT a.account_number) as count FROM accounts a 
        INNER JOIN payments p ON a.account_number = p.account_number 
        WHERE DATE_FORMAT(p.payment_date, '%Y-%m') = '$month' 
        AND (a.loan_balance IS NULL OR a.loan_balance <= 0)"
    ));
    
    $partial_loan = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM accounts a 
        INNER JOIN payments p ON a.account_number = p.account_number 
        WHERE DATE_FORMAT(p.payment_date, '%Y-%m') = '$month'
        AND a.loan_balance > 0"
    ));
    
    $monthly_trends[] = [
        'paid' => intval($paid_loan['count'] ?? 0),
        'partial' => intval($partial_loan['count'] ?? 0)
    ];
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
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-money-bill-wave text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-1">Payment Transactions Report</h1>
              <p class="text-muted mb-0">Complete list of all loan repayments</p>
            </div>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #4f46e5;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Transactions</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $total_payments; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #10b981;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Collected</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_collected_amount, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card" style="border-left: 4px solid #0ea5e9;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Fully Paid</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_paid; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Partial</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_partial; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card summary-card" style="border-left: 4px solid #ef4444;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Late</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_late; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Monthly Payment Collection</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="collectionChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Payment Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or payment ID..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="paid" <?php echo $status_filter == 'paid' ? 'selected' : ''; ?>>Fully Paid</option>
                            <option value="partial" <?php echo $status_filter == 'partial' ? 'selected' : ''; ?>>Partial Payment</option>
                            <option value="late" <?php echo $status_filter == 'late' ? 'selected' : ''; ?>>Late Payment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To Date" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
                        <a href="payment_transactions.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Legend -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <span class="me-3"><strong>Status Legend:</strong></span>
                <span class="badge bg-success me-1">Fully Paid</span>
                <span class="badge bg-warning text-dark me-1">Partial Payment</span>
                <span class="badge bg-danger me-1">Late Payment</span>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Payment Details</h5>
                    <a href="excel_payments.php" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-2 py-2">Payment ID</th>
                                <th class="px-2 py-2">Borrower Name</th>
                                <th class="px-2 py-2">Loan ID</th>
                                <th class="px-2 py-2 text-end">Amount Paid</th>
                                <th class="px-2 py-2">Payment Date</th>
                                <th class="px-2 py-2">Method</th>
                                <th class="px-2 py-2 text-end">Balance</th>
                                <th class="px-2 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($payments) > 0): ?>
                                <?php while($payment = mysqli_fetch_assoc($payments)): ?>
                                <?php 
                                    $borrower_name = trim(($payment['first_name'] ?? '') . ' ' . ($payment['middle_name'] ? $payment['middle_name'] . ' ' : '') . ($payment['surname'] ?? ''));
                                    $total_expected = floatval($payment['total_expected'] ?? 0);
                                    $total_paid = floatval($payment['total_paid_for_loan'] ?? 0);
                                    $remaining = $total_expected - $total_paid;
                                    
                                    $payment_status = '';
                                    $status_badge = '';
                                    if ($remaining <= 0 || $payment['account_status_name'] == 'Closed') {
                                        $payment_status = 'Paid';
                                        $status_badge = 'bg-success';
                                    } elseif ($payment['due_date'] && $payment['due_date'] < $payment['payment_date'] && $remaining > 0) {
                                        $payment_status = 'Late';
                                        $status_badge = 'bg-danger';
                                    } else {
                                        $payment_status = 'Partial';
                                        $status_badge = 'bg-warning text-dark';
                                    }
                                ?>
                                <tr>
                                    <td class="px-2 py-2">#<?php echo $payment['payment_number']; ?></td>
                                    <td class="px-2 py-2"><?php echo htmlspecialchars($borrower_name); ?></td>
                                    <td class="px-2 py-2">#<?php echo $payment['account_number']; ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($payment['payment_amount'], 2); ?></td>
                                    <td class="px-2 py-2"><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                    <td class="px-2 py-2"><?php echo htmlspecialchars($payment['payment_method'] ?? '-'); ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format(max(0, $remaining), 2); ?></td>
                                    <td class="px-2 py-2 text-center">
                                        <span class="badge <?php echo $status_badge; ?>"><?php echo $payment_status; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No payment records found.</td>
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
    let collectionChart, trendChart;
    
    const collectionLabels = <?php echo json_encode($monthly_labels); ?>;
    const collectionData = <?php echo json_encode(array_column($monthly_payments, 'amount')); ?>;
    
    const collectionCtx = document.getElementById('collectionChart').getContext('2d');
    collectionChart = new Chart(collectionCtx, {
        type: 'bar',
        data: {
            labels: collectionLabels,
            datasets: [{
                label: 'Amount Collected',
                data: collectionData,
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
            onClick: function(e, elements) { if(elements.length > 0) animateBar(collectionChart); },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(value) { return '₱' + value.toLocaleString(); } } }
            }
        }
    });
    
    const trendLabels = <?php echo json_encode($monthly_labels); ?>;
    const paidData = <?php echo json_encode(array_column($monthly_trends, 'paid')); ?>;
    const partialData = <?php echo json_encode(array_column($monthly_trends, 'partial')); ?>;
    
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Fully Paid',
                    data: paidData,
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 8
                },
                {
                    label: 'Partial',
                    data: partialData,
                    borderColor: 'rgba(245, 158, 11, 1)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) animateLineMulti(trendChart); },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
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
  </script>
  
  <style>
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    .animate-scale-in { animation: scaleIn 0.5s ease-out forwards; }
    
    .summary-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      opacity: 0;
      animation: fadeInUp 0.5s ease-out forwards;
    }
    .summary-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .card {
      opacity: 0;
      animation: fadeInUp 0.5s ease-out forwards;
      transition: box-shadow 0.3s ease;
    }
    .card:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .table tbody tr {
      transition: all 0.2s ease;
      opacity: 0;
      animation: fadeIn 0.3s ease-out forwards;
    }
    .table tbody tr:hover {
      background-color: rgba(79, 70, 229, 0.05);
      transform: scale(1.01);
    }
    
    canvas {
      opacity: 0;
      animation: scaleIn 0.6s ease-out forwards;
    }
    
    .btn {
      transition: all 0.2s ease;
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .badge {
      transition: all 0.2s ease;
    }
    .badge:hover {
      transform: scale(1.1);
    }
    
    .report-icon {
      flex-shrink: 0;
      box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
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
      const cards = document.querySelectorAll('.card');
      cards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
      });
      
      const summaryCards = document.querySelectorAll('.summary-card');
      summaryCards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
      });
      
      const tableRows = document.querySelectorAll('.table tbody tr');
      tableRows.forEach((row, index) => {
        row.style.animationDelay = (index * 0.03) + 's';
      });
      
      const canvases = document.querySelectorAll('canvas');
      canvases.forEach((canvas, index) => {
        canvas.style.animationDelay = (0.3 + index * 0.15) + 's';
      });
    });
  </script>
</body>
</html>
