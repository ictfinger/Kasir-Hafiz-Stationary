<?php
// Database Configuration
// Prioritize environment variables (for Vercel), fallback to local defaults
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'kasir_hafiz_stationary';
$db_port = getenv('DB_PORT') ?: 3306;
$db_ca   = getenv('DB_SSL_CA') ?: null; // Path to SSL CA certificate if needed

// Create connection
function getConnection() {
    global $db_host, $db_user, $db_pass, $db_name, $db_port, $db_ca;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    
    // SSL Connection handling (often needed for online databases like TiDB/Azure)
    if ($db_ca && file_exists($db_ca)) {
        $conn->ssl_set(NULL, NULL, $db_ca, NULL, NULL);
        $conn->real_connect($db_host, $db_user, $db_pass, $db_name, $db_port, NULL, MYSQLI_CLIENT_SSL);
    }
    
    // Check connection
    if ($conn->connect_error) {
        // Show simplified error in production
        if (getenv('VERCEL')) {
             die("Database connection failed. Please check configuration.");
        }
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Base URL
$base_url = getenv('BASE_URL') ?: 'http://localhost/kasir%20hafiz%20stationary/';
define('BASE_URL', $base_url);
?>
