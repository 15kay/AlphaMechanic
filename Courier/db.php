<?php
// Database connection variables
$servername = "localhost";    // Change this if your MySQL server is different
$username = "root";           // Replace with your MySQL username
$password = "";               // Replace with your MySQL password, if you have one
$dbname = "alphamec";         // The name of the database you're connecting to
try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exception
} catch (PDOException $e) {
    // Handle connection errors
    echo "Connection failed: " . $e->getMessage();
    exit(); // Exit if the connection fails
}
?>
