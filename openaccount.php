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
        
        <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error_msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); endif; ?>
        
        <?php if(isset($_SESSION['fraud_warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>⚠️ FRAUD ALERT - Possible Duplicate Account Detected!</strong>
            <ul class="mb-0 mt-2">
                <?php foreach($_SESSION['fraud_warning'] as $flag): ?>
                <li><?php echo $flag; ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        unset($_SESSION['fraud_warning']); 
        endif; ?>
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-file-signature text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-0">New Loan Account</h1>
            </div>
          </div>
          <div class="page-actions">
            <a href="manageaccount.php" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-list"></i> Manage Loans
            </a>
          </div>
        </div>
        
        <div class="row justify-content-center">
          <div class="col-lg-11">
            <div class="form-panel">
              <div class="form-panel-header">
                <h4>Loan Account Details</h4>
                <p>Enter loan details to create a new loan record.</p>
              </div>
              <div class="form-panel-body">
                <form action="app/openaccountHandler.php" method="post">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <label class="form-label">Account Number</label>
                      <input type="number" name="account_number" class="form-control" placeholder="Enter account number" required>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Select Customer</label>
                      <?php
                      $customers = mysqli_query($conn, "SELECT customer_number, first_name, surname, email, phone FROM customers WHERE user_id = '$user_id'");
                      $hasCustomers = mysqli_num_rows($customers) > 0;
                      ?>
                      <?php if($hasCustomers): ?>
                      <select class="form-select" name="customer" id="customerSelect" onchange="showCustomerInfo()" required>
                        <option value="" selected disabled>Choose customer...</option>
                        <?php while($row = mysqli_fetch_assoc($customers)): ?>
                        <option value="<?php echo $row['customer_number'];?>" data-email="<?php echo $row['email'];?>" data-phone="<?php echo $row['phone'];?>">
                          <?php echo $row['first_name'] . ' ' . $row['surname'];?> (<?php echo $row['customer_number'];?>)
                        </option>
                        <?php endwhile; ?>
                      </select>
                      <?php else: ?>
                      <div class="alert alert-warning mb-0 py-2">
                        No customers available. <a href="addcustomer.php">Add one</a>
                      </div>
                      <?php endif; ?>
                    </div>

                    <div class="col-12" id="customerInfo" style="display:none;">
                      <div class="bg-light rounded p-3">
                        <small class="text-muted d-block">Email</small>
                        <span id="customerEmail" class="me-4">-</span>
                        <small class="text-muted d-inline-block">Phone</small>
                        <span id="customerPhone">-</span>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Loan Type</label>
                      <?php
                      $loan_types = mysqli_query($conn, "SELECT account_type_number, account_type_name FROM account_type WHERE LOWER(account_type_name) != 'other'");
                      $hasLoanTypes = mysqli_num_rows($loan_types) > 0;
                      ?>
                      <?php if($hasLoanTypes): ?>
                      <select class="form-select" name="account_type" id="loanType" onchange="calculateInterest()" required>
                        <option value="" selected disabled>Select loan type...</option>
                        <?php while($rows = mysqli_fetch_assoc($loan_types)): ?>
                        <option value="<?php echo $rows['account_type_number']; ?>">
                          <?php echo $rows['account_type_name']; ?>
                        </option>
                        <?php endwhile;?>
                      </select>
                      <?php else: ?>
                      <div class="alert alert-warning mb-0 py-2">
                        No loan types. <a href="addaccount_type.php">Add one</a>
                      </div>
                      <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Loan Amount (₱)</label>
                      <input type="number" name="loan_amount" id="loanAmount" class="form-control" placeholder="Enter loan amount" step="0.01" oninput="calculateInterest()" required>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Loan Term</label>
                      <select class="form-select" name="loan_term" id="loanTerm" onchange="calculateInterest()" required>
                        <option value="" selected disabled>Select term...</option>
                        <option value="1">1 Month</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                        <option value="18">18 Months</option>
                        <option value="24">24 Months</option>
                      </select>
                    </div>

                    <!-- Interest Computation Display -->
                    <div class="col-12" id="interestCalculation" style="display:none;">
                      <div class="alert-info border rounded p-4 mt-2">
                        <h6 class="mb-3"><i class="fas fa-calculator me-2"></i>Interest Computation</h6>
                        <div class="row">
                          <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Principal Amount</small>
                            <strong id="displayPrincipal">₱0.00</strong>
                          </div>
                          <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Monthly Interest Rate</small>
                            <strong id="displayMonthlyRate">-</strong>
                          </div>
                          <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Total Interest Rate</small>
                            <strong id="displayTotalRate">0%</strong>
                          </div>
                          <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Interest Amount</small>
                            <strong id="displayInterest" class="text-primary">₱0.00</strong>
                          </div>
                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Total Amount with Interest</small>
                            <h4 id="displayTotal" class="text-success mb-0">₱0.00</h4>
                          </div>
                          <div class="col-md-6 col-12">
                            <small class="text-muted d-block">Monthly Payment</small>
                            <h5 id="displayMonthly" class="text-info mb-0">₱0.00</h5>
                          </div>
                        </div>
                        <!-- Hidden fields to store computed values -->
                        <input type="hidden" name="computed_interest" id="computedInterest" value="0">
                        <input type="hidden" name="computed_total" id="computedTotal" value="0">
                        <input type="hidden" name="monthly_payment" id="monthlyPayment" value="0">
                      </div>
                    </div>

                    <div class="col-12">
                      <hr class="my-4">
                      <h6 class="text-muted mb-3">Disbursement Information</h6>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Disbursement Method</label>
                      <select class="form-select" name="disbursement_method" id="disbursementMethod" onchange="toggleDisbursementFields()" required>
                        <option value="" selected disabled>Select method...</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="Cash">Cash Pickup</option>
                      </select>
                    </div>

                    <div id="bankFields" style="display:none;" class="row g-3 w-100 ms-0">
                      <div class="col-md-6">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" placeholder="Enter bank name">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="disbursement_account" class="form-control" placeholder="Enter account number">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="disbursement_account_name" class="form-control" placeholder="Enter account name">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Branch</label>
                        <input type="text" name="branch" class="form-control" placeholder="Enter branch">
                      </div>
                    </div>

                    <div id="ewalletFields" style="display:none;" class="row g-3 w-100 ms-0">
                      <div class="col-md-6">
                        <label class="form-label">E-Wallet Type</label>
                        <select name="ewallet_type" class="form-select">
                          <option value="GCash">GCash</option>
                          <option value="PayMaya">PayMaya</option>
                          <option value="ShopeePay">ShopeePay</option>
                          <option value="Other">Other</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">E-Wallet Number</label>
                        <input type="text" name="ewallet_number" class="form-control" placeholder="Enter e-wallet number">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="ewallet_account_name" class="form-control" placeholder="Enter account name">
                      </div>
                    </div>

                    <div id="cashFields" style="display:none;" class="row g-3 w-100 ms-0">
                      <div class="col-md-12">
                        <label class="form-label">Pickup Location</label>
                        <input type="text" name="pickup_location" class="form-control" placeholder="Enter pickup location">
                      </div>
                    </div>

                    <div class="col-12 mt-4">
                      <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5" name="open_account">
                          <i class="fas fa-check-circle me-2"></i> Open Account
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
        </div>
        
      </main>
    </div>
  </div>

  <script>
  function toggleDisbursementFields() {
      var method = document.getElementById('disbursementMethod').value;
      var bankFields = document.getElementById('bankFields');
      var ewalletFields = document.getElementById('ewalletFields');
      var cashFields = document.getElementById('cashFields');
      
      bankFields.style.display = 'none';
      ewalletFields.style.display = 'none';
      cashFields.style.display = 'none';
      
      if (method === 'Bank Transfer') {
          bankFields.style.display = 'flex';
      } else if (method === 'E-Wallet') {
          ewalletFields.style.display = 'flex';
      } else if (method === 'Cash') {
          cashFields.style.display = 'flex';
      }
  }
  
  function showCustomerInfo() {
      var select = document.getElementById('customerSelect');
      var customerInfo = document.getElementById('customerInfo');
      var email = select.options[select.selectedIndex].getAttribute('data-email');
      var phone = select.options[select.selectedIndex].getAttribute('data-phone');
      
      document.getElementById('customerEmail').textContent = email || '-';
      document.getElementById('customerPhone').textContent = phone || '-';
      
      customerInfo.style.display = select.value ? 'block' : 'none';
  }
  
  // Interest Calculation Function
  function calculateInterest() {
      var loanAmount = parseFloat(document.getElementById('loanAmount').value) || 0;
      var loanTerm = parseInt(document.getElementById('loanTerm').value) || 0;
      var interestSection = document.getElementById('interestCalculation');
      
      // Interest rates by loan type (monthly)
    var interestRates = {
        'Emergency Loan': 2.0,
        'Educational Loan': 1.5,
        'Personal Loan': 3.0,
        'Business Loan': 4.0
    };
    
    // Get loan type
    var loanTypeSelect = document.getElementById('loanType');
    var loanType = loanTypeSelect ? loanTypeSelect.options[loanTypeSelect.selectedIndex].text.trim() : '';
    var monthlyRate = interestRates[loanType] || 1.5;
    
    // Try to find matching rate (case-insensitive)
    for (var key in interestRates) {
        if (key.toLowerCase() === loanType.toLowerCase()) {
            monthlyRate = interestRates[key];
            break;
        }
    }
      
      if (loanAmount > 0 && loanTerm > 0) {
          interestSection.style.display = 'block';
          
          // Calculate total interest rate
          var totalRate = monthlyRate * loanTerm;
          
          // Calculate interest amount: (principal / 100) * totalRate
          var interest = (loanAmount / 100) * totalRate;
          
          // Calculate total amount with interest
          var totalWithInterest = loanAmount + interest;
          
          // Calculate monthly payment
          var monthlyPayment = totalWithInterest / loanTerm;
          
          // Update display
          document.getElementById('displayPrincipal').textContent = '₱' + loanAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
          document.getElementById('displayMonthlyRate').textContent = monthlyRate.toFixed(1) + '%';
          document.getElementById('displayTotalRate').textContent = totalRate.toFixed(2) + '%';
          document.getElementById('displayInterest').textContent = '₱' + interest.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
          document.getElementById('displayTotal').textContent = '₱' + totalWithInterest.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
          document.getElementById('displayMonthly').textContent = '₱' + monthlyPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
          
          // Set hidden field values
          document.getElementById('computedInterest').value = interest.toFixed(2);
          document.getElementById('computedTotal').value = totalWithInterest.toFixed(2);
          document.getElementById('monthlyPayment').value = monthlyPayment.toFixed(2);
      } else {
          interestSection.style.display = 'none';
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
body.dark-mode .card {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-color: #334155 !important;
}
body.dark-mode .card-body {
    color: #e2e8f0 !important;
    background: #1e293b !important;
}
body.dark-mode .form-panel {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .form-panel-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    border-color: #334155 !important;
}
body.dark-mode .form-panel-header h4,
body.dark-mode .form-panel-header p {
    color: #e2e8f0 !important;
}
body.dark-mode .form-panel-body {
    background: #1e293b !important;
}
body.dark-mode .page-title-section h1,
body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
    color: #f1f5f9 !important;
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
}
body.dark-mode .form-select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}
body.dark-mode .text-muted {
    color: #94a3b8 !important;
}
body.dark-mode .form-check-label {
    color: #e2e8f0 !important;
}
body.dark-mode .alert-success {
    background: #064e3b !important;
    color: #6ee7b7 !important;
}
body.dark-mode .alert-danger {
    background: #7f1d1d !important;
    color: #fca5a5 !important;
}
body.dark-mode .btn-close {
    filter: invert(1);
}
body.dark-mode .alert-warning {
    background: #78350f !important;
    color: #fcd34d !important;
}
body.dark-mode .alert-warning a {
    color: #fcd34d !important;
    text-decoration: underline !important;
}
body.dark-mode .bg-light {
    background: #334155 !important;
}
.report-icon {
  flex-shrink: 0;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
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

</body>
<?php require_once 'include/footer.php'; ?>
