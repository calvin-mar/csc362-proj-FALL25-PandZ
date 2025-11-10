
<?php
require_once 'config.php';
requireLogin();

if (getUserType() != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$form_id = $_GET['id'] ?? 0;

// Get form basic info
$stmt = $conn->prepare("SELECT * FROM forms WHERE form_id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$form = $stmt->fetch_all(MYSQLI_ASSOC);

if (!$form) {
    header('Location: govt_worker_dashboard.php');
    exit();
}

// Get client info
$stmt = $conn->prepare("
    SELECT c.client_id, c.client_username
    FROM clients c
    JOIN client_forms cf ON c.client_id = cf.client_id
    WHERE cf.form_id = ?
");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$clients = $stmt->fetch_all(MYSQLI_ASSOC);

// Get form-specific details
$form_details = null;
try {
    switch ($form['form_type']) {
        case 'Administrative Appeal Request':
            $stmt = $conn->prepare("SELECT * FROM administrative_appeal_requests WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $form_details = $stmt->fetch_all(MYSQLI_ASSOC);
            
            // Get appellants
            $stmt = $conn->prepare("
                SELECT a.* FROM aar_appellants a
                JOIN administrative_appellants aa ON a.aar_appellant_id = aa.aar_appellant_id
                WHERE aa.form_id = ?
            ");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $appellants = $stmt->fetch_all(MYSQLI_ASSOC);
            break;
            
        case 'Variance Applicatioin':
            $stmt = $conn->prepare("SELECT * FROM variance_applications WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $form_details = $stmt->fetch_all(MYSQLI_ASSOC);
            break;
            
        case 'Zoning Verification Application':
            $stmt = $conn->prepare("SELECT * FROM zoning_verification_letter WHERE form_id = ?");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $form_details = $stmt->fetch_all(MYSQLI_ASSOC);
            
            // Get applicant
            $stmt = $conn->prepare("SELECT * FROM zva_applicants LIMIT 1");
            $stmt->execute();
            $zva_applicant = $stmt->fetch_all(MYSQLI_ASSOC);
            break;
            
        case 'Major Subdivision Plat Application':
            $stmt = $conn->prepare("
                SELECT m.*, s.surveyor_first_name, s.surveyor_last_name, s.surveyor_firm,
                       e.engineer_first_name, e.engineer_last_name, e.engineer_firm
                FROM major_subdivision_plat_applications m
                LEFT JOIN surveyors s ON m.surveyor_id = s.surveyor_id
                LEFT JOIN engineers e ON m.engineer_id = e.engineer_id
                WHERE m.form_id = ?
            ");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $form_details = $stmt->fetch_all(MYSQLI_ASSOC);
            break;
            
        case 'Zoning Permit Application':
            $stmt = $conn->prepare("
                SELECT z.*, s.surveyor_first_name, s.surveyor_last_name,
                       a.architect_first_name, a.architect_last_name,
                       l.land_architect_first_name, l.land_architect_last_name,
                       c.contractor_first_name, c.contractor_last_name
                FROM zoning_permit_applications z
                LEFT JOIN surveyors s ON z.surveyor_id = s.surveyor_id
                LEFT JOIN architects a ON z.architect_id = a.architect_id
                LEFT JOIN land_architects l ON z.land_architect_id = l.land_architect_id
                LEFT JOIN contractors c ON z.contractor_id = c.contractor_id
                WHERE z.form_id = ?
            ");
            $stmt->bind_param("i", $form_id);
            $stmt->execute();
            $form_details = $stmt->fetch_all(MYSQLI_ASSOC);
            break;
    }
} catch(mysqli_sql_exception $e) {
    $error = "Error loading form details: " . $e->getMessage();
}

// Get department interactions
$stmt = $conn->prepare("
    SELECT dfi.*, d.department_name
    FROM department_form_interactions dfi
    LEFT JOIN departments d ON dfi.department_id = d.department_id
    WHERE dfi.form_id = ?
    ORDER BY dfi.department_form_interaction_id DESC
");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$interactions = $stmt->fetch_all(MYSQLI_ASSOC);

// Get correction forms
$stmt = $conn->prepare("
    SELECT cf.*, cb.correction_box_reviewer, cb.correction_box_text
    FROM correction_forms cf
    LEFT JOIN correction_boxes cb ON cf.correction_form_id = cb.correction_form_id
    WHERE cf.correction_form_id = (SELECT correction_form_id FROM forms WHERE form_id = ?)
");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$corrections = $stmt->fetch_all(MYSQLI_ASSOC);
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
            max-width: 1200px;
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
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
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
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Form Details - ID: <?php echo htmlspecialchars($form_id); ?></h1>
        <a href="govt_worker_dashboard.php">Back to Dashboard</a>
    </div>
    <div class="container">
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
        
        <?php if (count($clients) > 0): ?>
            <div class="card">
                <h2>Associated Clients</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Username</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                                <td><?php echo htmlspecialchars($client['client_username']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <?php if ($form_details): ?>
            <div class="card">
                <h2>Form-Specific Details</h2>
                <div class="detail-grid">
                    <?php foreach ($form_details as $key => $value): ?>
                        <?php if ($key != 'form_id' && $value !== null && $value !== ''): ?>
                            <div class="detail-label"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($value); ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($appellants) && count($appellants) > 0): ?>
            <div class="card">
                <h2>Appellants</h2>
                <table>
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appellants as $appellant): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appellant['aar_first_name']); ?></td>
                                <td><?php echo htmlspecialchars($appellant['aar_last_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <?php if (count($interactions) > 0): ?>
            <div class="card">
                <h2>Department Interactions</h2>
                <?php foreach ($interactions as $interaction): ?>
                    <div class="interaction-item">
                        <div class="interaction-header">
                            <?php echo htmlspecialchars($interaction['department_name'] ?? 'Department'); ?>
                        </div>
                        <div><?php echo htmlspecialchars($interaction['department_form_interaction_description']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (count($corrections) > 0): ?>
            <div class="card">
                <h2>Corrections</h2>
                <?php foreach ($corrections as $correction): ?>
                    <div class="correction-item">
                        <div class="correction-header">
                            Reviewer: <?php echo htmlspecialchars($correction['correction_box_reviewer'] ?? 'N/A'); ?>
                        </div>
                        <div><?php echo htmlspecialchars($correction['correction_box_text'] ?? 'No text provided'); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>