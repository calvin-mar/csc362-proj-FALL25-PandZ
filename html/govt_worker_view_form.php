<?php
// Show all errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Show all errors from MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'config.php';
requireLogin();

// Verify user is a government worker
if (getUserType() != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$form_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validate form_id
if ($form_id === false || $form_id === null || $form_id <= 0) {
    header('Location: govt_worker_dashboard.php?error=invalid_form_id');
    exit();
}

// Handle payment status update
$payment_success = '';
$payment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_payment') {
        try {
            $paid_bool = isset($_POST['form_paid_bool']) && $_POST['form_paid_bool'] == '1' ? 1 : 0;
            $payment_method = !empty($_POST['payment_method']) ? $_POST['payment_method'] : null;
            
            // Update the form payment status only
            $update_stmt = $conn->prepare("
                UPDATE forms 
                SET form_paid_bool = ?
                WHERE form_id = ?
            ");
            $update_stmt->bind_param("ii", $paid_bool, $form_id);
            
            if ($update_stmt->execute()) {
                // If you have a payment_method field in your database, update it here
                // This would require adding the field to your forms table or a related table
                
                $payment_success = "Payment status updated successfully!";
            } else {
                $payment_error = "Failed to update payment status.";
            }
            $update_stmt->close();
            
        } catch (Exception $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            $payment_error = "An error occurred while updating payment status.";
        }
    } elseif ($_POST['action'] === 'mark_resolved') {
        try {
            $datetime_resolved = !empty($_POST['form_datetime_resolved']) ? $_POST['form_datetime_resolved'] : date('Y-m-d H:i:s');
            
            // Update the form resolved status
            $update_stmt = $conn->prepare("
                UPDATE forms 
                SET form_datetime_resolved = ?
                WHERE form_id = ?
            ");
            $update_stmt->bind_param("si", $datetime_resolved, $form_id);
            
            if ($update_stmt->execute()) {
                $payment_success = "Form marked as resolved successfully!";
            } else {
                $payment_error = "Failed to mark form as resolved.";
            }
            $update_stmt->close();
            
        } catch (Exception $e) {
            error_log("Error marking form as resolved: " . $e->getMessage());
            $payment_error = "An error occurred while marking form as resolved.";
        }
    } elseif ($_POST['action'] === 'mark_unresolved') {
        try {
            // Set form_datetime_resolved to NULL to mark as unresolved
            $update_stmt = $conn->prepare("
                UPDATE forms 
                SET form_datetime_resolved = NULL
                WHERE form_id = ?
            ");
            $update_stmt->bind_param("i", $form_id);
            
            if ($update_stmt->execute()) {
                $payment_success = "Form marked as unresolved successfully!";
            } else {
                $payment_error = "Failed to mark form as unresolved.";
            }
            $update_stmt->close();
            
        } catch (Exception $e) {
            error_log("Error marking form as unresolved: " . $e->getMessage());
            $payment_error = "An error occurred while marking form as unresolved.";
        }
    }
}

// Get basic form information using the summary view
$stmt = $conn->prepare("SELECT * FROM vw_form_summary_with_client WHERE form_id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();
$stmt->close();

if (!$form) {
    header('Location: govt_worker_dashboard.php?error=form_not_found');
    exit();
}

// Initialize variables
$form_details = null;
$error = null;
$view_name = '';

// Get form-specific details based on form type using the complete views
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
        $stmt->bind_param("i", $form_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $form_details = $result->fetch_assoc();
        $stmt->close();
    }
} catch(Exception $e) {
    error_log("Error loading form details from view {$view_name}: " . $e->getMessage());
    $error = "Error loading form details. The database view may not exist.";
}

// Get department interactions using view
$interactions = [];
try {
    $stmt = $conn->prepare("
        SELECT * FROM vw_department_interactions 
        WHERE form_id = ? 
        ORDER BY department_form_interaction_id DESC
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

// Get corrections using view
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

/**
 * Helper function to format field names for display
 */
function formatFieldName($fieldName) {
    // Remove common prefixes
    $fieldName = preg_replace('/^(va_|zvl_|mspa_|minspa_|zpa_|aar_|zva_|cupa_|zmaa_|gdpa_|sdpa_|flum_|orr_|sp_|apof_)/', '', $fieldName);
    
    // Convert underscores to spaces and capitalize words
    $name = str_replace('_', ' ', $fieldName);
    $name = ucwords($name);
    
    // Fix common abbreviations and acronyms
    $replacements = [
        'Pva' => 'PVA',
        'Id' => 'ID',
        'Datetime' => 'Date/Time',
        'Bool' => '',
        'Gdp' => 'GDP',
        'Flum' => 'FLUM',
        'Zmaa' => 'ZMAA',
        'Cupa' => 'CUP',
        'Swppp' => 'SWPPP',
        'Epsc' => 'EPSC',
        'Orr' => 'ORR',
        'Zva' => 'ZVA',
        'Apof' => 'APOF',
        'Mspa' => 'Major Subdivision',
        'Minspa' => 'Minor Subdivision',
    ];
    
    foreach ($replacements as $search => $replace) {
        $name = str_replace($search, $replace, $name);
    }
    
    return trim($name);
}

/**
 * Helper function to check if field should be displayed
 */
function shouldDisplayField($key, $value) {
    // Skip internal/system fields
    $skip_fields = [
        'form_id', 'form_type', 'form_datetime_submitted', 'form_datetime_resolved',
        'form_paid_bool', 'client_id', 'correction_form_id', 'form_status', 
        'payment_status', 'days_since_submission', 'days_to_resolve',
        'client_username', 'client_type', 'has_corrections'
    ];
    
    if (in_array($key, $skip_fields)) {
        return false;
    }
    
    // Skip ID fields
    if (preg_match('/_id$/', $key)) {
        return false;
    }
    
    // Skip null or empty values
    if ($value === null || $value === '') {
        return false;
    }
    
    return true;
}

/**
 * Helper function to determine if value is long text
 */
function isLongText($value) {
    return strlen($value) > 150 || substr_count($value, "\n") > 2;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Form Details - Government Worker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .navbar {
            background: #dc3545;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar h1 { 
            font-size: 24px;
            font-weight: 600;
        }
        .navbar-links {
            display: flex;
            gap: 10px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
            font-size: 14px;
        }
        .navbar a:hover { 
            background: rgba(255,255,255,0.3); 
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            font-weight: 500;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            font-weight: 500;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
            font-size: 20px;
            font-weight: 600;
        }
        .card h3 {
            color: #dc3545;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        .detail-value {
            color: #333;
            word-break: break-word;
        }
        .detail-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
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
        .metric-badge {
            display: inline-block;
            background: #e9ecef;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 13px;
            color: #495057;
            margin-left: 10px;
        }
        .interaction-item, .correction-item {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 12px;
            border-left: 4px solid #dc3545;
        }
        .interaction-header, .correction-header {
            font-weight: 600;
            color: #dc3545;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .interaction-date, .correction-date {
            font-size: 12px;
            color: #666;
            font-weight: normal;
        }
        .interaction-text, .correction-text {
            color: #333;
            line-height: 1.6;
            margin-top: 8px;
            white-space: pre-wrap;
        }
        .text-block {
            background: #fff;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            white-space: pre-wrap;
            font-family: inherit;
            line-height: 1.6;
            margin-top: 8px;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        .btn:hover { 
            background: #c82333; 
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .info-box p {
            color: #1976D2;
            margin: 0;
        }
        .payment-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 2px solid #dee2e6;
        }
        .payment-form h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .form-check label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }
        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Form Details - ID: <?php echo htmlspecialchars($form_id); ?></h1>
        <div class="navbar-links">
            <a href="add_correction.php?form_id=<?php echo $form_id; ?>">Add Correction</a>
            <a href="generate_form_pdf.php?form_id=<?php echo $form_id; ?>">Download PDF</a>
            <a href="govt_worker_dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($payment_success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($payment_success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($payment_error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($payment_error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Basic Information Card -->
        <div class="card">
            <h2>Basic Information</h2>
            <div class="detail-grid">
                <div class="detail-label">Form ID:</div>
                <div class="detail-value"><?php echo htmlspecialchars($form['form_id']); ?></div>
                
                <div class="detail-label">Form Type:</div>
                <div class="detail-value"><strong><?php echo htmlspecialchars($form['form_type']); ?></strong></div>
                
                <div class="detail-label">Submitted:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($form['form_datetime_submitted']); ?>
                    <span class="metric-badge"><?php echo $form['days_since_submission']; ?> days ago</span>
                </div>
                
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <span class="status-badge <?php echo $form['form_datetime_resolved'] ? 'status-resolved' : 'status-pending'; ?>">
                        <?php echo htmlspecialchars($form['form_status']); ?>
                    </span>
                </div>
                
                <div class="detail-label">Payment:</div>
                <div class="detail-value">
                    <span class="status-badge <?php echo $form['form_paid_bool'] ? 'status-paid' : 'status-unpaid'; ?>">
                        <?php echo htmlspecialchars($form['payment_status']); ?>
                    </span>
                </div>
                
                <?php if ($form['form_datetime_resolved']): ?>
                    <div class="detail-label">Resolved:</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($form['form_datetime_resolved']); ?>
                        <?php if ($form['days_to_resolve']): ?>
                            <span class="metric-badge">Resolved in <?php echo $form['days_to_resolve']; ?> days</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($form['client_username']): ?>
                    <div class="detail-label">Submitted By:</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($form['client_username']); ?>
                        <span class="metric-badge"><?php echo htmlspecialchars($form['client_type']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Payment Update Form -->
            <div class="payment-form">
                <h3>Update Payment Status</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_payment">
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method:</label>
                        <select class="form-control" name="payment_method" id="payment_method">
                            <option value="">Select payment method...</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="money_order">Money Order</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="form_paid_bool" 
                                   id="form_paid_bool" 
                                   value="1"
                                   <?php echo $form['form_paid_bool'] ? 'checked' : ''; ?>>
                            <label for="form_paid_bool">Mark as Paid</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Update Payment Status</button>
                </form>
            </div>
            
            <!-- Resolved Status Form -->
            <div class="payment-form" style="margin-top: 20px; border-color: #28a745;">
                <h3>Mark Form as Resolved</h3>
                
                <?php if ($form['form_datetime_resolved']): ?>
                    <!-- Form is already resolved - show update and unresolve options -->
                    <form method="POST" action="" style="margin-bottom: 15px;">
                        <input type="hidden" name="action" value="mark_resolved">
                        
                        <div class="form-group">
                            <label for="form_datetime_resolved">Resolved Date & Time:</label>
                            <input type="datetime-local" 
                                   class="form-control" 
                                   name="form_datetime_resolved" 
                                   id="form_datetime_resolved"
                                   value="<?php echo date('Y-m-d\TH:i', strtotime($form['form_datetime_resolved'])); ?>">
                        </div>
                        
                        <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                            This form is currently marked as resolved. You can update the resolution date/time above.
                        </p>
                        
                        <button type="submit" class="btn btn-success"> Update Resolution Date</button>
                    </form>
                    
                    <!-- Add unresolve button -->
                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to mark this form as unresolved? This will remove the resolution date.');">
                        <input type="hidden" name="action" value="mark_unresolved">
                        <button type="submit" class="btn btn-secondary">Mark as Unresolved</button>
                    </form>
                    
                <?php else: ?>
                    <!-- Form is not resolved - show resolve option -->
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="mark_resolved">
                        
                        <div class="form-group">
                            <label for="form_datetime_resolved">Resolved Date & Time:</label>
                            <input type="datetime-local" 
                                   class="form-control" 
                                   name="form_datetime_resolved" 
                                   id="form_datetime_resolved"
                                   value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                        
                        <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                            ℹ️ This will mark the form as complete and set the resolution date/time.
                        </p>
                        
                        <button type="submit" class="btn btn-success">Mark as Resolved</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Form-Specific Details Card -->
        <?php if ($form_details): ?>
            <div class="card">
                <h2>Form-Specific Details</h2>
                
                <?php
                // Group fields by category for better organization
                $field_groups = [
                    'Property Information' => ['property_street', 'property_city', 'property_state', 'property_zip', 
                                               'pva_parcel_number', 'property_acreage', 'property_current_zoning',
                                               'address_street', 'address_city', 'state_code', 'address_zip_code'],
                    'Applicant Information' => ['applicants', 'applicant_name', 'applicant_phone', 'applicant_email',
                                                'applicant_street', 'applicant_city', 'applicant_state', 'applicant_zip'],
                    'Property Owner Information' => ['property_owners', 'owner_first_name', 'owner_last_name',
                                                     'owner_street', 'owner_city', 'owner_state', 'owner_zip'],
                    'Hearing Information' => ['hearing_docket_number', 'hearing_date', 'hearing_date_application_filed',
                                             'hearing_preapp_meeting_date', 'aar_hearing_date', 'aar_submit_date'],
                    'Attorney Information' => ['attorney_first_name', 'attorney_last_name', 'attorney_law_firm',
                                              'attorney_phone', 'attorney_email', 'attorney_cell'],
                    'Professional Services' => ['surveyor_first_name', 'surveyor_last_name', 'surveyor_firm',
                                               'engineer_first_name', 'engineer_last_name', 'engineer_firm',
                                               'architect_first_name', 'architect_last_name', 'architect_firm',
                                               'land_architect_first_name', 'land_architect_last_name', 'land_architect_firm',
                                               'contractor_first_name', 'contractor_last_name'],
                    'Request/Application Details' => ['va_variance_request', 'va_proposed_conditions', 'cupa_permit_request',
                                                      'cupa_proposed_conditions', 'zoning_map_amendment_request',
                                                      'zmaa_proposed_conditions', 'site_plan_request',
                                                      'gdpa_plan_amendment_request', 'gdpa_proposed_conditions',
                                                      'future_land_use_map_amendment_prop', 'required_findings_type',
                                                      'findings_explanation', 'aar_official_decision', 'aar_relevant_provisions'],
                    'Additional Information' => []
                ];
                
                $displayed_fields = [];
                
                // Display grouped fields
                foreach ($field_groups as $group_name => $group_fields) {
                    if ($group_name === 'Additional Information') continue;
                    
                    $group_has_data = false;
                    $group_html = '';
                    
                    foreach ($form_details as $key => $value) {
                        if (in_array(strtolower($key), array_map('strtolower', $group_fields)) && shouldDisplayField($key, $value)) {
                            if (!$group_has_data) {
                                $group_html .= "<h3>{$group_name}</h3><div class='detail-section'><div class='detail-grid'>";
                                $group_has_data = true;
                            }
                            
                            $displayed_fields[] = $key;
                            $formatted_name = formatFieldName($key);
                            $formatted_value = htmlspecialchars($value);
                            
                            // Handle long text fields differently
                            if (isLongText($value)) {
                                $group_html .= "<div class='detail-label' style='grid-column: 1 / -1; margin-top: 10px;'>{$formatted_name}:</div>";
                                $group_html .= "<div class='text-block' style='grid-column: 1 / -1;'>{$formatted_value}</div>";
                            } else {
                                $group_html .= "<div class='detail-label'>{$formatted_name}:</div>";
                                $group_html .= "<div class='detail-value'>{$formatted_value}</div>";
                            }
                        }
                    }
                    
                    if ($group_has_data) {
                        $group_html .= "</div></div>";
                        echo $group_html;
                    }
                }
                
                // Display any remaining fields under "Additional Information"
                $additional_has_data = false;
                $additional_html = '';
                
                foreach ($form_details as $key => $value) {
                    if (!in_array($key, $displayed_fields) && shouldDisplayField($key, $value)) {
                        if (!$additional_has_data) {
                            $additional_html .= "<h3>Additional Information</h3><div class='detail-section'><div class='detail-grid'>";
                            $additional_has_data = true;
                        }
                        
                        $formatted_name = formatFieldName($key);
                        $formatted_value = htmlspecialchars($value);
                        
                        if (isLongText($value)) {
                            $additional_html .= "<div class='detail-label' style='grid-column: 1 / -1; margin-top: 10px;'>{$formatted_name}:</div>";
                            $additional_html .= "<div class='text-block' style='grid-column: 1 / -1;'>{$formatted_value}</div>";
                        } else {
                            $additional_html .= "<div class='detail-label'>{$formatted_name}:</div>";
                            $additional_html .= "<div class='detail-value'>{$formatted_value}</div>";
                        }
                    }
                }
                
                if ($additional_has_data) {
                    $additional_html .= "</div></div>";
                    echo $additional_html;
                }
                ?>
            </div>
        <?php elseif (!$error): ?>
            <div class="info-box">
                <p>No additional form-specific details are available for this form type.</p>
            </div>
        <?php endif; ?>
        
        <!-- Department Interactions Card -->
        <div class="card">
            <h2>Department Interactions 
                <?php if (count($interactions) > 0): ?>
                    <span class="metric-badge"><?php echo count($interactions); ?> total</span>
                <?php endif; ?>
            </h2>
            <?php if (count($interactions) > 0): ?>
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
            <?php else: ?>
                <div class="empty-state">No department interactions recorded for this form.</div>
            <?php endif; ?>
        </div>
        
        <!-- Corrections Card -->
        <div class="card">
            <h2>Corrections 
                <?php if (count($corrections) > 0): ?>
                    <span class="metric-badge"><?php echo count($corrections); ?> total</span>
                <?php endif; ?>
            </h2>
            <?php if (count($corrections) > 0): ?>
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
            <?php else: ?>
                <div class="empty-state">No corrections recorded for this form.</div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="add_correction.php?form_id=<?php echo $form_id; ?>" class="btn">➕ Add Correction</a>
            </div>
        </div>
    </div>
</body>
</html>