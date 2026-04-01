<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="navbar navbar-dark sticky-top flex-md-nowrap p-0 shadow navbar-lg" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important; z-index: 1030; position: fixed; width: 100%; display: none;">
  <?php 
  $lock_info = isset($_SESSION['locked_until']) ? $_SESSION['locked_until'] : null;
  if ($lock_info): 
  ?>
  <div class="lockout-timer-nav" style="background: #dc3545; color: white; padding: 5px 15px; border-radius: 8px; font-size: 12px; display: flex; align-items: center; gap: 8px; margin-left: 10px; font-weight: 500; white-space: nowrap; position: absolute; left: 200px; z-index: 999;">
    <i class="fas fa-clock"></i> <span id="admin-lockout-countdown" data-locktime="<?php echo $lock_info; ?>"></span>
  </div>
  <?php endif; ?>
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); left: 10px;">
    <span class="navbar-toggler-icon" style="transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);"></span>
  </button>
  
  <div class="d-flex align-items-center px-4 ms-auto">
    <div class="user-dropdown">
      <button class="user-btn" onclick="toggleDropdown()">
        <?php
        $display_name = 'Account';
        $display_email = '';
        if (isset($_SESSION['admin'])) {
            $display_name = $_SESSION['admin_name'] ?? $_SESSION['admin'] ?? 'Admin';
            $display_email = $_SESSION['admin_email'] ?? '';
        }
        ?>
        <i class="fas fa-user-circle"></i> 
        <span class="user-btn-text"><?php echo htmlspecialchars($display_name); ?><?php if($display_email): ?><small class="d-block" style="font-size:10px;opacity:0.8;"><?php echo htmlspecialchars($display_email); ?></small><?php endif; ?></span>
        <i class="fas fa-chevron-down"></i>
      </button>
      <?php 
      $lock_info = isset($_SESSION['locked_until']) ? $_SESSION['locked_until'] : null;
      if ($lock_info): 
      ?>
      <span id="admin-lockout-countdown" data-locktime="<?php echo $lock_info; ?>" style="background:#dc3545;color:white;padding:5px 10px;border-radius:6px;font-size:12px;font-weight:500;margin-right:8px;white-space:nowrap;"></span>
      <?php endif; ?>
      <div class="dropdown-content" id="userDropdown">
        <?php
        // Determine user info based on session
        $user_name = '';
        $user_email = '';
        $user_icon = 'fa-user';
        
        if (isset($_SESSION['admin'])) {
            // Admin user
            $user_name = $_SESSION['admin_name'] ?? $_SESSION['admin'] ?? 'Admin';
            $user_email = 'Administrator';
            $user_icon = 'fa-user-shield';
        } elseif (isset($_SESSION['customer_name'])) {
            // Customer user
            $user_name = $_SESSION['customer_name'] ?? 'Customer';
            $user_email = $_SESSION['customer_email'] ?? 'customer@email.com';
            $user_icon = 'fa-user';
        }
        ?>
        <!-- User Profile Section -->
        <div class="user-profile-section">
          <div class="profile-icon">
            <i class="fas <?php echo $user_icon; ?>"></i>
          </div>
          <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="profile-email"><?php echo htmlspecialchars($user_email); ?></div>
          </div>
        </div>
        <div class="divider"></div>
        <a href="#" onclick="toggleTheme(); return false;">
          <i class="fas fa-moon" id="theme-icon"></i>
          <span id="theme-text">Dark Mode</span>
        </a>
        <div class="divider"></div>
        <a href="/mims/logout.php" class="logout">
          <i class="fas fa-sign-out-alt"></i> Log out
        </a>
      </div>
    </div>
    </div>
  </div>
</header>

<style>
.user-dropdown {
  position: relative;
  margin-left: 10px;
  z-index: 100;
}

.user-btn {
  background: transparent;
  border: none;
  color: white;
  font-weight: 500;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  position: relative;
  z-index: 101;
}

.user-btn:hover {
  background: rgba(255,255,255,0.15);
}

.user-btn-text {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  line-height: 1.2;
}

.user-btn .fa-chevron-down {
  font-size: 10px;
  margin-top: auto;
  margin-bottom: auto;
}

.dropdown-content {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 8px;
  min-width: 220px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  opacity: 0;
  visibility: hidden;
  overflow: hidden;
  z-index: 100000;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.dropdown-content.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-content a {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  cursor: pointer;
}

.dropdown-content a:hover {
  background: #f3f4f6;
  color: #4f46e5;
}

body.dark-mode .dropdown-content {
  background: #1a1a2e;
  border: 1px solid #2d3748;
}

body.dark-mode .dropdown-content a {
  color: #e2e8f0;
}

body.dark-mode .dropdown-content a:hover {
  background: #2d3748;
  color: #818cf8;
}

body.dark-mode .dropdown-content a.logout {
  color: #f87171 !important;
}

body.dark-mode .dropdown-content a.logout:hover {
  background: #7f1d1d;
  color: #fca5a5 !important;
}

body.dark-mode .dropdown-content .divider {
  background: #2d3748;
}

body.dark-mode .user-profile-section {
  background: linear-gradient(135deg, #1e3a5f 0%, #172554 100%);
}

body.dark-mode .user-profile-section .profile-email {
  color: #94a3b8;
}

body.dark-mode .lockout-timer {
  background: #7f1d1d !important;
  color: #fca5a5 !important;
}

.dropdown-content a.logout {
  color: #dc2626;
}

.dropdown-content a.logout:hover {
  background: #fee2e2;
}

.dropdown-content a i {
  width: 20px;
  margin-right: 12px;
}

.dropdown-content .divider {
  height: 1px;
  background: #e5e7eb;
  margin: 4px 0;
}

/* User Profile Section */
.user-profile-section {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

.user-profile-section .profile-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  flex-shrink: 0;
}

.user-profile-section .profile-icon i {
  color: white;
  font-size: 20px;
}

.user-profile-section .profile-info {
  overflow: hidden;
}

.user-profile-section .profile-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 140px;
}

.user-profile-section .profile-email {
  font-size: 12px;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 140px;
}

/* Dark mode */
body.dark-mode .navbar {
    background: linear-gradient(135deg, #312e81 0%, #4338ca 100%) !important;
}
body.dark-mode .dropdown-content {
    background: #1e293b;
    border: 1px solid #334155;
}

body.dark-mode .dropdown-content a {
    color: #e2e8f0;
}

body.dark-mode .dropdown-content a:hover {
    background: #334155;
    color: #818cf8;
}

body.dark-mode .dropdown-content .divider {
    background: #334155;
}

body.dark-mode .user-btn {
    color: #f1f5f9;
}

body.dark-mode .user-profile-section {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

body.dark-mode .user-profile-section .profile-name {
  color: #f1f5f9;
}

body.dark-mode .user-profile-section .profile-email {
  color: #94a3b8;
}
</style>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userDropdown');
    const btn = document.querySelector('.user-btn');
    if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

function toggleTheme() {
    const body = document.body;
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');
    const text = document.getElementById('theme-text');
    
    body.classList.toggle('dark-mode');
    html.classList.toggle('dark-mode-bg');
    
    if (body.classList.contains('dark-mode')) {
        html.style.backgroundColor = '#0f172a';
        body.style.backgroundColor = '#0f172a';
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        text.textContent = 'Light Mode';
        localStorage.setItem('theme', 'dark');
    } else {
        html.style.backgroundColor = '#f8fafc';
        body.style.backgroundColor = '#f8fafc';
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        text.textContent = 'Dark Mode';
        localStorage.setItem('theme', 'light');
    }
    
    document.getElementById('userDropdown').classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    const icon = document.getElementById('theme-icon');
    const text = document.getElementById('theme-text');
    
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        document.documentElement.classList.add('dark-mode-bg');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        text.textContent = 'Light Mode';
    }
    
    const lockoutEl = document.getElementById('admin-lockout-countdown');
    if (lockoutEl) {
        const lockTime = new Date(lockoutEl.dataset.locktime).getTime();
        function updateLockout() {
            const now = new Date().getTime();
            const remaining = lockTime - now;
            if (remaining > 0) {
                const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((remaining % (1000 * 60)) / 1000);
                lockoutEl.textContent = minutes + 'm ' + (seconds < 10 ? '0' : '') + seconds + 's';
            } else {
                lockoutEl.style.display = 'none';
            }
        }
        updateLockout();
        setInterval(updateLockout, 1000);
    }
});
</script>
