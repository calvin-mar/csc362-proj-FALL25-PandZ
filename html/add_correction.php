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

$user_type = getUserType();
if ($user_type != 'client' && $user_type != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$success = '';
$error = '';

// Get form_id from URL
$form_id = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;

if ($form_id <= 0) {
    $error = "Invalid form ID.";
} else {
    if (empty($correction_form_id)) {
        $conn->query("INSERT INTO correction_forms () VALUES ()");
        $correction_form_id = $conn->insert_id;

        $stmt = $conn->prepare("UPDATE forms SET correction_form_id = ? WHERE form_id = ?");
        $stmt->bind_param("ii", $correction_form_id, $form_id);
        $stmt->execute();
        $stmt->close();
    }
    else {
    // Find or create correction_form_id
    $stmt = $conn->prepare("SELECT correction_form_id FROM forms WHERE form_id = ?");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $stmt->bind_result($correction_form_id);
    $stmt->fetch();
    $stmt->close();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $correction_box_reviewer = trim($_POST['correction_box_reviewer'] ?? '');
    $correction_box_text = trim($_POST['correction_box_text'] ?? '');

    if (empty($correction_box_reviewer) || empty($correction_box_text)) {
        $error = 'Reviewer name and correction text are required.';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO correction_boxes (correction_box_reviewer, correction_box_text, correction_form_id)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("ssi", $correction_box_reviewer, $correction_box_text, $correction_form_id);

        if ($stmt->execute()) {
            $success = "Correction added successfully! Redirecting...";
            // Redirect after 2 seconds to dashboard (for govt_worker)
            if ($user_type === 'govt_worker') {
                header("Refresh: 2; URL=govt_worker_dashboard.php?success=1");
            }
        } else {
            $error = "Database error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Correction</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f0fa;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #6f42c1;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .btn-light {
            color: #6f42c1;
            font-weight: 600;
        }
        .container {
            max-width: 700px;
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <span class="navbar-brand mb-0 h1">
      Add Correction – Form #<?php echo htmlspecialchars($form_id); ?>
    </span>
    <a href="govt_worker_dashboard.php" class="btn btn-light btn-sm">
      ← Back to Dashboard
    </a>
  </div>
</nav>

<div class="container">
    <h3 class="text-center mb-4">Add Correction</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="correction_box_reviewer">Reviewer Name</label>
            <input type="text" class="form-control" id="correction_box_reviewer" name="correction_box_reviewer" placeholder="Enter reviewer name" required>
        </div>

        <div class="form-group">
            <label for="correction_box_text">Correction Details</label>
            <textarea class="form-control" id="correction_box_text" name="correction_box_text" rows="6" placeholder="Enter correction details..." required></textarea>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary px-5">Submit Correction</button>
        </div>
    </form>

    <div class="text-center mt-3">
        <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
    </div>
</div>
</body>
</html>

