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
    // Header fields
    $docket_number = isset($_POST['docket_number']) && $_POST['docket_number'] !== '' ? $_POST['docket_number'] : null;
    $public_hearing_date = isset($_POST['public_hearing_date']) && $_POST['public_hearing_date'] !== '' ? $_POST['public_hearing_date'] : null;
    $date_application_filed = isset($_POST['date_application_filed']) && $_POST['date_application_filed'] !== '' ? $_POST['date_application_filed'] : null;
    $pre_application_meeting_date = isset($_POST['pre_application_meeting_date']) && $_POST['pre_application_meeting_date'] !== '' ? $_POST['pre_application_meeting_date'] : null;
    
    // Primary applicant fields
    $applicant_name = isset($_POST['applicant_name']) && $_POST['applicant_name'] !== '' ? $_POST['applicant_name'] : null;
    $officers_names = isset($_POST['officers_names']) && is_array($_POST['officers_names']) ? json_encode($_POST['officers_names']) : null;
    $applicant_mailing_address = isset($_POST['applicant_mailing_address']) && $_POST['applicant_mailing_address'] !== '' ? $_POST['applicant_mailing_address'] : null;
    $applicant_phone = isset($_POST['applicant_phone']) && $_POST['applicant_phone'] !== '' ? $_POST['applicant_phone'] : null;
    $applicant_cell = isset($_POST['applicant_cell']) && $_POST['applicant_cell'] !== '' ? $_POST['applicant_cell'] : null;
    $applicant_email = isset($_POST['applicant_email']) && $_POST['applicant_email'] !== '' ? $_POST['applicant_email'] : null;
    
    // Additional applicants
    $additional_applicant_names = isset($_POST['additional_applicant_names']) && is_array($_POST['additional_applicant_names']) ? json_encode($_POST['additional_applicant_names']) : null;
    $additional_applicant_officers = [];
    foreach ($_POST as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value)) {
                $additional_applicant_officers[$matches[1]] = $value;
            }
        }
    }
    $additional_applicant_officers = !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null;
    $additional_applicant_mailing_addresses = isset($_POST['additional_applicant_mailing_addresses']) && is_array($_POST['additional_applicant_mailing_addresses']) ? json_encode($_POST['additional_applicant_mailing_addresses']) : null;
    $additional_applicant_phones = isset($_POST['additional_applicant_phones']) && is_array($_POST['additional_applicant_phones']) ? json_encode($_POST['additional_applicant_phones']) : null;
    $additional_applicant_cells = isset($_POST['additional_applicant_cells']) && is_array($_POST['additional_applicant_cells']) ? json_encode($_POST['additional_applicant_cells']) : null;
    $additional_applicant_emails = isset($_POST['additional_applicant_emails']) && is_array($_POST['additional_applicant_emails']) ? json_encode($_POST['additional_applicant_emails']) : null;
    
    // Property owner fields
    $owner_name = isset($_POST['owner_name']) && $_POST['owner_name'] !== '' ? $_POST['owner_name'] : null;
    $owner_mailing_address = isset($_POST['owner_mailing_address']) && $_POST['owner_mailing_address'] !== '' ? $_POST['owner_mailing_address'] : null;
    $owner_phone = isset($_POST['owner_phone']) && $_POST['owner_phone'] !== '' ? $_POST['owner_phone'] : null;
    $owner_cell = isset($_POST['owner_cell']) && $_POST['owner_cell'] !== '' ? $_POST['owner_cell'] : null;
    $owner_email = isset($_POST['owner_email']) && $_POST['owner_email'] !== '' ? $_POST['owner_email'] : null;
    
    // Additional property owners
    $additional_owner_names = isset($_POST['additional_owner_names']) && is_array($_POST['additional_owner_names']) ? json_encode($_POST['additional_owner_names']) : null;
    $additional_owner_mailing_addresses = isset($_POST['additional_owner_mailing_addresses']) && is_array($_POST['additional_owner_mailing_addresses']) ? json_encode($_POST['additional_owner_mailing_addresses']) : null;
    $additional_owner_phones = isset($_POST['additional_owner_phones']) && is_array($_POST['additional_owner_phones']) ? json_encode($_POST['additional_owner_phones']) : null;
    $additional_owner_cells = isset($_POST['additional_owner_cells']) && is_array($_POST['additional_owner_cells']) ? json_encode($_POST['additional_owner_cells']) : null;
    $additional_owner_emails = isset($_POST['additional_owner_emails']) && is_array($_POST['additional_owner_emails']) ? json_encode($_POST['additional_owner_emails']) : null;
    
    // Attorney fields
    $attorney_name = isset($_POST['attorney_name']) && $_POST['attorney_name'] !== '' ? $_POST['attorney_name'] : null;
    $law_firm = isset($_POST['law_firm']) && $_POST['law_firm'] !== '' ? $_POST['law_firm'] : null;
    $attorney_phone = isset($_POST['attorney_phone']) && $_POST['attorney_phone'] !== '' ? $_POST['attorney_phone'] : null;
    $attorney_cell = isset($_POST['attorney_cell']) && $_POST['attorney_cell'] !== '' ? $_POST['attorney_cell'] : null;
    $attorney_email = isset($_POST['attorney_email']) && $_POST['attorney_email'] !== '' ? $_POST['attorney_email'] : null;
    
    // Property information
    $property_address = isset($_POST['property_address']) && $_POST['property_address'] !== '' ? $_POST['property_address'] : null;
    $parcel_number = isset($_POST['parcel_number']) && $_POST['parcel_number'] !== '' ? $_POST['parcel_number'] : null;
    $acreage = isset($_POST['acreage']) && $_POST['acreage'] !== '' ? $_POST['acreage'] : null;
    $current_zoning = isset($_POST['current_zoning']) && $_POST['current_zoning'] !== '' ? $_POST['current_zoning'] : null;
    
    // FLUM request
    $flum_request = isset($_POST['flum_request']) && $_POST['flum_request'] !== '' ? $_POST['flum_request'] : null;
    
    // Findings
    $finding_type = isset($_POST['finding_type']) && $_POST['finding_type'] !== '' ? $_POST['finding_type'] : null;
    $findings_explanation = isset($_POST['findings_explanation']) && $_POST['findings_explanation'] !== '' ? $_POST['findings_explanation'] : null;
    
    // Checklist items
    $checklist_application = isset($_POST['checklist_application']) ? 1 : 0;
    $checklist_exhibit = isset($_POST['checklist_exhibit']) ? 1 : 0;
    $checklist_concept = isset($_POST['checklist_concept']) ? 1 : 0;
    $checklist_compatibility = isset($_POST['checklist_compatibility']) ? 1 : 0;
    
    // Handle file uploads
    $file_exhibit = null;
    $file_concept = null;
    $file_compatibility = null;
    
    if (isset($_FILES['file_exhibit']) && $_FILES['file_exhibit']['error'] === UPLOAD_ERR_OK) {
        $file_exhibit = file_get_contents($_FILES['file_exhibit']['tmp_name']);
    }
    if (isset($_FILES['file_concept']) && $_FILES['file_concept']['error'] === UPLOAD_ERR_OK) {
        $file_concept = file_get_contents($_FILES['file_concept']['tmp_name']);
    }
    if (isset($_FILES['file_compatibility']) && $_FILES['file_compatibility']['error'] === UPLOAD_ERR_OK) {
        $file_compatibility = file_get_contents($_FILES['file_compatibility']['tmp_name']);
    }
    
    // Signature fields
    $signature_date_1 = isset($_POST['signature_date_1']) && $_POST['signature_date_1'] !== '' ? $_POST['signature_date_1'] : null;
    $signature_name_1 = isset($_POST['signature_name_1']) && $_POST['signature_name_1'] !== '' ? $_POST['signature_name_1'] : null;
    $signature_date_2 = isset($_POST['signature_date_2']) && $_POST['signature_date_2'] !== '' ? $_POST['signature_date_2'] : null;
    $signature_name_2 = isset($_POST['signature_name_2']) && $_POST['signature_name_2'] !== '' ? $_POST['signature_name_2'] : null;
    
    // Admin fields
    $application_fee = isset($_POST['application_fee']) && $_POST['application_fee'] !== '' ? $_POST['application_fee'] : null;
    $certificate_fee = isset($_POST['certificate_fee']) && $_POST['certificate_fee'] !== '' ? $_POST['certificate_fee'] : null;
    $date_fees_received = isset($_POST['date_fees_received']) && $_POST['date_fees_received'] !== '' ? $_POST['date_fees_received'] : null;
    $form_paid_bool = isset($_POST['form_paid_bool']) ? 1 : 0;
    $correction_form_id = isset($_POST['correction_form_id']) && $_POST['correction_form_id'] !== '' ? $_POST['correction_form_id'] : null;
    
    // Parse address components for applicant
$applicant_street = isset($_POST['applicant_street']) ? $_POST['applicant_street'] : null;
$applicant_city = isset($_POST['applicant_city']) ? $_POST['applicant_city'] : null;
$applicant_state = isset($_POST['applicant_state']) ? $_POST['applicant_state'] : null;
$applicant_zip_code = isset($_POST['applicant_zip_code']) ? $_POST['applicant_zip_code'] : null;

// Parse address components for additional applicants
$additional_applicant_streets = isset($_POST['additional_applicant_streets']) && is_array($_POST['additional_applicant_streets']) ? json_encode($_POST['additional_applicant_streets']) : null;
$additional_applicant_cities = isset($_POST['additional_applicant_cities']) && is_array($_POST['additional_applicant_cities']) ? json_encode($_POST['additional_applicant_cities']) : null;
$additional_applicant_states = isset($_POST['additional_applicant_states']) && is_array($_POST['additional_applicant_states']) ? json_encode($_POST['additional_applicant_states']) : null;
$additional_applicant_zip_codes = isset($_POST['additional_applicant_zip_codes']) && is_array($_POST['additional_applicant_zip_codes']) ? json_encode($_POST['additional_applicant_zip_codes']) : null;

// Parse address components for owners
$owner_street = isset($_POST['owner_street']) ? $_POST['owner_street'] : null;
$owner_city = isset($_POST['owner_city']) ? $_POST['owner_city'] : null;
$owner_state = isset($_POST['owner_state']) ? $_POST['owner_state'] : null;
$owner_zip_code = isset($_POST['owner_zip_code']) ? $_POST['owner_zip_code'] : null;

// Parse address components for additional owners
$additional_owner_streets = isset($_POST['additional_owner_streets']) && is_array($_POST['additional_owner_streets']) ? json_encode($_POST['additional_owner_streets']) : null;
$additional_owner_cities = isset($_POST['additional_owner_cities']) && is_array($_POST['additional_owner_cities']) ? json_encode($_POST['additional_owner_cities']) : null;
$additional_owner_states = isset($_POST['additional_owner_states']) && is_array($_POST['additional_owner_states']) ? json_encode($_POST['additional_owner_states']) : null;
$additional_owner_zip_codes = isset($_POST['additional_owner_zip_codes']) && is_array($_POST['additional_owner_zip_codes']) ? json_encode($_POST['additional_owner_zip_codes']) : null;

// Parse property address components
$property_street = isset($_POST['property_street']) ? $_POST['property_street'] : null;
$property_city = isset($_POST['property_city']) ? $_POST['property_city'] : null;
$property_state = isset($_POST['property_state']) ? $_POST['property_state'] : null;
$property_zip_code = isset($_POST['property_zip_code']) ? $_POST['property_zip_code'] : null;

// Parse owner name
$owner_first_name = null;
$owner_last_name = null;
if (isset($_POST['applicant_first_name']) && isset($_POST['applicant_last_name'])) {
    $owner_first_name = $_POST['applicant_first_name'];
    $owner_last_name = $_POST['applicant_last_name'];
}

// Parse attorney name
$attorney_first_name = isset($_POST['attorney_first_name']) ? $_POST['attorney_first_name'] : null;
$attorney_last_name = isset($_POST['attorney_last_name']) ? $_POST['attorney_last_name'] : null;

// Call the stored procedure
$sql = "CALL sp_insert_future_land_use_map_application_comprehensive(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = 'Prepare failed: ' . $conn->error;
} else {
    // Bind parameters (60 parameters total)
    $types = 'siisssssssssssssssssssssssssssssssssssisssssssssssssssssss';
    
    $params = array();
    $params[] = &$types;
    
    // Form metadata (3)
    $form_datetime_resolved = null;
    $params[] = &$form_datetime_resolved;
    $params[] = &$form_paid_bool;
    $params[] = &$correction_form_id;
    
    // Hearing information (4)
    $params[] = &$docket_number;
    $params[] = &$public_hearing_date;
    $params[] = &$date_application_filed;
    $params[] = &$pre_application_meeting_date;
    
    // Primary applicant (9)
    $params[] = &$applicant_name;
    $params[] = &$officers_names;
    $params[] = &$applicant_street;
    $params[] = &$applicant_phone;
    $params[] = &$applicant_cell;
    $params[] = &$applicant_city;
    $params[] = &$applicant_state;
    $params[] = &$applicant_zip_code;
    $applicant_other_address = isset($_POST['applicant_other_address']) ? $_POST['applicant_other_address'] : null;
    $params[] = &$applicant_other_address;
    $params[] = &$applicant_email;
    
    // Additional applicants (10)
    $params[] = &$additional_applicant_names;
    $params[] = &$additional_applicant_officers;
    $params[] = &$additional_applicant_streets;
    $params[] = &$additional_applicant_phones;
    $params[] = &$additional_applicant_cells;
    $params[] = &$additional_applicant_cities;
    $params[] = &$additional_applicant_states;
    $params[] = &$additional_applicant_zip_codes;
    $additional_applicant_other_addresses = isset($_POST['additional_applicant_other_addresses']) && is_array($_POST['additional_applicant_other_addresses']) ? json_encode($_POST['additional_applicant_other_addresses']) : null;
    $params[] = &$additional_applicant_other_addresses;
    $params[] = &$additional_applicant_emails;
    
    // Property owner (9)
    $params[] = &$owner_first_name;
    $params[] = &$owner_last_name;
    $params[] = &$owner_street;
    $params[] = &$owner_phone;
    $params[] = &$owner_cell;
    $params[] = &$owner_city;
    $params[] = &$owner_state;
    $params[] = &$owner_zip_code;
    $owner_other_address = isset($_POST['owner_other_address']) ? $_POST['owner_other_address'] : null;
    $params[] = &$owner_other_address;
    $params[] = &$owner_email;
    
    // Additional owners (9)
    $params[] = &$additional_owner_names;
    $params[] = &$additional_owner_streets;
    $params[] = &$additional_owner_phones;
    $params[] = &$additional_owner_cells;
    $params[] = &$additional_owner_cities;
    $params[] = &$additional_owner_states;
    $params[] = &$additional_owner_zip_codes;
    $additional_owner_other_addresses = isset($_POST['additional_owner_other_addresses']) && is_array($_POST['additional_owner_other_addresses']) ? json_encode($_POST['additional_owner_other_addresses']) : null;
    $params[] = &$additional_owner_other_addresses;
    $params[] = &$additional_owner_emails;
    
    // Attorney (6)
    $params[] = &$attorney_first_name;
    $params[] = &$attorney_last_name;
    $params[] = &$law_firm;
    $params[] = &$attorney_phone;
    $params[] = &$attorney_cell;
    $params[] = &$attorney_email;
    
    // Property information (8)
    $params[] = &$property_street;
    $params[] = &$property_city;
    $params[] = &$property_state;
    $params[] = &$property_zip_code;
    $property_other_address = isset($_POST['property_other_address']) ? $_POST['property_other_address'] : null;
    $params[] = &$property_other_address;
    $params[] = &$parcel_number;
    $params[] = &$acreage;
    $params[] = &$current_zoning;
    
    // FLUM request (1)
    $params[] = &$flum_request;
    
    // Findings (2)
    $params[] = &$finding_type;
    $params[] = &$findings_explanation;
    
    // Checklist items (4)
    $params[] = &$checklist_application;
    $params[] = &$checklist_exhibit;
    $params[] = &$checklist_concept;
    $params[] = &$checklist_compatibility;
    
    // Files (3) - would need to save to disk and store filenames
    $file_exhibit_name = null;
    $file_concept_name = null;
    $file_compatibility_name = null;
    
    if (isset($_FILES['file_exhibit']) && $_FILES['file_exhibit']['error'] === UPLOAD_ERR_OK) {
        $file_exhibit_name = 'uploads/exhibit_' . time() . '_' . basename($_FILES['file_exhibit']['name']);
        move_uploaded_file($_FILES['file_exhibit']['tmp_name'], $file_exhibit_name);
    }
    if (isset($_FILES['file_concept']) && $_FILES['file_concept']['error'] === UPLOAD_ERR_OK) {
        $file_concept_name = 'uploads/concept_' . time() . '_' . basename($_FILES['file_concept']['name']);
        move_uploaded_file($_FILES['file_concept']['tmp_name'], $file_concept_name);
    }
    if (isset($_FILES['file_compatibility']) && $_FILES['file_compatibility']['error'] === UPLOAD_ERR_OK) {
        $file_compatibility_name = 'uploads/compatibility_' . time() . '_' . basename($_FILES['file_compatibility']['name']);
        move_uploaded_file($_FILES['file_compatibility']['tmp_name'], $file_compatibility_name);
    }
    
    $params[] = &$file_exhibit_name;
    $params[] = &$file_concept_name;
    $params[] = &$file_compatibility_name;
    
    // Signatures (4)
    $params[] = &$signature_date_1;
    $params[] = &$signature_name_1;
    $params[] = &$signature_date_2;
    $params[] = &$signature_name_2;
    
    // Admin/fees (2)
    $params[] = &$application_fee;
    $params[] = &$certificate_fee;
    
    // Bind all parameters
    $bindResult = @call_user_func_array(array($stmt, 'bind_param'), $params);
    
    if ($bindResult === false) {
        $error = 'Bind failed: ' . $stmt->error;
    } else {
        if (!$stmt->execute()) {
            $error = 'Execute failed: ' . $stmt->error;
        } else {
            $success = 'Form submitted successfully!';
        }
    }
    $stmt->close();
}
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Future Land Use Map Amendment Application</title>
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
    .findings-box {
      border: 1px solid #ddd;
      padding: 15px;
      background: #fafafa;
      margin: 15px 0;
    }
    .findings-box label {
      font-weight: normal;
      font-size: 13px;
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
              <input type="text" class="form-control" name="additional_applicant_states[]">
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
              <input type="text" class="form-control" name="additional_owner_states[]">
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
    <h2>Application for Zoning Map Amendment</h2>
  </div>
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>

  <div class="header-info">
    <div>
      <strong>Docket Number:</strong> <input type="text" name="p_docket_number" class="form-control small-input d-inline" style="width: 150px;">
    </div>
    <div>
      <strong>Public Hearing Date:</strong> <input type="text" name="p_public_hearing_date" class="form-control small-input d-inline" style="width: 150px;">
    </div>
  </div>
  <div class="header-info">
    <div>
      <strong>Date Application Filed:</strong> <input type="text" name="p_date_application_filed" class="form-control small-input d-inline" style="width: 150px;">
    </div>
    <div>
      <strong>Pre-Application Meeting Date:</strong> <input type="text" name="p_application_meeting_date" class="form-control small-input d-inline" style="width: 150px;">
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
          <input type="text" class="form-control" name="applicant_state">
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
          <input type="text" class="form-control" name="owner_state">
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

    <p class="info-text">*PLEASE ADD ADDITIONAL APPLICANTS AND PROPERTY OWNERS IF NEEDED*</p>

    <label><b>3) APPLICANT(S) ATTORNEY:</b></label>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>ATTORNEY FIRST NAME:</label>
          <input type="text" class="form-control" name="attorney_first_name">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>ATTORNEY LAST NAME:</label>
          <input type="text" class="form-control" name="attorney_last_name">
        </div>
      </div>
    </div>
    <div class="form-group">
      <label>Name of Law Firm:</label>
      <input type="text" class="form-control" name="law_firm">
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="attorney_phone">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="attorney_cell">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>E-Mail Address:</label>
      <input type="email" class="form-control" name="attorney_email">
    </div>

    <!-- PROPERTY INFORMATION -->
    <div class="section-title">PROPERTY INFORMATION</div>

    <div class="form-group">
      <label>Street:</label>
      <input type="text" class="form-control" name="property_street">
    </div>

    <div class="row">
      <div class="col-md-3">
        <div class="form-group">
          <label>City:</label>
          <input type="text" class="form-control" name="property_city">
        </div>
      </div>
      <div class="col-md-1">
        <div class="form-group">
          <label>State:</label>
          <input type="text" class="form-control" name="property_state">
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Zip Code:</label>
          <input type="text" class="form-control" name="property_zip_code">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Other Information:</label>
          <input type="text" class="form-control" name="property_other_address">
        </div>
      </div>
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
          <label>Acreage:</label>
          <input type="text" class="form-control" name="acreage">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Current Zoning:</label>
          <input type="text" class="form-control" name="current_zoning">
        </div>
      </div>
    </div>

    <!-- FLUM AMENDMENT REQUEST -->
    <div class="section-title">FUTURE LAND USE MAP (FLUM) AMENDMENT REQUEST</div>

    <div class="form-group">
      <label>Please describe, in detail, the desired FLUM designation request of the property being considered:</label>
      <textarea class="form-control" name="flum_request" rows="4"></textarea>
    </div>

    <!-- FINDINGS REQUIRED -->
    <div class="section-title">FINDINGS REQUIRED FOR FUTURE LAND USE MAP AMENDMENT</div>

    <p style="font-size: 13px;">In order for the Planning Commission to approve the request for a Future Land Use Map (FLUM) amendment, it must make findings of fact in support of its recommendation. Please provide a detailed explanation as to how the proposed FLUM amendment satisfies at least one of the following criteria:</p>

    <div class="findings-box">
      <div class="form-check mb-3">
        <input class="form-check-input" type="radio" name="finding_type" id="finding1" value="public_benefit">
        <label class="form-check-label" for="finding1">
          A demonstrated over-riding public benefit of the proposed development (this may include the provision of a major public facility or amenity, the provision of a major source of employment or an economic development asset that cannot be accommodated in a location consistent with the current FLUM);
        </label>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="radio" name="finding_type" id="finding2" value="correction">
        <label class="form-check-label" for="finding2">
          The request is a correction of inconsistencies or mapping errors contained within the FLUM; or
        </label>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="radio" name="finding_type" id="finding3" value="compatibility">
        <label class="form-check-label" for="finding3">
          That the proposed use is clearly compatible with existing surrounding development as demonstrated by the applicant. This review should include a compatibility assessment of the proposed use, which includes, but is not limited to, location and bulk of buildings and other structures, building height, building materials, intensity of use, density of development, location of parking and signage within the surrounding area. In addition, the applicant must prove that the proposed amendment will not result in development that exceeds the capacity of existing infrastructure (such as roads, water, sewer and stormwater).
        </label>
      </div>
    </div>

    <div class="form-group">
      <label>Please check (✓) one of the above findings of fact and cite specific evidence to address such finding in the space provided below. Please attach additional sheets if more space is needed.</label>
      <textarea class="form-control" name="findings_explanation" rows="8"></textarea>
    </div>

    <!-- APPLICATION CHECKLIST -->
    <div class="section-title">APPLICATION CHECKLIST</div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_exhibit" id="check2">
        <label class="form-check-label" for="check2">
          An exhibit prepared by a licensed surveyor depicting the various portion(s) of the property to be included in the proposed future land use map amendment (Please include: two (2) - 18" x 24" copies and two (2) - 11" x 17" copies)
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_exhibit" class="font-weight-normal">Upload Exhibit:</label>
        <input type="file" class="form-control-file" name="file_exhibit" id="file_exhibit">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_concept" id="check3">
        <label class="form-check-label" for="check3">
          Concept Plan, or Preliminary Site Plan, if required (Please include: two (2) - 11" x 17" copies)
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_concept" class="font-weight-normal">Upload Concept/Preliminary Site Plan:</label>
        <input type="file" class="form-control-file" name="file_concept" id="file_concept">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_compatibility" id="check4">
        <label class="form-check-label" for="check4">
          Compatibility Assessment
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_compatibility" class="font-weight-normal">Upload Compatibility Assessment:</label>
        <input type="file" class="form-control-file" name="file_compatibility" id="file_compatibility">
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
          <input type="text" class="form-control" name="signature_date_1">
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
          <input type="text" class="form-control" name="signature_date_2">
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
</html>check">
        <input class="form-check-input" type="checkbox" name="checklist_application" id="check1">
        <label class="form-check-label" for="check1">
          A completed and signed Application
        </label>
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-