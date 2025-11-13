<?php
// Show all errors from the PHP interpreter.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Show all errors from the MySQLi Extension.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'config.php';
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
        $stmt->bind_param("i", $form_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $form_details = $result->fetch_assoc();
        $stmt->close();
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

// Helper function to format field names
function formatFieldName($key) {
    // Remove common prefixes
    $key = preg_replace('/^(aar_|va_|zva_|cupa_|zmaa_|mspa_|minspa_|gdpa_|sdpa_|flum_|orr_|sp_|zpa_|apof_)/', '', $key);
    
    // Replace underscores with spaces and capitalize
    $name = str_replace('_', ' ', $key);
    $name = ucwords($name);
    
    // Fix common abbreviations
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
    ];
    
    foreach ($replacements as $search => $replace) {
        $name = str_replace($search, $replace, $name);
    }
    
    return trim($name);
}

// Helper function to check if field should be displayed
function shouldDisplayField($key, $value) {
    // Skip internal fields
    $skip_fields = ['form_id', 'form_type', 'form_datetime_submitted', 'form_datetime_resolved', 
                    'form_paid_bool', 'client_id', 'correction_form_id'];
    
    if (in_array($key, $skip_fields)) {
        return false;
    }
    
    // Skip null or empty values
    if ($value === null || $value === '') {
        return false;
    }
    
    return true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Form Details</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { font-size: 24px; }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
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
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .card h3 {
            color: #667eea;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
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
            padding: 15px;
            border-radius: 5px;
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
        .interaction-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        .interaction-header {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .no-data {
            color: #666;
            font-style: italic;
        }
        .text-block {
            background: #fff;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: inherit;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Form Details</h1>
        <a href="client_dashboard.php">Back to Dashboard</a>
    </div>
    <div class="container">
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Basic Information</h2>
            <div class="detail-grid">
                <div class="detail-label">Form ID:</div>
                <div class="detail-value"><?php echo htmlspecialchars($form['form_id']); ?></div>
                
                <div class="detail-label">Form Type:</div>
                <div class="detail-value"><?php echo htmlspecialchars($form['form_type']); ?></div>
                
                <div class="detail-label">Submitted:</div>
                <div class="detail-value"><?php echo htmlspecialchars($form['form_datetime_submitted']); ?></div>
                
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <span class="status-badge <?php echo $form['form_datetime_resolved'] ? 'status-resolved' : 'status-pending'; ?>">
                        <?php echo $form['form_datetime_resolved'] ? 'Resolved' : 'Pending'; ?>
                    </span>
                </div>
                
                <div class="detail-label">Payment:</div>
                <div class="detail-value">
                    <span class="status-badge <?php echo $form['form_paid_bool'] ? 'status-paid' : 'status-unpaid'; ?>">
                        <?php echo $form['form_paid_bool'] ? 'Paid' : 'Unpaid'; ?>
                    </span>
                </div>
                
                <?php if ($form['form_datetime_resolved']): ?>
                    <div class="detail-label">Resolved:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($form['form_datetime_resolved']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($form_details): ?>
            <div class="card">
                <h2>Form-Specific Details</h2>
                
                <?php
                // Group fields by category for better organization
                $field_groups = [
                    'Property Information' => ['property_street', 'property_city', 'property_state', 'property_zip', 
                                               'pva_parcel_number', 'property_acreage', 'property_current_zoning',
                                               'address_street', 'address_city', 'state_code', 'address_zip_code',
                                               'zoning_address_street', 'zoning_address_city', 'zoning_state_code', 'zoning_zip_code',
                                               'property_address_street', 'property_address_city', 'property_state_code', 'property_zip_code'],
                    'Applicant Information' => ['applicants', 'applicant_first_name', 'applicant_last_name', 
                                                'applicant_street', 'applicant_city', 'applicant_phone', 'applicant_email',
                                                'zva_applicant_first_name', 'zva_applicant_last_name', 'zva_applicant_phone_number',
                                                'zva_applicant_fax_number', 'orr_applicant_first_name', 'orr_applicant_last_name',
                                                'orr_applicant_telephone', 'orr_applicant_street', 'orr_applicant_city',
                                                'applicant_state', 'orr_applicant_zip_code'],
                    'Property Owner Information' => ['property_owners', 'owner_first_name', 'owner_last_name',
                                                     'owner_street', 'owner_city', 'owner_phone', 'owner_email',
                                                     'owner_state', 'owner_zip', 'zva_owner_first_name', 'zva_owner_last_name',
                                                     'sp_owner_first_name', 'sp_owner_last_name', 'sp_owner_street',
                                                     'sp_owner_city', 'sp_owner_zip_code'],
                    'Business Information' => ['sp_business_name', 'sp_business_street', 'sp_business_city',
                                              'business_state', 'sp_business_zip_code'],
                    'Contractor Information' => ['sp_contractor_first_name', 'sp_contractor_last_name', 'sp_contractor_phone_number',
                                                'contractor_first_name', 'contractor_last_name', 'contractor_firm',
                                                'contractor_email', 'contractor_phone', 'contractor_cell'],
                    'Neighbor/Adjacent Property' => ['pva_map_code', 'apof_neighbor_property_location', 'apof_neighbor_property_street',
                                                     'apof_neighbor_property_city', 'neighbor_state_code', 'apof_neighbor_property_zip',
                                                     'apof_neighbor_property_deed_book', 'apof_property_street_pg_number',
                                                     'adjacent_property_owner_street', 'adjacent_property_owner_city',
                                                     'owner_state_code', 'adjacent_property_owner_zip', 'appellants'],
                    'Hearing Information' => ['hearing_docket_number', 'hearing_date', 'hearing_date_application_filed',
                                             'hearing_preapp_meeting_date', 'aar_hearing_date', 'aar_submit_date'],
                    'Attorney Information' => ['attorney_first_name', 'attorney_last_name', 'attorney_law_firm',
                                              'attorney_phone', 'attorney_email'],
                    'Professional Services' => ['surveyor_first_name', 'surveyor_last_name', 'surveyor_firm', 'surveyor_phone',
                                               'surveyor_email', 'surveyor_cell',
                                               'engineer_first_name', 'engineer_last_name', 'engineer_firm', 'engineer_phone',
                                               'engineer_email', 'engineer_cell',
                                               'architect_first_name', 'architect_last_name', 'architect_firm', 'architect_phone',
                                               'architect_email', 'architect_cell',
                                               'land_architect_first_name', 'land_architect_last_name', 'land_architect_firm',
                                               'land_architect_email', 'land_architect_phone', 'land_architect_cell'],
                    'Technical Review Dates' => ['technical_app_filing_date', 'technical_review_date',
                                                'technical_prelim_approval_date', 'technical_final_approval_date'],
                    'Permit Details' => ['sp_date', 'sp_permit_number', 'sp_building_coverage_percent', 'sp_permit_fee',
                                        'signs', 'project_type', 'zpa_project_plans', 'zpa_preliminary_site_evaluation'],
                    'Request Details' => ['va_variance_request', 'va_proposed_conditions', 'cupa_permit_request',
                                         'cupa_proposed_conditions', 'zoning_map_amendment_request', 'zmaa_proposed_conditions',
                                         'gdpa_plan_amendment_request', 'gdpa_proposed_conditions', 'site_plan_request',
                                         'future_land_use_map_amendment_prop', 'findings_explanation', 'required_findings_type',
                                         'aar_official_decision', 'aar_relevant_provisions', 'zva_letter_content',
                                         'orr_commercial_purpose', 'orr_request_for_copies', 'orr_received_on_datetime',
                                         'orr_receivable_datetime', 'orr_denied_reasons', 'requested_records'],
                    'Subdivision Documents' => ['mspa_topographic_survey', 'mspa_proposed_plot_layout', 'mspa_plat_restrictions',
                                              'mspa_property_owner_convenants', 'mspa_association_covenants', 'mspa_master_deed',
                                              'mspa_construction_plans', 'mspa_traffic_impact_study', 'mspa_geologic_study',
                                              'mspa_drainage_plan', 'mspa_pavement_design', 'mspa_SWPPP_EPSC_plan',
                                              'mspa_construction_bond_est', 'minspa_topographic_survey', 'minspa_proposed_plot_layout',
                                              'minspa_plat_restrictions', 'minspa_property_owner_covenants',
                                              'minspa_association_covenants', 'minspa_master_deed'],
                    'Additional Information' => [] // Catch-all for remaining fields
                ];
                
                $displayed_fields = [];
                
                // Display grouped fields
                foreach ($field_groups as $group_name => $group_fields) {
                    if ($group_name === 'Additional Information') continue; // Handle this last
                    
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
                            if (strlen($value) > 100 || strpos($value, "\n") !== false) {
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
                        
                        // Handle long text fields differently
                        if (strlen($value) > 100 || strpos($value, "\n") !== false) {
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
                
                // If no details at all
                if (!$group_has_data && !$additional_has_data) {
                    echo "<p class='no-data'>No additional details available for this form.</p>";
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Department Interactions</h2>
            <?php if (count($interactions) > 0): ?>
                <?php foreach ($interactions as $interaction): ?>
                    <div class="interaction-item">
                        <div class="interaction-header">
                            <?php echo htmlspecialchars($interaction['department_name'] ?? 'Department'); ?>
                        </div>
                        <div><?php echo nl2br(htmlspecialchars($interaction['department_form_interaction_description'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">No department interactions recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>