<?php
    // Show all errors from the PHP interpreter.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  
?>
<?php
/**
 * Process Sign Permit Application
 * 
 * @param array $post POST data from the form
 * @param array $files FILES data from the form
 * @param mysqli $conn Database connection
 * @return array Result array with 'success' boolean and 'message' string
 */
function processSignPermit($post, $files, $conn) {
    try {
        // Validate required fields
        $requiredFields = ['business_name', 'property_owner'];
        foreach ($requiredFields as $field) {
            if (empty($post[$field])) {
                return [
                    'success' => false,
                    'message' => 'Please fill in all required fields: ' . str_replace('_', ' ', $field)
                ];
            }
        }

        // Parse property owner name
        $ownerParts = parseFullName($post['property_owner'] ?? '');
        $ownerFirstName = $ownerParts['first_name'];
        $ownerLastName = $ownerParts['last_name'];

        $ownerStreet = $post['property_owner_address'];  
        $ownerCity = $post['property_owner_city'];  
        $ownerState = $post['property_owner_state_code'];     
        $ownerZip = $post['property_owner_zip_code'];       

        $businessStreet = $post['business_address'];     
        $businessCity = $post['business_city'] ;     
        $businessState = $post['business_state_code'] ;    
        $businessZip = $post['business_zip_code']  ;

        $applicantName = $post['agent_applicant'];
        $applicantStreet = $post['applicant_address'];    
        $applicantCity = $post['applicant_city'];      
        $applicantState = $post['applicant_state_code'];      
        $applicantZip = $post['applicant_zip_code'];
        
        // Parse contractor name
        $contractorParts = parseFullName($post['contractor'] ?? '');
        $contractorFirstName = $contractorParts['first_name'];
        $contractorLastName = $contractorParts['last_name'];

        // Parse agent/applicant address (for additional info if needed)
        $agentAddress = parseAddress($post['agent_applicant_address'] ?? '');

        // Prepare form resolved date
        $formDatetimeResolved = !empty($post['p_form_datetime_resolved']) 
            ? $post['p_form_datetime_resolved'] 
            : null;
        
        // Prepare form paid boolean
        $formPaidBool = isset($post['p_form_paid_bool']) && $post['p_form_paid_bool'] == '1' ? 1 : 0;

        // Prepare date (default to today if not provided)
        $spDate = !empty($post['p_date']) ? $post['p_date'] : date('Y-m-d');

        // Prepare permit number
        $spPermitNumber = $post['p_permit_number'] ?? null;

        // Prepare building coverage percentage
        $spBuildingCoveragePercent = $post['building_coverage'] ?? null;

        // Prepare permit fee
        $spPermitFee = $post['total_permit_fee'] ?? null;
        // Remove dollar sign and commas if present
        if ($spPermitFee) {
            $spPermitFee = str_replace(['$', ','], '', $spPermitFee);
        }

        // Determine sign type based on checkboxes
        $signTypes = [];
        if (isset($post['sign_type_freestanding']) && $post['sign_type_freestanding'] == '1') {
            $signTypes[] = 'Free-Standing';
        }
        if (isset($post['sign_type_wall_mounted']) && $post['sign_type_wall_mounted'] == '1') {
            $signTypes[] = 'Wall-Mounted';
        }
        if (isset($post['sign_type_temporary']) && $post['sign_type_temporary'] == '1') {
            $signTypes[] = 'Temporary';
        }
        if (isset($post['sign_type_directional']) && $post['sign_type_directional'] == '1') {
            $signTypes[] = 'Directional';
        }
        
        // Join sign types with comma
        $signType = !empty($signTypes) ? implode(', ', $signTypes) : null;

        // Prepare sign square footage
        $signSquareFootage = !empty($post['square_footage']) ? floatval($post['square_footage']) : null;

        // Prepare lettering height
        $letteringHeight = $post['lettering_height'] ?? null;

        // Prepare stored procedure call
        $stmt = $conn->prepare("CALL sp_insert_sign_permit_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        // Bind parameters
        // IN p_form_datetime_resolved DATETIME,
        // IN p_form_paid_bool BOOLEAN,
        // IN p_sp_applicant_id INT,
        // IN p_contractor_id INT,
        // IN p_sp_business_id INT,
        // IN p_sp_date DATE,
        // IN p_sp_permit_number VARCHAR(255),
        // IN p_sp_building_coverage_percent VARCHAR(255),
        // IN p_sp_permit_fee VARCHAR(255),
        // IN p_sp_owner_first_name VARCHAR(255),
        // IN p_sp_owner_last_name VARCHAR(255),
        // IN p_sp_owner_street VARCHAR(255),
        // IN p_sp_owner_city VARCHAR(255),
        // IN p_sp_owner_state_code CHAR(2),
        // IN p_sp_owner_zip_code VARCHAR(50),
        // IN p_sp_business_name VARCHAR(255),
        // IN p_sp_business_street VARCHAR(255),
        // IN p_sp_business_city VARCHAR(255),
        // IN p_sp_business_state_code CHAR(2),
        // IN p_sp_business_zip_code VARCHAR(50),
        // IN p_sp_contractor_first_name VARCHAR(255),
        // IN p_sp_contractor_last_name VARCHAR(255),
        // IN p_sp_contractor_phone_number VARCHAR(50),
        // IN p_sign_type VARCHAR(255),
        // IN p_sign_square_footage DECIMAL(12,2),
        // IN p_lettering_height VARCHAR(255)

        $stmt->bind_param(
            "ssssssssssssssssssssssssii",
            $spDate,                       // p_sp_date
            $spPermitNumber,               // p_sp_permit_number
            $spBuildingCoveragePercent,    // p_sp_building_coverage_percent
            $spPermitFee,                  // p_sp_permit_fee
            $ownerFirstName,               // p_sp_owner_first_name
            $ownerLastName,                // p_sp_owner_last_name
            $ownerStreet,       // p_sp_owner_street
            $ownerCity,         // p_sp_owner_city
            $ownerState,        // p_sp_owner_state_code
            $ownerZip,          // p_sp_owner_zip_code
            $post['business_name'],        // p_sp_business_name
            $businessStreet,    // p_sp_business_street
            $businessCity,      // p_sp_business_city
            $businessState,     // p_sp_business_state_code
            $businessZip,  // p_sp_business_zip_code
            $applicantName,
            $applicantStreet, 
            $applicantCity,  
            $applicantState,      
            $applicantZip,     
            $contractorFirstName,          // p_sp_contractor_first_name
            $contractorLastName,           // p_sp_contractor_last_name
            $post['contractor_phone'],     // p_sp_contractor_phone_number
            $signType,                     // p_sign_type
            $signSquareFootage,            // p_sign_square_footage
            $letteringHeight               // p_lettering_height
        );

        // Execute the stored procedure
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $new_form_id = $row['form_id'];
            $client_id = getUserId();

            while($conn->more_results()) {
              $conn->next_result();
            }

            // Link form to client
            $link_sql = "INSERT INTO client_forms (form_id, client_id) VALUES (?, ?)";
            $link_stmt = $conn->prepare($link_sql);
            $link_stmt->bind_param("ii", $new_form_id, $client_id);
            $link_stmt->execute();
            $link_stmt->close();
            
            return [
              'success' => true,
              'message' => 'Sign permit application submitted successfully!'
          ];
        } else {
            throw new Exception("Failed to retrieve form ID");
        }


        // Handle file uploads if any


    } catch (Exception $e) {
        $thiserrormsg = "Error in processSignPermit: " . $e->getMessage();
        error_log($thiserrormsg);
        return [
            'success' => false,
            /*'message' => 'An error occurred while processing your application. Please try again.'*/
            'message' => $thiserrormsg
        ];
    }
}

/**
 * Parse a full name into first and last name
 * 
 * @param string $fullName Full name string
 * @return array Array with 'first_name' and 'last_name' keys
 */
function parseFullName($fullName) {
    $fullName = trim($fullName);
    if (empty($fullName)) {
        return ['first_name' => null, 'last_name' => null];
    }

    $parts = explode(' ', $fullName, 2);
    return [
        'first_name' => $parts[0] ?? null,
        'last_name' => $parts[1] ?? null
    ];
}

/**
 * Parse an address string into components
 * Handles various address formats
 * 
 * @param string $address Full address string
 * @return array Array with 'street', 'city', 'state', 'zip' keys
 */
function parseAddress($address) {
    $address = trim($address);
    if (empty($address)) {
        return [
            'street' => null,
            'city' => null,
            'state' => null,
            'zip' => null
        ];
    }

    // Default: treat entire string as street address
    $result = [
        'street' => $address,
        'city' => null,
        'state' => null,
        'zip' => null
    ];

    // Try to parse if address contains commas (common format: Street, City, State ZIP)
    if (strpos($address, ',') !== false) {
        $parts = array_map('trim', explode(',', $address));
        
        if (count($parts) >= 2) {
            $result['street'] = $parts[0];
            
            // Last part might contain state and zip
            $lastPart = $parts[count($parts) - 1];
            
            // Try to extract state and zip from last part
            if (preg_match('/([A-Z]{2})\s+(\d{5}(?:-\d{4})?)/', $lastPart, $matches)) {
                $result['state'] = $matches[1];
                $result['zip'] = $matches[2];
                // Remove state and zip from last part to get city
                $city = trim(str_replace($matches[0], '', $lastPart));
                if (!empty($city)) {
                    $result['city'] = $city;
                } elseif (count($parts) >= 3) {
                    $result['city'] = $parts[count($parts) - 2];
                }
            } else {
                // No state/zip found, assume last part is city or city+state
                $result['city'] = $lastPart;
            }
        }
    }

    return $result;
}



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
    $result = processSignPermit($_POST, $_FILES, $conn);
    
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
$states_result = $conn->query("SELECT state_code FROM states ORDER BY state_code");
$states = [];
if ($states_result) {
    while ($row = $states_result->fetch_assoc()) {
        $states[] = $row['state_code'];
    }
}
$stateOptionsHtml = '<option value="">Select</option>';
foreach ($states as $state) {
    $selected = ($state === 'KY') ? ' selected' : '';
    $stateOptionsHtml .= '<option value="' . htmlspecialchars($state) . '"' . $selected . '>' . htmlspecialchars($state) . '</option>';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sign Permit Application</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body { 
      background: #f5f5f5; 
      font-family: Arial, sans-serif;
    }
    .form-container {
      background: white;
      max-width: 900px;
      margin: 20px auto;
      padding: 40px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .form-header {
      text-align: center;
      border-bottom: 2px solid #333;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }
    .form-header h1 {
      font-size: 18px;
      font-weight: bold;
      margin: 0;
    }
    .form-header h2 {
      font-size: 16px;
      font-weight: bold;
      margin: 5px 0 0 0;
    }
    .form-header p {
      font-size: 14px;
      margin: 5px 0 0 0;
    }
    .header-info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      font-size: 14px;
    }
    .header-info > div {
      flex: 1;
    }
    .section-title {
      background: #e0e0e0;
      padding: 8px 12px;
      font-weight: bold;
      font-size: 14px;
      margin-top: 25px;
      margin-bottom: 15px;
      text-transform: uppercase;
    }
    .form-group label {
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 5px;
    }
    .form-control, .form-control:focus {
      font-size: 14px;
    }
    .small-input {
      display: inline-block;
      width: auto;
      max-width: 200px;
    }
    .sign-type-checkbox {
      padding: 10px 0;
    }
    .file-upload-section {
      margin-top: 10px;
      padding: 10px;
      background: #f0f8ff;
      border-radius: 4px;
    }
    .fee-section {
      background: #f9f9f9;
      border: 1px solid #ddd;
      padding: 15px;
      margin: 15px 0;
    }
    .info-text {
      font-size: 12px;
      color: #666;
      font-style: italic;
      margin-top: 10px;
    }
    .footer-info {
      background: #f0f0f0;
      padding: 15px;
      margin-top: 30px;
      font-size: 13px;
      text-align: center;
      border: 1px solid #ddd;
    }
  </style>
</head>
<body>

<div class="form-container">
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="form-header">
    <h1>Danville-Boyle County Planning & Zoning Commission</h1>
    <h2>SIGN PERMIT</h2>
    <p>445 West Main Street P.O. Box 670</p>
    <p>Danville, Kentucky 40423</p>
    <p>Phone: 859.238.1235 | www.boyleplanning.org</p>
  </div>
  
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>

  <div class="header-info">
    <div>
      <strong>Date:</strong> <input type="date" name="p_date" class="form-control small-input d-inline" style="width: 150px;" value="<?php echo date('Y-m-d'); ?>">
    </div>
    <div>
      <strong>Permit #:</strong> <input type="text" name="p_permit_number" class="form-control small-input d-inline" style="width: 150px;">
    </div>
  </div>

  <form method="post" enctype="multipart/form-data">
    
    <!-- TYPE & NUMBER OF PROPOSED SIGNS -->
    <div class="section-title">TYPE & NUMBER OF PROPOSED SIGNS</div>

    <div class="row">
      <div class="col-md-3">
        <div class="form-group sign-type-checkbox">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="sign_type_freestanding" id="freestanding" value="1">
            <label class="form-check-label" for="freestanding">
              Free-Standing
            </label>
          </div>
        </div>
        <div class="form-group">
          <label>Number:</label>
          <input type="number" class="form-control" name="sign_number_freestanding" min="0" value="0">
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group sign-type-checkbox">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="sign_type_wall_mounted" id="wallmounted" value="1">
            <label class="form-check-label" for="wallmounted">
              Wall-Mounted
            </label>
          </div>
        </div>
        <div class="form-group">
          <label>Number:</label>
          <input type="number" class="form-control" name="sign_number_wall_mounted" min="0" value="0">
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group sign-type-checkbox">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="sign_type_temporary" id="temporary" value="1">
            <label class="form-check-label" for="temporary">
              Temporary
            </label>
          </div>
        </div>
        <div class="form-group">
          <label>Number:</label>
          <input type="number" class="form-control" name="sign_number_temporary" min="0" value="0">
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group sign-type-checkbox">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="sign_type_directional" id="directional" value="1">
            <label class="form-check-label" for="directional">
              Directional
            </label>
          </div>
        </div>
        <div class="form-group">
          <label>Number:</label>
          <input type="number" class="form-control" name="sign_number_directional" min="0" value="0">
        </div>
      </div>
    </div>

    <!-- OWNER INFORMATION -->
    <div class="section-title">OWNER INFORMATION</div>

    <div class="form-group">
      <label>Business Name:</label>
      <input type="text" class="form-control" name="business_name" required>
    </div>

    <div class="form-group">
            <label for="business_address">Street Address: *</label>
            <input type="text" class="form-control" id="business_address" name="business_address" required>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="business_city">City: *</label>
                    <input type="text" class="form-control" id="business_city" name="business_city" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="business_state_code">State: *</label>
                    <select class="form-control" id="business_state_code" name="business_state_code" required>
                        <option value="">Select</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>" <?php echo $state === 'KY' ? 'selected' : ''; ?>><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label for="business_zip_code">ZIP Code: *</label>
                    <input type="text" class="form-control" id="business_zip_code" name="business_zip_code" required>
                </div>
            </div>
        </div>

    <div class="form-group">
      <label>Property Owner:</label>
      <input type="text" class="form-control" name="property_owner" required>
    </div>

    <div class="form-group">
            <label for="property_owner_address">Street Address: *</label>
            <input type="text" class="form-control" id="property_owner_address" name="property_owner_address" required>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="property_owner_city">City: *</label>
                    <input type="text" class="form-control" id="property_owner_city" name="property_owner_city" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="property_owner_state_code">State: *</label>
                    <select class="form-control" id="property_owner_state_code" name="property_owner_state_code" required>
                        <option value="">Select</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>" <?php echo $state === 'KY' ? 'selected' : ''; ?>><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label for="property_owner_zip_code">ZIP Code: *</label>
                    <input type="text" class="form-control" id="property_owner_zip_code" name="property_owner_zip_code" required>
                </div>
            </div>
        </div>

    <div class="form-group">
      <label>Agent/Applicant:</label>
      <input type="text" class="form-control" name="agent_applicant">
    </div>

    <div class="form-group">
            <label for="applicant_address">Street Address: *</label>
            <input type="text" class="form-control" id="applicant_address" name="applicant_address" required>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="applicant_city">City: *</label>
                    <input type="text" class="form-control" id="applicant_city" name="applicant_city" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="applicant_state_code">State: *</label>
                    <select class="form-control" id="applicant_state_code" name="applicant_state_code" required>
                        <option value="">Select</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>" <?php echo $state === 'KY' ? 'selected' : ''; ?>><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label for="applicant_zip_code">ZIP Code: *</label>
                    <input type="text" class="form-control" id="applicant_zip_code" name="applicant_zip_code" required>
                </div>
            </div>
        </div>

    <!-- REQUIRED MATERIAL -->
    <div class="section-title">REQUIRED MATERIAL</div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="checklist_sign_specs" id="signspecs">
      <label class="form-check-label" for="signspecs">
        Sign specifications including a drawing with sign dimensions and size of lettering
      </label>
    </div>
    <div class="file-upload-section">
      <label for="file_sign_specs" class="font-weight-normal">Upload Sign Specifications:</label>
      <input type="file" class="form-control-file" name="file_sign_specs" id="file_sign_specs">
    </div>

    <div class="form-check mb-3 mt-3">
      <input class="form-check-input" type="checkbox" name="checklist_location_drawing" id="locationdrawing">
      <label class="form-check-label" for="locationdrawing">
        Drawing indicating the location of existing and proposed signs on the premises
      </label>
    </div>
    <div class="file-upload-section">
      <label for="file_location_drawing" class="font-weight-normal">Upload Location Drawing:</label>
      <input type="file" class="form-control-file" name="file_location_drawing" id="file_location_drawing">
    </div>

    <!-- CONSTRUCTION INFORMATION -->
    <div class="section-title">CONSTRUCTION INFORMATION</div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Contractor:</label>
          <input type="text" class="form-control" name="contractor">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Contact Phone:</label>
          <input type="text" class="form-control" name="contractor_phone">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
          <label>Square Footage of Each Sign:</label>
          <input type="text" class="form-control" name="square_footage">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Height of all Lettering:</label>
          <input type="text" class="form-control" name="lettering_height">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>% of Building Coverage:</label>
          <input type="text" class="form-control" name="building_coverage">
        </div>
      </div>
    </div>

    <p class="info-text">*If applicable, attach drawing of building facia dimensions</p>
    <div class="file-upload-section">
      <label for="file_building_facia" class="font-weight-normal">Upload Building Facia Drawing:</label>
      <input type="file" class="form-control-file" name="file_building_facia" id="file_building_facia">
    </div>

    <!-- PERMIT FEE -->
    <div class="section-title">PERMIT FEE</div>

    <div class="fee-section">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Number of Permanent Free-standing Signs:</label>
            <input type="number" class="form-control" name="fee_freestanding_count" min="0" value="0">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Fee (× $75.00):</label>
            <input type="text" class="form-control" name="fee_freestanding_amount" readonly>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Number of Permanent Wall-Mounted Signs:</label>
            <input type="number" class="form-control" name="fee_wall_mounted_count" min="0" value="0">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Fee (× $50.00):</label>
            <input type="text" class="form-control" name="fee_wall_mounted_amount" readonly>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Number of Temporary Signs:</label>
            <input type="number" class="form-control" name="fee_temporary_count" min="0" value="0">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Fee (× $25.00):</label>
            <input type="text" class="form-control" name="fee_temporary_amount" readonly>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Number of Directional Signs:</label>
            <input type="number" class="form-control" name="fee_directional_count" min="0" value="0">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Fee (× $5.00):</label>
            <input type="text" class="form-control" name="fee_directional_amount" readonly>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-12">
          <div class="form-group">
            <label><strong>Total Permit Fee:</strong></label>
            <input type="text" class="form-control" name="total_permit_fee" readonly style="font-weight: bold;">
          </div>
        </div>
      </div>
    </div>

    <!-- APPLICANT SIGNATURE -->
    <div class="section-title">APPLICANT SIGNATURE</div>

    <div class="row">
      <div class="col-md-8">
        <div class="form-group">
          <label>Applicant Signature:</label>
          <div style="border-bottom: 1px solid #333; min-height: 40px; margin: 10px 0;"></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Date:</label>
          <input type="date" class="form-control" name="applicant_signature_date">
        </div>
      </div>
    </div>

    <!-- ADMIN SECTION -->
    <div class="section-title" style="background: #d0d0d0;">ADMIN SECTION</div>

    <div class="form-group mt-4">
      <button class="btn btn-primary btn-lg btn-block" type="submit">Submit Application</button>
    </div>
  </form>

  <div class="footer-info">
    <strong>Submit Application to:</strong><br>
    Danville-Boyle County Planning and Zoning Commission<br>
    P.O. Box 670<br>
    Danville, KY 40423-0670<br>
    859.238.1235<br>
    www.boyleplanning.org
  </div>
</div>

<script>
// Calculate fees dynamically
document.addEventListener('DOMContentLoaded', function() {
  const feeInputs = {
    freestanding: { count: document.querySelector('[name="fee_freestanding_count"]'), amount: document.querySelector('[name="fee_freestanding_amount"]'), rate: 75 },
    wall_mounted: { count: document.querySelector('[name="fee_wall_mounted_count"]'), amount: document.querySelector('[name="fee_wall_mounted_amount"]'), rate: 50 },
    temporary: { count: document.querySelector('[name="fee_temporary_count"]'), amount: document.querySelector('[name="fee_temporary_amount"]'), rate: 25 },
    directional: { count: document.querySelector('[name="fee_directional_count"]'), amount: document.querySelector('[name="fee_directional_amount"]'), rate: 5 }
  };

  const totalFeeInput = document.querySelector('[name="total_permit_fee"]');

  function calculateFees() {
    let total = 0;
    Object.values(feeInputs).forEach(fee => {
      const count = parseInt(fee.count.value) || 0;
      const amount = count * fee.rate;
      fee.amount.value = '$' + amount.toFixed(2);
      total += amount;
    });
    totalFeeInput.value = '$' + total.toFixed(2);
  }

  Object.values(feeInputs).forEach(fee => {
    fee.count.addEventListener('input', calculateFees);
  });

  calculateFees();
});
</script>

</body>
</html>