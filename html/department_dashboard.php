<?php
    // Show all errors from the PHP interpreter.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  
?>
<?php
require_once 'config.php';
requireLogin();

if (getUserType() != 'department') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$department_id = getUserId();

// Handle new interaction submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_interaction'])) {
    $form_id = filter_input(INPUT_POST, 'form_id', FILTER_VALIDATE_INT);
    $description = trim($_POST['description'] ?? '');
    
    if ($form_id && !empty($description)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO department_form_interactions 
                (department_id, form_id, department_form_interaction_description, client_id) 
                VALUES (?, ?, ?, NULL)
            ");
            $stmt->bind_param("iis", $department_id, $form_id, $description);
            $stmt->execute();
            $stmt->close();
            $success = "Interaction added successfully!";
        } catch(Exception $e) {
            error_log("Error adding department interaction: " . $e->getMessage());
            $error = "Error adding interaction. Please try again.";
        }
    }
}

// Get filter parameters
$form_type_filter = $_GET['form_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query with filters
$query = "
    SELECT 
        f.form_id, 
        f.form_type, 
        f.form_datetime_submitted,
        f.form_datetime_resolved,
        f.form_paid_bool,
        COUNT(dfi.form_id) as interaction_count
    FROM forms f
    LEFT JOIN department_form_interactions dfi ON f.form_id = dfi.form_id
    WHERE 1=1
";

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

if ($status_filter === 'resolved') {
    $query .= " AND f.form_datetime_resolved IS NOT NULL";
} elseif ($status_filter === 'pending') {
    $query .= " AND f.form_datetime_resolved IS NULL";
}

$query .= " GROUP BY f.form_id ORDER BY f.form_datetime_submitted DESC LIMIT 100";

$stmt = $conn->prepare($query);

if (!empty($bind_values)) {
    array_unshift($bind_values, $param_types);
    call_user_func_array([$stmt, 'bind_param'], $bind_values);
}

$stmt->execute();
$result = $stmt->get_result();
$forms = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get form types for filter
$form_types = [];
$form_types_result = $conn->query("SELECT form_type FROM form_types ORDER BY form_type");
if ($form_types_result) {
    while ($row = $form_types_result->fetch_assoc()) {
        $form_types[] = $row['form_type'];
    }
    $form_types_result->free();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: #28a745;
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
            border-bottom: 2px solid #28a745;
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
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn:hover { background: #218838; }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
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
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            margin: 100px auto;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
        }
        .modal-content h3 {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-height: 100px;
            font-family: inherit;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .interaction-badge {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            color: #495057;
            font-weight: 600;
        }
        .status-resolved { color: #28a745; font-weight: 600; }
        .status-pending { color: #ffc107; font-weight: 600; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Department Dashboard - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <div>
            <a href="department_reports.php">Reports</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
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
                        <label>Status</label>
                        <select name="status">
                            <option value="">All</option>
                            <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">Apply Filters</button>
                <a href="department_dashboard.php" class="btn btn-secondary">Clear Filters</a>
            </form>
        </div>
        
        <div class="card">
            <h2>Submitted Forms (<?php echo count($forms); ?> results)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Form ID</th>
                        <th>Form Type</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Interactions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($form['form_id']); ?></td>
                            <td><?php echo htmlspecialchars($form['form_type']); ?></td>
                            <td><?php echo htmlspecialchars($form['form_datetime_submitted']); ?></td>
                            <td class="<?php echo $form['form_datetime_resolved'] ? 'status-resolved' : 'status-pending'; ?>">
                                <?php echo $form['form_datetime_resolved'] ? 'Resolved' : 'Pending'; ?>
                            </td>
                            <td>
                                <span class="interaction-badge">
                                    <?php echo $form['interaction_count']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="department_view_form.php?id=<?php echo $form['form_id']; ?>" class="btn">View Details</a>
                                <button class="btn" onclick="openModal(<?php echo json_encode($form['form_id']); ?>)">Add Interaction</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="interactionModal" class="modal">
        <div class="modal-content">
            <h3>Add Department Interaction</h3>
            <form method="POST">
                <input type="hidden" name="form_id" id="modal_form_id">
                <div class="form-group">
                    <label>Interaction Description</label>
                    <textarea name="description" required></textarea>
                </div>
                <button type="submit" name="add_interaction" class="btn">Submit Interaction</button>
                <button type="button" class="btn" onclick="closeModal()" style="background: #6c757d;">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(formId) {
            document.getElementById('modal_form_id').value = formId;
            document.getElementById('interactionModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('interactionModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('interactionModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>