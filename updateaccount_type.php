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
    mysqli_query($conn, "UPDATE account_type SET
  account_type_name = '" . $_POST['account_type_name'] . "'
   WHERE
    account_type_number = '" . $_POST['account_type_number'] . "' ");

    $update_success_msg = "Information has been updated successfully";
}
$result = mysqli_query($conn, "SELECT * FROM account_type
WHERE account_type_number = '" . $_GET['account_type_number'] . "'");
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
            <div class="card-header bg-primary text-white">
              <h4>Update Account Type</h4>
            </div>
            <div class="card-body">
              <form action="updateaccount_type.php?account_type_number=<?php echo $_GET['account_type_number']; ?>" method="post" autocomplete="off">
                <div class="form-group">
                  <label for="account type number">Account type number</label>
                  <input type="text" name="account_type_number" class="form-control" value="<?php echo $row['account_type_number']; ?>" readonly>
                </div>
                <div class="form-group">
                  <label for="account type name">Account type name</label>
                  <input type="text" name="account_type_name" class="form-control" value="<?php echo $row['account_type_name']; ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                <a href="manageaccount_type.php" class="btn btn-secondary">Cancel</a>
              </form>
            </div>
          </div>
        </div>
        
      </main>
    </div>
  </div>
</body>
<?php require_once 'include/footer.php'; ?>
