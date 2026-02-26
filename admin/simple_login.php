<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Simple Login Test</h2>
        
        <!-- PHP Debug Info -->
        <div class="mb-4 p-4 bg-gray-100 rounded text-sm">
            <strong>PHP Debug:</strong><br>
            Session ID: <?php echo session_id(); ?><br>
            Session Data: <?php echo json_encode($_SESSION); ?><br>
            POST Data: <?php echo json_encode($_POST); ?>
        </div>
        
        <!-- JavaScript Debug Info -->
        <div id="jsDebug" class="mb-4 p-4 bg-gray-100 rounded text-sm"></div>
        
        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>
        
        <!-- Login Form -->
        <form id="loginForm">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" id="email" value="admin@wl.org" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" value="admin123" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600">
                Test Login
            </button>
        </form>
        
        <div class="mt-4 space-y-2">
            <a href="login_debug.php" class="block text-blue-500 hover:underline">AJAX Debug Test</a>
            <a href="debug_login.php" class="block text-blue-500 hover:underline">PHP Debug Test</a>
            <a href="login.php" class="block text-blue-500 hover:underline">Main Login</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show JavaScript debug info
            $('#jsDebug').html(`
                <strong>JavaScript Debug:</strong><br>
                jQuery: ${$.fn.jquery}<br>
                Current URL: ${window.location.href}<br>
                Ready: ✅
            `);
            
            // Test form submission
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const email = $('#email').val();
                const password = $('#password').val();
                
                $('#jsDebug').append('<br>Form submitted: ' + email + ' / ' + password);
                
                // Test with console.log first
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
                        $('#alertContainer').html('<div class="p-3 bg-blue-100 text-blue-700 rounded">Sending request...</div>');
                    },
                    success: function(response) {
                        console.log('AJAX Success:', response);
                        $('#jsDebug').append('<br>✅ Response: ' + JSON.stringify(response));
                        
                        if (response.success) {
                            $('#alertContainer').html('<div class="p-3 bg-green-100 text-green-700 rounded">✅ Login successful!</div>');
                            setTimeout(() => {
                                console.log('Redirecting to dashboard...');
                                window.location.href = 'dashboard.php';
                            }, 1500);
                        } else {
                            $('#alertContainer').html('<div class="p-3 bg-red-100 text-red-700 rounded">❌ ' + response.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', error, xhr.responseText);
                        $('#jsDebug').append('<br>❌ Error: ' + status + ' - ' + error);
                        $('#jsDebug').append('<br>Response: ' + xhr.responseText);
                        $('#alertContainer').html('<div class="p-3 bg-red-100 text-red-700 rounded">❌ Network error!</div>');
                    }
                });
            });
        });
    </script>
</body>
</html>
