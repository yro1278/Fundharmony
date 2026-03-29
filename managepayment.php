<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$user_id = $_SESSION['user_id'];

$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$method_filter = isset($_GET['method']) ? $_GET['method'] : '';

$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
if(mysqli_num_rows($check_table) == 0) {
    $create_table = "CREATE TABLE IF NOT EXISTS `payments` (
      `payment_number` int(10) NOT NULL,
      `user_id` int(10) DEFAULT NULL,
      `account_number` int(10) NOT NULL,
      `payment_amount` decimal(10,2) NOT NULL,
      `payment_date` date NOT NULL,
      `payment_method` varchar(50) NOT NULL,
      `notes` text,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`payment_number`)
    )";
    mysqli_query($conn, $create_table);
}

$where = "1=1";
if($search) {
    $where .= " AND (c.first_name LIKE '%$search%' OR c.surname LIKE '%$search%' OR p.payment_number LIKE '%$search%' OR p.account_number LIKE '%$search%')";
}
if($date_from) {
    $where .= " AND p.payment_date >= '$date_from'";
}
if($date_to) {
    $where .= " AND p.payment_date <= '$date_to'";
}
if($method_filter) {
    $where .= " AND p.payment_method = '$method_filter'";
}

$retrieve = mysqli_query($conn, "SELECT p.*, c.first_name, c.surname, a.account_type 
FROM payments p 
LEFT JOIN accounts a ON p.account_number = a.account_number 
LEFT JOIN customers c ON a.customer = c.customer_number 
WHERE $where
ORDER BY p.payment_date DESC");

$total_stats = mysqli_query($conn, "SELECT 
    COUNT(*) as total_count,
    COALESCE(SUM(payment_amount), 0) as total_amount,
    COALESCE(AVG(payment_amount), 0) as avg_amount
FROM payments p
LEFT JOIN accounts a ON p.account_number = a.account_number
LEFT JOIN customers c ON a.customer = c.customer_number
WHERE $where");
$stats = mysqli_fetch_assoc($total_stats);
?>

<body>
  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="fas fa-history text-white" style="font-size: 22px;"></i>
            </div>
            <h1 class="mb-0">Payment History</h1>
          </div>
          <div class="page-actions">
            <a href="addpayment.php" class="btn btn-success">
              <i class="fas fa-plus"></i> New Payment
            </a>
            <a href="pdfpayment.php" class="btn btn-outline-primary" target="_blank">
              <i class="fas fa-file-pdf"></i> Export PDF
            </a>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 opacity-75">Total Payments</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['total_count']); ?></h3>
                            </div>
                            <i class="fas fa-receipt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-gradient-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 opacity-75">Total Amount</h6>
                                <h3 class="mb-0">₱<?php echo number_format($stats['total_amount'], 2); ?></h3>
                            </div>
                            <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-gradient-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 opacity-75">Average Payment</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['avg_amount'], 2); ?></h3>
                            </div>
                            <i class="fas fa-calculator fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo $search; ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="Cash" <?php echo $method_filter == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Bank Transfer" <?php echo $method_filter == 'Bank Transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                            <option value="Cheque" <?php echo $method_filter == 'Cheque' ? 'selected' : ''; ?>>Cheque</option>
                            <option value="E-Wallet" <?php echo $method_filter == 'E-Wallet' ? 'selected' : ''; ?>>E-Wallet</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i> Filter</button>
                        <a href="managepayment.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Table -->
        <div class="card">
          <div class="card-body p-0">
            <?php if(mysqli_num_rows($retrieve) > 0): ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th><i class="fas fa-hashtag"></i> SN</th>
                    <th><i class="fas fa-tag"></i> Payment No.</th>
                    <th><i class="fas fa-user"></i> Client</th>
                    <th><i class="fas fa-id-card"></i> Account</th>
                    <th><i class="fas fa-peso-sign"></i> Amount</th>
                    <th><i class="fas fa-calendar"></i> Date</th>
                    <th><i class="fas fa-money-bill"></i> Method</th>
                    <th><i class="fas fa-cogs"></i> Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $num = 1;
                  $total = 0;
                  while($row = mysqli_fetch_assoc($retrieve)): 
                    $total += $row['payment_amount'];
                  ?>
                  <tr>
                    <td><span class="badge bg-light text-dark"><?php echo $num++; ?></span></td>
                    <td><strong>#<?php echo str_pad($row['payment_number'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo isset($row['first_name']) ? $row['first_name'] . ' ' . $row['surname'] : 'N/A'; ?></td>
                    <td><?php echo $row['account_number']; ?></td>
                    <td><strong><?php echo number_format($row['payment_amount'], 2); ?></strong></td>
                    <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                    <td><span class="badge bg-info"><?php echo $row['payment_method']; ?></span></td>
                    <td>
                        <a href="admin_receipt.php?id=<?php echo $row['payment_number']; ?>" 
                           class="btn btn-sm btn-outline-primary" target="_blank" title="Print Receipt">
                            <i class="fas fa-print"></i>
                        </a>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                  <tr class="table-secondary fw-bold">
                    <td colspan="4" class="text-end">Total</td>
                    <td><?php echo number_format($total, 2); ?></td>
                    <td colspan="3"></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-receipt"></i>
              <h4>No Payments Found</h4>
              <p>No payments match your criteria.</p>
              <a href="addpayment.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Record Payment
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
        
      </main>
    </div>
  </div>

<style>
body.dark-mode {
    background: #0f172a !important;
    color: #e2e8f0 !important;
}
body.dark-mode .container-fluid {
    background: #0f172a !important;
}
body.dark-mode .card {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-color: #334155 !important;
}
body.dark-mode .card-body {
    color: #e2e8f0 !important;
    background: #1e293b !important;
}
body.dark-mode .page-title-section h1,
body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
    color: #f1f5f9 !important;
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
.page-actions {
    animation: slideInRight 0.5s ease-out 0.2s forwards;
    opacity: 0;
}
@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-30px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(30px); }
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
body.dark-mode p, body.dark-mode span, body.dark-mode div, body.dark-mode strong {
    color: #e2e8f0 !important;
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
body.dark-mode .bg-gradient-success,
body.dark-mode .bg-gradient-success .card-body {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
    color: white !important;
}
body.dark-mode .bg-gradient-primary,
body.dark-mode .bg-gradient-primary .card-body {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
    color: white !important;
}
body.dark-mode .bg-gradient-warning,
body.dark-mode .bg-gradient-warning .card-body {
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
    color: white !important;
}
body.dark-mode .bg-gradient-info,
body.dark-mode .bg-gradient-info .card-body {
    background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%) !important;
    color: white !important;
}
body.dark-mode .bg-gradient-success h6,
body.dark-mode .bg-gradient-success h3,
body.dark-mode .bg-gradient-primary h6,
body.dark-mode .bg-gradient-primary h3,
body.dark-mode .bg-gradient-warning h6,
body.dark-mode .bg-gradient-warning h3,
body.dark-mode .bg-gradient-info h6,
body.dark-mode .bg-gradient-info h3,
body.dark-mode .bg-gradient-success i,
body.dark-mode .bg-gradient-primary i,
body.dark-mode .bg-gradient-warning i,
body.dark-mode .bg-gradient-info i {
    color: white !important;
}
body.dark-mode .empty-state {
    color: #94a3b8 !important;
}
body.dark-mode .input-group-text {
    color: #94a3b8 !important;
}
</style>

</body>
<?php require_once 'include/footer.php'; ?>
