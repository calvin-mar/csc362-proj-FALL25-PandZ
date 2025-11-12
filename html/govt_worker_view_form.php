<?php
require_once 'config.php';
requireLogin();

if (getUserType() != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$form_id = $_GET['id'] ?? 0;

// Validate form_id
if (!is_numeric($form_id) || $form_id <= 0) {
    header('Location: govt_worker_dashboard.php');
    exit();
}

// Get form summary using view
$stmt = $conn->prepare("SELECT * FROM vw_form_summary WHERE form_id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();

if (!$form) {
    header('Location: govt_worker_dashboard.php?error=form_not_found');
    exit();
}

// Get client info using view
$stmt = $conn->prepare("SELECT * FROM vw_form_clients WHERE form_id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$clients = $result->fetch_all(MYSQLI_ASSOC);

// Get form-specific details based on form type
$form_details = null;
$appellants = [];
$error = null;

try {
    switch ($form['form_type']) {
        case 'Administrative Appeal Request':
            $stmt = $conn->prepare("SELECT * FROM vw_administrative_appeal_details WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $form_details = $result->fetch_assoc();
            
            // Get individual appellants
            $stmt = $conn->prepare("SELECT * FROM vw_appeal_appellants WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $appellants = $result->fetch_all(MYSQLI_ASSOC);
            break;
            
        case 'Variance Application':
            $stmt = $conn->prepare("SELECT * FROM vw_variance_application_details WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $form_details = $result->fetch_assoc();
            break;
            
        case 'Zoning Verification Application':
            $stmt = $conn->prepare("SELECT * FROM vw_zoning_verification_details WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $form_details = $result->fetch_assoc();
            break;
            
        case 'Major Subdivision Plat Application':
            $stmt = $conn->prepare("SELECT * FROM vw_major_subdivision_details WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $form_details = $result->fetch_assoc();
            break;
            
        case 'Zoning Permit Application':
            $stmt = $conn->prepare("SELECT * FROM vw_zoning_permit_details WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $form_details = $result->fetch_assoc();
            break;
    }
} catch(mysqli_sql_exception $e) {
    $error = "Error loading form details: " . $e->getMessage();
}

// Get department interactions using view
$stmt = $conn->prepare("
    SELECT * FROM vw_department_interactions 
    WHERE form_id = ? 
");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$interactions = $result->fetch_all(MYSQLI_ASSOC);

// Get corrections using view
$stmt = $conn->prepare("
    SELECT * FROM vw_form_corrections 
    WHERE form_id = ?
");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$corrections = $result->fetch_all(MYSQLI_ASSOC);

/**
 * Helper function to format field names for display
 */
function formatFieldName($fieldName) {
    // Remove common prefixes
    $fieldName = preg_replace('/^(va_|zvl_|mspa_|zpa_|aar_|zva_)/', '', $fieldName);
    
    // Convert underscores to spaces and capitalize words
    return ucwords(str_replace('_', ' ', $fieldName));
}

/**
 * Helper function to check if field should be displayed
 */
function shouldDisplayField($key, $value) {
    // Skip form_id and null/empty values
    if ($key === 'form_id' || $value === null || $value === '') {
        return false;
    }
    
    // Skip ID fields that end with _id
    if (preg_match('/_id$/', $key)) {
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
    <title>View Form Details - Government Worker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #721c24;
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
        .interaction-item, .correction-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #dc3545;
        }
        .interaction-header, .correction-header {
            font-weight: 600;
            color: #dc3545;
            margin-bottom: 5px;
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
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
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
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Form Details - ID: <?php echo htmlspecialchars($form_id); ?></h1>
        <a href="govt_worker_dashboard.php">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Basic Information Card -->
        <div class="card">
            <h2>Basic Information</h2>
            <div class="detail-grid">
                <div class="detail-label">Form ID:</div>
                <div class="detail-value"><?php echo htmlspecialchars($form['form_id']); ?></div>
                
                <div class="detail-label">Form Type:</div>
                <div class="detail-value"><?php echo htmlspecialchars($form['form_type']); ?></div>
                
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
                
                <?php if ($form['correction_form_id']): ?>
                    <div class="detail-label">Correction Form ID:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($form['correction_form_id']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Associated Clients Card -->
        <?php if (count($clients) > 0): ?>
            <div class="card">
                <h2>Associated Clients</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                                <td><?php echo htmlspecialchars($client['client_username']); ?></td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($client['client_first_name'] . ' ' . $client['client_last_name']); 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($client['client_email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <!-- Form-Specific Details Card -->
        <?php if ($form_details): ?>
            <div class="card">
                <h2>Form-Specific Details</h2>
                <div class="detail-grid">
                    <?php foreach ($form_details as $key => $value): ?>
                        <?php if (shouldDisplayField($key, $value)): ?>
                            <div class="detail-label"><?php echo htmlspecialchars(formatFieldName($key)); ?>:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($value); ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Appellants Card (for Administrative Appeals) -->
        <?php if (count($appellants) > 0): ?>
            <div class="card">
                <h2>Appellants</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appellants as $appellant): ?>
                            <tr>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($appellant['aar_first_name'] . ' ' . $appellant['aar_last_name']); 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($appellant['aar_address'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($appellant['aar_phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($appellant['aar_email'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <!-- Department Interactions Card -->
        <?php if (count($interactions) > 0): ?>
            <div class="card">
                <h2>Department Interactions <span class="metric-badge"><?php echo count($interactions); ?> total</span></h2>
                <?php foreach ($interactions as $interaction): ?>
                    <div class="interaction-item">
                        <div class="interaction-header">
                            <span><?php echo htmlspecialchars($interaction['department_name'] ?? 'Department'); ?></span>
                            <span class="interaction-date">
                                <?php echo htmlspecialchars($interaction['department_form_interaction_datetime'] ?? ''); ?>
                            </span>
                        </div>
                        <div class="interaction-text">
                            <?php echo nl2br(htmlspecialchars($interaction['department_form_interaction_description'] ?? 'No description')); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>Department Interactions</h2>
                <div class="empty-state">No department interactions recorded for this form.</div>
            </div>
        <?php endif; ?>
        
 <!-- Corrections Card -->
<?php if (count($corrections) > 0): ?>
    <div class="card">
        <h2>Corrections <span class="metric-badge"><?php echo count($corrections); ?> total</span></h2>
        <?php foreach ($corrections as $correction): ?>
            <div class="correction-item">
                <div class="correction-header">
                    <span>Reviewer: <?php echo htmlspecialchars($correction['correction_box_reviewer'] ?? 'N/A'); ?></span>
                    <span class="correction-date">
                        <?php echo htmlspecialchars($correction['correction_box_datetime'] ?? ''); ?>
                    </span>
                </div>
                <div class="correction-text">
                    <?php echo nl2br(htmlspecialchars($correction['correction_box_text'] ?? 'No text provided')); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card">
        <h2>Corrections</h2>
        <div class="empty-state">No corrections recorded for this form.</div>
    </div>
<?php endif; ?>

<!-- Add Correction Button -->
<div class="text-center" style="margin-top: 20px;">
    <form method="get" action="add_correction.php">
        <input type="hidden" name="form_id" value="<?php echo htmlspecialchars($form_id ?? ''); ?>">
        <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 500;">
            ➕ Add Correction
        </button>
    </form>
</div>
</body>
</html>