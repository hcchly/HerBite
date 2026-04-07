<?php
$host = "localhost";
$username = "root";
$password = "root";
$database = "herbits_db";

$conn = new mysqli($host, $username, $password, $database,8888);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
