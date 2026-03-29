<?php
session_start();
include_once 'database/db_connection.php';

if (!isset($_SESSION['fresh_login'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['otp_username'] ?? $_SESSION['admin'] ?? 'User';
$user_id = $_SESSION['otp_user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - FundHarmony</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/web-icon/css/all.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden !important;
            background: #0a0a0a;
            margin: 0 !important;
            padding: 0 !important;
            border: none;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0a0a0a !important;
            display: flex;
        }

        .welcome-container {
            width: 100%;
            height: 100%;
            display: flex;
            margin: 0 !important;
            padding: 0 !important;
        }

        .welcome-left {
            width: 45%;
            height: 100%;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .welcome-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(102, 126, 234, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(118, 75, 162, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        .welcome-left::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 60%);
            animation: shimmer 8s linear infinite;
        }

        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .left-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1);
            max-width: 500px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-logo {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 40px;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
            animation: float 4s ease-in-out infinite, glow 2s ease-in-out infinite alternate;
            transition: transform 0.3s ease;
        }

        .brand-logo:hover {
            transform: scale(1.05);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        @keyframes glow {
            from { box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3); }
            to { box-shadow: 0 20px 80px rgba(102, 126, 234, 0.5); }
        }

        .brand-logo i {
            font-size: 60px;
            color: white;
        }

        .welcome-left h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 52px;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, #e0e0e0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }

        .welcome-left .tagline {
            font-size: 22px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .welcome-left .description {
            font-size: 17px;
            line-height: 1.9;
            opacity: 0.8;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 50px;
            text-align: center;
        }

        .left-stats {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .number {
            font-family: 'Outfit', sans-serif;
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
        }

        .stat-item .label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .welcome-right {
            width: 55%;
            height: 100%;
            padding: 60px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #0a0a0a;
            position: relative;
        }

        .welcome-right::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .welcome-right::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.3), transparent);
        }

        .right-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
        }

        .welcome-header {
            margin-bottom: 40px;
        }

        .welcome-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 42px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }

        .welcome-header p {
            font-size: 17px;
            color: rgba(255, 255, 255, 0.6);
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .user-info {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 40px;
            border: 1px solid rgba(102, 126, 234, 0.2);
            animation: fadeInUp 0.8s ease-out 0.5s both;
            transition: all 0.3s ease;
        }

        .user-info:hover {
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
            border-color: rgba(102, 126, 234, 0.4);
        }

        .user-info .greeting {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .user-info .name {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 45px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 24px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            animation: fadeInUp 0.6s ease-out 0.6s both;
        }

        .feature-item:nth-child(2) { animation-delay: 0.65s; }
        .feature-item:nth-child(3) { animation-delay: 0.7s; }
        .feature-item:nth-child(4) { animation-delay: 0.75s; }

        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(102, 126, 234, 0.5);
        }

        .feature-item .icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .feature-item:hover .icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-item .text {
            flex: 1;
        }

        .feature-item .text strong {
            display: block;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .feature-item .text span {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        .continue-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 20px 40px;
            border-radius: 16px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            animation: fadeInUp 0.8s ease-out 0.8s both;
            position: relative;
            overflow: hidden;
        }

        .continue-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .continue-btn:hover::before {
            left: 100%;
        }

        .continue-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
        }

        .continue-btn i {
            transition: transform 0.3s ease;
            font-size: 18px;
        }

        .continue-btn:hover i {
            transform: translateX(8px);
        }

        @media (max-width: 1024px) {
            html, body {
                height: auto;
                overflow: auto;
            }

            body {
                flex-direction: column;
            }

            .welcome-container {
                height: auto;
            }

            .welcome-left, .welcome-right {
                width: 100%;
                height: auto;
                min-height: 100vh;
            }

            .welcome-left {
                padding: 80px 40px;
            }

            .welcome-right {
                padding: 60px 40px;
            }

            .left-stats {
                gap: 30px;
            }
        }

        @media (max-width: 768px) {
            .welcome-left h1 {
                font-size: 36px;
            }

            .welcome-left .tagline {
                font-size: 16px;
            }

            .welcome-header h2 {
                font-size: 32px;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .brand-logo {
                width: 100px;
                height: 100px;
                border-radius: 30px;
            }

            .brand-logo i {
                font-size: 45px;
            }

            .left-stats {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-left">
            <div class="left-content">
                <div class="brand-logo">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h1>FundHarmony</h1>
                <p class="tagline">Microfinance Management System</p>
                <p class="description">
                    Streamline your microfinance operations with our comprehensive platform. 
                    Manage customer accounts, process loan applications, track payments, 
                    and generate detailed reports — all in one powerful system designed for 
                    modern financial institutions.
                </p>
                <div class="left-stats">
                    <div class="stat-item">
                        <div class="number">10K+</div>
                        <div class="label">Customers</div>
                    </div>
                    <div class="stat-item">
                        <div class="number">₱50M+</div>
                        <div class="label">Loans Processed</div>
                    </div>
                    <div class="stat-item">
                        <div class="number">99.9%</div>
                        <div class="label">Uptime</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="welcome-right">
            <div class="right-content">
                <div class="welcome-header">
                    <h2>Welcome to FundHarmony!</h2>
                    <p>We're excited to have you here. Here's what you can do:</p>
                </div>
                
                <div class="user-info">
                    <div class="greeting">Hello,</div>
                    <div class="name"><?php echo htmlspecialchars($username); ?></div>
                </div>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <div class="text">
                            <strong>Manage Clients</strong>
                            <span>Add and manage customer records</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="text">
                            <strong>Loan Accounts</strong>
                            <span>Process and track loan applications</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="text">
                            <strong>Payments</strong>
                            <span>Record and track payments</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="icon"><i class="fas fa-chart-line"></i></div>
                        <div class="text">
                            <strong>Reports</strong>
                            <span>View analytics and reports</span>
                        </div>
                    </div>
                </div>
                
                <button class="continue-btn" onclick="window.location.href='dashboard.php'">
                    Continue to Dashboard <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
