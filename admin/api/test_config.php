<?php
// Test script to verify database configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Include database configuration
require_once __DIR__ . '/config.php';

try {
    // Test database connection
    $pdo = getDatabaseConnection();
    
    // Test a simple query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    // Check if required tables exist
    $tables = [];
    $table_check = $pdo->query("SHOW TABLES");
    while ($row = $table_check->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database configuration is working correctly',
        'database' => DB_NAME,
        'host' => DB_HOST,
        'user' => DB_USER,
        'tables_found' => count($tables),
        'tables' => $tables,
        'test_query' => $result['test'] === 1
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration test failed',
        'error' => $e->getMessage(),
        'database' => DB_NAME,
        'host' => DB_HOST,
        'user' => DB_USER
    ]);
}
?>
