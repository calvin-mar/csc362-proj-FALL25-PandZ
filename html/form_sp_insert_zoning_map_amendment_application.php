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
    $p_docket_number = isset($_POST['p_docket_number']) && $_POST['p_docket_number'] !== '' ? $_POST['p_docket_number'] : null;
    $p_public_hearing_date = isset($_POST['p_public_hearing_date']) && $_POST['p_public_hearing_date'] !== '' ? $_POST['p_public_hearing_date'] : null;
    $p_date_application_filed = isset($_POST['p_date_application_filed']) && $_POST['p_date_application_filed'] !== '' ? $_POST['p_date_application_filed'] : null;
    $p_application_meeting_date = isset($_POST['p_application_meeting_date']) && $_POST['p_application_meeting_date'] !== '' ? $_POST['p_application_meeting_date'] : null;
    
    // Primary applicant fields
    $applicant_name = isset($_POST['applicant_name']) && $_POST['applicant_name'] !== '' ? $_POST['applicant_name'] : null;
    $officers_names = isset($_POST['officers_names']) && is_array($_POST['officers_names']) ? json_encode($_POST['officers_names']) : null;
    $applicant_street = isset($_POST['applicant_street']) && $_POST['applicant_street'] !== '' ? $_POST['applicant_street'] : null;
    $applicant_phone = isset($_POST['applicant_phone']) && $_POST['applicant_phone'] !== '' ? $_POST['applicant_phone'] : null;
    $applicant_cell = isset($_POST['applicant_cell']) && $_POST['applicant_cell'] !== '' ? $_POST['applicant_cell'] : null;
    $applicant_city = isset($_POST['applicant_city']) && $_POST['applicant_city'] !== '' ? $_POST['applicant_city'] : null;
    $applicant_state = isset($_POST['applicant_state']) && $_POST['applicant_state'] !== '' ? $_POST['applicant_state'] : null;
    $applicant_zip_code = isset($_POST['applicant_zip_code']) && $_POST['applicant_zip_code'] !== '' ? $_POST['applicant_zip_code'] : null;
    $applicant_other_address = isset($_POST['applicant_other_address']) && $_POST['applicant_other_address'] !== '' ? $_POST['applicant_other_address'] : null;
    $applicant_email = isset($_POST['applicant_email']) && $_POST['applicant_email'] !== '' ? $_POST['applicant_email'] : null;
    
    // Additional applicants
    $additional_applicant_names = isset($_POST['additional_applicant_names']) && is_array($_POST['additional_applicant_names']) ? json_encode($_POST['additional_applicant_names']) : null;
    
    // Handle additional applicant officers dynamically
    $additional_applicant_officers = [];
    foreach ($_POST as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value)) {
                $additional_applicant_officers[$matches[1]] = $value;
            }
        }
    }
    $additional_applicant_officers = !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null;
    
    $additional_applicant_streets = isset($_POST['additional_applicant_streets']) && is_array($_POST['additional_applicant_streets']) ? json_encode($_POST['additional_applicant_streets']) : null;
    $additional_applicant_phones = isset($_POST['additional_applicant_phones']) && is_array($_POST['additional_applicant_phones']) ? json_encode($_POST['additional_applicant_phones']) : null;
    $additional_applicant_cells = isset($_POST['additional_applicant_cells']) && is_array($_POST['additional_applicant_cells']) ? json_encode($_POST['additional_applicant_cells']) : null;
    $additional_applicant_cities = isset($_POST['additional_applicant_cities']) && is_array($_POST['additional_applicant_cities']) ? json_encode($_POST['additional_applicant_cities']) : null;
    $additional_applicant_states = isset($_POST['additional_applicant_states']) && is_array($_POST['additional_applicant_states']) ? json_encode($_POST['additional_applicant_states']) : null;
    $additional_applicant_zip_codes = isset($_POST['additional_applicant_zip_codes']) && is_array($_POST['additional_applicant_zip_codes']) ? json_encode($_POST['additional_applicant_zip_codes']) : null;
    $additional_applicant_other_addresses = isset($_POST['additional_applicant_other_addresses']) && is_array($_POST['additional_applicant_other_addresses']) ? json_encode($_POST['additional_applicant_other_addresses']) : null;
    $additional_applicant_emails = isset($_POST['additional_applicant_emails']) && is_array($_POST['additional_applicant_emails']) ? json_encode($_POST['additional_applicant_emails']) : null;
    
    // Property owner fields
    $applicant_first_name = isset($_POST['applicant_first_name']) && $_POST['applicant_first_name'] !== '' ? $_POST['applicant_first_name'] : null;
    $applicant_last_name = isset($_POST['applicant_last_name']) && $_POST['applicant_last_name'] !== '' ? $_POST['applicant_last_name'] : null;
    $owner_street = isset($_POST['owner_street']) && $_POST['owner_street'] !== '' ? $_POST['owner_street'] : null;
    $owner_phone = isset($_POST['owner_phone']) && $_POST['owner_phone'] !== '' ? $_POST['owner_phone'] : null;
    $owner_cell = isset($_POST['owner_cell']) && $_POST['owner_cell'] !== '' ? $_POST['owner_cell'] : null;
    $owner_city = isset($_POST['owner_city']) && $_POST['owner_city'] !== '' ? $_POST['owner_city'] : null;
    $owner_state = isset($_POST['owner_state']) && $_POST['owner_state'] !== '' ? $_POST['owner_state'] : null;
    $owner_zip_code = isset($_POST['owner_zip_code']) && $_POST['owner_zip_code'] !== '' ? $_POST['owner_zip_code'] : null;
    $owner_other_address = isset($_POST['owner_other_address']) && $_POST['owner_other_address'] !== '' ? $_POST['owner_other_address'] : null;
    $owner_email = isset($_POST['owner_email']) && $_POST['owner_email'] !== '' ? $_POST['owner_email'] : null;
    
    // Additional property owners
    $additional_owner_names = isset($_POST['additional_owner_names']) && is_array($_POST['additional_owner_names']) ? json_encode($_POST['additional_owner_names']) : null;
    $additional_owner_streets = isset($_POST['additional_owner_streets']) && is_array($_POST['additional_owner_streets']) ? json_encode($_POST['additional_owner_streets']) : null;
    $additional_owner_phones = isset($_POST['additional_owner_phones']) && is_array($_POST['additional_owner_phones']) ? json_encode($_POST['additional_owner_phones']) : null;
    $additional_owner_cells = isset($_POST['additional_owner_cells']) && is_array($_POST['additional_owner_cells']) ? json_encode($_POST['additional_owner_cells']) : null;
    $additional_owner_cities = isset($_POST['additional_owner_cities']) && is_array($_POST['additional_owner_cities']) ? json_encode($_POST['additional_owner_cities']) : null;
    $additional_owner_states = isset($_POST['additional_owner_states']) && is_array($_POST['additional_owner_states']) ? json_encode($_POST['additional_owner_states']) : null;
    $additional_owner_zip_codes = isset($_POST['additional_owner_zip_codes']) && is_array($_POST['additional_owner_zip_codes']) ? json_encode($_POST['additional_owner_zip_codes']) : null;
    $additional_owner_other_addresses = isset($_POST['additional_owner_other_addresses']) && is_array($_POST['additional_owner_other_addresses']) ? json_encode($_POST['additional_owner_other_addresses']) : null;
    $additional_owner_emails = isset($_POST['additional_owner_emails']) && is_array($_POST['additional_owner_emails']) ? json_encode($_POST['additional_owner_emails']) : null;
    
    // Attorney fields
    $attorney_first_name = isset($_POST['attorney_first_name']) && $_POST['attorney_first_name'] !== '' ? $_POST['attorney_first_name'] : null;
    $attorney_last_name = isset($_POST['attorney_last_name']) && $_POST['attorney_last_name'] !== '' ? $_POST['attorney_last_name'] : null;
    $law_firm = isset($_POST['law_firm']) && $_POST['law_firm'] !== '' ? $_POST['law_firm'] : null;
    $attorney_phone = isset($_POST['attorney_phone']) && $_POST['attorney_phone'] !== '' ? $_POST['attorney_phone'] : null;
    $attorney_cell = isset($_POST['attorney_cell']) && $_POST['attorney_cell'] !== '' ? $_POST['attorney_cell'] : null;
    $attorney_email = isset($_POST['attorney_email']) && $_POST['attorney_email'] !== '' ? $_POST['attorney_email'] : null;
    
    // Property information fields
    $property_street = isset($_POST['property_street']) && $_POST['property_street'] !== '' ? $_POST['property_street'] : null;
    $property_city = isset($_POST['property_city']) && $_POST['property_city'] !== '' ? $_POST['property_city'] : null;
    $property_state = isset($_POST['property_state']) && $_POST['property_state'] !== '' ? $_POST['property_state'] : null;
    $property_zip_code = isset($_POST['property_zip_code']) && $_POST['property_zip_code'] !== '' ? $_POST['property_zip_code'] : null;
    $property_other_address = isset($_POST['property_other_address']) && $_POST['property_other_address'] !== '' ? $_POST['property_other_address'] : null;
    $parcel_number = isset($_POST['parcel_number']) && $_POST['parcel_number'] !== '' ? $_POST['parcel_number'] : null;
    $acreage = isset($_POST['acreage']) && $_POST['acreage'] !== '' ? $_POST['acreage'] : null;
    $current_zoning = isset($_POST['current_zoning']) && $_POST['current_zoning'] !== '' ? $_POST['current_zoning'] : null;
    
    // Zoning amendment request
    $p_zoning_map_amendment_request = isset($_POST['p_zoning_map_amendment_request']) && $_POST['p_zoning_map_amendment_request'] !== '' ? $_POST['p_zoning_map_amendment_request'] : null;
    
    // Zone change conditions
    $zone_change_conditions = isset($_POST['zone_change_conditions']) && $_POST['zone_change_conditions'] !== '' ? $_POST['zone_change_conditions'] : null;
    
    // Findings
    $finding_type = isset($_POST['finding_type']) && $_POST['finding_type'] !== '' ? $_POST['finding_type'] : null;
    $findings_explanation = isset($_POST['findings_explanation']) && $_POST['findings_explanation'] !== '' ? $_POST['findings_explanation'] : null;
    
    // Checklist items
    $checklist_application = isset($_POST['checklist_application']) ? 1 : 0;
    $checklist_exhibit = isset($_POST['checklist_exhibit']) ? 1 : 0;
    $checklist_adjacent = isset($_POST['checklist_adjacent']) ? 1 : 0;
    $checklist_verification = isset($_POST['checklist_verification']) ? 1 : 0;
    $checklist_fees = isset($_POST['checklist_fees']) ? 1 : 0;
    $checklist_conditions = isset($_POST['checklist_conditions']) ? 1 : 0;
    $checklist_concept = isset($_POST['checklist_concept']) ? 1 : 0;
    $checklist_traffic = isset($_POST['checklist_traffic']) ? 1 : 0;
    $checklist_geologic = isset($_POST['checklist_geologic']) ? 1 : 0;
    
    // Handle file uploads and convert to blobs
    $file_exhibit = null;
    $file_adjacent = null;
    $file_verification = null;
    $file_conditions = null;
    $file_concept = null;
    $file_traffic = null;
    $file_geologic = null;
    
    if (isset($_FILES['file_exhibit']) && $_FILES['file_exhibit']['error'] === UPLOAD_ERR_OK) {
        $file_exhibit = file_get_contents($_FILES['file_exhibit']['tmp_name']);
    }
    if (isset($_FILES['file_adjacent']) && $_FILES['file_adjacent']['error'] === UPLOAD_ERR_OK) {
        $file_adjacent = file_get_contents($_FILES['file_adjacent']['tmp_name']);
    }
    if (isset($_FILES['file_verification']) && $_FILES['file_verification']['error'] === UPLOAD_ERR_OK) {
        $file_verification = file_get_contents($_FILES['file_verification']['tmp_name']);
    }
    if (isset($_FILES['file_conditions']) && $_FILES['file_conditions']['error'] === UPLOAD_ERR_OK) {
        $file_conditions = file_get_contents($_FILES['file_conditions']['tmp_name']);
    }
    if (isset($_FILES['file_concept']) && $_FILES['file_concept']['error'] === UPLOAD_ERR_OK) {
        $file_concept = file_get_contents($_FILES['file_concept']['tmp_name']);
    }
    if (isset($_FILES['file_traffic']) && $_FILES['file_traffic']['error'] === UPLOAD_ERR_OK) {
        $file_traffic = file_get_contents($_FILES['file_traffic']['tmp_name']);
    }
    if (isset($_FILES['file_geologic']) && $_FILES['file_geologic']['error'] === UPLOAD_ERR_OK) {
        $file_geologic = file_get_contents($_FILES['file_geologic']['tmp_name']);
    }
    
    // Signature fields
    $signature_date_1 = isset($_POST['signature_date_1']) && $_POST['signature_date_1'] !== '' ? $_POST['signature_date_1'] : null;
    $signature_name_1 = isset($_POST['signature_name_1']) && $_POST['signature_name_1'] !== '' ? $_POST['signature_name_1'] : null;
    $signature_date_2 = isset($_POST['signature_date_2']) && $_POST['signature_date_2'] !== '' ? $_POST['signature_date_2'] : null;
    $signature_name_2 = isset($_POST['signature_name_2']) && $_POST['signature_name_2'] !== '' ? $_POST['signature_name_2'] : null;
    
    // Admin fields
    $application_fee = isset($_POST['application_fee']) && $_POST['application_fee'] !== '' ? $_POST['application_fee'] : null;
    $certificate_fee = isset($_POST['certificate_fee']) && $_POST['certificate_fee'] !== '' ? $_POST['certificate_fee'] : null;
    $p_form_datetime_resolved = isset($_POST['p_form_datetime_resolved']) && $_POST['p_form_datetime_resolved'] !== '' ? $_POST['p_form_datetime_resolved'] : null;
    $p_form_paid_bool = isset($_POST['p_form_paid_bool']) ? 1 : 0;
    $p_correction_form_id = isset($_POST['p_correction_form_id']) && $_POST['p_correction_form_id'] !== '' ? $_POST['p_correction_form_id'] : null;
    
    $sql = "CALL sp_insert_zoning_map_amendment_application(?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siis';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_zoning_map_amendment_request;
        array_unshift($bind_names, $types);
        $bindResult = @call_user_func_array(array($stmt, 'bind_param'), $bind_names);
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
  <title>Zoning Map Amendment Application</title>
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
    .signature-section {
      margin-top: 30px;
      padding-top: 20px;
      border-top: 2px solid #333;
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

    <!-- ZONING MAP AMENDMENT REQUEST -->
    <div class="section-title">ZONING MAP AMENDMENT REQUEST</div>

    <div class="form-group">
      <label>Please describe, in detail, the proposed use and desired zoning district request of the property being considered:</label>
      <textarea class="form-control" name="p_zoning_map_amendment_request" rows="4"></textarea>
    </div>

    <!-- PROPOSED ZONE CHANGE CONDITIONS -->
    <div class="section-title">PROPOSED ZONE CHANGE CONDITIONS</div>

    <div class="form-group">
      <label>Please provide a list of all proposed conditions for the subject property:</label>
      <textarea class="form-control" name="zone_change_conditions" rows="4"></textarea>
    </div>

    <!-- FINDINGS REQUIRED -->
    <div class="section-title">FINDINGS REQUIRED FOR ZONING MAP AMENDMENT</div>

    <p style="font-size: 13px;">In order for the Planning Commission to make a recommendation for a zoning map amendment, it must make findings of fact in support of its recommendation. Please provide a detailed explanation as to:</p>

    <div class="findings-box">
      <div class="form-check mb-3">
        <input class="form-check-input" type="radio" name="finding_type" id="finding1" value="comprehensive_plan">
        <label class="form-check-label" for="finding1">
          How the proposed zoning map amendment is in agreement with the 2017 Comprehensive Plan, including compliance with the adopted Future Land Use Map;
        </label>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="radio" name="finding_type" id="finding2" value="inappropriate_zoning">
        <label class="form-check-label" for="finding2">
          Why the original zoning classification of the property in question is inappropriate or improper; or
        </label>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="radio" name="finding_type" id="finding3" value="major_changes">
        <label class="form-check-label" for="finding3">
          What major economic, physical or social changes, if any, have occurred in the vicinity of the property in question that were not anticipated by the Comprehensive Plan and which have substantially altered the basic character of the area, which make the proposed amendment to the Official Zoning Map appropriate. This explanation shall contain a list of such specific changes, a description as to how said changes were not anticipated by the comprehensive plan, a description as to how said changes have altered the basic character of the area and a description as to how said changes make the proposed amendment to the official zoning map appropriate.
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
        <input class="form-check-input" type="checkbox" name="checklist_application" id="check1">
        <label class="form-check-label" for="check1">
          A completed and signed Application
        </label>
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_exhibit" id="check2">
        <label class="form-check-label" for="check2">
          An exhibit prepared by a licensed surveyor depicting the various portion(s) of the property to be included in the proposed zoning map amendment (Please include: two (2) - 18" x 24" copies and two (2) - 11" x 17" copies)
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_exhibit" class="font-weight-normal">Upload Exhibit:</label>
        <input type="file" class="form-control-file" name="file_exhibit" id="file_exhibit">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_adjacent" id="check3">
        <label class="form-check-label" for="check3">
          Adjacent Property Owners Form
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_adjacent" class="font-weight-normal">Upload Adjacent Property Owners Form:</label>
        <input type="file" class="form-control-file" name="file_adjacent" id="file_adjacent">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_verification" id="check4">
        <label class="form-check-label" for="check4">
          Water/Sewer/Floodplain Verification Letter(s)
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_verification" class="font-weight-normal">Upload Verification Letter(s):</label>
        <input type="file" class="form-control-file" name="file_verification" id="file_verification">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_fees" id="check5">
        <label class="form-check-label" for="check5">
          Filing and Recording Fees
        </label>
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_conditions" id="check6">
        <label class="form-check-label" for="check6">
          Proposed Zone Change Conditions, signed and notarized
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_conditions" class="font-weight-normal">Upload Signed and Notarized Conditions:</label>
        <input type="file" class="form-control-file" name="file_conditions" id="file_conditions">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_concept" id="check7">
        <label class="form-check-label" for="check7">
          Concept Plan, or Preliminary Site Plan (Please include: two (2) - 11" x 17" copies)
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_concept" class="font-weight-normal">Upload Concept/Preliminary Site Plan:</label>
        <input type="file" class="form-control-file" name="file_concept" id="file_concept">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_traffic" id="check8">
        <label class="form-check-label" for="check8">
          Traffic Impact Study, if required
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_traffic" class="font-weight-normal">Upload Traffic Impact Study:</label>
        <input type="file" class="form-control-file" name="file_traffic" id="file_traffic">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_geologic" id="check9">
        <label class="form-check-label" for="check9">
          Geologic Analysis (Phase I), if required
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_geologic" class="font-weight-normal">Upload Geologic Analysis:</label>
        <input type="file" class="form-control-file" name="file_geologic" id="file_geologic">
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

    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
          <label>Application Fee:</label>
          <input type="text" class="form-control" name="application_fee">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Land Use Certificate Fee:</label>
          <input type="text" class="form-control" name="certificate_fee">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Date Fees Received:</label>
          <input type="text" class="form-control" name="p_form_datetime_resolved">
        </div>
      </div>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="p_form_paid_bool" value="1" id="paid">
      <label class="form-check-label" for="paid">
        <strong>Form Paid</strong>
      </label>
    </div>

    <div class="form-group">
      <label>Correction Form ID (if applicable):</label>
      <input type="number" class="form-control" name="p_correction_form_id">
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
    zoning@danvilleky.gov<br>
    www.boyleplanning.org
  </div>
</div>

</body>
</html>