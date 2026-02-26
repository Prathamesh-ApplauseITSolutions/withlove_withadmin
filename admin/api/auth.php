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

// Helper function to send email
function sendResetEmail($email, $token) {
    $subject = "Password Reset Request - With Love For You";
    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/wl/admin/reset-password.php?token=" . $token;
    
    $message = "
    <html>
    <head>
        <title>Password Reset</title>
    </head>
    <body>
        <h2>Password Reset Request</h2>
        <p>Hello,</p>
        <p>You requested a password reset for your admin account at With Love For You.</p>
        <p>Click the link below to reset your password:</p>
        <p><a href='$reset_link'>$reset_link</a></p>
        <p>This link will expire in 1 hour.</p>
        <p>If you didn't request this, please ignore this email.</p>
        <br>
        <p>Best regards,<br>With Love For You Team</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@wl.org" . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

// Handle login
if ($_POST['action'] === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = $_POST['remember_me'] ?? false;
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Set session
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            
            // Set remember me cookie if requested
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30 days
                
                setcookie('remember_token', $token, $expires, '/', '', false, true);
                
                // Store token in database (you'd need to add a remember_token column)
                // $stmt = $pdo->prepare("UPDATE admin_users SET remember_token = ? WHERE id = ?");
                // $stmt->execute([$token, $user['id']]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Login failed']);
    }
}

// Handle forgot password
if ($_POST['action'] === 'forgot_password') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Don't reveal if email exists or not for security
            echo json_encode(['success' => true, 'message' => 'If the email exists, a reset link has been sent']);
            exit;
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete any existing tokens for this email
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);
        
        // Insert new token
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);
        
        // Send email
        if (sendResetEmail($email, $token)) {
            echo json_encode(['success' => true, 'message' => 'Password reset link sent to your email']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send reset email']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to process request']);
    }
}

// Handle reset password
if ($_POST['action'] === 'reset_password') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($token) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Token and password are required']);
        exit;
    }
    
    try {
        // Verify token
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired reset token']);
            exit;
        }
        
        // Update user password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $reset['email']]);
        
        // Delete used token
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        
        echo json_encode(['success' => true, 'message' => 'Password reset successful']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
    }
}

// Handle logout
if ($_POST['action'] === 'logout') {
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

// Check authentication status
if ($_POST['action'] === 'check_auth') {
    if (isset($_SESSION['admin_id'])) {
        echo json_encode([
            'success' => true, 
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['admin_id'],
                'email' => $_SESSION['admin_email'],
                'name' => $_SESSION['admin_name'],
                'role' => $_SESSION['admin_role']
            ]
        ]);
    } else {
        echo json_encode(['success' => true, 'authenticated' => false]);
    }
}
?>
