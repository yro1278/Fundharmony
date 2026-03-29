<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Clear ALL data
if (isset($_POST['clear_data'])) {
    mysqli_query($conn, "DELETE FROM payments");
    mysqli_query($conn, "DELETE FROM accounts");
    mysqli_query($conn, "DELETE FROM customers");
    $message = "All data has been cleared successfully!";
}

// Get total table counts
$customers_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM customers"))['cnt'];
$accounts_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM accounts"))['cnt'];
$payments_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM payments"))['cnt'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clear Data - FundHarmony</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            padding: 12px 30px;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-database fa-3x text-danger mb-3"></i>
                    <h3>Clear All Data</h3>
                    <p class="text-muted">This will remove all records from the database</p>
                </div>

                <?php if($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
                <?php endif; ?>

                <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Current Data:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-users"></i> Customers: <strong><?php echo $customers_count; ?></strong></li>
                            <li><i class="fas fa-money-check"></i> Accounts: <strong><?php echo $accounts_count; ?></strong></li>
                            <li><i class="fas fa-money-bill"></i> Payments: <strong><?php echo $payments_count; ?></strong></li>
                        </ul>
                    </div>
                </div>

                <form method="POST" onsubmit="return confirm('Are you sure you want to clear ALL data? This action cannot be undone!');">
                    <button type="submit" name="clear_data" class="btn btn-danger w-100">
                        <i class="fas fa-trash-alt"></i> Clear All Data
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="dashboard.php" class="text-muted">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
