<?php
/**
 * Reporting dashboard for government workers:
 * - Shows wide range of metrics and performance stats.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'config.php';
requireLogin();

if (getUserType() !== 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection(); // MySQLi

// ------------------------------------------------------------
// 1) KPIs (v_form_metrics) - Enhanced
$kpis = [
    'submitted' => 0,
    'resolved' => 0,
    'incomplete' => 0,
    'paid' => 0,
    'unpaid' => 0,
    'pending' => 0
];

if ($res = $conn->query("SELECT * FROM v_form_metrics")) {
    if ($row = $res->fetch_assoc()) {
        $kpis['submitted']  = (int)($row['total_forms_submitted'] ?? 0);
        $kpis['resolved']   = (int)($row['total_forms_resolved'] ?? 0);
        $kpis['incomplete'] = (int)($row['incomplete_client_forms'] ?? 0);
        $kpis['paid']       = (int)($row['total_forms_paid'] ?? 0);
        $kpis['unpaid']     = (int)($row['total_forms_unpaid'] ?? 0);
        $kpis['pending']    = (int)($row['total_forms_pending'] ?? 0);
    }
    $res->free();
}

// ------------------------------------------------------------
// 2) Form type usage (vw_form_type_usage_summary) - Enhanced
$form_type_usage = [];
$q = "SELECT * FROM vw_form_type_usage_summary ORDER BY total_submissions DESC";
if ($res = $conn->query($q)) {
    $form_type_usage = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();
}

// ------------------------------------------------------------
// 3) Dept workload (vw_department_workload_summary) - Enhanced
$dept_workload = [];
$q = "SELECT * FROM vw_department_workload_summary ORDER BY total_forms_interacted_with DESC";
if ($res = $conn->query($q)) {
    $dept_workload = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();
}

// ------------------------------------------------------------
// 4) Forms needing correction (vw_form_corrections) - Enhanced
$needs_correction = [];
$q = "
    SELECT 
        form_id,
        form_type AS form_type_name,
        form_datetime_submitted,
        correction_box_reviewer,
        correction_box_text AS correction_reason,
        correction_status,
        days_since_submission
    FROM vw_form_corrections
    ORDER BY form_datetime_submitted DESC
    LIMIT 100
";
if ($res = $conn->query($q)) {
    $needs_correction = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();
}

// ------------------------------------------------------------
// 5) Unpaid forms (vw_unpaid_forms_detail) - Enhanced
$unpaid = [];
$q = "
    SELECT 
        form_id,
        form_type_name,
        form_datetime_submitted,
        client_username,
        days_since_submission,
        status
    FROM vw_unpaid_forms_detail
    ORDER BY form_datetime_submitted DESC
    LIMIT 100
";
if ($res = $conn->query($q)) {
    $unpaid = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();
}

// ------------------------------------------------------------
// 6) SLA / Resolution time stats (vw_resolution_time_stats) - Enhanced
$resolve_days = [];
$q = "
    SELECT resolution_days AS days
    FROM vw_resolution_time_stats
    WHERE form_datetime_submitted >= (NOW() - INTERVAL 365 DAY)
      AND resolution_days >= 0
";
if ($res = $conn->query($q)) {
    while ($row = $res->fetch_assoc()) {
        $d = (int)$row['days'];
        if ($d >= 0) $resolve_days[] = $d;
    }
    $res->free();
}

$resolve_stats = ['avg_days'=>0,'p50_days'=>0,'p90_days'=>0,'min_days'=>0,'max_days'=>0];
if (!empty($resolve_days)) {
    sort($resolve_days);
    $n = count($resolve_days);
    $avg = array_sum($resolve_days)/$n;
    $p50_index = (int)floor(0.5*($n-1));
    $p90_index = (int)floor(0.9*($n-1));
    $resolve_stats['avg_days'] = round($avg, 1);
    $resolve_stats['p50_days'] = $resolve_days[$p50_index];
    $resolve_stats['p90_days'] = $resolve_days[$p90_index];
    $resolve_stats['min_days'] = $resolve_days[0];
    $resolve_stats['max_days'] = $resolve_days[$n-1];
}

// ------------------------------------------------------------
// 7) Resolution Speed Distribution
$speed_distribution = [];
$q = "
    SELECT 
        resolution_speed_category,
        COUNT(*) AS count
    FROM vw_resolution_time_stats
    WHERE form_datetime_submitted >= (NOW() - INTERVAL 365 DAY)
    GROUP BY resolution_speed_category
    ORDER BY FIELD(resolution_speed_category, 
        'Fast (0-7 days)', 
        'Medium (8-30 days)', 
        'Slow (31-90 days)', 
        'Very Slow (90+ days)')
";
if ($res = $conn->query($q)) {
    $speed_distribution = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports - Government Worker</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f5f5f5; }
    .navbar { background:#dc3545; color:#fff; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
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
        border-bottom: 2px solid #dc3545;
        padding-bottom: 10px;
        font-size: 20px;
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #f8f9fa; font-weight: 600; color: #333; }
    tr:hover { background: #f8f9fa; }
    .kpis { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:20px; }
    .kpi { background:#fff; padding:16px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.06); }
    .kpi h3 { font-size:13px; color:#666; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
    .kpi .value { font-size:28px; font-weight:700; color:#dc3545; }
    .kpi.success .value { color:#28a745; }
    .kpi.warning .value { color:#ffc107; }
    .kpi.info .value { color:#17a2b8; }
    .muted { color:#777; font-size:12px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 15px; }
    .stat-item { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }
    .stat-item .label { font-size: 12px; color: #666; margin-bottom: 5px; }
    .stat-item .value { font-size: 24px; font-weight: 700; color: #333; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .badge-warning { background: #fff3cd; color: #856404; }
    .badge-success { background: #d4edda; color: #155724; }
    .badge-danger { background: #f8d7da; color: #721c24; }
    .badge-info { background: #d1ecf1; color: #0c5460; }
    a { color: #dc3545; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="navbar">
    <h1>Reports Dashboard</h1>
    <div>
        <a href="govt_worker_dashboard.php">Back to Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <!-- KPI Cards (Enhanced with 6 metrics) -->
    <div class="kpis">
        <div class="kpi">
            <h3>Total Submitted</h3>
            <div class="value"><?= number_format($kpis['submitted']); ?></div>
            <div class="muted">All-Time</div>
        </div>
        <div class="kpi success">
            <h3>Resolved</h3>
            <div class="value"><?= number_format($kpis['resolved']); ?></div>
            <div class="muted">Completed Forms</div>
        </div>
        <div class="kpi warning">
            <h3>Pending</h3>
            <div class="value"><?= number_format($kpis['pending']); ?></div>
            <div class="muted">In Progress</div>
        </div>
        <div class="kpi warning">
            <h3>Need Correction</h3>
            <div class="value"><?= number_format($kpis['incomplete']); ?></div>
            <div class="muted">Flagged Issues</div>
        </div>
        <div class="kpi success">
            <h3>Paid</h3>
            <div class="value"><?= number_format($kpis['paid']); ?></div>
            <div class="muted">Payment Received</div>
        </div>
        <div class="kpi warning">
            <h3>Unpaid</h3>
            <div class="value"><?= number_format($kpis['unpaid']); ?></div>
            <div class="muted">Payment Pending</div>
        </div>
    </div>

    <!-- Resolution Time Statistics -->
    <div class="card">
        <h2>Resolution Time Statistics (Last 365 Days)</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="label">Average</div>
                <div class="value"><?= $resolve_stats['avg_days']; ?></div>
                <div class="muted">days</div>
            </div>
            <div class="stat-item">
                <div class="label">Median (P50)</div>
                <div class="value"><?= $resolve_stats['p50_days']; ?></div>
                <div class="muted">days</div>
            </div>
            <div class="stat-item">
                <div class="label">P90 Percentile</div>
                <div class="value"><?= $resolve_stats['p90_days']; ?></div>
                <div class="muted">days</div>
            </div>
            <div class="stat-item">
                <div class="label">Fastest</div>
                <div class="value"><?= $resolve_stats['min_days']; ?></div>
                <div class="muted">days</div>
            </div>
            <div class="stat-item">
                <div class="label">Slowest</div>
                <div class="value"><?= $resolve_stats['max_days']; ?></div>
                <div class="muted">days</div>
            </div>
        </div>
        
        <?php if (!empty($speed_distribution)): ?>
            <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 16px;">Resolution Speed Distribution</h3>
            <table>
                <thead>
                    <tr>
                        <th>Speed Category</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_resolved = array_sum(array_column($speed_distribution, 'count'));
                    foreach ($speed_distribution as $row): 
                        $percentage = $total_resolved > 0 ? round(($row['count'] / $total_resolved) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['resolution_speed_category']); ?></td>
                            <td><?= number_format($row['count']); ?></td>
                            <td>
                                <span class="badge <?= 
                                    strpos($row['resolution_speed_category'], 'Fast') !== false ? 'badge-success' : 
                                    (strpos($row['resolution_speed_category'], 'Medium') !== false ? 'badge-info' : 
                                    (strpos($row['resolution_speed_category'], 'Slow') !== false ? 'badge-warning' : 'badge-danger'))
                                ?>">
                                    <?= $percentage; ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Forms Needing Correction -->
    <div class="card">
        <h2>Forms Needing Correction (<?= count($needs_correction); ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Form</th>
                    <th>Submitted</th>
                    <th>Days Waiting</th>
                    <th>Status</th>
                    <th>Reviewer</th>
                    <th>Correction Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($needs_correction as $row): ?>
                    <tr>
                        <td>
                            <a href="govt_worker_view_form.php?id=<?= urlencode($row['form_id']); ?>">
                                <?= htmlspecialchars($row['form_type_name']); ?> (#<?= htmlspecialchars($row['form_id']); ?>)
                            </a>
                        </td>
                        <td><?= htmlspecialchars($row['form_datetime_submitted']); ?></td>
                        <td><?= htmlspecialchars($row['days_since_submission']); ?> days</td>
                        <td>
                            <span class="badge <?= $row['correction_status'] === 'Resolved' ? 'badge-success' : 'badge-warning'; ?>">
                                <?= htmlspecialchars($row['correction_status']); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['correction_box_reviewer']); ?></td>
                        <td><?= htmlspecialchars(substr($row['correction_reason'], 0, 100)); ?><?= strlen($row['correction_reason']) > 100 ? '...' : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($needs_correction)): ?>
                    <tr><td colspan="6" style="text-align: center; color: #28a745;">✓ No forms needing correction</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Unpaid Forms -->
    <div class="card">
        <h2>Unpaid Forms (<?= count($unpaid); ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Form</th>
                    <th>Client</th>
                    <th>Submitted</th>
                    <th>Days Unpaid</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unpaid as $row): ?>
                    <tr>
                        <td>
                            <a href="govt_worker_view_form.php?id=<?= urlencode($row['form_id']); ?>">
                                <?= htmlspecialchars($row['form_type_name']); ?> (#<?= htmlspecialchars($row['form_id']); ?>)
                            </a>
                        </td>
                        <td><?= htmlspecialchars($row['client_username'] ?? 'N/A'); ?></td>
                        <td><?= htmlspecialchars($row['form_datetime_submitted']); ?></td>
                        <td><?= htmlspecialchars($row['days_since_submission']); ?> days</td>
                        <td>
                            <span class="badge <?= 
                                strpos($row['status'], 'Resolved') !== false ? 'badge-success' : 
                                (strpos($row['status'], 'Correction') !== false ? 'badge-warning' : 'badge-info')
                            ?>">
                                <?= htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($unpaid)): ?>
                    <tr><td colspan="5" style="text-align: center; color: #28a745;">🎉 All forms are paid!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Form Type Usage -->
    <div class="card">
        <h2>Form Type Usage & Performance</h2>
        <table>
            <thead>
                <tr>
                    <th>Form Type</th>
                    <th>Total</th>
                    <th>Resolved</th>
                    <th>Pending</th>
                    <th>Paid</th>
                    <th>Need Correction</th>
                    <th>Avg Resolution</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($form_type_usage as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['form_type_name']); ?></td>
                        <td><?= number_format($row['total_submissions']); ?></td>
                        <td><?= number_format($row['total_resolved']); ?></td>
                        <td><?= number_format($row['total_pending']); ?></td>
                        <td><?= number_format($row['total_paid']); ?></td>
                        <td><?= number_format($row['total_needing_correction']); ?></td>
                        <td><?= $row['avg_resolution_days'] ? round($row['avg_resolution_days'], 1) . ' days' : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($form_type_usage)): ?>
                    <tr><td colspan="7" style="text-align: center;">No data available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Department Workload -->
    <div class="card">
        <h2>Department Workload & Performance</h2>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Forms Handled</th>
                    <th>Total Interactions</th>
                    <th>Pending Forms</th>
                    <th>Resolved Forms</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dept_workload as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['department_name']); ?></td>
                        <td><?= number_format($row['total_forms_interacted_with']); ?></td>
                        <td><?= number_format($row['total_interactions']); ?></td>
                        <td><?= number_format($row['pending_forms_with_interactions']); ?></td>
                        <td><?= number_format($row['resolved_forms_with_interactions']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($dept_workload)): ?>
                    <tr><td colspan="5" style="text-align: center;">No data available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>