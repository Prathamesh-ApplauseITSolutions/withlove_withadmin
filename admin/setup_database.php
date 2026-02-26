<?php
// Database setup script
// Note: This script uses hardcoded credentials for initial setup
// You can modify these to match your cPanel credentials if needed
$host = 'localhost';
$dbname = 'withlove';
$username = 'withlove';
$password = 'Applause@2026';

try {
    // Connect to MySQL (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL successfully!<br>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' created or already exists!<br>";
    
    // Switch to the database
    $pdo->exec("USE $dbname");
    
    // Create admin_users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT TRUE,
            last_login DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'admin_users' created or already exists!<br>";
    
    // Create password_resets table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_token (token)
        )
    ");
    echo "Table 'password_resets' created or already exists!<br>";
    
    // Create projects table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(200) NOT NULL,
            slug VARCHAR(200) UNIQUE NOT NULL,
            description TEXT NOT NULL,
            short_description VARCHAR(500),
            category VARCHAR(100),
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            featured_image VARCHAR(255),
            meta_title VARCHAR(200),
            meta_description VARCHAR(300),
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_category (category),
            INDEX idx_created_by (created_by)
        )
    ");
    echo "Table 'projects' created or already exists!<br>";
    
    // Create project_images table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_images (
            id INT PRIMARY KEY AUTO_INCREMENT,
            project_id INT NOT NULL,
            image_name VARCHAR(255) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            alt_text VARCHAR(200),
            sort_order INT DEFAULT 0,
            is_featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_project_id (project_id),
            INDEX idx_sort_order (sort_order)
        )
    ");
    echo "Table 'project_images' created or already exists!<br>";
    
    // Create project_categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'project_categories' created or already exists!<br>";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE email = ?");
    $stmt->execute(['admin@wl.org']);
    $userCount = $stmt->fetchColumn();
    
    if ($userCount == 0) {
        // Create admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO admin_users (username, email, password, full_name, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(['admin', 'admin@wl.org', $hashedPassword, 'Super Administrator', 'super_admin']);
        echo "Admin user created successfully for With Love For You!<br>";
        echo "Email: admin@wl.org<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Admin user already exists!<br>";
    }
    
    // Insert sample categories if they don't exist
    $categories = [
        ['Education', 'education', 'Projects focused on educational initiatives'],
        ['Healthcare', 'healthcare', 'Health and wellness related projects'],
        ['Community Development', 'community-development', 'Community upliftment projects'],
        ['Environment', 'environment', 'Environmental conservation projects'],
        ['Youth Empowerment', 'youth-empowerment', 'Projects empowering young people']
    ];
    
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM project_categories WHERE slug = ?");
        $stmt->execute([$category[1]]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO project_categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute($category);
        }
    }
    echo "Sample categories inserted/verified!<br>";
    
    echo "<br><strong>With Love For You Admin Database setup completed successfully!</strong><br>";
    echo "You can now login at: <a href='login.html'>admin/login.html</a><br>";
    echo "Email: admin@wl.org<br>";
    echo "Password: admin123<br>";
    
} catch(PDOException $e) {
    echo "Database setup error: " . $e->getMessage();
}
?>
