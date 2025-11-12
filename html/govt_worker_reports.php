<?php
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
// 1) KPIs (v_form_metrics)
$kpis = ['submitted'=>0,'resolved'=>0,'incomplete'=>0];
if ($res = $conn->query("SELECT * FROM v_form_metrics")) {
  if ($row = $res->fetch_assoc()) {
    $kpis['submitted']  = (int)($row['total_forms_submitted'] ?? 0);
    $kpis['resolved']   = (int)($row['total_forms_resolved'] ?? 0);
    $kpis['incomplete'] = (int)($row['incomplete_client_forms'] ?? 0);
  }
  $res->free();
}

// ------------------------------------------------------------
// 2) Form type usage  (vw_form_summary)
$form_type_usage = [];
$q = "
  SELECT form_type AS form_type_name, COUNT(*) AS total_submissions
  FROM vw_form_summary
  GROUP BY form_type
  ORDER BY total_submissions DESC
";
if ($res = $conn->query($q)) { $form_type_usage = $res->fetch_all(MYSQLI_ASSOC); $res->free(); }

// ------------------------------------------------------------
// 3) Dept workload  (vw_department_interactions -> aggregate here)
$dept_workload = [];
$q = "
  SELECT department_name, COUNT(*) AS total_forms_interacted_with
  FROM vw_department_interactions
  GROUP BY department_name
  ORDER BY total_forms_interacted_with DESC
";
if ($res = $conn->query($q)) { $dept_workload = $res->fetch_all(MYSQLI_ASSOC); $res->free(); }

// ------------------------------------------------------------
// 4) Forms needing correction  (vw_form_corrections + vw_form_summary)
$needs_correction = [];
$q = "
  SELECT vc.form_id,
         s.form_type AS form_type_name,
         s.form_datetime_submitted,
         vc.correction_box_reviewer,
         vc.correction_box_text AS correction_reason
  FROM vw_form_corrections vc
  JOIN vw_form_summary s ON s.form_id = vc.form_id
  ORDER BY s.form_datetime_submitted DESC
  LIMIT 100
";
if ($res = $conn->query($q)) { $needs_correction = $res->fetch_all(MYSQLI_ASSOC); $res->free(); }

// ------------------------------------------------------------
// 5) Unpaid forms (vw_form_summary)
$unpaid = [];
$q = "
  SELECT f.form_id,
         f.form_type AS form_type_name,
         f.form_datetime_submitted
  FROM vw_form_summary f
  WHERE COALESCE(f.form_paid_bool,0)=0
  ORDER BY f.form_datetime_submitted DESC
  LIMIT 100
";
if ($res = $conn->query($q)) { $unpaid = $res->fetch_all(MYSQLI_ASSOC); $res->free(); }

// ------------------------------------------------------------
// 6) SLA / Resolution time stats (days) - last 365 days (vw_form_summary)
$resolve_days = [];
$q = "
  SELECT TIMESTAMPDIFF(DAY, f.form_datetime_submitted, f.form_datetime_resolved) AS days
  FROM vw_form_summary f
  WHERE f.form_datetime_resolved IS NOT NULL
    AND f.form_datetime_submitted >= (NOW() - INTERVAL 365 DAY)
";
if ($res = $conn->query($q)) {
  while ($row = $res->fetch_assoc()) {
    $d = (int)$row['days'];
    if ($d >= 0) $resolve_days[] = $d;
  }
  $res->free();
}
$resolve_stats = ['avg_days'=>0,'p50_days'=>0,'p90_days'=>0];
if (!empty($resolve_days)) {
  sort($resolve_days);
  $n = count($resolve_days);
  $avg = array_sum($resolve_days)/$n;
  $p50_index = (int)floor(0.5*($n-1));
  $p90_index = (int)floor(0.9*($n-1));
  $resolve_stats['avg_days'] = round($avg, 1);
  $resolve_stats['p50_days'] = $resolve_days[$p50_index];
  $resolve_stats['p90_days'] = $resolve_days[$p90_index];
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
  .container {
  max-width: 1200px;   /* narrower overall layout */
  margin: 30px auto;
  padding: 0 20px;
}
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
tr:hover { background: #f8f9fa; }
  .kpis { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:20px; }
  .kpi { background:#fff; padding:16px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.06); }
  .kpi h3 { font-size:13px; color:#666; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
  .kpi .value { font-size:24px; font-weight:700; }
  .muted { color:#777; font-size:12px; }
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

  <!-- KPI Cards (kept as responsive grid) -->
  <div class="kpis">
    <div class="kpi">
      <h3>Submitted (All-Time)</h3>
      <div class="value"><?= number_format((int)$kpis['submitted']); ?></div>
    </div>
    <div class="kpi">
      <h3>Resolved (All-Time)</h3>
      <div class="value"><?= number_format((int)$kpis['resolved']); ?></div>
    </div>
    <div class="kpi">
      <h3>Incomplete (Flagged)</h3>
      <div class="value"><?= number_format((int)$kpis['incomplete']); ?></div>
    </div>
  </div>

  <!-- Each section below is a FULL-WIDTH card (stacked) -->
  
  <!-- Forms Needing Correction -->
  <div class="card">
    <h2>Forms Needing Correction</h2>
    <table>
      <thead><tr><th>Form</th><th>Submitted</th><th>Reviewer</th><th>Reason</th></tr></thead>
      <tbody>
        <?php foreach ($needs_correction as $row): ?>
          <tr>
            <td><a href="govt_worker_view_form.php?id=<?= urlencode($row['form_id']); ?>">
              <?= htmlspecialchars($row['form_type_name']); ?> (#<?= htmlspecialchars($row['form_id']); ?>)
            </a></td>
            <td><?= htmlspecialchars($row['form_datetime_submitted']); ?></td>
            <td><?= htmlspecialchars($row['correction_box_reviewer']); ?></td>
            <td><?= htmlspecialchars($row['correction_reason']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($needs_correction)): ?>
          <tr><td colspan="4">No forms needing correction</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Unpaid Forms -->
  <div class="card">
    <h2>Unpaid Forms</h2>
    <table>
      <thead><tr><th>Form</th><th>Submitted</th></tr></thead>
      <tbody>
        <?php foreach ($unpaid as $row): ?>
          <tr>
            <td><a href="govt_worker_view_form.php?id=<?= urlencode($row['form_id']); ?>">
              <?= htmlspecialchars($row['form_type_name']); ?> (#<?= htmlspecialchars($row['form_id']); ?>)
            </a></td>
            <td><?= htmlspecialchars($row['form_datetime_submitted']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($unpaid)): ?>
          <tr><td colspan="2">No unpaid forms 🎉</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Form Type Usage -->
  <div class="card">
    <h2>Form Type Usage</h2>
    <table>
      <thead><tr><th>Form Type</th><th>Total Submissions</th></tr></thead>
      <tbody>
        <?php foreach ($form_type_usage as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['form_type_name']); ?></td>
            <td><?= number_format((int)($row['total_submissions'] ?? 0)); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($form_type_usage)): ?>
          <tr><td colspan="2">No data available</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Department Workload -->
  <div class="card">
    <h2>Department Workload</h2>
    <table>
      <thead><tr><th>Department</th><th>Total Interactions</th></tr></thead>
      <tbody>
        <?php foreach ($dept_workload as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['department_name']); ?></td>
            <td><?= number_format((int)($row['total_forms_interacted_with'] ?? 0)); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($dept_workload)): ?>
          <tr><td colspan="2">No data available</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>

