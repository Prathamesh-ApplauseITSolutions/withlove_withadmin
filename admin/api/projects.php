<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Start session
session_start();

// Include database configuration
require_once __DIR__ . '/config.php';

// Check if user is authenticated
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get database connection
try {
    $pdo = getDatabaseConnection();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get action from POST or GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// If no action, return error
if (empty($action)) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadsDir = __DIR__ . '/../uploads/projects/';
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Set proper permissions
chmod($uploadsDir, 0755);

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_stats':
        getStats();
        break;
    case 'get_recent_projects':
        getRecentProjects();
        break;
    case 'get_all_projects':
        getAllProjects();
        break;
    case 'get_project':
        getProject();
        break;
    case 'add_project':
        addProject();
        break;
    case 'update_project':
        updateProject();
        break;
    case 'delete_project':
        deleteProject();
        break;
    case 'delete_project_image':
        deleteProjectImage();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getProjectColumns() {
    global $pdo;

    static $columns = null;
    if ($columns !== null) {
        return $columns;
    }

    $columns = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM projects");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['Field']] = true;
    }
    return $columns;
}

function hasProjectColumn($columnName) {
    $columns = getProjectColumns();
    return isset($columns[$columnName]);
}

function resolveCategoryIdByName($categoryName) {
    global $pdo;

    if (empty($categoryName)) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM project_categories WHERE name = ? LIMIT 1");
        $stmt->execute([$categoryName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    } catch (Exception $e) {
        return null;
    }
}

function resolveCategoryNameById($categoryId) {
    global $pdo;

    if (empty($categoryId)) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT name FROM project_categories WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$categoryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['name'] : null;
    } catch (Exception $e) {
        return null;
    }
}

function getStats() {
    global $pdo;
    
    try {
        // Get total projects
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
        $totalProjects = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get published projects
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects WHERE status = 'published'");
        $publishedProjects = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get draft projects
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects WHERE status = 'draft'");
        $draftProjects = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get total images
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM project_images");
        $totalImages = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total_projects' => $totalProjects,
                'published_projects' => $publishedProjects,
                'draft_projects' => $draftProjects,
                'total_images' => $totalImages
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to get stats']);
    }
}

function getRecentProjects() {
    global $pdo;
    
    $limit = (int)($_GET['limit'] ?? 5);
    if ($limit < 1) {
        $limit = 5;
    }
    if ($limit > 50) {
        $limit = 50;
    }
    
    try {
        $categorySelect = "'Uncategorized' AS category_name";
        $categoryJoin = '';

        if (hasProjectColumn('category_id')) {
            $categoryJoin = "LEFT JOIN project_categories c ON p.category_id = c.id";
            if (hasProjectColumn('category')) {
                $categorySelect = "COALESCE(c.name, p.category, 'Uncategorized') AS category_name";
            } else {
                $categorySelect = "COALESCE(c.name, 'Uncategorized') AS category_name";
            }
        } elseif (hasProjectColumn('category')) {
            $categorySelect = "COALESCE(p.category, 'Uncategorized') AS category_name";
        }

        $sql = "
            SELECT
                p.id,
                p.title,
                p.status,
                p.created_at,
                $categorySelect,
                COALESCE(img.image_count, 0) AS image_count
            FROM projects p
            $categoryJoin
            LEFT JOIN (
                SELECT project_id, COUNT(*) AS image_count
                FROM project_images
                GROUP BY project_id
            ) img ON p.id = img.project_id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $projects]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to get recent projects: ' . $e->getMessage()]);
    }
}

function getAllProjects() {
    global $pdo;
    
    $page = (int)($_GET['page'] ?? 1);
    if ($page < 1) {
        $page = 1;
    }
    $limit = (int)($_GET['limit'] ?? 10);
    if ($limit < 1) {
        $limit = 10;
    }
    if ($limit > 100) {
        $limit = 100;
    }
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $status = trim($_GET['status'] ?? '');
    
    try {
        $joins = [];
        $categorySelect = "'Uncategorized' AS category_name";

        $hasCategoryId = hasProjectColumn('category_id');
        $hasCategory = hasProjectColumn('category');

        if ($hasCategoryId) {
            $joins[] = "LEFT JOIN project_categories c ON p.category_id = c.id";
            if ($hasCategory) {
                $categorySelect = "COALESCE(c.name, p.category, 'Uncategorized') AS category_name";
            } else {
                $categorySelect = "COALESCE(c.name, 'Uncategorized') AS category_name";
            }
        } elseif ($hasCategory) {
            $categorySelect = "COALESCE(p.category, 'Uncategorized') AS category_name";
        }

        $joins[] = "
            LEFT JOIN (
                SELECT project_id, COUNT(*) AS image_count
                FROM project_images
                GROUP BY project_id
            ) img ON p.id = img.project_id
        ";

        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($category)) {
            if ($hasCategoryId && ctype_digit($category)) {
                $where[] = "p.category_id = ?";
                $params[] = (int)$category;
            } elseif ($hasCategory) {
                $where[] = "p.category = ?";
                $params[] = $category;
            } elseif ($hasCategoryId) {
                $where[] = "c.name = ?";
                $params[] = $category;
            }
        }
        
        if (!empty($status)) {
            $where[] = "p.status = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $joinClause = implode("\n", $joins);
        
        $countSql = "
            SELECT COUNT(*) as total 
            FROM projects p
            $joinClause
            $whereClause
        ";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalProjects = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $sql = "
            SELECT
                p.id,
                p.title,
                p.short_description,
                p.status,
                p.created_at,
                $categorySelect,
                COALESCE(img.image_count, 0) as image_count
            FROM projects p
            $joinClause
            $whereClause
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalPages = max(1, (int)ceil($totalProjects / $limit));
        $from = $totalProjects > 0 ? $offset + 1 : 0;
        $to = $totalProjects > 0 ? min($offset + $limit, $totalProjects) : 0;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'projects' => $projects,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_projects' => $totalProjects,
                    'per_page' => $limit,
                    'from' => $from,
                    'to' => $to,
                    'total' => $totalProjects
                ]
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to get projects: ' . $e->getMessage()]);
    }
}

function getProject() {
    global $pdo;
    
    $projectId = $_GET['id'] ?? '';
    
    if (empty($projectId)) {
        echo json_encode(['success' => false, 'message' => 'Project ID is required']);
        return;
    }
    
    try {
        // Get project details
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }
        
        // Get project images
        $stmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order");
        $stmt->execute([$projectId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $project['images'] = $images;
        
        echo json_encode(['success' => true, 'data' => $project]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to get project']);
    }
}

function addProject() {
    global $pdo, $uploadsDir;

    $title = trim($_POST['title'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $month_of_execution = trim($_POST['month_of_execution'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $beneficiaries = trim($_POST['beneficiaries'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $show_first = isset($_POST['show_first']) ? (int)$_POST['show_first'] : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $featured_image = trim($_POST['featured_image'] ?? '');

    $categoryId = trim($_POST['category_id'] ?? '');
    $categoryName = trim($_POST['category'] ?? '');

    if (empty($title) || empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Title and description are required']);
        return;
    }

    if (hasProjectColumn('category_id') && empty($categoryId) && !empty($categoryName)) {
        $resolvedId = resolveCategoryIdByName($categoryName);
        if (!empty($resolvedId)) {
            $categoryId = $resolvedId;
        }
    }
    if (hasProjectColumn('category') && empty($categoryName) && !empty($categoryId)) {
        $resolvedName = resolveCategoryNameById($categoryId);
        if (!empty($resolvedName)) {
            $categoryName = $resolvedName;
        }
    }

    if (hasProjectColumn('category_id') && empty($categoryId) && hasProjectColumn('category') && empty($categoryName)) {
        echo json_encode(['success' => false, 'message' => 'Category is required']);
        return;
    }

    // Create slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    if ($slug === '') {
        $slug = 'project';
    }
    $originalSlug = $slug;
    $counter = 1;

    // Check if slug exists
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM projects WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->rowCount() == 0) {
            break;
        }
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    try {
        $insertColumns = [];
        $insertValues = [];
        $placeholders = [];

        $addColumnValue = function($columnName, $value) use (&$insertColumns, &$insertValues, &$placeholders) {
            if (!hasProjectColumn($columnName)) {
                return;
            }
            $insertColumns[] = $columnName;
            $insertValues[] = $value;
            $placeholders[] = '?';
        };

        $addColumnValue('title', $title);
        $addColumnValue('slug', $slug);
        $addColumnValue('short_description', $shortDescription);
        $addColumnValue('description', $description);
        $addColumnValue('category_id', !empty($categoryId) ? (int)$categoryId : null);
        $addColumnValue('category', $categoryName);
        $addColumnValue('status', $status);
        $addColumnValue('created_by', $_SESSION['admin_id'] ?? null);
        $addColumnValue('month_of_execution', $month_of_execution);
        $addColumnValue('venue', $venue);
        $addColumnValue('beneficiaries', $beneficiaries);
        $addColumnValue('display_order', $display_order);
        $addColumnValue('show_first', $show_first);
        $addColumnValue('meta_title', $meta_title);
        $addColumnValue('meta_description', $meta_description);
        // Don't add featured_image here - it will be set after file upload

        if (empty($insertColumns)) {
            echo json_encode(['success' => false, 'message' => 'Projects table schema is invalid']);
            return;
        }

        $sql = "INSERT INTO projects (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($insertValues);

        $projectId = $pdo->lastInsertId();

        // Handle featured image upload
        if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
            error_log("Featured image file detected in addProject: " . print_r($_FILES['featured_image_file'], true));
            $featuredImagePath = handleFeaturedImageUpload($_FILES['featured_image_file']);
            if ($featuredImagePath) {
                error_log("Featured image path in addProject: " . $featuredImagePath);
                // Update the project with the featured image path
                $updateStmt = $pdo->prepare("UPDATE projects SET featured_image = ? WHERE id = ?");
                $result = $updateStmt->execute([$featuredImagePath, $projectId]);
                error_log("Featured image update result in addProject: " . ($result ? 'success' : 'failed'));
            } else {
                error_log("Featured image upload failed in addProject");
            }
        } else {
            error_log("No featured image file uploaded in addProject or error: " . ($_FILES['featured_image_file']['error'] ?? 'no file'));
        }

        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            handleImageUploads($projectId, $_FILES['images']);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Project created successfully',
            'project_id' => $projectId
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to create project: ' . $e->getMessage()]);
    }
}

function updateProject() {
    global $pdo, $uploadsDir;

    $projectId = $_POST['id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $month_of_execution = trim($_POST['month_of_execution'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $beneficiaries = trim($_POST['beneficiaries'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $show_first = isset($_POST['show_first']) ? (int)$_POST['show_first'] : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $categoryId = trim($_POST['category_id'] ?? '');
    $categoryName = trim($_POST['category'] ?? '');

    if (empty($projectId) || empty($title) || empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Project ID, title, and description are required']);
        return;
    }

    if (hasProjectColumn('category_id') && empty($categoryId) && !empty($categoryName)) {
        $resolvedId = resolveCategoryIdByName($categoryName);
        if (!empty($resolvedId)) {
            $categoryId = $resolvedId;
        }
    }
    if (hasProjectColumn('category') && empty($categoryName) && !empty($categoryId)) {
        $resolvedName = resolveCategoryNameById($categoryId);
        if (!empty($resolvedName)) {
            $categoryName = $resolvedName;
        }
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            return;
        }

        $setClauses = [];
        $params = [];

        $addSet = function($columnName, $value) use (&$setClauses, &$params) {
            if (!hasProjectColumn($columnName)) {
                return;
            }
            $setClauses[] = "$columnName = ?";
            $params[] = $value;
        };

        $addSet('title', $title);
        $addSet('short_description', $shortDescription);
        $addSet('description', $description);
        $addSet('category_id', !empty($categoryId) ? (int)$categoryId : null);
        $addSet('category', $categoryName);
        $addSet('status', $status);
        $addSet('month_of_execution', $month_of_execution);
        $addSet('venue', $venue);
        $addSet('beneficiaries', $beneficiaries);
        $addSet('display_order', $display_order);
        $addSet('show_first', $show_first);
        $addSet('meta_title', $meta_title);
        $addSet('meta_description', $meta_description);

        if (hasProjectColumn('updated_at')) {
            $setClauses[] = "updated_at = NOW()";
        }

        if (empty($setClauses)) {
            echo json_encode(['success' => false, 'message' => 'No updatable fields found']);
            return;
        }

        $sql = "UPDATE projects SET " . implode(', ', $setClauses) . " WHERE id = ?";
        $params[] = $projectId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Handle featured image upload
        if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
            $featuredImagePath = handleFeaturedImageUpload($_FILES['featured_image_file']);
            if ($featuredImagePath) {
                // Update the project with the featured image path
                $updateStmt = $pdo->prepare("UPDATE projects SET featured_image = ? WHERE id = ?");
                $updateStmt->execute([$featuredImagePath, $projectId]);
            }
        }

        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            handleImageUploads($projectId, $_FILES['images']);
        }

        echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update project: ' . $e->getMessage()]);
    }
}

function deleteProject() {
    global $pdo;
    
    $projectId = $_POST['id'] ?? '';
    
    if (empty($projectId)) {
        echo json_encode(['success' => false, 'message' => 'Project ID is required']);
        return;
    }
    
    try {
        // Get project images to delete files
        $stmt = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Delete image files
        foreach ($images as $image) {
            $imagePath = __DIR__ . '/../..' . $image['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete project (cascade will delete project_images)
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        
        echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete project']);
    }
}

function deleteProjectImage() {
    global $pdo;

    $imageId = $_POST['image_id'] ?? '';
    $projectId = $_POST['project_id'] ?? '';

    if (empty($imageId) || empty($projectId)) {
        echo json_encode(['success' => false, 'message' => 'Image ID and Project ID are required']);
        return;
    }

    try {
        // Validate image belongs to project and fetch file path
        $stmt = $pdo->prepare("SELECT id, image_path FROM project_images WHERE id = ? AND project_id = ?");
        $stmt->execute([(int)$imageId, (int)$projectId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            echo json_encode(['success' => false, 'message' => 'Image not found']);
            return;
        }

        // Delete DB record first
        $stmt = $pdo->prepare("DELETE FROM project_images WHERE id = ? AND project_id = ?");
        $stmt->execute([(int)$imageId, (int)$projectId]);

        // Delete file if present
        if (!empty($image['image_path'])) {
            $imagePath = __DIR__ . '/../..' . $image['image_path'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Image removed successfully']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to remove image: ' . $e->getMessage()]);
    }
}

function handleImageUploads($projectId, $files) {
    global $pdo, $uploadsDir;
    
    $imageCount = count($files['name']);
    
    for ($i = 0; $i < $imageCount; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = $files['name'][$i];
            $fileTmpPath = $files['tmp_name'][$i];
            $fileSize = $files['size'][$i];
            $fileType = $files['type'][$i];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($fileType, $allowedTypes)) {
                continue;
            }
            
            // Generate unique filename
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = uniqid('project_', true) . '.' . $fileExtension;
            $uploadPath = $uploadsDir . $uniqueFileName;
            
            // Move file
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                // Insert into database with correct path for live server
                $relativePath = '/new/admin/uploads/projects/' . $uniqueFileName;
                $stmt = $pdo->prepare("
                    INSERT INTO project_images (project_id, image_name, image_path, sort_order) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$projectId, $fileName, $relativePath, $i]);
            }
        }
    }
}

function handleFeaturedImageUpload($file) {
    global $uploadsDir;
    
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileType = $file['type'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueFileName = 'featured_' . uniqid('project_', true) . '.' . $fileExtension;
    $uploadPath = $uploadsDir . $uniqueFileName;
    
    // Move file
    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
        // Return correct path for live server
        return '/new/admin/uploads/projects/' . $uniqueFileName;
    }
    
    return false;
}
?>
