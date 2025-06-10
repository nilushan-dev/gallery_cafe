<?php

$conn = new mysqli("localhost", "root", "", "gallery_cafe_r_db");
if ($conn->connect_error) {
    die("Connection failed: " . $database->connect_error);
}
?>