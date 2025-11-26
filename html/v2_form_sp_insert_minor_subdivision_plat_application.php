<?php
require_once 'config.php';
require_once 'zoning_form_functions.php';

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
    // Extract form data using the function
    $formData = extractMinorSubdivisionPlatFormData($_POST, $_FILES);
    
    // Validate the form data
    $errors = validateMinorSubdivisionPlatData($formData);
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        // Insert the application
        $result = insertMinorSubdivisionPlatApplication($conn, $formData);
        
        if ($result['success']) {
            $success = $result['message'];
            
            // Link form to client if form_id is returned
            if (isset($result['form_id']) && $result['form_id']) {
                $linkResult = linkFormToClient($conn, $result['form_id'], $client_id);
                if (!$linkResult['success']) {
                    $error = 'Application submitted but failed to link to client: ' . $linkResult['message'];
                }
            }
        } else {
            $error = $result['message'];
        }
    }
}

// Fetch states for dropdown
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