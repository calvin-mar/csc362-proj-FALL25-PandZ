<?php
    // Show all errors from the PHP interpreter.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    // Show all errors from the MySQLi Extension.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  

$config = parse_ini_file('/home/calvinmar/mysqli.ini');


$conn = new mysqli(
    $config['mysqli.personal_host'],
    $config['mysqli.personal_user'],
    $config['mysqli.personal_pw'],
    "planning_zoning");

if ($conn->connect_errno) {
    echo "Error: Failed to make a MySQL connection, here is why: ". "<br>";
    echo "Errno: " . $conn->connect_errno . "\n";
    echo "Error: " . $conn->connect_error . "\n";
    exit; // Quit this PHP script if the connection fails.
} 
$insert_query = file_get_contents("czma_insert.sql");
$query_result = $conn->query($insert_query);
?>