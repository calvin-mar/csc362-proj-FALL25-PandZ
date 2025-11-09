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
    $p_zva_letter_content = isset($_POST['p_zva_letter_content']) && $_POST['p_zva_letter_content'] !== '' ? $_POST['p_zva_letter_content'] : null;
    $p_zva_zoning_letter_street = isset($_POST['p_zva_zoning_letter_street']) && $_POST['p_zva_zoning_letter_street'] !== '' ? $_POST['p_zva_zoning_letter_street'] : null;
    $p_zva_state_code = isset($_POST['p_zva_state_code']) && $_POST['p_zva_state_code'] !== '' ? $_POST['p_zva_state_code'] : null;
    $p_zva_zoning_letter_city = isset($_POST['p_zva_zoning_letter_city']) && $_POST['p_zva_zoning_letter_city'] !== '' ? $_POST['p_zva_zoning_letter_city'] : null;
    $p_zva_zoning_letter_zip = isset($_POST['p_zva_zoning_letter_zip']) && $_POST['p_zva_zoning_letter_zip'] !== '' ? $_POST['p_zva_zoning_letter_zip'] : null;
    $p_zva_property_street = isset($_POST['p_zva_property_street']) && $_POST['p_zva_property_street'] !== '' ? $_POST['p_zva_property_street'] : null;
    $p_zva_property_state_code = isset($_POST['p_zva_property_state_code']) && $_POST['p_zva_property_state_code'] !== '' ? $_POST['p_zva_property_state_code'] : null;
    $p_zva_property_zip = isset($_POST['p_zva_property_zip']) && $_POST['p_zva_property_zip'] !== '' ? $_POST['p_zva_property_zip'] : null;
    $p_property_city = isset($_POST['p_property_city']) && $_POST['p_property_city'] !== '' ? $_POST['p_property_city'] : null;
    $p_zva_applicant_last_name = isset($_POST['p_zva_applicant_last_name']) && $_POST['p_zva_applicant_last_name'] !== '' ? $_POST['p_zva_applicant_last_name'] : null;
    $p_zva_applicant_street = isset($_POST['p_zva_applicant_street']) && $_POST['p_zva_applicant_street'] !== '' ? $_POST['p_zva_applicant_street'] : null;
    $p_zva_applicant_city = isset($_POST['p_zva_applicant_city']) && $_POST['p_zva_applicant_city'] !== '' ? $_POST['p_zva_applicant_city'] : null;
    $p_zva_applicant_state_code = isset($_POST['p_zva_applicant_state_code']) && $_POST['p_zva_applicant_state_code'] !== '' ? $_POST['p_zva_applicant_state_code'] : null;
    $p_zva_applicant_zip_code = isset($_POST['p_zva_applicant_zip_code']) && $_POST['p_zva_applicant_zip_code'] !== '' ? $_POST['p_zva_applicant_zip_code'] : null;
    $p_zva_applicant_phone_number = isset($_POST['p_zva_applicant_phone_number']) && $_POST['p_zva_applicant_phone_number'] !== '' ? $_POST['p_zva_applicant_phone_number'] : null;
    $p_zva_applicant_fax_number = isset($_POST['p_zva_applicant_fax_number']) && $_POST['p_zva_applicant_fax_number'] !== '' ? $_POST['p_zva_applicant_fax_number'] : null;
    $p_zva_owner_last_name = isset($_POST['p_zva_owner_last_name']) && $_POST['p_zva_owner_last_name'] !== '' ? $_POST['p_zva_owner_last_name'] : null;
    $p_zva_owner_street = isset($_POST['p_zva_owner_street']) && $_POST['p_zva_owner_street'] !== '' ? $_POST['p_zva_owner_street'] : null;
    $p_zva_owner_city = isset($_POST['p_zva_owner_city']) && $_POST['p_zva_owner_city'] !== '' ? $_POST['p_zva_owner_city'] : null;
    $p_zva_owner_state_code = isset($_POST['p_zva_owner_state_code']) && $_POST['p_zva_owner_state_code'] !== '' ? $_POST['p_zva_owner_state_code'] : null;
    $p_zva_owner_zip_code = isset($_POST['p_zva_owner_zip_code']) && $_POST['p_zva_owner_zip_code'] !== '' ? $_POST['p_zva_owner_zip_code'] : null;
    $sql = "CALL sp_insert_zoning_verification_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siisssssssssssssssssssss';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_zva_letter_content;
        $bind_names[] = &$p_zva_zoning_letter_street;
        $bind_names[] = &$p_zva_state_code;
        $bind_names[] = &$p_zva_zoning_letter_city;
        $bind_names[] = &$p_zva_zoning_letter_zip;
        $bind_names[] = &$p_zva_property_street;
        $bind_names[] = &$p_zva_property_state_code;
        $bind_names[] = &$p_zva_property_zip;
        $bind_names[] = &$p_property_city;
        $bind_names[] = &$p_zva_applicant_last_name;
        $bind_names[] = &$p_zva_applicant_street;
        $bind_names[] = &$p_zva_applicant_city;
        $bind_names[] = &$p_zva_applicant_state_code;
        $bind_names[] = &$p_zva_applicant_zip_code;
        $bind_names[] = &$p_zva_applicant_phone_number;
        $bind_names[] = &$p_zva_applicant_fax_number;
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
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Zoning Verification Application</title>
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
<div class="form-group">
    <label for="p_form_datetime_resolved">Form Datetime Resolved</label>
    <input class="form-control" type="text" id="p_form_datetime_resolved" name="p_form_datetime_resolved">
</div>
<div class="form-group">
    <label for="p_correction_form_id">Correction Form Id</label>
    <input class="form-control" type="number" id="p_correction_form_id" name="p_correction_form_id">
</div>
<div class="form-group">
    <label for="p_zva_letter_content">Zva Letter Content</label>
    <input class="form-control" type="text" id="p_zva_letter_content" name="p_zva_letter_content">
</div>
<div class="form-group">
    <label for="p_zva_zoning_letter_street">Zva Zoning Letter Street</label>
    <input class="form-control" type="text" id="p_zva_zoning_letter_street" name="p_zva_zoning_letter_street">
</div>
<div class="form-group">
    <label for="p_zva_state_code">Zva State Code</label>
    <input class="form-control" type="text" id="p_zva_state_code" name="p_zva_state_code">
</div>
<div class="form-group">
    <label for="p_zva_zoning_letter_city">Zva Zoning Letter City</label>
    <input class="form-control" type="text" id="p_zva_zoning_letter_city" name="p_zva_zoning_letter_city">
</div>
<div class="form-group">
    <label for="p_zva_zoning_letter_zip">Zva Zoning Letter Zip</label>
    <input class="form-control" type="text" id="p_zva_zoning_letter_zip" name="p_zva_zoning_letter_zip">
</div>
<div class="form-group">
    <label for="p_zva_property_street">Zva Property Street</label>
    <input class="form-control" type="text" id="p_zva_property_street" name="p_zva_property_street">
</div>
<div class="form-group">
    <label for="p_zva_property_state_code">Zva Property State Code</label>
    <input class="form-control" type="text" id="p_zva_property_state_code" name="p_zva_property_state_code">
</div>
<div class="form-group">
    <label for="p_zva_property_zip">Zva Property Zip</label>
    <input class="form-control" type="text" id="p_zva_property_zip" name="p_zva_property_zip">
</div>
<div class="form-group">
    <label for="p_property_city">Property City</label>
    <input class="form-control" type="text" id="p_property_city" name="p_property_city">
</div>
<div class="form-group">
    <label for="p_zva_applicant_last_name">Zva Applicant Last Name</label>
    <input class="form-control" type="text" id="p_zva_applicant_last_name" name="p_zva_applicant_last_name">
</div>
<div class="form-group">
    <label for="p_zva_applicant_street">Zva Applicant Street</label>
    <input class="form-control" type="text" id="p_zva_applicant_street" name="p_zva_applicant_street">
</div>
<div class="form-group">
    <label for="p_zva_applicant_city">Zva Applicant City</label>
    <input class="form-control" type="text" id="p_zva_applicant_city" name="p_zva_applicant_city">
</div>
<div class="form-group">
    <label for="p_zva_applicant_state_code">Zva Applicant State Code</label>
    <input class="form-control" type="text" id="p_zva_applicant_state_code" name="p_zva_applicant_state_code">
</div>
<div class="form-group">
    <label for="p_zva_applicant_zip_code">Zva Applicant Zicode</label>
    <input class="form-control" type="text" id="p_zva_applicant_zip_code" name="p_zva_applicant_zip_code">
</div>
<div class="form-group">
    <label for="p_zva_applicant_phone_number">Zva Applicant Phone Number</label>
    <input class="form-control" type="text" id="p_zva_applicant_phone_number" name="p_zva_applicant_phone_number">
</div>
<div class="form-group">
    <label for="p_zva_applicant_fax_number">Zva Applicant Fax Number</label>
    <input class="form-control" type="text" id="p_zva_applicant_fax_number" name="p_zva_applicant_fax_number">
</div>
<div class="form-group">
    <label for="p_zva_owner_last_name">Zva Owner Last Name</label>
    <input class="form-control" type="text" id="p_zva_owner_last_name" name="p_zva_owner_last_name">
</div>
<div class="form-group">
    <label for="p_zva_owner_street">Zva Owner Street</label>
    <input class="form-control" type="text" id="p_zva_owner_street" name="p_zva_owner_street">
</div>
<div class="form-group">
    <label for="p_zva_owner_city">Zva Owner City</label>
    <input class="form-control" type="text" id="p_zva_owner_city" name="p_zva_owner_city">
</div>
<div class="form-group">
    <label for="p_zva_owner_state_code">Zva Owner State Code</label>
    <input class="form-control" type="text" id="p_zva_owner_state_code" name="p_zva_owner_state_code">
</div>
<div class="form-group">
    <label for="p_zva_owner_zip_code">Zva Owner Zicode</label>
    <input class="form-control" type="text" id="p_zva_owner_zip_code" name="p_zva_owner_zip_code">
</div>

    <div class="form-group mt-3">
      <button class="btn btn-primary" type="submit">Submit</button>
    </div>
  </form>
  </div>
</div>
</body>
</html>
