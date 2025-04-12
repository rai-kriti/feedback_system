<?php include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: ".($user['role'] == 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
            exit();
        }
    }
    $error = "Invalid credentials!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerFeedback - Login</title>
    <link rel="icon" href="favicon.png"  type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                            950: '#082f49',
                        },
                        secondary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        .video-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 1rem;
        }
        
        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="max-w-4xl w-full">
        <a href="index.html" class="absolute top-4 left-4 text-primary-600 hover:text-primary-700 transition-colors duration-200 flex items-center">
            <i data-lucide="arrow-left" class="h-5 w-5 mr-1"></i>
            Back to Main
        </a>
        
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100">
            <div class="md:flex">
                <!-- Login Form -->
                <div class="md:w-1/2 p-8 md:p-10">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800">Welcome Back</h2>
                        <p class="text-gray-600 mt-2">Sign in to your PowerFeedback account</p>
                    </div>
                    
                    <?php if(isset($error)): ?>
                        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded-lg flex items-center">
                            <i data-lucide="alert-circle" class="h-5 w-5 mr-2"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-5">
                        <div class="space-y-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="login" placeholder="Username or Email" required
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200">
                            </div>
                            
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                                </div>
                                <input type="password" name="password" placeholder="Password" required
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200">
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" id="remember-me" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="remember-me" class="ml-2 text-sm text-gray-600">Remember me</label>
                            </div>
                            <a href="forgot_password.php" class="text-sm text-primary-600 hover:text-primary-700 transition-colors duration-200">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="w-full py-3 px-4 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition duration-200 flex items-center justify-center">
                            <i data-lucide="log-in" class="h-5 w-5 mr-2"></i>
                            Login
                        </button>
                    </form>
                    
                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-gray-500">Or Login with</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-center">
                            <a href="google-login.php" class="w-full max-w-xs flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                <img src="google.png" alt="Google" class="h-5 w-5 mr-2">
                                Google
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center space-y-2">
                        <p class="text-gray-600">
                            Don't have an account? 
                            <a href="signup.php" class="text-primary-600 font-medium hover:text-primary-700 transition-colors duration-200">Sign up</a>
                        </p>
                        <p class="text-sm text-gray-600">
                            Are you an admin? 
                            <a href="admin_login.php" class="text-primary-600 hover:text-primary-700 transition-colors duration-200">Click here</a>
                        </p>
                    </div>
                </div>
                
                <!-- Image/Info Section -->
                <div class="hidden md:block md:w-1/2 bg-gradient-to-r from-secondary-500 to-primary-500 p-10 flex flex-col items-center justify-center">
                    <div class="text-white text-center">
                        <h3 class="text-2xl font-bold mb-4">Campus Energy Management</h3>
                        <p class="mb-6">Your feedback helps us identify and resolve power issues more efficiently across campus facilities.</p>
                        <div class="video-container">
                            <video id="energy-video" preload="auto" muted>
                                <source src="monkey.mp4" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.querySelector('input[name="password"]');
            const video = document.getElementById('energy-video');
            let videoPlayed = false;
            
            document.addEventListener('click', function(e) {
                if (e.target !== passwordField && !passwordField.contains(e.target)) {
                    video.pause();
                    video.currentTime = 0;
                    videoPlayed = false;
                }
            });
            
            passwordField.addEventListener('click', function() {
                if (!videoPlayed) {
                    video.play();
                    videoPlayed = true;
                }
            });
        });
    </script>
</body>
</html>