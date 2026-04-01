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
mysqli_query($conn, "UPDATE accounts SET account_status = -3 WHERE loan_balance <= 0 AND account_status = -2");

$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';

$where = "c.user_id IS NOT NULL";
if($status_filter !== '') {
    if($status_filter == 'fullpaid') {
        $where .= " AND a.account_status = -3";
    } else {
        $where .= " AND acs.account_status_number = '$status_filter'";
    }
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
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-check-circle text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-0">Loan Approvals</h1>
            </div>
          </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="fullpaid" <?php echo $status_filter == 'fullpaid' ? 'selected' : ''; ?>>Closed (Fully Paid)</option>
                            <?php
                            $statuses = mysqli_query($conn, "SELECT * FROM account_status WHERE account_status_name NOT IN ('Closed', 'Up to Date', 'Partial', 'Due Date', 'Approved')");
                            while($s = mysqli_fetch_assoc($statuses)):
                            ?>
                            <option value="<?php echo $s['account_status_number']; ?>" <?php echo $status_filter == $s['account_status_number'] ? 'selected' : ''; ?>>
                                <?php echo $s['account_status_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="loan_approvals.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <?php
        $pending_loans = mysqli_query($conn, "
            SELECT a.*, c.first_name, c.surname, c.email, c.phone, c.user_id, act.account_type_name, acs.account_status_name 
            FROM accounts a
            INNER JOIN customers c ON a.customer = c.customer_number
            LEFT JOIN account_type act ON a.account_type = act.account_type_number
            LEFT JOIN account_status acs ON a.account_status = acs.account_status_number
            WHERE $where
            ORDER BY a.open_date DESC
        ");
        ?>

        <?php if(mysqli_num_rows($pending_loans) > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php while($loan = mysqli_fetch_assoc($pending_loans)): ?>
            <div class="col">
                <div class="card loan-card h-100" style="font-size: 0.85rem;">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Account #<?php echo $loan['account_number']; ?></span>
                            <?php $display_status = in_array($loan['account_status_name'], ['Active', 'Partial']) ? 'Active' : $loan['account_status_name']; ?>
                            <span class="badge" style="<?php
                                if($loan['account_status_name'] == 'Pending') echo 'background-color: #fd7e14; color: white;';
                                elseif($loan['account_status_name'] == 'Active') echo 'background-color: #28a745; color: white;';
                                elseif($loan['account_status_name'] == 'Closed' || $loan['account_status_name'] == 'Approved' || $loan['account_status_name'] == 'Fully Paid') echo 'background-color: #007bff; color: white;';
                                elseif($loan['account_status_name'] == 'Declined') echo 'background-color: #dc3545; color: white;';
                                elseif($loan['account_status_name'] == 'Rejected') echo 'background-color: #8B0000; color: white;';
                                elseif($loan['account_status_name'] == 'Partial') echo 'background-color: #20c997; color: white;';
                                else echo 'background-color: #fd7e14; color: white;';
                            ?>"><?php echo $display_status; ?></span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Step Tabs -->
                        <div class="loan-steps d-flex border-bottom">
                            <button type="button" class="btn btn-sm flex-fill loan-step-btn active" data-step="1" onclick="showLoanStep(<?php echo $loan['account_number']; ?>, 1, this)">
                                <i class="fas fa-user"></i>
                            </button>
                            <button type="button" class="btn btn-sm flex-fill loan-step-btn" data-step="2" onclick="showLoanStep(<?php echo $loan['account_number']; ?>, 2, this)">
                                <i class="fas fa-university"></i>
                            </button>
                            <button type="button" class="btn btn-sm flex-fill loan-step-btn" data-step="3" onclick="showLoanStep(<?php echo $loan['account_number']; ?>, 3, this)">
                                <i class="fas fa-file-alt"></i>
                            </button>
                        </div>
                        
                        <!-- Step 1: Customer Details -->
                        <div id="loan-step-1-<?php echo $loan['account_number']; ?>" class="loan-step-content">
                            <?php if($loan['account_status_name'] == 'Declined'): ?>
                            <div class="alert alert-secondary m-2 p-2">
                                <h6 class="mb-1"><i class="fas fa-user-times me-1"></i>Declined by Customer</h6>
                                <small>The customer has declined this loan after approval.</small>
                            </div>
                            <?php endif; ?>
                            <?php if($loan['account_status_name'] == 'Rejected' && !empty($loan['reject_notes'])): ?>
                            <div class="alert alert-danger m-2 p-2">
                                <h6 class="mb-1"><i class="fas fa-times-circle me-1"></i>Rejected</h6>
                                <small><?php echo htmlspecialchars($loan['reject_notes']); ?></small>
                            </div>
                            <?php endif; ?>
                            <div class="detail-row">
                                <span class="detail-label">Customer</span>
                                <span class="detail-value"><?php echo $loan['first_name'] . ' ' . $loan['surname']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo $loan['email']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone</span>
                                <span class="detail-value"><?php echo $loan['phone']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Loan Type</span>
                                <span class="detail-value"><?php echo $loan['account_type_name']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Amount</span>
                                <span class="detail-value text-success fw-bold">₱<?php echo number_format($loan['loan_amount'], 2); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Applied</span>
                                <span class="detail-value"><?php echo date('M d, Y', strtotime($loan['open_date'])); ?></span>
                            </div>
                        </div>
                        
                        <!-- Step 2: Disbursement -->
                        <div id="loan-step-2-<?php echo $loan['account_number']; ?>" class="loan-step-content" style="display: none;">
                            <div class="detail-row">
                                <span class="detail-label">Method</span>
                                <span class="detail-value"><?php echo $loan['disbursement_method']; ?></span>
                            </div>
                            <?php if($loan['disbursement_method'] == 'Bank Transfer'): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Bank</span>
                                    <span class="detail-value"><?php echo $loan['bank_name']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Account #</span>
                                    <span class="detail-value"><?php echo $loan['disbursement_account']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Account Name</span>
                                    <span class="detail-value"><?php echo $loan['disbursement_account_name']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Branch</span>
                                    <span class="detail-value"><?php echo $loan['branch']; ?></span>
                                </div>
                            <?php elseif($loan['disbursement_method'] == 'E-Wallet'): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Type</span>
                                    <span class="detail-value"><?php echo $loan['ewallet_type']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Number</span>
                                    <span class="detail-value"><?php echo $loan['ewallet_number']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Account Name</span>
                                    <span class="detail-value"><?php echo $loan['ewallet_account_name']; ?></span>
                                </div>
                            <?php elseif($loan['disbursement_method'] == 'Cash'): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Pickup Location</span>
                                    <span class="detail-value"><?php echo $loan['pickup_location']; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Step 3: Requirements -->
                        <div id="loan-step-3-<?php echo $loan['account_number']; ?>" class="loan-step-content" style="display: none;">
                            <?php
                            $requirements = mysqli_query($conn, "SELECT * FROM loan_requirements WHERE account_number = '{$loan['account_number']}'");
                            if(mysqli_num_rows($requirements) > 0): ?>
                                <div class="req-list">
                                <?php while($req = mysqli_fetch_assoc($requirements)): ?>
                                    <div class="req-item">
                                        <div class="req-info">
                                            <span class="req-name"><?php echo $req['requirement_type']; ?></span>
                                        </div>
                                        <div class="req-actions">
                                            <a href="<?php echo $req['file_path']; ?>" target="_blank" class="btn btn-sm btn-info py-0 px-1"><i class="fas fa-eye"></i></a>
                                            <span class="badge <?php echo $req['status'] == 'approved' ? 'bg-success' : ($req['status'] == 'rejected' ? 'bg-danger' : 'bg-warning'); ?> py-1">
                                                <?php echo ucfirst($req['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted text-center p-3">No requirements uploaded</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if($loan['account_status_name'] == 'Pending'): ?>
                    <div class="card-footer d-flex gap-2">
                        <a href="app/approve_loan.php?account_number=<?php echo $loan['account_number']; ?>" class="btn btn-success btn-sm flex-fill approve-btn" onclick="handleApprove(event, this)">
                            <span class="btn-text"><i class="fas fa-check"></i> Approve</span>
                            <span class="btn-loader" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm flex-fill" onclick="openRejectModal(<?php echo $loan['account_number']; ?>)">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                    
                    <!-- Reject Modal -->
                    <div id="rejectModal<?php echo $loan['account_number']; ?>" class="custom-modal reject-modal-<?php echo $loan['account_number']; ?>" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; align-items: center; justify-content: center;">
                        <div class="modal-inner-div" style="background: white; border-radius: 10px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                            <div style="background: #dc3545; color: white; padding: 15px 20px; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">
                                <h5 class="mb-0">Reject Loan</h5>
                                <button type="button" onclick="closeRejectModal(<?php echo $loan['account_number']; ?>)" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">&times;</button>
                            </div>
                            <form action="app/reject_loan.php" method="post" style="padding: 20px;">
                                <p>Are you sure you want to reject this loan?</p>
                                <input type="hidden" name="account_number" value="<?php echo $loan['account_number']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Reason for Rejection (Optional)</label>
                                    <textarea name="reject_notes" class="form-control" rows="3" placeholder="Enter reason for rejection..." style="min-height: 80px;"></textarea>
                                </div>
                                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                    <button type="button" onclick="closeRejectModal(<?php echo $loan['account_number']; ?>)" class="btn btn-secondary">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Reject Loan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h4>No Loan Applications</h4>
                <p>There are no loan applications to review.</p>
            </div>
        </div>
        <?php endif; ?>
        
      </main>
    </div>
  </div>

<style>
/* Status border colors */
.border-orange { border-color: #f97316 !important; }
.border-green { border-color: #22c55e !important; }
.border-blue { border-color: #3b82f6 !important; }
.border-red { border-color: #ef4444 !important; }
.border-dark-red { border-color: #b91c1c !important; }
.border-teal { border-color: #14b8a6 !important; }

.btn-success {
    transition: all 0.3s ease;
}
.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}
.btn-success.approved {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    transform: scale(1.02);
}
.btn-success .btn-loader {
    gap: 8px;
    align-items: center;
}
.btn-danger {
    transition: all 0.3s ease;
}
.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}
.loan-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.loan-card .card-body {
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.loan-card p {
    margin-bottom: 4px;
}
.loan-card hr {
    margin: 8px 0;
}
.loan-card h6 {
    font-size: 0.85rem;
    margin-bottom: 6px;
}
.req-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.req-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px 6px;
    background: #f8f9fa;
    border-radius: 3px;
    border: 1px solid #e9ecef;
}
.req-name {
    font-size: 0.7rem;
    color: #1e293b;
    font-weight: 500;
}
.loan-card p {
    margin-bottom: 4px;
}
.loan-card hr {
    margin: 8px 0;
}
.loan-card h6 {
    font-size: 0.85rem;
    margin-bottom: 6px;
}
.loan-steps {
    background: #f8f9fa;
}
.loan-step-btn {
    border: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #6c757d !important;
    padding: 8px !important;
}
.loan-step-btn.active {
    background: #4f46e5 !important;
    color: white !important;
}
.loan-step-btn:hover:not(.active) {
    background: #e2e8f0 !important;
    color: #4f46e5 !important;
}
/* Status Colors */
.bg-orange { background-color: #f97316 !important; color: white !important; }
.bg-green { background-color: #22c55e !important; color: white !important; }
.bg-blue { background-color: #3b82f6 !important; color: white !important; }
.bg-red { background-color: #ef4444 !important; color: white !important; }
.bg-dark-red { background-color: #b91c1c !important; color: white !important; }
.bg-teal { background-color: #14b8a6 !important; color: white !important; }
.loan-step-content {
    padding: 12px;
    flex: 1;
    overflow-y: visible;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid #f0f0f0;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}
.detail-value {
    font-size: 0.85rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
    word-break: break-word;
}
.req-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.req-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 8px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}
.req-name {
    font-size: 0.7rem;
    color: #1e293b;
    font-weight: 500;
}
.req-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}
.detail-label {
    font-size: 0.75rem;
    color: #1e293b;
    font-weight: 500;
}
.detail-value {
    font-size: 0.8rem;
    color: #0f172a;
    font-weight: 500;
    text-align: right;
}
.loan-step-content p {
    margin-bottom: 4px;
    font-size: 0.8rem;
}
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
body.dark-mode .loan-steps {
    background: #334155 !important;
}
body.dark-mode .loan-step-btn {
    color: #94a3b8 !important;
}
body.dark-mode .loan-step-btn:hover:not(.active) {
    background: #475569 !important;
    color: #818cf8 !important;
}
body.dark-mode .loan-step-content {
    background: #1e293b !important;
    color: #e2e8f0 !important;
}
body.dark-mode .loan-step-content p {
    color: #e2e8f0 !important;
}
body.dark-mode .loan-step-content small {
    color: #94a3b8 !important;
}
body.dark-mode .loan-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .loan-card .card-body {
    background: #1e293b !important;
    color: #ffffff !important;
}
body.dark-mode .loan-step-content {
    background: #1e293b !important;
    color: #ffffff !important;
}
body.dark-mode .detail-row {
    border-bottom-color: #475569 !important;
}
body.dark-mode .detail-label {
    color: #cbd5e1 !important;
    font-weight: 600;
}
body.dark-mode .detail-value {
    color: #ffffff !important;
    font-weight: 600;
}
body.dark-mode .req-name {
    color: #ffffff !important;
    font-weight: 500;
}
body.dark-mode .req-item {
    background: #334155 !important;
    border-color: #475569 !important;
}
body.dark-mode .req-actions .btn-info {
    background: #3b82f6 !important;
    border-color: #3b82f6 !important;
    color: white !important;
}
body.dark-mode .loan-step-content small {
    color: #e2e8f0 !important;
}
body.dark-mode .loan-step-content span {
    color: #ffffff !important;
}
body.dark-mode .alert-secondary {
    background: #334155 !important;
    color: #e2e8f0 !important;
    border-color: #475569 !important;
}
body.dark-mode .card-footer {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .border {
    border-color: #334155 !important;
}
body.dark-mode .form-select,
body.dark-mode .form-control {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .modal-content {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-color: #334155 !important;
}
body.dark-mode .modal-header {
    background: #0f172a !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .modal-body {
    background: #1e293b !important;
    color: #e2e8f0 !important;
}
body.dark-mode .modal-footer {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .form-label {
    color: #e2e8f0 !important;
}
body.dark-mode .btn-close {
    filter: invert(1);
}
body.dark-mode .bg-warning {
    background-color: #854d0e !important;
    color: white !important;
}
body.dark-mode .bg-orange { background-color: #c2410c !important; color: white !important; }
body.dark-mode .bg-green { background-color: #15803d !important; color: white !important; }
body.dark-mode .bg-blue { background-color: #1d4ed8 !important; color: white !important; }
body.dark-mode .bg-red { background-color: #dc2626 !important; color: white !important; }
body.dark-mode .bg-dark-red { background-color: #991b1b !important; color: white !important; }
body.dark-mode .bg-teal { background-color: #0d9488 !important; color: white !important; }
body.dark-mode .loan-card .card-header .text-muted { color: #94a3b8 !important; }
body.dark-mode .bg-info {
    background-color: #0ea5e9 !important;
}
body.dark-mode .bg-primary {
    background-color: #4f46e5 !important;
}
body.dark-mode .bg-teal {
    background-color: #0d9488 !important;
}
body.dark-mode .bg-dark {
    background-color: #1e293b !important;
}
body.dark-mode .badge.bg-light {
    background-color: #334155 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .btn {
    color: #f1f5f9 !important;
}
body.dark-mode .btn-success {
    background-color: #059669 !important;
    border-color: #059669 !important;
}
body.dark-mode .btn-danger {
    background-color: #dc2626 !important;
    border-color: #dc2626 !important;
}
body.dark-mode .btn-secondary {
    background-color: #475569 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}
.custom-modal { display: none; }
body.dark-mode .custom-modal .modal-inner-div { background: #1e293b !important; color: #e2e8f0 !important; }
body.dark-mode .custom-modal .form-label { color: #e2e8f0 !important; }
body.dark-mode .custom-modal .form-control { background: #334155 !important; border-color: #475569 !important; color: #f1f5f9 !important; }
body.dark-mode .custom-modal p { color: #e2e8f0 !important; }
body.dark-mode .custom-modal h5 { color: #e2e8f0 !important; }
body.dark-mode .custom-modal .btn-secondary { background-color: #475569 !important; border-color: #475569 !important; color: #f1f5f9 !important; }
</style>

</body>

<script>
function showLoanStep(accountNumber, step, btn) {
    // Hide all step contents for this card with fade
    document.querySelectorAll('#loan-step-1-' + accountNumber + ', #loan-step-2-' + accountNumber + ', #loan-step-3-' + accountNumber).forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateX(-10px)';
        setTimeout(() => {
            el.style.display = 'none';
        }, 200);
    });
    
    // Show the selected step with fade
    const selectedEl = document.getElementById('loan-step-' + step + '-' + accountNumber);
    setTimeout(() => {
        selectedEl.style.display = 'block';
        selectedEl.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        selectedEl.style.opacity = '1';
        selectedEl.style.transform = 'translateX(0)';
    }, 200);
    
    // Update button states with smooth transition
    btn.parentElement.querySelectorAll('.loan-step-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function openRejectModal(accountNumber) {
    const modal = document.getElementById('rejectModal' + accountNumber);
    modal.style.display = 'flex';
    // Focus on textarea after showing
    setTimeout(() => {
        const textarea = modal.querySelector('textarea');
        if (textarea) textarea.focus();
    }, 100);
}

function closeRejectModal(accountNumber) {
    const modal = document.getElementById('rejectModal' + accountNumber);
    modal.style.display = 'none';
}

function handleApprove(event, btn) {
    event.preventDefault();
    const href = btn.getAttribute('href');
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');
    
    btnText.style.display = 'none';
    btnLoader.style.display = 'inline-flex';
    btn.style.pointerEvents = 'none';
    btn.classList.add('approved');
    
    setTimeout(() => {
        window.location.href = href;
    }, 800);
}

function handleReject(event, btn) {
    event.preventDefault();
    btn.style.transform = 'scale(0.95)';
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById(btn.getAttribute('data-bs-target').replace('#', '')));
        modal.show();
    }, 150);
}

// Add staggered animation on page load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.loan-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

});
</script>
<?php require_once 'include/footer.php'; ?>
