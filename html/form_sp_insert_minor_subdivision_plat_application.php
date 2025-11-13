<?php
    // Show all errors from the PHP interpreter.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  
?>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Technical form dates
        $application_filing_date = isset($_POST['application_filing_date']) && $_POST['application_filing_date'] !== '' ? $_POST['application_filing_date'] : null;
        $technical_review_date = isset($_POST['technical_review_date']) && $_POST['technical_review_date'] !== '' ? $_POST['technical_review_date'] : null;
        $preliminary_approval_date = isset($_POST['preliminary_approval_date']) && $_POST['preliminary_approval_date'] !== '' ? $_POST['preliminary_approval_date'] : null;
        $final_approval_date = isset($_POST['final_approval_date']) && $_POST['final_approval_date'] !== '' ? $_POST['final_approval_date'] : null;
        
        // Primary applicant fields
        $applicant_name = isset($_POST['applicant_name']) && $_POST['applicant_name'] !== '' ? $_POST['applicant_name'] : null;
        $officers_names = isset($_POST['officers_names']) && is_array($_POST['officers_names']) 
            ? json_encode(array_filter($_POST['officers_names'], function($v) { return $v !== ''; })) 
            : null;
        
        // Parse mailing address into components
        $applicant_mailing_address = isset($_POST['applicant_mailing_address']) && $_POST['applicant_mailing_address'] !== '' ? $_POST['applicant_mailing_address'] : null;
        // For now, store full address in street field - could be enhanced to parse
        $applicant_street = $applicant_mailing_address;
        $applicant_city = null;
        $applicant_state = null;
        $applicant_zip_code = null;
        $applicant_other_address = null;
        
        $applicant_phone = isset($_POST['applicant_phone']) && $_POST['applicant_phone'] !== '' ? $_POST['applicant_phone'] : null;
        $applicant_cell = isset($_POST['applicant_cell']) && $_POST['applicant_cell'] !== '' ? $_POST['applicant_cell'] : null;
        $applicant_email = isset($_POST['applicant_email']) && $_POST['applicant_email'] !== '' ? $_POST['applicant_email'] : null;
        
        // Additional applicants - filter out empty entries
        $additional_applicant_names = isset($_POST['additional_applicant_names']) && is_array($_POST['additional_applicant_names']) 
            ? json_encode(array_values(array_filter($_POST['additional_applicant_names'], function($v) { return $v !== ''; }))) 
            : null;
        
        // Build additional applicant officers structure
        $additional_applicant_officers = [];
        foreach ($_POST as $key => $value) {
            if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
                if (is_array($value)) {
                    $filtered = array_filter($value, function($v) { return $v !== ''; });
                    if (!empty($filtered)) {
                        $additional_applicant_officers[$matches[1]] = array_values($filtered);
                    }
                }
            }
        }
        $additional_applicant_officers_json = !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null;
        
        // Additional applicant addresses (stored as single strings)
        $additional_applicant_mailing_addresses = isset($_POST['additional_applicant_mailing_addresses']) && is_array($_POST['additional_applicant_mailing_addresses']) 
            ? $_POST['additional_applicant_mailing_addresses'] 
            : [];
        $additional_applicant_streets = !empty($additional_applicant_mailing_addresses) ? json_encode(array_values($additional_applicant_mailing_addresses)) : null;
        $additional_applicant_cities = null;
        $additional_applicant_states = null;
        $additional_applicant_zip_codes = null;
        $additional_applicant_other_addresses = null;
        
        $additional_applicant_phones = isset($_POST['additional_applicant_phones']) && is_array($_POST['additional_applicant_phones']) 
            ? json_encode(array_values($_POST['additional_applicant_phones'])) 
            : null;
        $additional_applicant_cells = isset($_POST['additional_applicant_cells']) && is_array($_POST['additional_applicant_cells']) 
            ? json_encode(array_values($_POST['additional_applicant_cells'])) 
            : null;
        $additional_applicant_emails = isset($_POST['additional_applicant_emails']) && is_array($_POST['additional_applicant_emails']) 
            ? json_encode(array_values($_POST['additional_applicant_emails'])) 
            : null;
        
        // Property owner fields - parse name into first/last
        $owner_name = isset($_POST['owner_name']) && $_POST['owner_name'] !== '' ? $_POST['owner_name'] : null;
        if ($owner_name) {
            $name_parts = explode(' ', $owner_name, 2);
            $owner_first_name = $name_parts[0];
            $owner_last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        } else {
            $owner_first_name = null;
            $owner_last_name = null;
        }
        
        $owner_mailing_address = isset($_POST['owner_mailing_address']) && $_POST['owner_mailing_address'] !== '' ? $_POST['owner_mailing_address'] : null;
        $owner_street = $owner_mailing_address;
        $owner_city = null;
        $owner_state = null;
        $owner_zip_code = null;
        $owner_other_address = null;
        
        $owner_phone = isset($_POST['owner_phone']) && $_POST['owner_phone'] !== '' ? $_POST['owner_phone'] : null;
        $owner_cell = isset($_POST['owner_cell']) && $_POST['owner_cell'] !== '' ? $_POST['owner_cell'] : null;
        $owner_email = isset($_POST['owner_email']) && $_POST['owner_email'] !== '' ? $_POST['owner_email'] : null;
        
        // Additional property owners
        $additional_owner_names = isset($_POST['additional_owner_names']) && is_array($_POST['additional_owner_names']) 
            ? json_encode(array_values(array_filter($_POST['additional_owner_names'], function($v) { return $v !== ''; }))) 
            : null;
        
        $additional_owner_mailing_addresses = isset($_POST['additional_owner_mailing_addresses']) && is_array($_POST['additional_owner_mailing_addresses']) 
            ? $_POST['additional_owner_mailing_addresses'] 
            : [];
        $additional_owner_streets = !empty($additional_owner_mailing_addresses) ? json_encode(array_values($additional_owner_mailing_addresses)) : null;
        $additional_owner_cities = null;
        $additional_owner_states = null;
        $additional_owner_zip_codes = null;
        $additional_owner_other_addresses = null;
        
        $additional_owner_phones = isset($_POST['additional_owner_phones']) && is_array($_POST['additional_owner_phones']) 
            ? json_encode(array_values($_POST['additional_owner_phones'])) 
            : null;
        $additional_owner_cells = isset($_POST['additional_owner_cells']) && is_array($_POST['additional_owner_cells']) 
            ? json_encode(array_values($_POST['additional_owner_cells'])) 
            : null;
        $additional_owner_emails = isset($_POST['additional_owner_emails']) && is_array($_POST['additional_owner_emails']) 
            ? json_encode(array_values($_POST['additional_owner_emails'])) 
            : null;
        
        // Surveyor fields - parse name
        $surveyor_name = isset($_POST['surveyor_name']) && $_POST['surveyor_name'] !== '' ? $_POST['surveyor_name'] : null;
        if ($surveyor_name) {
            $name_parts = explode(' ', $surveyor_name, 2);
            $surveyor_first_name = $name_parts[0];
            $surveyor_last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        } else {
            $surveyor_first_name = null;
            $surveyor_last_name = null;
        }
        $surveyor_id = null; // Always create new surveyor
        $surveyor_firm = isset($_POST['surveyor_firm']) && $_POST['surveyor_firm'] !== '' ? $_POST['surveyor_firm'] : null;
        $surveyor_phone = isset($_POST['surveyor_phone']) && $_POST['surveyor_phone'] !== '' ? $_POST['surveyor_phone'] : null;
        $surveyor_cell = isset($_POST['surveyor_cell']) && $_POST['surveyor_cell'] !== '' ? $_POST['surveyor_cell'] : null;
        $surveyor_email = isset($_POST['surveyor_email']) && $_POST['surveyor_email'] !== '' ? $_POST['surveyor_email'] : null;
        
        // Engineer fields - parse name
        $engineer_name = isset($_POST['engineer_name']) && $_POST['engineer_name'] !== '' ? $_POST['engineer_name'] : null;
        if ($engineer_name) {
            $name_parts = explode(' ', $engineer_name, 2);
            $engineer_first_name = $name_parts[0];
            $engineer_last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        } else {
            $engineer_first_name = null;
            $engineer_last_name = null;
        }
        $engineer_id = null; // Always create new engineer
        $engineer_firm = isset($_POST['engineer_firm']) && $_POST['engineer_firm'] !== '' ? $_POST['engineer_firm'] : null;
        $engineer_phone = isset($_POST['engineer_phone']) && $_POST['engineer_phone'] !== '' ? $_POST['engineer_phone'] : null;
        $engineer_cell = isset($_POST['engineer_cell']) && $_POST['engineer_cell'] !== '' ? $_POST['engineer_cell'] : null;
        $engineer_email = isset($_POST['engineer_email']) && $_POST['engineer_email'] !== '' ? $_POST['engineer_email'] : null;
        
        // Property information - parse address
        $property_address = isset($_POST['property_address']) && $_POST['property_address'] !== '' ? $_POST['property_address'] : null;
        $property_street = $property_address;
        $property_city = null;
        $property_state = null;
        $property_zip_code = null;
        $property_other_address = null;
        
        $parcel_number = isset($_POST['parcel_number']) && $_POST['parcel_number'] !== '' ? (int)$_POST['parcel_number'] : null;
        $acreage = isset($_POST['lot_acreage']) && $_POST['lot_acreage'] !== '' ? $_POST['lot_acreage'] : null;
        $current_zoning = isset($_POST['current_zoning']) && $_POST['current_zoning'] !== '' ? $_POST['current_zoning'] : null;
        
        // Subdivision plat details
        $minspa_proposed_plot_layout = isset($_POST['file_lot_layout']) ? 'Uploaded' : null;
        $minspa_topographic_survey = isset($_POST['file_topographic']) ? 'Uploaded' : null;
        $minspa_plat_restrictions = isset($_POST['file_restrictions']) ? 'Uploaded' : null;
        $minspa_property_owner_covenants = null;
        $minspa_association_covenants = null;
        $minspa_master_deed = null;
        
        // Checklist items
        $checklist_application = isset($_POST['checklist_application']) ? 1 : 0;
        $checklist_agency_signatures = isset($_POST['checklist_agency_signatures']) ? 1 : 0;
        $checklist_lot_layout = isset($_POST['checklist_lot_layout']) ? 1 : 0;
        $checklist_topographic = isset($_POST['checklist_topographic']) ? 1 : 0;
        $checklist_restrictions = isset($_POST['checklist_restrictions']) ? 1 : 0;
        $checklist_fees = isset($_POST['checklist_fees']) ? 1 : 0;
        
        // Handle file uploads - store file paths/names
        $file_agency_signatures = null;
        $file_lot_layout = null;
        $file_topographic = null;
        $file_restrictions = null;
        
        if (isset($_FILES['file_agency_signatures']) && $_FILES['file_agency_signatures']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/subdivision_agency_signatures/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_agency_signatures = $upload_dir . uniqid() . '_' . basename($_FILES['file_agency_signatures']['name']);
            move_uploaded_file($_FILES['file_agency_signatures']['tmp_name'], $file_agency_signatures);
        }
        
        if (isset($_FILES['file_lot_layout']) && $_FILES['file_lot_layout']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/subdivision_lot_layout/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_lot_layout = $upload_dir . uniqid() . '_' . basename($_FILES['file_lot_layout']['name']);
            move_uploaded_file($_FILES['file_lot_layout']['tmp_name'], $file_lot_layout);
        }
        
        if (isset($_FILES['file_topographic']) && $_FILES['file_topographic']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/subdivision_topographic/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_topographic = $upload_dir . uniqid() . '_' . basename($_FILES['file_topographic']['name']);
            move_uploaded_file($_FILES['file_topographic']['tmp_name'], $file_topographic);
        }
        
        if (isset($_FILES['file_restrictions']) && $_FILES['file_restrictions']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/subdivision_restrictions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_restrictions = $upload_dir . uniqid() . '_' . basename($_FILES['file_restrictions']['name']);
            move_uploaded_file($_FILES['file_restrictions']['tmp_name'], $file_restrictions);
        }
        
        // Signature fields
        $signature_date_1 = isset($_POST['signature_date_1']) && $_POST['signature_date_1'] !== '' ? $_POST['signature_date_1'] : null;
        $signature_name_1 = isset($_POST['signature_name_1']) && $_POST['signature_name_1'] !== '' ? $_POST['signature_name_1'] : null;
        $signature_date_2 = isset($_POST['signature_date_2']) && $_POST['signature_date_2'] !== '' ? $_POST['signature_date_2'] : null;
        $signature_name_2 = isset($_POST['signature_name_2']) && $_POST['signature_name_2'] !== '' ? $_POST['signature_name_2'] : null;
        
        // Admin fields
        $application_fee = isset($_POST['application_fee']) && $_POST['application_fee'] !== '' ? $_POST['application_fee'] : null;
        $recording_fee = isset($_POST['recording_fee']) && $_POST['recording_fee'] !== '' ? $_POST['recording_fee'] : null;
        $form_paid_bool = isset($_POST['form_paid_bool']) ? 1 : 0;
        $correction_form_id = isset($_POST['correction_form_id']) && $_POST['correction_form_id'] !== '' ? (int)$_POST['correction_form_id'] : null;
        
        // Prepare the stored procedure call
        $sql = "CALL sp_insert_minor_subdivision_plat_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        // Bind parameters (81 total parameters)
        $stmt->bind_param(
            "sssssssssssssssssssssssssssssssssssissssssssssssssssssssssissssssssssssssssssssss",
            // Technical dates (4)
            $application_filing_date, $technical_review_date, $preliminary_approval_date, $final_approval_date,
            // Primary applicant (10)
            $applicant_name, $officers_names, $applicant_street, $applicant_phone, $applicant_cell,
            $applicant_city, $applicant_state, $applicant_zip_code, $applicant_other_address, $applicant_email,
            // Additional applicants (10)
            $additional_applicant_names, $additional_applicant_officers_json, $additional_applicant_streets,
            $additional_applicant_phones, $additional_applicant_cells, $additional_applicant_cities,
            $additional_applicant_states, $additional_applicant_zip_codes, $additional_applicant_other_addresses,
            $additional_applicant_emails,
            // Property owner (10)
            $owner_first_name, $owner_last_name, $owner_street, $owner_phone, $owner_cell,
            $owner_city, $owner_state, $owner_zip_code, $owner_other_address, $owner_email,
            // Additional owners (9)
            $additional_owner_names, $additional_owner_streets, $additional_owner_phones,
            $additional_owner_cells, $additional_owner_cities, $additional_owner_states,
            $additional_owner_zip_codes, $additional_owner_other_addresses, $additional_owner_emails,
            // Surveyor (7)
            $surveyor_id, $surveyor_first_name, $surveyor_last_name, $surveyor_firm, 
            $surveyor_email, $surveyor_phone, $surveyor_cell,
            // Engineer (7)
            $engineer_id, $engineer_first_name, $engineer_last_name, $engineer_firm,
            $engineer_email, $engineer_phone, $engineer_cell,
            // Property info (8)
            $property_street, $property_city, $property_state, $property_zip_code, $property_other_address,
            $parcel_number, $acreage, $current_zoning,
            // Subdivision details (6)
            $minspa_topographic_survey, $minspa_proposed_plot_layout, $minspa_plat_restrictions,
            $minspa_property_owner_covenants, $minspa_association_covenants, $minspa_master_deed,
            // Checklist (6)
            $checklist_application, $checklist_agency_signatures, $checklist_lot_layout,
            $checklist_topographic, $checklist_restrictions, $checklist_fees,
            // Files (4)
            $file_agency_signatures, $file_lot_layout, $file_topographic, $file_restrictions,
        );
        
        $form_datetime_resolved = null; // Initially unresolved
        
        // Execute the stored procedure
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute stored procedure: " . $stmt->error);
        }
        
        // Get the result (form_id)
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $new_form_id = $row['form_id'];

            while($conn->more_results()) {
              $conn->next_result();
            }

            // Link form to client
            $link_sql = "INSERT INTO client_forms (form_id, client_id) VALUES (?, ?)";
            $link_stmt = $conn->prepare($link_sql);
            $link_stmt->bind_param("ii", $new_form_id, $client_id);
            $link_stmt->execute();
            $link_stmt->close();
            
            $success = "Minor Subdivision Plat Application submitted successfully! Form ID: " . $new_form_id;
        } else {
            throw new Exception("Failed to retrieve form ID");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $error = "Error submitting form: " . $e->getMessage();
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
  <title>Subdivision Plat WITHOUT Improvements Application</title>
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
    .additional-entry {
      border: 1px solid #ddd;
      padding: 15px;
      margin: 15px 0;
      background: #f9f9f9;
      position: relative;
    }
    .officer-entry {
      border: 1px solid #ccc;
      padding: 10px;
      margin: 10px 0;
      background: #fff;
      position: relative;
      border-radius: 4px;
    }
    .remove-btn {
      position: absolute;
      top: 10px;
      right: 10px;
    }
    .add-more-btn {
      margin: 15px 0 20px 0;
      display: inline-block;
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
      text-transform: uppercase;
    }
    .form-header h2 {
      font-size: 16px;
      font-weight: bold;
      margin: 5px 0 0 0;
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
    .checklist-item {
      padding: 8px 0;
      border-bottom: 1px solid #eee;
    }
    .checklist-item:last-child {
      border-bottom: none;
    }
    .signature-line {
      border-bottom: 1px solid #333;
      min-height: 40px;
      margin: 10px 0;
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
    .file-upload-section {
      margin-top: 10px;
      padding: 10px;
      background: #f0f8ff;
      border-radius: 4px;
    }
  </style>
  <script>
    const stateOptions = `<?php echo $stateOptionsHtml; ?>`;
    let applicantCount = 0;
    let ownerCount = 0;
    let officerCount = 0;
    let additionalOfficerCounters = {};

    function addOfficer() {
      officerCount++;
      const container = document.getElementById('officers-container');
      const div = document.createElement('div');
      div.className = 'officer-entry';
      div.id = 'officer-' + officerCount;
      div.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('officer-${officerCount}')">Remove</button>
        <div class="form-group mb-2">
          <label>Name:</label>
          <input type="text" class="form-control" name="officers_names[]" placeholder="Full name of officer/director/shareholder/member">
        </div>
      `;
      container.appendChild(div);
    }

    function addAdditionalApplicantOfficer(applicantId) {
      if (!additionalOfficerCounters[applicantId]) {
        additionalOfficerCounters[applicantId] = 0;
      }
      additionalOfficerCounters[applicantId]++;
      const container = document.getElementById('additional-officers-' + applicantId);
      const div = document.createElement('div');
      div.className = 'officer-entry';
      div.id = 'additional-officer-' + applicantId + '-' + additionalOfficerCounters[applicantId];
      div.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('additional-officer-${applicantId}-${additionalOfficerCounters[applicantId]}')">Remove</button>
        <div class="form-group mb-2">
          <label>Name:</label>
          <input type="text" class="form-control" name="additional_applicant_officers_${applicantId}[]" placeholder="Full name of officer/director/shareholder/member">
        </div>
      `;
      container.appendChild(div);
    }

    function addApplicant() {
      applicantCount++;
      const container = document.getElementById('additional-applicants');
      const div = document.createElement('div');
      div.className = 'additional-entry';
      div.id = 'applicant-' + applicantCount;
      div.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('applicant-${applicantCount}')">Remove</button>
        <h6 class="mb-3"><strong>Additional Applicant ${applicantCount}</strong></h6>

        <div class="form-group">
          <label>APPLICANT NAME:</label>
          <input type="text" class="form-control" name="additional_applicant_names[]">
        </div>

        <div class="form-group">
          <label>Names of Officers, Directors, Shareholders or Members (If Applicable):</label>
          <p class="info-text">Add each name individually below. Click "Add Another Name" to add more.</p>
          <div id="additional-officers-${applicantCount}"></div>
          <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addAdditionalApplicantOfficer(${applicantCount})">
            + Add Another Name
          </button>
        </div>
        <label><b>Contact Information:</b></label>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Street:</label>
              <input type="text" class="form-control" name="additional_applicant_streets[]">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Phone Number:</label>
              <input type="text" class="form-control" name="additional_applicant_phones[]">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Cell Number:</label>
              <input type="text" class="form-control" name="additional_applicant_cells[]">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>City:</label>
              <input type="text" class="form-control" name="additional_applicant_cities[]">
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <label>State:</label>
              <select class="form-control" name="additional_applicant_states[]" required>
              ${stateOptions}
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Zip Code:</label>
              <input type="text" class="form-control" name="additional_applicant_zip_codes[]">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Other Information:</label>
              <input type="text" class="form-control" name="additional_applicant_other_addresses[]">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>E-Mail Address:</label>
          <input type="email" class="form-control" name="additional_applicant_emails[]">
        </div>
      `;
      container.appendChild(div);
    }

    function addOwner() {
      ownerCount++;
      const container = document.getElementById('additional-owners');
      const div = document.createElement('div');
      div.className = 'additional-entry';
      div.id = 'owner-' + ownerCount;
      div.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('owner-${ownerCount}')">Remove</button>
        <h6 class="mb-3"><strong>Additional Property Owner ${ownerCount}</strong></h6>
        <div class="form-group">
          <label>Property Owner Name(s):</label>
          <input type="text" class="form-control" name="additional_owner_names[]">
        </div>
        <label><b>Contact Information:</b></label>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Street:</label>
              <input type="text" class="form-control" name="additional_owner_streets[]">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Phone Number:</label>
              <input type="text" class="form-control" name="additional_owner_phones[]">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Cell Number:</label>
              <input type="text" class="form-control" name="additional_owner_cells[]">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>City:</label>
              <input type="text" class="form-control" name="additional_owner_cities[]">
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <label>State:</label>
              <select class="form-control" name="additional_owner_states[]" required>
              ${stateOptions}
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Zip Code:</label>
              <input type="text" class="form-control" name="additional_owner_zip_codes[]">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Other Information:</label>
              <input type="text" class="form-control" name="additional_owner_other_addresses[]">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>E-Mail Address:</label>
          <input type="email" class="form-control" name="additional_owner_emails[]">
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

<div class="form-container">
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="form-header">
    <h1>Danville-Boyle County Planning & Zoning Commission</h1>
    <h2>Application for Subdivision Plat WITHOUT Improvements</h2>
  </div>
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
  <div class="row mb-3">
    <div class="col-md-6">
      <strong>Application Filing Date:</strong>
      <input type="date" name="application_filing_date" class="form-control small-input d-inline" style="width: 150px;">
    </div>
    <div class="col-md-6">
      <strong>Technical Review Date:</strong>
      <input type="date" name="technical_review_date" class="form-control small-input d-inline" style="width: 150px;">
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-6">
      <strong>Preliminary Approval Date:</strong>
      <input type="date" name="preliminary_approval_date" class="form-control small-input d-inline" style="width: 150px;">
    </div>
    <div class="col-md-6">
      <strong>Final Approval Date:</strong>
      <input type="date" name="final_approval_date" class="form-control small-input d-inline" style="width: 150px;">
    </div>
  </div>

  <form method="post" enctype="multipart/form-data">
    <!-- APPLICANT'S INFORMATION -->
    <div class="section-title">APPLICANT(S) INFORMATION</div>

    <div class="form-group">
      <label>1) APPLICANT NAME:</label>
      <input type="text" class="form-control" name="applicant_name">
    </div>

    <div class="form-group">
      <label>Names of Officers, Directors, Shareholders or Members (If Applicable):</label>
      <p class="info-text">Add each name individually below. Click "Add Another Name" to add more.</p>
      <div id="officers-container"></div>
      <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addOfficer()">
        + Add Another Name
      </button>
    </div>

    <label><b>Contact Information:</b></label>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Street:</label>
          <input type="text" class="form-control" name="applicant_street">
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="applicant_phone">
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="applicant_cell">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">
        <div class="form-group">
          <label>City:</label>
          <input type="text" class="form-control" name="applicant_city">
        </div>
      </div>
      <div class="col-md-1">
        <div class="form-group">
          <label>State:</label>
          <select class="form-control" name="applicant_state" required>
            <?php echo $stateOptionsHtml;?>
          </select>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Zip Code:</label>
          <input type="text" class="form-control" name="applicant_zip_code">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Other Information:</label>
          <input type="text" class="form-control" name="applicant_other_address">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>E-Mail Address:</label>
      <input type="email" class="form-control" name="applicant_email">
    </div>

    <div id="additional-applicants"></div>
    
    <button type="button" class="btn btn-secondary add-more-btn" onclick="addApplicant()">
      + Add Another Applicant
    </button>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>2) PROPERTY OWNER FIRST NAME:</label>
          <input type="text" class="form-control" name="applicant_first_name">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>PROPERTY OWNER LAST NAME:</label>
          <input type="text" class="form-control" name="applicant_last_name">
        </div>
      </div>
    </div>

    <label><b>Contact Information:</b></label>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Street:</label>
          <input type="text" class="form-control" name="owner_street">
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="owner_phone">
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="owner_cell">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">
        <div class="form-group">
          <label>City:</label>
          <input type="text" class="form-control" name="owner_city">
        </div>
      </div>
      <div class="col-md-1">
        <div class="form-group">
          <label>State:</label>
          <select class="form-control" name="owner_state" required>
            <?php echo $stateOptionsHtml;?>
          </select>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Zip Code:</label>
          <input type="text" class="form-control" name="owner_zip_code">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Other Information:</label>
          <input type="text" class="form-control" name="owner_other_address">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>E-Mail Address:</label>
      <input type="email" class="form-control" name="owner_email">
    </div>

    <div id="additional-owners"></div>
    
    <button type="button" class="btn btn-secondary add-more-btn" onclick="addOwner()">
      + Add Another Property Owner
    </button>

    <p class="info-text">*PLEASE USE ADDITIONAL PAGES IF NEEDED*</p>

    <div class="form-group">
      <label>3) SURVEYOR:</label>
      <input type="text" class="form-control" name="surveyor_name" placeholder="Name">
    </div>

    <div class="form-group">
      <label>Name of Firm:</label>
      <input type="text" class="form-control" name="surveyor_firm">
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="surveyor_phone">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="surveyor_cell">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>E-Mail Address:</label>
      <input type="email" class="form-control" name="surveyor_email">
    </div>

    <div class="form-group">
      <label>4) ENGINEER:</label>
      <input type="text" class="form-control" name="engineer_name" placeholder="Name">
    </div>

    <div class="form-group">
      <label>Name of Firm:</label>
      <input type="text" class="form-control" name="engineer_firm">
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="engineer_phone">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="engineer_cell">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>E-Mail Address:</label>
      <input type="email" class="form-control" name="engineer_email">
    </div>

    <!-- PROPERTY INFORMATION -->
    <div class="section-title">PROPERTY INFORMATION</div>

    <div class="form-group">
      <label>Property Address:</label>
      <input type="text" class="form-control" name="property_address">
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
          <label>PVA Parcel Number:</label>
          <input type="text" class="form-control" name="parcel_number">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Lot Acreage:</label>
          <input type="text" class="form-control" name="lot_acreage">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Current Zoning:</label>
          <input type="text" class="form-control" name="current_zoning">
        </div>
      </div>
    </div>

    <!-- APPLICATION CHECKLIST -->
    <div class="section-title">APPLICATION CHECKLIST</div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_application" id="check1">
        <label class="form-check-label" for="check1">
          A completed and signed Application
        </label>
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_agency_signatures" id="check2">
        <label class="form-check-label" for="check2">
          Agency Signature(s), as required by Subdivision Regulations
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_agency_signatures" class="font-weight-normal">Upload Agency Signatures:</label>
        <input type="file" class="form-control-file" name="file_agency_signatures" id="file_agency_signatures">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_lot_layout" id="check3">
        <label class="form-check-label" for="check3">
          Proposed Lot Layout prepared by a licensed surveyor or engineer depicting the various portion(s) of the property to be included in the proposed Subdivision Plat (Please include: two (2) - 18" x 24" and two (2) - 11" x 17" preliminary plat sets)
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_lot_layout" class="font-weight-normal">Upload Lot Layout:</label>
        <input type="file" class="form-control-file" name="file_lot_layout" id="file_lot_layout">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_topographic" id="check4">
        <label class="form-check-label" for="check4">
          Topographic Survey, if required
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_topographic" class="font-weight-normal">Upload Topographic Survey:</label>
        <input type="file" class="form-control-file" name="file_topographic" id="file_topographic">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_restrictions" id="check5">
        <label class="form-check-label" for="check5">
          Any proposed Plat Restrictions, Property or Condominium Owners Association Covenants, Master Deed or Restrictions, if applicable
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_restrictions" class="font-weight-normal">Upload Restrictions/Covenants:</label>
        <input type="file" class="form-control-file" name="file_restrictions" id="file_restrictions">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_fees" id="check6">
        <label class="form-check-label" for="check6">
          Filing and Recording Fees
        </label>
      </div>
    </div>

    <!-- APPLICANT'S CERTIFICATION -->
    <div class="section-title">APPLICANT'S CERTIFICATION</div>

    <p style="font-size: 13px;">I do hereby certify that, to the best of my knowledge and belief, all application materials have been submitted and that the information they contain is true and correct. Please attach additional signature pages if needed.</p>

    <p><strong>Signature of Applicant(s) and Property Owner(s):</strong></p>

    <div class="row">
      <div class="col-md-8">
        <div class="form-group">
          <label>1) Signature:</label>
          <div class="signature-line"></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Date:</label>
          <input type="date" class="form-control" name="signature_date_1">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>(please print name and title)</label>
      <input type="text" class="form-control" name="signature_name_1">
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="form-group">
          <label>2) Signature:</label>
          <div class="signature-line"></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Date:</label>
          <input type="date" class="form-control" name="signature_date_2">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>(please print name and title)</label>
      <input type="text" class="form-control" name="signature_name_2">
    </div>

    <p class="info-text">The foregoing signatures constitute all of the owners of the affected property necessary to convey fee title, their attorney, or their legally constituted attorney-in-fact. If the signature is of an attorney, then such signature is certification that the attorney represents each and every owner of the affected property. Please use additional signature pages, if needed.</p>

    <!-- ADMIN SECTION -->
    <div class="section-title" style="background: #d0d0d0;">REQUIRED FILING FEES MUST BE PAID BEFORE ANY APPLICATION WILL BE ACCEPTED</div>

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
    zoning@danvilleky.gov<br>
    www.boyleplanning.org
  </div>
</div>

</body>
</html