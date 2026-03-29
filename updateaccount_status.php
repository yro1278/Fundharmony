<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';
$update_success_msg = '';

if (count($_POST) > 0) {
    mysqli_query($conn, "UPDATE account_status SET
  account_status_name = '" . $_POST['account_status_name'] . "'
   WHERE
    account_status_number = '" . $_POST['account_status_number'] . "' ");

    $update_success_msg = "Information has been updated successfully";
}
$result = mysqli_query($conn, "SELECT * FROM account_status
WHERE account_status_number = '" . $_GET['account_status_number'] . "'");
$row = mysqli_fetch_assoc($result);
?>

<body>
  <?php
  require_once 'include/navbar.php';
  ?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <div class="container mt-3">
          <?php if($update_success_msg): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <strong><?php echo $update_success_msg; ?></strong>
          </div>
          <?php endif; ?>
          
          <div class="card">
            <div class="card-header bg-success text-white">
              <h4>Update Account Status</h4>
            </div>
            <div class="card-body">
              <form action="updateaccount_status.php?account_status_number=<?php echo $_GET['account_status_number']; ?>" method="post" autocomplete="off">
                <div class="form-group">
                  <label for="account status number">Account status number</label>
                  <input type="text" name="account_status_number" class="form-control" value="<?php echo $row['account_status_number']; ?>" readonly>
                </div>
                <div class="form-group">
                  <label for="account status name">Account status name</label>
                  <input type="text" name="account_status_name" class="form-control" value="<?php echo $row['account_status_name']; ?>">
                </div>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update</button>
                <a href="manageaccount_status.php" class="btn btn-secondary">Cancel</a>
              </form>
            </div>
          </div>
        </div>
        
      </main>
    </div>
  </div>
</body>
<?php require_once 'include/footer.php'; ?>
