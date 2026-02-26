<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - With Love For You</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ef3090',
                        secondary: '#ff6b9d',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 p-4">
    <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl p-6 sm:p-8 max-w-md w-full animate-slideUp">
        <div class="text-center mb-8">
            <img src="../img/logo.jpg" alt="With Love For You Logo" class="w-20 h-auto mx-auto mb-4 rounded-lg">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Admin Login</h2>
            <p class="text-gray-600">With Love For You Dashboard</p>
        </div>
        
        <!-- Alert Container -->
        <div id="alert-container" class="mb-6"></div>
        
        <!-- Login Form -->
        <form id="loginForm" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email address</label>
                <input type="email" id="email" name="email" value="admin@wl.org" required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-300"
                    placeholder="name@example.com">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="password" name="password" value="admin123" required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-300"
                    placeholder="Enter your password">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="rememberMe" name="rememberMe" 
                    class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                <label for="rememberMe" class="ml-2 text-sm text-gray-700">Remember me</label>
            </div>
            
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                Sign In
            </button>
        </form>
        
        <div class="text-center mt-6">
            <button type="button" onclick="showForgotPassword()" class="text-primary hover:text-secondary font-medium transition-colors duration-300">
                Forgot your password?
            </button>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
                <div class="flex justify-between items-center mb-6">
                    <h5 class="text-xl font-bold text-gray-800">Reset Password</h5>
                    <button type="button" onclick="closeForgotPassword()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="mb-6">
                    <p class="text-gray-600">Enter your email address and we'll send you a link to reset your password.</p>
                </div>
                <form id="forgotPasswordForm" class="space-y-4">
                    <div>
                        <label for="resetEmail" class="block text-sm font-medium text-gray-700 mb-2">Email address</label>
                        <input type="email" id="resetEmail" name="resetEmail" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-300"
                            placeholder="name@example.com">
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                        Send Reset Link
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            console.log('Fixed login page loaded');
            
            // Login form submission - EXACT COPY FROM WORKING VERSION
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const email = $('#email').val();
                const password = $('#password').val();
                
                console.log('Testing login with:', email, password);
                
                $.ajax({
                    url: 'api/auth.php',
                    type: 'POST',
                    data: {
                        action: 'login',
                        email: email,
                        password: password
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('Sending AJAX request...');
                        $('#alert-container').html('<div class="p-3 bg-blue-100 text-blue-700 rounded">Sending request...</div>');
                    },
                    success: function(response) {
                        console.log('AJAX Success:', response);
                        
                        if (response.success) {
                            $('#alert-container').html('<div class="p-3 bg-green-100 text-green-700 rounded">✅ Login successful!</div>');
                            setTimeout(() => {
                                console.log('Redirecting to dashboard...');
                                window.location.href = 'dashboard.php';
                            }, 1500);
                        } else {
                            $('#alert-container').html('<div class="p-3 bg-red-100 text-red-700 rounded">❌ ' + response.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', error, xhr.responseText);
                        $('#alert-container').html('<div class="p-3 bg-red-100 text-red-700 rounded">❌ Network error!</div>');
                    }
                });
            });
        });

        function showForgotPassword() {
            const modal = document.getElementById('forgotPasswordModal');
            const content = document.getElementById('modalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeForgotPassword() {
            const modal = document.getElementById('forgotPasswordModal');
            const content = document.getElementById('modalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</body>
</html>
