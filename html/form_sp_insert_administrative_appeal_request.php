<?php
// Show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'config.php';
requireLogin();

if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form metadata
        $p_form_paid_bool = 0; // Default to unpaid on submission
        $p_correction_form_id = isset($_POST['p_correction_form_id']) && $_POST['p_correction_form_id'] !== '' ? (int)$_POST['p_correction_form_id'] : null;
        
        // Dates
        $p_aar_hearing_date = $_POST['p_aar_hearing_date'] ?? null;
        $p_aar_submit_date = $_POST['p_aar_submit_date'] ?? date('Y-m-d');
        
        // Address information
        $p_aar_street_address = trim($_POST['p_aar_street_address'] ?? '');
        $p_aar_city_address = trim($_POST['p_aar_city_address'] ?? '');
        $p_state_code = trim($_POST['p_state_code'] ?? '');
        $p_aar_zip_code = trim($_POST['p_aar_zip_code'] ?? '');
        
        // Appeal details
        $p_aar_property_location = trim($_POST['p_aar_property_location'] ?? '');
        $p_aar_official_decision = trim($_POST['p_aar_official_decision'] ?? '');
        $p_aar_relevant_provisions = trim($_POST['p_aar_relevant_provisions'] ?? '');
        
        // Primary appellant
        $p_aar_appellant_first_name = trim($_POST['p_aar_appellant_first_name'] ?? '');
        $p_aar_appellant_last_name = trim($_POST['p_aar_appellant_last_name'] ?? '');
        
        // Additional appellants - convert array to JSON
        $p_additional_appellants = isset($_POST['appellants_names']) && is_array($_POST['appellants_names']) ? json_encode(array_filter($_POST['appellants_names'])) : null;
        
        // Adjacent property owner
        $p_adjacent_property_owner_street = trim($_POST['p_adjacent_property_owner_street'] ?? '');
        $p_adjacent_property_owner_city = trim($_POST['p_adjacent_property_owner_city'] ?? '');
        $p_adjacent_property_owner_state_code = trim($_POST['p_adjacent_property_owner_state_code'] ?? '');
        $p_adjacent_property_owner_zip = trim($_POST['p_adjacent_property_owner_zip'] ?? '');
        
        // Primary property owner
        $p_aar_property_owner_first_name = trim($_POST['p_aar_property_owner_first_name'] ?? '');
        $p_aar_property_owner_last_name = trim($_POST['p_aar_property_owner_last_name'] ?? '');
        
        // Additional property owners - convert array to JSON
        $p_additional_property_owners = isset($_POST['property_owners_names']) && is_array($_POST['property_owners_names']) ? json_encode(array_filter($_POST['property_owners_names'])) : null;

        // Basic validation
        if (empty($p_aar_appellant_first_name) || empty($p_aar_appellant_last_name)) {
            throw new Exception("Appellant's First and Last Name are required.");
        }
        if (empty($p_aar_property_owner_first_name) || empty($p_aar_property_owner_last_name)) {
            throw new Exception("Property Owner's First and Last Name are required.");
        }
        if (empty($p_aar_street_address) || empty($p_aar_city_address) || empty($p_state_code) || empty($p_aar_zip_code)) {
            throw new Exception("Full address (Street, City, State, Zip) is required.");
        }
        if (empty($p_aar_property_location)) {
            throw new Exception("Location of Property is required.");
        }
        if (empty($p_aar_official_decision)) {
            throw new Exception("Decision of Official is required.");
        }
        if (empty($p_aar_relevant_provisions)) {
            throw new Exception("Relevant Provisions of Zoning Ordinance are required.");
        }

        // Call the stored procedure with 23 parameters
        $sql = "CALL sp_insert_administrative_appeal_request(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        // Bind all parameters
        $stmt->bind_param('isssssssssssssssssss',
            $p_form_paid_bool,
            $p_aar_hearing_date,
            $p_aar_submit_date,
            $p_aar_street_address,
            $p_aar_city_address,
            $p_state_code,
            $p_aar_zip_code,
            $p_aar_property_location,
            $p_aar_official_decision,
            $p_aar_relevant_provisions,
            $p_aar_appellant_first_name,
            $p_aar_appellant_last_name,
            $p_additional_appellants,
            $p_adjacent_property_owner_street,
            $p_adjacent_property_owner_city,
            $p_adjacent_property_owner_state_code,
            $p_adjacent_property_owner_zip,
            $p_aar_property_owner_first_name,
            $p_aar_property_owner_last_name,
            $p_additional_property_owners
        );

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        // Get the result with form_id
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $form_id = $row['form_id'];
        $stmt->close();

        // Close the stored procedure result set
        while($conn->more_results()) {
            $conn->next_result();
        }

        // Link form to client
        $sql = "INSERT INTO client_forms(form_id, client_id) VALUES(?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $form_id, $client_id);
        $stmt->execute();
        $stmt->close();

        $success = "Form submitted successfully! Form ID: {$form_id}";

    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        if ($conn->errno) {
            $conn->rollback();
        }
    }
}

// Fetch states for dropdown
$states_result = $conn->query("SELECT state_code FROM states ORDER BY state_code");
$states = [];
if ($states_result) {
    while ($row = $states_result->fetch_assoc()) {
        $states[] = $row['state_code'];
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application for Administrative Appeal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
        .navbar {
            background-color: #dc3545;
            color: white;
        }
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .header-section {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px 0;
            border-bottom: 2px solid #dc3545;
        }
        .header-section h1 {
            color: #dc3545;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .form-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
            text-transform: uppercase;
        }
        .section-title {
            background: #dc3545;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .appellant-entry, .owner-entry {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            background: #f9f9f9;
            position: relative;
            border-radius: 4px;
        }
        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .info-text {
            font-size: 12px;
            color: #666;
            font-style: italic;
            margin-top: 5px;
            margin-bottom: 10px;
        }
        .footer-info {
            text-align: center;
            margin-top: 40px;
            font-size: 13px;
            color: #555;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .signature-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            min-height: 40px;
            margin: 10px 0;
        }
    </style>
    <script>
        let appellantCount = 0;
        let ownerCount = 0;

        function addAppellant() {
            appellantCount++;
            const container = document.getElementById('appellants-container');
            const div = document.createElement('div');
            div.className = 'appellant-entry';
            div.id = 'appellant-' + appellantCount;
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('appellant-${appellantCount}')">Remove</button>
                <div class="form-group mb-2">
                    <label>Full Name:</label>
                    <input type="text" class="form-control" name="appellants_names[]" placeholder="Appellant Full Name">
                </div>
            `;
            container.appendChild(div);
        }

        function addOwner() {
            ownerCount++;
            const container = document.getElementById('owners-container');
            const div = document.createElement('div');
            div.className = 'owner-entry';
            div.id = 'owner-' + ownerCount;
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('owner-${ownerCount}')">Remove</button>
                <div class="form-group mb-2">
                    <label>Full Name:</label>
                    <input type="text" class="form-control" name="property_owners_names[]" placeholder="Property Owner Full Name">
                </div>
            `;
            container.appendChild(div);
        }

        function removeElement(id) {
            const element = document.getElementById(id);
            if (element) {
                element.remove();
            }
        }
    </script>
</head>
<body>
<nav class="navbar navbar-dark">
    <div class="container">
        <span class="navbar-brand mb-0 h1">Client Portal — Administrative Appeal</span>
    </div>
</nav>

<div class="form-container">
    <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
    
    <div class="header-section">
        <h1 class="mb-0">Danville-Boyle County Planning & Zoning Commission</h1>
        <p>445 West Main Street P.O. Box 670</p>
        <p>Danville, Kentucky 40423</p>
    </div>

    <h2 class="form-title">APPLICATION FOR ADMINISTRATIVE APPEAL</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post">
        
        <div class="section-title">BOARD OF ADJUSTMENTS</div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="p_aar_hearing_date">Date of Hearing:</label>
                    <input type="date" class="form-control" id="p_aar_hearing_date" name="p_aar_hearing_date">
                    <small class="form-text text-muted">Typically filled by government staff</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="p_aar_submit_date">Date of Submission: *</label>
                    <input type="date" class="form-control" id="p_aar_submit_date" name="p_aar_submit_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
        </div>

        <div class="section-title">APPELLANT(S) INFORMATION</div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="p_aar_appellant_first_name">Primary Appellant First Name: *</label>
                    <input type="text" class="form-control" id="p_aar_appellant_first_name" name="p_aar_appellant_first_name" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="p_aar_appellant_last_name">Primary Appellant Last Name: *</label>
                    <input type="text" class="form-control" id="p_aar_appellant_last_name" name="p_aar_appellant_last_name" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Additional Appellants (Optional):</label>
            <p class="info-text">Add each additional appellant individually. Click "Add Another Appellant" to add more.</p>
            <div id="appellants-container"></div>
            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addAppellant()">
                + Add Another Appellant
            </button>
        </div>

        <div class="section-title">PROPERTY OWNER(S) OR BUSINESS ENTITY</div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="p_aar_property_owner_first_name">Primary Owner First Name: *</label>
                    <input type="text" class="form-control" id="p_aar_property_owner_first_name" name="p_aar_property_owner_first_name" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="p_aar_property_owner_last_name">Primary Owner Last Name: *</label>
                    <input type="text" class="form-control" id="p_aar_property_owner_last_name" name="p_aar_property_owner_last_name" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Additional Property Owners (Optional):</label>
            <p class="info-text">Please list names of all owners, directors and/or shareholders. Click "Add Another Owner" to add more.</p>
            <div id="owners-container"></div>
            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addOwner()">
                + Add Another Owner
            </button>
        </div>

        <div class="section-title">ADDRESS INFORMATION</div>
        
        <div class="form-group">
            <label for="p_aar_street_address">Street Address: *</label>
            <input type="text" class="form-control" id="p_aar_street_address" name="p_aar_street_address" required>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="p_aar_city_address">City: *</label>
                    <input type="text" class="form-control" id="p_aar_city_address" name="p_aar_city_address" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="p_state_code">State: *</label>
                    <select class="form-control" id="p_state_code" name="p_state_code" required>
                        <option value="">Select</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>" <?php echo $state === 'KY' ? 'selected' : ''; ?>><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label for="p_aar_zip_code">ZIP Code: *</label>
                    <input type="text" class="form-control" id="p_aar_zip_code" name="p_aar_zip_code" required>
                </div>
            </div>
        </div>

        <div class="section-title">APPEAL DETAILS</div>
        
        <div class="form-group">
            <label for="p_aar_property_location">Location of Property: *</label>
            <textarea class="form-control" id="p_aar_property_location" name="p_aar_property_location" rows="3" required></textarea>
        </div>

        <div class="form-group">
            <label for="p_aar_official_decision">Decision of Official from Which Appeal is Made: *</label>
            <textarea class="form-control" id="p_aar_official_decision" name="p_aar_official_decision" rows="3" required></textarea>
        </div>

        <div class="form-group">
            <label for="p_aar_relevant_provisions">Provisions of Zoning Ordinance in Relation to Appeal: *</label>
            <textarea class="form-control" id="p_aar_relevant_provisions" name="p_aar_relevant_provisions" rows="3" required></textarea>
        </div>

        <div class="section-title">ADJACENT PROPERTY OWNER (OPTIONAL)</div>
        
        <div class="form-group">
            <label for="p_adjacent_property_owner_street">Street Address:</label>
            <input type="text" class="form-control" id="p_adjacent_property_owner_street" name="p_adjacent_property_owner_street">
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="p_adjacent_property_owner_city">City:</label>
                    <input type="text" class="form-control" id="p_adjacent_property_owner_city" name="p_adjacent_property_owner_city">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="p_adjacent_property_owner_state_code">State:</label>
                    <select class="form-control" id="p_adjacent_property_owner_state_code" name="p_adjacent_property_owner_state_code">
                        <option value="">Select</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label for="p_adjacent_property_owner_zip">ZIP Code:</label>
                    <input type="text" class="form-control" id="p_adjacent_property_owner_zip" name="p_adjacent_property_owner_zip">
                </div>
            </div>
        </div> 

        <div class="signature-section">
            <p style="font-size: 13px;">I hereby certify that the information provided in this application is true and correct to the best of my knowledge.</p>
            <div class="form-group">
                <label for="appellant_signature">Appellant Signature (Type full name to acknowledge): *</label>
                <input type="text" class="form-control" id="appellant_signature" name="appellant_signature" placeholder="Type your full name" required>
            </div>
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-danger btn-lg" type="submit">Submit Application</button>
        </div>
    </form>

    <div class="footer-info">
        <p><strong>Phone:</strong> 859.238.1235</p>
        <p><strong>Website:</strong> www.boyleplanning.org</p>
    </div>
</div>
</body>
</html>