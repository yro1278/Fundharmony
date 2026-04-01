<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';
$success_msg = '';

if (count($_POST) > 0) {
  mysqli_query($conn, "UPDATE customers_type SET 
  customer_type_name = '" .$_POST['customer_type_name'] ."'
   WHERE
    customer_type_number = '".$_POST['customer_type_number'] ."' ");

   $success_msg = 'Customer type record has been successfully updated';
}
$result = mysqli_query($conn, "SELECT * FROM customers_type 
WHERE customer_type_number = '".$_GET['customer_type_number']."'");
$row = mysqli_fetch_assoc( $result);
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
          <?php if($success_msg): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <strong><?php echo $success_msg; ?></strong>
          </div>
          <?php endif; ?>
          
          <div class="card">
            <div class="card-header bg-info text-white">
              <h4>Update Customer Type</h4>
            </div>
            <div class="card-body">
              <form action="update_customertype.php?customer_type_number=<?php echo $_GET['customer_type_number']; ?>" method="post" autocomplete="off">
                <div class="form-group">
                  <label for="customer type number">Customer type number</label>
                  <input type="text" name="customer_type_number" class="form-control" value="<?php echo $row['customer_type_number']; ?>" readonly>
                </div>
                <div class="form-group">
                  <label for="customer type name">Customer type name</label>
                  <input type="text" name="customer_type_name" class="form-control" value="<?php echo $row['customer_type_name']; ?>">
                </div>
                <button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Update</button>
                <a href="manage_customer_type.php" class="btn btn-secondary">Cancel</a>
              </form>
            </div>
          </div>
        </div>
        
      </main>
    </div>
  </div>
</body>
<?php require_once 'include/footer.php'; ?>
