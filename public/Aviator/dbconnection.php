<?php

$servername = "localhost";
$username = "admin_zupiter";
$password = "admin_zupiter";
$dbname = "admin_zupiter";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

