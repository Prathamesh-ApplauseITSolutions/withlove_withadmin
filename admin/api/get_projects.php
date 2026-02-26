<?php
// Enable comprehensive error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/api_error.log'); // Log to specific file

// Set headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Custom error handler to catch all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Custom exception handler to ensure JSON response
set_exception_handler(function($exception) {
    error_log("FATAL EXCEPTION: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'type' => 'fatal_exception'
    ]);
    exit();
});

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("FATAL ERROR: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => 'fatal_error'
        ]);
    }
});

// Log start of script
error_log("get_projects.php: Script started at " . date('Y-m-d H:i:s'));

function normalizeMediaPath($path) {
    $path = trim((string)$path);
    if ($path === '') {
        return '';
    }

    $path = str_replace('\\', '/', $path);

    // Absolute URL or root-relative path
    if (preg_match('/^https?:\/\//i', $path) || strpos($path, '/') === 0) {
        return $path;
    }

    // For live server path structure
    // Convert relative paths to absolute paths for live server
    if (strpos($path, 'admin/') === 0) {
        return '/new/' . ltrim($path, '/');
    }
    if (strpos($path, 'wl/') === 0) {
        return '/new/' . ltrim($path, '/');
    }
    if (strpos($path, 'newproject_imgs/') === 0) {
        return '/new/' . ltrim($path, '/');
    }

    // Default: assume it's relative to the new directory
    return '/new/' . ltrim($path, './\\/');
}

// Main execution wrapped in try-catch
try {
    error_log("get_projects.php: Starting main execution");
    
    // Include database configuration
    require_once __DIR__ . '/config.php';
    
    // Get database connection
    $pdo = getDatabaseConnection();
    
    error_log("get_projects.php: Database connected successfully with PDO");
    
    // Get and validate category
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    error_log("get_projects.php: Category received: '$category'");
    
    if (empty($category)) {
        throw new Exception("Category parameter is required");
    }
    
    // Simple test query first
    $test_sql = "SELECT 1 as test";
    $test_result = $pdo->query($test_sql);
    if (!$test_result) {
        throw new Exception("Database query test failed");
    }
    error_log("get_projects.php: Database query test passed");
    
    // Check if tables exist
    $tables_check = $pdo->query("SHOW TABLES LIKE 'projects'");
    if ($tables_check->rowCount() == 0) {
        throw new Exception("Projects table does not exist");
    }
    
    $categories_check = $pdo->query("SHOW TABLES LIKE 'project_categories'");
    if ($categories_check->rowCount() == 0) {
        throw new Exception("Project_categories table does not exist");
    }
    
    error_log("get_projects.php: Required tables exist");
    
    // Main query with gallery images
    $sql = "SELECT p.id, p.title, p.description, p.short_description, 
                   p.month_of_execution, p.venue, p.beneficiaries, p.featured_image,
                   p.show_first, p.display_order,
                   GROUP_CONCAT(pi.image_path ORDER BY pi.sort_order) as gallery_images
            FROM projects p
            LEFT JOIN project_categories c ON p.category_id = c.id
            LEFT JOIN project_images pi ON p.id = pi.project_id
            WHERE c.page_identifier = ? AND p.status = 'published'
            GROUP BY p.id
            ORDER BY p.show_first DESC, p.display_order ASC, p.created_at DESC";
    
    error_log("get_projects.php: Preparing main query");
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("SQL prepare failed");
    }
    
    $stmt->execute([$category]);
    
    error_log("get_projects.php: Query executed, found " . $stmt->rowCount() . " projects");
    
    $projects = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Process featured image
        $featuredImage = !empty($row['featured_image']) ? normalizeMediaPath($row['featured_image']) : '';
        
        // If no featured image, use a fallback based on project ID
        if (empty($featuredImage)) {
            $fallbackImages = [
                '/new/newproject_imgs/MGS_img/soni1.jpg',
                '/new/newproject_imgs/blood_donation/1.jpg',
                '/new/newproject_imgs/bookchange/1.jpg',
                '/new/newproject_imgs/cerebral_Palsy/palsy1.jpg'
            ];
            $featuredImage = $fallbackImages[$row['id'] % count($fallbackImages)];
        }
        
        // Process gallery images
        $gallery = [];
        if (!empty($row['gallery_images'])) {
            $galleryPaths = explode(',', $row['gallery_images']);
            foreach ($galleryPaths as $path) {
                if (!empty(trim($path))) {
                    $gallery[] = normalizeMediaPath(trim($path));
                }
            }
        }
        
        // If no gallery images but we have featured image, use it as gallery
        if (empty($gallery) && !empty($featuredImage)) {
            $gallery = [$featuredImage];
        }
        
        $projects[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'short_description' => $row['short_description'],
            'month' => $row['month_of_execution'] ?: 'N/A',
            'venue' => $row['venue'] ?: 'N/A',
            'beneficiaries' => $row['beneficiaries'] ?: 'N/A',
            'image' => $featuredImage,
            'gallery' => $gallery,
            'show_first' => $row['show_first'],
            'display_order' => $row['display_order']
        ];
    }
    
    error_log("get_projects.php: Successfully processed " . count($projects) . " projects");
    
    // Success response
    echo json_encode([
        'success' => true,
        'category' => $category,
        'projects' => $projects,
        'count' => count($projects),
        'debug' => 'script_completed_successfully'
    ]);
    
} catch (Exception $e) {
    error_log("get_projects.php: Exception caught: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    // Try fallback to static data
    try {
        include_once __DIR__ . '/get_projects_fallback.php';
        exit();
    } catch (Exception $fallbackError) {
        // If even fallback fails, return error
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'debug' => 'exception_caught_in_main_block',
            'fallback_error' => $fallbackError->getMessage()
        ]);
    }
} finally {
    error_log("get_projects.php: Script ended at " . date('Y-m-d H:i:s'));
}
?>
