<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';
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
            <strong><?php echo $_SESSION['success_msg']; ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); endif; ?>
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="fas fa-check-double text-white" style="font-size: 22px;"></i>
            </div>
            <h1 class="mb-0">Add Loan Status</h1>
          </div>
          <div class="page-actions">
            <a href="openaccount.php" class="btn btn-outline-primary">
              <i class="fas fa-arrow-left"></i> Back to Loan
            </a>
          </div>
        </div>
        
        <div class="row">
          <div class="col-lg-8">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>New Loan Status</h5>
              </div>
              <div class="card-body">
                <form action="app/addaccounts_statusHandler.php" method="post" autocomplete="off">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="account_status_number" class="form-label">Status Number</label>
                        <div class="input-group">
                          <input type="number" name="account_status_number" class="form-control" placeholder="Enter status number" required>
                          <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="account_status_name" class="form-label">Status Name</label>
                        <div class="input-group">
                          <select class="form-select" name="account_status_name" id="statusSelect" onchange="checkOtherStatus()">
                            <option value="">Select Status</option>
                            <option value="Active">Active</option>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                            <option value="On Hold">On Hold</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Other">Other (Specify)</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-12" id="otherStatus" style="display: none;">
                      <div class="form-group">
                        <label for="account_status_name_other" class="form-label">Specify Status</label>
                        <input type="text" name="account_status_name" class="form-control" placeholder="Enter status name">
                      </div>
                    </div>
                    
                    <div class="col-12 mt-3">
                      <button type="submit" class="btn btn-success" name="add_account_status">
                        <i class="fas fa-save"></i> Add Status
                      </button>
                      <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Reset
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          
          <div class="col-lg-4">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2 text-info"></i>Current Statuses</h5>
              </div>
              <div class="card-body p-0">
                <?php
                $statuses = mysqli_query($conn, "SELECT * FROM account_status ORDER BY account_status_name");
                if(mysqli_num_rows($statuses) > 0):
                ?>
                <ul class="list-group list-group-flush">
                  <?php while($status = mysqli_fetch_assoc($statuses)): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                      <?php if($status['account_status_name'] == 'Active'): ?>
                        <span class="badge bg-success me-2">●</span>
                      <?php elseif($status['account_status_name'] == 'Pending'): ?>
                        <span class="badge bg-warning me-2">●</span>
                      <?php elseif($status['account_status_name'] == 'Rejected' || $status['account_status_name'] == 'Cancelled'): ?>
                        <span class="badge bg-danger me-2">●</span>
                      <?php else: ?>
                        <span class="badge bg-secondary me-2">●</span>
                      <?php endif; ?>
                      <?php echo $status['account_status_name']; ?>
                    </span>
                    <span class="badge bg-light text-dark"><?php echo $status['account_status_number']; ?></span>
                  </li>
                  <?php endwhile; ?>
                </ul>
                <?php else: ?>
                <div class="p-3 text-muted text-center">
                  <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                  No statuses yet
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
      </main>
    </div>
  </div>
  
  <script>
  function checkOtherStatus() {
    var select = document.getElementById('statusSelect');
    var otherDiv = document.getElementById('otherStatus');
    var otherInput = otherDiv.querySelector('input');
    
    if (select.value === 'Other') {
      otherDiv.style.display = 'block';
      otherInput.setAttribute('required', 'required');
      select.removeAttribute('name');
    } else if (select.value !== '') {
      otherDiv.style.display = 'none';
      otherInput.removeAttribute('required');
      otherInput.removeAttribute('name');
      select.setAttribute('name', 'account_status_name');
    } else {
      otherDiv.style.display = 'none';
      otherInput.removeAttribute('required');
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
body.dark-mode .card-header {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
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
</style>

</body>
<?php require_once 'include/footer.php'?>
