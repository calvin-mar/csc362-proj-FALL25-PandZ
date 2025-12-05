<?php
session_start();
require_once 'config.php';
session_start();
requireLogin();

$conn = getDBConnection();

// Verify current user is an admin govt_worker
$current_user_id = $_SESSION['user_id'] ?? null;
$is_admin = false;
if ($current_user_id) {
    $role_stmt = $conn->prepare("SELECT govt_worker_role FROM govt_workers WHERE client_id = ?");
    if ($role_stmt) {
        $role_stmt->bind_param('i', $current_user_id);
        $role_stmt->execute();
        $res = $role_stmt->get_result();
        $row = $res->fetch_assoc();
        if ($row && isset($row['govt_worker_role']) && $row['govt_worker_role'] === 'Admin') {
            $is_admin = true;
        }
        $role_stmt->close();
    }
}

if (!$is_admin) {
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: govt_worker_dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: govt_worker_dashboard.php');
    exit();
}

// Collect and validate input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$department_client_id = intval($_POST['department_id'] ?? 0);
$role = $_POST['role'] ?? 'Regular';

if ($username === '' || $password === '' || $full_name === '' || $email === '' || $department_client_id <= 0) {
    $_SESSION['error_message'] = 'All fields are required.';
    header('Location: govt_worker_dashboard.php');
    exit();
}

if (!in_array($role, ['Regular', 'Admin'])) {
    $_SESSION['error_message'] = 'Invalid role specified.';
    header('Location: govt_worker_dashboard.php');
    exit();
}

// Ensure username isn't already taken in clients
$stmt = $conn->prepare('SELECT client_id FROM clients WHERE client_username = ?');
if (!$stmt) {
    $_SESSION['error_message'] = 'Database error: ' . $conn->error;
    header('Location: govt_worker_dashboard.php');
    exit();
}
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $_SESSION['error_message'] = 'Username already exists. Choose a different username.';
    $stmt->close();
    $conn->close();
    header('Location: govt_worker_dashboard.php');
    exit();
}
$stmt->close();

// Verify department exists (departments.client_id is the PK in this schema)
$stmt = $conn->prepare('SELECT client_id FROM departments WHERE client_id = ?');
if (!$stmt) {
    $_SESSION['error_message'] = 'Database error: ' . $conn->error;
    header('Location: govt_worker_dashboard.php');
    exit();
}
$stmt->bind_param('i', $department_client_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    $_SESSION['error_message'] = 'Invalid department selected.';
    $stmt->close();
    $conn->close();
    header('Location: govt_worker_dashboard.php');
    exit();
}
$stmt->close();

// Create client account (used for login)
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO clients (client_username, client_password, client_email, client_type) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    $_SESSION['error_message'] = 'Database error: ' . $conn->error;
    header('Location: govt_worker_dashboard.php');
    exit();
}
$client_type = 'govt_worker';
$stmt->bind_param('ssss', $username, $password_hash, $email, $client_type);
if (!$stmt->execute()) {
    $_SESSION['error_message'] = 'Error creating client account: ' . $stmt->error;
    $stmt->close();
    $conn->close();
    header('Location: govt_worker_dashboard.php');
    exit();
}
$new_client_id = $conn->insert_id;
$stmt->close();

// Link into govt_workers (client_id PK, role)
$stmt = $conn->prepare('INSERT INTO govt_workers (client_id, govt_worker_role) VALUES (?, ?)');
if (!$stmt) {
    $_SESSION['error_message'] = 'Database error: ' . $conn->error;
    header('Location: govt_worker_dashboard.php');
    exit();
}
$stmt->bind_param('is', $new_client_id, $role);
if (!$stmt->execute()) {
    $_SESSION['error_message'] = 'Error creating govt worker record: ' . $stmt->error;
    $stmt->close();
    $conn->close();
    header('Location: govt_worker_dashboard.php');
    exit();
}
$stmt->close();

$_SESSION['success_message'] = "Government worker '{$username}' created successfully with role {$role}.";
$conn->close();
header('Location: govt_worker_dashboard.php');
exit();
?>