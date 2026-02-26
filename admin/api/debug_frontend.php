<?php
header('Content-Type: application/json');

// Simulate the same API call as the frontend
$category = 'give-with-love';

// Database configuration
$host = 'localhost';
$dbname = 'wl';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Same query as get_projects.php
    $sql = "SELECT p.id, p.title, p.description, p.short_description, 
                   p.month_of_execution, p.venue, p.beneficiaries, p.featured_image,
                   p.show_first, p.display_order
            FROM projects p
            LEFT JOIN project_categories c ON p.category_id = c.id
            WHERE c.page_identifier = ? AND p.status = 'published'
            ORDER BY p.show_first DESC, p.display_order ASC, p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category]);
    
    $projects = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $featuredImage = !empty($row['featured_image']) ? normalizeMediaPath($row['featured_image']) : '';
        
        $project = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'short_description' => $row['short_description'],
            'month' => $row['month_of_execution'] ?: 'N/A',
            'venue' => $row['venue'] ?: 'N/A',
            'beneficiaries' => $row['beneficiaries'] ?: 'N/A',
            'image' => $featuredImage,
            'gallery' => $featuredImage ? [$featuredImage] : [],
            'show_first' => $row['show_first'],
            'display_order' => $row['display_order'],
            'debug_info' => [
                'original_featured_image' => $row['featured_image'],
                'normalized_image' => $featuredImage,
                'full_url' => 'https://www.withlovengo.org' . $featuredImage,
                'image_exists' => !empty($featuredImage)
            ]
        ];
        
        // Also get gallery images from project_images table
        $gallerySql = "SELECT image_path FROM project_images WHERE project_id = ? ORDER BY sort_order ASC";
        $galleryStmt = $pdo->prepare($gallerySql);
        $galleryStmt->execute([$row['id']]);
        
        $galleryImages = [];
        while ($galleryRow = $galleryStmt->fetch(PDO::FETCH_ASSOC)) {
            $normalizedPath = normalizeMediaPath($galleryRow['image_path']);
            $galleryImages[] = $normalizedPath;
        }
        
        if (!empty($galleryImages)) {
            $project['gallery'] = $galleryImages;
            $project['debug_info']['gallery_images'] = $galleryImages;
        }
        
        $projects[] = $project;
    }
    
    echo json_encode([
        'success' => true,
        'category' => $category,
        'projects' => $projects,
        'count' => count($projects),
        'frontend_data_format' => 'This is what the frontend receives'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

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
    if (strpos($path, 'admin/') === 0) {
        return '/new/' . ltrim($path, '/');
    }
    if (strpos($path, 'wl/') === 0) {
        return '/new/' . ltrim($path, '/');
    }
    if (strpos($path, 'newproject_imgs/') === 0) {
        return '/new/' . ltrim($path, '/');
    }

    return '/new/' . ltrim($path, './\\/');
}
?>
