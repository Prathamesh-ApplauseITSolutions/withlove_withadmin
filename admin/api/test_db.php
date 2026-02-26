<?php
header('Content-Type: application/json');

// Test common database configurations
$configs = [
    // Local XAMPP default
    ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'db' => 'wl'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'db' => 'withlovengo_wl'],
    
    // Common cPanel patterns
    ['host' => 'localhost', 'user' => 'withlovengo_wl', 'pass' => '', 'db' => 'withlovengo_wl'],
    ['host' => 'localhost', 'user' => 'withlove_wl', 'pass' => '', 'db' => 'withlove_wl'],
    ['host' => 'localhost', 'user' => 'wl_user', 'pass' => '', 'db' => 'wl_db'],
    
    // Try with common passwords
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root', 'db' => 'wl'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'password', 'db' => 'wl'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => '123456', 'db' => 'wl'],
];

$results = [];

foreach ($configs as $i => $config) {
    try {
        $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
        
        if ($conn->connect_error) {
            $results[] = [
                'config' => $config,
                'status' => 'failed',
                'error' => $conn->connect_error
            ];
        } else {
            // Test if tables exist
            $tables_check = $conn->query("SHOW TABLES LIKE 'projects'");
            $has_projects = $tables_check->num_rows > 0;
            
            $categories_check = $conn->query("SHOW TABLES LIKE 'project_categories'");
            $has_categories = $categories_check->num_rows > 0;
            
            $results[] = [
                'config' => $config,
                'status' => 'success',
                'has_projects_table' => $has_projects,
                'has_categories_table' => $has_categories,
                'message' => $has_projects && $has_categories ? 'PERFECT MATCH!' : 'Connected but missing tables'
            ];
            
            $conn->close();
        }
    } catch (Exception $e) {
        $results[] = [
            'config' => $config,
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
}

echo json_encode([
    'success' => true,
    'tested_configs' => count($configs),
    'results' => $results
]);
?>
