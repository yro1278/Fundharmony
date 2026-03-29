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

$where = "c.customer_number IS NOT NULL";
if ($search) {
    $where .= " AND (c.first_name LIKE '%$search%' OR c.surname LIKE '%$search%' OR c.middle_name LIKE '%$search%' OR c.customer_number LIKE '%$search%' OR c.email LIKE '%$search%' OR c.phone LIKE '%$search%')";
}
if ($status_filter !== '') {
    if ($status_filter == 'active') {
        $where .= " AND c.status = '1'";
    } elseif ($status_filter == 'inactive') {
        $where .= " AND c.status = '0'";
    }
}

$customers = mysqli_query($conn, "
    SELECT 
        c.*,
        (SELECT COUNT(*) FROM accounts a WHERE a.customer = c.customer_number) as total_loans,
        (SELECT COALESCE(SUM(a.loan_amount), 0) FROM accounts a WHERE a.customer = c.customer_number AND a.account_status NOT IN (0, 4)) as total_borrowed,
        (SELECT COALESCE(SUM(p.payment_amount), 0) FROM payments p INNER JOIN accounts a ON p.account_number = a.account_number WHERE a.customer = c.customer_number) as total_paid,
        (SELECT COALESCE(SUM(a.loan_balance), 0) FROM accounts a WHERE a.customer = c.customer_number AND a.account_status NOT IN (0, 3, 4)) as current_balance
    FROM customers c
    WHERE $where
    ORDER BY c.registration_date DESC
");

$total_customers = mysqli_num_rows($customers);

$active_customers = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM customers WHERE status = '1'"
));
$count_active = $active_customers['count'] ?? 0;

$inactive_customers = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM customers WHERE status = '0'"
));
$count_inactive = $inactive_customers['count'] ?? 0;

$monthly_registrations = [];
$monthly_labels = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $monthly_labels[] = $month_name;
    
    $reg = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM customers WHERE DATE_FORMAT(registration_date, '%Y-%m') = '$month'"
    ));
    $monthly_registrations[] = intval($reg['count'] ?? 0);
}

$total_borrowed_all = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(loan_amount), 0) as total FROM accounts WHERE account_status NOT IN (0, 4)"
));
$total_borrowed = $total_borrowed_all['total'] ?? 0;

$total_paid_all = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments"
));
$total_paid = $total_paid_all['total'] ?? 0;
?>

<body>
  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid" style="overflow-x: hidden;">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="overflow-x: hidden;">
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-users text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-1">Borrower / Client Report</h1>
              <p class="text-muted mb-0">Information about all borrowers in the system</p>
            </div>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #4f46e5;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Borrowers</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $total_customers; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #10b981;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Borrowed</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_borrowed, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Payments Made</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_paid, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #0ea5e9;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Active / Inactive</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><span class="text-success"><?php echo $count_active; ?></span> / <span class="text-danger"><?php echo $count_inactive; ?></span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>User Registrations (Last 12 Months)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="registrationChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Active vs Inactive Borrowers</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, ID, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="borrower_report.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Borrower Details</h5>
                    <a href="excel_customers.php" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-2 py-2">Borrower ID</th>
                                <th class="px-2 py-2">Full Name</th>
                                <th class="px-2 py-2">Contact Info</th>
                                <th class="px-2 py-2">Registration Date</th>
                                <th class="px-2 py-2 text-center">Loans</th>
                                <th class="px-2 py-2 text-end">Total Borrowed</th>
                                <th class="px-2 py-2 text-end">Total Paid</th>
                                <th class="px-2 py-2 text-end">Balance</th>
                                <th class="px-2 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($customers) > 0): ?>
                                <?php while($customer = mysqli_fetch_assoc($customers)): ?>
                                <tr>
                                    <td class="px-2 py-2">#<?php echo $customer['customer_number']; ?></td>
                                    <td class="px-2 py-2">
                                        <?php 
                                        $name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['middle_name'] ? $customer['middle_name'] . ' ' : '') . ($customer['surname'] ?? ''));
                                        echo htmlspecialchars($name);
                                        ?>
                                    </td>
                                    <td class="px-2 py-2">
                                        <?php if(!empty($customer['email'])): ?>
                                            <div><i class="fas fa-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($customer['email']); ?></div>
                                        <?php endif; ?>
                                        <?php if(!empty($customer['phone'])): ?>
                                            <div><i class="fas fa-phone me-1 text-muted"></i> <?php echo htmlspecialchars($customer['phone']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 py-2"><?php echo $customer['registration_date'] ? date('M d, Y', strtotime($customer['registration_date'])) : '-'; ?></td>
                                    <td class="px-2 py-2 text-center"><?php echo $customer['total_loans']; ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($customer['total_borrowed'], 2); ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($customer['total_paid'], 2); ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($customer['current_balance'], 2); ?></td>
                                    <td class="px-2 py-2 text-center">
                                        <?php 
                                        $status = $customer['status'];
                                        if($status == '1') {
                                            echo '<span class="badge bg-success">Active</span>';
                                        } else {
                                            echo '<span class="badge bg-danger">Inactive</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No borrower records found.</td>
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
    let registrationChart, statusChart;
    
    const registrationLabels = <?php echo json_encode($monthly_labels); ?>;
    const registrationData = <?php echo json_encode($monthly_registrations); ?>;
    
    const regCtx = document.getElementById('registrationChart').getContext('2d');
    registrationChart = new Chart(regCtx, {
        type: 'bar',
        data: {
            labels: registrationLabels,
            datasets: [{
                label: 'New Registrations',
                data: registrationData,
                backgroundColor: 'rgba(79, 70, 229, 0.8)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: '#818cf8'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) animateBar(registrationChart); },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
    
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                data: [<?php echo $count_active; ?>, <?php echo $count_inactive; ?>],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgba(16, 185, 129, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 1,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { animateRotate: true, animateScale: true, duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) bounceChart(statusChart); },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            }
        }
    });
    
    function bounceChart(chart) {
        chart.update({ duration: 800, easing: 'easeOutBounce' });
    }
    
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
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }
    
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
      box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
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
