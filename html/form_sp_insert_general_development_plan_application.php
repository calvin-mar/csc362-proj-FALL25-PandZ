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
    $p_state_code = isset($_POST['p_state_code']) && $_POST['p_state_code'] !== '' ? $_POST['p_state_code'] : null;
    $p_gdpa_applicant_zip = isset($_POST['p_gdpa_applicant_zip']) && $_POST['p_gdpa_applicant_zip'] !== '' ? $_POST['p_gdpa_applicant_zip'] : null;
    $p_gdpa_applicant_phone = isset($_POST['p_gdpa_applicant_phone']) && $_POST['p_gdpa_applicant_phone'] !== '' ? $_POST['p_gdpa_applicant_phone'] : null;
    $p_gdpa_plan_amendment_request = isset($_POST['p_gdpa_plan_amendment_request']) && $_POST['p_gdpa_plan_amendment_request'] !== '' ? $_POST['p_gdpa_plan_amendment_request'] : null;
    $p_gdpa_proposed_conditions = isset($_POST['p_gdpa_proposed_conditions']) && $_POST['p_gdpa_proposed_conditions'] !== '' ? $_POST['p_gdpa_proposed_conditions'] : null;
    $p_required_findings_type = isset($_POST['p_required_findings_type']) && $_POST['p_required_findings_type'] !== '' ? $_POST['p_required_findings_type'] : null;
    $p_gdpa_concept_plan = isset($_POST['p_gdpa_concept_plan']) && $_POST['p_gdpa_concept_plan'] !== '' ? $_POST['p_gdpa_concept_plan'] : null;
    $p_gdpa_traffic_study = isset($_POST['p_gdpa_traffic_study']) && $_POST['p_gdpa_traffic_study'] !== '' ? $_POST['p_gdpa_traffic_study'] : null;
    $p_gdpa_geologic_analysis = isset($_POST['p_gdpa_geologic_analysis']) && $_POST['p_gdpa_geologic_analysis'] !== '' ? $_POST['p_gdpa_geologic_analysis'] : null;
    $sql = "CALL sp_insert_general_development_plan_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siisssssssss';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_state_code;
        $bind_names[] = &$p_gdpa_applicant_zip;
        $bind_names[] = &$p_gdpa_applicant_phone;
        $bind_names[] = &$p_gdpa_plan_amendment_request;
        $bind_names[] = &$p_gdpa_proposed_conditions;
        $bind_names[] = &$p_required_findings_type;
        $bind_names[] = &$p_gdpa_concept_plan;
        $bind_names[] = &$p_gdpa_traffic_study;
        $bind_names[] = &$p_gdpa_geologic_analysis;
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
  <title>General Development Plan Application</title>
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
  <h1>General Development Plan Application</h1>
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
    <label for="p_state_code">State Code</label>
    <input class="form-control" type="text" id="p_state_code" name="p_state_code">
</div>
<div class="form-group">
    <label for="p_gdpa_applicant_zip">Gdpa Applicant Zip</label>
    <input class="form-control" type="text" id="p_gdpa_applicant_zip" name="p_gdpa_applicant_zip">
</div>
<div class="form-group">
    <label for="p_gdpa_applicant_phone">Gdpa Applicant Phone</label>
    <input class="form-control" type="text" id="p_gdpa_applicant_phone" name="p_gdpa_applicant_phone">
</div>
<div class="form-group">
    <label for="p_gdpa_plan_amendment_request">Gdpa Plan Amendment Request</label>
    <input class="form-control" type="text" id="p_gdpa_plan_amendment_request" name="p_gdpa_plan_amendment_request">
</div>
<div class="form-group">
    <label for="p_gdpa_proposed_conditions">Gdpa Proposed Conditions</label>
    <input class="form-control" type="text" id="p_gdpa_proposed_conditions" name="p_gdpa_proposed_conditions">
</div>
<div class="form-group">
    <label for="p_required_findings_type">Required Findings Type</label>
    <input class="form-control" type="text" id="p_required_findings_type" name="p_required_findings_type">
</div>
<div class="form-group">
    <label for="p_gdpa_concept_plan">Gdpa Concept Plan</label>
    <input class="form-control" type="text" id="p_gdpa_concept_plan" name="p_gdpa_concept_plan">
</div>
<div class="form-group">
    <label for="p_gdpa_traffic_study">Gdpa Traffic Study</label>
    <input class="form-control" type="text" id="p_gdpa_traffic_study" name="p_gdpa_traffic_study">
</div>
<div class="form-group">
    <label for="p_gdpa_geologic_analysis">Gdpa Geologic Analysis</label>
    <input class="form-control" type="text" id="p_gdpa_geologic_analysis" name="p_gdpa_geologic_analysis">
</div>

    <div class="form-group mt-3">
      <button class="btn btn-primary" type="submit">Submit</button>
    </div>
  </form>
  </div>
</div>
</body>
</html>
