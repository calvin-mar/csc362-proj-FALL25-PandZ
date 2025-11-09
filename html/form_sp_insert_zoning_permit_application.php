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
    $p_surveyor_id = isset($_POST['p_surveyor_id']) && $_POST['p_surveyor_id'] !== '' ? $_POST['p_surveyor_id'] : null;
    $p_architect_id = isset($_POST['p_architect_id']) && $_POST['p_architect_id'] !== '' ? $_POST['p_architect_id'] : null;
    $p_land_architect_id = isset($_POST['p_land_architect_id']) && $_POST['p_land_architect_id'] !== '' ? $_POST['p_land_architect_id'] : null;
    $p_contractor_id = isset($_POST['p_contractor_id']) && $_POST['p_contractor_id'] !== '' ? $_POST['p_contractor_id'] : null;
    $p_PVA_parcel_number = isset($_POST['p_PVA_parcel_number']) && $_POST['p_PVA_parcel_number'] !== '' ? $_POST['p_PVA_parcel_number'] : null;
    $p_project_type = isset($_POST['p_project_type']) && $_POST['p_project_type'] !== '' ? $_POST['p_project_type'] : null;
    $p_zpa_project_plans = isset($_POST['p_zpa_project_plans']) && $_POST['p_zpa_project_plans'] !== '' ? $_POST['p_zpa_project_plans'] : null;
    $p_zpa_preliminary_site_evaluation = isset($_POST['p_zpa_preliminary_site_evaluation']) && $_POST['p_zpa_preliminary_site_evaluation'] !== '' ? $_POST['p_zpa_preliminary_site_evaluation'] : null;
    $sql = "CALL sp_insert_zoning_permit_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siiiiiiisss';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_surveyor_id;
        $bind_names[] = &$p_architect_id;
        $bind_names[] = &$p_land_architect_id;
        $bind_names[] = &$p_contractor_id;
        $bind_names[] = &$p_PVA_parcel_number;
        $bind_names[] = &$p_project_type;
        $bind_names[] = &$p_zpa_project_plans;
        $bind_names[] = &$p_zpa_preliminary_site_evaluation;
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
  <title>Zoning Permit Application</title>
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
  <h1>Zoning Permit Application</h1>
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
    <label for="p_surveyor_id">Surveyor Id</label>
    <input class="form-control" type="number" id="p_surveyor_id" name="p_surveyor_id">
</div>
<div class="form-group">
    <label for="p_architect_id">Architect Id</label>
    <input class="form-control" type="number" id="p_architect_id" name="p_architect_id">
</div>
<div class="form-group">
    <label for="p_land_architect_id">Land Architect Id</label>
    <input class="form-control" type="number" id="p_land_architect_id" name="p_land_architect_id">
</div>
<div class="form-group">
    <label for="p_contractor_id">Contractor Id</label>
    <input class="form-control" type="number" id="p_contractor_id" name="p_contractor_id">
</div>
<div class="form-group">
    <label for="p_PVA_parcel_number">Pva Parcel Number</label>
    <input class="form-control" type="number" id="p_PVA_parcel_number" name="p_PVA_parcel_number">
</div>
<div class="form-group">
    <label for="p_project_type">Project Type</label>
    <input class="form-control" type="text" id="p_project_type" name="p_project_type">
</div>
<div class="form-group">
    <label for="p_zpa_project_plans">Zpa Project Plans</label>
    <input class="form-control" type="text" id="p_zpa_project_plans" name="p_zpa_project_plans">
</div>
<div class="form-group">
    <label for="p_zpa_preliminary_site_evaluation">Zpa Preliminary Site Evaluation</label>
    <input class="form-control" type="text" id="p_zpa_preliminary_site_evaluation" name="p_zpa_preliminary_site_evaluation">
</div>

    <div class="form-group mt-3">
      <button class="btn btn-primary" type="submit">Submit</button>
    </div>
  </form>
  </div>
</div>
</body>
</html>
