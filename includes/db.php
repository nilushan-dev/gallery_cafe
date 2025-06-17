<?php
// Database connection configuration
$host = "localhost";
$user = "root";
$password = "";
$dbname = "gallery_cafe_r_db";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set charset (recommended)
$conn->set_charset("utf8mb4");
?>
