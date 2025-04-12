<?php include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php");
            exit();
        }
    }
    $error = "Invalid admin credentials!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerFeedback - Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="favicon.png"  type="image/png">
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
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <a href="login.php" class="absolute top-4 left-4 text-primary-600 hover:text-primary-700 transition-colors duration-200 flex items-center">
            <i data-lucide="arrow-left" class="h-5 w-5 mr-1"></i>
            Back to Login
        </a>
        
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 p-8">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <div class="h-16 w-16 bg-primary-100 rounded-full flex items-center justify-center">
                        <i data-lucide="shield" class="h-8 w-8 text-primary-600"></i>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-gray-800">Admin Login</h2>
                <p class="text-gray-600 mt-2">Access the admin dashboard</p>
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
                        <input type="text" name="username" placeholder="Username" required
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
                
                <button type="submit" class="w-full py-3 px-4 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition duration-200 flex items-center justify-center">
                    <i data-lucide="log-in" class="h-5 w-5 mr-2"></i>
                    Login
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    <i data-lucide="info" class="h-4 w-4 inline-block mr-1"></i>
                    Note: Admin credentials are provided separately
                </p>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>