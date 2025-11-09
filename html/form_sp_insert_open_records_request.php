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
    $p_orr_commercial_purpose = isset($_POST['p_orr_commercial_purpose']) && $_POST['p_orr_commercial_purpose'] !== '' ? $_POST['p_orr_commercial_purpose'] : null;
    $p_orr_request_for_copies = isset($_POST['p_orr_request_for_copies']) && $_POST['p_orr_request_for_copies'] !== '' ? $_POST['p_orr_request_for_copies'] : null;
    $p_orr_received_on_datetime = isset($_POST['p_orr_received_on_datetime']) && $_POST['p_orr_received_on_datetime'] !== '' ? $_POST['p_orr_received_on_datetime'] : null;
    $p_orr_receivable_datetime = isset($_POST['p_orr_receivable_datetime']) && $_POST['p_orr_receivable_datetime'] !== '' ? $_POST['p_orr_receivable_datetime'] : null;
    $p_orr_denied_reasons = isset($_POST['p_orr_denied_reasons']) && $_POST['p_orr_denied_reasons'] !== '' ? $_POST['p_orr_denied_reasons'] : null;
    $p_orr_applicant_last_name = isset($_POST['p_orr_applicant_last_name']) && $_POST['p_orr_applicant_last_name'] !== '' ? $_POST['p_orr_applicant_last_name'] : null;
    $p_orr_applicant_telephone = isset($_POST['p_orr_applicant_telephone']) && $_POST['p_orr_applicant_telephone'] !== '' ? $_POST['p_orr_applicant_telephone'] : null;
    $p_orr_applicant_street = isset($_POST['p_orr_applicant_street']) && $_POST['p_orr_applicant_street'] !== '' ? $_POST['p_orr_applicant_street'] : null;
    $p_orr_applicant_city = isset($_POST['p_orr_applicant_city']) && $_POST['p_orr_applicant_city'] !== '' ? $_POST['p_orr_applicant_city'] : null;
    $p_orr_state_code = isset($_POST['p_orr_state_code']) && $_POST['p_orr_state_code'] !== '' ? $_POST['p_orr_state_code'] : null;
    $p_orr_applicant_zip_code = isset($_POST['p_orr_applicant_zip_code']) && $_POST['p_orr_applicant_zip_code'] !== '' ? $_POST['p_orr_applicant_zip_code'] : null;
    $sql = "CALL sp_insert_open_records_request(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siisssssssssss';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_orr_commercial_purpose;
        $bind_names[] = &$p_orr_request_for_copies;
        $bind_names[] = &$p_orr_received_on_datetime;
        $bind_names[] = &$p_orr_receivable_datetime;
        $bind_names[] = &$p_orr_denied_reasons;
        $bind_names[] = &$p_orr_applicant_last_name;
        $bind_names[] = &$p_orr_applicant_telephone;
        $bind_names[] = &$p_orr_applicant_street;
        $bind_names[] = &$p_orr_applicant_city;
        $bind_names[] = &$p_orr_state_code;
        $bind_names[] = &$p_orr_applicant_zip_code;
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
  <title>Open Records Request</title>
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
  <h1>Open Records Request</h1>
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
    <label for="p_orr_commercial_purpose">Orr Commercial Purpose</label>
    <input class="form-control" type="text" id="p_orr_commercial_purpose" name="p_orr_commercial_purpose">
</div>
<div class="form-group">
    <label for="p_orr_request_for_copies">Orr Request For Copies</label>
    <input class="form-control" type="text" id="p_orr_request_for_copies" name="p_orr_request_for_copies">
</div>
<div class="form-group">
    <label for="p_orr_received_on_datetime">Orr Received On Datetime</label>
    <input class="form-control" type="date" id="p_orr_received_on_datetime" name="p_orr_received_on_datetime">
</div>
<div class="form-group">
    <label for="p_orr_receivable_datetime">Orr Receivable Datetime</label>
    <input class="form-control" type="date" id="p_orr_receivable_datetime" name="p_orr_receivable_datetime">
</div>
<div class="form-group">
    <label for="p_orr_denied_reasons">Orr Denied Reasons</label>
    <input class="form-control" type="text" id="p_orr_denied_reasons" name="p_orr_denied_reasons">
</div>
<div class="form-group">
    <label for="p_orr_applicant_last_name">Orr Applicant Last Name</label>
    <input class="form-control" type="text" id="p_orr_applicant_last_name" name="p_orr_applicant_last_name">
</div>
<div class="form-group">
    <label for="p_orr_applicant_telephone">Orr Applicant Telephone</label>
    <input class="form-control" type="text" id="p_orr_applicant_telephone" name="p_orr_applicant_telephone">
</div>
<div class="form-group">
    <label for="p_orr_applicant_street">Orr Applicant Street</label>
    <input class="form-control" type="text" id="p_orr_applicant_street" name="p_orr_applicant_street">
</div>
<div class="form-group">
    <label for="p_orr_applicant_city">Orr Applicant City</label>
    <input class="form-control" type="text" id="p_orr_applicant_city" name="p_orr_applicant_city">
</div>
<div class="form-group">
    <label for="p_orr_state_code">Orr State Code</label>
    <input class="form-control" type="text" id="p_orr_state_code" name="p_orr_state_code">
</div>
<div class="form-group">
    <label for="p_orr_applicant_zip_code">Orr Applicant Zicode</label>
    <input class="form-control" type="text" id="p_orr_applicant_zip_code" name="p_orr_applicant_zip_code">
</div>

    <div class="form-group mt-3">
      <button class="btn btn-primary" type="submit">Submit</button>
    </div>
  </form>
  </div>
</div>
</body>
</html>
