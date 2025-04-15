<?php
include 'config.php';

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get admin details for sidebar
$admin = $conn->query("SELECT name, email FROM users WHERE id = {$_SESSION['user_id']}")->fetch_assoc();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $feedbackId = $_POST['id'];
    $newStatus = $_POST['status'];

    $stmt = $conn->prepare("UPDATE feedbacks SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $feedbackId);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?filter=" . ($_GET['filter'] ?? 'all'));
        exit();
    }
}

// Get feedback counts
$totalFeedback = $conn->query("SELECT COUNT(*) as count FROM feedbacks")->fetch_assoc()['count'];
$resolvedFeedback = $conn->query("SELECT COUNT(*) as count FROM feedbacks WHERE status = 'resolved'")->fetch_assoc()['count'];
$pendingFeedback = $conn->query("SELECT COUNT(*) as count FROM feedbacks WHERE status = 'pending'")->fetch_assoc()['count'];

// Determine active filter
$filter = $_GET['filter'] ?? 'all';
$query = "SELECT f.*, u.name, u.email FROM feedbacks f JOIN users u ON f.user_id = u.id";

if ($filter === 'resolved') {
    $query .= " WHERE f.status = 'resolved'";
} elseif ($filter === 'pending') {
    $query .= " WHERE f.status = 'pending'";
}

$query .= " ORDER BY f.created_at DESC";
$feedbacks = $conn->query($query);
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

        .glass-effect {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.8);
        }

        .card-hover {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card-hover:hover {
            transform: translateY(-3px);
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
    </style>
</head>

<body class="min-h-screen bg-gray-50 text-gray-800">
    <div class="flex">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-dark-800 to-dark-700 border-r border-dark-700/50 flex flex-col transition-smooth z-10 shadow-xl">
            <!-- Logo and name -->
            <div class="mx-auto py-2 border-b border-dark-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center space-x-2">
                        <img src="weblogo.png" alt="" class="max-h-14 max-w-full object-contain">
                    </div>
                </div>
            </div>

            <!-- Navigation links -->
            <nav class="flex-1 px-3 py-6 space-y-1.5">
                <a href="admin_dashboard.php"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-gradient-to-r from-primary-600/20 to-primary-500/10 text-white transition-smooth active-sidebar-link">
                    <i data-lucide="message-square" class="h-4.5 w-4.5"></i>
                    Feedbacks
                </a>

                <a href="user_management.php"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="users" class="h-4.5 w-4.5"></i>
                    User Management
                </a>

                <button
                    class="settings-btn w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="settings" class="h-4.5 w-4.5"></i>
                    Settings
                </button>
                <button
                    class="analytics-btn w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="bar-chart-2" class="h-4.5 w-4.5"></i>
                    Analytics
                </button>
            </nav>

            <!-- User profile & Logout -->
            <div class="p-4 border-t border-dark-700/50">
                <div
                    class="flex items-center gap-3 mb-4 px-3 py-3 rounded-xl hover:bg-dark-700/50 transition-smooth cursor-pointer">
                    <div
                        class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white shadow-lg">
                        <span class="font-medium"><?= strtoupper(substr($admin['name'], 0, 2)) ?></span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white"><?= $admin['name'] ?></p>
                        <p class="text-xs text-gray-400"><?= $admin['email'] ?></p>
                    </div>
                </div>
                <a href="logout.php"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl text-red-400 hover:bg-red-500/10 transition-smooth">
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 ml-64 p-8 overflow-auto transition-smooth">
            <!-- Welcome section with notification icons -->
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Welcome back, <?= $admin['name'] ?> 👋
                    </h1>
                    <p class="text-gray-500 mt-1.5">Here's what's happening with your feedback system today</p>
                </div>
                <div class="flex items-center gap-4">
                    <button
                        class="p-2.5 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50 transition-smooth relative">
                        <i data-lucide="bell" class="h-5 w-5 text-gray-600"></i>
                        <span
                            class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center text-[10px] text-white font-medium">3</span>
                    </button>
                    <button
                        class="p-2.5 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50 transition-smooth">
                        <i data-lucide="help-circle" class="h-5 w-5 text-gray-600"></i>
                    </button>
                </div>
            </div>

            <!-- Stats cards with trends -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <!-- Total Submitted -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-card border border-gray-100 hover:shadow-lg transition-smooth card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Submitted</p>
                            <h3 class="text-3xl font-bold mt-1 text-gray-900"><?= $totalFeedback ?></h3>
                        </div>
                        <div
                            class="p-3.5 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 text-primary-600 shadow-sm">
                            <i data-lucide="inbox" class="h-6 w-6"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-green-600 font-medium">
                        <i data-lucide="trending-up" class="h-4 w-4 mr-1.5"></i>
                        <span>12% from last month</span>
                    </div>
                </div>

                <!-- Resolved -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-card border border-gray-100 hover:shadow-lg transition-smooth card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Resolved</p>
                            <h3 class="text-3xl font-bold mt-1 text-gray-900"><?= $resolvedFeedback ?></h3>
                        </div>
                        <div
                            class="p-3.5 rounded-xl bg-gradient-to-br from-green-50 to-green-100 text-green-600 shadow-sm">
                            <i data-lucide="check-circle" class="h-6 w-6"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-green-600 font-medium">
                        <i data-lucide="trending-up" class="h-4 w-4 mr-1.5"></i>
                        <span>8% from last month</span>
                    </div>
                </div>

                <!-- Pending -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-card border border-gray-100 hover:shadow-lg transition-smooth card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <h3 class="text-3xl font-bold mt-1 text-gray-900"><?= $pendingFeedback ?></h3>
                        </div>
                        <div
                            class="p-3.5 rounded-xl bg-gradient-to-br from-yellow-50 to-yellow-100 text-yellow-600 shadow-sm">
                            <i data-lucide="clock" class="h-6 w-6"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm text-red-600 font-medium">
                        <i data-lucide="trending-down" class="h-4 w-4 mr-1.5"></i>
                        <span>4% from last month</span>
                    </div>
                </div>
            </div>

            <!-- Feedback section -->
            <div class="mt-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Feedback Management</h2>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i data-lucide="search"
                                class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"></i>
                            <input type="text" placeholder="Search feedback..."
                                class="pl-11 pr-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-smooth shadow-sm w-64">
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mb-8">
                    <div class="inline-flex rounded-xl shadow-sm bg-gray-100/80 p-1.5" role="tablist">
                        <a href="?filter=all"
                            class="px-6 py-2.5 text-sm font-medium rounded-lg <?= $filter === 'all' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' ?> transition-smooth">
                            All Feedbacks
                        </a>
                        <a href="?filter=resolved"
                            class="px-6 py-2.5 text-sm font-medium rounded-lg <?= $filter === 'resolved' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' ?> transition-smooth">
                            Resolved
                        </a>
                        <a href="?filter=pending"
                            class="px-6 py-2.5 text-sm font-medium rounded-lg <?= $filter === 'pending' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' ?> transition-smooth">
                            Pending
                        </a>
                    </div>
                </div>

                <!-- Feedback content -->
                <div class="space-y-5">
                    <?php if ($feedbacks->num_rows > 0): ?>
                        <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                            <div
                                class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-smooth overflow-hidden card-hover">
                                <div class="p-6 border-b border-gray-100">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2.5 mb-2.5">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium <?= $feedback['status'] === 'resolved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                    <i data-lucide="<?= $feedback['status'] === 'resolved' ? 'check-circle' : 'clock' ?>"
                                                        class="h-3 w-3"></i>
                                                    <?= ucfirst($feedback['status']) ?>
                                                </span>
                                                <span
                                                    class="text-xs font-medium text-primary-600 bg-primary-50 px-3 py-1.5 rounded-full">#FB-<?= date('Y', strtotime($feedback['created_at'])) ?>-<?= str_pad($feedback['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                <?= htmlspecialchars($feedback['issue']) ?></h3>
                                        </div>
                                        <span
                                            class="text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full"><?= date('M j, Y • g:i A', strtotime($feedback['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <p class="text-sm text-gray-700 mb-5 leading-relaxed">
                                        <?= htmlspecialchars($feedback['description']) ?></p>
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs bg-gray-50 px-4 py-2 rounded-lg">
                                            <p class="mb-0.5"><span
                                                    class="font-medium text-gray-900"><?= htmlspecialchars($feedback['name']) ?></span>
                                                • <?= htmlspecialchars($feedback['phone'] ?? 'N/A') ?></p>
                                            <p class="text-gray-500"><?= htmlspecialchars($feedback['address'] ?? 'N/A') ?></p>
                                        </div>
                                        <form method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="id" value="<?= $feedback['id'] ?>">
                                            <input type="hidden" name="update" value="1">
                                            <select name="status" onchange="this.form.submit()"
                                                class="text-sm border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 outline-none transition-smooth shadow-sm bg-white">
                                                <option value="pending" <?= $feedback['status'] === 'pending' ? 'selected' : '' ?>>
                                                    Pending</option>
                                                <option value="resolved" <?= $feedback['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 rounded-full bg-gray-100 mb-4">
                                    <i data-lucide="inbox" class="h-8 w-8 text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No feedbacks found</h3>
                                <p class="text-gray-500 text-sm">There are no feedbacks matching your current filter</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons(); // Now guaranteed to run after DOM is ready
        });

        // Settings and Analytics button handlers
        document.querySelector('.settings-btn').addEventListener('click', function () {
            alert('Settings feature will be available soon!');
        });

        document.querySelector('.analytics-btn').addEventListener('click', function () {
            alert('Analytics dashboard is coming in the next update!');
        });

        // Notification and Help button handlers
        document.querySelectorAll('button').forEach(button => {
            if (button.querySelector('[data-lucide="bell"]')) {
                button.addEventListener('click', function () {
                    alert('No new notifications');
                });
            }
            if (button.querySelector('[data-lucide="help-circle"]')) {
                button.addEventListener('click', function () {
                    alert('Help documentation is available in the knowledge base');
                });
            }
        });
    </script>
</body>

</html>