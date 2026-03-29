<?php 
session_start();
if (isset($_SESSION['customer_id'])) {
    header('Location: customer_dashboard.php');
    exit();
}
require_once 'include/head.php';
require_once 'database/db_connection.php';
?>
<style>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}
.login-box {
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    padding: 40px;
    width: 100%;
    max-width: 600px;
    position: relative;
}
.login-box h2 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
    font-weight: 600;
}
.avatar-container {
    text-align: center;
    margin-bottom: 20px;
}
.avatar-container img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid #667eea;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #1f2937;
    font-weight: 600;
    font-size: 14px;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    color: #1f2937;
}
.form-group select option {
    color: #1f2937;
    background: white;
    padding: 10px;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #667eea;
    background: white;
}
.login-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}
.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}
.login-link {
    text-align: center;
    margin-top: 20px;
    width: 100%;
}
.login-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}
.alert {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}
.alert-danger {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}
.alert-success {
    background: #efe;
    color: #3c3;
    border: 1px solid #cfc;
}
</style>

<body>
    <div class="login-container">
        <div class="login-box">
            <h2><i class="fas fa-user-plus"></i> User Registration</h2>
            
            <?php if(isset($_SESSION['register_error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['register_success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['register_success']; unset($_SESSION['register_success']); ?>
                </div>
            <?php endif; ?>
            
            <div class="avatar-container">
                <img src="assets/img/logo.png" alt="Avatar">
            </div>
            
            <form action="app/customer_registerHandler.php" method="post" autocomplete="off" onsubmit="return validateAge()" style="max-width: 500px; margin: 0 auto;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="first_name"><i class="fas fa-user"></i> First Name</label>
                            <input type="text" name="first_name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="surname"><i class="fas fa-user"></i> Surname</label>
                            <input type="text" name="surname" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="text" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="gender"><i class="fas fa-venus-mars"></i> Gender</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dob"><i class="fas fa-calendar"></i> Date of Birth</label>
                    <input type="date" name="dob" id="dob" required>
                </div>

                <div class="form-group">
                    <label for="nationality"><i class="fas fa-flag"></i> Nationality</label>
                    <select name="nationality" id="nationality" required>
                        <option value="">Select Nationality</option>
                        <option value="Filipino">Filipino</option>
                        <option value="Filipino">Filipino (Dual Citizen)</option>
                        <option value="American">American</option>
                        <option value="British">British</option>
                        <option value="Chinese">Chinese</option>
                        <option value="Indian">Indian</option>
                        <option value="Japanese">Japanese</option>
                        <option value="Korean">Korean</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="valid_id"><i class="fas fa-id-card"></i> Valid ID</label>
                    <select name="valid_id" id="valid_id" required>
                        <option value="">Select Valid ID</option>
                        <option value="Passport">Passport</option>
                        <option value="SSS">SSS ID</option>
                        <option value="GSIS">GSIS ID / UMID</option>
                        <option value="Driver's License">Driver's License</option>
                        <option value="NBI">NBI Clearance</option>
                        <option value="Police Clearance">Police Clearance</option>
                        <option value="Barangay ID">Barangay ID</option>
                        <option value="Postal ID">Postal ID</option>
                        <option value="TIN">TIN ID</option>
                        <option value="PhilHealth">PhilHealth ID</option>
                        <option value="PRC">PRC ID</option>
                        <option value="Senior Citizen">Senior Citizen ID</option>
                        <option value="PWD">Person with Disability (PWD) ID</option>
                        <option value="Other">Other Government ID</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_number"><i class="fas fa-hashtag"></i> ID Number</label>
                    <input type="text" name="id_number" id="id_number" placeholder="Enter your ID number" required>
                </div>
                
                <hr>
                <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="region"><i class="fas fa-map"></i> Region</label>
                            <select name="region" id="region" onchange="updateCities()" required>
                                <option value="">Select Region</option>
                                <option value="NCR">NCR (National Capital Region)</option>
                                <option value="Region I">Region I (Ilocos Region)</option>
                                <option value="Region II">Region II (Cagayan Valley)</option>
                                <option value="Region III">Region III (Central Luzon)</option>
                                <option value="Region IV-A">Region IV-A (CALABARZON)</option>
                                <option value="Region IV-B">Region IV-B (MIMAROPA)</option>
                                <option value="Region V">Region V (Bicol Region)</option>
                                <option value="Region VI">Region VI (Western Visayas)</option>
                                <option value="Region VII">Region VII (Central Visayas)</option>
                                <option value="Region VIII">Region VIII (Eastern Visayas)</option>
                                <option value="Region IX">Region IX (Zamboanga Peninsula)</option>
                                <option value="Region X">Region X (Northern Mindanao)</option>
                                <option value="Region XI">Region XI (Davao Region)</option>
                                <option value="Region XII">Region XII (SOCCSKSARGEN)</option>
                                <option value="Region XIII">Region XIII (Caraga)</option>
                                <option value="BARMM">BARMM (Bangsamoro Autonomous Region)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="city"><i class="fas fa-city"></i> City/Municipality</label>
                            <select name="city" id="city" onchange="updateBarangays()" required>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="barangay"><i class="fas fa-home"></i> Barangay</label>
                            <select name="barangay" id="barangay" required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="zip_code"><i class="fas fa-mail-bulk"></i> Zip Code</label>
                            <input type="text" name="zip_code" id="zip_code" placeholder="e.g., 1000" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="full_address"><i class="fas fa-address-card"></i> Full Address</label>
                    <input type="text" name="full_address" placeholder="Street address, building, etc.">
                </div>
                
                <hr>
                <h5 class="mb-3"><i class="fas fa-user-shield me-2"></i>Emergency Contact</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" placeholder="Full name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Emergency Contact Number</label>
                            <input type="text" name="emergency_contact_number" placeholder="Phone number" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-heart"></i> Relationship</label>
                    <select name="emergency_contact_relationship" required>
                        <option value="">Select Relationship</option>
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
                
                <hr>
                
                <div class="form-group">
                    <label for="psw"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="psw" id="password" required>
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
                
                <div class="form-group">
                    <label for="confirm_psw"><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" name="confirm_psw" id="confirm_password" required>
                </div>
                
                <button type="submit" name="register" class="login-btn">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 14px;">
                Already have an account? <a href="customer_login.php" style="color: #667eea; font-weight: 600; text-decoration: none;">Login here</a>
            </div>
        </div>
    </div>
</body>
<script>
function validateAge() {
    var dobInput = document.getElementById('dob');
    var dob = new Date(dobInput.value);
    var today = new Date();
    var age = today.getFullYear() - dob.getFullYear();
    var monthDiff = today.getMonth() - dob.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    if (age < 18) {
        alert('You must be at least 18 years old to register.');
        return false;
    }
    return true;
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
    var submitBtn = document.querySelector('.login-btn');
    submitBtn.disabled = !allValid;
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

document.addEventListener('DOMContentLoaded', function() {
    var dobInput = document.getElementById('dob');
    var today = new Date();
    var maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
    dobInput.max = maxDate.toISOString().split('T')[0];
});

var citiesData = {
    "NCR": {
        "Manila": ["Binondo", "Intramuros", "Santa Cruz", "Sampaloc", "San Nicolas", "Quiapo", "Santa Ana", "Pandacan", "Malate", "Paco", "Ermita", "Tondo"],
        "Quezon City": ["Batasan Hills", "Cubao", "Diliman", "Fairview", "Kamuning", "New Manila", "Novaletes", "Project 1-6", "Quezon Memorial Circle", "Santa Heights", "Tandang Sora", "West Triangle"],
        "Caloocan": ["Barrio San Jose", "Caloocan", "Kaunlaran", "Libis", "Mabini", "Narra", "Pasig", "Poblacion", "San Antonio", "San Francisco", "Santa Quiteria", "Tala"],
        "Las Piñas": ["Almanza", "Bayan", "BF Resort", "CAA", "Danielle", "Golden Gate", "Gulong", "Ilaya", "Manuyo", "Pulang Luma", "San Nicolas", "Talora"],
        "Makati": ["Bel-Air", "Carmona", "Dasmariñas", "Forbes Park", "Guadalupe Nuevo", "Guadalupe Viejo", "Kasilawan", "La Paz", "Legaspi", "Magallanes", "Olympia", "Palanan"],
        "Malabon": ["Acacia", "Barrio San Agustin", "Bayan", "Dampalit", "Flores", "Hulong Duhat", "Maysilo", "Muntinlupa Poblacion", "Navotas", "Poblacion", "San Agustin", "Tinajeros"],
        "Mandaluyong": ["Barangay 1", "Barangay 2", "Barangay 3", "Burol", "Calamba", "Central", "Corazon de Jesus", "Daang Bakal", "Guitnang Bayan I-A", "Hagbay B", "Highway Hills", "Ilaya"],
        "Marikina": ["Barangka", "Calumpang", "Concepcion Uno", "Concepcion Dos", "Fortune", "Industrial Valley", "Jalandoni Estate", "Kalunasan", "Kapitan Kuwago", "Marikina Heights", "Nangka", "Parang"],
        "Muntinlupa": ["Alabang", "Bayanan", "Buli", "Cupang", "New Alabang Village", "Poblacion", "Putatan", "Santa Rosa", "Sucat", "Tunasan"],
        "Navotas": ["Bagumbayan North", "Bagumbayan South", "Bangkulasi", "Daanghari", "Kapitan Bayan", "Navotas", "North Poblacion", "Poblacion", "San Jose", "San Juan", "San Roque", "Tangos"],
        "Pasay": ["Barangay 1", "Barangay 2", "Barangay 3", "Cabinet", "Campo", "EDSA", "Fidel A", "Malibay", "Maricaban", "Pasay", "San Antonio", "San Rafael"],
        "Pasig": ["Bagong Ilog", "Caniogan", "Dela Paz", "Kalawaan", "Kapasigan", "Mabini", "Malinao", "Oranbo", "Pineda", "Rosario", "Sagad", "San Antonio"],
        "Pateros": ["A. Mabini", "Batingan", "Bayan", "Bel-Air", "Calvari", "Dulong Bayan", "Poblacion", "San Antonio", "San Roque", "Santa Ana", "Santo Rosario", "Tabacalera"],
        "San Juan": ["Balong Bato", "Bayanihan", "Corazon de Jesus", "Ermitaño", "Greenhills", "Isabel", "Kabayanan", "Little Baguio", "Maytunas", "Onse", "Pasadena", "Progreso"],
        "Taguig": ["Bagumbayan", "Bayan", "Calzada", "Cembo", "Comembo", "Fort Bonifacio", "Ibayo Tipas", "Lower Bicutan", "Maharlika", "Napindan", "Pinagsama", "Upper Bicutan"],
        "Valenzuela": ["Arkong Bato", "Balintawak", "Bignay", "Canumay", "Coloong", "Dalandanan", "Iskrin", "Karuhatan", "Langar", "Malanday", "Maysan", "Poblacion"]
    },
    "Region I": {
        "San Fernando (La Union)": ["Bacsil", "Bangar", "Cabaritan", "Cabaroan", "Caoayan", "Illing", "Pilar", "Poblacion", "San Francisco", "San Vicente", "Santa Rosa", "Santo Tomas"],
        "Dagupan": ["Bacayao", "Banon", "Baraso", "Beced", "Bolosan", "Bonuan Gues", "Bonuan Tondaligan", "Calmay", "Carangan", "Herrero", "Lucao", "Magsaysay"],
        "San Carlos (Pangasinan)": ["Balococ", "Bani", "Batang", "Bayo", "Benigno Aquino", "Bolid", "Buenavista", "Cabaruan", "Cacandian", "Caloocan", "Camaley", "Canan"],
        "Alaminos": ["Amangbobonan", "Balangobong", "Balitoc", "Banan", "Batang", "Baybay", "Bolinao", "Cabalitian", "Caypuy", "Dila", "Dulacac", "Estanza"],
        "Candon": ["Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Barangay 7", "Barangay 8", "Barangay 9", "Barangay 10", "Poblacion", "San Jose"],
        "Vigan": ["Ayudante", "Barra", "Blana", "Bulag", "Cabangtudan", "Cabo", "Calleja", "Camarao", "Candari", "Capangpangan", "Cora", "Gotinga"]
    },
    "Region II": {
        "Tuguegarao": ["Atulio", "Baggao", "Buntun", "Caggay", "Carig", "Cataggaman", "Dadda", "Gosi", "Luna", "Pengue", "Poblacion", "Tuguegarao"],
        "Cauayan": ["Alinam", "Bannawag", "Barani", "Cabulay", "Cagong", "Calabayan", "Casalah", "Daram", "La Purisima", "Mabini", "Poblacion", "San Antonio"],
        "Ilagan": ["Alibagu", "Allangigan", "Aluna", "Angadeng", "Bagah", "Baki", "Baligatan", "Balliao", "Bangad", "Barak", "Bar Barb", "Bawengi"],
        "Santiago": ["Abbag", "Ambalate", "Balintawak", "Banglac", "Cagas", "Calapangan", "Dapac", "Diki", "Gabo", "Linao", "Poblacion", "San Andres"]
    },
    "Region III": {
        "Bulacan - Balagtas": ["Balagtas", "Bungahan", "Buenavista", "Longos", "Panginay", "Sibul", "San Juan", "Tala"],
        "Bulacan - Baliuag": ["Baliuag", "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Binubungcan", "Makinabang", "Poblacion", "San Roque", "Santo Cristo", "Subic"],
        "Bulacan - Bocaue": ["Antipona", "Bagong Buhay", "Baliuag", "Batia", "Boyl", "Bundukan", "Camachin", "Canukin", "Duhat", "Igulam", "Poblacion", "Turo"],
        "Bulacan - Bulacan": ["Bagumbayan", "Calasipay", "Dakila", "Maugat", "Poblacion", "Salangan", "San Francisco", "San Jose", "Tao", "Santa Elenaogan"],
        "Bulacan - Bustos": ["Bani", "Bayan", "Buntucan", "Camuning", "Candyau", "Catacte", "Culianin", "Dampol", "Gabihan", "Poblacion", "San Pedro", "Tibagan"],
        "Bulacan - Calumpit": ["Balite", "Bunlo", "Calumpit", "Candelar", "Capitangan", "Guiset", "Iba", "Liang", "Lomboy", "Poblacion", "San Jose", "Santo Nino"],
        "Bulacan - Guiguinto": ["Balubayan", "Basil", "Bayanan", "Buguion", "Camangdaan", "Gabihan", "Ilang-Ilang", "Malabon", "Poblacion", "San Juan", "Tuktukan"],
        "Bulacan - Hagonoy": ["Balangay", "Bani", "Carillo", "Guinian", "Iba", "Lubiano", "Poblacion", "San Agustin", "San Antonio", "San Jose", "San Miguel", "Santa Cruz"],
        "Bulacan - Malolos": ["Ati", "Balayong", "Bayan", "Bolinao", "Bulacan", "Calero", "Caniogan", "Carry", "Francisco", "Guinhawa", "Mabini", "Poblacion"],
        "Bulacan - Marilao": ["Abangan Norte", "Abangan Sur", "Ibayo", "Lakandula", "Maysan", "Poblacion", "San Jose", "Santa Rosa", "Tawiran"],
        "Bulacan - Meycauayan": ["Bahay Pare", "Bayan", "Binangonan", "Calvario", "Camalig", "Caniogan", "Gazu", "Langka", "Lomboy", "Poblacion", "Saluysoy", "Wa-Wa"],
        "Bulacan - Norzagaray": ["Bigte", "Bitungol", "Buliran", "Casay", "Colong", "Darat", "Gulap", "Lalawin", "Matictic", "Pinamalacan", "Poblacion", "San Mateo"],
        "Bulacan - Obando": ["Binuangan", "Catmon", "Hindi", "Poblacion", "Salambao", "San Juan", "San Roque", "Tawiran"],
        "Bulacan - Pandi": ["Bunduk", "Calingcuan", "Casay", "Gulap", "Lalangan", "Poblacion", "San Jose", "San Roque", "Santa Cruz", "Santo Nino"],
        "Bulacan - Plaridel": ["Arroyo", "Bagong Sikat", "Bayan", "Binuunan", "Bulac", "Cabulalan", "Casayo", "Poblacion", "San Jose", "San Juan", "Santo Nino"],
        "Bulacan - Pulilan": ["Balatong Bato", "Bulac", "Calantiao", "Dampol", "Inaon", "Poblacion", "San Agustin", "San Jose", "San Juan", "Santo Nino"],
        "Bulacan - San Ildefonso": ["Akle", "Alcancia", "Banggant", "Camachin", "Gara", "Guevarra", "Jalbaj", "Poblacion", "San Jose", "San Juan", "Santo Nino"],
        "Bulacan - San Jose del Monte": ["Acevida", "Bagong Buhay", "Bagong Nazareth", "Bulac", "Dapdap", "Del Monte", "Fatima", "Gaya", "Graceville", "Greenhills", "Poblacion", "San Roque"],
        "Bulacan - San Miguel": ["Bala", "Banga", "Barcellano", "Bayan", "Binguan", "Caduang Tete", "Calumpang", "Candating", "Poblacion", "San Jose", "Santa Cruz"],
        "Bulacan - San Rafael": ["Baba", "Bacuan", "Bagsong", "Baliuag", "Bibiclat", "Calibato", "Cuin", "Dulong Bay", "Poblacion", "San Jose", "Sapang Bayan"],
        "Bulacan - Santa Maria": ["Bagbaguin", "Balasing", "Buenavista", "Bulac", "Cayetano", "Guyong", "Halayong", "Loma", "Poblacion", "San Jose", "San Roque"],
        "Bulacan - Santo Tomas": ["Amber", "Banghilan", "Caliu", "Camgamora", "Poblacion", "San Antonio", "San Bartolome", "San Jose", "San Juan", "Santa Elena"],
        "Bulacan - DRT": ["Bayabas", "Bono", "Camanggan", "Cebu", "Giron", "Poblacion", "San Anton", "Talbak"],
        "Pampanga - San Fernando": ["Alasas", "Baliti", "Bulaon", "Calulut", "Del Carmen", "Del Pilar", "Dolores", "Juliana", "Mabalacat", "Macapil", "Pabanlag", "Pampanga"],
        "Pampanga - Angeles": ["Agapito del Rosario", "Anunas", "Balibago", "Barrio", "Camarillo", "Clark", "Cuayan", "Lourdes", "Malabañas", "Pandacaqui", "Poblacion", "Pulungbulo"],
        "Pampanga - Mabalacat": ["Atlu-Bola", "Bical", "Bundagul", "Cacutud", "Calapacuan", "Camuning", "Dapdap", "Dau", "Dolores", "Iglesia", "Mabalacat", "Poblacion"],
        "Pampanga - Bacolor": ["Cabalan", "Calibutbut", "Dela Paz", "Mesdane", "Poblacion", "San Antonio", "San Juan", "San Vicente"],
        "Pampanga - Porac": ["Bacolor", "Bancal", "Calzadilla", "Camuning", "Dapdap", "Duhat", "Jalung", "Mabical", "Padi", "Poblacion", "San Jose", "San Roch"],
        "Tarlac - Tarlac City": ["Amucao", "Anao", "Balanti", "Binauganan", "Barangay 1", "Barangay 2", "Barangay 3", "Cruz", "Poblacion", "San Francisco", "San Roque", "Tarlac"],
        "Tarlac - Camiling": ["Balloc", "Banga", "Cabanab", "Caniogan", "Cayapa", "La Purisima", "Nagramban", "Poblacion", "San Jose", "Santo Domingo"],
        "Tarlac - Concepcion": ["Agua Caliente", "Balimbing", "Bucanan", "Calangain", "Cerro", "Dapdapan", "Luba", "Poblacion", "San Bartolome", "San Juan"],
        "Nueva Ecija - Cabanatuan": ["Abit", "Bakal", "Balite", "Banga", "Barrio", "Caudillo", "Copao", "Dicarma", "Faustino", "Gapan", "Ibaba", "Magsaysay"],
        "Nueva Ecija - Gapan": ["Bayan", "Bulak", "Caniogan", "Poblacion", "San Antonio", "San Juan", "San Nicolas", "Santo Cristo", "Santo Nino", "Tarcan"],
        "Nueva Ecija - Muñoz": ["Bangabang", "Ferdinand", "Galette", "Mabini", "Poblacion", "Rang-ayan", "San Alfonso", "San Andres", "San Antonio", "San Felipe"],
        "Bataan - Balanga": ["Bayan", "Camarcam", "Cupang", "Mabini", "Poblacion", "San Jose", "Santa Rosa", "Tenejer"],
        "Bataan - Dinalupihan": ["Antonio", "Bayan", "Bonifacio", "Burgos", "Calzadilla", "Colo", "Dap", "Gomez", "Hagonoy", "Luacan", "Mabini"],
        "Bataan - Mariveles": ["Alas", "Baseco", "Bayan", "Bicutan", "Cabcaben", "Camaya", "Copeland", "Igang", "Lamao", "Mabayo", "Poblacion"],
        "Bataan - Orani": ["Bagong Katipunan", "Balut", "Bayan", "Calero", "Centro", "Mabing", "Poblacion", "Sibul", "Tagumpay", "Tenejero"],
        "Zambales - Iba": ["Alinganan", "Amung", "Bani", "Bangat", "Camiling", "Dek", "Iba", "Ologic", "Poblacion", "San Juan", "Santo Nino"],
        "Zambales - Olongapo": ["Barretto", "Baloy", "Cabinet", "Calapacuan", "CNEW", "East Tapinac", "Gordon Heights", "Mabayuan", "New Cabalan", "Olongapo City", "Poblacion", "West Tapinac"],
        "Zambales - Subic": ["Asinan", "Baraca", "Calapacuan", "Cubi Point", "Ilwas", "Oyster Bay", "Poblacion", "San Antonio", "San Juan", "Santo Domingo"],
        "Aurora - Baler": ["Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Barangay 7", "Barangay 8", "Poblacion", "San Luis"]
    },
    "Region IV-A": {
        "Cavite City": ["Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Barangay 7", "Barangay 8", "Barangay 9", "Barangay 10", "Poblacion", "San Antonio"],
        "Bacoor": ["Alima", "Bayan", "Binis", "Camposa", "Cavite", "Dapdap", "Digman", "Halayo", "Ibabang Pook", "Kaingin", "Labac", "Ligaspi"],
        "Dasmariñas": ["Buenavista", "Cabuco", "Datu Esmael", "Paliparan", "Poblacion", "Sabang", "Salitran", "San Augustin", "San Jose", "San Lorenzo", "San Mateo", "San Miguel"],
        "Imus": ["Alapan 1-A", "Alapan 1-B", "Alapan 2-A", "Alapan 2-B", "Anabu 1-A", "Anabu 1-B", "Anabu 2-A", "Anabu 2-B", "Bayan", "Bucandala", "Poblacion", "Tanzuela"],
        "General Trias": ["Alingaro", "Bacao", "Bagas", "Baliwat", "Barangay 1", "Barangay 2", "Barangay 3", "Prinza", "Poblacion", "San Francisco", "Tapia", "Vetero"],
        "Tagaytay": ["Ashton", "Bagong Tanyag", "Calabuso", "Gulod", "Iruhin", "Kaybagal", "Mahabang Dahilig", "Maitim", "Poblacion", "San Jose", "Silang Junction", "Zanjo"],
        "Trece Martires": ["Aguinaldo", "Cavite", "Corregidor", "De Diego", "FTZ", "Lallana", "Luzviminda", "Mabato", "Poblacion", "Rosario", "San Agustin", "Velasco"]
    },
    "Region V": {
        "Legazpi": ["Bagacay", "Bigaa", "Binogsacan", "Bonot", "Bulang", "Bulihan", "Cagpitac", "Cavasi", "Dapdap", "Ditas", "Gogon", "Poblacion"],
        "Naga": ["Abella", "Bagumbayan", "Balatas", "Binanuahan", "Carangcang", "Conception", "Dayangdang", "Del Rosario", "Fidel Surtida", "Ginawan", "Loboc", "Poblacion"],
        "Iriga": ["Abriza", "Antipolo", "Bagasawe", "Balagbag", "Cagbunga", "Canaway", "Catmaran", "Cawayan", "Centro", "Cristo Rey", "Del Pilar", "Garcia"],
        "Tabaco": ["Agua Dulce", "Bacolod", "Bagacay", "Baki", "Balinad", "Basud", "Bay", "Binanowan", "Bodega", "Boro", "Cagraray", "Poblacion"],
        "Ligao": ["Allas", "Bangsud", "Barayong", "Basud", "Batallan", "Bayan", "Benguet", "Binucbuc", "Bog", "Bogabong", "Bonbon", "Poblacion"]
    },
    "Region VI": {
        "Iloilo City": ["Adison Irwin", "Aguinaldo", "Ardem", "Bajumpog", "Balabago", "Balantang", "Balo", "Banuyao", "Barrio Obrero", "Culasi", "Dapitan", "Poblacion"],
        "Bacolod": ["Alangilan", "Bago City", "Baliangao", "Banate", "Barotac Nuevo", "Barotac Viejo", "Batal", "Bayawan", "Binalbagan", "Cadiz", "Calatrava", "Candoni"],
        "Roxas": ["Balijuian", "Banon", "Barotac Viejo", "Culasi", "Dapitan", "Dumolog", "Iloilo", "Jimalalud", "La Carlota", "Lambunao", "Lemery", "Molo"],
        "Kabankalan": ["Binicuil", "Culasi", "Dancalan", "Daun", "Gil Montilla", "Hilabitan", "Inapuyan", "Isabela", "Julie", "Liqui", "Magballo", "Mananag"],
        "Victorias": ["Alim", "Batea", "Binongtoan", "Cruz", "Guintorilan", "Isabela", "Lambunao", "Pacol", "Poblacion", "Remedios", "San Fernando", "Tamboal"]
    },
    "Region VII": {
        "Cebu City": ["Apas", "Bacayan", "Banilad", "Basak", "Binaliw", "Busay", "Camputhaw", "Capitol Site", "Carreta", "Cebu City", "Centro", "Guadalupe"],
        "Lapu-Lapu": ["Agus", "Babag", "Bankal", "Baring", "Basak", "Buaya", "Cawhagan", "Gun-ob", "Ibo", "Looc", "Mactan", "Maribago"],
        "Mandaue": ["Alang-alang", "Baku", "Banilad", "Bario Kawayan", "Basak", "Cabalim", "Camiing", "Canduman", "Casili", "Casuntingan", "Centro", "Conception"],
        "Talisay": ["Camp 7", "Camp 8", "Culasi", "Dapitan", "Jaguimit", "Lagtang", "Lamac", "Liki", "Linao", "Lipa", "Poblacion", "San Roque"],
        "Toledo": ["Bahi", "Bogo", "Cabrador", "Calongbuy", "Caring", "Daanluis", "Dunong", "Ilaya", "Ipres", "Jacao", "Lutop", "Poblacion"]
    },
    "Region IX": {
        "Zamboanga City": ["Aygu", "Bali", "Barra", "Boalan", "Bungcia", "Cabaluay", "Cacao", "Calabasa", "Calarian", "Campo Islam", "Canelar", "Poblacion"],
        "Dipolog": ["Barra", "Central", "Dicay", "Estaca", "Galas", "Irasan", "Lower Miputak", "Miputak", "Oturnan", "Poblacion", "Santa Filomena", "Sinaman"],
        "Pagadian": ["Balintawak", "Bayan", "Blancia", "Bulan", "Ditoray", "Dumalinao", "Guintin", "Kagawasan", "Katipunan", "Lourdes", "Poblacion", "San Jose"],
        "Ipil": ["Bacalan", "Bali", "Baluno", "Bangan", "Bukid", "Datu PI", "Don Jose", "Guinoman", "Katipunan", "Lower T-away", "Masantol", "Poblacion"]
    },
    "Region X": {
        "Cagayan de Oro": ["Aguinaldo", "Balulang", "Barra", "Bayabas", "Bogon", "Bonbon", "Bulua", "Carmen", "Cugman", "Gusa", "Iponan", "Poblacion"],
        "Iligan": ["Acmac", "Bagong Silang", "Bonbonon", "Bunawan", "Dalipuga", "Del Carmen", "Digkilaan", "Hinaplanon", "Kalandangan", "Luinab", "Mandulog", "Poblacion"],
        "Malaybalay": ["Apo Apoy", "Bangcud", "Busco", "Can-ayan", "Capinon", "Claveria", "Cugman", "Dalapusan", "Gethsemane", "Impalambong", "Kibalabag", "Poblacion"],
        "Valencia": ["Bugcaon", "Colonia", "Guatemala", "Lumbaca", "Mailag", "Malaybalay", "Mt. Nebo", "Poblacion", "San Carlos", "San Jose", "San Nicolas", "Violeta"]
    },
    "Region XI": {
        "Davao": ["Acharon", "Agdao", "Bago Aplaya", "Bago Oshiro", "Baliok", "Bangsamoro", "Baracayan", "Bayanan", "Bucana", "Buhangin", "Calinan", "Poblacion"],
        "Digos": ["Aplaya", "Balabag", "Cogon", "Dapco", "Digos", "Dullao", "Goma", "Kapatagan", "Lungag", "Magsaysay", "Poblacion", "San Jose"],
        "Tagum": ["Apokon", "Bincungan", "Busaon", "Canocotan", "Cuambog", "La Filipina", "Libog", "Madaum", "Magdum", "Manay", "Poblacion", "San Agustin"],
        "Panabo": ["Aroman", "Buenavista", "Dapco", "J.P. Laurel", "Katipunan", "Kauswagan", "New Malitbog", "Poblacion", "Salvacion", "San Francisco", "San Nicolas", "Santo Nino"]
    },
    "Region XII": {
        "Koronadal": ["Assumption", "Banga", "Bulod", "Camarinos", "Carpenter", "Clinton", "General Paulino Santos", "Mabini", "Namnap", "Poblacion", "Rotonda", "San Jose"],
        "General Santos": ["Acmac", "Baluan", "Batang", "Buayan", "Cafe", "Calumpang", "Dadiangas", "Fatima", "Glamang", "Koronadal", "Labangal", "Poblacion"],
        "Kidapawan": ["Amas", "Balabag", "Bato", "Bayaan", "Binoligan", "Gawang", "Ilian", "Luhong", "Magsaysay", "Malabuan", "Mateo", "Poblacion"],
        "Tacurong": ["AFM", "Baras", "Buenaflor", "Calingan", "Carmen", "Dadiangas", "Kalandangan", "Katipunan", "Legaspi", "Libas", "Poblacion", "San Pablo"]
    },
    "Region XIII": {
        "Butuan": ["Agusan Pequeno", "Ambao", "Antong", "Bading", "Bancasi", "Bonbon", "Bucao", "Dagohoy", "Dugmongan", "Holy Redeemer", "Humilog", "Poblacion"],
        "Surigao": ["Aleonar", "Anao-aon", "Aras-asan", "Bad-as", "Bailug", "Balibayon", "BanBan", "Baug", "Biibih", "Cabugao", "Cagdianao", "Poblacion"],
        "Tandag": ["Alegria", "Bad-ok", "Banga", "Binitinan", "Bislig", "Bodega", "Buenavista", "Cagwait", "Caras-adan", "Cortez", "Mabua", "Poblacion"],
        "Cabadbaran": ["Acmac", "Bacuag", "Cagmas", "Calamba", "Calipe", "Campo", "Casiklan", "Catie", "Cuarto Cantil", "Danao", "Gibon", "Poblacion"]
    },
    "BARMM": {
        "Marawi": ["Banga", "Bayan", "Cadayon", "Datu sa Dansalan", "Datu Sultan", "Kaur", "Lilod Madaya", "Lumbaca Madaya", "Moncado Colony", "Poblacion", "Tomboc", "Wawalayan"],
        "Cotabato City": ["Bagua", "Bagua II", "Bagua III", "Bontowan", "Buayan", "Bulod", "Datu Balabaran", "Datu Piang", "Kalangan", "Poblacion", "Rosario", "Tamontaka"],
        "Jolo": ["Aguada", "Asturias", "Bajac", "Bajop", "Buan", "Busbus", "Campo Islam", "Canelar", "CCR", "Datu Bago", "Jolo", "Poblacion"],
        "Lanao del Sur": ["Bacolod", "Balindong", "Bayang", "Binidayan", "Bubong", "Calanogas", "Darul-Millennial", "Kapatagan", "Lumba-Bayabao", "Lumbaca Maranao", "Madalum", "Poblacion"]
    },
    "Region IV-B": {
        "Puerto Princesa": ["Abariong", "Bancao-bancao", "Binduyan", "Bугoy", "Cabayugan", "Concepcion", "Iraan", "Irawan", "Kalisan", "Mabini", "Maligaya", "Poblacion"],
        "Calapan": ["Bayanan", "Bulusan", "Calapa", "Camansinan", "Camalig", "Ibaba", "Mabini", "Masipit", "Poblacion", "San Vicente", "Santo Niño", "Suqui"],
        "Boac": ["Balansas", "Bamban", "Bangbangalon", "Binitayan", "Bulan", "Caney", "Cruz", "Duyay", "Laylay", "Malibay", "Matal", "Poblacion"],
        "Odiongan": ["Amat", "Annapol", "Balatikan", "Baras", "Bongabong", "Campa", "Canduyong", "Catag", "Duyay", "Gabong", "Linao", "Poblacion"]
    },
    "Region VIII": {
        "Tacloban": ["Abucay", "Alang-alang", "Anibogan", "Barugohaya", "Cabadbaran", "Cagangon", "Calanipawan", "Campo", "Cañacapan", "Gimba", "Poblacion", "San Jose"],
        "Ormoc": ["Alto", "Bagong", "Baluarte", "Barugohay", "Caba", "Cagpit", "Calingaran", "Caridad", "Centro", "Corte", "Donghol", "Poblacion"],
        "Borongan": ["Alang-alang", "Ambao", "Balacdas", "Banaag", "Baras", "Bato", "Baybay", "Bocar", "Buenavista", "Cagbuc", "Poblacion", "San Jose"],
        "Catbalogan": ["Cabas", "Cagdianao", "Calbiga", "Cambur", "CanJuan", "Gulang", "Hernani", "Jabul", "Libertad", "Mambajao", "Poblacion", "San Andres"]
    }
};

var zipCodes = {
    "NCR": { "Manila": "1000", "Quezon City": "1100", "Caloocan": "1400", "Las Piñas": "1700", "Makati": "1200", "Malabon": "1470", "Mandaluyong": "1550", "Marikina": "1800", "Muntinlupa": "1770", "Navotas": "1480", "Pasay": "1300", "Pasig": "1600", "Pateros": "1620", "San Juan": "1500", "Taguig": "1630", "Valenzuela": "1440" },
    "Region I": { "San Fernando (La Union)": "2500", "Dagupan": "2400", "San Carlos (Pangasinan)": "2420", "Alaminos": "2404", "Candon": "2710", "Vigan": "2700" },
    "Region II": { "Tuguegarao": "3500", "Cauayan": "3300", "Ilagan": "3300", "Santiago": "3310" },
    "Region III": { "San Fernando (Pampanga)": "2000", "Angeles": "2009", "Olongapo": "2200", "Baliuag": "3006", "Cabanatuan": "3100", "Tarlac City": "2300" },
    "Region IV-A": { "Cavite City": "4100", "Bacoor": "4102", "Dasmariñas": "4114", "Imus": "4103", "General Trias": "4107", "Tagaytay": "4120", "Trece Martires": "4109" },
    "Region V": { "Legazpi": "4500", "Naga": "4400", "Iriga": "4431", "Tabaco": "4511", "Ligao": "4502" },
    "Region VI": { "Iloilo City": "5000", "Bacolod": "6100", "Roxas": "5800", "Kabankalan": "6111", "Victorias": "6119" },
    "Region VII": { "Cebu City": "6000", "Lapu-Lapu": "6015", "Mandaue": "6014", "Talisay": "6045", "Toledo": "6038" },
    "Region IX": { "Zamboanga City": "7000", "Dipolog": "7100", "Pagadian": "7016", "Ipil": "7201" },
    "Region X": { "Cagayan de Oro": "9000", "Iligan": "9200", "Malaybalay": "8700", "Valencia": "8710" },
    "Region XI": { "Davao": "8000", "Digos": "8002", "Tagum": "8100", "Panabo": "8105" },
    "Region XII": { "Koronadal": "9506", "General Santos": "9500", "Kidapawan": "9400", "Tacurong": "9600" },
    "Region XIII": { "Butuan": "8600", "Surigao": "8400", "Tandag": "8300", "Cabadbaran": "8605" },
    "BARMM": { "Marawi": "9700", "Cotabato City": "9600", "Jolo": "7400", "Lanao del Sur": "9300" },
    "Region IV-B": { "Puerto Princesa": "5300", "Calapan": "5200", "Boac": "4900", "Odiongan": "5505" },
    "Region VIII": { "Tacloban": "6500", "Ormoc": "6541", "Borongan": "6800", "Catbalogan": "6700" }
};

function updateCities() {
    var region = document.getElementById('region').value;
    var citySelect = document.getElementById('city');
    var barangaySelect = document.getElementById('barangay');
    var zipInput = document.getElementById('zip_code');
    
    citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    zipInput.value = '';
    
    if (citiesData[region]) {
        var cities = Object.keys(citiesData[region]);
        for (var i = 0; i < cities.length; i++) {
            var option = document.createElement('option');
            option.value = cities[i];
            option.text = cities[i];
            citySelect.appendChild(option);
        }
    }
}

function updateBarangays() {
    var region = document.getElementById('region').value;
    var city = document.getElementById('city').value;
    var barangaySelect = document.getElementById('barangay');
    var zipInput = document.getElementById('zip_code');
    
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    zipInput.value = '';
    
    if (citiesData[region] && citiesData[region][city]) {
        var barangays = citiesData[region][city];
        for (var i = 0; i < barangays.length; i++) {
            var option = document.createElement('option');
            option.value = barangays[i];
            option.text = barangays[i];
            barangaySelect.appendChild(option);
        }
        
        if (zipCodes[region] && zipCodes[region][city]) {
            zipInput.value = zipCodes[region][city];
        }
    }
}

var municipalitiesData = {
    "NCR": {
        "Manila": ["Binondo", "Intramuros", "Santa Cruz", "Sampaloc", "San Nicolas", "Quiapo", "Santa Ana", "Pandacan", "Malate", "Paco", "Ermita", "Tondo"],
        "Quezon City": ["Batasan Hills", "Cubao", "Diliman", "Fairview", "Kamuning", "New Manila", "Novaletes", "Project 1-6", "Quezon Memorial Circle", "Santa Heights", "Tandang Sora", "West Triangle"],
        "Caloocan": ["Barrio San Jose", "Caloocan", "Kaunlaran", "Libis", "Mabini", "Narra", "Pasig", "Poblacion", "San Antonio", "San Francisco", "Santa Quiteria", "Tala"],
        "Las Piñas": ["Almanza", "Bayan", "BF Resort", "CAA", "Danielle", "Golden Gate", "Gulong", "Ilaya", "Manuyo", "Pulang Luma", "San Nicolas", "Talora"],
        "Makati": ["Bel-Air", "Carmona", "Dasmariñas", "Forbes Park", "Guadalupe Nuevo", "Guadalupe Viejo", "Kasilawan", "La Paz", "Legaspi", "Magallanes", "Olympia", "Palanan"],
        "Malabon": ["Acacia", "Barrio San Agustin", "Bayan", "Dampalit", "Flores", "Hulong Duhat", "Maysilo", "Muntinlupa Poblacion", "Navotas", "Poblacion", "San Agustin", "Tinajeros"],
        "Mandaluyong": ["Barangay 1", "Barangay 2", "Barangay 3", "Burol", "Calamba", "Central", "Corazon de Jesus", "Daang Bakal", "Guitnang Bayan I-A", "Hagbay B", "Highway Hills", "Ilaya"],
        "Marikina": ["Barangka", "Calumpang", "Concepcion Uno", "Concepcion Dos", "Fortune", "Industrial Valley", "Jalandoni Estate", "Kalunasan", "Kapitan Kuwago", "Marikina Heights", "Nangka", "Parang"],
        "Muntinlupa": ["Alabang", "Bayanan", "Buli", "Cupang", "New Alabang Village", "Poblacion", "Putatan", "Santa Rosa", "Sucat", "Tunasan"],
        "Navotas": ["Bagumbayan North", "Bagumbayan South", "Bangkulasi", "Daanghari", "Kapitan Bayan", "Navotas", "North Poblacion", "Poblacion", "San Jose", "San Juan", "San Roque", "Tangos"],
        "Pasay": ["Barangay 1", "Barangay 2", "Barangay 3", "Cabinet", "Campo", "EDSA", "Fidel A", "Malibay", "Maricaban", "Pasay", "San Antonio", "San Rafael"],
        "Pasig": ["Bagong Ilog", "Caniogan", "Dela Paz", "Kalawaan", "Kapasigan", "Mabini", "Malinao", "Oranbo", "Pineda", "Rosario", "Sagad", "San Antonio"],
        "Pateros": ["A. Mabini", "Batingan", "Bayan", "Bel-Air", "Calvari", "Dulong Bayan", "Poblacion", "San Antonio", "San Roque", "Santa Ana", "Santo Rosario", "Tabacalera"],
        "San Juan": ["Balong Bato", "Bayanihan", "Corazon de Jesus", "Ermitaño", "Greenhills", "Isabel", "Kabayanan", "Little Baguio", "Maytunas", "Onse", "Pasadena", "Progreso"],
        "Taguig": ["Bagumbayan", "Bayan", "Calzada", "Cembo", "Comembo", "Fort Bonifacio", "Ibayo Tipas", "Lower Bicutan", "Maharlika", "Napindan", "Pinagsama", "Upper Bicutan"],
        "Valenzuela": ["Arkong Bato", "Balintawak", "Bignay", "Canumay", "Coloong", "Dalandanan", "Iskrin", "Karuhatan", "Langar", "Malanday", "Maysan", "Poblacion"]
    },
    "Region I": {
        "San Fernando (La Union)": ["Bacsil", "Bangar", "Cabaritan", "Cabaroan", "Caoayan", "Illing", "Pilar", "Poblacion", "San Francisco", "San Vicente", "Santa Rosa", "Santo Tomas"],
        "Dagupan": ["Bacayao", "Banon", "Baraso", "Beced", "Bolosan", "Bonuan Gues", "Bonuan Tondaligan", "Calmay", "Carangan", "Herrero", "Lucao", "Magsaysay"],
        "San Carlos (Pangasinan)": ["Balococ", "Bani", "Batang", "Bayo", "Benigno Aquino", "Bolid", "Buenavista", "Cabaruan", "Cacandian", "Caloocan", "Camaley", "Canan"],
        "Alaminos": ["Amangbobonan", "Balangobong", "Balitoc", "Banan", "Batang", "Baybay", "Bolinao", "Cabalitian", "Caypuy", "Dila", "Dulacac", "Estanza"],
        "Candon": ["Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Barangay 7", "Barangay 8", "Barangay 9", "Barangay 10", "Poblacion", "San Jose"],
        "Vigan": ["Ayudante", "Barra", "Blana", "Bulag", "Cabangtudan", "Cabo", "Calleja", "Camarao", "Candari", "Capangpangan", "Cora", "Gotinga"]
    },
    "Region II": {
        "Tuguegarao": ["Atulio", "Baggao", "Buntun", "Caggay", "Carig", "Cataggaman", "Dadda", "Gosi", "Luna", "Pengue", "Poblacion", "Tuguegarao"],
        "Cauayan": ["Alinam", "Bannawag", "Barani", "Cabulay", "Cagong", "Calabayan", "Casalah", "Daram", "La Purisima", "Mabini", "Poblacion", "San Antonio"],
        "Ilagan": ["Alibagu", "Allangigan", "Aluna", "Angadeng", "Bagah", "Baki", "Baligatan", "Balliao", "Bangad", "Barak", "Bar Barb", "Bawengi"],
        "Santiago": ["Abbag", "Ambalate", "Balintawak", "Banglac", "Cagas", "Calapangan", "Dapac", "Diki", "Gabo", "Linao", "Poblacion", "San Andres"]
    },
    "Region III": {
        "San Fernando (Pampanga)": ["Alasas", "Baliti", "Bulaon", "Calulut", "Del Carmen", "Del Pilar", "Dolores", "Juliana", "Mabalacat", "Macapil", "Pabanlag", "Pampanga"],
        "Angeles": ["Agapito del Rosario", "Anunas", "Balibago", "Barrio", "Camarillo", "Clark", "Cuayan", "Lourdes", "Malabañas", "Pandacaqui", "Poblacion", "Pulungbulo"],
        "Olongapo": ["Barretto", "Baloy", "Cabinet", "Calapacuan", "CNEW", "East Tapinac", "Gordon Heights", "Mabayuan", "New Cabalan", "Olongapo City", "Poblacion", "West Tapinac"],
        "Baliuag": ["Baliuag", "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Binubungcan", "Makinabang", "Poblacion", "San Roque", "Santo Cristo", "Subic"],
        "Cabanatuan": ["Balite", "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Caudillo", "Poblacion", "San Juan Poblacion", "San Roque", "Santa Monica"],
        "Tarlac City": ["Amucao", "Anao", "Balanti", "Binauganan", "Barangay 1", "Barangay 2", "Barangay 3", "Cruz", "Poblacion", "San Francisco", "San Roque", "Tarlac"],
        "Mabalacat": ["Atlu-Bola", "Bical", "Bundagul", "Cacutud", "Calapacuan", "Camuning", "Dapdap", "Dau", "Dolores", "Iglesia", "Mabalacat", "Poblacion"]
    },
    "Region IV-A": {
        "Cavite City": ["Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Barangay 7", "Barangay 8", "Barangay 9", "Barangay 10", "Poblacion", "San Antonio"],
        "Bacoor": ["Alima", "Bayan", "Binis", "Camposa", "Cavite", "Dapdap", "Digman", "Halayo", "Ibabang Pook", "Kaingin", "Labac", "Ligaspi"],
        "Dasmariñas": ["Buenavista", "Cabuco", "Datu Esmael", "Paliparan", "Poblacion", "Sabang", "Salitran", "San Augustin", "San Jose", "San Lorenzo", "San Mateo", "San Miguel"],
        "Imus": ["Alapan 1-A", "Alapan 1-B", "Alapan 2-A", "Alapan 2-B", "Anabu 1-A", "Anabu 1-B", "Anabu 2-A", "Anabu 2-B", "Bayan", "Bucandala", "Poblacion", "Tanzuela"],
        "General Trias": ["Alingaro", "Bacao", "Bagas", "Baliwat", "Barangay 1", "Barangay 2", "Barangay 3", "Prinza", "Poblacion", "San Francisco", "Tapia", "Vetero"],
        "Tagaytay": ["Ashton", "Bagong Tanyag", "Calabuso", "Gulod", "Iruhin", "Kaybagal", "Mahabang Dahilig", "Maitim", "Poblacion", "San Jose", "Silang Junction", "Zanjo"],
        "Trece Martires": ["Aguinaldo", "Cavite", "Corregidor", "De Diego", "FTZ", "Lallana", "Luzviminda", "Mabato", "Poblacion", "Rosario", "San Agustin", "Velasco"]
    },
    "Region IV-B": {
        "Puerto Princesa": ["Bagong Silang", "Bancao-Bancao", "Irawan", "Lu不含", "Magsaysay", "Mandarina", "Poblacion", "San Pedro", "San Miguel", "Santa Cruz", "Santa Lucia", "Tagabinet"],
        "Calapan": ["Balite", "Bayanan", "Bulusan", "Calapan", "Camansinan", "Ilaya", "Mabini", "Poblacion", "San Vicente", "Santo Niño", "Sariaya", "Tibaj"],
        "Boac": ["Baladiang", "Bunot", "Cag condo", "Canelar", "Catubug", "Cogan", "Duyay", "Irene", "Lupac", "Mabini", "Poblacion", "Tambunan"],
        "Odiongan": ["Anak", "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Poblacion", "Progreso", "Rizal", "San Agustin", "Tabing", "Tuburan"]
    },
    "Region V": {
        "Legazpi": ["Bagacay", "Bigaa", "Binogsacan", "Bonot", "Bulang", "Bulihan", "Cagpitac", "Cavasi", "Dapdap", "Ditas", "Gogon", "Poblacion"],
        "Naga": ["Abella", "Bagumbayan", "Balatas", "Binanuahan", "Carangcang", "Conception", "Dayangdang", "Del Rosario", "Fidel Surtida", "Ginawan", "Loboc", "Poblacion"],
        "Iriga": ["Abriza", "Antipolo", "Bagasawe", "Balagbag", "Cagbunga", "Canaway", "Catmaran", "Cawayan", "Centro", "Cristo Rey", "Del Pilar", "Garcia"],
        "Tabaco": ["Agua Dulce", "Bacolod", "Bagacay", "Baki", "Balinad", "Basud", "Bay", "Binanowan", "Bodega", "Boro", "Cagraray", "Poblacion"],
        "Ligao": ["Allas", "Bangsud", "Barayong", "Basud", "Batallan", "Bayan", "Benguet", "Binucbuc", "Bog", "Bogabong", "Bonbon", "Poblacion"]
    },
    "Region VI": {
        "Iloilo City": ["Adison Irwin", "Aguinaldo", "Ardem", "Bajumpog", "Balabago", "Balantang", "Balo", "Bantuyao", "Barrio Obrero", "Culasi", "Dapitan", "Poblacion"],
        "Bacolod": ["Alangilan", "Bago City", "Baliangao", "Banate", "Barotac Nuevo", "Barotac Viejo", "Batal", "Bayawan", "Binalbagan", "Cadiz", "Calatrava", "Candoni"],
        "Roxas": ["Balijuian", "Banon", "Barotac Viejo", "Culasi", "Dapitan", "Dumolog", "Iloilo", "Jimalalud", "La Carlota", "Lambunao", "Lemery", "Molo"],
        "Kabankalan": ["Binicuil", "Culasi", "Dancalan", "Daun", "Gil Montilla", "Hilabitan", "Inapuyan", "Isabela", "Julie", "Liqui", "Magballo", "Mananag"],
        "Victorias": ["Alim", "Batea", "Binongtoan", "Cruz", "Guintorilan", "Isabela", "Lambunao", "Pacol", "Poblacion", "Remedios", "San Fernando", "Tamboal"]
    },
    "Region VII": {
        "Cebu City": ["Apas", "Bacayan", "Banilad", "Basak", "Binaliw", "Busay", "Camputhaw", "Capitol Site", "Carreta", "Cebu City", "Centro", "Guadalupe"],
        "Lapu-Lapu": ["Babag", "Baring", "Basak", "Canjulao", "Gun-ob", "Ibo", "Looc", "Mactan", "Maribago", "Pajac", "Poblacion", "Punta Engaño"],
        "Mandaue": ["Alang-alang", "Bakilid", "Banilad", "Basak", "Cabancalan", "Canduman", "Casili", "Centro", "Guizo", "Jagobiao", "Looc", "Mandaue"],
        "Talisay": ["Camp 1", "Camp 2", "Camp 3", "Jabella", "Lawaan", "Poblacion", "San Roque", "Santa Teresa", "Tabla", "Talisay", "Tangke", "Tunghaan"],
        "Toledo": ["Bangabanga", "Bato", "Bonial", "Bulan", "Cabulihan", "Calongbuyan", "Camp 1", "Camp 2", "Dumlog", "Ishmael", "Poblacion", "Toledo"]
    },
    "Region VIII": {
        "Tacloban": ["Abucay", "Barugohara", "Cabatuan", "Cag lob", "Calanipawan", "Campetic", "Mainit", "Palanog", "Poblacion District", "San Jose", "San Roque", "Santa Elena"],
        "Ormoc": ["Alto", "Bagakay", "Baliang", "Batuan", "Cabulihan", "Cag-utong", "Calunian", "Camp", "Candar", "Caridad", "Cebu", "Donghol"],
        "Borongan": ["Alang-alang", "Ambao", "Balacdas", "Banaag", "Baras", "Bato", "Baybay", "Bocar", "Buenavista", "Cagbuc", "Poblacion", "San Jose"],
        "Catbalogan": ["Cabas", "Cagdianao", "Calbiga", "Cambur", "CanJuan", "Gulang", "Hernani", "Jabul", "Libertad", "Mambajao", "Poblacion", "San Andres"]
    },
    "Region IX": {
        "Zamboanga City": ["Barrio", "Culian", "Mabuhay", "Poblacion", "San Jose", "Santa Cruz", "Santiago", "Santo Niño", "Talagon", "Tetuan", "Victoria", "Zamboanga"],
        "Dipolog": ["Barra", "Bil", "Cabulihan", "Central", "Dicay", "Don J. Purisima", "Galas", "Mcip", "Owaon", "Poblacion", "San Jose", "Santa Filomena"],
        "Pagadian": ["Balangasan", "Bubual", "Calumang", "Dapacan", "Kawayan", "Lourdes", "Masantol", "Poblacion", "San Francisco", "San Jose", "Tawagan", "Tuburan"],
        "Ipil": ["Bangkerohan", "Bulawan", "Campo", "Dinas", "Don Perfecto", "Ipil", "Kawayan", "Lunday", "Poblacion", "Sukarno", "Taus", "Ticol"]
    },
    "Region X": {
        "Cagayan de Oro": ["Aplaya", "Balulang", "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Bulua", "Carmen", "Macasandig", "Poblacion", "Xavier"],
        "Iligan": ["Abaga", "Bagong Silang", "Bunawan", "Dalipuga", "Del Carmen", "Hinaplanon", "Kiwalan", "Lilog", "Lumba-an", "Poblacion", "San Miguel", "Santa Elena"],
        "Malaybalay": ["Aglayan", "Bangcud", "Busco", "Cahid", "Calabugao", "Capitan Angel", "Impalambong", "Kulaman", "Mabuhay", "Poblacion", "San Jose", "Santo Domingo"],
        "Valencia": ["Bagontaas", "Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Biasong", "Bulua", "Cahumay", "Guintulunan", "Poblacion", "San Carlos"]
    },
    "Region XI": {
        "Davao": ["Baguio", "Bayanan", "Bucana", "Calinan", "Catalunan Grande", "Catalunan Pequeño", "Centro", "Davao", "Las Terrazas", "Matina", "Poblacion", "Talomo"],
        "Digos": ["Aplaya", "Balabag", "Cogon", "Digos", "Dulut", "Goma", "Kapatagan", "Matti", "Poblacion", "Roxas", "San Jose", "Santo Niño"],
        "Tagum": ["Apokon", "Bincungan", "Binuhang", "Canocotan", "Cuambog", "La Filipina", "Liboganon", "Magdum", "Mandaum", "Poblacion", "San Agustin", "San Miguel"],
        "Panabo": ["Acuras", "Baniano", "Dapco", "Jaguimitan", "Katipunan", "Kiotoy", "Lower Patong", "New Malitbog", "Poblacion", "San Francisco", "Santo Niño", "Talisayan"]
    },
    "Region XII": {
        "Koronadal": ["Assumption", "Buluan", "Caburay", "Calinguan", "Concepcion", "Mabini", "Poblacion", "Prosperidad", "Roxas", "San Jose", "San Roque", "Santo Domingo"],
        "General Santos": ["Apopong", "Baling", "Batang", "Buayan", "Cabuling", "Calumpang", "Dadiangas", " Fatima", "Labangal", "Lanton", "Poblacion", "San Jose"],
        "Kidapawan": ["Amas", "Balabag", "Bato", "Buayon", "Ginatilan", "Il Failure", "Kalaisan", "Magsaysay", "Malinan", "Poblacion", "Sanilo", "Tumbao"],
        "Tacurong": ["Ariahan", "Balatong", "Baras", "Bato", "Buenavista", "Calean", "D'gra Ace", "Kalandayan", "Ladting", "Poblacion", "San Emmanuel", "Tinantik"]
    },
    "Region XIII": {
        "Butuan": ["Ampay", "Bayan", "Buhangin", "CDN", "Doongan", "Lapu-Lapu", "Lemon", "Mahay", "Poblacion", "San Ignacio", "San Jose", "Santa Trinidad"],
        "Surigao": ["Alegria", "Bad-as", "Bautista", "Baybay", "Binauganan", "Canlanip", "Capalayan", "Cular", "Danao", "Poblacion", "Sampaguita", "Sugbay"],
        "Tandag": ["Bag-ot", "Bagte", "Bai", "Bisar", "Cagban", "Cortez", "Mabini", "Poblacion", "San Antonio", "San Jose", "Telaje", "Tuason"],
        "Cabadbaran": ["Bayabas", "Cabadbaran", "Calamba", "Comagascas", "Guintomoyan", "La Union", "Mabini", "Poblacion", "Rio Grande", "San Juan", "Sanghay", "Tagbino"]
    }
};
</script>
<style>
* {
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

body { 
    background: #f8f9fa; 
    transition: background-color 0.3s;
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
body.dark-mode { background: #0f172a; color: #e2e8f0; }
body.dark-mode .card { background: #1e293b; color: #e2e8f0; }
body.dark-mode .form-label { color: #e2e8f0; }
body.dark-mode .form-control { background: #334155; border-color: #475569; color: #f1f5f9; }
body.dark-mode .form-select { background: #334155; border-color: #475569; color: #f1f5f9; }
body.dark-mode .form-group select option { color: #f1f5f9; background: #1e293b; }
body.dark-mode .text-muted { color: #94a3b8 !important; }
body.dark-mode h3 { color: #e2e8f0; }
body.dark-mode h5 { color: #e2e8f0; }
h5 { color: #111827 !important; font-weight: 700 !important; font-size: 18px !important; }
h5 i { color: #667eea !important; }
.form-group label { color: #111827 !important; font-weight: 600 !important; }
body.dark-mode .alert { color: #f1f5f9; }
body.dark-mode .alert-danger { background: #7f1d1d; color: #fca5a5; border-color: #991b1b; }
body.dark-mode .alert-success { background: #064e3b; color: #6ee7b7; border-color: #065f46; }
body.dark-mode .login-btn { background: #4f46e5; color: white; }
body.dark-mode .login-btn:hover { background: #4338ca; }
body.dark-mode .password-requirements { background: #1e293b; color: #e2e8f0; border-color: #475569; }
body.dark-mode .password-requirements li { color: #94a3b8; }
body.dark-mode .password-requirements li.valid { color: #10b981; }
body.dark-mode .password-requirements li.invalid { color: #f87171; }

.password-requirements {
    padding: 10px;
    border-radius: 8px;
    background: transparent;
    margin-top: 8px;
}
.password-requirements small {
    color: #6b7280;
    font-weight: 500;
}
.password-rules {
    list-style: none;
    padding: 0;
    margin: 0;
}
.password-rules li {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.password-rules li i {
    width: 14px;
}
.password-rules li.text-danger {
    color: #dc2626;
}
.password-rules li.text-success {
    color: #059669;
}
</style>
<script>
(function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }
})();
</script>
</html>
