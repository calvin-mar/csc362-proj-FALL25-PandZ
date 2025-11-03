<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    
    $conn = getDBConnection();
    
    try {
        if ($user_type == 'client') {
            $stmt = $conn->prepare("SELECT client_id, client_username, client_password FROM clients WHERE client_username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['client_password'])) {
                $_SESSION['user_id'] = $user['client_id'];
                $_SESSION['user_type'] = 'client';
                $_SESSION['username'] = $user['client_username'];
                header('Location: client_dashboard.php');
                exit();
            }
        } elseif ($user_type == 'department') {
            $stmt = $conn->prepare("SELECT department_id, department_name, department_password FROM departments WHERE department_name = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['department_password'])) {
                $_SESSION['user_id'] = $user['department_id'];
                $_SESSION['user_type'] = 'department';
                $_SESSION['username'] = $user['department_name'];
                header('Location: department_dashboard.php');
                exit();
            }
        } elseif ($user_type == 'govt_worker') {
            $stmt = $conn->prepare("SELECT worker_id, worker_username, worker_password, worker_name FROM govt_workers WHERE worker_username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['worker_password'])) {
                $_SESSION['user_id'] = $user['worker_id'];
                $_SESSION['user_type'] = 'govt_worker';
                $_SESSION['username'] = $user['worker_name'];
                header('Location: govt_worker_dashboard.php');
                exit();
            }
        }
        
        $error = 'Invalid username or password';
    } catch(PDOException $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P&Z Database - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>P&Z Database Login</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>User Type</label>
                <select name="user_type" required>
                    <option value="">Select User Type</option>
                    <option value="client">Client</option>
                    <option value="department">Department</option>
                    <option value="govt_worker">Government Worker</option>
                </select>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>