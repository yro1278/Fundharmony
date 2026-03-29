<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
error_reporting(0);
ini_set('display_errors', 0);
require_once 'database/db_connection.php';

$payment_number = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$payment_number) {
    die('Payment ID is required');
}

$sql = "SELECT p.*, c.first_name, c.surname, c.middle_name, a.account_number, a.account_type, a.loan_balance
FROM payments p 
LEFT JOIN accounts a ON p.account_number = a.account_number 
LEFT JOIN customers c ON a.customer = c.customer_number 
WHERE p.payment_number = $payment_number";

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
    <div class="receipt">
        <div class="receipt-header">
            <h2>FundHarmony</h2>
            <p>PAYMENT RECEIPT</p>
            <p>Receipt No: #<?php echo str_pad($row['payment_number'], 6, '0', STR_PAD_LEFT); ?></p>
        </div>
        
        <div class="receipt-detail">
            <span class="receipt-label">Client Name:</span>
            <span class="receipt-value"><?php echo $row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['surname']; ?></span>
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
            <a href="managepayment.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</body>
</html>
