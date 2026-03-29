<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Flowchart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .flowchart-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            color: #4f46e5;
            text-align: center;
            margin-bottom: 30px;
        }
        .step {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step-start {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            font-size: 1.2em;
        }
        .step-end {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            font-size: 1.2em;
        }
        .step-action {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .step-sub {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #e2e8f0;
            margin-left: 30px;
            font-size: 0.9em;
            padding: 10px 20px;
        }
        .step-decision {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        .step-result {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            margin-left: 60px;
        }
        .arrow {
            text-align: center;
            color: #94a3b8;
            font-size: 20px;
            margin: 5px 0;
        }
        .arrow::before {
            content: "⬇";
        }
        .divider {
            height: 3px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="flowchart-container">
        <div class="d-flex align-items-center mb-4">
          <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-sitemap text-white" style="font-size: 22px;"></i>
          </div>
          <div>
            <h1 class="mb-0">Customer Flowchart</h1>
          </div>
        </div>
        
        <!-- START -->
        <div class="step step-start">
            <i class="fas fa-play"></i> START - Customer Login / Register
        </div>
        <div class="arrow"></div>
        
        <!-- Dashboard -->
        <div class="step">
            <i class="fas fa-home"></i> Customer Dashboard
        </div>
        <div class="arrow"></div>
        
        <!-- Option 1: View Profile -->
        <div class="step step-action">
            <i class="fas fa-user"></i> 1. View Profile
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-eye"></i> View Personal Information
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-edit"></i> Update Profile (Optional)
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-save"></i> Save Changes
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-undo"></i> Return to Dashboard
        </div>
        
        <div class="divider"></div>
        
        <!-- Option 2: Apply for Loan -->
        <div class="step step-action">
            <i class="fas fa-file-invoice-dollar"></i> 2. Apply for Loan
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-list"></i> Select Loan Type
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-calculator"></i> Enter Loan Details (Amount, Term)
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-paper-plane"></i> Submit Application
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-cog"></i> System Computes Estimated Payment
        </div>
        <div class="arrow"></div>
        <div class="step step-decision">
            <i class="fas fa-question-circle"></i> Application Status
        </div>
        <div class="arrow"></div>
        <div class="step step-result">
            <i class="fas fa-clock"></i> Pending
        </div>
        <div class="step step-result">
            <i class="fas fa-check-circle"></i> Approved
        </div>
        <div class="step step-result">
            <i class="fas fa-times-circle"></i> Rejected
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-undo"></i> Return to Dashboard
        </div>
        
        <div class="divider"></div>
        
        <!-- Option 3: View Loan -->
        <div class="step step-action">
            <i class="fas fa-money-check-alt"></i> 3. View Loan
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-hand-pointer"></i> Select Active Loan
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-chart-line"></i> View: Loan Balance, Interest, Due Date, Repayment Schedule
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-undo"></i> Return to Dashboard
        </div>
        
        <div class="divider"></div>
        
        <!-- Option 4: Make Payment -->
        <div class="step step-action">
            <i class="fas fa-credit-card"></i> 4. Make Payment
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-hand-pointer"></i> Select Loan
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-money-bill-wave"></i> Enter Payment Amount
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-wallet"></i> Choose Payment Method
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-check-circle"></i> Confirm Payment
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-bell"></i> Receive Payment Confirmation
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-chart-bar"></i> Updated Balance Displayed
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-undo"></i> Return to Dashboard
        </div>
        
        <div class="divider"></div>
        
        <!-- Option 5: View Payment History -->
        <div class="step step-action">
            <i class="fas fa-history"></i> 5. View Payment History
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-list"></i> View List of Previous Payments
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-file-pdf"></i> Download Receipt (PDF / View)
        </div>
        <div class="arrow"></div>
        <div class="step step-sub">
            <i class="fas fa-undo"></i> Return to Dashboard
        </div>
        
        <div class="divider"></div>
        
        <!-- Option 6: Logout -->
        <div class="step step-action">
            <i class="fas fa-sign-out-alt"></i> 6. Logout
        </div>
        <div class="arrow"></div>
        
        <!-- END -->
        <div class="step step-end">
            <i class="fas fa-stop"></i> END
        </div>
    </div>
</body>
</html>
