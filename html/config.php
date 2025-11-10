<?php
session_start();
function getDBConnection() {
    try {

        $twoLevelsUp = dirname(dirname(getcwd())); 
        $config = parse_ini_file($twoLevelsUp ."/". "mysqli.ini");
        $conn = new mysqli(
            $config['mysqli.webuser_host'],
            $config['mysqli.webuser_user'],
            $config['mysqli.webuser_pw'],
            "planning_zoning");

        return $conn;
    } catch(mysqli_sql_exception $e) {
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