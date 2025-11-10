
<?php
    // Show all errors from the PHP interpreter.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  
?>
<?php
/*
  create_account.php
  ------------------
  This script allows clients to create an account.
  It hashes passwords using PHP's password_hash() for secure storage.
*/

require_once 'config.php';

function createPassword($plain_password) {
    return password_hash($plain_password, PASSWORD_DEFAULT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];
    $password_hashed = createPassword($password_plain);

    $conn = getDBConnection();

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO clients (client_username, client_password, client_type) VALUES (?, ?, 'client')");
    if ($stmt->execute([$username, $password_hashed])) {
        $success = true;
    } else {
        $error = "Error creating account. That username may already exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <style>
       body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .form-container {
            width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #4a148c;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }
        .button-blue {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        .button-blue:hover {
            background-color: #0056b3;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #6a1b9a;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .msg {
            text-align: center;
            margin-bottom: 10px;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Create Client Account</h2>

    <?php if (!empty($error)): ?>
        <p class="msg error"><?= htmlspecialchars($error) ?></p>
    <?php elseif (!empty($success)): ?>
        <p class="msg success">Account successfully created!</p>
        <p class="msg">You can now <a href="login.php" style="color:#007bff;text-decoration:none;">log in</a>.</p>
    <?php endif; ?>

    <?php if (empty($success)): ?>
    <form method="post" action="create_account.php">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="button-blue">Create Account</button>
    </form>
    <?php endif; ?>

    <a href="login.php" class="back-link">← Back to Login</a>
</div>

</body>
</html>
