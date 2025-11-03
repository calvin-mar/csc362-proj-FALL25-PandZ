<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'webuser');
define('DB_PASS', 'rainbows');
define('DB_NAME', 'planning_zoning');

function getDBConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>