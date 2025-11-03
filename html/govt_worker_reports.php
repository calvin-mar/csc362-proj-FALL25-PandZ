<?php
require_once 'config.php';
requireLogin();

if (getUserType() != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Get various report data using views
$form_type_usage = $conn->query("SELECT * FROM v_form_type_usage")->fetchAll(PDO::FETCH_ASSOC);
$recent_forms = $conn->query("SELECT * FROM v_recent_forms LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$department_workload = $conn->query("SELECT * FROM v_department_workload")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Government Worker</title>
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
            margin-left: 10px;
        }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container {
            max-width: 1400px;
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
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
        }
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
        <div class="reports-grid">
            <div class="card">
                <h2>Form Type Usage</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Form Type</th>
                            <th>Total Submissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($form_type_usage as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['form_type_name']); ?></td>
                                <td><?php echo number_format($row['total_submissions']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Department Workload</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Total Forms</th>
                            <th>Incomplete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($department_workload as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                                <td><?php echo number_format($row['total_forms']); ?></td>
                                <td><?php echo number_format($row['incomplete_forms']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <h2>Recent Form Submissions (Last 30 Days)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Form ID</th>
                        <th>Form Type</th>
                        <th>Client Name</th>
                        <th>Created</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_forms as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['form_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['form_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['client_name'] ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($row['updated_at'] ?: 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>