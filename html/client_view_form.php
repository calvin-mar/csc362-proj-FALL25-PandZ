
<?php
require_once 'config.php';
requireLogin();

if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId();
$form_id = $_GET['id'] ?? 0;

// Verify client owns this form
$stmt = $conn->prepare("
    SELECT f.*, cf.client_id
    FROM forms f
    JOIN client_forms cf ON f.form_id = cf.form_id
    WHERE f.form_id = ? AND cf.client_id = ?
");
$stmt->bind_param("ii", $form_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();

if (!$form) {
    header('Location: client_dashboard.php');
    exit();
}

// Get form-specific details based on form type
$form_details = null;
$form_type_key = str_replace(' ', '_', $form['form_type']);

try {
    switch ($form['form_type']) {
        case 'Administrative Appeal Request':
            $stmt = $conn->prepare("SELECT * FROM administrative_appeal_requests WHERE form_id = ?");
            $stmt->execute([$form_id]);
            $form_details = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'Variance Applicatioin':
            $stmt = $conn->prepare("SELECT * FROM variance_applications WHERE form_id = ?");
            $stmt->execute([$form_id]);
            $form_details = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'Zoning Verification Application':
            $stmt = $conn->prepare("SELECT * FROM zoning_verification_letter WHERE form_id = ?");
            $stmt->execute([$form_id]);
            $form_details = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'Conditional Use Permit Application':
            $stmt = $conn->prepare("SELECT * FROM conditional_use_permit_applications WHERE form_id = ?");
            $stmt->execute([$form_id]);
            $form_details = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'Zoning Map Amendment Application':
            $stmt = $conn->prepare("SELECT * FROM zoning_map_amendment_applications WHERE form_id = ?");
            $stmt->execute([$form_id]);
            $form_details = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
    }
} catch(PDOException $e) {
    $error = "Error loading form details";
}

// Get department interactions
$stmt = $conn->prepare("
    SELECT dfi.department_form_interaction_description, dfi.department_form_interaction_id, d.department_name
    FROM department_form_interactions dfi
    LEFT JOIN departments d ON dfi.department_id = d.department_id
    WHERE dfi.form_id = ?
    ORDER BY dfi.department_form_interaction_id DESC
");
$stmt->execute([$form_id]);
$interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Form Details</h1>
        <a href="client_dashboard.php">Back to Dashboard</a>
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
        
        <?php if ($form_details): ?>
            <div class="card">
                <h2>Form-Specific Details</h2>
                <div class="detail-grid">
                    <?php foreach ($form_details as $key => $value): ?>
                        <?php if ($key != 'form_id' && $value): ?>
                            <div class="detail-label"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($value); ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
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
    </div>
</body>
</html>