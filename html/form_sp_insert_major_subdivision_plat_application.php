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
    $p_engineer_id = isset($_POST['p_engineer_id']) && $_POST['p_engineer_id'] !== '' ? $_POST['p_engineer_id'] : null;
    $p_PVA_parcel_number = isset($_POST['p_PVA_parcel_number']) && $_POST['p_PVA_parcel_number'] !== '' ? $_POST['p_PVA_parcel_number'] : null;
    $p_mspa_topographic_survey = isset($_POST['p_mspa_topographic_survey']) && $_POST['p_mspa_topographic_survey'] !== '' ? $_POST['p_mspa_topographic_survey'] : null;
    $p_mspa_proposed_plot_layout = isset($_POST['p_mspa_proposed_plot_layout']) && $_POST['p_mspa_proposed_plot_layout'] !== '' ? $_POST['p_mspa_proposed_plot_layout'] : null;
    $p_mspa_plat_restrictions = isset($_POST['p_mspa_plat_restrictions']) && $_POST['p_mspa_plat_restrictions'] !== '' ? $_POST['p_mspa_plat_restrictions'] : null;
    $p_mspa_property_owner_convenants = isset($_POST['p_mspa_property_owner_convenants']) && $_POST['p_mspa_property_owner_convenants'] !== '' ? $_POST['p_mspa_property_owner_convenants'] : null;
    $p_mspa_association_covenants = isset($_POST['p_mspa_association_covenants']) && $_POST['p_mspa_association_covenants'] !== '' ? $_POST['p_mspa_association_covenants'] : null;
    $p_mspa_master_deed = isset($_POST['p_mspa_master_deed']) && $_POST['p_mspa_master_deed'] !== '' ? $_POST['p_mspa_master_deed'] : null;
    $p_mspa_construction_plans = isset($_POST['p_mspa_construction_plans']) && $_POST['p_mspa_construction_plans'] !== '' ? $_POST['p_mspa_construction_plans'] : null;
    $p_mspa_traffic_impact_study = isset($_POST['p_mspa_traffic_impact_study']) && $_POST['p_mspa_traffic_impact_study'] !== '' ? $_POST['p_mspa_traffic_impact_study'] : null;
    $p_mspa_geologic_study = isset($_POST['p_mspa_geologic_study']) && $_POST['p_mspa_geologic_study'] !== '' ? $_POST['p_mspa_geologic_study'] : null;
    $p_mspa_drainage_plan = isset($_POST['p_mspa_drainage_plan']) && $_POST['p_mspa_drainage_plan'] !== '' ? $_POST['p_mspa_drainage_plan'] : null;
    $p_mspa_pavement_design = isset($_POST['p_mspa_pavement_design']) && $_POST['p_mspa_pavement_design'] !== '' ? $_POST['p_mspa_pavement_design'] : null;
    $p_mspa_SWPPP_EPSC_plan = isset($_POST['p_mspa_SWPPP_EPSC_plan']) && $_POST['p_mspa_SWPPP_EPSC_plan'] !== '' ? $_POST['p_mspa_SWPPP_EPSC_plan'] : null;
    $p_mspa_construction_bond_est = isset($_POST['p_mspa_construction_bond_est']) && $_POST['p_mspa_construction_bond_est'] !== '' ? $_POST['p_mspa_construction_bond_est'] : null;
    $sql = "CALL sp_insert_major_subdivision_plat_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'siiiiisssssssssssss';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_surveyor_id;
        $bind_names[] = &$p_engineer_id;
        $bind_names[] = &$p_PVA_parcel_number;
        $bind_names[] = &$p_mspa_topographic_survey;
        $bind_names[] = &$p_mspa_proposed_plot_layout;
        $bind_names[] = &$p_mspa_plat_restrictions;
        $bind_names[] = &$p_mspa_property_owner_convenants;
        $bind_names[] = &$p_mspa_association_covenants;
        $bind_names[] = &$p_mspa_master_deed;
        $bind_names[] = &$p_mspa_construction_plans;
        $bind_names[] = &$p_mspa_traffic_impact_study;
        $bind_names[] = &$p_mspa_geologic_study;
        $bind_names[] = &$p_mspa_drainage_plan;
        $bind_names[] = &$p_mspa_pavement_design;
        $bind_names[] = &$p_mspa_SWPPP_EPSC_plan;
        $bind_names[] = &$p_mspa_construction_bond_est;
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
  <title>Major Subdivision Plat Application</title>
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
  <h1>Major Subdivision Plat Application</h1>
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
    <label for="p_engineer_id">Engineer Id</label>
    <input class="form-control" type="number" id="p_engineer_id" name="p_engineer_id">
</div>
<div class="form-group">
    <label for="p_PVA_parcel_number">Pva Parcel Number</label>
    <input class="form-control" type="number" id="p_PVA_parcel_number" name="p_PVA_parcel_number">
</div>
<div class="form-group">
    <label for="p_mspa_topographic_survey">Mspa Topographic Survey</label>
    <input class="form-control" type="text" id="p_mspa_topographic_survey" name="p_mspa_topographic_survey">
</div>
<div class="form-group">
    <label for="p_mspa_proposed_plot_layout">Mspa Proposed Plot Layout</label>
    <input class="form-control" type="text" id="p_mspa_proposed_plot_layout" name="p_mspa_proposed_plot_layout">
</div>
<div class="form-group">
    <label for="p_mspa_plat_restrictions">Mspa Plat Restrictions</label>
    <input class="form-control" type="text" id="p_mspa_plat_restrictions" name="p_mspa_plat_restrictions">
</div>
<div class="form-group">
    <label for="p_mspa_property_owner_convenants">Mspa Property Owner Convenants</label>
    <input class="form-control" type="text" id="p_mspa_property_owner_convenants" name="p_mspa_property_owner_convenants">
</div>
<div class="form-group">
    <label for="p_mspa_association_covenants">Mspa Association Covenants</label>
    <input class="form-control" type="text" id="p_mspa_association_covenants" name="p_mspa_association_covenants">
</div>
<div class="form-group">
    <label for="p_mspa_master_deed">Mspa Master Deed</label>
    <input class="form-control" type="text" id="p_mspa_master_deed" name="p_mspa_master_deed">
</div>
<div class="form-group">
    <label for="p_mspa_construction_plans">Mspa Construction Plans</label>
    <input class="form-control" type="text" id="p_mspa_construction_plans" name="p_mspa_construction_plans">
</div>
<div class="form-group">
    <label for="p_mspa_traffic_impact_study">Mspa Traffic Impact Study</label>
    <input class="form-control" type="text" id="p_mspa_traffic_impact_study" name="p_mspa_traffic_impact_study">
</div>
<div class="form-group">
    <label for="p_mspa_geologic_study">Mspa Geologic Study</label>
    <input class="form-control" type="text" id="p_mspa_geologic_study" name="p_mspa_geologic_study">
</div>
<div class="form-group">
    <label for="p_mspa_drainage_plan">Mspa Drainage Plan</label>
    <input class="form-control" type="text" id="p_mspa_drainage_plan" name="p_mspa_drainage_plan">
</div>
<div class="form-group">
    <label for="p_mspa_pavement_design">Mspa Pavement Design</label>
    <input class="form-control" type="text" id="p_mspa_pavement_design" name="p_mspa_pavement_design">
</div>
<div class="form-group">
    <label for="p_mspa_SWPPP_EPSC_plan">Mspa Swppp Epsc Plan</label>
    <input class="form-control" type="text" id="p_mspa_SWPPP_EPSC_plan" name="p_mspa_SWPPP_EPSC_plan">
</div>
<div class="form-group">
    <label for="p_mspa_construction_bond_est">Mspa Construction Bond Est</label>
    <input class="form-control" type="text" id="p_mspa_construction_bond_est" name="p_mspa_construction_bond_est">
</div>

    <div class="form-group mt-3">
      <button class="btn btn-primary" type="submit">Submit</button>
    </div>
  </form>
  </div>
</div>
</body>
</html>
