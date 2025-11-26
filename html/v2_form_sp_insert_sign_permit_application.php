<?php
// Show all errors from the PHP interpreter.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Show all errors from the MySQLi Extension.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'config.php';
require_once __DIR__ . '/zoning_form_functions.php';

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
        // Extract form data
        $formData = extractSignPermitFormData($_POST, $_FILES);
        
        // Validate form data
        $errors = validateSignPermitData($formData);
        
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            // Insert the application
            $result = insertSignPermitApplication($conn, $formData);
            
            if ($result['success']) {
                $form_id = $result['form_id'];
                
                // Link form to client
                $linkResult = linkFormToClient($conn, $form_id, $client_id);
                
                if ($linkResult['success']) {
                    $success = 'Sign permit application submitted successfully!';
                } else {
                    $error = $linkResult['message'];
                }
            } else {
                $error = $result['message'];
            }
        }
    } catch (Exception $e) {
        error_log("Error in sign permit form: " . $e->getMessage());
        $error = 'An error occurred while processing your application. Please try again.';
    }
}

// Fetch state codes for dropdowns
$states = fetchStateCodes($conn);
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
    <div class="alert alert-danger"><?php echo $error; ?></div>
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

  <form method="post" enctype="multipart/form-data">
    
    <div class="header-info">
      <div>
        <strong>Date:</strong> 
        <input type="date" name="p_date" class="form-control small-input d-inline" style="width: 150px;" value="<?php echo date('Y-m-d'); ?>">
      </div>
      <div>
        <strong>Permit #:</strong> 
        <input type="text" name="p_permit_number" class="form-control small-input d-inline" style="width: 150px;">
      </div>
    </div>

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
      <label>Business Name: *</label>
      <input type="text" class="form-control" name="business_name" required>
    </div>

    <div class="form-group">
      <label for="business_address">Business Street Address: *</label>
      <input type="text" class="form-control" id="business_address" name="business_address" required>
    </div>

    <div class="row">
      <div class="col-md-5">
        <div class="form-group">
          <label for="business_city">Business City: *</label>
          <input type="text" class="form-control" id="business_city" name="business_city" required>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label for="business_state_code">Business State: *</label>
          <select class="form-control" id="business_state_code" name="business_state_code" required>
            <?php echo $stateOptionsHtml; ?>
          </select>
        </div>
      </div>
      <div class="col-md-5">
        <div class="form-group">
          <label for="business_zip_code">Business ZIP Code: *</label>
          <input type="text" class="form-control" id="business_zip_code" name="business_zip_code" required>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>Property Owner (Full Name): *</label>
      <input type="text" class="form-control" name="property_owner" required>
    </div>

    <div class="form-group">
      <label for="property_owner_address">Property Owner Street Address: *</label>
      <input type="text" class="form-control" id="property_owner_address" name="property_owner_address" required>
    </div>

    <div class="row">
      <div class="col-md-5">
        <div class="form-group">
          <label for="property_owner_city">Property Owner City: *</label>
          <input type="text" class="form-control" id="property_owner_city" name="property_owner_city" required>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label for="property_owner_state_code">Property Owner State: *</label>
          <select class="form-control" id="property_owner_state_code" name="property_owner_state_code" required>
            <?php echo $stateOptionsHtml; ?>
          </select>
        </div>
      </div>
      <div class="col-md-5">
        <div class="form-group">
          <label for="property_owner_zip_code">Property Owner ZIP Code: *</label>
          <input type="text" class="form-control" id="property_owner_zip_code" name="property_owner_zip_code" required>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>Agent/Applicant:</label>
      <input type="text" class="form-control" name="agent_applicant">
    </div>

    <div class="form-group">
      <label for="applicant_address">Applicant Street Address:</label>
      <input type="text" class="form-control" id="applicant_address" name="applicant_address">
    </div>

    <div class="row">
      <div class="col-md-5">
        <div class="form-group">
          <label for="applicant_city">Applicant City:</label>
          <input type="text" class="form-control" id="applicant_city" name="applicant_city">
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label for="applicant_state_code">Applicant State:</label>
          <select class="form-control" id="applicant_state_code" name="applicant_state_code">
            <?php echo $stateOptionsHtml; ?>
          </select>
        </div>
      </div>
      <div class="col-md-5">
        <div class="form-group">
          <label for="applicant_zip_code">Applicant ZIP Code:</label>
          <input type="text" class="form-control" id="applicant_zip_code" name="applicant_zip_code">
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
          <label>Contractor (Full Name):</label>
          <input type="text" class="form-control" name="contractor">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Contractor Phone:</label>
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
          <p class="info-text">Digital signature will be recorded upon submission</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Date:</label>
          <input type="date" class="form-control" name="applicant_signature_date">
        </div>
      </div>
    </div>

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