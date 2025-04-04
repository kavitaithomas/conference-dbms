<?php
$host = "localhost";
$dbname = "conferenceDB"; 
$username = "Kavita Thomas"; // Default XAMPP MySQL user
$password = "lola";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>