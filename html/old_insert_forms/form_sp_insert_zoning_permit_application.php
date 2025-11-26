<?php
require_once 'config.php';
requireLogin();
if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}
$conn = getDBConnection();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file uploads
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    function uploadFile($fieldName, $uploadDir) {
        if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES[$fieldName]['name']);
            $targetPath = $uploadDir . time() . '_' . $fileName;
            if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
                return $targetPath;
            }
        }
        return null;
    }

    $projectPlansFile = uploadFile('project_plans', $uploadDir);
    $landscapePlansFile = uploadFile('landscape_plans', $uploadDir);
    $verificationFile = uploadFile('verification_file', $uploadDir);
    $siteEvaluationFile = uploadFile('site_evaluation_file', $uploadDir);
    $additionalDocsFile = uploadFile('additional_docs', $uploadDir);

    // Collect form data
    $application_date = $_POST['application_date'] ?? null;
    $construction_start_date = $_POST['construction_start_date'] ?? null;
    $permit_number = $_POST['permit_number'] ?? null;

    $applicant_name = $_POST['applicant_name'] ?? null;
    $applicant_address = $_POST['applicant_address'] ?? null;
    $applicant_phone = $_POST['applicant_phone'] ?? null;
    $applicant_cell = $_POST['applicant_cell'] ?? null;
    $applicant_email = $_POST['applicant_email'] ?? null;

    $owner_name = $_POST['owner_name'] ?? null;
    $owner_address = $_POST['owner_address'] ?? null;
    $owner_phone = $_POST['owner_phone'] ?? null;
    $owner_cell = $_POST['owner_cell'] ?? null;
    $owner_email = $_POST['owner_email'] ?? null;

    $surveyor = $_POST['surveyor'] ?? null;
    $contractor = $_POST['contractor'] ?? null;
    $architect = $_POST['architect'] ?? null;
    $landscape_architect = $_POST['landscape_architect'] ?? null;

    $property_address = $_POST['property_address'] ?? null;
    $pva_number = $_POST['pva_number'] ?? null;
    $acreage = $_POST['acreage'] ?? null;
    $current_zoning = $_POST['current_zoning'] ?? null;
    $project_type = $_POST['project_type'] ?? null;

    $structure_type = $_POST['structure_type'] ?? null;
    $square_feet = $_POST['square_feet'] ?? null;
    $project_value = $_POST['project_value'] ?? null;

    // Insert logic (adjust stored procedure or use INSERT)
    $sql = "CALL sp_insert_zoning_permit_application(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = 'Prepare failed: ' . $conn->error;
    } else {
        $stmt->bind_param(
            'sssssssssssssssssssssssss',
            $application_date,
            $construction_start_date,
            $applicant_address,
            $applicant_phone,
            $applicant_cell,
            $applicant_email,
            $owner_name,
            $owner_address,
            $owner_phone,
            $owner_cell,
            $owner_email,
            $surveyor,
            $contractor,
            $architect,
            $landscape_architect,
            $property_address,
            $pva_number,
            $acreage,
            $current_zoning,
           project_type,
            $structure_type,
            $square_feet,
            $project_value
        );
        if (!$stmt->execute()) {
            $error = 'Execute failed: ' . $stmt->error;
        } else {
            $success = 'Application submitted successfully!';
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
    <style>
        body { background: #f8f9fa; }
        .section-title { background: #6a1b9a; color: #fff; padding: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark" style="background-color:#6a1b9a;">
    <div class="container">
        <span class="navbar-brand mb-0 h1">Client Portal – Planning & Zoning</span>
    </div>
</nav>
<div class="container py-4">
    <h1>Zoning Permit Application</h1>
    <p><a href="client_new_form.php">← Back to form selector</a></p>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <!-- Application Details -->
        <div class="section-title">Application Details</div>
        <div class="form-row">
            <div class="form-group col-md-4"><label>Application Date</label><input type="date" name="application_date" class="form-control"></div>
            <div class="form-group col-md-4"><label>Construction Start Date</label><input type="date" name="construction_start_date" class="form-control"></div>
            <div class="form-group col-md-4"><label>Permit Number</label><input type="text" name="permit_number" class="form-control"></div>
        </div>

        <!-- Applicant Info -->
        <div class="section-title">Applicant Information</div>
        <div class="form-group"><label>Name</label><input type="text" name="applicant_name" class="form-control"></div>
        <div class="form-group"><label>Mailing Address</label><input type="text" name="applicant_address" class="form-control"></div>
        <div class="form-row">
            <div class="form-group col-md-4"><label>Phone</label><input type="text" name="applicant_phone" class="form-control"></div>
            <div class="form-group col-md-4"><label>Cell</label><input type="text" name="applicant_cell" class="form-control"></div>
            <div class="form-group col-md-4"><label>Email</label><input type="email" name="applicant_email" class="form-control"></div>
        </div>

        <!-- Property Owner Info -->
        <div class="section-title">Property Owner Information</div>
        <div class="form-group"><label>Name</label><input type="text" name="owner_name" class="form-control"></div>
        <div class="form-group"><label>Mailing Address</label><input type="text" name="owner_address" class="form-control"></div>
        <div class="form-row">
            <div class="form-group col-md-4"><label>Phone</label><input type="text" name="owner_phone" class="form-control"></div>
            <div class="form-group col-md-4"><label>Cell</label><input type="text" name="owner_cell" class="form-control"></div>
            <div class="form-group col-md-4"><label>Email</label><input type="email" name="owner_email" class="form-control"></div>
        </div>

        <!-- Professionals -->
        <div class="section-title">Professional Contacts</div>
        <div class="form-group"><label>Surveyor/Engineer</label><input type="text" name="surveyor" class="form-control"></div>
        <div class="form-group"><label>Contractor</label><input type="text" name="contractor" class="form-control"></div>
        <div class="form-group"><label>Architect</label><input type="text" name="architect" class="form-control"></div>
        <div class="form-group"><label>Landscape Architect</label><input type="text" name="landscape_architect" class="form-control"></div>

        <!-- Property Info -->
        <div class="section-title">Property Information</div>
        <div class="form-group"><label>Property Address</label><input type="text" name="property_address" class="form-control"></div>
        <div class="form-row">
            <div class="form-group col-md-3"><label>PVA Parcel Number</label><input type="text" name="pva_number" class="form-control"></div>
            <div class="form-group col-md-3"><label>Acreage</label><input type="text" name="acreage" class="form-control"></div>
            <div class="form-group col-md-3"><label>Current Zoning</label><input type="text" name="current_zoning" class="form-control"></div>
            <div class="form-group col-md-3"><label>Project Type</label>
                <select name="project_type" class="form-control">
                    <option>Multi-Family</option>
                    <option>Commercial</option>
                    <option>Industrial</option>
                    <option>Temporary Use</option>
                    <option>Parking/Display</option>
                    <option>Use Change</option>
                </select>
            </div>
        </div>

        <!-- Construction Info -->
        <div class="section-title">Construction Information</div>
        <div class="form-row">
            <div class="form-group col-md-4"><label>Type of Structure/Use</label><input type="text" name="structure_type" class="form-control"></div>
            <div class="form-group col-md-4"><label>Square Feet</label><input type="text" name="square_feet" class="form-control"></div>
            <div class="form-group col-md-4"><label>Project Value</label><input type="text" name="project_value" class="form-control"></div>
        </div>

        <!-- File Uploads -->
        <div class="section-title">Upload Documents</div>
        <div class="form-group"><label>Project Plans</label><input type="file" name="project_plans" class="form-control-file"></div>
        <div class="form-group"><label>Landscape/Drainage Plans</label><input type="file" name="landscape_plans" class="form-control-file"></div>
        <div class="form-group"><label>Water/Sewer/Floodplain Verification</label><input type="file" name="verification_file" class="form-control-file"></div>
        <div class="form-group"><label>Preliminary Site Evaluation</label><input type="file" name="site_evaluation_file" class="form-control-file"></div>
        <div class="form-group"><label>Additional Supporting Documents</label><input type="file" name="additional_docs" class="form-control-file"></div>

        <!-- Certification -->
        <div class="section-title">Certification</div>
        <p>I certify that all information provided is true and correct.</p>

        <button type="submit" class="btn btn-primary mt-3">Submit Application</button>
    </form>
</div>
</body>
</html>