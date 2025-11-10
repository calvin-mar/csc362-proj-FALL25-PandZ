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
    $p_aar_submit_date = isset($_POST['p_aar_submit_date']) && $_POST['p_aar_submit_date'] !== '' ? $_POST['p_aar_submit_date'] : null;
    $p_aar_street_address = isset($_POST['p_aar_street_address']) && $_POST['p_aar_street_address'] !== '' ? $_POST['p_aar_street_address'] : null;
    $p_aar_city_address = isset($_POST['p_aar_city_address']) && $_POST['p_aar_city_address'] !== '' ? $_POST['p_aar_city_address'] : null;
    $p_state_code = isset($_POST['p_state_code']) && $_POST['p_state_code'] !== '' ? $_POST['p_state_code'] : null;
    $p_aar_zip_code = isset($_POST['p_aar_zip_code']) && $_POST['p_aar_zip_code'] !== '' ? $_POST['p_aar_zip_code'] : null;
    $p_aar_property_location = isset($_POST['p_aar_property_location']) && $_POST['p_aar_property_location'] !== '' ? $_POST['p_aar_property_location'] : null;
    $p_aar_official_decision = isset($_POST['p_aar_official_decision']) && $_POST['p_aar_official_decision'] !== '' ? $_POST['p_aar_official_decision'] : null;
    $p_aar_relevant_provisions = isset($_POST['p_aar_relevant_provisions']) && $_POST['p_aar_relevant_provisions'] !== '' ? $_POST['p_aar_relevant_provisions'] : null;
    $p_aar_hearing_date = isset($_POST['p_aar_hearing_date']) && $_POST['p_aar_hearing_date'] !== '' ? $_POST['p_aar_hearing_date'] : null;
    $p_aar_appellant_first_name = isset($_POST['p_aar_appellant_first_name']) && $_POST['p_aar_appellant_first_name'] !== '' ? $_POST['p_aar_appellant_first_name'] : null;
    $p_adjacent_property_owner_street = isset($_POST['p_adjacent_property_owner_street']) && $_POST['p_adjacent_property_owner_street'] !== '' ? $_POST['p_adjacent_property_owner_street'] : null;
    $p_aar_appellant_last_name = isset($_POST['p_aar_appellant_last_name']) && $_POST['p_aar_appellant_last_name'] !== '' ? $_POST['p_aar_appellant_last_name'] : null;
    $p_adjacent_property_owner_city = isset($_POST['p_adjacent_property_owner_city']) && $_POST['p_adjacent_property_owner_city'] !== '' ? $_POST['p_adjacent_property_owner_city'] : null;
    $p_adjacent_property_owner_state_code = isset($_POST['p_adjacent_property_owner_state_code']) && $_POST['p_adjacent_property_owner_state_code'] !== '' ? $_POST['p_adjacent_property_owner_state_code'] : null;
    $p_adjacent_property_owner_zip = isset($_POST['p_adjacent_property_owner_zip']) && $_POST['p_adjacent_property_owner_zip'] !== '' ? $_POST['p_adjacent_property_owner_zip'] : null;
    $p_aar_property_owner_first_name = isset($_POST['p_aar_property_owner_first_name']) && $_POST['p_aar_property_owner_first_name'] !== '' ? $_POST['p_aar_property_owner_first_name'] : null;
    $p_aar_property_owner_last_name = isset($_POST['p_aar_property_owner_last_name']) && $_POST['p_aar_property_owner_last_name'] !== '' ? $_POST['p_aar_property_owner_last_name'] : null;
    $sql = "CALL sp_insert_administrative_appeal_request(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siisssssssssssssssss';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_aar_hearing_date;
        $bind_names[] = &$p_aar_submit_date;
        $bind_names[] = &$p_aar_street_address;
        $bind_names[] = &$p_aar_city_address;
        $bind_names[] = &$p_state_code;
        $bind_names[] = &$p_aar_zip_code;
        $bind_names[] = &$p_aar_property_location;
        $bind_names[] = &$p_aar_official_decision;
        $bind_names[] = &$p_aar_relevant_provisions;
        $bind_names[] = &$p_aar_appellant_first_name;
        $bind_names[] = &$p_aar_appellant_last_name;
        $bind_names[] = &$p_adjacent_property_owner_street;
        $bind_names[] = &$p_adjacent_property_owner_city;
        $bind_names[] = &$p_adjacent_property_owner_state_code;
        $bind_names[] = &$p_adjacent_property_owner_zip;
        $bind_names[] = &$p_aar_property_owner_first_name;
        $bind_names[] = &$p_aar_property_owner_last_name;
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
  <title>Administrative Appeal Request</title>
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
  <h1>Administrative Appeal Request</h1>
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
    <label for="p_aar_submit_date">Aar Submit Date</label>
    <input class="form-control" type="date" id="p_aar_submit_date" name="p_aar_submit_date">
</div>
<div class="form-group">
    <label for="p_aar_hearing_date">Aar Hearing Date</label>
    <input class="form-control" type="date" id="p_aar_hearing_date" name="p_aar_hearing_date">
</div>
<div class="form-group">
    <label for="p_aar_street_address">Aar Street Address</label>
    <input class="form-control" type="text" id="p_aar_street_address" name="p_aar_street_address">
</div>
<div class="form-group">
    <label for="p_aar_city_address">Aar City Address</label>
    <input class="form-control" type="text" id="p_aar_city_address" name="p_aar_city_address">
</div>
<div class="form-group">
    <label for="p_state_code">State Code</label>
    <input class="form-control" type="text" id="p_state_code" name="p_state_code">
</div>
<div class="form-group">
    <label for="p_aar_zip_code">Aar Zipcode</label>
    <input class="form-control" type="text" id="p_aar_zip_code" name="p_aar_zip_code">
</div>
<div class="form-group">
    <label for="p_aar_property_location">Aar Property Location</label>
    <input class="form-control" type="text" id="p_aar_property_location" name="p_aar_property_location">
</div>
<div class="form-group">
    <label for="p_aar_official_decision">Aar Official Decision</label>
    <input class="form-control" type="text" id="p_aar_official_decision" name="p_aar_official_decision">
</div>
<div class="form-group">
    <label for="p_aar_relevant_provisions">Aar Relevant Provisions</label>
    <input class="form-control" type="text" id="p_aar_relevant_provisions" name="p_aar_relevant_provisions">
</div>
<div class="form-group">
    <label for="p_aar_appellant_first_name">Aar Appellant First Name</label>
    <input class="form-control" type="text" id="p_aar_appellant_first_name" name="p_aar_appellant_first_name">
</div>
<div class="form-group">
    <label for="p_aar_appellant_last_name">Aar Appellant Last Name</label>
    <input class="form-control" type="text" id="p_aar_appellant_last_name" name="p_aar_appellant_last_name">
</div>
<div class="form-group">
    <label for="p_aar_property_owner_first_name">Property Owner First Name</label>
    <input class="form-control" type="text" id="p_aar_property_owner_first_name" name="p_aar_property_owner_first_name">
</div>
<div class="form-group">
    <label for="p_aar_property_owner_last_name">Property Owner Last Name</label>
    <input class="form-control" type="text" id="p_aar_property_owner_last_name" name="p_aar_property_owner_last_name">
</div>
<div class="form-group">
    <label for="p_adjacent_property_owner_street">Adjacent Property Owner Street</label>
    <input class="form-control" type="text" id="p_adjacent_property_owner_street" name="p_adjacent_property_owner_street">
</div>
<div class="form-group">
    <label for="p_adjacent_property_owner_city">Adjacent Property Owner City</label>
    <input class="form-control" type="text" id="p_adjacent_property_owner_city" name="p_adjacent_property_owner_city">
</div>
<div class="form-group">
    <label for="p_adjacent_property_owner_state_code">Adjacent Property Owner State Code</label>
    <input class="form-control" type="text" id="p_adjacent_property_owner_state_code" name="p_adjacent_property_owner_state_code">
</div>
<div class="form-group">
    <label for="p_adjacent_property_owner_zip">Adjacent Property Owner Zip</label>
    <input class="form-control" type="text" id="p_adjacent_property_owner_zip" name="p_adjacent_property_owner_zip">
</div>

    <div class="form-group mt-3">
      <button class="btn btn-primary" type="submit">Submit</button>
    </div>
  </form>
  </div>
</div>
</body>
</html>
