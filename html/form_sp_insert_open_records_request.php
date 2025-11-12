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
    $p_form_datetime_resolved = isset($_POST['p_form_datetime_resolved']) && $_POST['p_form_datetime_resolved'] !== '' ? $_POST['p_form_datetime_resolved'] : null;
    $p_form_paid_bool = 0;
    $p_correction_form_id = null; // Added: missing parameter
    $p_orr_commercial_purpose = isset($_POST['p_orr_commercial_purpose']) && $_POST['p_orr_commercial_purpose'] !== '' ? $_POST['p_orr_commercial_purpose'] : null;
    $p_orr_request_for_copies = isset($_POST['p_orr_request_for_copies']) && $_POST['p_orr_request_for_copies'] !== '' ? $_POST['p_orr_request_for_copies'] : null;
    $p_orr_received_on_datetime = isset($_POST['p_orr_received_on_datetime']) && $_POST['p_orr_received_on_datetime'] !== '' ? $_POST['p_orr_received_on_datetime'] : null;
    $p_orr_receivable_datetime = isset($_POST['p_orr_receivable_datetime']) && $_POST['p_orr_receivable_datetime'] !== '' ? $_POST['p_orr_receivable_datetime'] : null;
    $p_orr_denied_reasons = isset($_POST['p_orr_denied_reasons']) && $_POST['p_orr_denied_reasons'] !== '' ? $_POST['p_orr_denied_reasons'] : null;
    
    // Parse name into first and last name
    $p_orr_applicant_name = isset($_POST['p_orr_applicant_name']) && $_POST['p_orr_applicant_name'] !== '' ? $_POST['p_orr_applicant_name'] : '';
    if ($p_orr_applicant_name !== '') {
        $name_parts = explode(' ', trim($p_orr_applicant_name), 2);
        $p_orr_applicant_first_name = $name_parts[0];
        $p_orr_applicant_last_name = isset($name_parts[1]) ? $name_parts[1] : '';
    } else {
        $p_orr_applicant_first_name = null;
        $p_orr_applicant_last_name = null;
    }
    
    $p_orr_applicant_telephone = isset($_POST['p_orr_applicant_telephone']) && $_POST['p_orr_applicant_telephone'] !== '' ? $_POST['p_orr_applicant_telephone'] : null;
    $p_orr_applicant_street = isset($_POST['p_orr_applicant_street']) && $_POST['p_orr_applicant_street'] !== '' ? $_POST['p_orr_applicant_street'] : null;
    $p_orr_applicant_city = isset($_POST['p_orr_applicant_city']) && $_POST['p_orr_applicant_city'] !== '' ? $_POST['p_orr_applicant_city'] : null;
    $p_orr_state_code = isset($_POST['p_orr_state_code']) && $_POST['p_orr_state_code'] !== '' ? $_POST['p_orr_state_code'] : null;
    $p_orr_applicant_zip_code = isset($_POST['p_orr_applicant_zip_code']) && $_POST['p_orr_applicant_zip_code'] !== '' ? $_POST['p_orr_applicant_zip_code'] : null;
    $p_orr_records_requested = isset($_POST['p_orr_records_requested']) && $_POST['p_orr_records_requested'] !== '' ? $_POST['p_orr_records_requested'] : null;
    
    // Fixed: Now calling with 18 parameters matching the stored procedure
    $sql = "CALL sp_insert_open_records_request(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siissssssssssssss'; // Updated: 18 parameters (s=string, i=integer)
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id; // Added
        $bind_names[] = &$p_orr_commercial_purpose;
        $bind_names[] = &$p_orr_request_for_copies;
        $bind_names[] = &$p_orr_received_on_datetime;
        $bind_names[] = &$p_orr_receivable_datetime;
        $bind_names[] = &$p_orr_denied_reasons;
        $bind_names[] = &$p_orr_applicant_first_name; // Changed: split name
        $bind_names[] = &$p_orr_applicant_last_name;  // Changed: split name
        $bind_names[] = &$p_orr_applicant_telephone;
        $bind_names[] = &$p_orr_applicant_street;
        $bind_names[] = &$p_orr_applicant_city;
        $bind_names[] = &$p_orr_state_code;
        $bind_names[] = &$p_orr_applicant_zip_code;
        $bind_names[] = &$p_orr_records_requested; // Changed: renamed to match SP
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
  <title>Public Records Inspection Request</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body { 
      background: #f5f5f5; 
      font-family: Arial, sans-serif;
    }
    .form-container {
      background: white;
      max-width: 800px;
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
      font-size: 16px;
      font-weight: bold;
      margin: 0;
    }
    .form-header p {
      font-size: 13px;
      margin: 2px 0;
    }
    .form-header .divider {
      border-bottom: 1px solid #666;
      margin: 10px 0;
    }
    .section-title {
      font-weight: bold;
      font-size: 15px;
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
    .signature-line {
      border-bottom: 1px solid #333;
      min-height: 40px;
      margin: 10px 0;
    }
    .info-section {
      background: #f9f9f9;
      border: 1px solid #ddd;
      padding: 20px;
      margin-top: 30px;
    }
    .info-section h3 {
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 15px;
      text-transform: uppercase;
    }
    .info-section p {
      font-size: 13px;
      margin: 8px 0;
      line-height: 1.6;
    }
    .divider-line {
      border-top: 2px solid #333;
      margin: 25px 0;
    }
    .inline-field {
      display: inline-block;
      border-bottom: 1px solid #333;
      min-width: 100px;
      padding: 0 5px;
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
    <p>445 West Main Street P.O. Box 670</p>
    <p>Danville, Kentucky 40423</p>
    <div class="divider"></div>
    <p>Phone: 859.238.1235</p>
    <p>www.boyleplanning.org</p>
  </div>
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
  <h2 class="text-center mb-4"><strong>PUBLIC RECORDS INSPECTION REQUEST</strong></h2>
  
  <form method="post">
    <!-- REQUEST SECTION -->
    <div class="section-title">1) REQUEST:</div>

    <div class="form-group">
      <label>Is the information requested to be used for commercial purpose?</label>
      <div class="form-check form-check-inline ml-3">
        <input class="form-check-input" type="radio" name="p_orr_commercial_purpose" id="commercial_yes" value="YES">
        <label class="form-check-label" for="commercial_yes">
          YES
        </label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="p_orr_commercial_purpose" id="commercial_no" value="NO">
        <label class="form-check-label" for="commercial_no">
          NO
        </label>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="form-group">
          <label>NAME:</label>
          <input type="text" class="form-control" name="p_orr_applicant_name">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>TELEPHONE:</label>
          <input type="text" class="form-control" name="p_orr_applicant_telephone">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>ADDRESS:</label>
      <input type="text" class="form-control" name="p_orr_applicant_street" placeholder="Street">
    </div>

    <div class="row">
      <div class="col-md-5">
        <div class="form-group">
          <input type="text" class="form-control" name="p_orr_applicant_city" placeholder="City">
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <input type="text" class="form-control" name="p_orr_state_code" placeholder="State">
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <input type="text" class="form-control" name="p_orr_applicant_zip_code" placeholder="Zip Code">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>REQUESTS TO INSPECT THE FOLLOWING PUBLIC RECORDS (Please Specify by Records Name)</label>
      <textarea class="form-control" name="p_orr_records_requested" rows="4"></textarea>
    </div>

    <div class="form-group">
      <label><strong>Request for copies:</strong></label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="p_orr_request_for_copies" id="copies_yes" value="YES">
        <label class="form-check-label" for="copies_yes">
          Yes, I agree in advance to pay for copies of the request
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="p_orr_request_for_copies" id="copies_no" value="NO">
        <label class="form-check-label" for="copies_no">
          No
        </label>
      </div>
    </div>

    <div class="form-group">
      <label>SIGNATURE OF PERSON MAKING REQUEST:</label>
      <div class="signature-line"></div>
    </div>

    <div class="divider-line"></div>

    <div class="form-group mt-4">
      <button class="btn btn-primary btn-lg btn-block" type="submit">Submit Request</button>
    </div>
  </form>

</div>

</body>
</html>