<?php
$servername = "10.2.1.15:3306";
$username = "root";
$password = "rootpassword";
$dbname = "libriverse";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
