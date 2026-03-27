<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "focustrack";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    $conn = null;
}
?>
