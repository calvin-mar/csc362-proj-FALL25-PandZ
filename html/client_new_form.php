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
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>New Form Selector</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>body { background: linear-gradient(135deg, #ede7f6, #d1c4e9); }</style>
</head>
<body>
<nav class="navbar navbar-dark" style="background-color: #6a1b9a;">
  <div class="container">
    <span class="navbar-brand mb-0 h1">Client Portal – Planning & Zoning</span>
  </div>
</nav>

<div class="container py-4">
  <h1>Create New Form</h1>
  <p>Select which form you want to fill and submit. This hub will redirect to the individual form page.</p>
  <div class="card p-4 shadow-sm">
  <form method="get" action="">
    <div class="form-group">
      <label for="formfile">Form type</label>
      <select id="formfile" name="file" class="form-control">
        <option value="">-- Select a form --</option>
<option value="form_sp_insert_administrative_appeal_request.php">Administrative Appeal Request</option>
<option value="form_sp_insert_adjacent_property_owners_form.php">Adjacent Property Owners Form</option>
<option value="form_sp_insert_conditional_use_permit_application.php">Conditional Use Permit Application</option>
<option value="form_sp_insert_general_development_plan_application.php">General Development Plan Application</option>
<option value="form_sp_insert_site_development_plan_application.php">Site Development Plan Application</option>
<option value="form_sp_insert_future_land_use_map_application.php">Future Land Use Map Application</option>
<option value="form_sp_insert_open_records_request.php">Open Records Request</option>
<option value="form_sp_insert_sign_permit_application.php">Sign Permit Application</option>
<option value="form_sp_insert_major_subdivision_plat_application.php">Major Subdivision Plat Application</option>
<option value="form_sp_insert_minor_subdivision_plat_application.php">Minor Subdivision Plat Application</option>
<option value="form_sp_insert_variance_application.php">Variance Application</option>
<option value="form_sp_insert_zoning_map_amendment_application.php">Zoning Map Amendment Application</option>
<option value="form_sp_insert_zoning_permit_application.php">Zoning Permit Application</option>
<option value="form_sp_insert_zoning_verification_application.php">Zoning Verification Application</option>

      </select>
    </div>
    <button class="btn btn-primary" type="submit">Open form</button>
  </form>
  </div>
  <?php
  if (!empty($_GET['file'])) {
    $f = basename($_GET['file']);
    header('Location: ' . $f);
    exit;
  }
  ?>
</div>
</body>
</html>
