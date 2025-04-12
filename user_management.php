<?php
include 'config.php';

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $username = $_POST['username'];
    
    // Prevent admin deletion
    if ($username === 'admin') {
        $_SESSION['error'] = "Cannot delete admin user!";
        header("Location: user_management.php");
        exit();
    }

    // Delete user and associated feedback
    try {
        $conn->begin_transaction();
        
        // Delete feedbacks first
        $stmt = $conn->prepare("DELETE f FROM feedbacks f JOIN users u ON f.user_id = u.id WHERE u.username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success'] = "User and associated feedback deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
    }
    
    header("Location: user_management.php");
    exit();
}

// Get admin details for sidebar
$admin = $conn->query("SELECT name, email FROM users WHERE id = {$_SESSION['user_id']}")->fetch_assoc();

// Search functionality
$search = '';
$users = [];
$baseQuery = "SELECT username, name, phone, email, address, gender FROM users WHERE role != 'admin'";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("$baseQuery AND (username LIKE ? OR name LIKE ? OR email LIKE ? OR phone LIKE ?)");
    $searchTerm = "%$search%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($baseQuery);
}

$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zephyr Group</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" href="favicon.png"  type="image/png">
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
                        },
                        dark: {
                            800: '#111827',
                            700: '#1f2937',
                            600: '#374151',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        
        .transition-smooth {
            transition: all 0.3s ease-in-out;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .highlight {
            background-color: rgba(14, 165, 233, 0.1);
            animation: highlight 2s;
        }
        
        @keyframes highlight {
            from { background-color: rgba(14, 165, 233, 0.2); }
            to { background-color: rgba(14, 165, 233, 0.1); }
        }
        
        .sidebar-link {
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background-color: rgba(255, 255, 255, 0.1);
            transition: width 0.3s ease;
        }
        
        .sidebar-link:hover::before {
            width: 100%;
        }
        
        .active-sidebar-link {
            position: relative;
        }
        
        .active-sidebar-link::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60%;
            width: 3px;
            background-color: #38bdf8;
            border-radius: 4px 0 0 4px;
        }
        
        .table-row-hover:hover {
            background-color: rgba(14, 165, 233, 0.05);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-dark-800 to-dark-700 border-r border-dark-700/50 flex flex-col transition-smooth z-10 shadow-xl">
            <!-- Logo and name -->
            <div class="mx-auto py-2 border-b border-dark-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center space-x-2">
                        <img src="weblogo.png" alt="" class="max-h-16 max-w-full object-contain">
                    </div>
                </div>
            </div>

            <!-- Navigation links -->
            <nav class="flex-1 px-3 py-6 space-y-1.5">
                <a href="admin_dashboard.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="message-square" class="h-4.5 w-4.5"></i>
                    Feedbacks
                </a>

                <a href="user_management.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-gradient-to-r from-primary-600/20 to-primary-500/10 text-white transition-smooth active-sidebar-link">
                    <i data-lucide="users" class="h-4.5 w-4.5"></i>
                    User Management
                </a>
                
                <button class="settings-btn w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="settings" class="h-4.5 w-4.5"></i>
                    Settings
                </button>
                <button class="analytics-btn w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="bar-chart-2" class="h-4.5 w-4.5"></i>
                    Analytics
                </button>
            </nav>

            <!-- User profile & Logout -->
            <div class="p-4 border-t border-dark-700/50">
                <div class="flex items-center gap-3 mb-4 px-3 py-3 rounded-xl hover:bg-dark-700/50 transition-smooth cursor-pointer">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white shadow-lg">
                        <span class="font-medium"><?= strtoupper(substr($admin['name'], 0, 2)) ?></span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white"><?= $admin['name'] ?></p>
                        <p class="text-xs text-gray-400"><?= $admin['email'] ?></p>
                    </div>
                </div>
                <a href="logout.php" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl text-red-400 hover:bg-red-500/10 transition-smooth">
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 ml-64 p-8 overflow-auto transition-smooth">
            <!-- Status Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-md mb-6 animate-fadeIn" role="alert">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="check-circle" class="h-5 w-5 text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?= $_SESSION['success'] ?></p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-md mb-6 animate-fadeIn" role="alert">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800"><?= $_SESSION['error'] ?></p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Welcome section -->
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">User Management 👥</h1>
                    <p class="text-gray-500 mt-1.5">Manage all registered users in the system</p>
                </div>
                <div class="flex items-center gap-4">
                    <button class="p-2.5 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50 transition-smooth relative">
                        <i data-lucide="bell" class="h-5 w-5 text-gray-600"></i>
                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center text-[10px] text-white font-medium">2</span>
                    </button>
                    <button class="p-2.5 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50 transition-smooth">
                        <i data-lucide="help-circle" class="h-5 w-5 text-gray-600"></i>
                    </button>
                </div>
            </div>

            <!-- Search and Add User -->
            <div class="bg-white rounded-2xl shadow-card border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            <i data-lucide="users" class="h-5 w-5 text-primary-500"></i>
                            User List
                            <span class="ml-2 px-2.5 py-1 bg-primary-50 text-primary-700 text-xs font-medium rounded-full">
                                <?= count($users) ?> users
                            </span>
                        </h2>
                        <button class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-500 text-white text-sm font-medium rounded-xl hover:from-primary-700 hover:to-primary-600 transition-smooth shadow-md">
                            <i data-lucide="user-plus" class="h-4 w-4"></i>
                            Add New User
                        </button>
                    </div>
                </div>
                
                <!-- Search Bar -->
                <div class="p-6 border-b border-gray-100 bg-white">
                    <form id="searchForm" class="flex gap-3">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"></i>
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Search by username, name, email, or phone..." 
                                class="w-full pl-11 pr-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-smooth shadow-sm"
                                value="<?= htmlspecialchars($search) ?>"
                            >
                        </div>
                        <button type="submit" class="bg-gradient-to-r from-primary-600 to-primary-500 text-white px-5 py-2.5 rounded-xl hover:from-primary-700 hover:to-primary-600 transition-smooth flex items-center gap-2 shadow-md">
                            <i data-lucide="search" class="h-4 w-4"></i>
                            Search
                        </button>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTable" class="bg-white divide-y divide-gray-200">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr data-search="<?= strtolower(implode(' ', $user)) ?>" class="table-row-hover transition-smooth">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <div class="flex items-center gap-2">
                                                <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600">
                                                    <span class="font-medium"><?= strtoupper(substr($user['name'], 0, 2)) ?></span>
                                                </div>
                                                <?= htmlspecialchars($user['username']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?= htmlspecialchars($user['name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php if ($user['phone']): ?>
                                                <div class="flex items-center gap-1.5">
                                                    <i data-lucide="phone" class="h-3.5 w-3.5 text-gray-400"></i>
                                                    <?= htmlspecialchars($user['phone']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <div class="flex items-center gap-1.5">
                                                <i data-lucide="mail" class="h-3.5 w-3.5 text-gray-400"></i>
                                                <?= htmlspecialchars($user['email']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php if ($user['address']): ?>
                                                <div class="flex items-center gap-1.5">
                                                    <i data-lucide="map-pin" class="h-3.5 w-3.5 text-gray-400"></i>
                                                    <?= htmlspecialchars($user['address']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php if ($user['gender']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700">
                                                    <?= htmlspecialchars($user['gender']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <div class="flex items-center gap-2">
                                                <button onclick="editingDetails()" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600 transition-smooth">
                                                    <i data-lucide="edit" class="h-4 w-4"></i>
                                                </button>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user and all their feedback?');">
                                                    <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                                    <button type="submit" name="delete_user" class="p-1.5 rounded-lg hover:bg-red-50 text-red-600 transition-smooth">
                                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="p-4 rounded-full bg-gray-100 mb-4">
                                                <i data-lucide="users" class="h-8 w-8 text-gray-400"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">No users found</h3>
                                            <p class="text-gray-500 text-sm">Try adjusting your search criteria</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer with Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?= count($users) ?></span> users
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition-smooth disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            Previous
                        </button>
                        <button class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition-smooth disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Settings and Analytics button handlers
        document.querySelector('.settings-btn').addEventListener('click', function() {
            alert('Settings feature will be available soon!');
        });
        
        document.querySelector('.analytics-btn').addEventListener('click', function() {
            alert('Analytics dashboard is coming in the next update!');
        });

        function editingDetails(){
            alert("Editing featuer will be avail soon!");
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const userTable = document.getElementById('userTable');
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('opacity-0');
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
            
            // Highlight and scroll to matching row
            function highlightMatch() {
                const searchTerm = new URLSearchParams(window.location.search).get('search')?.toLowerCase();
                if (!searchTerm) return;

                const rows = userTable.querySelectorAll('tr');
                let found = false;

                rows.forEach(row => {
                    const rowText = row.getAttribute('data-search');
                    if (rowText && rowText.includes(searchTerm)) {
                        row.classList.add('highlight');
                        if (!found) {
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            found = true;
                        }
                    } else {
                        row.classList.remove('highlight');
                    }
                });
            }

            // Initial highlight on page load
            highlightMatch();

            // Form submission (preserve existing search)
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const searchInput = this.querySelector('input[name="search"]');
                window.location.href = `user_management.php?search=${encodeURIComponent(searchInput.value)}`;
            });
        });
    </script>
</body>
</html>