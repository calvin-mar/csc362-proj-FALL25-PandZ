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
    $p_apof_neighbor_property_location = isset($_POST['p_apof_neighbor_property_location']) && $_POST['p_apof_neighbor_property_location'] !== '' ? $_POST['p_apof_neighbor_property_location'] : null;
    $p_PVA_map_code = isset($_POST['p_PVA_map_code']) && $_POST['p_PVA_map_code'] !== '' ? $_POST['p_PVA_map_code'] : null;
    $p_apof_neighbor_property_street = isset($_POST['p_apof_neighbor_property_street']) && $_POST['p_apof_neighbor_property_street'] !== '' ? $_POST['p_apof_neighbor_property_street'] : null;
    $p_apof_neighbor_property_city = isset($_POST['p_apof_neighbor_property_city']) && $_POST['p_apof_neighbor_property_city'] !== '' ? $_POST['p_apof_neighbor_property_city'] : null;
    $p_apof_state_code = isset($_POST['p_apof_state_code']) && $_POST['p_apof_state_code'] !== '' ? $_POST['p_apof_state_code'] : null;
    $p_apof_neighbor_property_zip = isset($_POST['p_apof_neighbor_property_zip']) && $_POST['p_apof_neighbor_property_zip'] !== '' ? $_POST['p_apof_neighbor_property_zip'] : null;
    $p_apof_neighbor_property_deed_book = isset($_POST['p_apof_neighbor_property_deed_book']) && $_POST['p_apof_neighbor_property_deed_book'] !== '' ? $_POST['p_apof_neighbor_property_deed_book'] : null;
    $p_apof_property_street_pg_number = isset($_POST['p_apof_property_street_pg_number']) && $_POST['p_apof_property_street_pg_number'] !== '' ? $_POST['p_apof_property_street_pg_number'] : null;
    $p_adjacent_property_owner_street = isset($_POST['p_adjacent_property_owner_street']) && $_POST['p_adjacent_property_owner_street'] !== '' ? $_POST['p_adjacent_property_owner_street'] : null;
    $p_adjacent_property_owner_city = isset($_POST['p_adjacent_property_owner_city']) && $_POST['p_adjacent_property_owner_city'] !== '' ? $_POST['p_adjacent_property_owner_city'] : null;
    $p_adjacent_state_code = isset($_POST['p_adjacent_state_code']) && $_POST['p_adjacent_state_code'] !== '' ? $_POST['p_adjacent_state_code'] : null;
    $p_adjacent_property_owner_zip = isset($_POST['p_adjacent_property_owner_zip']) && $_POST['p_adjacent_property_owner_zip'] !== '' ? $_POST['p_adjacent_property_owner_zip'] : null;
    $sql = "CALL sp_insert_adjacent_property_owners_form(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $types = 'iissssssssssss';
        $bind_names = array();
        $bind_names[] = &$p_form_datetime_resolved;
        $bind_names[] = &$p_form_paid_bool;
        $bind_names[] = &$p_correction_form_id;
        $bind_names[] = &$p_PVA_map_code;
        $bind_names[] = &$p_apof_neighbor_property_location;
        $bind_names[] = &$p_apof_neighbor_property_street;
        $bind_names[] = &$p_apof_neighbor_property_city;
        $bind_names[] = &$p_apof_state_code;
        $bind_names[] = &$p_apof_neighbor_property_zip;
        $bind_names[] = &$p_apof_neighbor_property_deed_book;
        $bind_names[] = &$p_apof_property_street_pg_number;
        $bind_names[] = &$p_adjacent_property_owner_street;
        $bind_names[] = &$p_adjacent_property_owner_city;
        $bind_names[] = &$p_adjacent_state_code;
        $bind_names[] = &$p_adjacent_property_owner_zip;
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
  <title>Adjacent Property Owners Form</title>
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
  <h1>Adjacent Property Owners Form</h1>
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
  <label for="p_PVA_map_code">PVA Map Code</label>
  <input class="form-control" type="text" id="p_PVA_map_code" name="p_PVA_map_code">
</div>
<div class="form-group">
    <label for="p_apof_neighbor_property_location">Apof Neighbor Property Location</label>
    <input class="form-control" type="text" id="p_apof_neighbor_property_location" name="p_apof_neighbor_property_location">
</div>
<div class="form-group">
    <label for="p_apof_neighbor_property_street">Apof Neighbor Property Street</label>
    <input class="form-control" type="number" id="p_apof_neighbor_property_street" name="p_apof_neighbor_property_street" step="any">
</div>
<div class="form-group">
    <label for="p_apof_neighbor_property_city">Apof Neighbor Property City</label>
    <input class="form-control" type="text" id="p_apof_neighbor_property_city" name="p_apof_neighbor_property_city">
</div>
<div class="form-group">
    <label for="p_apof_state_code">Apof State Code</label>
    <input class="form-control" type="text" id="p_apof_state_code" name="p_apof_state_code">
</div>
<div class="form-group">
    <label for="p_apof_neighbor_property_zip">Apof Neighbor Property Zip</label>
    <input class="form-control" type="text" id="p_apof_neighbor_property_zip" name="p_apof_neighbor_property_zip">
</div>
<div class="form-group">
    <label for="p_apof_neighbor_property_deed_book">Apof Neighbor Property Deed Book</label>
    <input class="form-control" type="number" id="p_apof_neighbor_property_deed_book" name="p_apof_neighbor_property_deed_book" step="any">
</div>
<div class="form-group">
    <label for="p_apof_property_street_pg_number">Apof Property Street Pg Number</label>
    <input class="form-control" type="text" id="p_apof_property_street_pg_number" name="p_apof_property_street_pg_number">
</div>
<div class="form-group">
  <label for="p_adjacent_property_owner_street">Owner Street</label>
  <input class="form-control" type="text" id="p_adjacent_property_owner_street" name="p_adjacent_property_owner_street">
</div>
<div class="form-group">
  <label for="p_adjacent_property_owner_city">Owner City</label>
  <input class="form-control" type="text" id="p_adjacent_property_owner_city" name="p_adjacent_property_owner_city">
</div>
<div class="form-group">
  <label for="p_adjacent_state_code">Owner State Code</label>
  <input class="form-control" type="text" id="p_adjacent_state_code" name="p_adjacent_state_code" maxlength="2">
</div>
<div class="form-group">
  <label for="p_adjacent_property_owner_zip">Owner Zip</label>
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
