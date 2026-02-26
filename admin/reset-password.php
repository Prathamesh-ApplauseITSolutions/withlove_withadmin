<?php
session_start();
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: login.php');
    exit;
}

// Include database configuration
require_once __DIR__ . '/api/config.php';

try {
    $pdo = getDatabaseConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Database connection failed');
}

// Verify token
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    $error = "Invalid or expired reset link";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - With Love For You</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ef3090',
                        secondary: '#ff6b9d',
                    },
                    animation: {
                        'slideUp': 'slideUp 0.6s ease-out',
                        'spin': 'spin 1s linear infinite',
                    },
                    keyframes: {
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 p-4">
    <?php if (isset($error)): ?>
        <!-- Error Message -->
        <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl p-8 max-w-md w-full animate-slideUp">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Reset Link Error</h2>
                <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($error); ?></p>
                <a href="login.php" class="inline-block bg-gradient-to-r from-primary to-secondary text-white font-semibold px-6 py-3 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                    Back to Login
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Reset Form -->
        <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl p-6 sm:p-8 max-w-lg w-full animate-slideUp">
            <div class="text-center mb-8">
                <img src="../img/logo.jpg" alt="With Love For You Logo" class="w-20 h-auto mx-auto mb-4 rounded-lg">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Reset Password</h2>
                <p class="text-gray-600">Enter your new password below</p>
            </div>
            
            <div id="alert-container" class="mb-6"></div>
            
            <form id="resetPasswordForm" class="space-y-6">
                <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div>
                    <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-300"
                        placeholder="Enter your new password">
                    <div id="passwordStrength" class="hidden mt-2 h-1 rounded-full transition-all duration-300"></div>
                </div>
                
                <div>
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-300"
                        placeholder="Confirm your new password">
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden group">
                    <span class="relative z-10 flex items-center justify-center">
                        <span id="resetText">Reset Password</span>
                        <div id="resetSpinner" class="hidden ml-2 w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-500"></div>
                </button>
            </form>
            
            <div class="text-center mt-6">
                <a href="login.php" class="text-primary hover:text-secondary font-medium transition-colors duration-300">
                    Back to Login
                </a>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Password strength indicator
            $('#newPassword').on('input', function() {
                const password = $(this).val();
                const strengthDiv = $('#passwordStrength');
                
                if (password.length === 0) {
                    strengthDiv.addClass('hidden').removeClass('bg-red-500 bg-yellow-500 bg-green-500');
                    return;
                }
                
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[$@#&!]+/)) strength++;
                
                strengthDiv.removeClass('hidden bg-red-500 bg-yellow-500 bg-green-500');
                
                if (strength <= 2) {
                    strengthDiv.addClass('bg-red-500');
                } else if (strength === 3 || strength === 4) {
                    strengthDiv.addClass('bg-yellow-500');
                } else {
                    strengthDiv.addClass('bg-green-500');
                }
            });

            // Reset password form submission
            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();
                const token = $('#token').val();
                
                // Validation
                if (newPassword.length < 8) {
                    showAlert('danger', 'Password must be at least 8 characters long');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showAlert('danger', 'Passwords do not match');
                    return;
                }
                
                // Show loading
                $('#resetText').text('Resetting...');
                $('#resetSpinner').removeClass('hidden');
                
                $.ajax({
                    url: 'api/auth.php',
                    type: 'POST',
                    data: {
                        action: 'reset_password',
                        token: token,
                        password: newPassword
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#resetSpinner').addClass('hidden');
                        $('#resetText').text('Reset Password');
                        
                        if (response.success) {
                            showAlert('success', 'Password reset successful! Redirecting to login...');
                            setTimeout(() => {
                                window.location.href = 'login.php';
                            }, 2000);
                        } else {
                            showAlert('danger', response.message || 'Failed to reset password');
                        }
                    },
                    error: function() {
                        $('#resetSpinner').addClass('hidden');
                        $('#resetText').text('Reset Password');
                        showAlert('danger', 'Network error. Please try again.');
                    }
                });
            });
        });

        function showAlert(type, message) {
            const alertColors = {
                success: 'bg-green-50 border-green-200 text-green-800',
                danger: 'bg-red-50 border-red-200 text-red-800',
                warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
                info: 'bg-blue-50 border-blue-200 text-blue-800'
            };
            
            const alertHtml = `
                <div class="border rounded-xl p-4 mb-4 animate-slideUp ${alertColors[type]} flex justify-between items-center">
                    <span>${message}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="ml-4 text-current hover:opacity-70">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            $('#alert-container').html(alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('.border').first().fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>
</body>
</html>
