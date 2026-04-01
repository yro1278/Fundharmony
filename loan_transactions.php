<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$today = date('Y-m-d');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) && $_GET['type'] !== '' ? $_GET['type'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$active_statuses = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name IN ('Active', 'Partial', 'Up to Date', 'Due Date')");
$active_ids = [];
while ($s = mysqli_fetch_assoc($active_statuses)) {
    $active_ids[] = $s['account_status_number'];
}
$active_ids_str = implode(',', $active_ids);

$closed_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Closed'"));
$closed_id = $closed_status['account_status_number'] ?? -3;

$rejected_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Rejected'"));
$rejected_id = $rejected_status['account_status_number'] ?? 3;

$declined_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Declined'"));
$declined_id = $declined_status['account_status_number'] ?? -1;

$where = "a.account_number IS NOT NULL";
if ($search) {
    $where .= " AND (c.first_name LIKE '%$search%' OR c.surname LIKE '%$search%' OR c.middle_name LIKE '%$search%' OR a.account_number LIKE '%$search%')";
}
if ($status_filter !== '') {
    if ($status_filter == 'active') {
        $where .= " AND a.account_status IN ($active_ids_str)";
    } elseif ($status_filter == 'fully_paid') {
        $where .= " AND a.account_status = '$closed_id'";
    } elseif ($status_filter == 'pending') {
        $where .= " AND a.account_status = 4";
    } elseif ($status_filter == 'approved') {
        $where .= " AND a.account_status = 1";
    } elseif ($status_filter == 'overdue') {
        $where .= " AND a.due_date < '$today' AND a.loan_balance > 0 AND a.account_status NOT IN ($closed_id, $rejected_id)";
    } elseif ($status_filter == 'rejected') {
        $where .= " AND a.account_status = '$rejected_id'";
    } elseif ($status_filter == 'declined') {
        $where .= " AND a.account_status = '$declined_id'";
    }
}
if ($type_filter !== '') {
    $where .= " AND a.account_type = '$type_filter'";
}
if ($date_from !== '') {
    $where .= " AND a.open_date >= '$date_from'";
}
if ($date_to !== '') {
    $where .= " AND a.open_date <= '$date_to'";
}

$loans = mysqli_query($conn, "
    SELECT 
        a.*,
        c.first_name,
        c.middle_name,
        c.surname,
        act.account_type_name,
        acs.account_status_name,
        (SELECT SUM(payment_amount) FROM payments WHERE account_number = a.account_number) as total_paid
    FROM accounts a
    INNER JOIN customers c ON a.customer = c.customer_number
    LEFT JOIN account_type act ON a.account_type = act.account_type_number
    LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
    WHERE $where
    ORDER BY a.open_date DESC
");

$loan_types = mysqli_query($conn, "SELECT * FROM account_type ORDER BY account_type_name");
$statuses = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_name");

$total_loans = mysqli_num_rows($loans);
$total_amount = 0;
$total_outstanding = 0;
mysqli_data_seek($loans, 0);
while ($row = mysqli_fetch_assoc($loans)) {
    $total_amount += floatval($row['loan_amount'] ?? 0);
    $total_outstanding += floatval($row['loan_balance'] ?? 0);
}
mysqli_data_seek($loans, 0);
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
              <i class="fas fa-file-invoice-dollar text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-1">Loan Transactions Report</h1>
              <p class="text-muted mb-0">Complete list of all loan records in the system</p>
            </div>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card summary-card" style="border-left: 4px solid #4f46e5;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Loans</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;"><?php echo $total_loans; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card summary-card" style="border-left: 4px solid #10b981;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Loan Amount</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_amount, 2); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card summary-card" style="border-left: 4px solid #f59e0b;">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.75rem;">Total Outstanding</h6>
                        <h4 class="mb-0" style="font-size: 1.1rem;">₱<?php echo number_format($total_outstanding, 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or loan ID..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="fully_paid" <?php echo $status_filter == 'fully_paid' ? 'selected' : ''; ?>>Fully Paid</option>
                            <option value="overdue" <?php echo $status_filter == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="declined" <?php echo $status_filter == 'declined' ? 'selected' : ''; ?>>Declined</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <?php while($type = mysqli_fetch_assoc($loan_types)): ?>
                            <option value="<?php echo $type['account_type_number']; ?>" <?php echo $type_filter == $type['account_type_number'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['account_type_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To Date" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loan Status Legend -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <span class="me-3"><strong>Status Legend:</strong></span>
                <span class="badge bg-warning text-dark me-1">Pending</span>
                <span class="badge bg-secondary me-1">Approved</span>
                <span class="badge bg-success me-1">Active</span>
                <span class="badge bg-info me-1">Partial</span>
                <span class="badge bg-primary me-1">Closed</span>
                <span class="badge bg-danger me-1">Rejected/Declined</span>
                <span class="badge bg-warning text-dark me-1">Due Date</span>
                <span class="badge bg-success me-1">Up to Date</span>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Loan Transactions</h5>
                    <a href="excel_loans.php" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-2 py-2">Loan ID</th>
                                <th class="px-2 py-2">Borrower Name</th>
                                <th class="px-2 py-2">Loan Type</th>
                                <th class="px-2 py-2 text-end">Loan Amount</th>
                                <th class="px-2 py-2 text-center">Term</th>
                                <th class="px-2 py-2 text-end">Interest</th>
                                <th class="px-2 py-2">Application Date</th>
                                <th class="px-2 py-2">Approval Date</th>
                                <th class="px-2 py-2">Release Date</th>
                                <th class="px-2 py-2">Due Date</th>
                                <th class="px-2 py-2 text-end">Balance</th>
                                <th class="px-2 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($loans) > 0): ?>
                                <?php while($loan = mysqli_fetch_assoc($loans)): ?>
                                <tr>
                                    <td class="px-2 py-2">#<?php echo $loan['account_number']; ?></td>
                                    <td class="px-2 py-2">
                                        <?php 
                                        $name = trim(($loan['first_name'] ?? '') . ' ' . ($loan['middle_name'] ? $loan['middle_name'] . ' ' : '') . ($loan['surname'] ?? ''));
                                        echo htmlspecialchars($name);
                                        ?>
                                    </td>
                                    <td class="px-2 py-2"><?php echo htmlspecialchars($loan['account_type_name'] ?? 'N/A'); ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($loan['loan_amount'], 2); ?></td>
                                    <td class="px-2 py-2 text-center"><?php echo $loan['loan_term']; ?> mo</td>
                                    <td class="px-2 py-2 text-end"><?php echo number_format($loan['interest'], 2); ?>%</td>
                                    <td class="px-2 py-2"><?php echo $loan['open_date'] ? date('M d, Y', strtotime($loan['open_date'])) : '-'; ?></td>
                                    <td class="px-2 py-2"><?php echo $loan['approval_date'] ? date('M d, Y', strtotime($loan['approval_date'])) : '-'; ?></td>
                                    <td class="px-2 py-2"><?php echo $loan['release_date'] ? date('M d, Y', strtotime($loan['release_date'])) : '-'; ?></td>
                                    <td class="px-2 py-2"><?php echo $loan['due_date'] ? date('M d, Y', strtotime($loan['due_date'])) : '-'; ?></td>
                                    <td class="px-2 py-2 text-end">₱<?php echo number_format($loan['loan_balance'], 2); ?></td>
                                    <td class="px-2 py-2 text-center">
                                        <?php 
                                        $status = $loan['account_status_name'];
                                        $badge_class = '';
                                        if($status == 'Pending') $badge_class = 'bg-warning text-dark';
                                        elseif($status == 'Approved') $badge_class = 'bg-secondary';
                                        elseif($status == 'Active') $badge_class = 'bg-success';
                                        elseif($status == 'Closed' || $status == 'Fully Paid') $badge_class = 'bg-primary';
                                        elseif($status == 'Declined') $badge_class = 'bg-secondary';
                                        elseif($status == 'Rejected') $badge_class = 'bg-danger';
                                        elseif($status == 'Partial') $badge_class = 'bg-info';
                                        elseif($status == 'Due Date') $badge_class = 'bg-warning text-dark';
                                        elseif($status == 'Up to Date') $badge_class = 'bg-success';
                                        else $badge_class = 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $status ?? 'N/A'; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center py-4 text-muted">No loan records found.</td>
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
  
  <style>
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .animate-fade-in-up {
      animation: fadeInUp 0.5s ease-out forwards;
    }
    .animate-fade-in {
      animation: fadeIn 0.4s ease-out forwards;
    }
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
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
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
      
      const tableRows = document.querySelectorAll('.table tbody tr');
      tableRows.forEach((row, index) => {
        row.style.animationDelay = (index * 0.03) + 's';
      });
    });
  </script>
</body>
</html>
