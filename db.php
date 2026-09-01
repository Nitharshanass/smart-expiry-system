<?php

$servername = "db";
$username = "expiry_user";
$password = "expiry_password";
$dbname = "expiry_management";

$conn = new mysqli(
    $servername,
    $username,
    $password,
    $dbname
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>