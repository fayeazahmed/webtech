<?php

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'social';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
