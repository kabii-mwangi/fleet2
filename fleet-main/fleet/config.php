<?php
// Database configuration - Update these values with your hosting details
$db_host = 'localhost:3306';  // Your MySQL server host
$db_name = 'maggie_fleet';  // Your database name
$db_user = 'maggie_mwas';  // Your MySQL username
$db_pass = 'Mwaskabii123#';  // Your MySQL password

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed. Please check your database configuration in config.php");
}

// Authentication check function
function requireAuth() {
    session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: index.php');
        exit;
    }
}

// Format currency in KSH
function formatCurrency($amount) {
    return 'KSH ' . number_format($amount, 2);
}

// Format date
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

// Calculate efficiency (km/L)
function calculateEfficiency($distance, $fuel) {
    return $fuel > 0 ? round($distance / $fuel, 2) : 0;
}
?>