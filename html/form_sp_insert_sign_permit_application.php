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
    $p_form_datetime_resolved = isset($_POST['p_form_datetime_resolved']) && $_POST['p_form_datetime_resolved'] !== '' ? $_POST['p_form_datetime_resolved'] : null;
    $p_form_paid_bool = 0;
    $p_correction_form_id = isset($_POST['p_correction_form_id']) && $_POST['p_correction_form_id'] !== '' ? $_POST['p_correction_form_id'] : null;
    $p_sp_permit_number = isset($_POST['p_sp_permit_number']) && $_POST['p_sp_permit_number'] !== '' ? $_POST['p_sp_permit_number'] : null;
    $p_sp_building_coverage_percent = isset($_POST['p_sp_building_coverage_percent']) && $_POST['p_sp_building_coverage_percent'] !== '' ? $_POST['p_sp_building_coverage_percent'] : null;
    $p_sp_permit_fee = isset($_POST['p_sp_permit_fee']) && $_POST['p_sp_permit_fee'] !== '' ? $_POST['p_sp_permit_fee'] : null;
    $p_sp_owner_last_name = isset($_POST['p_sp_owner_last_name']) && $_POST['p_sp_owner_last_name'] !== '' ? $_POST['p_sp_owner_last_name'] : null;
    $p_sp_owner_street = isset($_POST['p_sp_owner_street']) && $_POST['p_sp_owner_street'] !== '' ? $_POST['p_sp_owner_street'] : null;
    $p_sp_owner_city = isset($_POST['p_sp_owner_city']) && $_POST['p_sp_owner_city'] !== '' ? $_POST['p_sp_owner_city'] : null;
    $p_sp_owner_state_code = isset($_POST['p_sp_owner_state_code']) && $_POST['p_sp_owner_state_code'] !== '' ? $_POST['p_sp_owner_state_code'] : null;
    $p_sp_owner_zip_code = isset($_POST['p_sp_owner_zip_code']) && $_POST['p_sp_owner_zip_code'] !== '' ? $_POST['p_sp_owner_zip_code'] : null;
    $p_sp_business_street = isset($_POST['p_sp_business_street']) && $_POST['p_sp_business_street'] !== '' ? $_POST['p_sp_business_street'] : null;
    $p_sp_business_city = isset($_POST['p_sp_business_city']) && $_POST['p_sp_business_city'] !== '' ? $_POST['p_sp_business_city'] : null;
    $p_sp_business_state_code = isset($_POST['p_sp_business_state_code']) && $_POST['p_sp_business_state_code'] !== '' ? $_POST['p_sp_business_state_code'] : null;
    $p_sp_business_zip_code = isset($_POST['p_sp_business_zip_code']) && $_POST['p_sp_business_zip_code'] !== '' ? $_POST['p_sp_business_zip_code'] : null;
    $p_sp_contractor_last_name = isset($_POST['p_sp_contractor_last_name']) && $_POST['p_sp_contractor_last_name'] !== '' ? $_POST['p_sp_contractor_last_name'] : null;
    $p_sp_contractor_phone_number = isset($_POST['p_sp_contractor_phone_number']) && $_POST['p_sp_contractor_phone_number'] !== '' ? $_POST['p_sp_contractor_phone_number'] : null;
    $p_sign_square_footage = isset($_POST['p_sign_square_footage']) && $_POST['p_sign_square_footage'] !== '' ? $_POST['p_sign_square_footage'] : null;
    $p_lettering_height = isset($_POST['p_lettering_height']) && $_POST['p_lettering_height'] !== '' ? $_POST['p_lettering_height'] : null;
    $sql = "CALL sp_insert_sign_permit_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siissssssssssssssds';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_sp_permit_number;
        $bind_names[] = &$p_sp_building_coverage_percent;
        $bind_names[] = &$p_sp_permit_fee;
        $bind_names[] = &$p_sp_owner_last_name;
        $bind_names[] = &$p_sp_owner_street;
        $bind_names[] = &$p_sp_owner_city;
        $bind_names[] = &$p_sp_owner_state_code;
        $bind_names[] = &$p_sp_owner_zip_code;
        $bind_names[] = &$p_sp_business_street;
        $bind_names[] = &$p_sp_business_city;
        $bind_names[] = &$p_sp_business_state_code;
        $bind_names[] = &$p_sp_business_zip_code;
        $bind_names[] = &$p_sp_contractor_last_name;
        $bind_names[] = &$p_sp_contractor_phone_number;
        $bind_names[] = &$p_sign_square_footage;
        $bind_names[] = &$p_lettering_height;
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
  <title>Sign Permit Application</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>body { background: linear-gradient(135deg, #ede7f6, #d1c4e9); }</style>
</head>
<body>
<nav class="navbar navbar-dark" style="background-color: #6a1b9a;">
  <div class="container">
    <span class="navbar-brand mb-0 h1">Client Portal – Planning & Zoning</span>
  </div>
</nav>
<p><a href="client_new_form.php">&larr; Back to form selector</a></p>
<div class="container py-4">
  <h1>Sign Permit Application</h1>
  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>
  <div class="card p-4 shadow-sm">
  <form method="post">
<div class="form-group">
    <label for="p_form_datetime_resolved">Form Datetime Resolved</label>
    <input class="form-control" type="text" id="p_form_datetime_resolved" name="p_form_datetime_resolved">
</div>
<div class="form-group">
    <label for="p_correction_form_id">Correction Form Id</label>
    <input class="form-control" type="number" id="p_correction_form_id" name="p_correction_form_id">
</div>
<div class="form-group">
    <label for="p_sp_permit_number">Spermit Number</label>
    <input class="form-control" type="text" id="p_sp_permit_number" name="p_sp_permit_number">
</div>
<div class="form-group">
    <label for="p_sp_building_coverage_percent">Sbuilding Coverage Percent</label>
    <input class="form-control" type="text" id="p_sp_building_coverage_percent" name="p_sp_building_coverage_percent">
</div>
<div class="form-group">
    <label for="p_sp_permit_fee">Spermit Fee</label>
    <input class="form-control" type="text" id="p_sp_permit_fee" name="p_sp_permit_fee">
</div>
<div class="form-group">
    <label for="p_sp_owner_last_name">Sowner Last Name</label>
    <input class="form-control" type="text" id="p_sp_owner_last_name" name="p_sp_owner_last_name">
</div>
<div class="form-group">
    <label for="p_sp_owner_street">Sowner Street</label>
    <input class="form-control" type="text" id="p_sp_owner_street" name="p_sp_owner_street">
</div>
<div class="form-group">
    <label for="p_sp_owner_city">Sowner City</label>
    <input class="form-control" type="text" id="p_sp_owner_city" name="p_sp_owner_city">
</div>
<div class="form-group">
    <label for="p_sp_owner_state_code">Sowner State Code</label>
    <input class="form-control" type="text" id="p_sp_owner_state_code" name="p_sp_owner_state_code">
</div>
<div class="form-group">
    <label for="p_sp_owner_zip_code">Sowner Zicode</label>
    <input class="form-control" type="text" id="p_sp_owner_zip_code" name="p_sp_owner_zip_code">
</div>
<div class="form-group">
    <label for="p_sp_business_street">Sbusiness Street</label>
    <input class="form-control" type="text" id="p_sp_business_street" name="p_sp_business_street">
</div>
<div class="form-group">
    <label for="p_sp_business_city">Sbusiness City</label>
    <input class="form-control" type="text" id="p_sp_business_city" name="p_sp_business_city">
</div>
<div class="form-group">
    <label for="p_sp_business_state_code">Sbusiness State Code</label>
    <input class="form-control" type="text" id="p_sp_business_state_code" name="p_sp_business_state_code">
</div>
<div class="form-group">
    <label for="p_sp_business_zip_code">Sbusiness Zicode</label>
    <input class="form-control" type="text" id="p_sp_business_zip_code" name="p_sp_business_zip_code">
</div>
<div class="form-group">
    <label for="p_sp_contractor_last_name">Scontractor Last Name</label>
    <input class="form-control" type="text" id="p_sp_contractor_last_name" name="p_sp_contractor_last_name">
</div>
<div class="form-group">
    <label for="p_sp_contractor_phone_number">Scontractor Phone Number</label>
    <input class="form-control" type="text" id="p_sp_contractor_phone_number" name="p_sp_contractor_phone_number">
</div>
<div class="form-group">
    <label for="p_sign_square_footage">Sign Square Footage</label>
    <input class="form-control" type="number" id="p_sign_square_footage" name="p_sign_square_footage" step="any">
</div>
<div class="form-group">
    <label for="p_lettering_height">Lettering Height</label>
    <input class="form-control" type="text" id="p_lettering_height" name="p_lettering_height">
</div>

    <div class="form-group mt-3">
      <button class="btn btn-primary" type="submit">Submit</button>
    </div>
  </form>
  </div>
</div>
</body>
</html>
