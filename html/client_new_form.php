<?php
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

// Get form types
$stmt = $conn->query("SELECT form_type FROM form_types ORDER BY form_type");
$form_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get states
$stmt = $conn->query("SELECT state_code FROM states ORDER BY state_code");
$states = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_type = $_POST['form_type'] ?? '';
    
    try {
        $conn->beginTransaction();
        
        // Insert base form
        $stmt = $conn->prepare("INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool) VALUES (?, NOW(), 0)");
        $stmt->execute([$form_type]);
        $form_id = $conn->lastInsertId();
        
        // Link to client
        $stmt = $conn->prepare("INSERT INTO client_forms (form_id, client_id) VALUES (?, ?)");
        $stmt->execute([$form_id, $client_id]);
        
        // Handle form-specific data based on form type
        switch ($form_type) {
            case 'Administrative Appeal Request':
                $stmt = $conn->prepare("CALL sp_insert_administrative_appeal_request(
                    NULL, FALSE, NULL,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?
                )");
                $stmt->execute([
                    $_POST['aar_hearing_date'] ?? null,
                    $_POST['aar_submit_date'] ?? null,
                    $_POST['aar_street_address'] ?? null,
                    $_POST['aar_city_address'] ?? null,
                    $_POST['state_code'] ?? null,
                    $_POST['aar_zip_code'] ?? null,
                    $_POST['aar_property_location'] ?? null,
                    $_POST['aar_official_decision'] ?? null,
                    $_POST['aar_relevant_provisions'] ?? null,
                    $_POST['appellant_first_name'] ?? null,
                    $_POST['appellant_last_name'] ?? null,
                    $_POST['adjacent_owner_street'] ?? null,
                    $_POST['adjacent_owner_city'] ?? null,
                    $_POST['adjacent_owner_state'] ?? null,
                    $_POST['adjacent_owner_zip'] ?? null
                ]);
                break;
                
            case 'Variance Application':
                $stmt = $conn->prepare("CALL sp_insert_variance_application(
                    NULL, FALSE, NULL, ?, ?, ?
                )");
                $stmt->execute([
                    $_POST['va_variance_request'] ?? null,
                    $_POST['va_proposed_conditions'] ?? null,
                    $_POST['PVA_parcel_number'] ?? null
                ]);
                break;
                
            case 'Zoning Verification Application':
                $stmt = $conn->prepare("CALL sp_insert_zoning_verification_application(
                    NULL, FALSE, NULL,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?
                )");
                $stmt->execute([
                    $_POST['zva_letter_content'] ?? null,
                    $_POST['zva_zoning_letter_street'] ?? null,
                    $_POST['zva_state_code'] ?? null,
                    $_POST['zva_zoning_letter_city'] ?? null,
                    $_POST['zva_zoning_letter_zip'] ?? null,
                    $_POST['zva_property_street'] ?? null,
                    $_POST['zva_property_state_code'] ?? null,
                    $_POST['zva_property_zip'] ?? null,
                    $_POST['property_city'] ?? null,
                    $_POST['zva_applicant_first_name'] ?? null,
                    $_POST['zva_applicant_last_name'] ?? null,
                    $_POST['zva_applicant_street'] ?? null,
                    $_POST['zva_applicant_city'] ?? null,
                    $_POST['zva_applicant_state_code'] ?? null,
                    $_POST['zva_applicant_zip_code'] ?? null,
                    $_POST['zva_applicant_phone_number'] ?? null,
                    $_POST['zva_applicant_fax_number'] ?? null,
                    $_POST['zva_owner_first_name'] ?? null,
                    $_POST['zva_owner_last_name'] ?? null,
                    $_POST['zva_owner_street'] ?? null,
                    $_POST['zva_owner_city'] ?? null,
                    $_POST['zva_owner_state_code'] ?? null,
                    $_POST['zva_owner_zip_code'] ?? null
                ]);
                break;
                
            case 'Conditional Use Permit Application':
                $stmt = $conn->prepare("CALL sp_insert_conditional_use_permit_application(
                    NULL, FALSE, NULL, ?, ?
                )");
                $stmt->execute([
                    $_POST['cupa_permit_request'] ?? null,
                    $_POST['cupa_proposed_conditions'] ?? null
                ]);
                break;
                
            case 'Zoning Map Amendment Application':
                $stmt = $conn->prepare("CALL sp_insert_zoning_map_amendment_application(
                    NULL, FALSE, NULL, ?
                )");
                $stmt->execute([
                    $_POST['zoning_map_amendment_request'] ?? null
                ]);
                break;
        }
        
        $conn->commit();
        $success = "Form submitted successfully! Form ID: " . $form_id;
        
    } catch(PDOException $e) {
        $conn->rollBack();
        $error = "Error submitting form: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Form</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { font-size: 24px; }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .btn:hover { background: #5568d3; }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-section {
            display: none;
            animation: fadeIn 0.3s;
        }
        .form-section.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Submit New Form</h1>
        <a href="client_dashboard.php">Back to Dashboard</a>
    </div>
    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Select Form Type</h2>
            <form method="POST" id="mainForm">
                <div class="form-group">
                    <label>Form Type *</label>
                    <select name="form_type" id="formTypeSelect" required onchange="showFormFields()">
                        <option value="">-- Select Form Type --</option>
                        <?php foreach ($form_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>">
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Administrative Appeal Request Fields -->
                <div id="form_Administrative_Appeal_Request" class="form-section">
                    <h3>Administrative Appeal Request Details</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Hearing Date</label>
                            <input type="date" name="aar_hearing_date">
                        </div>
                        <div class="form-group">
                            <label>Submit Date</label>
                            <input type="date" name="aar_submit_date">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Street Address</label>
                        <input type="text" name="aar_street_address">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="aar_city_address">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="state_code">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>">
                                        <?php echo htmlspecialchars($state); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="aar_zip_code">
                    </div>
                    <div class="form-group">
                        <label>Property Location</label>
                        <input type="text" name="aar_property_location">
                    </div>
                    <div class="form-group">
                        <label>Official Decision</label>
                        <textarea name="aar_official_decision"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Relevant Provisions</label>
                        <textarea name="aar_relevant_provisions"></textarea>
                    </div>
                    <h4>Appellant Information</h4>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="appellant_first_name">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="appellant_last_name">
                        </div>
                    </div>
                    <h4>Adjacent Property Owner (Optional)</h4>
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="adjacent_owner_street">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="adjacent_owner_city">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="adjacent_owner_state">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>">
                                        <?php echo htmlspecialchars($state); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="adjacent_owner_zip">
                    </div>
                </div>
                
                <!-- Variance Application Fields -->
                <div id="form_Variance_Applicatioin" class="form-section">
                    <h3>Variance Application Details</h3>
                    <div class="form-group">
                        <label>PVA Parcel Number</label>
                        <input type="number" name="PVA_parcel_number">
                        <div class="help-text">Property Valuation Administrator parcel identification number</div>
                    </div>
                    <div class="form-group">
                        <label>Variance Request</label>
                        <textarea name="va_variance_request" placeholder="Describe the variance you are requesting..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Proposed Conditions</label>
                        <textarea name="va_proposed_conditions" placeholder="Describe any proposed conditions or limitations..."></textarea>
                    </div>
                </div>
                
                <!-- Zoning Verification Application Fields -->
                <div id="form_Zoning_Verification_Application" class="form-section">
                    <h3>Zoning Verification Application Details</h3>
                    <div class="form-group">
                        <label>Letter Content</label>
                        <textarea name="zva_letter_content"></textarea>
                    </div>
                    <h4>Zoning Letter Address</h4>
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="zva_zoning_letter_street">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="zva_zoning_letter_city">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="zva_state_code">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>">
                                        <?php echo htmlspecialchars($state); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="zva_zoning_letter_zip">
                    </div>
                    <h4>Property Information</h4>
                    <div class="form-group">
                        <label>Property Street</label>
                        <input type="text" name="zva_property_street">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="property_city">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="zva_property_state_code">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>">
                                        <?php echo htmlspecialchars($state); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="zva_property_zip">
                    </div>
                    <h4>Applicant Information</h4>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="zva_applicant_first_name">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="zva_applicant_last_name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="zva_applicant_street">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="zva_applicant_city">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="zva_applicant_state_code">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>">
                                        <?php echo htmlspecialchars($state); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>ZIP Code</label>
                            <input type="text" name="zva_applicant_zip_code">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="zva_applicant_phone_number">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Fax Number</label>
                        <input type="text" name="zva_applicant_fax_number">
                    </div>
                    <h4>Property Owner Information (Optional)</h4>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="zva_owner_first_name">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="zva_owner_last_name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="zva_owner_street">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="zva_owner_city">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="zva_owner_state_code">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>">
                                        <?php echo htmlspecialchars($state); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="zva_owner_zip_code">
                    </div>
                </div>
                
                <!-- Conditional Use Permit Application Fields -->
                <div id="form_Conditional_Use_Permit_application" class="form-section">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Docket Number</label>
                            <input type="date" name="aar_hearing_date">
                        </div>
                        <div class="form-group">
                            <label>Public Hearing Date</label>
                            <input type="date" name="aar_submit_date">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Date Application Filed</label>
                            <input type="date" name="aar_hearing_date">
                        </div>
                        <div class="form-group">
                            <label>Pre-Application Hearing Date</label>
                            <input type="date" name="aar_submit_date">
                        </div>
                    </div>
                    <h3>Applicant(s) Information</h3>
                    <div class="form-group">
                        <label>Street Address</label>
                        <input type="text" name="aar_street_address">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="aar_city_address">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="state_code">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>">
                                        <?php echo htmlspecialchars($state); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="aar_zip_code">
                    </div>
                    <div class="form-group">
                        <label>Property Location</label>
                        <input type="text" name="aar_property_location">
                    </div>
                    <div class="form-group">
                        <label>Official Decision</label>
                        <textarea name="aar_official_decision"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Relevant Provisions</label>
                        <textarea name="aar_relevant_provisions"></textarea>
                    </div>
                    <h4>Appellant Information</h4>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="appellant_first_name">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="appellant_last_name">
                        </div>
                    </div>
                    <h4>Adjacent Property Owner (Optional)</h4>
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="adjacent_owner_street">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="adjacent_owner_city">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="adjacent_owner_state">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state); ?>">
                                        <?php echo htmlspecialchars($state); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="adjacent_owner_zip">
                    </div>
                </div>
                
                <!-- Zoning Map Amendment Application Fields -->
                <div id="form_Zoning_Map_Amendment_Application" class="form-section">
                    <h3>Zoning Map Amendment Application Details</h3>
                    <div class="form-group">
                        <label>Amendment Request</label>
                        <textarea name="zoning_map_amendment_request" placeholder="Describe the zoning map amendment you are requesting..."></textarea>
                    </div>
                </div>
                
                <div id="submitSection" style="display:none; margin-top: 30px;">
                    <button type="submit" class="btn">Submit Form</button>
                    <a href="client_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showFormFields() {
            const formType = document.getElementById('formTypeSelect').value;
            const allSections = document.querySelectorAll('.form-section');
            const submitSection = document.getElementById('submitSection');
            
            // Hide all sections
            allSections.forEach(section => section.classList.remove('active'));
            
            if (formType) {
                // Show selected form section
                const selectedSection = document.getElementById('form_' + formType.replace(/\s+/g, '_').replace(/[()]/g, ''));
                if (selectedSection) {
                    selectedSection.classList.add('active');
                }
                submitSection.style.display = 'block';
            } else {
                submitSection.style.display = 'none';
            }
        }
    </script>
</body>
</html>