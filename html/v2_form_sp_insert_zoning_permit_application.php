<?php
/**
 * Refactored Zoning Permit Application Form Handler
 * Replace the existing POST handling in form_sp_insert_zoning_permit_application.php
 * with this code block (lines 11-99 in original file)
 */

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'zoning_form_functions.php';  // ← THIS IS CRITICAL!
requireLogin();

if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extract form data
        $formData = extractZoningPermitFormData($_POST, $_FILES);
        
        // Validate form data
        $errors = validateZoningPermitData($formData);
        
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            // Process file uploads
            $uploadDir = 'uploads/';
            $uploadedFiles = processZoningPermitFileUploads($_FILES, $uploadDir);
            
            // Merge uploaded file paths into form data
            $formData = array_merge($formData, $uploadedFiles);
            
            // Insert application
            $result = insertZoningPermitApplication($conn, $formData);
            
            if ($result['success']) {
                $success = $result['message'];
                if ($result['form_id']) {
                    $success .= " Form ID: " . $result['form_id'];
                }
            } else {
                $error = $result['message'];
            }
        }
    } catch (Exception $e) {
        $error = 'An error occurred: ' . $e->getMessage();
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