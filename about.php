<?php
session_start();

$session_timeout = 86400;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - FundHarmony</title>
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">
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
            min-height: 100vh;
            background: #0a0a0a;
            overflow-x: auto;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0a0a0a;
            position: relative;
        }

        .intro-section {
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 60px 40px;
            position: relative;
        }

        .brand-logo {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.5);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        .brand-logo i {
            font-size: 65px;
            color: white;
        }

        .intro-section h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 60px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 15px;
        }

        .intro-section .tagline {
            font-size: 20px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .scroll-indicator i {
            font-size: 20px;
            animation: bounce 1.5s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(8px); }
        }

        .main-content {
            position: relative;
            z-index: 1;
            margin-top: 0;
        }

        .content-section {
            width: 100%;
            min-height: 100vh;
            background: #0a0a0a;
            padding: 80px 40px;
            display: flex;
            justify-content: center;
        }

        .content-inner {
            max-width: 800px;
            width: 100%;
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.6s ease;
        }

        .content-inner.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 35px;
            padding-bottom: 12px;
            border-bottom: 3px solid #667eea;
        }

        .developer-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 24px;
            padding: 45px;
            border: 1px solid rgba(102, 126, 234, 0.2);
            text-align: center;
        }

        .developer-card .developer-img {
            width: 150px;
            height: 150px;
            margin: 0 auto 25px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid #667eea;
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .developer-card .developer-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .developer-card .developer-fallback {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .developer-card .developer-fallback i {
            font-size: 55px;
            color: white;
        }

        .developer-card h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .developer-card .role {
            font-size: 16px;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .developer-card .school {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
        }

        .developer-card .description {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.75);
            line-height: 1.8;
        }

        .system-info {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(102, 126, 234, 0.4);
        }

        .info-card .icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .info-card .icon i {
            font-size: 26px;
            color: white;
        }

        .info-card h4 {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
        }

        .info-card p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }

        @media (max-width: 1024px) {
            .system-info {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .system-info {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1024px) {
            .system-info {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .system-info {
                grid-template-columns: 1fr;
            }
        }

        .info-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card .icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .info-card .icon i {
            font-size: 26px;
            color: white;
        }

        .info-card h4 {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
        }

        .info-card p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-section {
            background: #0a0a0a;
            padding: 60px 40px;
            text-align: center;
            border-top: 1px solid rgba(102, 126, 234, 0.2);
        }

        .footer-section p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
        }

        .footer-section .copyright {
            font-weight: 700;
            color: #667eea;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 30px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            .intro-section h1 { font-size: 40px; }
            .system-info { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="intro-section" id="introSection">
        <div class="brand-logo">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <h1>FundHarmony</h1>
        <p class="tagline">Microfinance Management System</p>
        <div class="scroll-indicator">
            <span>Scroll down</span>
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
    
    <div class="main-content">
        <div class="content-section">
            <div class="content-inner" id="content1">
                <h2 class="section-title">Meet the Developer</h2>
                <div class="developer-card">
                    <div class="developer-img">
                        <img src="assets/img/tyrone.jpg" alt="Tyrone Alariao" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="developer-fallback">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <h3>Tyrone Alariao</h3>
                    <p class="role">Lead Developer & System Architect</p>
                    <p class="school">BSIT Student</p>
                    <p class="school">Bestlink College of the Philippines</p>
                    <p class="description">
                        A dedicated IT student with a passion for building innovative financial solutions. 
                        Developed FundHarmony as a capstone project to demonstrate expertise in web development, 
                        database management, and system design.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="content-section">
            <div class="content-inner" id="content2">
                <h2 class="section-title">System Information</h2>
                <div class="system-info">
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-code"></i></div>
                        <h4>Technology</h4>
                        <p>PHP, MySQL, JavaScript</p>
                    </div>
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-paint-brush"></i></div>
                        <h4>Design</h4>
                        <p>Modern UI/UX</p>
                    </div>
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-shield-alt"></i></div>
                        <h4>Security</h4>
                        <p>OTP Authentication</p>
                    </div>
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-chart-line"></i></div>
                        <h4>Analytics</h4>
                        <p>Real-time Reports</p>
                    </div>
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-database"></i></div>
                        <h4>Database</h4>
                        <p>MariaDB Storage</p>
                    </div>
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                        <h4>Responsive</h4>
                        <p>Mobile Friendly</p>
                    </div>
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-bolt"></i></div>
                        <h4>Performance</h4>
                        <p>Fast & Efficient</p>
                    </div>
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-envelope"></i></div>
                        <h4>Notifications</h4>
                        <p>Email Alerts</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-section">
            <p>&copy; 2026 <span class="copyright">FundHarmony</span>. All rights reserved.</p>
            <p style="margin-top: 8px; color: rgba(255,255,255,0.5); font-size: 13px;">Designed & Developed by Tyrone Alariao</p>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        const introSection = document.getElementById('introSection');
        const content1 = document.getElementById('content1');
        const content2 = document.getElementById('content2');

        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            const windowHeight = window.innerHeight;
            
            // Fade intro section as you scroll
            if (scrollY < windowHeight) {
                const progress = scrollY / windowHeight;
                introSection.style.opacity = 1 - progress;
                introSection.style.transform = `scale(${1 - progress * 0.1})`;
            } else {
                introSection.style.opacity = 0;
                introSection.style.transform = 'scale(0.9)';
            }
            
            // Show content sections
            if (scrollY > windowHeight * 0.3) {
                content1.classList.add('visible');
            }
            
            if (scrollY > windowHeight * 0.7) {
                content2.classList.add('visible');
            }
        });
    </script>
</body>
</html>
