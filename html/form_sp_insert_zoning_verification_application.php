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
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form fields
    $p_form_datetime_resolved = isset($_POST['p_form_datetime_resolved']) && $_POST['p_form_datetime_resolved'] !== '' ? $_POST['p_form_datetime_resolved'] : null;
    $p_form_paid_bool = 0;
    $p_correction_form_id = isset($_POST['p_correction_form_id']) && $_POST['p_correction_form_id'] !== '' ? $_POST['p_correction_form_id'] : null;
    $p_zva_letter_content = isset($_POST['p_zva_letter_content']) && $_POST['p_zva_letter_content'] !== '' ? $_POST['p_zva_letter_content'] : null;
    
    // Zoning address fields
    $p_zva_zoning_letter_street = isset($_POST['p_zva_zoning_letter_street']) && $_POST['p_zva_zoning_letter_street'] !== '' ? $_POST['p_zva_zoning_letter_street'] : null;
    $p_zva_zoning_letter_city = isset($_POST['p_zva_zoning_letter_city']) && $_POST['p_zva_zoning_letter_city'] !== '' ? $_POST['p_zva_zoning_letter_city'] : null;
    $p_zva_state_code = isset($_POST['p_zva_state_code']) && $_POST['p_zva_state_code'] !== '' ? $_POST['p_zva_state_code'] : null;
    $p_zva_zoning_letter_zip = isset($_POST['p_zva_zoning_letter_zip']) && $_POST['p_zva_zoning_letter_zip'] !== '' ? $_POST['p_zva_zoning_letter_zip'] : null;
    
    // Property address fields
    $p_zva_property_street = isset($_POST['p_zva_property_street']) && $_POST['p_zva_property_street'] !== '' ? $_POST['p_zva_property_street'] : null;
    $p_property_city = isset($_POST['p_property_city']) && $_POST['p_property_city'] !== '' ? $_POST['p_property_city'] : null;
    $p_zva_property_state_code = isset($_POST['p_zva_property_state_code']) && $_POST['p_zva_property_state_code'] !== '' ? $_POST['p_zva_property_state_code'] : null;
    $p_zva_property_zip = isset($_POST['p_zva_property_zip']) && $_POST['p_zva_property_zip'] !== '' ? $_POST['p_zva_property_zip'] : null;
    
    // Applicant fields
    $p_zva_applicant_first_name = isset($_POST['p_zva_applicant_first_name']) && $_POST['p_zva_applicant_first_name'] !== '' ? $_POST['p_zva_applicant_first_name'] : null;
    $p_zva_applicant_last_name = isset($_POST['p_zva_applicant_last_name']) && $_POST['p_zva_applicant_last_name'] !== '' ? $_POST['p_zva_applicant_last_name'] : null;
    $p_zva_applicant_street = isset($_POST['p_zva_applicant_street']) && $_POST['p_zva_applicant_street'] !== '' ? $_POST['p_zva_applicant_street'] : null;
    $p_zva_applicant_city = isset($_POST['p_zva_applicant_city']) && $_POST['p_zva_applicant_city'] !== '' ? $_POST['p_zva_applicant_city'] : null;
    $p_zva_applicant_state_code = isset($_POST['p_zva_applicant_state_code']) && $_POST['p_zva_applicant_state_code'] !== '' ? $_POST['p_zva_applicant_state_code'] : null;
    $p_zva_applicant_zip_code = isset($_POST['p_zva_applicant_zip_code']) && $_POST['p_zva_applicant_zip_code'] !== '' ? $_POST['p_zva_applicant_zip_code'] : null;
    $p_zva_applicant_phone_number = isset($_POST['p_zva_applicant_phone_number']) && $_POST['p_zva_applicant_phone_number'] !== '' ? $_POST['p_zva_applicant_phone_number'] : null;
    $p_zva_applicant_fax_number = isset($_POST['p_zva_applicant_fax_number']) && $_POST['p_zva_applicant_fax_number'] !== '' ? $_POST['p_zva_applicant_fax_number'] : null;
    
    // Property owner fields
    $p_zva_owner_first_name = isset($_POST['p_zva_owner_first_name']) && $_POST['p_zva_owner_first_name'] !== '' ? $_POST['p_zva_owner_first_name'] : null;
    $p_zva_owner_last_name = isset($_POST['p_zva_owner_last_name']) && $_POST['p_zva_owner_last_name'] !== '' ? $_POST['p_zva_owner_last_name'] : null;
    $p_zva_owner_street = isset($_POST['p_zva_owner_street']) && $_POST['p_zva_owner_street'] !== '' ? $_POST['p_zva_owner_street'] : null;
    $p_zva_owner_city = isset($_POST['p_zva_owner_city']) && $_POST['p_zva_owner_city'] !== '' ? $_POST['p_zva_owner_city'] : null;
    $p_zva_owner_state_code = isset($_POST['p_zva_owner_state_code']) && $_POST['p_zva_owner_state_code'] !== '' ? $_POST['p_zva_owner_state_code'] : null;
    $p_zva_owner_zip_code = isset($_POST['p_zva_owner_zip_code']) && $_POST['p_zva_owner_zip_code'] !== '' ? $_POST['p_zva_owner_zip_code'] : null;
    
    $sql = "CALL sp_insert_zoning_verification_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siisssssssssssssssssssssss';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_zva_letter_content;
        $bind_names[] = &$p_zva_zoning_letter_street;
        $bind_names[] = &$p_zva_zoning_letter_city;
        $bind_names[] = &$p_zva_state_code;
        $bind_names[] = &$p_zva_zoning_letter_zip;
        $bind_names[] = &$p_zva_property_street;
        $bind_names[] = &$p_property_city;
        $bind_names[] = &$p_zva_property_state_code;
        $bind_names[] = &$p_zva_property_zip;
        $bind_names[] = &$p_zva_applicant_first_name;
        $bind_names[] = &$p_zva_applicant_last_name;
        $bind_names[] = &$p_zva_applicant_street;
        $bind_names[] = &$p_zva_applicant_city;
        $bind_names[] = &$p_zva_applicant_state_code;
        $bind_names[] = &$p_zva_applicant_zip_code;
        $bind_names[] = &$p_zva_applicant_phone_number;
        $bind_names[] = &$p_zva_applicant_fax_number;
        $bind_names[] = &$p_zva_owner_first_name;
        $bind_names[] = &$p_zva_owner_last_name;
        $bind_names[] = &$p_zva_owner_street;
        $bind_names[] = &$p_zva_owner_city;
        $bind_names[] = &$p_zva_owner_state_code;
        $bind_names[] = &$p_zva_owner_zip_code;
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

// Fetch state codes for dropdown
$states_result = $conn->query("SELECT state_code FROM states ORDER BY state_code");
$states = [];
if ($states_result) {
    while ($row = $states_result->fetch_assoc()) {
        $states[] = $row['state_code'];
    }
}
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
    <span class="navbar-brand mb-0 h1">Client Portal — Planning & Zoning</span>
  </div>
</nav>

<div class="container py-4">
  <h1>Zoning Verification Application</h1>
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
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
        <input class="form-control" type="datetime-local" id="p_form_datetime_resolved" name="p_form_datetime_resolved">
        <small class="form-text text-muted">Leave blank if not yet resolved</small>
      </div>
      
      <div class="form-group">
        <label for="p_correction_form_id">Correction Form ID (optional)</label>
        <input class="form-control" type="number" id="p_correction_form_id" name="p_correction_form_id">
        <small class="form-text text-muted">Only if this is a correction of a previous form</small>
      </div>
      
      <div class="form-group">
        <label for="p_zva_letter_content">Letter Content</label>
        <textarea class="form-control" id="p_zva_letter_content" name="p_zva_letter_content" rows="4"></textarea>
      </div>

      <!-- Zoning Letter Address Section -->
      <div class="section-header">Zoning Letter Address</div>
      
      <div class="form-group">
        <label for="p_zva_zoning_letter_street">Street Address</label>
        <input class="form-control" type="text" id="p_zva_zoning_letter_street" name="p_zva_zoning_letter_street">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="p_zva_zoning_letter_city">City</label>
          <input class="form-control" type="text" id="p_zva_zoning_letter_city" name="p_zva_zoning_letter_city">
        </div>
        
        <div class="form-group col-md-3">
          <label for="p_zva_state_code">State</label>
          <select class="form-control" id="p_zva_state_code" name="p_zva_state_code">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group col-md-4">
          <label for="p_zva_zoning_letter_zip">ZIP Code</label>
          <input class="form-control" type="text" id="p_zva_zoning_letter_zip" name="p_zva_zoning_letter_zip">
        </div>
      </div>

      <!-- Property Address Section -->
      <div class="section-header">Property Address</div>
      
      <div class="form-group">
        <label for="p_zva_property_street">Street Address</label>
        <input class="form-control" type="text" id="p_zva_property_street" name="p_zva_property_street">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="p_property_city">City</label>
          <input class="form-control" type="text" id="p_property_city" name="p_property_city">
        </div>
        
        <div class="form-group col-md-3">
          <label for="p_zva_property_state_code">State</label>
          <select class="form-control" id="p_zva_property_state_code" name="p_zva_property_state_code">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group col-md-4">
          <label for="p_zva_property_zip">ZIP Code</label>
          <input class="form-control" type="text" id="p_zva_property_zip" name="p_zva_property_zip">
        </div>
      </div>

      <!-- Applicant Information Section -->
      <div class="section-header">Applicant Information</div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_zva_applicant_first_name">First Name</label>
          <input class="form-control" type="text" id="p_zva_applicant_first_name" name="p_zva_applicant_first_name">
        </div>
        
        <div class="form-group col-md-6">
          <label for="p_zva_applicant_last_name">Last Name</label>
          <input class="form-control" type="text" id="p_zva_applicant_last_name" name="p_zva_applicant_last_name">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_zva_applicant_street">Street Address</label>
        <input class="form-control" type="text" id="p_zva_applicant_street" name="p_zva_applicant_street">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="p_zva_applicant_city">City</label>
          <input class="form-control" type="text" id="p_zva_applicant_city" name="p_zva_applicant_city">
        </div>
        
        <div class="form-group col-md-3">
          <label for="p_zva_applicant_state_code">State</label>
          <select class="form-control" id="p_zva_applicant_state_code" name="p_zva_applicant_state_code">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group col-md-4">
          <label for="p_zva_applicant_zip_code">ZIP Code</label>
          <input class="form-control" type="text" id="p_zva_applicant_zip_code" name="p_zva_applicant_zip_code">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_zva_applicant_phone_number">Phone Number</label>
          <input class="form-control" type="tel" id="p_zva_applicant_phone_number" name="p_zva_applicant_phone_number">
        </div>
        
        <div class="form-group col-md-6">
          <label for="p_zva_applicant_fax_number">Fax Number</label>
          <input class="form-control" type="tel" id="p_zva_applicant_fax_number" name="p_zva_applicant_fax_number">
        </div>
      </div>

      <!-- Property Owner Information Section -->
      <div class="section-header">Property Owner Information</div>
      
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="p_zva_owner_first_name">First Name</label>
          <input class="form-control" type="text" id="p_zva_owner_first_name" name="p_zva_owner_first_name">
        </div>
        
        <div class="form-group col-md-6">
          <label for="p_zva_owner_last_name">Last Name</label>
          <input class="form-control" type="text" id="p_zva_owner_last_name" name="p_zva_owner_last_name">
        </div>
      </div>
      
      <div class="form-group">
        <label for="p_zva_owner_street">Street Address</label>
        <input class="form-control" type="text" id="p_zva_owner_street" name="p_zva_owner_street">
      </div>
      
      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="p_zva_owner_city">City</label>
          <input class="form-control" type="text" id="p_zva_owner_city" name="p_zva_owner_city">
        </div>
        
        <div class="form-group col-md-3">
          <label for="p_zva_owner_state_code">State</label>
          <select class="form-control" id="p_zva_owner_state_code" name="p_zva_owner_state_code">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
              <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group col-md-4">
          <label for="p_zva_owner_zip_code">ZIP Code</label>
          <input class="form-control" type="text" id="p_zva_owner_zip_code" name="p_zva_owner_zip_code">
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