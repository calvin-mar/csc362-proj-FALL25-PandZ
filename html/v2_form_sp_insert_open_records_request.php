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
        $formData = extractOpenRecordsRequestFormData($_POST);
        
        // Validate form data
        $errors = validateOpenRecordsRequestData($formData);
        
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            // Insert the request
            $result = insertOpenRecordsRequest($conn, $formData);
            
            if ($result['success']) {
                $form_id = $result['form_id'];
                
                // Link form to client if form_id was returned
                if ($form_id) {
                    $linkResult = linkFormToClient($conn, $form_id, $client_id);
                    
                    if ($linkResult['success']) {
                        $success = 'Open records request submitted successfully!';
                    } else {
                        $error = $linkResult['message'];
                    }
                } else {
                    $success = 'Open records request submitted successfully!';
                }
            } else {
                $error = $result['message'];
            }
        }
    } catch (Exception $e) {
        error_log("Error in open records request form: " . $e->getMessage());
        $error = 'An error occurred while processing your request. Please try again.';
    }
}

// Fetch state codes for dropdown
$states = fetchStateCodes($conn);
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
    <div class="alert alert-danger"><?php echo $error; ?></div>
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
      <label>Is the information requested to be used for commercial purpose? *</label>
      <div class="form-check form-check-inline ml-3">
        <input class="form-check-input" type="radio" name="p_orr_commercial_purpose" id="commercial_yes" value="YES">
        <label class="form-check-label" for="commercial_yes">
          YES
        </label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="p_orr_commercial_purpose" id="commercial_no" value="NO" checked>
        <label class="form-check-label" for="commercial_no">
          NO
        </label>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="form-group">
          <label>NAME (Full Name): *</label>
          <input type="text" class="form-control" name="p_orr_applicant_name" required>
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
          <select class="form-control" name="p_orr_state_code">
            <option value="">State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>" <?php echo $state === 'KY' ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($state); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <input type="text" class="form-control" name="p_orr_applicant_zip_code" placeholder="ZIP Code">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>REQUESTS TO INSPECT THE FOLLOWING PUBLIC RECORDS (Please Specify by Records Name): *</label>
      <textarea class="form-control" name="p_orr_records_requested" rows="4" required></textarea>
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
        <input class="form-check-input" type="radio" name="p_orr_request_for_copies" id="copies_no" value="NO" checked>
        <label class="form-check-label" for="copies_no">
          No
        </label>
      </div>
    </div>

    <div class="form-group">
      <label>SIGNATURE OF PERSON MAKING REQUEST:</label>
      <div class="signature-line"></div>
      <p class="info-text" style="font-size: 12px; color: #666; font-style: italic;">Digital signature will be recorded upon submission</p>
    </div>

    <div class="divider-line"></div>

    <div class="form-group mt-4">
      <button class="btn btn-primary btn-lg btn-block" type="submit">Submit Request</button>
    </div>
  </form>

  <div class="info-section">
    <h3>Information:</h3>
    <p>Pursuant to KRS 61.870 to 61.884, this agency shall provide access to public records during regular office hours. Exceptions include records containing information of a personal nature where public disclosure thereof would constitute a clearly unwarranted invasion of personal privacy.</p>
    <p>Copies of records may be provided at a reasonable fee. The fee shall not exceed the actual cost of reproduction, including the costs of staff required to make the records available.</p>
  </div>

</div>

</body>
</html>