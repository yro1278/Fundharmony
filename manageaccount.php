<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$check_closed = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Closed'");
if (mysqli_num_rows($check_closed) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (-3, 'Closed')");
}

$check_partial = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Partial'");
if (mysqli_num_rows($check_partial) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (5, 'Partial')");
}

$check_up_to_date = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Up to Date'");
if (mysqli_num_rows($check_up_to_date) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (7, 'Up to Date')");
}

$check_due_date = mysqli_query($conn, "SELECT account_status_number FROM account_status WHERE account_status_name = 'Due Date'");
if (mysqli_num_rows($check_due_date) == 0) {
    mysqli_query($conn, "INSERT INTO account_status (account_status_number, account_status_name) VALUES (6, 'Due Date')");
}

$active_accounts = mysqli_query($conn, "SELECT account_number, loan_amount, interest, loan_term, open_date, account_status, loan_balance FROM accounts WHERE account_status IN (-2, 5, 6, 7, 1)");
while ($acc = mysqli_fetch_assoc($active_accounts)) {
    $loan_amount = floatval($acc['loan_amount'] ?? 0);
    $interest = floatval($acc['interest'] ?? 0);
    $loan_term = intval($acc['loan_term'] ?? 1);
    $open_date = $acc['open_date'] ?? date('Y-m-d');
    
    $total_due = $loan_amount + $interest;
    $monthly_payment = $loan_term > 0 ? $total_due / $loan_term : $total_due;
    
    $start_date = new DateTime($open_date);
    $current_date = new DateTime();
    $months_passed = (($current_date->format('Y') - $start_date->format('Y')) * 12) + ($current_date->format('n') - $start_date->format('n'));
    if ($months_passed < 0) $months_passed = 0;
    
    $next_monthly_due = clone $start_date;
    $next_monthly_due->modify('+' . ($months_passed + 1) . ' months');
    $days_until_due = (strtotime($next_monthly_due->format('Y-m-d')) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
    
    $total_paid_result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(payment_amount) as total_paid FROM payments WHERE account_number = '".$acc['account_number']."'"));
    $total_paid = floatval($total_paid_result['total_paid'] ?? 0);
    
    $expected_payment_for_month = $monthly_payment * ($months_passed + 1);
    
    $new_status = 6;
    
    if ($total_paid >= $expected_payment_for_month || $days_until_due > 21) {
        $new_status = 7;
    } elseif ($days_until_due <= 7 || $days_until_due < 0) {
        $new_status = 6;
    } elseif ($total_paid > 0 && $total_paid < $expected_payment_for_month) {
        $new_status = 5;
    } else {
        $new_status = 6;
    }
    
    if ($acc['account_status'] != $new_status) {
        mysqli_query($conn, "UPDATE accounts SET account_status = $new_status WHERE account_number = '".$acc['account_number']."'");
    }
}

mysqli_query($conn, "UPDATE accounts SET account_status = -3 WHERE loan_balance <= 0 AND account_status IN (-2, 5, 6, 7, 1)");

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';

$where = "a.user_id IS NOT NULL AND a.account_status NOT IN (0, 3, 4)";
if($search) {
    $where .= " AND (c.first_name LIKE '%$search%' OR c.surname LIKE '%$search%' OR c.phone LIKE '%$search%' OR c.email LIKE '%$search%' OR a.account_number LIKE '%$search%')";
}
if($status_filter !== '') {
    if($status_filter == 'partial' || $status_filter == 5) {
        $where .= " AND a.account_status = 5";
    } elseif($status_filter == 'fullpaid' || $status_filter == -3) {
        $where .= " AND a.account_status = -3";
    } elseif($status_filter == 'up_to_date' || $status_filter == 7) {
        $where .= " AND a.account_status = 7";
    } elseif($status_filter == 'due_date' || $status_filter == 6) {
        $where .= " AND a.account_status = 6";
    } else {
        $where .= " AND acs.account_status_number = '$status_filter'";
    }
}

$retrieve = mysqli_query($conn, 
"SELECT 
a.account_number, 
a.customer AS customer_id,
a.open_date,
a.loan_amount,
a.loan_term,
a.loan_balance,
a.interest,
a.overdue_interest,
a.due_date,
c.first_name AS account_name,
c.middle_name AS middle_account_name,
c.surname AS last_account_name,
c.email AS customer_email,
c.phone AS customer_phone,
ct.customer_type_name AS customer_type,
act.account_type_name AS account_type,
acs.account_status_name AS account_status,
acs.account_status_number AS status_number,
(SELECT SUM(payment_amount) FROM payments WHERE account_number = a.account_number) as total_paid,
(SELECT MAX(payment_date) FROM payments WHERE account_number = a.account_number) as last_payment_date
FROM accounts a
INNER JOIN customers c ON a.customer = c.customer_number
LEFT JOIN customers_type ct ON c.customer_type = ct.customer_type_number
LEFT JOIN account_type act ON a.account_type = act.account_type_number
LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
WHERE $where
ORDER BY a.open_date DESC;
");

$total = mysqli_num_rows($retrieve);
?>

<body>
  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="fas fa-list text-white" style="font-size: 22px;"></i>
            </div>
            <h1 class="mb-0">Manage Loan Accounts</h1>
          </div>
          <div class="page-actions">
            <a href="openaccount.php" class="btn btn-primary">
              <i class="fas fa-plus"></i> New Account
            </a>
            <a href="pdfaccount.php" class="btn btn-outline-primary" target="_blank">
              <i class="fas fa-file-pdf"></i> Export PDF
            </a>
          </div>
        </div>
 
        <!-- Search & Filter -->
        <div class="card mb-4">
          <div class="card-body">
            <form method="GET" action="" class="row g-3">
              <div class="col-md-5">
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                  <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search by name, account number, phone, or email..." value="<?php echo $search; ?>">
                </div>
              </div>
              <div class="col-md-4">
                <select name="status" class="form-select">
                  <option value="">All Status</option>
                  <?php
                  $all_statuses = mysqli_query($conn, "SELECT * FROM account_status WHERE account_status_name IN ('Up to Date', 'Due Date') ORDER BY account_status_name");
                  while($s = mysqli_fetch_assoc($all_statuses)):
                      $selected = '';
                      if($status_filter == $s['account_status_number'] || $status_filter == strtolower(str_replace(' ', '_', $s['account_status_name']))) {
                          $selected = 'selected';
                      }
                  ?>
                  <option value="<?php echo $s['account_status_number']; ?>" <?php echo $selected; ?>>
                      <?php echo $s['account_status_name']; ?>
                  </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-search"></i> Filter
                </button>
              </div>
            </form>
          </div>
        </div>

        <?php if($total > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th><i class="fas fa-hashtag"></i> SN</th>
                <th><i class="fas fa-id-card"></i> Account No.</th>
                <th><i class="fas fa-user"></i> Client Name</th>
                <th><i class="fas fa-money-check"></i> Loan Type</th>
                <th><i class="fas fa-clock"></i> Term</th>
                <th><i class="fas fa-peso-sign"></i> Loan Amount</th>
                <th><i class="fas fa-calendar-alt"></i> Due Date</th>
                <th><i class="fas fa-money-bill"></i> Monthly</th>
                <th><i class="fas fa-toggle-on"></i> Status</th>
                <th><i class="fas fa-bell"></i> Notification</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $num = 1;
              while ($result = mysqli_fetch_assoc($retrieve)):
                $fullname = $result['account_name'] . ' ' . ($result['middle_account_name'] ? $result['middle_account_name'] . ' ' : '') . $result['last_account_name'];
                $loan_amount = floatval($result['loan_amount'] ?? 0);
                $interest = floatval($result['interest'] ?? 0);
                $original_total = $loan_amount + $interest;
                $balance = floatval($result['loan_balance'] ?? $original_total);
                $overdue = floatval($result['overdue_interest'] ?? 0);
                $total_due = $original_total + $overdue;
                $loan_term = intval($result['loan_term'] ?? 1);
                $open_date = $result['open_date'];
                $is_paid = $balance <= 0;
                $customer_email = $result['customer_email'] ?? '';
                $customer_phone = $result['customer_phone'] ?? '';
                
                $monthly_payment = 0;
                if($loan_term > 0 && $original_total > 0) {
                    $monthly_payment = $original_total / $loan_term;
                }
                
                $total_paid = floatval($result['total_paid'] ?? 0);
                $last_payment_date = $result['last_payment_date'] ?? null;
                $remaining_balance = $total_due - $total_paid;
                
                $monthly_due_date = null;
                $monthly_due_info = '';
                $payment_status = '';
                if($open_date && !$is_paid && $loan_term > 0) {
                    $start_date = new DateTime($open_date);
                    $current_date = new DateTime();
                    $months_passed = (($current_date->format('Y') - $start_date->format('Y')) * 12) + ($current_date->format('n') - $start_date->format('n'));
                    
                    $next_monthly_due = clone $start_date;
                    $next_monthly_due->modify('+' . ($months_passed + 1) . ' months');
                    
                    if($months_passed < $loan_term) {
                        $monthly_due_date = $next_monthly_due->format('Y-m-d');
                        $days_until = (strtotime($monthly_due_date) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                        
                        if($days_until < 0) {
                            $monthly_due_info = '<br><small class="text-danger">(Overdue)</small>';
                            $payment_status = 'text-danger';
                        } elseif($days_until <= 7) {
                            $monthly_due_info = '<br><small class="text-warning">(Due in ' . floor($days_until) . ' days)</small>';
                            $payment_status = 'text-warning';
                        } else {
                            $monthly_due_info = '<br><small class="text-muted">(' . floor($days_until) . ' days)</small>';
                        }
                    }
                }
              ?>
              <tr>
                <td><span class="badge bg-light text-dark"><?php echo $num++; ?></span></td>
                <td><strong><?php echo $result["account_number"]; ?></strong></td>
                <td><?php echo $fullname; ?></td>
                <td><?php echo $result["account_type"]; ?></td>
                <td><?php echo $result["loan_term"] ?? 1; ?> month(s)</td>
                <td>
                  ₱<?php echo number_format($loan_amount, 2); ?>
                  <?php if(!$is_paid): ?>
                    <br><small class="text-danger">Total: ₱<?php echo number_format($total_due, 2); ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($monthly_due_date && !$is_paid): ?>
                    <span class="<?php echo $payment_status; ?>"><?php echo date('M d, Y', strtotime($monthly_due_date)); ?></span>
                    <?php echo $monthly_due_info; ?>
                  <?php elseif($is_paid): ?>
                    <span class="text-success">Paid</span>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($is_paid): ?>
                    <span class="text-muted">-</span>
                  <?php else: ?>
                    ₱<?php echo number_format($monthly_payment, 2); ?>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($result["status_number"] == 0): ?>
                    <span class="badge bg-secondary">Declined</span>
                  <?php elseif($result["status_number"] == 3): ?>
                    <span class="badge bg-danger">Rejected</span>
                  <?php elseif($result["status_number"] == 4): ?>
                    <span class="badge bg-warning">Pending</span>
                  <?php elseif($result["status_number"] == -3): ?>
                    <span class="badge bg-success">Full Paid</span>
                  <?php elseif($result["status_number"] == 5): ?>
                    <span class="badge bg-success">Active</span>
                    <br><small class="text-muted">Partial Payment</small>
                  <?php elseif($result["status_number"] == 7): ?>
                    <span class="badge bg-success">Up to Date</span>
                  <?php elseif($result["status_number"] == 6): ?>
                    <?php if($payment_status == 'text-danger'): ?>
                      <span class="badge bg-danger">Due Date</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Due Date</span>
                    <?php endif; ?>
                  <?php elseif($is_paid): ?>
                    <span class="badge bg-success">Full Paid</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?php echo $result["account_status"]; ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if(!$is_paid): ?>
                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning" title="Send Reminder" onclick="sendReminder('<?php echo $result['account_number']; ?>', '<?php echo addslashes($fullname); ?>', '<?php echo $customer_phone; ?>', '<?php echo $customer_email; ?>', '<?php echo $monthly_due_date; ?>', '<?php echo number_format($monthly_payment, 2); ?>', '<?php echo number_format($remaining_balance, 2); ?>', '<?php echo $result['customer_id']; ?>')">
                      <i class="fas fa-bell"></i>
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php if($total_paid > 0): ?>
              <tr class="table-success">
                <td colspan="10" class="border-0 py-0">
                  <small>
                    <a href="javascript:void(0)" class="text-success text-decoration-none payment-history-toggle" onclick="togglePaymentHistory('<?php echo $result['account_number']; ?>')">
                      <i class="fas fa-check-circle me-1"></i> Payment History: ₱<?php echo number_format($total_paid, 2); ?> total paid
                      <i class="fas fa-chevron-down ms-1" id="icon-<?php echo $result['account_number']; ?>" style="font-size: 10px; transition: transform 0.3s ease;"></i>
                    </a>
                  </small>
                  <div id="paymentHistory<?php echo $result['account_number']; ?>" class="payment-history-content" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease, opacity 0.4s ease, padding 0.4s ease; opacity: 0;">
                    <div class="card card-body py-2 mt-2" style="background: #f8f9fa;">
                      <small>
                        <?php 
                        $account_payments = mysqli_query($conn, "SELECT payment_amount, payment_date, payment_method FROM payments WHERE account_number = '".$result['account_number']."' ORDER BY payment_date DESC");
                        while($pay = mysqli_fetch_assoc($account_payments)):
                        ?>
                        <div class="d-flex justify-content-between border-bottom py-1">
                          <span><i class="fas fa-calendar-alt me-1 text-muted"></i> <?php echo date('M d, Y', strtotime($pay['payment_date'])); ?></span>
                          <span class="text-success">₱<?php echo number_format($pay['payment_amount'], 2); ?></span>
                          <span class="text-muted"><?php echo $pay['payment_method'] ?? 'Payment'; ?></span>
                        </div>
                        <?php endwhile; ?>
                        <div class="d-flex justify-content-between pt-1">
                          <strong>Remaining Balance:</strong>
                          <strong class="text-danger">₱<?php echo number_format($remaining_balance, 2); ?></strong>
                        </div>
                      </small>
                    </div>
                  </div>
                </td>
              </tr>
              <?php endif; ?>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        
        <script>
        // Real-time search with animation
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            var searchValue = e.target.value.toLowerCase();
            var tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(function(row, index) {
                var text = row.textContent.toLowerCase();
                if (text.indexOf(searchValue) !== -1) {
                    row.style.display = '';
                    row.style.opacity = '0';
                    row.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        row.style.opacity = '1';
                        row.style.transform = 'translateY(0)';
                    }, index * 30);
                } else {
                    row.style.opacity = '0';
                    row.style.transform = 'translateY(10px)';
                    setTimeout(function() {
                        row.style.display = 'none';
                    }, 300);
                }
            });
        });

        function togglePaymentHistory(accountNumber) {
          var element = document.getElementById('paymentHistory' + accountNumber);
          var icon = document.getElementById('icon-' + accountNumber);
          if (element.classList.contains('expanded')) {
            element.classList.remove('expanded');
            element.style.maxHeight = '0';
            element.style.opacity = '0';
            element.style.padding = '0';
            icon.style.transform = 'rotate(0deg)';
          } else {
            element.classList.add('expanded');
            element.style.maxHeight = '500px';
            element.style.opacity = '1';
            element.style.padding = '10px 0';
            icon.style.transform = 'rotate(180deg)';
          }
        }
        
        function sendReminder(accountNumber, name, phone, email, dueDate, monthlyPayment, remainingBalance, customerId) {
          var message = 'This is a friendly reminder that your loan payment is due.\n\nAccount: ' + accountNumber + '\nDue Date: ' + dueDate + '\nMonthly Payment: ₱' + monthlyPayment + '\nRemaining Balance: ₱' + remainingBalance + '\n\nPlease make your payment on time to avoid penalties.\n\nThank you!';
          
          var options = [];
          if (phone) options.push('SMS to: ' + phone);
          if (email) options.push('Email to: ' + email);
          
          var displayMsg = 'Send reminder notification to customer.\n\n';
          if (options.length > 0) {
            displayMsg += 'Via: ' + options.join(', ') + '\n\n';
          } else {
            displayMsg += '(No contact info - will show in customer dashboard only)\n\n';
          }
          displayMsg += 'Message:\n' + message;
          
          var choice = confirm(displayMsg + '\n\nClick OK to send, Cancel to abort.');
          if (choice) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'app/save_notification.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
              if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                  alert('Reminder sent successfully! The customer will see it in their dashboard.');
                } else {
                  alert('Error: ' + response.error);
                }
              }
            };
            xhr.send('customer_id=' + customerId + '&account_number=' + accountNumber + '&message=' + encodeURIComponent(message));
          }
        }
        // Animate rows on page load (for filter results)
        document.addEventListener('DOMContentLoaded', function() {
            var tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(function(row, index) {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                row.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                setTimeout(function() {
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
        </script>
        
        <div class="mt-3 text-muted">
          <i class="fas fa-info-circle"></i> Showing <?php echo $total; ?> account(s)
        </div>
        
        <?php else: ?>
        <div class="card">
          <div class="card-body empty-state">
            <i class="fas fa-folder-open"></i>
            <h4>No Accounts Found</h4>
            <p>No accounts match your search criteria.</p>
            <a href="openaccount.php" class="btn btn-primary">
              <i class="fas fa-plus"></i> Create New Account
            </a>
          </div>
        </div>
        <?php endif; ?>
        
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
body.dark-mode .btn-outline-primary,
body.dark-mode .btn-outline-danger,
body.dark-mode .btn-outline-info {
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .btn-outline-primary:hover,
body.dark-mode .btn-outline-danger:hover,
body.dark-mode .btn-outline-info:hover {
    background: #334155 !important;
}
body.dark-mode .empty-state {
    color: #94a3b8 !important;
}
body.dark-mode .badge {
    color: white !important;
}
body.dark-mode .input-group-text {
    color: #94a3b8 !important;
}
body.dark-mode .payment-history-toggle:hover {
    color: #6ee7b7 !important;
}
body.dark-mode .payment-history-content .card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
</style>

</body>
<?php require_once 'include/footer.php'; ?>
