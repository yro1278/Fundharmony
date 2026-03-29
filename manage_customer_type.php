<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$retrieve = mysqli_query($conn, "SELECT * FROM customers_type ORDER BY registration_date DESC");
?>

<body>
  <?php 
  require_once 'include/navbar.php'; 
  ?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <?php if(mysqli_num_rows($retrieve) > 0): ?>
        <div class="topnav mt-3" id="myTopnav">
          <a href="addcustomer_type.php"><i class="fas fa-plus"></i> Add Customer Type</a>
          <a href="manage_customer_type.php" class="active"><i class="fas fa-table"></i> Manage Customer Type</a>
        </div>

        <div class="table-responsive mt-3">
          <table class="table table-striped table-bordered">
            <thead class="bg-info text-white">
              <tr>
                <th>SN</th>
                <th>Type Number</th>
                <th>Type Name</th>
                <th>Registration Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $num = 1;
              while ($result = mysqli_fetch_assoc($retrieve)):
              ?>
              <tr>
                <td><?php echo $num++; ?></td>
                <td><?php echo $result["customer_type_number"]; ?></td>
                <td><?php echo $result["customer_type_name"]; ?></td>
                <td><?php echo $result["registration_date"]; ?></td>
                <td>
                  <a href="update_customertype.php?customer_type_number=<?php echo $result["customer_type_number"]; ?>" class="btn btn-primary btn-sm"><i class="fas fa-pencil-alt"></i> Edit</a>
                  <a href="app/deletecustomer_type.php?customer_type_number=<?php echo $result["customer_type_number"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info mt-3">No customer types found. <a href="addcustomer_type.php">Add a new customer type</a></div>
        <?php endif; ?>
        
      </main>
    </div>
  </div>
</body>
<?php require_once 'include/footer.php'; ?>
