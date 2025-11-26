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
$validationErrors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract and sanitize form data
    $formData = extractZoningFormData($_POST);
    
    // Validate form data
    $validationErrors = validateZoningFormData($formData);
    
    if (empty($validationErrors)) {
        // Insert the application
        $result = insertZoningVerificationApplication($conn, $formData);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Please correct the following errors:';
    }
}

// Fetch state codes for dropdown
$states = fetchStateCodes($conn);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Zoning Verification Application</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body { background: linear-gradient(135deg, #ede7f6, #d1c4e9); }
    .section-header {
      background-color: #6a1b9a;
      color: white;
      padding: 10px;
      margin-top: 20px;
      margin-bottom: 15px;
      border-radius: 5px;
      font-weight: bold;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-dark" style="background-color: #6a1b9a;">
  <div class="container">
    <span class="navbar-brand mb-0 h1">Client Portal – Planning & Zoning</span>
  </div>
</nav>

<div class="container py-4">
  <h1>Zoning Verification Application</h1>
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
  
  <?php if ($error): ?>
    <div class="alert alert-danger">
      <?php echo htmlspecialchars($error); ?>
      <?php if (!empty($validationErrors)): ?>
        <ul class="mb-0 mt-2">
          <?php foreach ($validationErrors as $validationError): ?>
            <li><?php echo htmlspecialchars($validationError); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>
  
  <div class="card p-4 shadow-sm">
    <form method="post">
      
      <!-- Form Information Section -->
      <div class="section-header">Form Information</div>
      
      <div class="form-group">
        <label for="p_form_datetime_resolved">Form Datetime Resolved (optional)</label>
        <input class="form-control" type="datetime-local" id="p_form_datetime_resolved" name="p_form_datetime_resolved" value="<?php echo isset($_POST['p_form_datetime_resolved']) ? htmlspecialchars($_POST['p_form_datetime_resolved']) : ''; ?>">
        <small class="form-text text-muted">Leave blank if not yet resolved</small>
      </div>
      
      <div class="form-group">
        <label for="p_correction_form_id">Correction Form ID (optional)</label>
        <input class="form-control" type="number" id="p_correction_form_id" name="p_correction_form_id" value="<?php echo isset($_POST['p_correction_form_id']) ? htmlspecialchars($_POST['p_correction_form_id']) : ''; ?>">
        <small class="form-text text-muted">Only if this is a correction of a previous form</small>
      </div>
      
      <div class="form-group">
        <label for="p_zva_letter_content">Letter Content</label>
        <textarea class="form-control" id="p_zva_letter_content" name="p_zva_letter_content" rows="4"><?php echo isset($_POST['p_zva_letter_content']) ? htmlspecialchars($_POST['p_zva_letter_content']) : ''; ?></textarea>
      </div>

      <!-- Zoning Letter Address Section -->
      <div class="section-header">Zoning Letter Address</div>
      
      <div class="form-group">
        <label for="p_zva_zoning_letter_street">Street Address</label>
        <input class="form-control" type="text" id="p_zva_zoning_letter_street" name="p_zva_zoning_letter_street" value="<?php echo isset($_POST['p_zva_zoning_letter_street']) ? htmlspecialchars($_POST['p_zva_zoning_letter_street']) : ''; ?>">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="p_zva_zoning_letter_city">City</label>
          <input class="form-control" type="text" id="p_zva_zoning_letter_city" name="p_zva_zoning_letter_city" value="<?php echo isset($_POST['p_zva_zoning_letter_city']) ? htmlspecialchars($_POST['p_zva_zoning_letter_city']) : ''; ?>">
        </div>
        
        <div class="form-group col-md-3">
          <label for="p_zva_state_code">State</label>
          <select class="form-control" id="p_zva_state_code" name="p_zva_state_code">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>" <?php echo (isset($_POST['p_zva_state_code']) && $_POST['p_zva_state_code'] === $state) ? 'selected' : ''; ?>><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group col-md-4">
          <label for="p_zva_zoning_letter_zip">ZIP Code</label>
          <input class="form-control" type="text" id="p_zva_zoning_letter_zip" name="p_zva_zoning_letter_zip" value="<?php echo isset($_POST['p_zva_zoning_letter_zip']) ? htmlspecialchars($_POST['p_zva_zoning_letter_zip']) : ''; ?>">
        </div>
      </div>

      <!-- Property Address Section -->
      <div class="section-header">Property Address <span class="text-danger">*</span></div>
      
      <div class="form-group">
        <label for="p_zva_property_street">Street Address <span class="text-danger">*</span></label>
        <input class="form-control" type="text" id="p_zva_property_street" name="p_zva_property_street" required value="<?php echo isset($_POST['p_zva_property_street']) ? htmlspecialchars($_POST['p_zva_property_street']) : ''; ?>">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="p_property_city">City <span class="text-danger">*</span></label>
          <input class="form-control" type="text" id="p_property_city" name="p_property_city" required value="<?php echo isset($_POST['p_property_city']) ? htmlspecialchars($_POST['p_property_city']) : ''; ?>">
        </div>
        
        <div class="form-group col-md-3">
          <label for="p_zva_property_state_code">State <span class="text-danger">*</span></label>
          <select class="form-control" id="p_zva_property_state_code" name="p_zva_property_state_code" required>
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>" <?php echo (isset($_POST['p_zva_property_state_code']) && $_POST['p_zva_property_state_code'] === $state) ? 'selected' : ''; ?>><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group col-md-4">
          <label for="p_zva_property_zip">ZIP Code <span class="text-danger">*</span></label>
          <input class="form-control" type="text" id="p_zva_property_zip" name="p_zva_property_zip" required pattern="\d{5}(-\d{4})?" value="<?php echo isset($_POST['p_zva_property_zip']) ? htmlspecialchars($_POST['p_zva_property_zip']) : ''; ?>">
        </div>
      </div>

      <!-- Applicant Information Section -->
      <div class="section-header">Applicant Information</div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_zva_applicant_first_name">First Name</label>
          <input class="form-control" type="text" id="p_zva_applicant_first_name" name="p_zva_applicant_first_name" value="<?php echo isset($_POST['p_zva_applicant_first_name']) ? htmlspecialchars($_POST['p_zva_applicant_first_name']) : ''; ?>">
        </div>
        
        <div class="form-group col-md-6">
          <label for="p_zva_applicant_last_name">Last Name</label>
          <input class="form-control" type="text" id="p_zva_applicant_last_name" name="p_zva_applicant_last_name" value="<?php echo isset($_POST['p_zva_applicant_last_name']) ? htmlspecialchars($_POST['p_zva_applicant_last_name']) : ''; ?>">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_zva_applicant_street">Street Address</label>
        <input class="form-control" type="text" id="p_zva_applicant_street" name="p_zva_applicant_street" value="<?php echo isset($_POST['p_zva_applicant_street']) ? htmlspecialchars($_POST['p_zva_applicant_street']) : ''; ?>">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="p_zva_applicant_city">City</label>
          <input class="form-control" type="text" id="p_zva_applicant_city" name="p_zva_applicant_city" value="<?php echo isset($_POST['p_zva_applicant_city']) ? htmlspecialchars($_POST['p_zva_applicant_city']) : ''; ?>">
        </div>
        
        <div class="form-group col-md-3">
          <label for="p_zva_applicant_state_code">State</label>
          <select class="form-control" id="p_zva_applicant_state_code" name="p_zva_applicant_state_code">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>" <?php echo (isset($_POST['p_zva_applicant_state_code']) && $_POST['p_zva_applicant_state_code'] === $state) ? 'selected' : ''; ?>><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group col-md-4">
          <label for="p_zva_applicant_zip_code">ZIP Code</label>
          <input class="form-control" type="text" id="p_zva_applicant_zip_code" name="p_zva_applicant_zip_code" pattern="\d{5}(-\d{4})?" value="<?php echo isset($_POST['p_zva_applicant_zip_code']) ? htmlspecialchars($_POST['p_zva_applicant_zip_code']) : ''; ?>">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_zva_applicant_phone_number">Phone Number</label>
          <input class="form-control" type="tel" id="p_zva_applicant_phone_number" name="p_zva_applicant_phone_number" value="<?php echo isset($_POST['p_zva_applicant_phone_number']) ? htmlspecialchars($_POST['p_zva_applicant_phone_number']) : ''; ?>">
        </div>
        
        <div class="form-group col-md-6">
          <label for="p_zva_applicant_fax_number">Fax Number</label>
          <input class="form-control" type="tel" id="p_zva_applicant_fax_number" name="p_zva_applicant_fax_number" value="<?php echo isset($_POST['p_zva_applicant_fax_number']) ? htmlspecialchars($_POST['p_zva_applicant_fax_number']) : ''; ?>">
        </div>
      </div>

      <!-- Property Owner Information Section -->
      <div class="section-header">Property Owner Information</div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_zva_owner_first_name">First Name</label>
          <input class="form-control" type="text" id="p_zva_owner_first_name" name="p_zva_owner_first_name" value="<?php echo isset($_POST['p_zva_owner_first_name']) ? htmlspecialchars($_POST['p_zva_owner_first_name']) : ''; ?>">
        </div>
        
        <div class="form-group col-md-6">
          <label for="p_zva_owner_last_name">Last Name</label>
          <input class="form-control" type="text" id="p_zva_owner_last_name" name="p_zva_owner_last_name" value="<?php echo isset($_POST['p_zva_owner_last_name']) ? htmlspecialchars($_POST['p_zva_owner_last_name']) : ''; ?>">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_zva_owner_street">Street Address</label>
        <input class="form-control" type="text" id="p_zva_owner_street" name="p_zva_owner_street" value="<?php echo isset($_POST['p_zva_owner_street']) ? htmlspecialchars($_POST['p_zva_owner_street']) : ''; ?>">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="p_zva_owner_city">City</label>
          <input class="form-control" type="text" id="p_zva_owner_city" name="p_zva_owner_city" value="<?php echo isset($_POST['p_zva_owner_city']) ? htmlspecialchars($_POST['p_zva_owner_city']) : ''; ?>">
        </div>
        
        <div class="form-group col-md-3">
          <label for="p_zva_owner_state_code">State</label>
          <select class="form-control" id="p_zva_owner_state_code" name="p_zva_owner_state_code">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>" <?php echo (isset($_POST['p_zva_owner_state_code']) && $_POST['p_zva_owner_state_code'] === $state) ? 'selected' : ''; ?>><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group col-md-4">
          <label for="p_zva_owner_zip_code">ZIP Code</label>
          <input class="form-control" type="text" id="p_zva_owner_zip_code" name="p_zva_owner_zip_code" pattern="\d{5}(-\d{4})?" value="<?php echo isset($_POST['p_zva_owner_zip_code']) ? htmlspecialchars($_POST['p_zva_owner_zip_code']) : ''; ?>">
        </div>
      </div>

      <div class="form-group mt-4">
        <button class="btn btn-primary btn-lg" type="submit">Submit Application</button>
        <a href="client_new_form.php" class="btn btn-secondary btn-lg ml-2">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>