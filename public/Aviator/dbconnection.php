<?php

$servername = "localhost";
$username = "masterpro";
$password = "masterpro";
$dbname = "masterpro";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

