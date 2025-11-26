<?php
require_once 'config.php';
requireLogin();

// Show all errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Show all errors from MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId(); // Assuming client_id is captured if needed by form-specific tables, though not directly in this SP.
$success = '';
$error = '';

// Fetch state codes for dropdown
$states_result = $conn->query("SELECT state_code FROM states ORDER BY state_code");
$states = [];
if ($states_result) {
    while ($row = $states_result->fetch_assoc()) {
        $states[] = $row['state_code'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form metadata
        $p_form_datetime_resolved = isset($_POST['p_form_datetime_resolved']) && $_POST['p_form_datetime_resolved'] !== '' ? $_POST['p_form_datetime_resolved'] : null;
        $p_form_paid_bool = 0; // Always 0 when submitted by client
        $p_correction_form_id = isset($_POST['p_correction_form_id']) && $_POST['p_correction_form_id'] !== '' ? $_POST['p_correction_form_id'] : null;
        
        // Hearing information
        $p_docket_number = isset($_POST['p_docket_number']) && $_POST['p_docket_number'] !== '' ? $_POST['p_docket_number'] : null;
        $p_public_hearing_date = isset($_POST['p_public_hearing_date']) && $_POST['p_public_hearing_date'] !== '' ? $_POST['p_public_hearing_date'] : null;
        $p_date_application_filed = isset($_POST['p_date_application_filed']) && $_POST['p_date_application_filed'] !== '' ? $_POST['p_date_application_filed'] : null;
        $p_preapp_meeting_date = isset($_POST['p_preapp_meeting_date']) && $_POST['p_preapp_meeting_date'] !== '' ? $_POST['p_preapp_meeting_date'] : null;
        
        // Primary applicant
        $p_applicant_name = isset($_POST['p_applicant_name']) && $_POST['p_applicant_name'] !== '' ? $_POST['p_applicant_name'] : null;
        // Officers names (JSON array)
        $p_officers_names = isset($_POST['p_officers_names']) && is_array($_POST['p_officers_names']) ? json_encode(array_filter($_POST['p_officers_names'])) : '[]';
        if ($p_officers_names == '[]') $p_officers_names = null; // Set to null if empty array
        
        $p_applicant_street = isset($_POST['p_applicant_street']) && $_POST['p_applicant_street'] !== '' ? $_POST['p_applicant_street'] : null;
        $p_applicant_phone = isset($_POST['p_applicant_phone']) && $_POST['p_applicant_phone'] !== '' ? $_POST['p_applicant_phone'] : null;
        $p_applicant_cell = isset($_POST['p_applicant_cell']) && $_POST['p_applicant_cell'] !== '' ? $_POST['p_applicant_cell'] : null;
        $p_applicant_city = isset($_POST['p_applicant_city']) && $_POST['p_applicant_city'] !== '' ? $_POST['p_applicant_city'] : null;
        $p_applicant_state = isset($_POST['p_applicant_state']) && $_POST['p_applicant_state'] !== '' ? $_POST['p_applicant_state'] : null;
        $p_applicant_zip_code = isset($_POST['p_applicant_zip_code']) && $_POST['p_applicant_zip_code'] !== '' ? $_POST['p_applicant_zip_code'] : null;
        $p_applicant_other_address = isset($_POST['p_applicant_other_address']) && $_POST['p_applicant_other_address'] !== '' ? $_POST['p_applicant_other_other_address'] : null; // typo in HTML
        $p_applicant_email = isset($_POST['p_applicant_email']) && $_POST['p_applicant_email'] !== '' ? $_POST['p_applicant_email'] : null;

        // Additional applicants (JSON arrays)
        $p_additional_applicant_names = isset($_POST['p_additional_applicant_names']) && is_array($_POST['p_additional_applicant_names']) ? json_encode(array_filter($_POST['p_additional_applicant_names'])) : '[]';
        if ($p_additional_applicant_names == '[]') $p_additional_applicant_names = null;
        $p_additional_applicant_officers = isset($_POST['p_additional_applicant_officers']) && is_array($_POST['p_additional_applicant_officers']) ? json_encode(array_filter($_POST['p_additional_applicant_officers'], function($item) { return !empty($item) && is_array($item); })) : '[]';
        if ($p_additional_applicant_officers == '[]') $p_additional_applicant_officers = null;
        $p_additional_applicant_streets = isset($_POST['p_additional_applicant_streets']) && is_array($_POST['p_additional_applicant_streets']) ? json_encode(array_filter($_POST['p_additional_applicant_streets'])) : '[]';
        if ($p_additional_applicant_streets == '[]') $p_additional_applicant_streets = null;
        $p_additional_applicant_phones = isset($_POST['p_additional_applicant_phones']) && is_array($_POST['p_additional_applicant_phones']) ? json_encode(array_filter($_POST['p_additional_applicant_phones'])) : '[]';
        if ($p_additional_applicant_phones == '[]') $p_additional_applicant_phones = null;
        $p_additional_applicant_cells = isset($_POST['p_additional_applicant_cells']) && is_array($_POST['p_additional_applicant_cells']) ? json_encode(array_filter($_POST['p_additional_applicant_cells'])) : '[]';
        if ($p_additional_applicant_cells == '[]') $p_additional_applicant_cells = null;
        $p_additional_applicant_cities = isset($_POST['p_additional_applicant_cities']) && is_array($_POST['p_additional_applicant_cities']) ? json_encode(array_filter($_POST['p_additional_applicant_cities'])) : '[]';
        if ($p_additional_applicant_cities == '[]') $p_additional_applicant_cities = null;
        $p_additional_applicant_states = isset($_POST['p_additional_applicant_states']) && is_array($_POST['p_additional_applicant_states']) ? json_encode(array_filter($_POST['p_additional_applicant_states'])) : '[]';
        if ($p_additional_applicant_states == '[]') $p_additional_applicant_states = null;
        $p_additional_applicant_zip_codes = isset($_POST['p_additional_applicant_zip_codes']) && is_array($_POST['p_additional_applicant_zip_codes']) ? json_encode(array_filter($_POST['p_additional_applicant_zip_codes'])) : '[]';
        if ($p_additional_applicant_zip_codes == '[]') $p_additional_applicant_zip_codes = null;
        $p_additional_applicant_other_addresses = isset($_POST['p_additional_applicant_other_addresses']) && is_array($_POST['p_additional_applicant_other_addresses']) ? json_encode(array_filter($_POST['p_additional_applicant_other_addresses'])) : '[]';
        if ($p_additional_applicant_other_addresses == '[]') $p_additional_applicant_other_addresses = null;
        $p_additional_applicant_emails = isset($_POST['p_additional_applicant_emails']) && is_array($_POST['p_additional_applicant_emails']) ? json_encode(array_filter($_POST['p_additional_applicant_emails'])) : '[]';
        if ($p_additional_applicant_emails == '[]') $p_additional_applicant_emails = null;

        // Property owner
        $p_owner_first_name = isset($_POST['p_owner_first_name']) && $_POST['p_owner_first_name'] !== '' ? $_POST['p_owner_first_name'] : null;
        $p_owner_last_name = isset($_POST['p_owner_last_name']) && $_POST['p_owner_last_name'] !== '' ? $_POST['p_owner_last_name'] : null;
        $p_owner_street = isset($_POST['p_owner_street']) && $_POST['p_owner_street'] !== '' ? $_POST['p_owner_street'] : null;
        $p_owner_phone = isset($_POST['p_owner_phone']) && $_POST['p_owner_phone'] !== '' ? $_POST['p_owner_phone'] : null;
        $p_owner_cell = isset($_POST['p_owner_cell']) && $_POST['p_owner_cell'] !== '' ? $_POST['p_owner_cell'] : null;
        $p_owner_city = isset($_POST['p_owner_city']) && $_POST['p_owner_city'] !== '' ? $_POST['p_owner_city'] : null;
        $p_owner_state = isset($_POST['p_owner_state']) && $_POST['p_owner_state'] !== '' ? $_POST['p_owner_state'] : null;
        $p_owner_zip_code = isset($_POST['p_owner_zip_code']) && $_POST['p_owner_zip_code'] !== '' ? $_POST['p_owner_zip_code'] : null;
        $p_owner_other_address = isset($_POST['p_owner_other_address']) && $_POST['p_owner_other_address'] !== '' ? $_POST['p_owner_other_address'] : null;
        $p_owner_email = isset($_POST['p_owner_email']) && $_POST['p_owner_email'] !== '' ? $_POST['p_owner_email'] : null;
        
        // Additional owners (JSON arrays)
        $p_additional_owner_names = isset($_POST['p_additional_owner_names']) && is_array($_POST['p_additional_owner_names']) ? json_encode(array_filter($_POST['p_additional_owner_names'])) : '[]';
        if ($p_additional_owner_names == '[]') $p_additional_owner_names = null;
        $p_additional_owner_streets = isset($_POST['p_additional_owner_streets']) && is_array($_POST['p_additional_owner_streets']) ? json_encode(array_filter($_POST['p_additional_owner_streets'])) : '[]';
        if ($p_additional_owner_streets == '[]') $p_additional_owner_streets = null;
        $p_additional_owner_phones = isset($_POST['p_additional_owner_phones']) && is_array($_POST['p_additional_owner_phones']) ? json_encode(array_filter($_POST['p_additional_owner_phones'])) : '[]';
        if ($p_additional_owner_phones == '[]') $p_additional_owner_phones = null;
        $p_additional_owner_cells = isset($_POST['p_additional_owner_cells']) && is_array($_POST['p_additional_owner_cells']) ? json_encode(array_filter($_POST['p_additional_owner_cells'])) : '[]';
        if ($p_additional_owner_cells == '[]') $p_additional_owner_cells = null;
        $p_additional_owner_cities = isset($_POST['p_additional_owner_cities']) && is_array($_POST['p_additional_owner_cities']) ? json_encode(array_filter($_POST['p_additional_owner_cities'])) : '[]';
        if ($p_additional_owner_cities == '[]') $p_additional_owner_cities = null;
        $p_additional_owner_states = isset($_POST['p_additional_owner_states']) && is_array($_POST['p_additional_owner_states']) ? json_encode(array_filter($_POST['p_additional_owner_states'])) : '[]';
        if ($p_additional_owner_states == '[]') $p_additional_owner_states = null;
        $p_additional_owner_zip_codes = isset($_POST['p_additional_owner_zip_codes']) && is_array($_POST['p_additional_owner_zip_codes']) ? json_encode(array_filter($_POST['p_additional_owner_zip_codes'])) : '[]';
        if ($p_additional_owner_zip_codes == '[]') $p_additional_owner_zip_codes = null;
        $p_additional_owner_other_addresses = isset($_POST['p_additional_owner_other_addresses']) && is_array($_POST['p_additional_owner_other_addresses']) ? json_encode(array_filter($_POST['p_additional_owner_other_addresses'])) : '[]';
        if ($p_additional_owner_other_addresses == '[]') $p_additional_owner_other_addresses = null;
        $p_additional_owner_emails = isset($_POST['p_additional_owner_emails']) && is_array($_POST['p_additional_owner_emails']) ? json_encode(array_filter($_POST['p_additional_owner_emails'])) : '[]';
        if ($p_additional_owner_emails == '[]') $p_additional_owner_emails = null;

        // Attorney
        $p_attorney_first_name = isset($_POST['p_attorney_first_name']) && $_POST['p_attorney_first_name'] !== '' ? $_POST['p_attorney_first_name'] : null;
        $p_attorney_last_name = isset($_POST['p_attorney_last_name']) && $_POST['p_attorney_last_name'] !== '' ? $_POST['p_attorney_last_name'] : null;
        $p_law_firm = isset($_POST['p_law_firm']) && $_POST['p_law_firm'] !== '' ? $_POST['p_law_firm'] : null;
        $p_attorney_phone = isset($_POST['p_attorney_phone']) && $_POST['p_attorney_phone'] !== '' ? $_POST['p_attorney_phone'] : null;
        $p_attorney_cell = isset($_POST['p_attorney_cell']) && $_POST['p_attorney_cell'] !== '' ? $_POST['p_attorney_cell'] : null;
        $p_attorney_email = isset($_POST['p_attorney_email']) && $_POST['p_attorney_email'] !== '' ? $_POST['p_attorney_email'] : null;
        
        // Property information
        $p_property_street = isset($_POST['p_property_street']) && $_POST['p_property_street'] !== '' ? $_POST['p_property_street'] : null;
        $p_property_city = isset($_POST['p_property_city']) && $_POST['p_property_city'] !== '' ? $_POST['p_property_city'] : null;
        $p_property_state = isset($_POST['p_property_state']) && $_POST['p_property_state'] !== '' ? $_POST['p_property_state'] : null;
        $p_property_zip_code = isset($_POST['p_property_zip_code']) && $_POST['p_property_zip_code'] !== '' ? $_POST['p_property_zip_code'] : null;
        $p_property_other_address = isset($_POST['p_property_other_address']) && $_POST['p_property_other_address'] !== '' ? $_POST['p_property_other_address'] : null;
        $p_parcel_number = isset($_POST['p_parcel_number']) && $_POST['p_parcel_number'] !== '' ? $_POST['p_parcel_number'] : null;
        $p_acreage = isset($_POST['p_acreage']) && $_POST['p_acreage'] !== '' ? $_POST['p_acreage'] : null;
        $p_current_zoning = isset($_POST['p_current_zoning']) && $_POST['p_current_zoning'] !== '' ? $_POST['p_current_zoning'] : null;
        
        // Variance request
        $p_variance_request = isset($_POST['p_variance_request']) && $_POST['p_variance_request'] !== '' ? $_POST['p_variance_request'] : null;
        $p_proposed_conditions = isset($_POST['p_proposed_conditions']) && $_POST['p_proposed_conditions'] !== '' ? $_POST['p_proposed_conditions'] : null;
        $p_findings_explanation = isset($_POST['p_findings_explanation']) && $_POST['p_findings_explanation'] !== '' ? $_POST['p_findings_explanation'] : null;
        
        // Checklist items
        $p_checklist_application = isset($_POST['p_checklist_application']) ? 1 : 0;
        $p_checklist_exhibit = isset($_POST['p_checklist_exhibit']) ? 1 : 0;
        $p_checklist_adjacent = isset($_POST['p_checklist_adjacent']) ? 1 : 0;
        $p_checklist_fees = isset($_POST['p_checklist_fees']) ? 1 : 0;
        
        // File uploads (currently just names/paths, actual upload logic is separate)
        $p_file_exhibit = isset($_FILES['p_file_exhibit']['name']) && $_FILES['p_file_exhibit']['name'] !== '' ? basename($_FILES['p_file_exhibit']['name']) : null;
        $p_file_adjacent = isset($_FILES['p_file_adjacent']['name']) && $_FILES['p_file_adjacent']['name'] !== '' ? basename($_FILES['p_file_adjacent']['name']) : null;
        
        // Signatures
        $p_signature_date_1 = isset($_POST['p_signature_date_1']) && $_POST['p_signature_date_1'] !== '' ? $_POST['p_signature_date_1'] : null;
        $p_signature_name_1 = isset($_POST['p_signature_name_1']) && $_POST['p_signature_name_1'] !== '' ? $_POST['p_signature_name_1'] : null;
        $p_signature_date_2 = isset($_POST['p_signature_date_2']) && $_POST['p_signature_date_2'] !== '' ? $_POST['p_signature_date_2'] : null;
        $p_signature_name_2 = isset($_POST['p_signature_name_2']) && $_POST['p_signature_name_2'] !== '' ? $_POST['p_signature_name_2'] : null;

        // Admin/fees (these are usually set by govt worker, but including for SP completeness)
        $p_application_fee = isset($_POST['p_application_fee']) && $_POST['p_application_fee'] !== '' ? $_POST['p_application_fee'] : null;
        $p_certificate_fee = isset($_POST['p_certificate_fee']) && $_POST['p_certificate_fee'] !== '' ? $_POST['p_certificate_fee'] : null;

        $sql = "CALL sp_insert_variance_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $types = 'siisssssssssssssssssssssssssssssssssssssssssssssssisssssiiiissssss'; // 68 parameters
        $bind_params = [
            &$p_form_datetime_resolved,
            &$p_form_paid_bool,
            &$p_correction_form_id,
            &$p_docket_number,
            &$p_public_hearing_date,
            &$p_date_application_filed,
            &$p_preapp_meeting_date,
            &$p_applicant_name,
            &$p_officers_names,
            &$p_applicant_street,
            &$p_applicant_phone,
            &$p_applicant_cell,
            &$p_applicant_city,
            &$p_applicant_state,
            &$p_applicant_zip_code,
            &$p_applicant_other_address,
            &$p_applicant_email,
            &$p_additional_applicant_names,
            &$p_additional_applicant_officers,
            &$p_additional_applicant_streets,
            &$p_additional_applicant_phones,
            &$p_additional_applicant_cells,
            &$p_additional_applicant_cities,
            &$p_additional_applicant_states,
            &$p_additional_applicant_zip_codes,
            &$p_additional_applicant_other_addresses,
            &$p_additional_applicant_emails,
            &$p_owner_first_name,
            &$p_owner_last_name,
            &$p_owner_street,
            &$p_owner_phone,
            &$p_owner_cell,
            &$p_owner_city,
            &$p_owner_state,
            &$p_owner_zip_code,
            &$p_owner_other_address,
            &$p_owner_email,
            &$p_additional_owner_names,
            &$p_additional_owner_streets,
            &$p_additional_owner_phones,
            &$p_additional_owner_cells,
            &$p_additional_owner_cities,
            &$p_additional_owner_states,
            &$p_additional_owner_zip_codes,
            &$p_additional_owner_other_addresses,
            &$p_additional_owner_emails,
            &$p_attorney_first_name,
            &$p_attorney_last_name,
            &$p_law_firm,
            &$p_attorney_phone,
            &$p_attorney_cell,
            &$p_attorney_email,
            &$p_property_street,
            &$p_property_city,
            &$p_property_state,
            &$p_property_zip_code,
            &$p_property_other_address,
            &$p_parcel_number,
            &$p_acreage,
            &$p_current_zoning,
            &$p_variance_request,
            &$p_proposed_conditions,
            &$p_findings_explanation,
            &$p_checklist_application,
            &$p_checklist_exhibit,
            &$p_checklist_adjacent,
            &$p_checklist_fees,
            &$p_file_exhibit,
            &$p_file_adjacent,
            &$p_signature_date_1,
            &$p_signature_name_1,
            &$p_signature_date_2,
            &$p_signature_name_2,
            &$p_application_fee,
            &$p_certificate_fee
        ];

        array_unshift($bind_params, $types);
        $bind_result = call_user_func_array([$stmt, 'bind_param'], $bind_params);

        if ($bind_result === false) {
            throw new Exception('Bind failed: ' . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        // Fetch the form_id returned by the stored procedure
        $stmt->next_result(); // Move to the next result set (the SELECT @new_form_id)
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $new_form_id = $row['form_id'];

        $success = 'Variance Application submitted successfully! Your Form ID is: ' . $new_form_id;
        
        // Clear POST data after successful submission to prevent re-submission on refresh
        $_POST = array();

    } catch (Exception $e) {
        $error = 'An error occurred: ' . $e->getMessage();
        error_log("Error in variance_application.php: " . $e->getMessage());
    } finally {
        if ($stmt) $stmt->close();
        // The connection will be closed at the end of the script
    }
}
$conn->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Variance Application</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body { background: linear-gradient(135deg, #e3f2fd, #bbdefb); } /* Lighter blue gradient */
    .navbar { background-color: #2196F3 !important; } /* Blue navbar */
    .section-header {
      background-color: #1976D2; /* Darker blue */
      color: white;
      padding: 10px;
      margin-top: 20px;
      margin-bottom: 15px;
      border-radius: 5px;
      font-weight: bold;
    }
    .add-more-button {
        margin-top: 10px;
    }
    .dynamic-field-group {
        border: 1px dashed #bbb;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
        background-color: #f8f8f8;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-dark" style="background-color: #2196F3;">
  <div class="container">
    <span class="navbar-brand mb-0 h1">Client Portal — Planning & Zoning</span>
  </div>
</nav>

<div class="container py-4">
  <h1>Variance Application</h1>
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>
  
  <div class="card p-4 shadow-sm">
    <form method="post" enctype="multipart/form-data">
      
      <!-- Form Information Section -->
      <div class="section-header">Form Information</div>
      
      <div class="form-group">
        <label for="p_form_datetime_resolved">Form Datetime Resolved (optional)</label>
        <input class="form-control" type="datetime-local" id="p_form_datetime_resolved" name="p_form_datetime_resolved">
        <small class="form-text text-muted">Leave blank; typically set by government worker</small>
      </div>
      
      <div class="form-group">
        <label for="p_correction_form_id">Correction Form ID (optional)</label>
        <input class="form-control" type="number" id="p_correction_form_id" name="p_correction_form_id">
        <small class="form-text text-muted">Only if this is a correction of a previous form</small>
      </div>

      <!-- Hearing Information Section -->
      <div class="section-header">Hearing Information</div>
      
      <div class="form-group">
        <label for="p_docket_number">Docket Number</label>
        <input class="form-control" type="text" id="p_docket_number" name="p_docket_number">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_public_hearing_date">Public Hearing Date</label>
          <input class="form-control" type="date" id="p_public_hearing_date" name="p_public_hearing_date">
        </div>
        <div class="form-group col-md-6">
          <label for="p_date_application_filed">Date Application Filed</label>
          <input class="form-control" type="date" id="p_date_application_filed" name="p_date_application_filed">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_preapp_meeting_date">Pre-Application Meeting Date</label>
        <input class="form-control" type="date" id="p_preapp_meeting_date" name="p_preapp_meeting_date">
      </div>

      <!-- Primary Applicant Information Section -->
      <div class="section-header">Primary Applicant Information</div>
      
      <div class="form-group">
        <label for="p_applicant_name">Full Name / Company Name</label>
        <input class="form-control" type="text" id="p_applicant_name" name="p_applicant_name" required>
      </div>
      
      <div id="officers-container">
        <div class="form-group">
          <label>Officers / Directors (for corporate applicants)</label>
          <div class="input-group mb-2">
            <input type="text" class="form-control" name="p_officers_names[]" placeholder="Officer Name">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-danger remove-officer">Remove</button>
            </div>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-secondary btn-sm add-more-button" id="add-officer">Add Another Officer</button>
      
      <div class="form-group mt-3">
        <label for="p_applicant_street">Street Address</label>
        <input class="form-control" type="text" id="p_applicant_street" name="p_applicant_street">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="p_applicant_city">City</label>
          <input class="form-control" type="text" id="p_applicant_city" name="p_applicant_city">
        </div>
        <div class="form-group col-md-4">
          <label for="p_applicant_state">State</label>
          <select class="form-control" id="p_applicant_state" name="p_applicant_state">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="p_applicant_zip_code">ZIP Code</label>
          <input class="form-control" type="text" id="p_applicant_zip_code" name="p_applicant_zip_code">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_applicant_other_address">Other Address Details (optional)</label>
        <input class="form-control" type="text" id="p_applicant_other_address" name="p_applicant_other_address">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_applicant_phone">Phone Number</label>
          <input class="form-control" type="tel" id="p_applicant_phone" name="p_applicant_phone">
        </div>
        <div class="form-group col-md-6">
          <label for="p_applicant_cell">Cell Phone</label>
          <input class="form-control" type="tel" id="p_applicant_cell" name="p_applicant_cell">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_applicant_email">Email</label>
        <input class="form-control" type="email" id="p_applicant_email" name="p_applicant_email">
      </div>

      <!-- Additional Applicants Section -->
      <div class="section-header">Additional Applicants</div>
      <div id="additional-applicants-container">
        <!-- Dynamic additional applicant fields will be added here by JS -->
      </div>
      <button type="button" class="btn btn-info btn-sm add-more-button" id="add-additional-applicant">Add Another Applicant</button>

      <!-- Primary Property Owner Information Section -->
      <div class="section-header">Primary Property Owner Information</div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_owner_first_name">First Name</label>
          <input class="form-control" type="text" id="p_owner_first_name" name="p_owner_first_name">
        </div>
        <div class="form-group col-md-6">
          <label for="p_owner_last_name">Last Name</label>
          <input class="form-control" type="text" id="p_owner_last_name" name="p_owner_last_name">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_owner_street">Street Address</label>
        <input class="form-control" type="text" id="p_owner_street" name="p_owner_street">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="p_owner_city">City</label>
          <input class="form-control" type="text" id="p_owner_city" name="p_owner_city">
        </div>
        <div class="form-group col-md-4">
          <label for="p_owner_state">State</label>
          <select class="form-control" id="p_owner_state" name="p_owner_state">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="p_owner_zip_code">ZIP Code</label>
          <input class="form-control" type="text" id="p_owner_zip_code" name="p_owner_zip_code">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_owner_other_address">Other Address Details (optional)</label>
        <input class="form-control" type="text" id="p_owner_other_address" name="p_owner_other_address">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_owner_phone">Phone Number</label>
          <input class="form-control" type="tel" id="p_owner_phone" name="p_owner_phone">
        </div>
        <div class="form-group col-md-6">
          <label for="p_owner_cell">Cell Phone</label>
          <input class="form-control" type="tel" id="p_owner_cell" name="p_owner_cell">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_owner_email">Email</label>
        <input class="form-control" type="email" id="p_owner_email" name="p_owner_email">
      </div>

      <!-- Additional Property Owners Section -->
      <div class="section-header">Additional Property Owners</div>
      <div id="additional-owners-container">
        <!-- Dynamic additional owner fields will be added here by JS -->
      </div>
      <button type="button" class="btn btn-info btn-sm add-more-button" id="add-additional-owner">Add Another Owner</button>

      <!-- Attorney Information Section -->
      <div class="section-header">Attorney Information</div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_attorney_first_name">First Name</label>
          <input class="form-control" type="text" id="p_attorney_first_name" name="p_attorney_first_name">
        </div>
        <div class="form-group col-md-6">
          <label for="p_attorney_last_name">Last Name</label>
          <input class="form-control" type="text" id="p_attorney_last_name" name="p_attorney_last_name">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_law_firm">Law Firm</label>
        <input class="form-control" type="text" id="p_law_firm" name="p_law_firm">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_attorney_phone">Phone Number</label>
          <input class="form-control" type="tel" id="p_attorney_phone" name="p_attorney_phone">
        </div>
        <div class="form-group col-md-6">
          <label for="p_attorney_cell">Cell Phone</label>
          <input class="form-control" type="tel" id="p_attorney_cell" name="p_attorney_cell">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_attorney_email">Email</label>
        <input class="form-control" type="email" id="p_attorney_email" name="p_attorney_email">
      </div>

      <!-- Property Information Section -->
      <div class="section-header">Property Information</div>
      
      <div class="form-group">
        <label for="p_property_street">Street Address</label>
        <input class="form-control" type="text" id="p_property_street" name="p_property_street">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="p_property_city">City</label>
          <input class="form-control" type="text" id="p_property_city" name="p_property_city">
        </div>
        <div class="form-group col-md-4">
          <label for="p_property_state">State</label>
          <select class="form-control" id="p_property_state" name="p_property_state">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="p_property_zip_code">ZIP Code</label>
          <input class="form-control" type="text" id="p_property_zip_code" name="p_property_zip_code">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_property_other_address">Other Property Address Details (optional)</label>
        <input class="form-control" type="text" id="p_property_other_address" name="p_property_other_address">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="p_parcel_number">PVA Parcel Number</label>
          <input class="form-control" type="number" id="p_parcel_number" name="p_parcel_number">
        </div>
        <div class="form-group col-md-4">
          <label for="p_acreage">Acreage</label>
          <input class="form-control" type="text" id="p_acreage" name="p_acreage">
        </div>
        <div class="form-group col-md-4">
          <label for="p_current_zoning">Current Zoning</label>
          <input class="form-control" type="text" id="p_current_zoning" name="p_current_zoning">
        </div>
      </div>

      <!-- Variance Request Details Section -->
      <div class="section-header">Variance Request Details</div>
      
      <div class="form-group">
        <label for="p_variance_request">Variance Request (explain in detail)</label>
        <textarea class="form-control" id="p_variance_request" name="p_variance_request" rows="5" required></textarea>
      </div>
      
      <div class="form-group">
        <label for="p_proposed_conditions">Proposed Conditions (if any)</label>
        <textarea class="form-control" id="p_proposed_conditions" name="p_proposed_conditions" rows="3"></textarea>
      </div>
      
      <div class="form-group">
        <label for="p_findings_explanation">Explanation of Findings</label>
        <textarea class="form-control" id="p_findings_explanation" name="p_findings_explanation" rows="4"></textarea>
      </div>

      <!-- Checklist Items Section -->
      <div class="section-header">Checklist Items</div>
      
      <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="p_checklist_application" name="p_checklist_application" value="1">
        <label class="form-check-label" for="p_checklist_application">Completed Application Form</label>
      </div>
      <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="p_checklist_exhibit" name="p_checklist_exhibit" value="1">
        <label class="form-check-label" for="p_checklist_exhibit">Exhibit / Site Plan</label>
      </div>
      <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="p_checklist_adjacent" name="p_checklist_adjacent" value="1">
        <label class="form-check-label" for="p_checklist_adjacent">Adjacent Property Owners List</label>
      </div>
      <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="p_checklist_fees" name="p_checklist_fees" value="1">
        <label class="form-check-label" for="p_checklist_fees">Required Fees</label>
      </div>

      <!-- File Uploads Section (Note: Actual file storage logic needs to be implemented separately) -->
      <div class="section-header">File Uploads</div>
      
      <div class="form-group">
        <label for="p_file_exhibit">Upload Exhibit / Site Plan</label>
        <input type="file" class="form-control-file" id="p_file_exhibit" name="p_file_exhibit">
        <small class="form-text text-muted">PDF or image files preferred.</small>
      </div>
      
      <div class="form-group">
        <label for="p_file_adjacent">Upload Adjacent Property Owners List</label>
        <input type="file" class="form-control-file" id="p_file_adjacent" name="p_file_adjacent">
        <small class="form-text text-muted">PDF or spreadsheet files preferred.</small>
      </div>

      <!-- Signatures Section -->
      <div class="section-header">Signatures</div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_signature_name_1">Signature 1 Name</label>
          <input class="form-control" type="text" id="p_signature_name_1" name="p_signature_name_1" required>
        </div>
        <div class="form-group col-md-6">
          <label for="p_signature_date_1">Signature 1 Date</label>
          <input class="form-control" type="date" id="p_signature_date_1" name="p_signature_date_1" required>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_signature_name_2">Signature 2 Name (optional)</label>
          <input class="form-control" type="text" id="p_signature_name_2" name="p_signature_name_2">
        </div>
        <div class="form-group col-md-6">
          <label for="p_signature_date_2">Signature 2 Date (optional)</label>
          <input class="form-control" type="date" id="p_signature_date_2" name="p_signature_date_2">
        </div>
      </div>

      <!-- Admin/Fees Section (Visible for completeness, but typically filled by govt worker) -->
      <div class="section-header">Admin & Fees (for internal use)</div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_application_fee">Application Fee</label>
          <input class="form-control" type="text" id="p_application_fee" name="p_application_fee" placeholder="$0.00">
          <small class="form-text text-muted">Typically filled by government worker.</small>
        </div>
        <div class="form-group col-md-6">
          <label for="p_certificate_fee">Certificate Fee</label>
          <input class="form-control" type="text" id="p_certificate_fee" name="p_certificate_fee" placeholder="$0.00">
          <small class="form-text text-muted">Typically filled by government worker.</small>
        </div>
      </div>

      <div class="form-group mt-4">
        <button class="btn btn-primary btn-lg" type="submit">Submit Variance Application</button>
        <a href="client_new_form.php" class="btn btn-secondary btn-lg ml-2">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Function to add a new officer input field
    $('#add-officer').click(function() {
        var officerHtml = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="p_officers_names[]" placeholder="Officer Name">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger remove-officer">Remove</button>
                </div>
            </div>
        `;
        $('#officers-container').append(officerHtml);
    });

    // Remove officer input field
    $('#officers-container').on('click', '.remove-officer', function() {
        $(this).closest('.input-group').remove();
    });

    // Function to add a new additional applicant group
    $('#add-additional-applicant').click(function() {
        var applicantCount = $('#additional-applicants-container .dynamic-field-group').length;
        var newApplicantHtml = `
            <div class="dynamic-field-group">
                <h4>Additional Applicant #${applicantCount + 1} <button type="button" class="btn btn-danger btn-sm float-right remove-dynamic-group">Remove</button></h4>
                <div class="form-group">
                    <label for="p_additional_applicant_names_${applicantCount}">Full Name / Company Name</label>
                    <input class="form-control" type="text" name="p_additional_applicant_names[]" id="p_additional_applicant_names_${applicantCount}" required>
                </div>
                <div id="additional-officers-container-${applicantCount}">
                    <div class="form-group">
                        <label>Officers / Directors (for this applicant)</label>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="p_additional_applicant_officers[${applicantCount}][]" placeholder="Officer Name">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-danger remove-officer">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm add-more-button add-additional-officer" data-applicant-index="${applicantCount}">Add Officer for this Applicant</button>

                <div class="form-group mt-3">
                    <label for="p_additional_applicant_streets_${applicantCount}">Street Address</label>
                    <input class="form-control" type="text" name="p_additional_applicant_streets[]" id="p_additional_applicant_streets_${applicantCount}">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="p_additional_applicant_cities_${applicantCount}">City</label>
                        <input class="form-control" type="text" name="p_additional_applicant_cities[]" id="p_additional_applicant_cities_${applicantCount}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="p_additional_applicant_states_${applicantCount}">State</label>
                        <select class="form-control" name="p_additional_applicant_states[]" id="p_additional_applicant_states_${applicantCount}">
                            <option value="">Select State</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="p_additional_applicant_zip_codes_${applicantCount}">ZIP Code</label>
                        <input class="form-control" type="text" name="p_additional_applicant_zip_codes[]" id="p_additional_applicant_zip_codes_${applicantCount}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="p_additional_applicant_other_addresses_${applicantCount}">Other Address Details (optional)</label>
                    <input class="form-control" type="text" name="p_additional_applicant_other_addresses[]" id="p_additional_applicant_other_addresses_${applicantCount}">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="p_additional_applicant_phones_${applicantCount}">Phone Number</label>
                        <input class="form-control" type="tel" name="p_additional_applicant_phones[]" id="p_additional_applicant_phones_${applicantCount}">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="p_additional_applicant_cells_${applicantCount}">Cell Phone</label>
                        <input class="form-control" type="tel" name="p_additional_applicant_cells[]" id="p_additional_applicant_cells_${applicantCount}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="p_additional_applicant_emails_${applicantCount}">Email</label>
                    <input class="form-control" type="email" name="p_additional_applicant_emails[]" id="p_additional_applicant_emails_${applicantCount}">
                </div>
            </div>
        `;
        $('#additional-applicants-container').append(newApplicantHtml);
    });

    // Add officer for additional applicant
    $('#additional-applicants-container').on('click', '.add-additional-officer', function() {
        var applicantIndex = $(this).data('applicant-index');
        var officerHtml = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="p_additional_applicant_officers[${applicantIndex}][]" placeholder="Officer Name">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger remove-officer">Remove</button>
                </div>
            </div>
        `;
        $(`#additional-officers-container-${applicantIndex}`).append(officerHtml);
    });

    // Remove dynamic field group (applicant or owner)
    $(document).on('click', '.remove-dynamic-group', function() {
        $(this).closest('.dynamic-field-group').remove();
        // Re-index additional applicant/owner titles if necessary (optional)
        $('#additional-applicants-container .dynamic-field-group').each(function(index) {
            $(this).find('h4').first().html(`Additional Applicant #${index + 1} <button type="button" class="btn btn-danger btn-sm float-right remove-dynamic-group">Remove</button>`);
            // Update data-applicant-index for officer buttons if you re-index the names array
            $(this).find('.add-additional-officer').attr('data-applicant-index', index);
            // This is crucial: if you re-index the titles, you also need to re-index the `name` attributes of the officer inputs.
            // For simplicity, current implementation just uses `[${applicantCount}][]` based on initial count,
            // which will leave gaps in array indices if an item in the middle is removed.
            // MySQL's JSON_EXTRACT can handle gaps, but if not, a more complex re-indexing JS is needed.
        });
        $('#additional-owners-container .dynamic-field-group').each(function(index) {
            $(this).find('h4').first().html(`Additional Owner #${index + 1} <button type="button" class="btn btn-danger btn-sm float-right remove-dynamic-group">Remove</button>`);
        });
    });


    // Function to add a new additional owner group
    $('#add-additional-owner').click(function() {
        var ownerCount = $('#additional-owners-container .dynamic-field-group').length;
        var newOwnerHtml = `
            <div class="dynamic-field-group">
                <h4>Additional Property Owner #${ownerCount + 1} <button type="button" class="btn btn-danger btn-sm float-right remove-dynamic-group">Remove</button></h4>
                <div class="form-group">
                    <label for="p_additional_owner_names_${ownerCount}">Full Name / Company Name</label>
                    <input class="form-control" type="text" name="p_additional_owner_names[]" id="p_additional_owner_names_${ownerCount}" required>
                </div>
                <div class="form-group">
                    <label for="p_additional_owner_streets_${ownerCount}">Street Address</label>
                    <input class="form-control" type="text" name="p_additional_owner_streets[]" id="p_additional_owner_streets_${ownerCount}">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="p_additional_owner_cities_${ownerCount}">City</label>
                        <input class="form-control" type="text" name="p_additional_owner_cities[]" id="p_additional_owner_cities_${ownerCount}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="p_additional_owner_states_${ownerCount}">State</label>
                        <select class="form-control" name="p_additional_owner_states[]" id="p_additional_owner_states_${ownerCount}">
                            <option value="">Select State</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="p_additional_owner_zip_codes_${ownerCount}">ZIP Code</label>
                        <input class="form-control" type="text" name="p_additional_owner_zip_codes[]" id="p_additional_owner_zip_codes_${ownerCount}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="p_additional_owner_other_addresses_${ownerCount}">Other Address Details (optional)</label>
                    <input class="form-control" type="text" name="p_additional_owner_other_addresses[]" id="p_additional_owner_other_addresses_${ownerCount}">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="p_additional_owner_phones_${ownerCount}">Phone Number</label>
                        <input class="form-control" type="tel" name="p_additional_owner_phones[]" id="p_additional_owner_phones_${ownerCount}">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="p_additional_owner_cells_${ownerCount}">Cell Phone</label>
                        <input class="form-control" type="tel" name="p_additional_owner_cells[]" id="p_additional_owner_cells_${ownerCount}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="p_additional_owner_emails_${ownerCount}">Email</label>
                    <input class="form-control" type="email" name="p_additional_owner_emails[]" id="p_additional_owner_emails_${ownerCount}">
                </div>
            </div>
        `;
        $('#additional-owners-container').append(newOwnerHtml);
    });
});
</script>
</body>
</html>