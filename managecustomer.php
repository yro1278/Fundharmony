<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/db_connection.php';
require_once 'include/head.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = "1=1";
if($search) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where .= " AND (first_name LIKE '%$search_escaped%' OR surname LIKE '%$search_escaped%' OR middle_name LIKE '%$search_escaped%' OR customer_number LIKE '%$search_escaped%' OR EXISTS (SELECT 1 FROM accounts WHERE customer = customers.customer_number AND account_number LIKE '%$search_escaped%'))";
}

$retrieve = mysqli_query($conn, "SELECT * FROM customers WHERE $where ORDER BY registration_date DESC");
$total = mysqli_num_rows($retrieve);

// Ensure is_active column exists
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'is_active'");
if(mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE customers ADD COLUMN is_active TINYINT(1) DEFAULT 1");
}
?>

<body>
  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="fas fa-users-cog text-white" style="font-size: 22px;"></i>
            </div>
            <h1 class="mb-0">Manage Clients</h1>
          </div>
          <div class="page-actions">
            <a href="addcustomer.php" class="btn btn-primary btn-sm">
              <i class="fas fa-plus"></i> Add Client
            </a>
          </div>
        </div>

        <div class="form-panel mb-4">
          <div class="form-panel-body py-3">
            <form method="GET" action="" class="row g-3 mb-0">
              <div class="col-md-10">
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                  <input type="text" name="search" class="form-control" placeholder="Search by name, client ID, or account ID..." value="<?php echo $search; ?>">
                </div>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-search"></i> Search
                </button>
              </div>
            </form>
          </div>
        </div>

        <?php if($total > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
          <?php
          while ($result = mysqli_fetch_assoc($retrieve)):
            $fullname = $result['first_name'] . ' ' . ($result['middle_name'] ? $result['middle_name'] . ' ' : '') . $result['surname'];
          ?>
          <div class="col">
            <div class="client-card-box">
              <div class="card-top">
                <div class="client-avatar" onclick="showClientModal(<?php echo $result['customer_number']; ?>)" style="cursor: pointer;" title="View Details">
                  <?php echo strtoupper(substr($result['first_name'], 0, 1)); ?>
                </div>
                <div class="client-details">
                  <h4><?php echo htmlspecialchars($fullname); ?></h4>
                  <span class="id">#<?php echo htmlspecialchars($result["customer_number"]); ?></span>
                  <?php $is_active = isset($result["is_active"]) ? $result["is_active"] : 1; ?>
                  <span class="badge <?php echo $is_active ? 'bg-success' : 'bg-danger'; ?> ms-2">
                    <?php echo $is_active ? 'Active' : 'Deactivated'; ?>
                  </span>
                </div>
              </div>
              
              <div class="card-body-box">
                <div class="info-line">
                  <i class="fas fa-envelope"></i>
                  <span><?php echo $result["email"] ?: '-'; ?></span>
                </div>
                <div class="info-line">
                  <i class="fas fa-phone"></i>
                  <span><?php echo $result["phone"] ?: '-'; ?></span>
                </div>
                <div class="info-line">
                  <i class="fas fa-map-marker-alt"></i>
                  <span>
                    <?php 
                      $addr = [];
                      if($result["barangay"]) $addr[] = $result["barangay"];
                      if($result["city"]) $addr[] = $result["city"];
                      echo $addr ? implode(', ', $addr) : '-';
                    ?>
                  </span>
                </div>
                <div class="info-line">
                  <i class="fas fa-calendar-alt"></i>
                  <span><?php echo date('M d, Y', strtotime($result["registration_date"])); ?></span>
                </div>
                <?php if(!empty($result["last_login"])): ?>
                <div class="info-line">
                  <i class="fas fa-sign-in-alt"></i>
                  <span>Last login: <?php echo date('M d, Y g:i A', strtotime($result["last_login"])); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-line">
                  <i class="fas fa-info-circle"></i>
                  <span style="color: #667eea; font-weight: 500;">Click avatar to view full details</span>
                </div>
                  
                <div class="card-bottom">
                  <?php if($is_active): ?>
                  <a href="app/toggle_customer_status.php?customer_number=<?php echo $result["customer_number"]; ?>&action=deactivate"
                     class="btn btn-warning btn-sm"
                     onclick="var reason=prompt('Enter reason for deactivation (optional):'); if(reason!==null){this.href+='&reason='+encodeURIComponent(reason);return true;}return false;">
                    <i class="fas fa-ban"></i> Deactivate
                  </a>
                  <?php else: ?>
                  <a href="app/toggle_customer_status.php?customer_number=<?php echo $result["customer_number"]; ?>&action=activate"
                     class="btn btn-success btn-sm"
                     onclick="return confirm('Activate this account? User will be able to login again.')">
                    <i class="fas fa-check"></i> Activate
                  </a>
                  <?php endif; ?>
                </div>
                 
              </div>

            </div>
          </div>
          <?php endwhile; ?>
        </div>
        
        <div class="mt-4 text-muted small text-center">
          Showing <?php echo $total; ?> client(s)
        </div>
        
        <?php else: ?>
        <div class="form-panel">
          <div class="form-panel-body empty-state py-5">
            <i class="fas fa-user-slash"></i>
            <h4>No Clients Found</h4>
            <p>No clients match your search.</p>
            <a href="addcustomer.php" class="btn btn-primary">
              <i class="fas fa-plus"></i> Add Client
            </a>
          </div>
        </div>
        <?php endif; ?>
        
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
body.dark-mode .card-body {
    color: #e2e8f0 !important;
    background: #1e293b !important;
}
body.dark-mode .form-panel {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .form-panel-body {
    background: #1e293b !important;
}
body.dark-mode .page-title-section h1,
body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
    color: #f1f5f9 !important;
}
.report-icon {
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    animation: slideInLeft 0.5s ease-out forwards;
}
.report-icon i {
    width: auto;
    height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.6s ease-out 0.2s forwards;
    opacity: 0;
}
.page-title-section h1 {
    animation: slideInUp 0.5s ease-out 0.1s forwards;
    opacity: 0;
}
.page-actions {
    animation: slideInRight 0.5s ease-out 0.2s forwards;
    opacity: 0;
}
@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-30px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(30px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes slideInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
body.dark-mode p, body.dark-mode span, body.dark-mode div, body.dark-mode strong {
    color: #e2e8f0 !important;
}
body.dark-mode .text-muted {
    color: #94a3b8 !important;
}
body.dark-mode .form-select,
body.dark-mode .form-control,
body.dark-mode .input-group-text {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .form-select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}
body.dark-mode .table {
    color: #e2e8f0 !important;
    background: #1e293b !important;
}
body.dark-mode .table thead th {
    background: #1e293b !important;
    color: #94a3b8 !important;
    border-color: #334155 !important;
}
body.dark-mode .table tbody tr {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .table tbody td {
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .btn-outline-primary,
body.dark-mode .btn-outline-danger {
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .btn-outline-primary:hover,
body.dark-mode .btn-outline-danger:hover {
    background: #334155 !important;
}
body.dark-mode .empty-state {
    color: #94a3b8 !important;
}
body.dark-mode .client-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .client-info h5,
body.dark-mode .client-info p {
    color: #e2e8f0 !important;
}
body.dark-mode .input-group-text {
    color: #94a3b8 !important;
}
body.dark-mode .client-card {
    background: #1e293b !important;
    color: #e2e8f0 !important;
}
body.dark-mode .client-info {
    color: #e2e8f0 !important;
}
body.dark-mode .badge {
    background: #334155 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .badge.bg-success {
    background: #22c55e !important;
    color: white !important;
}
body.dark-mode .badge.bg-danger {
    background: #ef4444 !important;
    color: white !important;
}
body.dark-mode .client-card-box {
    background: #1e293b !important;
    border-color: #334155 !important;
}
body.dark-mode .client-card-box:hover {
    border-color: #6366f1 !important;
}
body.dark-mode .card-top {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    border-color: #334155 !important;
}
body.dark-mode .client-avatar {
    background: rgba(255, 255, 255, 0.2) !important;
    transition: transform 0.2s, box-shadow 0.2s;
}
body.dark-mode .client-avatar:hover {
    transform: scale(1.1);
    box-shadow: 0 0 15px rgba(255,255,255,0.3);
}
body.dark-mode .client-details h4 {
    color: #f1f5f9 !important;
}
body.dark-mode .client-details .id {
    color: #94a3b8 !important;
}
body.dark-mode .card-body-box {
    background: #1e293b !important;
}
body.dark-mode .info-line {
    color: #e2e8f0 !important;
}
body.dark-mode .info-line i {
    color: #94a3b8 !important;
}
body.dark-mode .info-line span {
    color: #e2e8f0 !important;
}
body.dark-mode .card-bottom {
    background: #1e293b !important;
    border-color: #334155 !important;
    position: relative;
    z-index: 10;
}
body.dark-mode .card-bottom a {
    position: relative;
    z-index: 11;
    pointer-events: auto;
}
body.dark-mode .btn-edit {
    background: #334155 !important;
    color: #f1f5f9 !important;
    border-color: #475569 !important;
}
body.dark-mode .btn-delete {
    background: #7f1d1d !important;
    color: #fca5a5 !important;
}
</style>
<script>
function toggleEmergency(id) {
    var section = document.getElementById('emergency-' + id);
    var icon = document.getElementById('emergency-icon-' + id);
    if (section.style.display === 'none') {
        section.style.display = 'block';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    } else {
        section.style.display = 'none';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    }
}

var clientData = {};

<?php
mysqli_data_seek($retrieve, 0);
while ($result = mysqli_fetch_assoc($retrieve)): 
    $addr = [];
    if($result["barangay"]) $addr[] = $result["barangay"];
    if($result["city"]) $addr[] = $result["city"];
    if($result["region"]) $addr[] = $result["region"];
    $full_address = implode(', ', $addr);
?>
clientData[<?php echo $result['customer_number']; ?>] = {
    first_name: "<?php echo htmlspecialchars($result['first_name'] ?? ''); ?>",
    middle_name: "<?php echo htmlspecialchars($result['middle_name'] ?? ''); ?>",
    surname: "<?php echo htmlspecialchars($result['surname'] ?? ''); ?>",
    customer_number: "<?php echo htmlspecialchars($result['customer_number'] ?? ''); ?>",
    email: "<?php echo htmlspecialchars($result['email'] ?? ''); ?>",
    phone: "<?php echo htmlspecialchars($result['phone'] ?? ''); ?>",
    gender: "<?php echo htmlspecialchars($result['gender'] ?? ''); ?>",
    dob: "<?php echo htmlspecialchars($result['date_of_birth'] ?? ''); ?>",
    nationality: "<?php echo htmlspecialchars($result['nationality'] ?? ''); ?>",
    valid_id: "<?php echo htmlspecialchars($result['valid_id'] ?? ''); ?>",
    id_number: "<?php echo htmlspecialchars($result['id_number'] ?? ''); ?>",
    region: "<?php echo htmlspecialchars($result['region'] ?? ''); ?>",
    city: "<?php echo htmlspecialchars($result['city'] ?? ''); ?>",
    barangay: "<?php echo htmlspecialchars($result['barangay'] ?? ''); ?>",
    zip_code: "<?php echo htmlspecialchars($result['zip_code'] ?? ''); ?>",
    full_address: "<?php echo htmlspecialchars($result['full_address'] ?? ''); ?>",
    emergency_contact_name: "<?php echo htmlspecialchars($result['emergency_contact_name'] ?? ''); ?>",
    emergency_contact_number: "<?php echo htmlspecialchars($result['emergency_contact_number'] ?? ''); ?>",
    emergency_contact_relationship: "<?php echo htmlspecialchars($result['emergency_contact_relationship'] ?? ''); ?>",
    password: "<?php echo htmlspecialchars($result['password'] ?? ''); ?>",
    is_active: "<?php echo htmlspecialchars($result['is_active'] ?? '1'); ?>",
    registration_date: "<?php echo htmlspecialchars($result['registration_date'] ?? ''); ?>",
    last_login: "<?php echo htmlspecialchars($result['last_login'] ?? ''); ?>"
};
<?php endwhile; ?>

function showClientModal(id) {
    var client = clientData[id];
    if (!client) return;
    
    var genderText = client.gender === 'M' ? 'Male' : (client.gender === 'F' ? 'Female' : '-');
    
    var fullAddress = [];
    if (client.barangay) fullAddress.push(client.barangay);
    if (client.city) fullAddress.push(client.city);
    if (client.region) fullAddress.push(client.region);
    if (client.zip_code) fullAddress.push(client.zip_code);
    if (client.full_address && client.full_address !== client.barangay && client.full_address !== client.city) fullAddress.push(client.full_address);
    
    var modalContent = `
        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="modal-title">
                <i class="fas fa-user-circle me-2"></i>${client.first_name} ${client.middle_name ? client.middle_name + ' ' : ''}${client.surname}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="closeModal()"></button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div class="text-center mb-4">
                <div class="client-avatar-large mx-auto mb-3">${client.first_name.charAt(0).toUpperCase()}</div>
                <h4>${client.first_name} ${client.middle_name ? client.middle_name + ' ' : ''}${client.surname}</h4>
                <span class="badge ${client.is_active == '1' ? 'bg-success' : 'bg-danger'}">${client.is_active == '1' ? 'Active' : 'Deactivated'}</span>
                <span class="badge bg-secondary ms-1">#${client.customer_number}</span>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="detail-card">
                        <h6><i class="fas fa-id-card me-2"></i>Personal Information</h6>
                        <p><strong>Gender:</strong> ${genderText}</p>
                        <p><strong>Date of Birth:</strong> ${client.dob || '-'}</p>
                        <p><strong>Nationality:</strong> ${client.nationality || '-'}</p>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="detail-card">
                        <h6><i class="fas fa-address-card me-2"></i>Valid ID</h6>
                        <p><strong>ID Type:</strong> ${client.valid_id || '-'}</p>
                        <p><strong>ID Number:</strong> ${client.id_number || '-'}</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="detail-card">
                        <h6><i class="fas fa-envelope me-2"></i>Contact Information</h6>
                        <p><strong>Email:</strong> ${client.email || '-'}</p>
                        <p><strong>Phone:</strong> ${client.phone || '-'}</p>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="detail-card">
                        <h6><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                        <p>${fullAddress.length > 0 ? fullAddress.join(', ') : '-'}</p>
                    </div>
                </div>
            </div>
            
            ${client.emergency_contact_name ? `
            <div class="mb-3">
                <div class="detail-card">
                    <h6><i class="fas fa-user-shield me-2"></i>Emergency Contact</h6>
                    <p><strong>Name:</strong> ${client.emergency_contact_name}</p>
                    <p><strong>Relationship:</strong> ${client.emergency_contact_relationship || '-'}</p>
                    <p><strong>Phone:</strong> ${client.emergency_contact_number || '-'}</p>
                </div>
            </div>
            ` : ''}
            
            <div class="mb-3">
                <div class="detail-card">
                    <h6><i class="fas fa-lock me-2"></i>Account Information</h6>
                    <p><strong>Password:</strong> 
                        <span style="position: relative; display: inline-flex; align-items: center; gap: 5px;">
                            <input type="password" value="${client.password || ''}" readonly style="border:none; background:transparent; width:100px; color:inherit; padding: 0;" id="modal-password-${client.customer_number}">
                            <i class="fas fa-eye toggle-password" onclick="var pwd=document.getElementById('modal-password-'+${client.customer_number}); pwd.type=pwd.type==='password'?'text':'password'; this.classList.toggle('fa-eye-slash');" style="cursor:pointer; font-size: 0.85rem;"></i>
                        </span>
                    </p>
                    <p><strong>Registered:</strong> ${client.registration_date ? new Date(client.registration_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '-'}</p>
                    <p><strong>Last Login:</strong> ${client.last_login ? new Date(client.last_login).toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Never'}</p>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('clientModalContent').innerHTML = modalContent;
    document.getElementById('clientModal').classList.add('show');
}

function closeModal() {
    document.getElementById('clientModal').classList.remove('show');
}

window.onclick = function(event) {
    var modal = document.getElementById('clientModal');
    if (event.target == modal) {
        modal.classList.remove('show');
    }
}
</script>

<style>
.client-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
}
.detail-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    border-left: 4px solid #667eea;
}
.detail-card h6 {
    color: #667eea;
    margin-bottom: 10px;
    font-weight: 600;
}
.detail-card p {
    margin-bottom: 5px;
    font-size: 0.9rem;
    color: #475569;
}
.detail-card strong {
    color: #1e293b;
}
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.modal-overlay.show {
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
}
.modal-content-custom {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    background: #f1f5f9;
    border-radius: 15px;
    max-width: 600px;
    width: 90%;
    max-height: 85vh;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    z-index: 10000;
    opacity: 0;
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.modal-overlay.show .modal-content-custom {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
}
.detail-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 15px;
    border-left: 4px solid #667eea;
    opacity: 0;
    transform: translateY(20px);
    animation: slideIn 0.4s ease forwards;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.detail-card:nth-child(1) { animation-delay: 0.1s; }
.detail-card:nth-child(2) { animation-delay: 0.15s; }
.detail-card:nth-child(3) { animation-delay: 0.2s; }
.detail-card:nth-child(4) { animation-delay: 0.25s; }
.detail-card:nth-child(5) { animation-delay: 0.3s; }

@keyframes slideIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.client-avatar-large {
    animation: fadeInScale 0.4s ease;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
body.dark-mode .modal-content-custom {
    background: #1e293b;
}
body.dark-mode .detail-card {
    background: #334155;
    box-shadow: none;
}
body.dark-mode .detail-card h6 {
    color: #818cf8;
}
body.dark-mode .detail-card p {
    color: #e2e8f0;
}
body.dark-mode .detail-card strong {
    color: #f1f5f9;
}
body.dark-mode .modal-body {
    background: #1e293b;
}
body.dark-mode .modal-body h4 {
    color: #f1f5f9;
}
body.dark-mode .text-center {
    color: #f1f5f9;
}
body.dark-mode .modal-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
}
body.dark-mode .modal-footer {
    background: #334155 !important;
}
body.dark-mode .modal-footer .btn-secondary {
    background: #475569 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .modal-footer .btn-warning {
    background: #f59e0b !important;
    border-color: #f59e0b !important;
    color: #1e293b !important;
}
body.dark-mode .modal-footer .btn-success {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: white !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.client-card-box');
    const searchInput = document.querySelector('input[name="search"]');
    let searchTimeout;
    
    // Initial animation - show all cards first
    function showAllCards() {
        cards.forEach((card, index) => {
            card.style.display = '';
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'opacity 0.4s ease, transform 0.4s ease, box-shadow 0.3s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }
    
    showAllCards();
    
    // Real-time search with debounce
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const searchValue = e.target.value.toLowerCase().trim();
        
        // If search is empty, show all cards
        if (searchValue === '') {
            showAllCards();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            let visibleIndex = 0;
            cards.forEach((card) => {
                const text = card.textContent.toLowerCase();
                if (text.indexOf(searchValue) !== -1) {
                    card.style.display = '';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, visibleIndex * 30);
                    visibleIndex++;
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        }, 150);
    });
    
    // Prevent form submission, use JS search only
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
    });
});
</script>

<div id="clientModal" class="modal-overlay">
    <div id="clientModalContent" class="modal-content-custom"></div>
</div>

</body>
<?php require_once 'include/footer.php';?>
