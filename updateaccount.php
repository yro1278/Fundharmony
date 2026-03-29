<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';
$update_success_msg = '';

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT * FROM accounts
WHERE account_number = '" . $_GET['account_number'] . "' AND user_id = '$user_id'");
$row = mysqli_fetch_assoc($result);
if (!$row) {
    header('Location: manageaccount.php');
    exit();
}
$rdata = [];
foreach($row as $k =>$v){
    $rdata[$k] = $v;
}
?>

<body>
   <?php 
   require_once 'include/navbar.php';
   ?>
   
   <div class="container-fluid">
     <div class="row">
       <?php require_once 'include/sidebar.php'; ?>
       
       <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
         
         <div class="topnav mt-3" id="myTopnav">
           <a href="openaccount.php"><i class="fas fa-plus"></i> Create new account</a>
           <a href="manageaccount.php"><i class="fas fa-table"></i> Manage account</a>
           <a href="updateaccount.php?account_number=<?php echo $_GET['account_number']; ?>" class="active"><i class="fas fa-edit"></i> Update account</a>
         </div>
   <div class="card ">
      <div class="card-header bg-primary text-bold">
         CUSTOMER ACCOUNT OPENING FORM
      </div>
      <div class="card-body">
         <div class="card-text">
          <div class="alert alert-success alert-dismissible fade show " role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
         <span aria-hidden="true">&times;</span>
         <span class="sr-only">Close</span>
      </button>
      <strong><?php echo $_SESSION['success_msg']; ?></strong>
   </div>
            <form action="app/openaccountHandler.php" method="post">
               <div class="row">
                  <?php 
                  
               $account_number = $row['account_number'];
               ?>
                  <div class="col-md-12 col-sm-6">
                     <div class="form-group">
                        <label for="account number">Account number</label>
                        <input type="text" name="account_number" id="" class="form-control" placeholder="" aria-describedby="helpId"
                        value="<?php echo $account_number;?>" readonly>
                     </div>
                  </div>
                  <div class="col-md-12 col-sm-6">
                  <div class="form-group">
                     <label for="customer info">Customer Information</label>
                     <select class="custom-select" name="customer" id="">
                     <option selected>Select one</option>
                      <?php
                        $result = mysqli_query($conn, "SELECT customer_number, first_name FROM customers WHERE user_id = '$user_id'");
                        ?>
                        <?php
                        while($row =mysqli_fetch_assoc($result)){
                        ?>
                        
                        <option disabled><?php echo $row['first_name'];?></option>
                        <option value="<?php echo $row['customer_number'];?>" <?php echo $row['customer_number'] == $rdata['customer'] ? "selected" : '';?>>
                        <?php echo $row['customer_number'];?></option>
                        <?php } ?>
                     </select>
                  </div>
                  </div>
                  <div class="col-md-12 col-sm-6">
                  <div class="form-group">
                     <label for="account_type">Account Type</label>
                     <select class="custom-select" name="account_type" id="">
                     <option selected>Select one</option>
                         <?php
                           $results = mysqli_query($conn, "SELECT account_type_number, account_type_name FROM account_type");
                           ?>
                        <?php
                        while ($rows = mysqli_fetch_assoc($results)){ 
                           ?>
                        
                        <option disabled><?php echo $rows['account_type_name']; ?></option>
                        <option value="<?php echo $rows['account_type_number']; ?>" <?php echo $rows['account_type_number'] == $rdata['account_type'] ? "selected" : ''; ?>>
                        <?php echo $rows['account_type_number']; ?></option>
                        <?php }?>
                     </select>
                  </div>
                  </div>
                  <div class="col-md-12 col-sm-6">
                  <div class="form-group">
                     <label for="customer info">Loan Status</label>
                     <small class="text-muted d-block mb-2">Select the current status of the loan.</small>
                     <select class="custom-select" name="account_status" id="">
                      <option selected>Select one</option>
                        <?php
                        $results = mysqli_query($conn, "SELECT account_status_number, account_status_name FROM account_status");
                        ?>
                        <?php
                        while ($rows = mysqli_fetch_assoc($results)){
                        ?>
                       
                        <option disabled><?php echo $rows['account_status_name']; ?></option>
                        <option value="<?php echo $rows['account_status_number']; ?>" <?php echo $rows['account_status_number'] == $rdata['account_status'] ? "selected" : ''; ?>>
                        <?php echo $rows['account_status_number']; ?></option>
                        <?php }?>
                      </select>
                   </div>
                   </div>
                   <div class="form-group">
                     <button type="submit" class="btn btn-primary" name="update_account">Update Account</button>
                   </div>

                </div>
             </form>
          </div>
        </div>
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
body.dark-mode .form-label,
body.dark-mode label {
    color: #e2e8f0 !important;
}
body.dark-mode .form-select,
body.dark-mode .form-control {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}
</style>

</body>
<?php require_once 'include/footer.php'; ?>