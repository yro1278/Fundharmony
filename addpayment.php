<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$user_id = $_SESSION['user_id'];
?>

<body>
  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); endif; ?>
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="fas fa-money-bill-wave text-white" style="font-size: 22px;"></i>
            </div>
            <h1 class="mb-0">Record Payment</h1>
          </div>
          <div class="page-actions">
            <a href="managepayment.php" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-history"></i> Payment History
            </a>
          </div>
        </div>
        
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="form-panel">
              <div class="form-panel-header">
                <h4>Payment Details</h4>
                <p>Record a new payment from a client</p>
              </div>
              <div class="form-panel-body">
                <form action="app/addpaymentHandler.php" method="post">
                  <div class="row g-4">
                    <div class="col-md-12">
                      <label class="form-label">Select Client Account</label>
                      <select class="form-select" name="account_number" id="account_number" required>
                        <option value="" selected disabled>Choose account...</option>
                        <?php
                        $result = mysqli_query($conn, "SELECT a.account_number, COALESCE(a.loan_balance, a.loan_amount) as balance, c.first_name, c.surname, a.account_type FROM accounts a INNER JOIN customers c ON a.customer = c.customer_number WHERE a.account_status IN (-2, 1, 5, 6, 7) AND (COALESCE(a.loan_balance, a.loan_amount) > 0)");
                        while($row = mysqli_fetch_assoc($result)):
                        ?>
                        <option value="<?php echo $row['account_number'];?>">
                          <?php echo $row['account_number'] . ' - ' . $row['first_name'] . ' ' . $row['surname'] . ' (Balance: ₱' . number_format($row['balance'], 2) . ')';?>
                        </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    
                    <div class="col-md-12">
                      <label class="form-label">Payment Amount (₱)</label>
                      <input type="number" step="0.01" class="form-control" name="payment_amount" id="payment_amount" placeholder="Enter amount" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Payment Date</label>
                      <input type="date" class="form-control" name="payment_date" id="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Payment Method</label>
                      <select class="form-select" name="payment_method" id="payment_method" onchange="toggleDisbursementFields()">
                        <option value="" selected disabled>Select method...</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="Cheque">Cheque</option>
                      </select>
                    </div>
                    
                    <div id="bankFields" style="display:none;" class="row g-3 w-100 ms-0">
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="bank_name" class="form-control" id="bank_name" placeholder="Bank Name">
                          <label for="bank_name">Bank Name</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="disbursement_account" class="form-control" id="disbursement_account" placeholder="Account Number">
                          <label for="disbursement_account">Account Number</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="disbursement_account_name" class="form-control" id="disbursement_account_name" placeholder="Account Name">
                          <label for="disbursement_account_name">Account Name</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="branch" class="form-control" id="branch" placeholder="Branch">
                          <label for="branch">Branch</label>
                        </div>
                      </div>
                    </div>
                    
                    <div id="ewalletFields" style="display:none;" class="row g-3 w-100 ms-0">
                      <div class="col-md-6">
                        <div class="form-floating">
                          <select name="ewallet_type" class="form-select" id="ewallet_type" style="text-align: left; padding-left: 12px;">
                            <option value="GCash">GCash</option>
                            <option value="PayMaya">PayMaya</option>
                            <option value="ShopeePay">ShopeePay</option>
                            <option value="Other">Other</option>
                          </select>
                          <label for="ewallet_type">E-Wallet Type</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="ewallet_number" class="form-control" id="ewallet_number" placeholder="E-Wallet Number">
                          <label for="ewallet_number">E-Wallet Number</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="ewallet_account_name" class="form-control" id="ewallet_account_name" placeholder="Account Name">
                          <label for="ewallet_account_name">Account Name</label>
                        </div>
                      </div>
                    </div>
                    
                    <div id="cashFields" style="display:none;" class="row g-3 w-100 ms-0">
                      <div class="col-md-12">
                        <div class="form-floating">
                          <input type="text" name="pickup_location" class="form-control" id="pickup_location" placeholder="Pickup Location">
                          <label for="pickup_location">Pickup Location</label>
                        </div>
                      </div>
                    </div>
                    
                    <div id="chequeFields" style="display:none;" class="row g-3 w-100 ms-0">
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="cheque_number" class="form-control" id="cheque_number" placeholder="Cheque Number">
                          <label for="cheque_number">Cheque Number</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="cheque_bank" class="form-control" id="cheque_bank" placeholder="Bank Name">
                          <label for="cheque_bank">Bank Name</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="date" name="cheque_date" class="form-control" id="cheque_date">
                          <label for="cheque_date">Cheque Date</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input type="text" name="cheque_account_name" class="form-control" id="cheque_account_name" placeholder="Account Name">
                          <label for="cheque_account_name">Account Name</label>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-12">
                      <div class="form-floating">
                        <textarea class="form-control" name="notes" id="notes" rows="2" placeholder="Notes" style="height: auto;"></textarea>
                        <label for="notes">Notes (Optional)</label>
                      </div>
                    </div>
                    
                    <div class="col-12 mt-4">
                      <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-success btn-lg px-5" name="add_payment">
                          <i class="fas fa-check me-2"></i> Record Payment
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg">
                          Reset
                        </button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          
          <div class="col-lg-4">
            <div class="quick-tips-full">
              <div class="tips-content">
                <h5><i class="fas fa-lightbulb me-2"></i>Quick Tips</h5>
                <ul class="mb-0">
                  <li>Select the correct client account</li>
                  <li>Enter the exact payment amount</li>
                  <li>Choose appropriate payment method</li>
                  <li>Disbursement fields appear based on method</li>
                  <li>Date defaults to today</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        
      </main>
    </div>
  </div>

  <script>
  function toggleDisbursementFields() {
      var method = document.getElementById('payment_method').value;
      var bankFields = document.getElementById('bankFields');
      var ewalletFields = document.getElementById('ewalletFields');
      var cashFields = document.getElementById('cashFields');
      var chequeFields = document.getElementById('chequeFields');
      
      bankFields.style.display = 'none';
      ewalletFields.style.display = 'none';
      cashFields.style.display = 'none';
      chequeFields.style.display = 'none';
      
      if (method === 'Bank Transfer') {
          bankFields.style.display = 'flex';
      } else if (method === 'E-Wallet') {
          ewalletFields.style.display = 'flex';
      } else if (method === 'Cash') {
          cashFields.style.display = 'flex';
      } else if (method === 'Cheque') {
          chequeFields.style.display = 'flex';
      }
  }
  </script>

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
body.dark-mode .form-panel-header {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
    border-color: #334155 !important;
    color: white !important;
}
body.dark-mode .form-panel-header h4,
body.dark-mode .form-panel-header p {
    color: white !important;
}
body.dark-mode .form-panel-body {
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
body.dark-mode .form-label {
    color: #e2e8f0 !important;
}
body.dark-mode .form-select,
body.dark-mode .form-control {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
    text-align: left !important;
}
body.dark-mode .form-select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
    text-align: left !important;
}
body.dark-mode .text-muted {
    color: #94a3b8 !important;
}
body.dark-mode .form-floating > label {
    color: #94a3b8 !important;
    align-items: flex-start !important;
    padding-top: 1rem !important;
}
body.dark-mode select.form-select,
body.dark-mode select {
    text-align: left !important;
    padding-left: 0.75rem !important;
}
body.dark-mode #ewallet_type {
    text-align: left !important;
}
body.dark-mode .form-floating select.form-select {
    padding-top: 1rem !important;
    padding-bottom: 0.5rem !important;
}
</style>

</body>
<?php require_once 'include/footer.php'; ?>
