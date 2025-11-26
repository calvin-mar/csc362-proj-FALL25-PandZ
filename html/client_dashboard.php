<?php
    // Show all errors from the PHP interpreter.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  
?>
<?php
session_start();
require_once 'config.php';
requireLogin();

if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId();

// Get client's forms
$stmt = $conn->prepare("
    SELECT f.form_id, f.form_type, f.form_datetime_submitted, f.form_datetime_resolved, f.form_paid_bool
    FROM forms f
    JOIN client_forms cf ON f.form_id = cf.form_id
    WHERE cf.client_id = ?
    ORDER BY f.form_datetime_submitted DESC
");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$forms = $result->fetch_all(MYSQLI_ASSOC);


// Fetch saved drafts
$stmt = $conn->prepare("
    SELECT icf.form_id AS draft_id, f.form_type, f.form_datetime_submitted
    FROM incomplete_client_forms icf
    JOIN forms f ON icf.form_id = f.form_id
    WHERE icf.client_id = ?
    ORDER BY f.form_datetime_submitted DESC
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$draft_result = $stmt->get_result();
$drafts = $draft_result->fetch_all(MYSQLI_ASSOC);
$stmt->close()
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
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

        .nav-links {
            display: flex;
            gap: 10px;
            margin-left: auto; /* Push buttons to the right */
        }

        .manage-btn {
            margin-right: 10px; /* Optional spacing before Logout */
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
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
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
    </style>
</head>
<body>
    
    <div class="navbar">
        <h1>Client Dashboard - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <div class="nav-links">
            <a href="account_management.php" class="btn btn-success manage-btn">Manage Account</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Quick Actions</h2>
            <a href="client_new_form.php" class="btn btn-success">Submit New Form</a>
        </div>
        
        <div class="card">
            <h2>My Submitted Forms</h2>
            <?php if ($forms !== null): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Form ID</th>
                            <th>Form Type</th>
                            <th>Submitted</th>
                            <th>Resolved</th>
                            <th>Paid</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $form): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($form['form_id']); ?></td>
                                <td><?php echo htmlspecialchars($form['form_type']); ?></td>
                                <td><?php echo htmlspecialchars($form['form_datetime_submitted']); ?></td>
                                <td><?php echo $form['form_datetime_resolved'] ? htmlspecialchars($form['form_datetime_resolved']) : 'Pending'; ?></td>
                                <td class="<?php echo $form['form_paid_bool'] ? 'status-paid' : 'status-unpaid'; ?>">
                                    <?php echo $form['form_paid_bool'] ? 'Paid' : 'Unpaid'; ?>
                                </td>
                                <td>
                                    <a href="client_view_form.php?id=<?php echo $form['form_id']; ?>" class="btn">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No forms submitted yet.</p>
            <?php endif; ?>
                <!-- Saved Drafts -->
            <h3 class="mt-5">My Saved Drafts</h3>
            <?php if (!empty($drafts)): ?>
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Draft ID</th>
                            <th>Form Type</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($drafts as $draft): ?>
                        <tr>
                            <td><?= htmlspecialchars($draft['draft_id']); ?></td>
                            <td><?= htmlspecialchars($draft['form_type']); ?></td>
                            <td><?= htmlspecialchars($draft['form_datetime_submitted']); ?></td>
                            <td>
                                <a href="form_sp_insert_administrative_appeal_request.php?draft_id=<?= urlencode($draft['draft_id']); ?>" class="btn btn-warning btn-sm">Edit Draft</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No saved drafts yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

