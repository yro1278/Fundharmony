<!DOCTYPE html>
<html lang="en">
<head>
    <?php $freshLogin = isset($_SESSION['fresh_login']) && $_SESSION['fresh_login']; ?>
    <script>
        // Apply theme BEFORE anything else to prevent flash
        (function() {
            <?php if ($freshLogin): ?>
            // Reset to light mode on fresh login
            localStorage.setItem('theme', 'light');
            // Also reset sidebar state
            localStorage.setItem('openMenus', '[]');
            localStorage.setItem('sidebarState', 'expanded');
            <?php endif; ?>
            var theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode', 'dark-mode-bg');
                document.body.classList.add('dark-mode');
                // Set inline styles immediately
                document.documentElement.style.backgroundColor = "#0f172a";
                document.body.style.backgroundColor = "#0f172a";
            }
        })();
    </script>
    <style>
        /* Force dark mode backgrounds immediately */
        html.dark-mode, html.dark-mode-bg { background-color: #0f172a !important; }
        html.dark-mode body, html.dark-mode-bg body { background-color: #0f172a !important; }
        
        /* Default light mode - but allow dark mode to override */
        html:not(.dark-mode):not(.dark-mode-bg) { background-color: #f8fafc; }
        html:not(.dark-mode):not(.dark-mode-bg) body { background-color: #f8fafc; }
        
        /* Container-fluid override for dark mode */
        body.dark-mode .container-fluid,
        html.dark-mode .container-fluid,
        html.dark-mode-bg .container-fluid {
            background-color: #0f172a !important;
        }
    </style>
   <title>Microfinance Management System</title>
   <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">
   <link rel="apple-touch-icon" href="assets/img/favicon.png">
   <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="description" content="Online microfinance management system, Bank management system, Loan management system">

   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <link rel="stylesheet" href="assets/web-icon/css/all.css">
   <link rel="stylesheet" href="assets/web-icon/css/all.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <link rel="stylesheet" href="assets/vendor/css/bootstrap.css">
   <link rel="stylesheet" href="assets/vendor/css/bootstrap.min.css">

   <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="assets/css/dashboard.css" rel="stylesheet">

   <link rel="stylesheet" href="assets/css/adminlte.min.css">

   <link rel="stylesheet" href="assets/css/custom_style.css">
   
      <style>
          * { font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
          body, html { font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
          body.dark-mode, html.dark-mode, html.dark-mode-bg, body.dark-mode .container-fluid, html.dark-mode .container-fluid, html.dark-mode-bg .container-fluid {
              background-color: #0f172a !important;
          }
          /* Fix for modal textarea not being interactive */
          .modal textarea.form-control {
              pointer-events: auto !important;
              opacity: 1 !important;
          }
      </style>
     <?php if ($freshLogin): unset($_SESSION['fresh_login']); endif; ?>
</head>
