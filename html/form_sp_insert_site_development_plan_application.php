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
    
    // Surveyor fields
    $surveyor_name = isset($_POST['surveyor_name']) && $_POST['surveyor_name'] !== '' ? $_POST['surveyor_name'] : null;
    $surveyor_firm = isset($_POST['surveyor_firm']) && $_POST['surveyor_firm'] !== '' ? $_POST['surveyor_firm'] : null;
    $surveyor_phone = isset($_POST['surveyor_phone']) && $_POST['surveyor_phone'] !== '' ? $_POST['surveyor_phone'] : null;
    $surveyor_cell = isset($_POST['surveyor_cell']) && $_POST['surveyor_cell'] !== '' ? $_POST['surveyor_cell'] : null;
    $surveyor_email = isset($_POST['surveyor_email']) && $_POST['surveyor_email'] !== '' ? $_POST['surveyor_email'] : null;
    
    // Engineer fields
    $engineer_name = isset($_POST['engineer_name']) && $_POST['engineer_name'] !== '' ? $_POST['engineer_name'] : null;
    $engineer_firm = isset($_POST['engineer_firm']) && $_POST['engineer_firm'] !== '' ? $_POST['engineer_firm'] : null;
    $engineer_phone = isset($_POST['engineer_phone']) && $_POST['engineer_phone'] !== '' ? $_POST['engineer_phone'] : null;
    $engineer_cell = isset($_POST['engineer_cell']) && $_POST['engineer_cell'] !== '' ? $_POST['engineer_cell'] : null;
    $engineer_email = isset($_POST['engineer_email']) && $_POST['engineer_email'] !== '' ? $_POST['engineer_email'] : null;
    
    // Architect fields
    $architect_name = isset($_POST['architect_name']) && $_POST['architect_name'] !== '' ? $_POST['architect_name'] : null;
    $architect_firm = isset($_POST['architect_firm']) && $_POST['architect_firm'] !== '' ? $_POST['architect_firm'] : null;
    $architect_phone = isset($_POST['architect_phone']) && $_POST['architect_phone'] !== '' ? $_POST['architect_phone'] : null;
    $architect_cell = isset($_POST['architect_cell']) && $_POST['architect_cell'] !== '' ? $_POST['architect_cell'] : null;
    $architect_email = isset($_POST['architect_email']) && $_POST['architect_email'] !== '' ? $_POST['architect_email'] : null;
    
    // Landscape Architect fields
    $landscape_architect_name = isset($_POST['landscape_architect_name']) && $_POST['landscape_architect_name'] !== '' ? $_POST['landscape_architect_name'] : null;
    $landscape_architect_firm = isset($_POST['landscape_architect_firm']) && $_POST['landscape_architect_firm'] !== '' ? $_POST['landscape_architect_firm'] : null;
    $landscape_architect_phone = isset($_POST['landscape_architect_phone']) && $_POST['landscape_architect_phone'] !== '' ? $_POST['landscape_architect_phone'] : null;
    $landscape_architect_cell = isset($_POST['landscape_architect_cell']) && $_POST['landscape_architect_cell'] !== '' ? $_POST['landscape_architect_cell'] : null;
    $landscape_architect_email = isset($_POST['landscape_architect_email']) && $_POST['landscape_architect_email'] !== '' ? $_POST['landscape_architect_email'] : null;
    
    // Application type
    $application_type = isset($_POST['application_type']) && $_POST['application_type'] !== '' ? $_POST['application_type'] : null;
    
    // Site plan request
    $site_plan_request = isset($_POST['site_plan_request']) && $_POST['site_plan_request'] !== '' ? $_POST['site_plan_request'] : null;
    
    // Checklist items
    $checklist_application = isset($_POST['checklist_application']) ? 1 : 0;
    $checklist_verification = isset($_POST['checklist_verification']) ? 1 : 0;
    $checklist_project_plans = isset($_POST['checklist_project_plans']) ? 1 : 0;
    $checklist_landscape = isset($_POST['checklist_landscape']) ? 1 : 0;
    $checklist_topographic = isset($_POST['checklist_topographic']) ? 1 : 0;
    $checklist_traffic = isset($_POST['checklist_traffic']) ? 1 : 0;
    $checklist_architectural = isset($_POST['checklist_architectural']) ? 1 : 0;
    $checklist_covenants = isset($_POST['checklist_covenants']) ? 1 : 0;
    $checklist_fees = isset($_POST['checklist_fees']) ? 1 : 0;
    
    // Handle file uploads
    $file_verification = null;
    $file_project_plans = null;
    $file_landscape = null;
    $file_topographic = null;
    $file_traffic = null;
    $file_architectural = null;
    $file_covenants = null;
    
    if (isset($_FILES['file_verification']) && $_FILES['file_verification']['error'] === UPLOAD_ERR_OK) {
        $file_verification = file_get_contents($_FILES['file_verification']['tmp_name']);
    }
    if (isset($_FILES['file_project_plans']) && $_FILES['file_project_plans']['error'] === UPLOAD_ERR_OK) {
        $file_project_plans = file_get_contents($_FILES['file_project_plans']['tmp_name']);
    }
    if (isset($_FILES['file_landscape']) && $_FILES['file_landscape']['error'] === UPLOAD_ERR_OK) {
        $file_landscape = file_get_contents($_FILES['file_landscape']['tmp_name']);
    }
    if (isset($_FILES['file_topographic']) && $_FILES['file_topographic']['error'] === UPLOAD_ERR_OK) {
        $file_topographic = file_get_contents($_FILES['file_topographic']['tmp_name']);
    }
    if (isset($_FILES['file_traffic']) && $_FILES['file_traffic']['error'] === UPLOAD_ERR_OK) {
        $file_traffic = file_get_contents($_FILES['file_traffic']['tmp_name']);
    }
    if (isset($_FILES['file_architectural']) && $_FILES['file_architectural']['error'] === UPLOAD_ERR_OK) {
        $file_architectural = file_get_contents($_FILES['file_architectural']['tmp_name']);
    }
    if (isset($_FILES['file_covenants']) && $_FILES['file_covenants']['error'] === UPLOAD_ERR_OK) {
        $file_covenants = file_get_contents($_FILES['file_covenants']['tmp_name']);
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
    
    // Insert into database
    $success = 'Form submitted successfully!';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Site Plan Application</title>
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

        <div class="form-group">
          <label>Mailing Address:</label>
          <input type="text" class="form-control" name="additional_applicant_mailing_addresses[]">
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Phone Number:</label>
              <input type="text" class="form-control" name="additional_applicant_phones[]">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Cell Number:</label>
              <input type="text" class="form-control" name="additional_applicant_cells[]">
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

        <div class="form-group">
          <label>Mailing Address:</label>
          <input type="text" class="form-control" name="additional_owner_mailing_addresses[]">
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Phone Number:</label>
              <input type="text" class="form-control" name="additional_owner_phones[]">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Cell Number:</label>
              <input type="text" class="form-control" name="additional_owner_cells[]">
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
    <h2>Application for Site Plan</h2>
  </div>
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
  <div class="header-info">
    <div>
      <strong>Docket Number:</strong> <input type="text" name="docket_number" class="form-control small-input d-inline" style="width: 150px;">
    </div>
    <div>
      <strong>Public Hearing Date:</strong> <input type="text" name="public_hearing_date" class="form-control small-input d-inline" style="width: 150px;">
    </div>
  </div>
  <div class="header-info">
    <div>
      <strong>Date Application Filed:</strong> <input type="text" name="date_application_filed" class="form-control small-input d-inline" style="width: 150px;">
    </div>
    <div>
      <strong>Pre-Application Meeting Date:</strong> <input type="text" name="pre_application_meeting_date" class="form-control small-input d-inline" style="width: 150px;">
    </div>
  </div>

  <form method="post" enctype="multipart/form-data">
    <!-- APPLICANT'S INFORMATION -->
    <div class="section-title">APPLICANT(S) INFORMATION</div>

    <div class="form-group">
      <label>1) APPLICANT(S) NAME(S):</label>
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

    <div class="form-group">
      <label>Mailing Address:</label>
      <input type="text" class="form-control" name="applicant_mailing_address">
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="applicant_phone">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="applicant_cell">
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

    <div class="form-group">
      <label>2) PROPERTY OWNER(S) NAME(S):</label>
      <input type="text" class="form-control" name="owner_name">
    </div>

    <div class="form-group">
      <label>Mailing Address:</label>
      <input type="text" class="form-control" name="owner_mailing_address">
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="owner_phone">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="owner_cell">
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
      <label>3) APPLICANT(S) ATTORNEY:</label>
      <input type="text" class="form-control" name="attorney_name" placeholder="Name">
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

    <div class="form-group">
      <label>4) SURVEYOR:</label>
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
      <label>5) ENGINEER:</label>
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

    <div class="form-group">
      <label>6) ARCHITECT:</label>
      <input type="text" class="form-control" name="architect_name" placeholder="Name">
    </div>

    <div class="form-group">
      <label>Name of Firm:</label>
      <input type="text" class="form-control" name="architect_firm">
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="architect_phone">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="architect_cell">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>E-Mail Address:</label>
      <input type="email" class="form-control" name="architect_email">
    </div>

    <div class="form-group">
      <label>7) LANDSCAPE ARCHITECT:</label>
      <input type="text" class="form-control" name="landscape_architect_name" placeholder="Name">
    </div>

    <div class="form-group">
      <label>Name of Firm:</label>
      <input type="text" class="form-control" name="landscape_architect_firm">
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Phone Number:</label>
          <input type="text" class="form-control" name="landscape_architect_phone">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Cell Number:</label>
          <input type="text" class="form-control" name="landscape_architect_cell">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>E-Mail Address:</label>
      <input type="email" class="form-control" name="landscape_architect_email">
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
      <div class="form-group mt-2">
        <label>Application Type:</label>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="application_type" id="type1" value="new">
          <label class="form-check-label" for="type1">
            New Site Plan Application
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="application_type" id="type2" value="amendment">
          <label class="form-check-label" for="type2">
            Site Plan Amendment or PUD Amendment
          </label>
        </div>
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_verification" id="check2">
        <label class="form-check-label" for="check2">
          Water/Sewer/Floodplain Verification Letter(s) or Signature(s)
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_verification" class="font-weight-normal">Upload Verification Letter(s):</label>
        <input type="file" class="form-control-file" name="file_verification" id="file_verification">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_project_plans" id="check3">
        <label class="form-check-label" for="check3">
          Complete set of project plans prepared by a licensed surveyor or engineer depicting the various portion(s) of the property to be included in the proposed Site Plan project (Please include: two (2) - 18" x 24" plan-sets and two (2) - 11" x 17" plan-sets)
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_project_plans" class="font-weight-normal">Upload Project Plans:</label>
        <input type="file" class="form-control-file" name="file_project_plans" id="file_project_plans">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_landscape" id="check4">
        <label class="form-check-label" for="check4">
          Two (2) sets of Landscape Plan, if applicable
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_landscape" class="font-weight-normal">Upload Landscape Plan:</label>
        <input type="file" class="form-control-file" name="file_landscape" id="file_landscape">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_topographic" id="check5">
        <label class="form-check-label" for="check5">
          Two (2) sets of Topographic Survey/Drainage Plan & Calculations
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_topographic" class="font-weight-normal">Upload Topographic Survey/Drainage Plan:</label>
        <input type="file" class="form-control-file" name="file_topographic" id="file_topographic">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_traffic" id="check6">
        <label class="form-check-label" for="check6">
          Traffic Impact Study (TIS) and/or Geologic Analysis (Phase I), if required
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_traffic" class="font-weight-normal">Upload Traffic Impact Study/Geologic Analysis:</label>
        <input type="file" class="form-control-file" name="file_traffic" id="file_traffic">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_architectural" id="check7">
        <label class="form-check-label" for="check7">
          Two (2) sets of site Architectural Plan, including proposed elevations of all building sides
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_architectural" class="font-weight-normal">Upload Architectural Plan:</label>
        <input type="file" class="form-control-file" name="file_architectural" id="file_architectural">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_covenants" id="check8">
        <label class="form-check-label" for="check8">
          Two (2) draft sets of proposed Property or Condominium Owners Association Covenants, Master Deed or Restrictions, if applicable
        </label>
      </div>
      <div class="file-upload-section">
        <label for="file_covenants" class="font-weight-normal">Upload Covenants/Master Deed:</label>
        <input type="file" class="form-control-file" name="file_covenants" id="file_covenants">
      </div>
    </div>

    <div class="checklist-item">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="checklist_fees" id="check9">
        <label class="form-check-label" for="check9">
          Filing and Recording Fees
        </label>
      </div>
    </div>

    <!-- SITE PLAN REQUEST -->
    <div class="section-title">SITE PLAN REQUEST</div>

    <div class="form-group">
      <label>Please describe, in detail, the proposed use and desired site plan project request of the property being considered:</label>
      <textarea class="form-control" name="site_plan_request" rows="4"></textarea>
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
          <input type="text" class="form-control" name="date_fees_received">
        </div>
      </div>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="form_paid_bool" value="1" id="paid">
      <label class="form-check-label" for="paid">
        <strong>Form Paid</strong>
      </label>
    </div>

    <div class="form-group">
      <label>Correction Form ID (if applicable):</label>
      <input type="number" class="form-control" name="correction_form_id">
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