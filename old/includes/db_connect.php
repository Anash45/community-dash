<?php
// Database credentials
$dbHost = "localhost"; // Change this to your database host
$dbUser = "your_username"; // Change this to your database username
$dbPassword = "your_password"; // Change this to your database password
$dbName = "your_database_name"; // Change this to your database name

// Create connection
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
