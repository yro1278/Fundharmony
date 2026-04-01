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
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="fas fa-tags text-white" style="font-size: 22px;"></i>
            </div>
            <h1 class="mb-0">Add Loan Type</h1>
          </div>
          <div class="page-actions">
            <a href="manageaccount.php" class="btn btn-outline-primary">
              <i class="fas fa-list"></i> Manage Loans
            </a>
          </div>
        </div>
        
        <div class="row">
          <div class="col-lg-8">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>New Loan Type</h5>
              </div>
              <div class="card-body">
                <form action="app/addaccounts_typeHandler.php" method="post" autocomplete="off">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="account_type_number" class="form-label">Loan Type Number</label>
                        <div class="input-group">
                          <input type="number" name="account_type_number" class="form-control" placeholder="Enter type number" required>
                          <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="account_type_name" class="form-label">Loan Type Name</label>
                        <div class="input-group">
                          <select class="form-select" name="account_type_name" id="loanTypeSelect">
                            <option value="">Select Loan Type</option>
                            <option value="Emergency Loan">Emergency Loan</option>
                            <option value="Business Loan">Business Loan</option>
                            <option value="Personal Loan">Personal Loan</option>
                            <option value="Educational Loan">Educational Loan</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-12 mt-3">
                      <button type="submit" class="btn btn-primary" name="add_account_type">
                        <i class="fas fa-save"></i> Add Loan Type
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
                <h5 class="mb-0"><i class="fas fa-list me-2 text-info"></i>Current Loan Types</h5>
              </div>
              <div class="card-body p-0">
                <?php
                $types = mysqli_query($conn, "SELECT * FROM account_type ORDER BY account_type_name");
                if(mysqli_num_rows($types) > 0):
                ?>
                <ul class="list-group list-group-flush">
                  <?php while($type = mysqli_fetch_assoc($types)): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><?php echo $type['account_type_name']; ?></span>
                    <span class="badge bg-primary"><?php echo $type['account_type_number']; ?></span>
                  </li>
                  <?php endwhile; ?>
                </ul>
                <?php else: ?>
                <div class="p-3 text-muted text-center">
                  <i class="fas fa-money-bill-wave fa-2x mb-2 d-block"></i>
                  No loan types yet
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
      </main>
    </div>
  </div>
  
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
body.dark-mode .form-control,
body.dark-mode .input-group-text {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .text-muted {
    color: #94a3b8 !important;
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
body.dark-mode .btn-outline-danger {
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .btn-outline-primary:hover,
body.dark-mode .btn-outline-danger:hover {
    background: #334155 !important;
}
body.dark-mode .input-group-text {
    color: #94a3b8 !important;
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
</style>

</body>
<?php require_once 'include/footer.php'?>
