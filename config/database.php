<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "student_management_system";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>