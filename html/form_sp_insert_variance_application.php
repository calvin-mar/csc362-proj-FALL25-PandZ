<?php
// Show all errors from the PHP interpreter.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Show all errors from the MySQLi Extension.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'config.php';
require_once 'zoning_functions.php';

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
    $result = processZoningVerificationLetter($_POST, $_FILES, $conn);
    
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Zoning Verification Letter</title>
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
    .fee-notice {
      background: #fff3cd;
      border: 1px solid #ffc107;
      padding: 10px 15px;
      margin: 15px 0;
      border-radius: 4px;
      font-size: 14px;
      font-weight: bold;
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
    <h1>Zoning Verification Letter</h1>
    <h2>Danville-Boyle County Planning and Zoning Commission</h2>
  </div>
  
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>

  <form method="post" enctype="multipart/form-data">
    
    <!-- PROPERTY OWNER INFORMATION -->
    <div class="section-title">Property Owner Information</div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Property Owner:</label>
          <input type="text" class="form-control" name="property_owner" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Address:</label>
          <input type="text" class="form-control" name="property_owner_address">
        </div>
      </div>
    </div>

    <!-- APPLICANT INFORMATION -->
    <div class="section-title">Applicant Information</div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Applicant:</label>
          <input type="text" class="form-control" name="applicant" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Address:</label>
          <input type="text" class="form-control" name="applicant_address">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Applicant Phone Number:</label>
          <input type="text" class="form-control" name="applicant_phone">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Fax Number:</label>
          <input type="text" class="form-control" name="applicant_fax">
        </div>
      </div>
    </div>

    <!-- PROPERTY INFORMATION -->
    <div class="section-title">Property Information</div>

    <div class="form-group">
      <label>Physical Address of Property:</label>
      <input type="text" class="form-control" name="physical_address_line1" required>
    </div>

    <div class="form-group">
      <label>Additional Address Information (if needed):</label>
      <input type="text" class="form-control" name="physical_address_line2">
    </div>

    <!-- INFORMATION NEEDED -->
    <div class="section-title">Information Needed in Letter</div>

    <div class="form-group">
      <label>Information needed in content of letter:</label>
      <p class="info-text">Please describe what specific zoning information you need verified (e.g., current zoning classification, permitted uses, setback requirements, etc.)</p>
      <textarea class="form-control" name="information_needed" rows="4" required></textarea>
    </div>

    <!-- MAILING INFORMATION -->
    <div class="section-title">Mail Zoning Letter To</div>

    <div class="form-group">
      <label>Name:</label>
      <input type="text" class="form-control" name="mail_to_name">
    </div>

    <div class="form-group">
      <label>Street Address:</label>
      <input type="text" class="form-control" name="mail_to_street">
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
          <label>City:</label>
          <input type="text" class="form-control" name="mail_to_city">
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>State:</label>
          <input type="text" class="form-control" name="mail_to_state" maxlength="2">
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Zip Code:</label>
          <input type="text" class="form-control" name="mail_to_zip">
        </div>
      </div>
    </div>

    <!-- APPLICANT SIGNATURE -->
    <div class="section-title">Applicant Signature</div>

    <div class="row">
      <div class="col-md-8">
        <div class="form-group">
          <label>Applicant Signature:</label>
          <div class="signature-line"></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Date:</label>
          <input type="text" class="form-control" name="applicant_signature_date" value="<?php echo date('Y-m-d'); ?>">
        </div>
      </div>
    </div>

    <!-- FEE INFORMATION -->
    <div class="fee-notice">
      Fee: $20.00 (payable to Danville-Boyle County Planning & Zoning Commission)
    </div>

    <!-- ADMIN SECTION -->
    <div class="section-title" style="background: #d0d0d0;">ADMIN SECTION</div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Fee Amount:</label>
          <input type="text" class="form-control" name="fee_amount" value="$20.00" readonly>
        </div>
      </div>
      <div class="col-md-6">
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

    <div class="form-group">
      <label>Admin Notes:</label>
      <textarea class="form-control" name="admin_notes" rows="3"></textarea>
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