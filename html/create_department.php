<?php
/**
 * Allow a government worker to create a new department record in departments table
 * User must be logged in w/ type govt_worker with admin role.
 * On POST:
 *  - Validate department name provided and check for duplicates
 *  - Insert new department and redirect back to dashboard.
 */
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
    $department_name = trim($_POST['department_name'] ?? '');
    $department_description = trim($_POST['department_description'] ?? '');
    
    if (empty($department_name)) {
        $_SESSION['error_message'] = "Department name is required.";
        header('Location: govt_worker_dashboard.php');
        exit();
    }
    
    // Check if department already exists
    $check_query = "SELECT department_id FROM departments WHERE department_name = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $department_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "A department with this name already exists.";
        $check_stmt->close();
        $conn->close();
        header('Location: govt_worker_dashboard.php');
        exit();
    }
    $check_stmt->close();
    
    // Insert new department
    $insert_query = "INSERT INTO departments (department_name, department_description) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ss", $department_name, $department_description);
    
    if ($insert_stmt->execute()) {
        $_SESSION['success_message'] = "Department '$department_name' created successfully!";
    } else {
        $_SESSION['error_message'] = "Error creating department: " . $insert_stmt->error;
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