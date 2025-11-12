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
    $form_id = $_POST['form_id'];
    $description = $_POST['description'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO department_form_interactions (client_id, form_id, department_form_interaction_description) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $department_id, $form_id, $description);
        $stmt->execute();
        $success = "Interaction added successfully!";
    } catch(PDOException $e) {
        $error = "Error adding interaction: " . $e->getMessage();
    }
}

// Get forms with interactions
$stmt = $conn->prepare("
    SELECT f.form_id, f.form_type, f.form_datetime_submitted,
           dfi.department_form_interaction_description
    FROM forms f
    LEFT JOIN department_form_interactions dfi ON f.form_id = dfi.form_id
    ORDER BY f.form_datetime_submitted DESC
");
$stmt->execute();
$result = $stmt->get_result();
$forms = $result->fetch_all(MYSQLI_ASSOC);

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
        }
        .btn:hover { background: #218838; }
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
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Department Dashboard - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="card">
            <h2>Submitted Forms</h2>
            <table>
                <thead>
                    <tr>
                        <th>Form ID</th>
                        <th>Form Type</th>
                        <th>Submitted</th>
                        <th>Current Interaction</th>
                        <th>Actions </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                        <?php 
                        // --- Safeguard 1: Ensure $form is an array ---
                        if (!is_array($form)) {
                            // Log this issue, or just skip it if it's an intermittent data problem
                            error_log("Warning: Encountered non-array element in \$forms. Skipping. Value: " . print_r($form, true));
                            continue; // Skip to the next item in the loop
                        }

                        // --- Retrieve values safely, providing defaults if keys are missing or null ---
                        $form_id = $form['form_id'] ?? null; // Use null if ID might be truly absent/null
                        $form_type = $form['form_type'] ?? '';
                        $form_datetime_submitted = $form['form_datetime_submitted'] ?? '';
                        $description_raw = $form['department_form_interaction_description'] ?? ''; // Get raw description

                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($form_id ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($form_type); ?></td>
                            <td><?php echo htmlspecialchars($form_datetime_submitted); ?></td>
                            <td>
                                <?php
                                if ($description_raw) {
                                    // Shorten and then escape
                                    $short_description = substr($description_raw, 0, 50);
                                    echo htmlspecialchars($short_description);
                                    if (strlen($description_raw) > 50) { // Check original length for ellipsis
                                        echo '...';
                                    }
                                } else {
                                    echo 'No interaction';
                                }
                                ?>
                            </td>
                            <td>
                                <!-- json_encode safely handles null or integers for JavaScript -->
                                <button class="btn" onclick="openModal(<?php echo json_encode($form_id); ?>)">Add Interaction</button>
                            </td>
                        </tr>
                    <?php endforeach;?>
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