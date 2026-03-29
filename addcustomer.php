<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'include/head.php';
require_once 'database/db_connection.php';
?>

<body>
  <?php require_once 'include/navbar.php'; ?>
  
  <div class="container-fluid">
    <div class="row">
      <?php require_once 'include/sidebar.php'; ?>
      
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        
        <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); endif; ?>
        
        <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error_msg']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); endif; ?>
        
        <?php if(isset($_SESSION['fraud_warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>⚠️ FRAUD ALERT - Possible Duplicate Detected!</strong>
            <ul class="mb-0 mt-2">
                <?php foreach($_SESSION['fraud_warning'] as $flag): ?>
                <li><?php echo $flag; ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        unset($_SESSION['fraud_warning']); 
        endif; ?>
        
        <div class="page-title-section mt-3">
          <div class="d-flex align-items-center">
            <div class="report-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-user-plus text-white" style="font-size: 22px;"></i>
            </div>
            <div>
              <h1 class="mb-0">Add New Client</h1>
            </div>
          </div>
          <div class="page-actions">
            <a href="managecustomer.php" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-users"></i> View Clients
            </a>
          </div>
        </div>
        
        <div class="row justify-content-center">
          <div class="col-lg-11">
            <div class="form-panel">
              <div class="form-panel-header">
                <h4>Client Information</h4>
                <p>Fill in the client details below</p>
              </div>
              <div class="form-panel-body">
                <form action="app/addcustomerHandler.php" method="post" autocomplete="off">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <label class="form-label">Customer Number</label>
                      <div class="input-group">
                        <input type="text" name="customer_number" id="customerNumber" class="form-control" placeholder="Auto-generated" readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="generateCustomerNumber()" title="Generate new number">
                          <i class="fas fa-sync-alt"></i>
                        </button>
                      </div>
                      <small class="text-muted">Auto-generated unique number</small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Customer Type</label>
                      <?php
                      $result = mysqli_query($conn, "SELECT customer_type_number, customer_type_name FROM customers_type");
                      $hasTypes = mysqli_num_rows($result) > 0;
                      
                      // Default customer types if database is empty
                      $default_types = [
                        ['number' => '1', 'name' => 'Student'],
                        ['number' => '2', 'name' => 'Employee'],
                        ['number' => '3', 'name' => 'Self-Employed'],
                        ['number' => '4', 'name' => 'Business Owner'],
                        ['number' => '5', 'name' => 'OFW'],
                        ['number' => '6', 'name' => 'Senior Citizen'],
                        ['number' => '7', 'name' => 'Pensioner'],
                        ['number' => '8', 'name' => 'Unemployed']
                      ];
                      ?>
                      <select class="form-select" name="customer_type" required>
                        <option value="" selected disabled>Select type...</option>
                        <option value="1">Student</option>
                        <option value="2">Employee</option>
                        <option value="3">Self-Employed</option>
                        <option value="4">Business Owner</option>
                        <option value="5">OFW</option>
                        <option value="6">Senior Citizen</option>
                        <option value="7">Pensioner</option>
                        <option value="8">Unemployed</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">First Name</label>
                      <input type="text" name="first_name" class="form-control" placeholder="First name" required>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Middle Name</label>
                      <input type="text" name="middle_name" class="form-control" placeholder="Middle name">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Surname</label>
                      <input type="text" name="surname" class="form-control" placeholder="Surname" required>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Nationality</label>
                      <select name="nationality" class="form-select">
                        <option value="" selected disabled>Select</option>
                        <option value="Filipino" selected>Philippines</option>
                        <option value="Kenyan">Kenya</option>
                        <option value="Ugandan">Uganda</option>
                        <option value="Tanzanian">Tanzania</option>
                        <option value="American">American</option>
                        <option value="Chinese">Chinese</option>
                        <option value="Indian">Indian</option>
                        <option value="Japanese">Japanese</option>
                        <option value="Korean">Korean</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Date of Birth</label>
                      <input type="date" name="date_of_birth" class="form-control">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label d-block">Gender</label>
                      <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="gender" id="gender_m" value="M">
                        <label class="btn btn-outline-secondary" for="gender_m">Male</label>
                        <input type="radio" class="btn-check" name="gender" id="gender_f" value="F">
                        <label class="btn btn-outline-secondary" for="gender_f">Female</label>
                        <input type="radio" class="btn-check" name="gender" id="gender_o" value="O">
                        <label class="btn btn-outline-secondary" for="gender_o">Other</label>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Email</label>
                      <input type="email" name="email" class="form-control" placeholder="email@example.com">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Phone</label>
                      <input type="text" name="phone" class="form-control" placeholder="Phone number">
                    </div>

                    <!-- Government ID Section -->
                    <div class="col-12 mt-3">
                      <h6 class="text-muted border-bottom pb-2"><i class="fas fa-id-card me-2"></i>Government ID</h6>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">ID Type</label>
                      <select name="gov_id_type" class="form-select">
                        <option value="">Select ID Type</option>
                        <option value="Passport">Passport</option>
                        <option value="Driver's License">Driver's License</option>
                        <option value="National ID">National ID</option>
                        <option value="SSS ID">SSS ID</option>
                        <option value="GSIS ID">GSIS ID</option>
                        <option value="Voter's ID">Voter's ID</option>
                        <option value="TIN ID">TIN ID</option>
                        <option value="PRC ID">PRC ID</option>
                        <option value="Police Clearance">Police Clearance</option>
                        <option value="Barangay ID">Barangay ID</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">ID Number</label>
                      <input type="text" name="gov_id_number" class="form-control" placeholder="ID Number">
                    </div>

                    <div class="col-md-4">
                    </div>

                    <!-- Emergency Contact Section -->
                    <div class="col-12 mt-3">
                      <h6 class="text-muted border-bottom pb-2"><i class="fas fa-user-shield me-2"></i>Emergency Contact</h6>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Contact Name</label>
                      <input type="text" name="emergency_contact_name" class="form-control" placeholder="Full name">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Contact Number</label>
                      <input type="text" name="emergency_contact_number" class="form-control" placeholder="Phone number">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Relationship</label>
                      <select name="emergency_contact_relationship" class="form-select">
                        <option value="">Select</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Parent">Parent</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Child">Child</option>
                        <option value="Relative">Relative</option>
                        <option value="Friend">Friend</option>
                        <option value="Colleague">Colleague</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>

                    <!-- Address Section -->
                    <div class="col-12 mt-3">
                      <h6 class="text-muted border-bottom pb-2"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Region</label>
                      <select name="region" id="region" class="form-select" onchange="loadCities()">
                        <option value="" selected disabled>Select Region</option>
                        <option value="NCR">National Capital Region (NCR)</option>
                        <option value="CAR">Cordillera Administrative Region (CAR)</option>
                        <option value="Region I">Ilocos Region (Region I)</option>
                        <option value="Region II">Cagayan Valley (Region II)</option>
                        <option value="Region III">Central Luzon (Region III)</option>
                        <option value="Region IV-A">CALABARZON (Region IV-A)</option>
                        <option value="Region IV-B">MIMAROPA (Region IV-B)</option>
                        <option value="Region V">Bicol Region (Region V)</option>
                        <option value="Region VI">Western Visayas (Region VI)</option>
                        <option value="Region VII">Central Visayas (Region VII)</option>
                        <option value="Region VIII">Eastern Visayas (Region VIII)</option>
                        <option value="Region IX">Zamboanga Peninsula (Region IX)</option>
                        <option value="Region X">Northern Mindanao (Region X)</option>
                        <option value="Region XI">Davao Region (Region XI)</option>
                        <option value="Region XII">SOCCSKSARGEN (Region XII)</option>
                        <option value="Region XIII">Caraga (Region XIII)</option>
                        <option value="BARMM">Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">City</label>
                      <select name="city" id="city" class="form-select" onchange="loadBarangays()">
                        <option value="" selected disabled>Select City</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Barangay</label>
                      <select name="barangay" id="barangay" class="form-select">
                        <option value="" selected disabled>Select Barangay</option>
                      </select>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Zip Code</label>
                      <input type="text" name="zip_code" class="form-control" placeholder="Zip code">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Full Address</label>
                      <input type="text" name="full_address" class="form-control" placeholder="Full address">
                    </div>

                    <div class="col-12">
                      <hr class="my-4">
                      <h6 class="text-muted border-bottom pb-2 mb-3"><i class="fas fa-lock me-2"></i>Login Credentials</h6>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Login Password</label>
                      <input type="password" name="password" id="password" class="form-control" placeholder="Customer login password (min 12 chars)">
                      <small class="text-muted">Must have uppercase, lowercase, number, special char</small>
                      <div id="password-requirements" class="password-requirements mt-2">
                        <small class="d-block mb-1">Password must contain:</small>
                        <ul class="password-rules ps-3 mb-0" style="font-size: 0.75rem;">
                          <li id="req-length" class="text-danger"><i class="fas fa-times"></i> At least 12 characters</li>
                          <li id="req-upper" class="text-danger"><i class="fas fa-times"></i> At least 1 uppercase letter</li>
                          <li id="req-lower" class="text-danger"><i class="fas fa-times"></i> At least 1 lowercase letter</li>
                          <li id="req-number" class="text-danger"><i class="fas fa-times"></i> At least 1 number</li>
                          <li id="req-special" class="text-danger"><i class="fas fa-times"></i> At least 1 special character (!@#$%^&*)</li>
                        </ul>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Confirm Password</label>
                      <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password">
                    </div>

                    <div class="col-12 mt-4">
                      <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5" name="addcustomer">
                          <i class="fas fa-user-plus me-2"></i> Register Client
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg">
                          Reset
                        </button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
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
body.dark-mode .form-panel-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    border-color: #334155 !important;
}
body.dark-mode .form-panel-header h4,
body.dark-mode .form-panel-header p {
    color: #e2e8f0 !important;
}
body.dark-mode .form-panel-body {
    background: #1e293b !important;
}
body.dark-mode .page-title-section h1,
body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, 
body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
    color: #f1f5f9 !important;
}
body.dark-mode p, body.dark-mode span, body.dark-mode div, body.dark-mode strong {
    color: #e2e8f0 !important;
}
body.dark-mode .text-muted {
    color: #94a3b8 !important;
}
body.dark-mode .form-label {
    color: #e2e8f0 !important;
}
body.dark-mode .form-select,
body.dark-mode .form-control {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .form-select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}
body.dark-mode .form-check-label {
    color: #e2e8f0 !important;
}
body.dark-mode .alert-success {
    background: #064e3b !important;
    color: #6ee7b7 !important;
    border-color: #065f46 !important;
}
body.dark-mode .alert-danger {
    background: #7f1d1d !important;
    color: #fca5a5 !important;
    border-color: #991b1b !important;
}
body.dark-mode .btn-close {
    filter: invert(1);
}
</style>

<script>
// Generate unique customer number on page load
document.addEventListener('DOMContentLoaded', function() {
    generateCustomerNumber();
});

function generateCustomerNumber() {
    // Generate a 9-digit random number starting with a non-zero digit
    var randomNum = Math.floor(100000000 + Math.random() * 900000000);
    document.getElementById('customerNumber').value = randomNum;
}

const citiesData = {
    "NCR": {
        "Manila": ["Binondo", "Intramuros", "Santa Cruz", "Sampaloc", "San Nicolas", "Quiapo", "Tondo", "Santa Ana", "Pandacan", "Malate", "Ermita", "Paco", "Santa Mesa", "San Andres", "Kasilawan", "San Roque", "Concepcion", "Pariancillo", "Maynila"],
        "Quezon City": ["Project 1", "Project 2", "Project 3", "Project 4", "Project 5", "Project 6", "Project 7", "Project 8", "Novaliches", "Fairview", "Commonwealth", "Holy Spirit", "Batasan Hills", "Payatas", "Bagong Silang", "Tatalon", "Sauyo", "Maliwanag", "Masagana", "Museo"],
        "Caloocan": ["Bagong Barrio", "Bagong Silang", "Camarin", "Deparo", "Grace Park", "Kaunlaran", "Libis", "Maypajo", "Meycauayan", "Monumento", "Muzon", "Poblacion", "San Jose", "San Juan", "Sta. Quiteria", "Tala", "Tipolo"],
        "Makati": ["Bel-Air", "Carmona", "Dasmariñas", "Forbes Park", "Guadalupe Nuevo", "Guadalupe Viejo", "Kasilawan", "La Paz", "Legaspi", "Magallanes", "Malate", "Pacos", "Pandi", "Pinagkaisahan", "Pio Del Pilar", "San Antonio", "San Lorenzo", "Santa Cruz", "Sauyo", "Valverde"],
        "Pasay": ["Bay View", "CARMONA", "Dona Escolastica", "Malibay", "Manila Bay", "Merville", "Midtown", "New Port", "Pasay", "Pio Del Pilar", "San Antonio", "San Fidencio", "San Jose", "San Rafael", "Santa Clara", "Santa Lucia", "Santo Niño", "Tambo", "Vicente Cruz"],
        "Taguig": ["Bagumbayan", "Bayan", "Calzada", "Ligid", "Lower Bicutan", "Maharlika", "Napindan", "New Lower Bicutan", "Pinagsama", "San Miguel", "Santa Ana", "Santo Domingo", "Signal Village", "Taguig", "Upper Bicutan", "Western Bicutan"],
        "Parañaque": ["Baclaran", "Bayan", "Bayanan", "Don Bosco", "Don Galo", "La Huerta", "Libertad", "Maling", "Marina", "Moonwalk", "Pulo", "San Antonio", "San Dionisio", "San Francisco", "San Isisdro", "San Martin De Porres", "Santo Niño", "Sun Valley", "Tambo", "Vitalez"],
        "Las Piñas": ["Almanza", "B.F. International", "CAA", "Dalawang Baryo", "Daniel Fajardo", "Elias Aldana", "Ilaya", "Manila", "Pamplona", "Pilar", "Pinagbayanan", "Pulanglupa", "Pulang Lupa Dos", "Pulang Lupa Uno", "Salas", "San Antonio", "San Nicolas", "Tsalik", "Zapote"]
    },
    "Region III": {
        "Bulacan": ["Angeles", "Apalit", "Arayat", "Bacolor", "Baliuag", "Bocaue", "Bulacan", "Bustos", "Calumpit", "Doña Remedios Trinidad", "Guiguinto", "Hagonoy", "Luzon", "Malolos", "Marilao", "Meycauayan", "Norzagaray", "Obando", "Pandi", "Paombong", "Plaridel", "Pulilan", "San Ildefonso", "San Jose del Monte", "San Miguel", "San Rafael", "Santa Maria", "Santo Tomas", "Subic"],
        "Pampanga": ["Angeles City", "Apalit", "Arayat", "Bacolor", "Candaba", "Capas", "Concepcion", "Floridablanca", "Guagua", "Lubao", "Mabalacat", "Macabebe", "Magalang", "Masantol", "Mexico", "Minalin", "Porac", "San Fernando", "San Luis", "San Simon", "Santa Ana", "Santa Rita", "Santo Tomas", "Sas", "Tarlac City"],
        "Tarlac": ["Anao", "Bamban", "Camiling", "Capas", "Concepcion", "Gerona", "Laantoc", "Moncada", "Paniqu Paz", "Mayi", "Pura", "Ramos", "San Clemente", "San Jose", "San Manuel", "Santa Ignacia", "Tarlac City", "Victoria"]
    },
    "Region IV-A": {
        "Laguna": ["Alaminos", "Bay", "Binan", "Cabuyao", "Calamba", "Calauan", "Cavinti", "Famy", "Kalayaan", "Liliw", "Los Baños", "Luisiana", "Lumban", "Mabitac", "Magdalena", "Majayjay", "Nagcarlan", "Paete", "Pagsanjan", "Pakil", "Pangil", "Pila", "Rizal", "San Pablo", "San Pedro", "Santa Cruz", "Santa Maria", "Santo Domingo", "Siniloan", "Victoria"],
        "Cavite": ["Amadeo", "Bacoor", "Carmona", "Cavite City", "Dasmariñas", "General Emilio Aguinaldo", "General Mariano Alvarez", "Imus", "Indang", "Kawit", "Magallanes", "Maragondon", "Mendez", "Naic", "Noveleta", "Rosario", "Silang", "Tagaytay", "Tanza", "Ternate", "Trece Martires"],
        "Batangas": ["Agoncillo", "Alitagtag", "Balayan", "Balete", "Batangas City", "Bauan", "Calaca", "Calatagan", "Cuenca", "Ibaan", "Laurel", "Lemery", "Lian", "Lipa", "Lobo", "Mabini", "Malvar", "Mataasnakahoy", "Nasugbu", "Padre Garcia", "Rosario", "San Jose", "San Juan", "San Leonardo", "Santo Tomas", "Taal", "Talisay", "Tanauan", "Taysan", "Tingloy", "Tuy"],
        "Rizal": ["Angono", "Antipolo", "Baras", "Binangonan", "Cainta", "Cardona", "Jalajala", "Morong", "Pililla", "Rodriguez", "San Mateo", "Tanay", "Taytay", "Ternate", "Rodriguez"]
    },
    "Region IV-B": {
        "Mindoro Oriental": ["Baco", "Bansud", "Bongabong", "Bulalacao", "Calapan City", "Gloria", "Mansalay", "Naujan", "Pinamalayan", "Pola", "Puerto Galera", "Roxas", "San Teodoro", "Soccoro", "Victoria"],
        "Palawan": ["Aborlan", "Agutaya", "Araceli", "Balabac", "Bataraza", "Brooke's Point", "Busuanga", "Cagayancillo", "Coron", "Culion", "Cuyo", "Dapecol", "El Nido", "Garcia Hernandez", "IndPalawan", "Linapacan", "Luzon", "Magsaysay", "Mawab", "Narra", "Palawan", "Quezon", "Rizal", "Ronda", "San Rafael", "San Vicente", "Sofronio Española", "Taytay"]
    },
    "Region V": {
        "Albay": ["Bacacay", "Camalig", "Daraga", "Guinobatan", "Jovellar", "Legazpi", "Libon", "Malilipot", "Malinao", "Manito", "Oas", "Pio Duran", "Polangui", "Rapu-Rapu", "Santo Domingo", "Tiwi"],
        "Camarines Sur": ["Baao", "Balatan", "Bato", "Buhi", "Bula", "Cabusao", "Calabanga", "Camaligan", "Canaman", "Caramoan", "Del Gallego", "Gainza", "Garchitorena", "Iriga", "Lagonoy", "Libmanan", "Lupi", "Magarao", "Milaor", "Minalabac", "Nabua", "Naga", "Ocampo", "Pamplona", "Pasacao", "Pili", "Presentacion", "Ragay", "Sagñay", "San Fernando", "San Jose", "Sipocot", "Siruma", "Tigaon", "Tinambac"],
        "Sorsogon": ["Barcelona", "Bulan", "Bulusan", "Casiguran", "Castilla", "Davao", "Donsol", "Gubat", "Irosin", "Juban", "Magallanes", "Matnog", "Pilar", "Prieto Diaz", "Pud", "Santa Magdalena", "Sorsogon City", "Toril"]
    },
    "Region VI": {
        "Iloilo": ["Ajuy", "Alimodian", "Anilao", "Badiangan", "Balasan", "Banate", "Barotac Nuevo", "Barotac Viejo", "Batuan", "Bingawan", "Culasi", "Dingle", "Duenas", "Dumangas", "Estancia", "Guimbal", "Igbaras", "Iloilo City", "Janiuay", "Lambunao", "Leganes", "Lemery", "Leon", "Maasin", "Magsaysay", "Miagao", "Molo", "Nabas", "New Lucena", "Oton", "Passi", "Pavia", "Pototan", "San Dionisio", "San Enrique", "San Joaquin", "San Miguel", "San Rafael", "Santa Barbara", "Santa Cruz", "Santa Elena", "Santo Domingo", "Sibonga", "Tibiao", "Tobias Fornier", "Tubungan", "Zarraga"],
        "Aklan": ["Altavas", "Balete", "Banga", "Batan", "Boracay", "Buruanga", "Ibajay", "Kalibo", "Lezo", "Libacao", "Madalag", "Makato", "Malay", "Malinao", "Nabas", "New Washington", "Numancia", "Tangalan"],
        "Antique": ["Anini-y", "Barbaza", "Belison", "Bugasong", "Culasi", "Hamtic", "Libertad", "Pandan", "Patnongon", "San Jose", "San Pedro", "San Remigio", "Sebaste", "Sibalom", "Tibiao", "Valderrama"]
    },
    "Region VII": {
        "Cebu": ["Alcantara", "Alcoy", "Alegria", "Aloguinsan", "Argao", "Asturias", "Badian", "Balamban", "Bantayan", "Barili", "Bogo", "Boljoon", "Borbon", "Carmen", "Catmon", "Cebu City", "Compostela", "Consolacion", "Cordoba", "Daanbantayan", "Dalaguete", "Danao", "Dumanjug", "Ginatilan", "Lapu-Lapu", "Liloan", "Madridejos", "Malabuyoc", "Mandaue", "Medellin", "Minglanilla", "Moalboal", "Naga", "Oslob", "Pilar", "Pinamalayan", "Poro", "Ronda", "Samboan", "San Fernando", "San Francisco", "San Remigio", "Santa Fe", "Santander", "Sibonga", "Sogod", "Tabogon", "Tabuelan", "Talisay", "Toledo", "Tuburan", "Tudela"],
        "Bohol": ["Alburquerque", "Alicia", "Anda", "Antequera", "Baclayon", "Balilihan", "Batuan", "Bien Unida", "Bilar", "Buenavista", "Calape", "Candijay", "Carmen", "Catigbian", "Corella", "Cortes", "Dauis", "Dimiao", "Duero", "Garcia Hernandez", "Getafe", "Guindulman", "Inabanga", "Jagna", "Lila", "Loay", "Loboc", "Loon", "Mabini", "Maribojoc", "Panglao", "Pilar", "President Carlos P. Garcia", "Sagbayan", "San Isidro", "San Miguel", " Sevilla", "Sierra Bullones", "Sikatuna", "Tagbilaran City", "Talibon", "Trinidad", "Tubigon", "Ubay", "Valencia"],
        "Negros Oriental": ["Amlan", "Ayungon", "Bacong", "Basay", "Bindoy", "Canlaon", "Dauin", "Dumaguete City", "Guihulngan", "Jimalalud", "La Libertad", "Mabinay", "Manjuyod", "Pamplona", "San Jose", "San Juan", "Santa Catalina", "Siaton", "Sibulan", "Tanjay", "Tayasan", "Valencia", "Vallehermoso", "Zamboanguita"]
    },
    "Region VIII": {
        "Leyte": ["Abuyog", "Alangalang", "Albuera", "Baba", "Barug", "Bato", "Baybay", "Burauen", "Calubian", "Capoocan", "Carigara", "Dulag", "Hilongos", "Hind", "Inopacan", "Isabel", "Jaro", "Javier", "Julita", "Kananga", "La Paz", "Leyte", "Libagon", "Liloan", "MacArthur", "Mahaplag", "Matag-Ob", "Matalom", "Mayorga", "Merida", "Ormoc", "Palo", "Palompon", "Pastrana", "San Isidro", "San Miguel", "Santa Fe", "Tabango", "Tabontabon", "Tacloban", "Tanauan", "Tolosa", "Tunga", "Villaba"],
        "Samar": ["Almagro", "Balangiga", "Baluan", "Basey", "Calbiga", "Catbalogan", "Daram", "Gandara", "Hinabangan", "Jiabong", "Marabut", "Matuguinao", "Motiong", "Pagsanghan", "Paranas", "Pinamalayan", "San Jorge", "San Jose De Buan", "San Sebastian", "Santa Margarita", "Santa Rita", "Santo Niño", "Tagapul-An", "Talalora", "Tarangnan", "Villareal", "Zumarraga"]
    },
    "Region IX": {
        "Zamboanga del Norte": ["Dapitan", "Dipolog", "Katipunan", "La Libertad", "Labason", "Liloy", "Manukan", "Mutia", "Piñan", "Polanco", "Pres. Manuel A. Roxas", "Rizal", "Salug", "Sibuco", "Sindangan", "Siocon", "Sirawai", "Tambulig", "Tubod"],
        "Zamboanga del Sur": ["Aurora", "Bayog", "Dimataling", "Dinas", "Dumalinao", "Dumingag", "Guipos", "Josefina", "Kumalarang", "Labangan", "Lakewood", "Lapuyan", "Mahayag", "Margosatubig", "Midsalip", "Molave", "Pitogo", "Ramon Magsaysay", "San Pablo", "Sominot", "Tabina", "Tambulig", "Tigbao", "Tukuran", "Vincenzo A. Sagun", "Zamboanga City"],
        "Zamboanga Sibugay": ["Alicia", "Buug", "Diplahan", "Imelda", "Ipil", "Kabasalan", "Mabuhay", "Malangas", "Naga", "Olutanga", "Payao", "Roseller T. Lim", "Siay", "Sibuco", "Sibunganay", "Talusan", "Titay", "Tungawan"]
    },
    "Region X": {
        "Cagayan de Oro": ["Alubijid", "Balingasag", "Balingoan", "Binuangan", "Cagayan de Oro", "Claveria", "Gingoog", "Jasaan", "Jimenez", "Kinoguitan", "Lagonglong", "Laguindingan", "Libona", "Magsaysay", "Manticao", "Medina", "Naawan", "Opol", "Ozamis", "Panaon", "Plaridel", "Salay", "Sugbongcogon", "Tagoloan", "Talisayan", "Villanueva"],
        "Bukidnon": ["Baungon", "Cabanglasan", "Damulog", "Dangcagan", "Don Carlos", "Impasug-ong", "Kadingilan", "Kalilangan", "Kibawe", "Kitaotao", "Lantapan", "Libona", "Malaybalay", "Maramag", "Musuan", "Pangantucan", "Quezon", "San Fernando", "Sumilao", "Talakag", "Manolo Fortich"],
        "Lanao del Norte": ["Bacolod", "Baloi", "Baroy", "Kapatagan", "Kauswagan", "Kolambugan", "Lanao del Norte", "Lumba-an", "Magsaysay", "Maigo", "Matungao", "Munai", "Nunukan", "Pantar", "Poona Piagapo", "Salvador", "Sapad", " Sultan Naga Dipatuan", "Tagoloan", "Tangkal", "Tubod"]
    },
    "Region XI": {
        "Davao del Norte": ["Asuncion", "Braulio E. Dujali", "Carmen", "Davao City", "Davao del Norte", "Kapalong", "New Corella", "Panabo", "Samal", "Santo Tomas", "Tagum", "Talaingod"],
        "Davao del Sur": ["Bansalan", "Davao City", "Davao del Sur", "Digos", "Hagonoy", "Kiblawan", "Magsaysay", "Malalag", "Matanao", "Padada", "Santa Cruz", "Sulop"],
        "Davao Oriental": ["Bagana", "Banaybanay", "Cateel", "Davao Oriental", "Governor Generoso", "Lupon", "Manay", "Mati", "San Isidrio", "Tarragona"],
        "Compostela Valley": ["Compostela", "Laak", "Mabini", "Maco", "Montevista", "Nabunturan", "New Bataan", "Pantukan"]
    },
    "Region XII": {
        "Cotabato": ["Aleosan", "Antipas", "Arakan", "Banisilan", "Carmen", "Cotabato", "Kabacan", "Kidapawan", "Libungan", "M'Lang", "Magpet", "Makar", "Matalam", "Midlaj", "Pigcawayan", "Pikit", "President Roxas", "Tulunan"],
        "South Cotabato": ["Bangko", "General Santos", "Koronadal", "Lake Sebu", "Norala", "Polomolok", "Santo Niño", "Surallah", "Tampacan", "Tantangan", "Tupi"],
        "Sultan Kudarat": ["Bagumbayan", "Columbio", "Esperanza", "Isulan", "Kalamansig", "Lebak", "Lutayan", "Palimbang", "President Quirino", "Sultan Kudarat", "Tacurong"]
    },
    "Region XIII": {
        "Agusan del Norte": ["Buenavista", "Butuan City", "Cabadbaran", "Carmen", "Jabonga", "Kitcharao", "Las Nieves", "Magallanes", "Nasipit", "Remedios T. Romualdez", "Santiago", "Tubay"],
        "Agusan del Sur": ["Bayugan", "Bunawan", "Esperanza", "La Paz", "Loreto", "Prosperidad", "San Francisco", "San Luis", "Santa Josefa", "Sibagat", "Talacogon", "Trento", "Veruela"],
        "Surigao del Norte": ["Alegria", "Bacuag", "Burgos", "Claver", "Dapa", "Del Carmen", "General Luna", "Gigaquit", "Loreto", "Mainit", "Malimono", "Pilar", "Placer", "San Benito", "San Francisco", "San Isidro", "Santa Monica", "Sison", "Socorro", "Surigao City", "Tagana-an", "Tubajon"],
        "Surigao del Sur": ["Barobo", "Bayabas", "Bislig", "Cagwait", "Cantilan", "Carmen", "Carrascal", "Cortez", "Hinatuan", "Lanuza", "Lianga", "Lingig", "Madrid", "Marihatag", "San Agustin", "San Miguel", "Tagbina", "Tandag"]
    },
    "BARMM": {
        "Basilan": ["Akbar", "Al-Barka", "Hadji Mohammad Ajul", "Hadji Muhtamad", "Isabela City", "Lamitan", "Lantawan", "Maluso", "Sumisip", "Tabuan-Lasa", "Tipo-Tipo", "Tuburan", "Ungkaya Pukan"],
        "Lanao del Sur": ["Amai Manabilang", "Bacolod-Kalawi", "Balabagan", "Balindong", "Bayang", "Binidayan", "Buadiposo-Buntong", "Bubong", "Butig", "Calanogas", "Ditsaan-Ramain", "Ganassi", "Kapatagan", "Lumba-an", "Lumbaca-Unayan", "Lumbayanague", "Madalum", "Madamba", "Maguing", "Malabang", "Masiu", "Mulondo", "Pagayawan", "Piagapo", "Piket", "Poona Bayabao", "Pualas", "Saguiaran", "Sultan Dumalondong", "Tagoloan II", "Tamparan", "Taraka", "Tukuran", "Wao"]
    }
};

function loadCities() {
    const regionSelect = document.getElementById('region');
    const citySelect = document.getElementById('city');
    const selectedRegion = regionSelect.value;
    
    citySelect.innerHTML = '<option value="" selected disabled>Select City</option>';
    document.getElementById('barangay').innerHTML = '<option value="" selected disabled>Select Barangay</option>';
    
    if (citiesData[selectedRegion]) {
        const cities = Object.keys(citiesData[selectedRegion]);
        cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    }
}

function loadBarangays() {
    const regionSelect = document.getElementById('region');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');
    const selectedRegion = regionSelect.value;
    const selectedCity = citySelect.value;
    
    barangaySelect.innerHTML = '<option value="" selected disabled>Select Barangay</option>';
    
    if (citiesData[selectedRegion] && citiesData[selectedRegion][selectedCity]) {
        const barangays = citiesData[selectedRegion][selectedCity];
        barangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay;
            option.textContent = barangay;
            barangaySelect.appendChild(option);
        });
    }
}

document.getElementById('password').addEventListener('input', function() {
    var password = this.value;
    
    var hasLength = password.length >= 12;
    var hasUpper = /[A-Z]/.test(password);
    var hasLower = /[a-z]/.test(password);
    var hasNumber = /[0-9]/.test(password);
    var hasSpecial = /[!@#$%^&*]/.test(password);
    
    updateRequirement('req-length', hasLength);
    updateRequirement('req-upper', hasUpper);
    updateRequirement('req-lower', hasLower);
    updateRequirement('req-number', hasNumber);
    updateRequirement('req-special', hasSpecial);
    
    var allValid = hasLength && hasUpper && hasLower && hasNumber && hasSpecial;
    var submitBtn = document.querySelector('button[name="addcustomer"]');
    submitBtn.disabled = !allValid;
    if (!allValid) {
        submitBtn.title = 'Please meet all password requirements';
    } else {
        submitBtn.title = '';
    }
});

function updateRequirement(id, valid) {
    var element = document.getElementById(id);
    if (!element) return;
    if (valid) {
        element.classList.remove('text-danger');
        element.classList.add('text-success');
        element.querySelector('i').classList.remove('fa-times');
        element.querySelector('i').classList.add('fa-check');
    } else {
        element.classList.remove('text-success');
        element.classList.add('text-danger');
        element.querySelector('i').classList.remove('fa-check');
        element.querySelector('i').classList.add('fa-times');
    }
}

document.querySelector('form').addEventListener('submit', function(e) {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;
    
    var hasLength = password.length >= 12;
    var hasUpper = /[A-Z]/.test(password);
    var hasLower = /[a-z]/.test(password);
    var hasNumber = /[0-9]/.test(password);
    var hasSpecial = /[!@#$%^&*]/.test(password);
    
    if (!hasLength || !hasUpper || !hasLower || !hasNumber || !hasSpecial) {
        e.preventDefault();
        alert('Please meet all password requirements before registering.');
        return false;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match.');
        return false;
    }
});
</script>

<style>
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
@keyframes slideInLeft {
  from { opacity: 0; transform: translateX(-30px); }
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
</style>

</body>
<?php require_once 'include/footer.php'; ?>
