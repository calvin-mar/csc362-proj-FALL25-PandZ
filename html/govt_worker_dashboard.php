<?php
session_start(); // ADD THIS LINE FIRST

require_once 'config.php';
requireLogin();

if (getUserType() != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection(); // This should return a mysqli connection object

// Get filter parameters
$form_type_filter = $_GET['form_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$paid_filter = $_GET['paid'] ?? '';

// --- Build main forms query with filters (mysqli prepared statement) ---
$query = "SELECT f.form_id, f.form_type, f.form_datetime_submitted, f.form_datetime_resolved, f.form_paid_bool,
          GROUP_CONCAT(DISTINCT CONCAT(c.client_username) SEPARATOR ', ') as clients
          FROM forms f
          LEFT JOIN client_forms cf ON f.form_id = cf.form_id
          LEFT JOIN clients c ON cf.client_id = c.client_id
          WHERE 1=1";

$param_types = '';
$bind_values = [];

if (!empty($form_type_filter)) {
    $query .= " AND f.form_type = ?";
    $param_types .= "s";
    $bind_values[] = &$form_type_filter;
}

if (!empty($date_from)) {
    $query .= " AND DATE(f.form_datetime_submitted) >= ?";
    $param_types .= "s";
    $bind_values[] = &$date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(f.form_datetime_submitted) <= ?";
    $param_types .= "s";
    $bind_values[] = &$date_to;
}

if ($paid_filter !== '' && $paid_filter !== null) {
    $query .= " AND f.form_paid_bool = ?";
    $param_types .= "i";
    $bind_values[] = &$paid_filter;
}

$query .= " GROUP BY f.form_id ORDER BY f.form_datetime_submitted DESC LIMIT 100";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('MySQLi prepare error for main forms query: ' . $conn->error);
}

if (!empty($bind_values)) {
    array_unshift($bind_values, $param_types);
    $bind_result = call_user_func_array([$stmt, 'bind_param'], $bind_values);
    if ($bind_result === false) {
        die('MySQLi bind_param error for main forms query: ' . $stmt->error);
    }
}

if (!$stmt->execute()) {
    die('MySQLi execute error for main forms query: ' . $stmt->error);
}

$result = $stmt->get_result();
$forms = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// --- Get form types for filter dropdown (mysqli) ---
$form_types = []; // Initialize as empty array
$form_types_result = $conn->query("SELECT form_type FROM form_types ORDER BY form_type");
if ($form_types_result) {
    // Corrected to fetch all rows and then extract the 'form_type' column
    while ($row = $form_types_result->fetch_assoc()) {
        $form_types[] = $row['form_type'];
    }
    $form_types_result->free(); // Free the result set
} else {
    error_log("Error fetching form types: " . $conn->error);
}


// --- Get statistics (mysqli) ---
$stats_query = "SELECT
    COUNT(*) as total_forms,
    SUM(CASE WHEN form_paid_bool = 1 THEN 1 ELSE 0 END) as paid_forms,
    SUM(CASE WHEN form_datetime_resolved IS NULL THEN 1 ELSE 0 END) as pending_forms
    FROM forms";
$stats_result = $conn->query($stats_query);
$stats = ['total_forms' => 0, 'paid_forms' => 0, 'pending_forms' => 0]; // Default values
if ($stats_result) {
    $stats_rows = $stats_result->fetch_all(MYSQLI_ASSOC);
    if (!empty($stats_rows)) {
        $stats = $stats_rows[0]; // Get the first (and only) row
    }
    $stats_result->free(); // Free the result set
} else {
    error_log("Error fetching stats: " . $conn->error);
}

$conn->close(); // Close connection at the end of the script
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Government Worker Dashboard</title>
    <style>
        /* ... (Your CSS here, unchanged) ... */
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
            margin-left: 10px;
        }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #dc3545;
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
        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover { background: #c82333; }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .status-paid { color: #28a745; font-weight: 600; }
        .status-unpaid { color: #dc3545; font-weight: 600; }
        .status-resolved { color: #28a745; }
        .status-pending { color: #ffc107; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Government Worker Dashboard - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <div>
            <a href="govt_worker_reports.php">Reports</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Total Forms</h3>
                <div class="number"><?php echo number_format($stats['total_forms']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Paid Forms</h3>
                <div class="number"><?php echo number_format($stats['paid_forms']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Forms</h3>
                <div class="number"><?php echo number_format($stats['pending_forms']); ?></div>
            </div>
        </div>

        <div class="card">
            <h2>Filter Forms</h2>
            <form method="GET">
                <div class="filters">
                    <div class="filter-group">
                        <label>Form Type</label>
                        <select name="form_type">
                            <option value="">All Types</option>
                            <?php foreach ($form_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"
                                    <?php echo $form_type_filter == $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Payment Status</label>
                        <select name="paid">
                            <option value="">All</option>
                            <option value="1" <?php echo $paid_filter === '1' ? 'selected' : ''; ?>>Paid</option>
                            <option value="0" <?php echo $paid_filter === '0' ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">Apply Filters</button>
                <a href="govt_worker_dashboard.php" class="btn btn-secondary">Clear Filters</a>
            </form>
        </div>

        <div class="card">
            <h2>All Forms (<?php echo count($forms); ?> results)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Form ID</th>
                        <th>Form Type</th>
                        <th>Client(s)</th>
                        <th>Submitted</th>
                        <th>Resolved</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($form['form_id']); ?></td>
                            <td><?php echo htmlspecialchars($form['form_type']); ?></td>
                            <td><?php echo htmlspecialchars($form['clients'] ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($form['form_datetime_submitted']); ?></td>
                            <td class="<?php echo $form['form_datetime_resolved'] ? 'status-resolved' : 'status-pending'; ?>">
                                <?php echo $form['form_datetime_resolved'] ? htmlspecialchars($form['form_datetime_resolved']) : 'Pending'; ?>
                            </td>
                            <td class="<?php echo $form['form_paid_bool'] ? 'status-paid' : 'status-unpaid'; ?>">
                                <?php echo $form['form_paid_bool'] ? 'Paid' : 'Unpaid'; ?>
                            </td>
                            <td>
                                <a href="govt_worker_view_form.php?id=<?php echo $form['form_id']; ?>" class="btn">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>