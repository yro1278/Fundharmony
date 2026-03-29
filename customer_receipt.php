<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}
error_reporting(0);
ini_set('display_errors', 0);
require_once 'database/db_connection.php';

$payment_number = isset($_GET['id']) ? intval($_GET['id']) : 0;
$customer_id = $_SESSION['customer_id'];

if (!$payment_number) {
    die('Payment ID is required');
}

$sql = "SELECT p.*, a.account_type, a.account_number, a.loan_amount, a.loan_balance
FROM payments p 
INNER JOIN accounts a ON p.account_number = a.account_number 
WHERE p.payment_number = $payment_number AND a.customer = '$customer_id'";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Query error: ' . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);

if (!$row) {
    die('Payment not found');
}

require_once 'include/head.php';
?>
<style>
    body { background: #f8f9fa; }
    .page-title {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 15px 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .page-title .brand {
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .receipt {
        max-width: 500px;
        margin: 50px auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        padding: 30px;
    }
    .receipt-header {
        text-align: center;
        border-bottom: 2px solid #4f46e5;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    .receipt-header h2 {
        color: #4f46e5;
        margin: 0;
    }
    .receipt-detail {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    .receipt-detail:last-child {
        border-bottom: none;
    }
    .receipt-label {
        color: #666;
        font-weight: 500;
    }
    .receipt-value {
        color: #333;
        font-weight: 600;
    }
    .amount-paid {
        text-align: center;
        margin: 25px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .amount-paid .label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }
    .amount-paid .amount {
        font-size: 32px;
        color: #4f46e5;
        font-weight: bold;
    }
    .no-print {
        text-align: center;
        margin-top: 20px;
    }
    @media print {
        body { background: white; }
        .no-print { display: none; }
        .receipt { box-shadow: none; margin: 0; }
    }
</style>
</head>
<body>
    <div class="page-title">
        <a href="customer_dashboard.php" class="brand">FundHarmony</a>
    </div>
    <div class="container py-3 text-center">
        <a href="customer_payment_history.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Payment History
        </a>
    </div>
    <div class="receipt" style="margin-top: 0;">
        <div class="receipt-header">
            <h2>FundHarmony</h2>
            <p>PAYMENT RECEIPT</p>
            <p>Receipt No: #<?php echo str_pad($row['payment_number'], 6, '0', STR_PAD_LEFT); ?></p>
        </div>
        
        <div class="receipt-detail">
            <span class="receipt-label">Account Number:</span>
            <span class="receipt-value"><?php echo $row['account_number']; ?></span>
        </div>
        <div class="receipt-detail">
            <span class="receipt-label">Account Type:</span>
            <span class="receipt-value"><?php echo $row['account_type']; ?></span>
        </div>
        <div class="receipt-detail">
            <span class="receipt-label">Payment Date:</span>
            <span class="receipt-value"><?php echo date('F d, Y', strtotime($row['payment_date'])); ?></span>
        </div>
        <div class="receipt-detail">
            <span class="receipt-label">Payment Method:</span>
            <span class="receipt-value"><?php echo $row['payment_method']; ?></span>
        </div>
        <?php if($row['notes']): ?>
        <div class="receipt-detail">
            <span class="receipt-label">Notes:</span>
            <span class="receipt-value"><?php echo $row['notes']; ?></span>
        </div>
        <?php endif; ?>
        
        <div class="amount-paid">
            <div class="label">Amount Paid</div>
            <div class="amount">₱<?php echo number_format($row['payment_amount'], 2); ?></div>
        </div>
        
        <?php if(isset($row['loan_balance'])): ?>
        <div class="receipt-detail">
            <span class="receipt-label">Remaining Balance:</span>
            <span class="receipt-value">₱<?php echo number_format($row['loan_balance'], 2); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <a href="customer_payment_history.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</body>
</html>
