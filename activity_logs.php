<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
date_default_timezone_set('Asia/Manila');
require_once 'include/head.php';

$server_time = mysqli_query($conn, "SELECT NOW() as now");
$server_time_row = mysqli_fetch_assoc($server_time);
$current_time = $server_time_row['now'];

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$user_type_filter = isset($_GET['user_type']) ? $_GET['user_type'] : '';

$where = "1=1";
if($search) {
    $where .= " AND (username LIKE '%$search%' OR action LIKE '%$search%' OR description LIKE '%$search%')";
}
if($filter) {
    $where .= " AND action = '$filter'";
}
if($user_type_filter) {
    $where .= " AND user_type = '$user_type_filter'";
}
if($date_from) {
    $where .= " AND DATE(created_at) >= '$date_from'";
}
if($date_to) {
    $where .= " AND DATE(created_at) <= '$date_to'";
}

$retrieve = mysqli_query($conn, "SELECT * FROM activity_logs WHERE $where ORDER BY id DESC");
$total = mysqli_num_rows($retrieve);

$actions = mysqli_query($conn, "SELECT DISTINCT action FROM activity_logs ORDER BY action");

$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM activity_logs WHERE user_type = 'admin'"));
$count_admin = $total_admin['count'] ?? 0;

$total_customer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM activity_logs WHERE user_type = 'customer'"));
$count_customer = $total_customer['count'] ?? 0;

$action_counts = mysqli_query($conn, "
    SELECT action, COUNT(*) as count 
    FROM activity_logs 
    GROUP BY action 
    ORDER BY count DESC 
    LIMIT 10
");

$daily_activity = [];
$daily_labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('M d', strtotime("-$i days"));
    $daily_labels[] = $day_name;
    
    $count = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM activity_logs WHERE DATE(created_at) = '$date'"
    ));
    $daily_activity[] = intval($count['count'] ?? 0);
}

$top_users = mysqli_query($conn, "
    SELECT username, user_type, COUNT(*) as count 
    FROM activity_logs 
    GROUP BY username, user_type 
    ORDER BY count DESC 
    LIMIT 10
");

$hourly_distribution = [];
for ($i = 0; $i < 24; $i++) {
    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
    $count = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM activity_logs WHERE DATE_FORMAT(created_at, '%H') = '$hour'"
    ));
    $hourly_distribution[] = intval($count['count'] ?? 0);
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
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-history text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-1">System Activity Report</h1>
              <p class="text-muted mb-0">Admin monitoring and system logs</p>
            </div>
          </div>
          <div class="mt-3">
            <span class="badge bg-secondary">DB Time: <?php echo date('M d, Y h:i:s A', strtotime($current_time)); ?></span>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #4f46e5;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Activities</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo number_format($total); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #10b981;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Admin Actions</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo number_format($count_admin); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Customer Actions</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo number_format($count_customer); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card" style="border-left: 4px solid #0ea5e9;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Today's Activities</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $daily_activity[6]; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Activities by User Type</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userTypeChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Daily Activity (7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Hourly Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="hourlyChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Actions & Users -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Top Actions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 py-2">Action</th>
                                        <th class="px-3 py-2 text-end">Count</th>
                                        <th class="px-3 py-2 text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    mysqli_data_seek($action_counts, 0);
                                    $total_actions = $count_admin + $count_customer;
                                    while($action = mysqli_fetch_assoc($action_counts)): 
                                    ?>
                                    <tr>
                                        <td class="px-3 py-2"><?php echo htmlspecialchars($action['action']); ?></td>
                                        <td class="px-3 py-2 text-end"><?php echo $action['count']; ?></td>
                                        <td class="px-3 py-2 text-end"><?php echo $total > 0 ? number_format(($action['count'] / $total) * 100, 1) : 0; ?>%</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Top Active Users</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 py-2">Username</th>
                                        <th class="px-3 py-2 text-center">Type</th>
                                        <th class="px-3 py-2 text-end">Activities</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = mysqli_fetch_assoc($top_users)): ?>
                                    <tr>
                                        <td class="px-3 py-2"><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td class="px-3 py-2 text-center">
                                            <?php 
                                            $badge = $user['user_type'] == 'admin' ? 'bg-danger' : 'bg-success';
                                            ?>
                                            <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($user['user_type']); ?></span>
                                        </td>
                                        <td class="px-3 py-2 text-end"><?php echo $user['count']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="form-panel mb-4">
          <div class="form-panel-body py-3">
            <form method="GET" action="" class="row g-3 mb-0">
              <div class="col-md-3">
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                  <input type="text" name="search" class="form-control" placeholder="Search logs..." value="<?php echo $search; ?>">
                </div>
              </div>
              <div class="col-md-2">
                <select name="user_type" class="form-select">
                  <option value="">All Users</option>
                  <option value="admin" <?php echo $user_type_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                  <option value="customer" <?php echo $user_type_filter == 'customer' ? 'selected' : ''; ?>>User</option>
                </select>
              </div>

              <div class="col-md-2">
                <select name="filter" class="form-select">
                  <option value="">All Actions</option>
                  <?php 
                  mysqli_data_seek($actions, 0);
                  while($action = mysqli_fetch_assoc($actions)): ?>
                  <option value="<?php echo $action['action']; ?>" <?php echo $filter == $action['action'] ? 'selected' : ''; ?>><?php echo $action['action']; ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From Date" value="<?php echo $date_from; ?>">
              </div>
              <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="To Date" value="<?php echo $date_to; ?>">
              </div>
              <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-filter"></i>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Activity Logs Table -->
        <?php if($total > 0): ?>
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Activity Log Details</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover mb-0" style="font-size: 0.8rem;">
                    <thead class="table-light">
                      <tr>
                        <th class="px-2 py-2">Date & Time</th>
                        <th class="px-2 py-2">User</th>
                        <th class="px-2 py-2">User Type</th>
                        <th class="px-2 py-2">Action</th>
                        <th class="px-2 py-2">Description</th>
                        <th class="px-2 py-2">IP Address</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($result = mysqli_fetch_assoc($retrieve)): ?>
                      <tr>
                        <td class="px-2 py-2"><?php echo date('M d, Y h:i A', strtotime($result["created_at"])); ?></td>
                        <td class="px-2 py-2">
                          <span class="badge bg-primary"><?php echo htmlspecialchars($result["username"] ?? 'System'); ?></span>
                        </td>
                        <td class="px-2 py-2">
                          <?php 
                            $user_type = $result["user_type"] ?? 'unknown';
                            $type_badge = 'bg-secondary';
                            if($user_type == 'admin') $type_badge = 'bg-danger';
                            elseif($user_type == 'customer') $type_badge = 'bg-success';
                          ?>
                          <span class="badge <?php echo $type_badge; ?>"><?php echo ucfirst(htmlspecialchars($user_type)); ?></span>
                        </td>
                        <td class="px-2 py-2">
                          <?php 
                            $badge_class = 'bg-secondary';
                            if(stripos($result["action"], 'Login') !== false) $badge_class = 'bg-success';
                            elseif(stripos($result["action"], 'Logout') !== false) $badge_class = 'bg-warning text-dark';
                            elseif(stripos($result["action"], 'Create') !== false || stripos($result["action"], 'Add') !== false) $badge_class = 'bg-info';
                            elseif(stripos($result["action"], 'Update') !== false) $badge_class = 'bg-primary';
                            elseif(stripos($result["action"], 'Delete') !== false || stripos($result["action"], 'Reject') !== false) $badge_class = 'bg-danger';
                            elseif(stripos($result["action"], 'Approve') !== false) $badge_class = 'bg-success';
                          ?>
                          <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($result["action"]); ?></span>
                        </td>
                        <td class="px-2 py-2"><?php echo htmlspecialchars($result["description"] ?? '-'); ?></td>
                        <td class="px-2 py-2"><small class="text-muted"><?php echo htmlspecialchars($result["ip_address"] ?? '-'); ?></small></td>
                      </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-muted small text-center">
          Showing <?php echo $total; ?> log entry(s)
        </div>
        
        <?php else: ?>
        <div class="form-panel">
          <div class="form-panel-body empty-state py-5">
            <i class="fas fa-history"></i>
            <h4>No Activity Logs Found</h4>
            <p>No activity logs match your search criteria.</p>
          </div>
        </div>
        <?php endif; ?>
        
      </main>
    </div>
  </div>

  <?php require_once 'include/footer.php'; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    let userTypeChart, dailyChart, hourlyChart;
    
    const userTypeCtx = document.getElementById('userTypeChart').getContext('2d');
    userTypeChart = new Chart(userTypeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Admin', 'Customer'],
            datasets: [{
                data: [<?php echo $count_admin; ?>, <?php echo $count_customer; ?>],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)'
                ],
                borderColor: [
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)'
                ],
                borderWidth: 1,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { animateRotate: true, animateScale: true, duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) bounceChart(userTypeChart); },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            }
        }
    });
    
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    dailyChart = new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($daily_labels); ?>,
            datasets: [{
                label: 'Activities',
                data: <?php echo json_encode($daily_activity); ?>,
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
            onClick: function(e, elements) { if(elements.length > 0) animateBar(dailyChart); },
            plugins: { 
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
    
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    hourlyChart = new Chart(hourlyCtx, {
        type: 'line',
        data: {
            labels: ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'],
            datasets: [{
                label: 'Activities',
                data: <?php echo json_encode($hourly_distribution); ?>,
                backgroundColor: 'rgba(14, 165, 233, 0.2)',
                borderColor: 'rgba(14, 165, 233, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(14, 165, 233, 1)',
                pointRadius: 4,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1500, easing: 'easeOutQuart' },
            onClick: function(e, elements) { if(elements.length > 0) animateLine(hourlyChart); },
            plugins: { 
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } }
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
  </script>

  <script>

  <style>
  body.dark-mode {
      background: #0f172a !important;
      color: #e2e8f0 !important;
  }
  body.dark-mode .container-fluid {
      background: #0f172a !important;
  }
  body.dark-mode .form-panel {
      background: #1e293b !important;
      border-color: #334155 !important;
  }
  body.dark-mode .form-panel-body {
      background: #1e293b !important;
  }
  body.dark-mode .page-title-section h1 {
      color: #f1f5f9 !important;
  }
  body.dark-mode .text-muted {
      color: #94a3b8 !important;
  }
  body.dark-mode .form-select,
  body.dark-mode .form-control,
  body.dark-mode .input-group-text {
      background: #334155 !important;
      border-color: #475569 !important;
      color: #f1f5f9 !important;
  }
  body.dark-mode .table {
      color: #e2e8f0 !important;
      background: #1e293b !important;
  }
  body.dark-mode .table thead th {
      background: #1e293b !important;
      color: #94a3b8 !important;
      border-color: #334155 !important;
  }
  body.dark-mode .table tbody tr {
      background: #1e293b !important;
      border-color: #334155 !important;
  }
  body.dark-mode .table tbody td {
      border-color: #334155 !important;
      color: #e2e8f0 !important;
  }
  body.dark-mode .empty-state {
      color: #94a3b8 !important;
  }
  body.dark-mode .badge.bg-secondary {
      background: #475569 !important;
  }
  </style>
  
  <style>
    @keyframes fadeInUp { 
      from { opacity: 0; transform: translateY(30px); } 
      to { opacity: 1; transform: translateY(0); } 
    }
    @keyframes fadeIn { 
      from { opacity: 0; } 
      to { opacity: 1; } 
    }
    @keyframes scaleIn { 
      from { opacity: 0; transform: scale(0.8); } 
      to { opacity: 1; transform: scale(1); } 
    }
    @keyframes slideInLeft {
      from { opacity: 0; transform: translateX(-40px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideInUp {
      from { opacity: 0; transform: translateY(25px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }
    @keyframes bounceIn {
      0% { opacity: 0; transform: scale(0.3); }
      50% { transform: scale(1.05); }
      70% { transform: scale(0.9); }
      100% { opacity: 1; transform: scale(1); }
    }
    @keyframes slideInRight {
      from { opacity: 0; transform: translateX(30px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    .animate-on-scroll {
      opacity: 0;
      transform: translateY(40px);
      transition: opacity 0.7s cubic-bezier(0.4, 0, 0.2, 1), 
                  transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .animate-on-scroll.visible {
      opacity: 1;
      transform: translateY(0);
    }
    
    .summary-card { 
      opacity: 0; 
      animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards; 
      transition: box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                  transform 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                  border-color 0.3s ease;
      border-radius: 12px;
      border: 1px solid rgba(0,0,0,0.05);
      position: relative;
      overflow: hidden;
    }
    .summary-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s ease;
    }
    .summary-card:hover::before {
      left: 100%;
    }
    .summary-card:hover { 
      transform: translateY(-8px) scale(1.02); 
      box-shadow: 0 20px 40px rgba(0,0,0,0.15); 
    }
    
    .card { 
      opacity: 0; 
      animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards; 
      transition: box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                  transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 16px;
      border: 1px solid rgba(0,0,0,0.05);
      overflow: hidden;
    }
    .card:hover { 
      transform: translateY(-6px); 
      box-shadow: 0 15px 35px rgba(0,0,0,0.12); 
    }
    
    .table tbody tr { 
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      opacity: 0; 
      animation: slideInRight 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
      position: relative;
    }
    .table tbody tr::after {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      width: 3px;
      height: 100%;
      background: linear-gradient(180deg, #4f46e5, #0ea5e9);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    .table tbody tr:hover { 
      background-color: rgba(79, 70, 229, 0.08); 
      transform: translateX(5px);
    }
    .table tbody tr:hover::after {
      opacity: 1;
    }
    
    canvas { 
      opacity: 0; 
      animation: scaleIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards; 
      transition: transform 0.3s ease;
    }
    canvas:hover {
      transform: scale(1.02);
    }
    
    .btn { 
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    .btn::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      transform: translate(-50%, -50%);
      transition: width 0.6s ease, height 0.6s ease;
    }
    .btn:hover::after {
      width: 300px;
      height: 300px;
    }
    .btn:hover { 
      transform: translateY(-3px); 
      box-shadow: 0 8px 20px rgba(0,0,0,0.2); 
    }
    .btn:active {
      transform: translateY(-1px);
    }
    
    .badge { 
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
    }
    .badge:hover { 
      transform: scale(1.15) rotate(5deg); 
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .form-select, .form-control { 
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 8px;
    }
    .form-select:focus, .form-control:focus { 
      transform: scale(1.02);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }
    
    .form-panel {
      border-radius: 16px;
      animation: slideInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
      opacity: 0;
    }
    
    .report-icon {
      flex-shrink: 0;
      box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
      animation: slideInLeft 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards,
                 pulse 2s ease-in-out 1s infinite;
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
      animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.1s forwards;
      opacity: 0;
    }
    .page-title-section p {
      animation: fadeIn 0.5s ease-out 0.3s forwards;
      opacity: 0;
    }
    .page-title-section .badge {
      animation: bounceIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.4s forwards;
      opacity: 0;
    }
    
    .stat-number {
      animation: countUp 1s ease-out forwards;
    }
    
    .input-group {
      transition: all 0.3s ease;
    }
    .input-group:focus-within {
      transform: scale(1.02);
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.15);
    }
    
    .card-header {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      transition: background 0.3s ease;
    }
    .card:hover .card-header {
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    }
    
    .empty-state {
      animation: fadeInUp 0.6s ease-out forwards;
    }
    .empty-state i {
      animation: pulse 2s ease-in-out infinite;
    }
  </style>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.summary-card').forEach((el, i) => {
        el.style.animationDelay = (i * 0.12) + 's';
        el.classList.add('animate-on-scroll');
      });
      document.querySelectorAll('.card').forEach((el, i) => {
        if (!el.closest('.summary-card')) {
          el.style.animationDelay = (0.3 + i * 0.1) + 's';
          el.classList.add('animate-on-scroll');
        }
      });
      document.querySelectorAll('.table tbody tr').forEach((el, i) => {
        el.style.animationDelay = (i * 0.03) + 's';
      });
      document.querySelectorAll('canvas').forEach((el, i) => {
        el.style.animationDelay = (0.5 + i * 0.2) + 's';
      });
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
          }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
      
      document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
      
      document.querySelectorAll('.summary-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
          this.style.zIndex = '10';
        });
        card.addEventListener('mouseleave', function() {
          this.style.zIndex = '1';
        });
      });
    });
  </script>
</body>
</html>
