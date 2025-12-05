<?php
// Show all errors from the PHP interpreter.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Show all errors from the MySQLi Extension.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'config.php';
require_once 'pdf_generation_functions.php'; // Include the PDF generation functions

requireLogin();

if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId();

// Validate and sanitize form_id
$form_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($form_id === false || $form_id === null || $form_id <= 0) {
    header('Location: client_dashboard.php');
    exit();
}

// Verify client owns this form and get basic info
$stmt = $conn->prepare("
    SELECT f.*, cf.client_id
    FROM forms f
    JOIN client_forms cf ON f.form_id = cf.form_id
    WHERE f.form_id = ? AND cf.client_id = ?
");
$stmt->bind_param("is", $form_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();
$stmt->close();

if (!$form) {
    header('Location: client_dashboard.php');
    exit();
}

// Get form-specific details based on form type using views
$form_details = null;
$error = null;
$view_name = '';

try {
    switch ($form['form_type']) {
        case 'Administrative Appeal Request':
            $view_name = 'vw_administrative_appeal_complete';
            break;
            
        case 'Variance Application':
            $view_name = 'vw_variance_application_complete';
            break;
            
        case 'Zoning Verification Application':
            $view_name = 'vw_zoning_verification_complete';
            break;
            
        case 'Conditional Use Permit Application':
            $view_name = 'vw_conditional_use_permit_complete';
            break;
            
        case 'Zoning Map Amendment Application':
            $view_name = 'vw_zoning_map_amendment_complete';
            break;
            
        case 'Major Subdivision Plat Application':
            $view_name = 'vw_major_subdivision_complete';
            break;
            
        case 'Minor Subdivision Plat Application':
            $view_name = 'vw_minor_subdivision_complete';
            break;
            
        case 'Development Plan Application (General)':
            $view_name = 'vw_general_development_plan_complete';
            break;
            
        case 'Development Plan Application (Site)':
            $view_name = 'vw_site_development_plan_complete';
            break;
            
        case 'Future Land Use Map (FLUM) Application':
            $view_name = 'vw_flum_application_complete';
            break;
            
        case 'Adjacent Property Owners Form':
            $view_name = 'vw_adjacent_property_owners_complete';
            break;
            
        case 'Open Records Request':
            $view_name = 'vw_open_records_request_complete';
            break;
            
        case 'Sign Permit Appplication': // Note: typo in original stored procedure
            $view_name = 'vw_sign_permit_application_complete';
            break;
            
        case 'Zoning Permit Application':
            $view_name = 'vw_zoning_permit_application_complete';
            break;
    }
    
    // Query the appropriate view if one was found
    if ($view_name) {
        $stmt = $conn->prepare("SELECT * FROM {$view_name} WHERE form_id = ?");
        if (!$stmt) {
            error_log("[DEBUG] Failed to prepare view query: " . $conn->error . " | View: $view_name | Form ID: $form_id");
            $error = "Database error: View not available or inaccessible";
        } else {
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            if ($stmt->errno) {
                error_log("[ERROR] View query execution failed: " . $stmt->error . " | View: $view_name | Form ID: $form_id");
            }
            $result = $stmt->get_result();
            $form_details = $result->fetch_assoc();
            if (!$form_details) {
                error_log("[DEBUG] No data returned from view: $view_name | Form ID: $form_id");
            }
            $stmt->close();
        }
    }
} catch(Exception $e) {
    error_log("Error loading form details: " . $e->getMessage());
    $error = "Error loading form details. Please try again later.";
}

// Get department interactions using view
$interactions = [];
try {
    $stmt = $conn->prepare("
        SELECT * FROM vw_department_interactions
        WHERE form_id = ?
        ORDER BY interaction_started DESC
    ");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $interactions[] = $row;
    }
    $stmt->close();
} catch(Exception $e) {
    error_log("Error loading interactions: " . $e->getMessage());
}

// Get corrections if this form has any
$corrections = [];
try {
    if ($form['correction_form_id']) {
        $stmt = $conn->prepare("
            SELECT * FROM vw_correction_forms_detail
            WHERE form_id = ?
            ORDER BY correction_box_id
        ");
        $stmt->bind_param("i", $form_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $corrections[] = $row;
        }
        $stmt->close();
    }
} catch(Exception $e) {
    error_log("Error loading corrections: " . $e->getMessage());
}

$conn->close();

// Generate the formatted HTML view using the PDF generation function
$formatted_html = '';
if ($form_details) {
    $formatted_html = generateFormPDF($form_id, $form['form_type'], $form_details);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Form - <?php echo htmlspecialchars($form['form_type']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .top-navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .top-navbar h1 { 
            font-size: 20px;
            font-weight: 600;
        }
        .top-navbar-links {
            display: flex;
            gap: 10px;
        }
        .top-navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
            font-size: 14px;
        }
        .top-navbar a:hover { 
            background: rgba(255,255,255,0.3); 
        }
        .status-banner {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .status-items {
            display: flex;
            gap: 30px;
            align-items: center;
            flex-wrap: wrap;
        }
        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status-label {
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        .status-resolved {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-correction {
            background: #f8d7da;
            color: #721c24;
        }
        .form-display-container {
            background: white;
            max-width: 900px;
            margin: 0 auto 30px auto;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .interactions-section {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .interactions-section h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            font-size: 20px;
        }
        .interaction-item, .correction-item {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 12px;
            border-left: 4px solid #667eea;
        }
        .interaction-header, .correction-header {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .interaction-text, .correction-text {
            color: #333;
            line-height: 1.6;
            margin-top: 8px;
            white-space: pre-wrap;
        }
        .no-data {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 900px;
            border-left: 4px solid #dc3545;
        }
        .print-button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        .print-button:hover {
            background: #5568d3;
        }
        @media print {
            .top-navbar, .status-banner, .interactions-section, .print-button {
                display: none !important;
            }
            .form-display-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="top-navbar">
        <h1>View Form: <?php echo htmlspecialchars($form['form_type']); ?></h1>
        <div class="top-navbar-links">
            <button class="print-button" onclick="window.print()">Print Form</button>
            <a href="client_dashboard.php">← Back to Dashboard</a>
        </div>
    </div>

    <div class="status-banner">
        <div class="status-items">
            <div class="status-item">
                <span class="status-label">Form ID:</span>
                <span><?php echo htmlspecialchars($form['form_id']); ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Submitted:</span>
                <span><?php echo date('M j, Y g:i A', strtotime($form['form_datetime_submitted'])); ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Status:</span>
                <span class="status-badge <?php echo $form['form_datetime_resolved'] ? 'status-resolved' : 'status-pending'; ?>">
                    <?php echo $form['form_datetime_resolved'] ? 'Resolved' : 'Pending'; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Payment:</span>
                <span class="status-badge <?php echo $form['form_paid_bool'] ? 'status-paid' : 'status-unpaid'; ?>">
                    <?php echo $form['form_paid_bool'] ? 'Paid' : 'Unpaid'; ?>
                </span>
            </div>
            <?php if ($form['correction_form_id']): ?>
            <div class="status-item">
                <span class="status-badge status-correction">Needs Correction</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Display formatted form HTML -->
    <?php if ($formatted_html): ?>
        <div class="form-display-container">
            <h1 style="text-align:center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px;">
                Danville-Boyle County Planning & Zoning Commission<br>
                <span style="font-size: 16px; color: #666;"><?php echo htmlspecialchars($form['form_type']); ?></span>
            </h1>
            <?php echo $formatted_html; ?>
        </div>
    <?php elseif (!$form_details && $view_name): ?>
        <div class="form-display-container">
            <div class="error-message">
                <strong>Form data not available:</strong><br>
                The form details view (<?php echo htmlspecialchars($view_name); ?>) is not returning any data for this form.
                This typically means either:
                <ul>
                    <li>The view does not exist in the database</li>
                    <li>The form has not been submitted with all required information</li>
                    <li>There is a database configuration issue</li>
                </ul>
                Please contact the system administrator if this problem persists.
            </div>
        </div>
    <?php else: ?>
        <div class="form-display-container">
            <p class="no-data">Form details are not available for display.</p>
        </div>
    <?php endif; ?>

    
    <?php if (count($corrections) > 0): ?>
    <div class="interactions-section">
        <h2>Corrections Required</h2>
        <?php foreach ($corrections as $correction): ?>
            <div class="correction-item">
                <div class="correction-header">
                    <span>Reviewer: <?php echo htmlspecialchars($correction['correction_box_reviewer'] ?? 'N/A'); ?></span>
                </div>
                <div class="correction-text">
                    <?php echo nl2br(htmlspecialchars($correction['correction_box_text'] ?? 'No text provided')); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if (count($interactions) > 0): ?>
    <div class="interactions-section">
        <h2>Department Interactions</h2>
        <?php foreach ($interactions as $interaction): ?>
            <div class="interaction-item">
                <div class="interaction-header">
                    <span><?php echo htmlspecialchars($interaction['department_name'] ?? 'Department'); ?></span>
                </div>
                <div class="interaction-text">
                    <?php echo nl2br(htmlspecialchars($interaction['department_form_interaction_description'] ?? 'No description')); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</body>
</html>