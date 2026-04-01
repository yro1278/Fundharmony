<nav id="sidebarMenu" class="sidebar collapse show">
  <div class="sidebar-header">
    <div class="sidebar-brand" style="justify-content: center; padding: 20px 0; gap: 12px;">
      <div class="brand-logo" style="width: 45px; height: 45px; background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);">
        <i class="fas fa-hand-holding-usd" style="font-size: 22px; color: white;"></i>
      </div>
      <span style="font-size: 22px; font-weight: 800; background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">FundHarmony</span>
    </div>
  </div>
  
  <div class="sidebar-inner">
    <!-- Dashboard -->
    <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
      <div class="link-icon"><i class="fas fa-grip-horizontal"></i></div>
      <span>Dashboard</span>
    </a>

    <!-- Activity Logs -->
    <a href="activity_logs.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'active' : ''; ?>">
      <div class="link-icon"><i class="fas fa-clock"></i></div>
      <span>Activity Logs</span>
    </a>

    <div class="menu-divider"></div>
    <div class="menu-section-label">Management</div>

    <!-- Client Registration -->
    <div class="menu-item">
      <div class="menu-trigger" data-target="client-section">
        <div class="menu-icon"><i class="fas fa-user-friends"></i></div>
        <span class="menu-text">Client Registration</span>
        <i class="fas fa-angle-right menu-arrow"></i>
      </div>
      <ul class="submenu" id="client-section">
        <li><a href="addcustomer.php"><i class="fas fa-plus"></i> Add Client</a></li>
        <li><a href="managecustomer.php"><i class="fas fa-users-cog"></i> Manage Clients</a></li>
      </ul>
    </div>

    <!-- Loan Application -->
    <div class="menu-item">
      <div class="menu-trigger" data-target="loan-section">
        <div class="menu-icon"><i class="fas fa-coins"></i></div>
        <span class="menu-text">Loan Application</span>
        <i class="fas fa-angle-right menu-arrow"></i>
      </div>
      <ul class="submenu" id="loan-section">
        <li><a href="openaccount.php"><i class="fas fa-file-signature"></i> Apply for Loan</a></li>
        <li><a href="manageaccount.php"><i class="fas fa-list-ul"></i> Manage Loans</a></li>
        <li><a href="loan_approvals.php"><i class="fas fa-clipboard-check"></i> Loan Approvals</a></li>
        <li><a href="addaccount_type.php"><i class="fas fa-tags"></i> Loan Type</a></li>
        <li><a href="addaccount_status.php"><i class="fas fa-check-double"></i> Loan Status</a></li>
      </ul>
    </div>

    <!-- Repayment Entry -->
    <div class="menu-item">
      <div class="menu-trigger" data-target="repayment-section">
        <div class="menu-icon"><i class="fas fa-money-bill-wave"></i></div>
        <span class="menu-text">Repayment Entry</span>
        <i class="fas fa-angle-right menu-arrow"></i>
      </div>
      <ul class="submenu" id="repayment-section">
        <li><a href="addpayment.php"><i class="fas fa-plus-circle"></i> Enter Payment</a></li>
        <li><a href="managepayment.php"><i class="fas fa-history"></i> Payment History</a></li>
      </ul>
    </div>



    <!-- Settings -->
    <div class="menu-item">
      <div class="menu-trigger" data-target="settings-section">
        <div class="menu-icon"><i class="fas fa-sliders-h"></i></div>
        <span class="menu-text">Settings</span>
        <i class="fas fa-angle-right menu-arrow"></i>
      </div>
      <ul class="submenu" id="settings-section">
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
        <li><a href="cleardata.php"><i class="fas fa-trash-restore"></i> Clear All Data</a></li>
      </ul>
    </div>
  </div>
  
  <div class="sidebar-footer">
    <div class="user-info mb-3">
      <div class="user-avatar">
        <i class="fas fa-user"></i>
      </div>
      <div class="user-details">
        <span class="user-name"><?php echo isset($_SESSION['admin']) ? htmlspecialchars($_SESSION['admin']) : 'Admin'; ?></span>
        <span class="user-role">Administrator</span>
      </div>
    </div>
    
    <!-- Theme Toggle -->
    <div class="theme-toggle mb-2" onclick="toggleTheme()" style="cursor: pointer; padding: 10px; background: var(--sidebar-hover); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
      <span class="theme-label" id="theme-text" style="font-size: 12px; font-weight: 600; color: #1f2937;"><i class="fas fa-moon me-2"></i>Theme</span>
      <div class="theme-switch" style="width: 40px; height: 22px; background: var(--sidebar-active); border-radius: 11px; position: relative;">
        <div class="theme-slider" style="width: 18px; height: 18px; background: white; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: 0.3s;"></div>
      </div>
    </div>
    
    <!-- Logout -->
    <a href="/mims/logout.php" class="logout-btn" style="display: flex; align-items: center; justify-content: center; padding: 10px; background: #fee2e2; color: #dc2626; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 500; transition: all 0.2s;">
      <i class="fas fa-sign-out-alt me-2"></i> Logout
    </a>
  </div>
</nav>

<style>
:root {
  --sidebar-bg: #e5e7eb;
  --sidebar-bg-dark: #1e293b;
  --sidebar-hover: #d1d5db;
  --sidebar-hover-dark: #334155;
  --sidebar-active: #4f46e5;
  --sidebar-active-light: rgba(79, 70, 229, 0.15);
  --sidebar-text: #374151;
  --sidebar-text-dark: #cbd5e1;
  --sidebar-border: #d1d5db;
  --sidebar-border-dark: #334155;
}

.sidebar {
  background-color: var(--sidebar-bg) !important;
  border-right: 1px solid var(--sidebar-border);
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  width: 220px;
  z-index: 1000;
  display: flex !important;
  flex-direction: column;
}

body.dark-mode .sidebar {
  background-color: var(--sidebar-bg-dark) !important;
}

.sidebar-header {
  padding: 15px;
  border-bottom: 1px solid var(--sidebar-border);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-content: center;
}

.brand-logo {
  width: 45px;
  height: 45px;
  background: linear-gradient(135deg, #4f46e5 0%, #8b5cf6 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
}

.sidebar-inner {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
}

.sidebar-link {
  display: flex;
  align-items: center;
  padding: 8px 10px;
  color: var(--sidebar-text);
  text-decoration: none;
  font-weight: 500;
  font-size: 12px;
  margin: 1px 0;
  border-radius: 6px;
  gap: 8px;
}

.sidebar-link:hover {
  background-color: var(--sidebar-hover);
  color: var(--sidebar-active);
}

.sidebar-link.active {
  background-color: var(--sidebar-active-light);
  color: var(--sidebar-active);
  font-weight: 600;
}

.sidebar-link .link-icon {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 13px;
}

.sidebar-link:nth-child(3) .link-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.sidebar-link:nth-child(4) .link-icon { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }

.menu-divider {
  height: 1px;
  background: var(--sidebar-border);
  margin: 8px 0;
}

.menu-section-label {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #9ca3af;
  padding: 8px 10px 4px;
}

.menu-item {
  margin-bottom: 2px;
}

.menu-trigger {
  display: flex;
  align-items: center;
  padding: 8px 10px;
  cursor: pointer;
  border-radius: 6px;
  gap: 8px;
}

.menu-trigger:hover {
  background-color: var(--sidebar-hover);
}

.menu-trigger.active {
  background-color: var(--sidebar-active-light);
}

.menu-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  color: white;
  font-size: 13px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.menu-item:nth-child(6) .menu-icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.menu-item:nth-child(7) .menu-icon { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
.menu-item:nth-child(8) .menu-icon { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.menu-item:nth-child(9) .menu-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.menu-item:nth-child(11) .menu-icon { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }

.menu-text {
  flex: 1;
  font-size: 12px;
  font-weight: 500;
  color: var(--sidebar-text);
}

.menu-arrow {
  font-size: 10px;
  color: #9ca3af;
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-trigger.active .menu-arrow {
  transform: rotate(90deg);
}

.submenu {
  list-style: none;
  padding: 0;
  margin: 0;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
  opacity: 0;
}

.submenu.open {
  max-height: 500px;
  opacity: 1;
}

.submenu li a {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px 6px 42px;
  color: var(--sidebar-text);
  text-decoration: none;
  font-size: 11px;
  border-radius: 4px;
  margin: 1px 0;
}

.submenu li a:hover {
  background-color: var(--sidebar-hover);
  color: var(--sidebar-active);
}

.submenu li a i {
  width: 16px;
  font-size: 10px;
  color: #9ca3af;
}

.sidebar-footer {
  padding: 10px;
  border-top: 1px solid var(--sidebar-border);
  background: #e2e8f0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 12px;
  font-weight: 600;
  color: var(--sidebar-text);
}

.user-role {
  font-size: 10px;
  color: #9ca3af;
}

.logout-btn:hover {
  background: #dc2626 !important;
  color: white !important;
}

.theme-toggle:hover {
  background: #cbd5e1 !important;
}

/* Dark Mode */
body.dark-mode .theme-label,
body.dark-mode .theme-label i {
  color: #ffffff !important;
  text-shadow: 0 0 10px rgba(255,255,255,0.3);
}

body.dark-mode .theme-toggle {
  background: #334155 !important;
}

body.dark-mode .theme-toggle:hover {
  background: #475569 !important;
}

body.dark-mode .sidebar-link:hover {
  background-color: #334155;
  color: #818cf8;
}

body.dark-mode .sidebar-link.active {
  background-color: rgba(129, 140, 248, 0.15);
  color: #818cf8;
}

body.dark-mode .menu-trigger:hover {
  background-color: #334155;
}

body.dark-mode .menu-trigger.active {
  background-color: rgba(129, 140, 248, 0.15);
}

body.dark-mode .sidebar-footer {
  background: #0f172a;
}

body.dark-mode .menu-text {
  color: var(--sidebar-text-dark);
}

body.dark-mode .submenu li a {
  color: var(--sidebar-text-dark);
}

body.dark-mode .submenu li a:hover {
  background: var(--sidebar-hover-dark);
  color: #818cf8;
}

body.dark-mode .sidebar-header {
  border-bottom-color: #334155;
}

body.dark-mode .brand-logo {
  background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
  box-shadow: 0 4px 20px rgba(129, 140, 248, 0.6);
}

body.dark-mode .sidebar-brand span {
  background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-shadow: 0 0 30px rgba(129, 140, 248, 0.5);
  filter: drop-shadow(0 0 8px rgba(129, 140, 248, 0.4));
}

body.dark-mode .sidebar-link .link-icon {
  background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
}

body.dark-mode .menu-icon {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

body.dark-mode .menu-item:nth-child(7) .menu-icon {
  background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
}

body.dark-mode .menu-item:nth-child(8) .menu-icon {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
}

body.dark-mode .menu-item:nth-child(9) .menu-icon {
  background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
}

body.dark-mode .menu-item:nth-child(11) .menu-icon {
  background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
}

body.dark-mode .submenu li a i {
  color: #94a3b8;
}

body.dark-mode .submenu li a {
  color: #cbd5e1;
  background: transparent;
}

body.dark-mode .submenu li a:hover {
  color: #818cf8;
  background: #334155;
}

body.dark-mode .menu-arrow {
  color: #94a3b8;
}

body.dark-mode .menu-trigger.active .menu-arrow {
  color: #818cf8;
}

body.dark-mode .menu-section-label {
  color: #64748b;
}

body.dark-mode .menu-divider {
  background: #334155;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Load saved menu state from localStorage
  const savedMenus = JSON.parse(localStorage.getItem('openMenus') || '[]');
  savedMenus.forEach(function(menuId) {
    const submenu = document.getElementById(menuId);
    const trigger = document.querySelector('[data-target="' + menuId + '"]');
    if (submenu) submenu.classList.add('open');
    if (trigger) trigger.classList.add('active');
  });

  // Menu toggle click handler - stays open until manually closed
  document.querySelectorAll('.menu-trigger').forEach(function(trigger) {
    trigger.addEventListener('click', function(e) {
      e.stopPropagation();
      const targetId = this.getAttribute('data-target');
      const submenu = document.getElementById(targetId);
      
      // Toggle current submenu only (doesn't close others)
      if (submenu) {
        submenu.classList.toggle('open');
        this.classList.toggle('active');
        
        // Save state to localStorage
        const openMenus = [];
        document.querySelectorAll('.submenu.open').forEach(function(sm) {
          openMenus.push(sm.id);
        });
        localStorage.setItem('openMenus', JSON.stringify(openMenus));
      }
    });
  });
  
  // Theme toggle
  window.toggleTheme = function() {
    const body = document.body;
    const html = document.documentElement;
    const slider = document.querySelector('.theme-slider');
    const themeText = document.getElementById('theme-text');
    
    if (body.classList.contains('dark-mode')) {
      body.classList.remove('dark-mode');
      html.classList.remove('dark-mode', 'dark-mode-bg');
      slider.style.left = '2px';
      if (themeText) themeText.style.color = '#1f2937';
      localStorage.setItem('theme', 'light');
    } else {
      body.classList.add('dark-mode');
      html.classList.add('dark-mode', 'dark-mode-bg');
      slider.style.left = '20px';
      if (themeText) themeText.style.color = '#ffffff';
      localStorage.setItem('theme', 'dark');
    }
  };
  
  // Load saved theme
  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
    document.documentElement.classList.add('dark-mode', 'dark-mode-bg');
    const slider = document.querySelector('.theme-slider');
    const themeText = document.getElementById('theme-text');
    if (slider) slider.style.left = '20px';
    if (themeText) themeText.style.color = '#ffffff';
  } else {
    const themeText = document.getElementById('theme-text');
    if (themeText) themeText.style.color = '#1f2937';
  }
});
</script>
