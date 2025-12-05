<?php
/**
 * Provide reporting view for a single department user.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'config.php';
requireLogin();

if (getUserType() !== 'department') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$department_id = getUserId();

// Get department name - FIXED: using client_id instead of department_id
$dept_name = 'Department';
$stmt = $conn->prepare("SELECT department_name FROM departments WHERE client_id = ?");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $dept_name = $row['department_name'];
}
$stmt->close();

// ------------------------------------------------------------
// 1) Department-specific KPIs using view
$kpis = [
    'total_interactions' => 0,
    'forms_interacted' => 0,
    'pending_forms' => 0,
    'resolved_forms' => 0
];

$stmt = $conn->prepare("
    SELECT * FROM vw_department_activity_summary 
    WHERE department_id = ?
");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $kpis['total_interactions'] = (int)$row['total_interactions'];
    $kpis['forms_interacted'] = (int)$row['forms_interacted'];
    $kpis['pending_forms'] = (int)$row['pending_forms'];
    $kpis['resolved_forms'] = (int)$row['resolved_forms'];
}
$stmt->close();

// ------------------------------------------------------------
// 2) Recent interactions by this department using view
// FIXED: Removed department_form_interaction_id reference and using interaction_started
$recent_interactions = [];
$stmt = $conn->prepare("
    SELECT * FROM vw_department_recent_interactions 
    WHERE department_id = ?
    ORDER BY interaction_started DESC
    LIMIT 50
");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
$recent_interactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ------------------------------------------------------------
// 3) Form type breakdown for this department using view
$form_type_breakdown = [];
$stmt = $conn->prepare("
    SELECT * FROM vw_department_form_type_breakdown 
    WHERE department_id = ?
    ORDER BY forms_count DESC
");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
$form_type_breakdown = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ------------------------------------------------------------
// 4) Forms pending your department's attention
// FIXED: Using composite key from department_form_interactions
$pending_forms = [];
$stmt = $conn->prepare("
    SELECT 
        p.*,
        COUNT(CASE WHEN dfi.client_id = ? THEN 1 END) as my_interaction_count
    FROM vw_pending_forms_with_dept_interactions p
    LEFT JOIN department_form_interactions dfi ON p.form_id = dfi.form_id
    WHERE dfi.client_id = ? OR dfi.client_id IS NULL
    GROUP BY p.form_id, p.form_type, p.form_datetime_submitted, p.days_pending, 
             p.clients, p.total_interaction_count, p.form_paid_bool, p.correction_form_id
    HAVING my_interaction_count > 0 OR my_interaction_count = 0
    ORDER BY p.days_pending DESC
    LIMIT 100
");
$stmt->bind_param("ii", $department_id, $department_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_forms = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ------------------------------------------------------------
// 5) Monthly activity trend (last 12 months) using view
$monthly_activity = [];
$stmt = $conn->prepare("
    SELECT * FROM vw_department_monthly_activity 
    WHERE department_id = ?
    ORDER BY month ASC
");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
$monthly_activity = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ------------------------------------------------------------
// 6) Compare with other departments using view
$dept_comparison = [];
$stmt = $conn->prepare("
    SELECT 
        *,
        CASE WHEN department_id = ? THEN 1 ELSE 0 END as is_my_dept
    FROM vw_department_comparison
    ORDER BY forms_handled DESC
");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
$dept_comparison = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Department Reports - <?= htmlspecialchars($dept_name); ?></title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f5f5f5; }
    .navbar { background:#28a745; color:#fff; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
    .navbar h1 { font-size:24px; }
    .navbar a { color:#fff; text-decoration:none; padding:8px 15px; background:rgba(255,255,255,0.2); border-radius:5px; transition:background .3s; margin-left:10px; }
    .navbar a:hover { background:rgba(255,255,255,0.3); }
    .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
    .card {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .card h2 {
        color: #333;
        margin-bottom: 20px;
        border-bottom: 2px solid #28a745;
        padding-bottom: 10px;
        font-size: 20px;
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #f8f9fa; font-weight: 600; color: #333; }
    tr:hover { background: #f8f9fa; }
    tr.highlight { background: #e7f4ea; font-weight: 600; }
    .kpis { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:20px; }
    .kpi { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.06); text-align:center; }
    .kpi h3 { font-size:13px; color:#666; margin-bottom:8px; text-transform:uppercase; letter-spacing:.4px; }
    .kpi .value { font-size:32px; font-weight:700; color:#28a745; }
    .kpi.success .value { color:#28a745; }
    .kpi.warning .value { color:#ffc107; }
    .kpi.info .value { color:#17a2b8; }
    .muted { color:#777; font-size:12px; margin-top:4px; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .badge-success { background: #d4edda; color: #155724; }
    .badge-warning { background: #fff3cd; color: #856404; }
    .badge-info { background: #d1ecf1; color: #0c5460; }
    .badge-primary { background: #cce5ff; color: #004085; }
    a { color: #28a745; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .empty-state { text-align: center; padding: 40px; color: #666; font-style: italic; }
    .interaction-preview { 
        max-width: 300px; 
        overflow: hidden; 
        text-overflow: ellipsis; 
        white-space: nowrap; 
    }
</style>
</head>
<body>
<div class="navbar">
    <h1><?= htmlspecialchars($dept_name); ?> - Reports</h1>
    <div>
        <a href="department_dashboard.php">Back to Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <!-- KPI Cards -->
    <div class="kpis">
        <div class="kpi success">
            <h3>Total Interactions</h3>
            <div class="value"><?= number_format($kpis['total_interactions']); ?></div>
            <div class="muted">All-Time</div>
        </div>
        <div class="kpi info">
            <h3>Forms Handled</h3>
            <div class="value"><?= number_format($kpis['forms_interacted']); ?></div>
            <div class="muted">Unique Forms</div>
        </div>
        <div class="kpi warning">
            <h3>Pending Forms</h3>
            <div class="value"><?= number_format($kpis['pending_forms']); ?></div>
            <div class="muted">Still Active</div>
        </div>
        <div class="kpi success">
            <h3>Resolved Forms</h3>
            <div class="value"><?= number_format($kpis['resolved_forms']); ?></div>
            <div class="muted">Completed</div>
        </div>
    </div>

    <!-- Forms Pending Attention -->
    <div class="card">
        <h2>Forms Pending Your Department's Attention (<?= count($pending_forms); ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Form ID</th>
                    <th>Form Type</th>
                    <th>Client(s)</th>
                    <th>Submitted</th>
                    <th>Days Pending</th>
                    <th>Your Interactions</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_forms as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['form_id']); ?></td>
                        <td><?= htmlspecialchars($row['form_type']); ?></td>
                        <td><?= htmlspecialchars($row['clients'] ?? 'N/A'); ?></td>
                        <td><?= htmlspecialchars($row['form_datetime_submitted']); ?></td>
                        <td>
                            <span class="badge <?= $row['days_pending'] > 30 ? 'badge-warning' : 'badge-info'; ?>">
                                <?= $row['days_pending']; ?> days
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-primary">
                                <?= $row['my_interaction_count']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="department_view_form.php?id=<?= urlencode($row['form_id']); ?>">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($pending_forms)): ?>
                    <tr><td colspan="7" class="empty-state">No pending forms to display</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Interactions -->
    <div class="card">
        <h2>Recent Interactions (Last 50)</h2>
        <table>
            <thead>
                <tr>
                    <th>Form ID</th>
                    <th>Form Type</th>
                    <th>Started</th>
                    <th>Interaction</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_interactions as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['form_id']); ?></td>
                        <td><?= htmlspecialchars($row['form_type']); ?></td>
                        <td><?= htmlspecialchars($row['interaction_started']); ?></td>
                        <td>
                            <div class="interaction-preview">
                                <?= htmlspecialchars(substr($row['department_form_interaction_description'] ?? '', 0, 100)); ?>
                                <?= strlen($row['department_form_interaction_description'] ?? '') > 100 ? '...' : ''; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $row['form_status'] === 'Resolved' ? 'badge-success' : 'badge-warning'; ?>">
                                <?= htmlspecialchars($row['form_status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="department_view_form.php?id=<?= urlencode($row['form_id']); ?>">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_interactions)): ?>
                    <tr><td colspan="6" class="empty-state">No interactions recorded yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Form Type Breakdown -->
    <div class="card">
        <h2>Form Type Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Form Type</th>
                    <th>Forms Handled</th>
                    <th>Total Interactions</th>
                    <th>Pending</th>
                    <th>Resolved</th>
                    <th>Avg Interactions/Form</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($form_type_breakdown as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['form_type']); ?></td>
                        <td><?= number_format($row['forms_count']); ?></td>
                        <td><?= number_format($row['interaction_count']); ?></td>
                        <td><?= number_format($row['pending_count']); ?></td>
                        <td><?= number_format($row['resolved_count']); ?></td>
                        <td><?= $row['avg_interactions_per_form']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($form_type_breakdown)): ?>
                    <tr><td colspan="6" class="empty-state">No data available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Monthly Activity Trend -->
    <?php if (!empty($monthly_activity)): ?>
        <div class="card">
            <h2>Monthly Activity Trend (Last 12 Months)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Forms Interacted With</th>
                        <th>Total Interactions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_activity as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['month']); ?></td>
                            <td><?= number_format($row['forms_interacted']); ?></td>
                            <td><?= number_format($row['total_interactions']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Department Comparison -->
    <div class="card">
        <h2>Department Comparison</h2>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Forms Handled</th>
                    <th>Total Interactions</th>
                    <th>Avg Interactions/Form</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dept_comparison as $row): ?>
                    <tr <?= $row['is_my_dept'] ? 'class="highlight"' : ''; ?>>
                        <td>
                            <?= htmlspecialchars($row['department_name']); ?>
                            <?= $row['is_my_dept'] ? ' <span class="badge badge-success">YOU</span>' : ''; ?>
                        </td>
                        <td><?= number_format($row['forms_handled']); ?></td>
                        <td><?= number_format($row['total_interactions']); ?></td>
                        <td><?= $row['avg_interactions_per_form']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($dept_comparison)): ?>
                    <tr><td colspan="4" class="empty-state">No data available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>