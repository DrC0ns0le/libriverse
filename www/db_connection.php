<?php
// Database connection parameters
$servername = "localhost"; // Replace with your server name if different
$username = "root"; // Replace with your database username
$password = "rootpassword"; // Replace with your database password
$dbname = "libriverse"; // Replace with your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset("utf8");

// Uncomment the following line to enable error reporting for SQL queries
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>
