<?php
session_start();
// Database credentials
// $host = "localhost"; // Change this if your database is hosted on a different server
// $username = "uwjfknevadf2g"; // Change this to your MySQL username
// $password = "nwobgfwc5a9r"; // Change this to your MySQL password
// $database = "dbmjogfpv5z0z0"; // Change this to your MySQL database name


$host = "localhost"; // Change this if your database is hosted on a different server
$username = "root"; // Change this to your MySQL username
$password = "root"; // Change this to your MySQL password
$database = "mission_berlin_db"; // Change this to your MySQL database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
