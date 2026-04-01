<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$today = date('Y-m-d');

$total_released = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(loan_amount), 0) as total FROM accounts WHERE account_status NOT IN (0, 4)"
));
$total_loan_released = $total_released['total'] ?? 0;

$active_statuses = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name IN ('Active', 'Partial', 'Up to Date', 'Due Date')");
$active_ids = [];
while ($s = mysqli_fetch_assoc($active_statuses)) {
    $active_ids[] = $s['account_status_number'];
}
$active_ids_str = implode(',', $active_ids);

$active_loans = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts WHERE account_status IN ($active_ids_str)"
));
$count_active_loans = $active_loans['count'] ?? 0;

$closed_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Closed'"));
$closed_id = $closed_status['account_status_number'] ?? -3;
$fully_paid = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts WHERE account_status = '$closed_id'"
));
$count_fully_paid = $fully_paid['count'] ?? 0;

$overdue = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts WHERE due_date < '$today' AND loan_balance > 0 AND account_status NOT IN ($closed_id, 3)"
));
$count_overdue = $overdue['count'] ?? 0;

$rejected_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Rejected'"));
$rejected_id = $rejected_status['account_status_number'] ?? 3;
$rejected = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts WHERE account_status = '$rejected_id'"
));
$count_rejected = $rejected['count'] ?? 0;

$declined_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Declined'"));
$declined_id = $declined_status['account_status_number'] ?? -1;
$declined = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM accounts WHERE account_status = '$declined_id'"
));
$count_declined = $declined['count'] ?? 0;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';

$where = "a.account_status IS NOT NULL";
if ($search) {
    $where .= " AND (c.first_name LIKE '%$search%' OR c.surname LIKE '%$search%' OR a.account_number LIKE '%$search%')";
}
if ($status_filter !== '') {
    if ($status_filter == 'active') {
        $where .= " AND a.account_status IN ($active_ids_str)";
    } elseif ($status_filter == 'fully_paid') {
        $where .= " AND a.account_status = '$closed_id'";
    } elseif ($status_filter == 'overdue') {
        $where .= " AND a.due_date < '$today' AND a.loan_balance > 0 AND a.account_status NOT IN ($closed_id, $rejected_id)";
    } elseif ($status_filter == 'rejected') {
        $where .= " AND a.account_status = '$rejected_id'";
    } elseif ($status_filter == 'declined') {
        $where .= " AND a.account_status = '$declined_id'";
    }
}

$loans = mysqli_query($conn, "
    SELECT 
        a.account_number,
        a.loan_amount,
        a.interest,
        a.open_date,
        a.due_date,
        CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.surname) as customer_name,
        act.account_type_name,
        acs.account_status_name
    FROM accounts a
    INNER JOIN customers c ON a.customer = c.customer_number
    LEFT JOIN account_type act ON a.account_type = act.account_type_number
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE $where
    ORDER BY a.open_date DESC
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
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chart-pie text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-0">Loan Reports</h1>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #4f46e5;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Loan Released</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_loan_released, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #28a745;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Active Loans</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_active_loans; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #007bff;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Fully Paid</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_fully_paid; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #dc3545;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Overdue</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_overdue; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #8B0000;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Rejected</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_rejected; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card summary-card" style="border-left: 4px solid #6c757d;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Declined</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $count_declined; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or loan ID..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="fully_paid" <?php echo $status_filter == 'fully_paid' ? 'selected' : ''; ?>>Fully Paid</option>
                            <option value="overdue" <?php echo $status_filter == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="declined" <?php echo $status_filter == 'declined' ? 'selected' : ''; ?>>Declined</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="loan_reports.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Loan Details</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-2">Loan ID</th>
                                <th class="px-3 py-2">Customer Name</th>
                                <th class="px-3 py-2">Loan Type</th>
                                <th class="px-3 py-2 text-end">Amount</th>
                                <th class="px-3 py-2 text-end">Interest Rate</th>
                                <th class="px-3 py-2">Date Released</th>
                                <th class="px-3 py-2">Due Date</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($loans) > 0): ?>
                                <?php while($loan = mysqli_fetch_assoc($loans)): ?>
                                <tr>
                                    <td class="px-3 py-2">#<?php echo $loan['account_number']; ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars(trim($loan['customer_name'])); ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($loan['account_type_name'] ?? 'N/A'); ?></td>
                                    <td class="px-3 py-2 text-end">₱<?php echo number_format($loan['loan_amount'], 2); ?></td>
                                    <td class="px-3 py-2 text-end"><?php echo number_format($loan['interest'], 2); ?>%</td>
                                    <td class="px-3 py-2"><?php echo $loan['open_date'] ? date('M d, Y', strtotime($loan['open_date'])) : 'N/A'; ?></td>
                                    <td class="px-3 py-2"><?php echo $loan['due_date'] ? date('M d, Y', strtotime($loan['due_date'])) : 'N/A'; ?></td>
                                    <td class="px-3 py-2">
                                        <?php 
                                        $status = $loan['account_status_name'];
                                        $badge_class = '';
                                        if($status == 'Pending') $badge_class = 'bg-warning text-dark';
                                        elseif($status == 'Active') $badge_class = 'bg-success';
                                        elseif($status == 'Closed' || $status == 'Fully Paid') $badge_class = 'bg-primary';
                                        elseif($status == 'Declined') $badge_class = 'bg-secondary';
                                        elseif($status == 'Rejected') $badge_class = 'bg-danger';
                                        elseif($status == 'Partial') $badge_class = 'bg-info';
                                        elseif($status == 'Due Date' || $status == 'Up to Date') $badge_class = 'bg-success';
                                        else $badge_class = 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $status ?? 'N/A'; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No loan records found.</td>
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
</body>
</html>
