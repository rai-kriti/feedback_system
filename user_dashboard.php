<?php
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Get user details
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Submit feedback
$showAlert = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $issue = $_POST['issue'];
    $description = $_POST['description'];
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? null;
    
    if ($phone && !preg_match('/^(\+91|0)?[ -]?[6-9]\d{9}$/', $phone)) {
        $error = "Invalid phone number! Please use a valid 10-digit number starting with 6, 7, 8, or 9.";
    } else {
        $stmt = $conn->prepare("INSERT INTO feedbacks (user_id, issue, description, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $_SESSION['user_id'], $issue, $description, $phone, $address);
        if ($stmt->execute()) {
            $showAlert = true;
        } else {
            $error = "Failed to submit feedback. Please try again.";
        }
    }
}

// Get feedback stats
$total_stmt = $conn->prepare("SELECT COUNT(*) FROM feedbacks WHERE user_id = ?");
$total_stmt->bind_param("i", $_SESSION['user_id']);
$total_stmt->execute();
$total_feedbacks = $total_stmt->get_result()->fetch_row()[0];

$resolved_stmt = $conn->prepare("SELECT COUNT(*) FROM feedbacks WHERE user_id = ? AND status = 'resolved'");
$resolved_stmt->bind_param("i", $_SESSION['user_id']);
$resolved_stmt->execute();
$resolved_feedbacks = $resolved_stmt->get_result()->fetch_row()[0];

$pending_feedbacks = $total_feedbacks - $resolved_feedbacks;

// Get user feedbacks
$feedback_stmt = $conn->prepare("SELECT * FROM feedbacks WHERE user_id = ? ORDER BY created_at DESC");
$feedback_stmt->bind_param("i", $_SESSION['user_id']);
$feedback_stmt->execute();
$feedbacks = $feedback_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Zephyr Group</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="lucide.js"></script>
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
        
        .bg-sidebar { background-color: #111827; }
        .border-sidebar-border { border-color: #1f2937; }
        .text-destructive { color: #ef4444; }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Success Alert -->
    <?php if ($showAlert): ?>
    <div class="fixed top-4 right-4 z-50 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-lg transition-smooth" id="successAlert">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i data-lucide="check-circle" class="h-5 w-5 text-green-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">Feedback submitted successfully!</p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="document.getElementById('successAlert').remove()" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-dark-800 to-dark-700 border-r border-sidebar-border flex flex-col shadow-xl z-10">
            <!-- Website Logo Section -->
            <div class="mx-auto py-2 border-b border-dark-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center space-x-2">
                        <img src="weblogo.png" alt="" class="max-h-16 max-w-full object-contain">
                    </div>
                </div>
            </div>

            <!-- Navigation links -->
            <nav class="flex-1 p-3 space-y-1.5">
                <a href="profile.php" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="user" class="h-4.5 w-4.5"></i>
                    Profile
                </a>
                <button onclick="document.getElementById('previousFeedbackSection').scrollIntoView({behavior: 'smooth'})" 
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="message-square" class="h-4.5 w-4.5"></i>
                    Previous Feedback
                </button>

                <!-- Added back FAQs and Terms -->
                <button class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="help-circle" class="h-4.5 w-4.5"></i>
                    FAQs
                </button>
                <button class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl text-gray-300 hover:bg-dark-700/50 hover:text-white transition-smooth sidebar-link">
                    <i data-lucide="file-text" class="h-4.5 w-4.5"></i>
                    Terms & Conditions
                </button>
            </nav>

            <!-- Profile Section Above Logout -->
            <div class="p-3 border-t border-sidebar-border">
                <div class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-dark-700/50 transition-smooth cursor-pointer">
                <?php if ($user['profile_picture']): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" 
                        class="h-10 w-10 rounded-full object-cover cursor-pointer shadow-md border-2 border-primary-500/30" 
                        alt="Profile"
                        onclick="window.open('<?= htmlspecialchars($user['profile_picture']) ?>', '_blank')">
                <?php else: ?>
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white shadow-lg">
                        <span class="font-medium"><?= strtoupper(substr($user['name'], 0, 2)) ?></span>
                    </div>
                <?php endif; ?>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white"><?= htmlspecialchars($user['name']) ?></p>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Logout button -->
            <div class="p-3 border-t border-sidebar-border">
                <a href="logout.php" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl text-red-400 hover:bg-red-500/10 transition-smooth">
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 ml-64 p-8 overflow-auto">
            <!-- Welcome Section -->
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Welcome, <?= htmlspecialchars($user['name']) ?> 👋</h1>
                    <p class="text-gray-500 mt-1.5">Manage your power supply feedback and track previous submissions</p>
                </div>
                <?php if ($user['profile_picture']): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" 
                        class="h-14 w-14 rounded-full object-cover shadow-md border-2 border-primary-100" 
                        alt="Profile">
                <?php else: ?>
                    <div class="h-14 w-14 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center text-primary-600 shadow-md">
                        <span class="text-lg font-semibold"><?= strtoupper(substr($user['name'], 0, 2)) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Dashboard content -->
            <div class="grid gap-6 md:grid-cols-1 lg:grid-cols-3">
                <!-- Feedback submission section -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-card overflow-hidden">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            <i data-lucide="message-square" class="h-5 w-5 text-primary-500"></i>
                            Submit Feedback
                        </h2>
                    </div>
                    <form method="POST" class="p-6 space-y-5">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                <i data-lucide="alert-circle" class="h-4 w-4 text-primary-500"></i>
                                Issue Title
                            </label>
                            <input name="issue" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-smooth" placeholder="Enter issue title">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                <i data-lucide="file-text" class="h-4 w-4 text-primary-500"></i>
                                Description
                            </label>
                            <textarea name="description" required class="min-h-[140px] w-full px-4 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-smooth" placeholder="Describe your issue in detail..."></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                <i data-lucide="phone" class="h-4 w-4 text-primary-500"></i>
                                Phone Number
                            </label>
                            <input name="phone" type="text" placeholder="Optional" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-smooth">
                            <p class="text-sm text-red-500 mt-1 hidden" id="phone-error"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="h-4 w-4 text-primary-500"></i>
                                Address
                            </label>
                            <input name="address" type="text" placeholder="Optional" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-smooth">
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-500 text-white text-sm font-medium rounded-xl hover:from-primary-700 hover:to-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 shadow-md transition-smooth">
                            <i data-lucide="send" class="h-4 w-4"></i>
                            Submit Feedback
                        </button>
                    </form>
                </div>

                <!-- Stats card -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-card overflow-hidden">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            <i data-lucide="bar-chart-2" class="h-5 w-5 text-primary-500"></i>
                            Feedback Overview
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-gray-50 to-white border border-gray-100 shadow-sm card-hover">
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-700">Total Submitted</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $total_feedbacks ?></p>
                            </div>
                            <div class="p-3 rounded-xl bg-primary-50 text-primary-500">
                                <i data-lucide="inbox" class="h-8 w-8"></i>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-gray-50 to-white border border-gray-100 shadow-sm card-hover">
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-700">Resolved</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $resolved_feedbacks ?></p>
                            </div>
                            <div class="p-3 rounded-xl bg-green-50 text-green-500">
                                <i data-lucide="check-circle" class="h-8 w-8"></i>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-gray-50 to-white border border-gray-100 shadow-sm card-hover">
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-700">Pending</p>
                                <p class="text-2xl font-bold text-gray-900"><?= $pending_feedbacks ?></p>
                            </div>
                            <div class="p-3 rounded-xl bg-yellow-50 text-yellow-500">
                                <i data-lucide="alert-circle" class="h-8 w-8"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Previous feedbacks section -->
            <div class="mt-10" id="previousFeedbackSection">
                <h2 class="text-2xl font-bold tracking-tight mb-6 flex items-center gap-2">
                    <i data-lucide="history" class="h-6 w-6 text-primary-500"></i>
                    Previous Feedbacks
                </h2>
                <div class="space-y-5">
                    <?php if (count($feedbacks) > 0): ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-card overflow-hidden card-hover">
                            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2.5">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium <?= $feedback['status'] === 'resolved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <i data-lucide="<?= $feedback['status'] === 'resolved' ? 'check-circle' : 'clock' ?>" class="h-3 w-3"></i>
                                            <?= ucfirst($feedback['status']) ?>
                                        </span>
                                        <span class="text-xs font-medium text-primary-600 bg-primary-50 px-3 py-1.5 rounded-full">
                                            #FB-<?= date('Y', strtotime($feedback['created_at'])) ?>-<?= str_pad($feedback['id'], 3, '0', STR_PAD_LEFT) ?>
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full">
                                        <?= date('F j, Y \a\t g:i A', strtotime($feedback['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3"><?= htmlspecialchars($feedback['issue']) ?></h3>
                                <p class="text-sm text-gray-700 leading-relaxed mb-4"><?= htmlspecialchars($feedback['description']) ?></p>
                                <?php if ($feedback['phone'] || $feedback['address']): ?>
                                <div class="mt-4 p-3 bg-gray-50 rounded-xl text-xs text-gray-600">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <?php if ($feedback['phone']): ?>
                                        <div class="flex items-center gap-1.5">
                                            <i data-lucide="phone" class="h-3.5 w-3.5 text-gray-500"></i>
                                            <span><?= htmlspecialchars($feedback['phone']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($feedback['address']): ?>
                                        <div class="flex items-center gap-1.5">
                                            <i data-lucide="map-pin" class="h-3.5 w-3.5 text-gray-500"></i>
                                            <span><?= htmlspecialchars($feedback['address']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center shadow-card">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 rounded-full bg-gray-100 mb-4">
                                    <i data-lucide="inbox" class="h-8 w-8 text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No feedbacks yet</h3>
                                <p class="text-gray-500 text-sm">Submit your first feedback using the form above</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
            
            // Auto-hide success alert after 5 seconds
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.add('opacity-0');
                    setTimeout(() => {
                        successAlert.remove();
                    }, 300);
                }, 5000);
            }
        });

        // Enhanced Scroll Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll to feedback section
            document.querySelector('[onclick*="previousFeedbackSection"]').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('previousFeedbackSection').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const phoneInput = document.querySelector('input[name="phone"]');
            const phoneError = document.getElementById('phone-error');

            phoneInput.addEventListener('input', () => {
                const phoneNumber = phoneInput.value.trim();
                const isValid = /^(\+91|0)?[ -]?[6-9]\d{9}$/.test(phoneNumber);

                if (phoneNumber === '') {
                    phoneError.classList.add('hidden');
                    phoneInput.classList.remove('border-red-500');
                } else if (!isValid) {
                    phoneError.textContent = 'Invalid phone number format!';
                    phoneError.classList.remove('hidden');
                    phoneInput.classList.add('border-red-500');
                } else {
                    phoneError.classList.add('hidden');
                    phoneInput.classList.remove('border-red-500');
                }
            });
        });
    </script>
</body>
</html>