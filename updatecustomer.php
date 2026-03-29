<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'include/head.php';
require_once 'database/db_connection.php';
$success_msg = '';
$user_id = $_SESSION['user_id'];

if (isset($_POST['update_customer'])) {
    $password_update = "";
    $customer_number = $_POST['customer_number'];
    
    // Get customer info before update for logging
    $old_customer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT first_name, surname FROM customers WHERE customer_number = '$customer_number'"));
    $old_name = ($old_customer['first_name'] ?? '') . ' ' . ($old_customer['surname'] ?? '');
    
    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $password_update = ", password = '$password'";
    }
    
    mysqli_query($conn, "UPDATE customers SET
  customer_type = '" . $_POST['customer_type'] . "', first_name = '" . $_POST['first_name'] . "',
  middle_name = '" . $_POST['middle_name'] . "', surname ='" . $_POST['surname'] . "',
  nationality = '" . $_POST['nationality'] . "', date_of_birth = '" . $_POST['date_of_birth'] . "',
  gender = '" . $_POST['gender'] . "', email = '" . $_POST['email'] . "', phone = '" . $_POST['phone'] . "',
  region = '" . $_POST['region'] . "', city = '" . $_POST['city'] . "', barangay = '" . $_POST['barangay'] . "',
  zip_code = '" . $_POST['zip_code'] . "', full_address = '" . $_POST['full_address'] . "'
  $password_update
  WHERE
  customer_number = '$customer_number' AND user_id = '$user_id'");

  $success_msg = 'Information successfully updated';
  
  // Log the activity
  $admin_username = $_SESSION['admin'] ?? 'admin';
  $admin_user_id = $_SESSION['user_id'] ?? null;
  logActivity($conn, $admin_user_id, $admin_username, 'Update Customer', 'Updated customer: ' . $old_name . ' (ID: ' . $customer_number . ')', 'admin');

}
$update_customer = mysqli_query($conn, "SELECT * FROM customers
WHERE
customer_number = '" . $_GET['customer_number'] . "' AND user_id = '$user_id'");
$query = mysqli_fetch_assoc($update_customer);
if (!$query) {
    header('Location: managecustomer.php');
    exit();
}
?>

<body>
  <?php require_once 'include/navbar.php';?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <?php if($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <strong><?php echo $success_msg; ?></strong>
        </div>
        <?php endif; ?>
        
        <div class="topnav mt-3" id="myTopnav">
          <a href="addcustomer.php"><i class="fas fa-plus"></i> Add Customer</a>
          <a href="managecustomer.php"><i class="fas fa-table"></i> Manage Customer</a>
          <a href="updatecustomer.php?customer_number=<?php echo $_GET['customer_number']; ?>" class="active"><i class="fas fa-edit"></i> Update Customer</a>
        </div>
        
        <form action="updatecustomer.php?customer_number=<?php echo $_GET['customer_number']; ?>" method="post" autocomplete="off" class="mt-3">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="">Customer number</label>
                <input type="number" name="customer_number" class="form-control" value="<?php echo $query['customer_number']; ?>" readonly>
              </div>
            </div>
            
            <div class="col-md-12">
              <div class="form-group">
                <label for="">Customer Type</label>
                <select class="custom-select" name="customer_type">
                  <?php
                  $result = mysqli_query($conn, "SELECT customer_type_number, customer_type_name FROM customers_type");
                  while($row = mysqli_fetch_assoc($result)){
                    $selected = ($row['customer_type_number'] == $query['customer_type']) ? 'selected' : '';
                    echo '<option value="'.$row['customer_type_number'].'" '.$selected.'>'.$row['customer_type_name'].'</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="">First name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo $query['first_name']; ?>">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="">Middle name</label>
                <input type="text" name="middle_name" class="form-control" value="<?php echo $query['middle_name']; ?>">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="">Surname</label>
                <input type="text" name="surname" class="form-control" value="<?php echo $query['surname']; ?>">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="">Nationality</label>
                <select name="nationality" class="form-control">
                  <option value="Filipino" <?php echo ($query['nationality'] == 'Filipino') ? 'selected' : ''; ?>>Filipino</option>
                  <option value="Kenyan" <?php echo ($query['nationality'] == 'Kenyan') ? 'selected' : ''; ?>>Kenya</option>
                  <option value="Ugandan" <?php echo ($query['nationality'] == 'Ugandan') ? 'selected' : ''; ?>>Uganda</option>
                  <option value="Tanzanian" <?php echo ($query['nationality'] == 'Tanzanian') ? 'selected' : ''; ?>>Tanzania</option>
                  <option value="American" <?php echo ($query['nationality'] == 'American') ? 'selected' : ''; ?>>American</option>
                  <option value="British" <?php echo ($query['nationality'] == 'British') ? 'selected' : ''; ?>>British</option>
                  <option value="Chinese" <?php echo ($query['nationality'] == 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                  <option value="Indian" <?php echo ($query['nationality'] == 'Indian') ? 'selected' : ''; ?>>Indian</option>
                  <option value="Japanese" <?php echo ($query['nationality'] == 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                  <option value="Korean" <?php echo ($query['nationality'] == 'Korean') ? 'selected' : ''; ?>>Korean</option>
                  <option value="Other" <?php echo ($query['nationality'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group">
                <label for="">Date of birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?php echo $query['date_of_birth']; ?>">
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label for="">Gender</label><br>
                Male: <input type="radio" name="gender" value="M" <?php echo ($query['gender'] == 'M') ? 'checked' : ''; ?>>
                Female: <input type="radio" name="gender" value="F" <?php echo ($query['gender'] == 'F') ? 'checked' : ''; ?>>
                Others: <input type="radio" name="gender" value="O" <?php echo ($query['gender'] == 'O') ? 'checked' : ''; ?>>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $query['email']; ?>">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo $query['phone']; ?>">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="">Region</label>
                <input type="text" name="region" class="form-control" value="<?php echo $query['region']; ?>">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="">City/Municipality</label>
                <input type="text" name="city" class="form-control" value="<?php echo $query['city']; ?>">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="">Barangay</label>
                <input type="text" name="barangay" class="form-control" value="<?php echo $query['barangay']; ?>">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="">Zip Code</label>
                <input type="text" name="zip_code" class="form-control" value="<?php echo $query['zip_code']; ?>">
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label for="">Full Address</label>
                <input type="text" name="full_address" class="form-control" value="<?php echo $query['full_address']; ?>">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="">Password (leave blank to keep current)</label>
                <input type="text" name="password" class="form-control" value="<?php echo htmlspecialchars($query['password']); ?>">
              </div>
            </div>
            
            <div class="col-md-12">
              <div class="form-group">
                <button type="submit" name="update_customer" class="btn btn-primary">Update Customer</button>
                <a href="managecustomer.php" class="btn btn-secondary">Cancel</a>
              </div>
            </div>
          </div>
        </form>
        
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
body.dark-mode .form-panel {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .form-panel-header {
    background: #1e293b !important;
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
body.dark-mode .form-select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}
body.dark-mode .text-muted {
    color: #94a3b8 !important;
}
</style>

</body>
<?php require_once 'include/footer.php'; ?>
