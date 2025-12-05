<?php
    // Show all errors from the PHP interpreter.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  
?>
<?php
/** Page that allows a logged in client to
 *  - Change email
 *  - Change password
 *  - Delete account enirely
 */
require_once 'config.php';
requireLogin();
$conn = getDBConnection(); 
$message = "";
$origin = getUserType() . "_dashboard.php";

// Display status message from redirect
if (isset($_GET['status'])) {
    $message = htmlspecialchars($_GET['status']);
}

// Handle POST requests (from submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = "";
    // Update Email with confirmation
    if (isset($_POST['update_email'])) {
        $email = trim($_POST['email']);
        $confirm_email = trim($_POST['confirm_email']);
        // Validate email
        if ($email === $confirm_email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("UPDATE clients SET email = ? WHERE client_id = ?");
            $stmt->bind_param("ss", $email, $client_id);
            $status = $stmt->execute() ? "Email updated successfully!" : "Error updating email.";
        } else {
            $status = "Emails do not match or invalid format.";
        }
    }

    // Update Password with confirmation and security rules
    if (isset($_POST['update_password'])) {
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $pattern = '/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+\-=]).{8,}$/';
        // Validate password w/ fields match and regex
        if ($password === $confirm_password && preg_match($pattern, $password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE clients SET password = ? WHERE client_id = ?");
            $stmt->bind_param("ss", $hashed_password, $client_id);
            $status = $stmt->execute() ? "Password updated successfully!" : "Error updating password.";
        } else {
            $status = "Passwords do not match or do not meet security requirements.";
        }
    }

    // Delete Account
    if (isset($_POST['delete_account'])) {
        $stmt = $conn->prepare("DELETE FROM clients WHERE client_id = ?");
        $stmt->bind_param("s", $client_id);
        if ($stmt->execute()) {
            session_destroy();
            header("Location: goodbye.php");
            exit();
        } else {
            $status = "Error deleting account.";
        }
    }

    // Redirect to avoid resubmission
    header("Location: account_management.php?status=" . urlencode($status));
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Management</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        h2 { color: #333; }
        .message { font-weight: bold; color: #007BFF; margin-bottom: 15px; }
        form { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; max-width: 400px; background: #f9f9f9; }
        input { width: 95%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 10px 20px; background-color: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .delete-btn { background-color: #dc3545; }
        .delete-btn:hover { background-color: #a71d2a; }
        #strength { font-size: 12px; font-weight: bold; }
    </style>
    <script>
        /**
         * Client side validation
         * Ensure that the email and confirmation fields match before submit
         */
        function validateEmailForm() {
            const email = document.querySelector('input[name="email"]').value;
            const confirmEmail = document.querySelector('input[name="confirm_email"]').value;
            if (email !== confirmEmail) {
                alert("Emails do not match.");
                return false;
            }
            return true;
        }
        /**
         * Client side validation for change password form.
         * Passwords must match
         * Passwords must follow same pattern as enforced on the server
         */
        function validatePasswordForm() {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            const pattern = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+\-=]).{8,}$/;

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            if (!pattern.test(password)) {
                alert("Password must be at least 8 characters, include an uppercase letter, a number, and a special character.");
                return false;
            }
            return true;
        }
        //Remind user that delete is permanent
        function confirmDelete() {
            return confirm("⚠️ Are you sure you want to delete your account? This cannot be undone.");
        }
        /**
         * Password strength indicator
         * Updates text w/ Too short, Medium(missing reqs), and Strong
         */
        function checkStrength() {
            const password = document.querySelector('input[name="password"]').value;
            const strengthText = document.getElementById('strength');
            const pattern = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+\-=]).{8,}$/;

            if (password.length < 8) {
                strengthText.textContent = "Weak: Too short";
                strengthText.style.color = "red";
            } else if (!pattern.test(password)) {
                strengthText.textContent = "Medium: Missing requirements";
                strengthText.style.color = "orange";
            } else {
                strengthText.textContent = "Strong password!";
                strengthText.style.color = "green";
            }
        }
    </script>
</head>
<body>
    <h2>Manage Your Account</h2>
    <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>

    <form method="POST" onsubmit="return validateEmailForm();">
        <h3>Change Email</h3>
        <input type="email" name="email" placeholder="New Email" required>
        <input type="email" name="confirm_email" placeholder="Confirm New Email" required>
        <button type="submit" name="update_email">Update Email</button>
    </form>

    <form method="POST" onsubmit="return validatePasswordForm();">
        <h3>Change Password</h3>
        <input type="password" name="password" placeholder="New Password" required onkeyup="checkStrength();">
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <p id="strength">Enter a password to check strength</p>
        <p style="font-size:12px;color:#555;">Password must be at least 8 characters, include an uppercase letter, a number, and a special character.</p>
        <button type="submit" name="update_password">Update Password</button>
    </form>

    <form method="POST" onsubmit="return confirmDelete();">
        <h3>Delete Account</h3>
        <p style="color:#dc3545;">This action is permanent and cannot be undone.</p>
        <button type="submit" name="delete_account" class="delete-btn">Delete My Account</button>
    </form>
    <p><a href= <?php echo $origin;?> >&larr; Back to Dashboard</a></p>

</body>
</html>
