<?php
session_start();
require_once 'config.php';
requireLogin();

if (getUserType() != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Verify user is admin
$current_user_id = $_SESSION['user_id'] ?? null;
$is_admin = false;
if ($current_user_id) {
    $role_query = "SELECT govt_worker_role FROM govt_workers WHERE govt_worker_id = ?";
    $role_stmt = $conn->prepare($role_query);
    $role_stmt->bind_param("i", $current_user_id);
    $role_stmt->execute();
    $role_result = $role_stmt->get_result();
    if ($role_row = $role_result->fetch_assoc()) {
        $is_admin = ($role_row['govt_worker_role'] === 'Admin');
    }
    $role_stmt->close();
}

if (!$is_admin) {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header('Location: govt_worker_dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department_id = $_POST['department_id'] ?? '';
    $role = $_POST['role'] ?? 'Regular';
    
    // Validate required fields
    if (empty($username) || empty($password) || empty($full_name) || empty($email) || empty($department_id)) {
        $_SESSION['error_message'] = "All fields are required.";
        header('Location: govt_worker_dashboard.php');
        exit();
    }
    
    // Validate role
    if (!in_array($role, ['Regular', 'Admin'])) {
        $_SESSION['error_message'] = "Invalid role specified.";
        header('Location: govt_worker_dashboard.php');
        exit();
    }
    
    // Check if username already exists
    $check_query = "SELECT govt_worker_id FROM govt_workers WHERE govt_worker_username = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Username already exists. Please choose a different username.";
        $check_stmt->close();
        $conn->close();
        header('Location: govt_worker_dashboard.php');
        exit();
    }
    $check_stmt->close();
    
    // Verify department exists
    $dept_check_query = "SELECT department_id FROM departments WHERE department_id = ?";
    $dept_check_stmt = $conn->prepare($dept_check_query);
    $dept_check_stmt->bind_param("i", $department_id);
    $dept_check_stmt->execute();
    $dept_check_result = $dept_check_stmt->get_result();
    
    if ($dept_check_result->num_rows === 0) {
        $_SESSION['error_message'] = "Invalid department selected.";
        $dept_check_stmt->close();
        $conn->close();
        header('Location: govt_worker_dashboard.php');
        exit();
    }
    $dept_check_stmt->close();
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new government worker
    $insert_query = "INSERT INTO govt_workers (govt_worker_username, govt_worker_password_hash, 
                     govt_worker_full_name, govt_worker_email, department_id, govt_worker_role) 
                     VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ssssis", $username, $password_hash, $full_name, $email, $department_id, $role);
    
    if ($insert_stmt->execute()) {
        $_SESSION['success_message'] = "Government worker account '$username' created successfully as $role!";
    } else {
        $_SESSION['error_message'] = "Error creating account: " . $insert_stmt->error;
    }
    
    $insert_stmt->close();
    $conn->close();
    header('Location: govt_worker_dashboard.php');
    exit();
} else {
    // If not POST, redirect back
    header('Location: govt_worker_dashboard.php');
    exit();
}
?>