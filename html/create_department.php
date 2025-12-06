<?php
session_start();
require_once 'config.php';
session_start();
requireLogin();

if (getUserType() != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Verify current user is admin
$current_user_id = $_SESSION['user_id'] ?? null;
$is_admin = false;
if ($current_user_id) {
    $role_stmt = $conn->prepare("SELECT govt_worker_role FROM govt_workers WHERE client_id = ?");
    if ($role_stmt) {
        $role_stmt->bind_param('i', $current_user_id);
        $role_stmt->execute();
        $res = $role_stmt->get_result();
        $row = $res->fetch_assoc();
        if ($row && ($row['govt_worker_role'] ?? '') === 'Admin') {
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

$department_name = trim($_POST['department_name'] ?? '');
$department_description = trim($_POST['department_description'] ?? '');
$password = $_POST['password'] ?? '';

if ($department_name === '' || $password === '') {
    $_SESSION['error_message'] = 'Department name and password are required.';
    header('Location: govt_worker_dashboard.php');
    exit();
}

// Generate a username from department name (slug), ensure uniqueness
function slugify($s) {
    $s = preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim($s)));
    $s = trim($s, '_');
    return $s ?: 'dept';
}

$base_username = slugify($department_name);
$username = $base_username;
$i = 1;
while (true) {
    $stmt = $conn->prepare('SELECT client_id FROM clients WHERE client_username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 0) {
        $stmt->close();
        break;
    }
    $stmt->close();
    $i++;
    $username = $base_username . $i;
}

// Create client account for department
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$client_type = 'department';
$stmt = $conn->prepare('INSERT INTO clients (client_username, client_password, client_email, client_type) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    $_SESSION['error_message'] = 'Database error: ' . $conn->error;
    header('Location: govt_worker_dashboard.php');
    exit();
}
$email = null; // no email provided by form
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

// Insert department row (departments.client_id is PK)
$stmt = $conn->prepare('INSERT INTO departments (client_id, department_name) VALUES (?, ?)');
if (!$stmt) {
    $_SESSION['error_message'] = 'Database error: ' . $conn->error;
    header('Location: govt_worker_dashboard.php');
    exit();
}
$stmt->bind_param('is', $new_client_id, $department_name);
if (!$stmt->execute()) {
    $_SESSION['error_message'] = 'Error creating department: ' . $stmt->error;
    $stmt->close();
    $conn->close();
    header('Location: govt_worker_dashboard.php');
    exit();
}
$stmt->close();

$_SESSION['success_message'] = "Department '{$department_name}' created successfully. Login username: {$username}";
$conn->close();
header('Location: govt_worker_dashboard.php');
exit();
?>