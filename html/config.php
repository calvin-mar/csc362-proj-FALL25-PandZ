<?php
session_start();

//Create and return a MySQLi connection to p&z db
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
//Check if a user is currently logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}
// Enforce that user is logged in before accessing a page
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
// Get the current logged in user's role.
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}
// Get the current logged in user's ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>