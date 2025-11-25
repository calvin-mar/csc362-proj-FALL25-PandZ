<?php
// Show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'config.php';
requireLogin();

if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId();
$message = "";

// Fetch client info
$stmt = $conn->prepare("SELECT client_username, client_email FROM clients WHERE client_id = ?");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

// Fetch form count
$stmt_forms = $conn->prepare("SELECT COUNT(*) AS form_count FROM client_forms WHERE client_id = ?");
$stmt_forms->bind_param("s", $client_id);
$stmt_forms->execute();
$form_result = $stmt_forms->get_result();
$form_count = $form_result->fetch_assoc()['form_count'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_email'])) {
        $new_email = trim($_POST['email']);
        $confirm_email = trim($_POST['confirm_email']);
        if ($new_email === $confirm_email && filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("UPDATE clients SET client_email = ? WHERE client_id = ?");
            $stmt->bind_param("ss", $new_email, $client_id);
            if ($stmt->execute()) {
                // Redirect after success
                header('Location: client_account_management.php?success=email');
                exit();
            } else {
                $message = "Error updating email.";
            }
        } else {
            $message = "Emails do not match or are invalid.";
        }
    }

    if (isset($_POST['update_password'])) {
        $new_password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if ($new_password === $confirm_password && preg_match($pattern, $new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE clients SET client_password = ? WHERE client_id = ?");
            $stmt->bind_param("ss", $hashed_password, $client_id);
            if ($stmt->execute()) {
                // Redirect after success
                header('Location: client_account_management.php?success=password');
                exit();
            } else {
                $message = "Error updating password.";
            }
        } else {
            $message = "Passwords do not match or do not meet complexity requirements. Please retype";
        }
    }
}
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'email') {
        $message = "Email updated successfully!";
    } elseif ($_GET['success'] === 'password') {
        $message = "Password updated successfully!";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; }
        .container { max-width: 600px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .message { text-align: center; margin-bottom: 15px; font-weight: bold; color: #007BFF; }
        .info-box { padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; }
        form { margin-bottom: 20px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .back-link { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #007BFF; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
        .note { font-size: 12px; color: #555; }
    </style>
    <script>
        function validatePassword() {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            const pattern = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!pattern.test(password)) {
                alert("Password must be at least 8 characters, include uppercase, lowercase, number, and special character.");
                return false;
            }
            if (password !== confirmPassword) {
                alert("Passwords do not match. Please retype.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Manage Your Account</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <div class="info-box">
        <h3>Your Information</h3>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($client['client_username'] ?? " "); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($client['client_email'] ?? " "); ?></p>
        <p><strong>Forms Submitted:</strong> <?php echo $form_count; ?></p>
    </div>

    <form method="POST">
        <h3>Change Email</h3>
        <input type="email" name="email" placeholder="New Email" required>
        <input type="email" name="confirm_email" placeholder="Confirm New Email" required>
        <button type="submit" name="update_email">Update Email</button>
    </form>

    <form method="POST" onsubmit="return validatePassword();">
        <h3>Change Password</h3>
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <p class="note">Password must be at least 8 characters, include uppercase, lowercase, number, and special character.</p>
        <button type="submit" name="update_password">Update Password</button>
    </form>

    <a href="client_dashboard.php" class="back-link">← Back to Dashboard</a>
</div>
</body>
</html>