<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "student_management_system";

function get_db_connection(): mysqli
{
    static $connection = null;

    global $host, $username, $password, $database;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $connection = new mysqli($host, $username, $password, $database);

    if ($connection->connect_error) {
        die("Database connection failed: " . $connection->connect_error);
    }

    return $connection;
}

$conn = get_db_connection();
