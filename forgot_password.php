<?php
include 'config.php';

$error = '';
$success = '';
$showSecurityQuestion = false;
$securityQuestion = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['security_answer'])) {
        // Step 1: Verify username and fetch security question
        $username = trim($_POST['username']);

        $stmt = $conn->prepare("SELECT security_question FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $securityQuestion = $user['security_question'];
            $showSecurityQuestion = true;
        } else {
            $error = "Username not found!";
        }
    } else {
        // Step 2: Verify security answer and update password
        $username = trim($_POST['username']);
        $securityAnswer = trim($_POST['security_answer']);
        $newPassword = $_POST['new_password'];

        // Validate password match
        if ($newPassword !== $_POST['confirm_password']) {
            $error = "Passwords don't match!";
            $showSecurityQuestion = true;
            $securityQuestion = $_POST['security_question_hidden'];
        } else {
            $stmt = $conn->prepare("SELECT security_answer FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verify security answer
                if ($securityAnswer === $user['security_answer']) {
                    // Update password
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                    $stmt->bind_param("ss", $newPasswordHash, $username);

                    if ($stmt->execute()) {
                        $success = "Password updated successfully!";
                    } else {
                        $error = "Failed to update password. Please try again.";
                        $showSecurityQuestion = true;
                        $securityQuestion = $_POST['security_question_hidden'];
                    }
                } else {
                    $error = "Incorrect security answer!";
                    $showSecurityQuestion = true;
                    $securityQuestion = $_POST['security_question_hidden'];
                }
            } else {
                $error = "Username not found!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | YourApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="lucide.js"></script>
    <link rel="icon" href="./media/image/favicon.png"  type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .input-focus-effect:focus-within {
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.5);
        }

        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">
    <a href="login.php"
        class="absolute top-4 left-4 text-purple-600 flex items-center">
        <i data-lucide="arrow-left" class="h-5 w-5 mr-1"></i>
        Back to Login
    </a>
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="relative h-3 bg-gradient-to-r from-purple-500 to-indigo-600"></div>

        <div class="p-8 sm:p-10">
            <?php if ($success): ?>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-green-600 text-2xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Password Reset!</h2>
                    <p class="text-gray-600 mb-6">Your password has been successfully updated.</p>
                    <a href="login.php"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 inline-block">
                        Return to Login
                    </a>
                </div>
            <?php else: ?>
                <!-- Step 1: Username Form -->
                <div id="step1" class="<?= $showSecurityQuestion ? 'hidden' : 'block' ?>">
                    <div class="flex justify-center mb-8">
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                            <i class="fas fa-user text-indigo-600 text-2xl"></i>
                        </div>
                    </div>

                    <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Forgot Password?</h1>
                    <p class="text-gray-600 text-center mb-8">Enter your username to verify your identity</p>

                    <form method="POST" class="space-y-6" id="usernameForm">
                        <?php if ($error && !$showSecurityQuestion): ?>
                            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded shake"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="input-focus-effect rounded-lg transition-all duration-200">
                            <div
                                class="flex items-center border border-gray-300 rounded-lg px-4 py-3 focus-within:border-indigo-500">
                                <i class="fas fa-user text-gray-400 mr-3"></i>
                                <input type="text" name="username" placeholder="Username"
                                    value="<?= htmlspecialchars($username) ?>"
                                    class="flex-1 outline-none text-gray-700 placeholder-gray-400" required>
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit" id="continueBtn"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                <span id="continueText">Continue</span>
                                <i id="continueSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                            </button>

                            <a href="login.php"
                                class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                <i class="fas fa-arrow-left mr-2"></i>
                                <span>Login</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Security Question and Password Form -->
                <div id="step2" class="<?= $showSecurityQuestion ? 'block' : 'hidden' ?>">
                    <div class="flex justify-center mb-8">
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center">
                            <i class="fas fa-shield-alt text-indigo-600 text-2xl"></i>
                        </div>
                    </div>

                    <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Verify Your Identity</h1>
                    <p class="text-gray-600 text-center mb-8">Answer your security question to reset password</p>

                    <form method="POST" class="space-y-6" id="resetForm">
                        <?php if ($error && $showSecurityQuestion): ?>
                            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded shake"><?= $error ?></div>
                        <?php endif; ?>

                        <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                        <input type="hidden" name="security_question_hidden"
                            value="<?= htmlspecialchars($securityQuestion) ?>">

                        <div class="input-focus-effect rounded-lg transition-all duration-200 bg-gray-50">
                            <div class="flex items-center rounded-lg px-4 py-3">
                                <i class="fas fa-user text-gray-400 mr-3"></i>
                                <input type="text" value="<?= htmlspecialchars($username) ?>"
                                    class="flex-1 outline-none text-gray-700 bg-transparent" readonly>
                            </div>
                        </div>

                        <div class="input-focus-effect rounded-lg transition-all duration-200 bg-gray-50">
                            <div class="flex items-center rounded-lg px-4 py-3">
                                <i class="fas fa-question-circle text-gray-400 mr-3"></i>
                                <input type="text" value="<?= htmlspecialchars($securityQuestion) ?>"
                                    class="flex-1 outline-none text-gray-700 bg-transparent" readonly>
                            </div>
                        </div>

                        <div class="input-focus-effect rounded-lg transition-all duration-200">
                            <div
                                class="flex items-center border border-gray-300 rounded-lg px-4 py-3 focus-within:border-indigo-500">
                                <i class="fas fa-key text-gray-400 mr-3"></i>
                                <input type="text" name="security_answer" placeholder="Your answer"
                                    class="flex-1 outline-none text-gray-700 placeholder-gray-400" required>
                            </div>
                        </div>

                        <div class="input-focus-effect rounded-lg transition-all duration-200">
                            <div
                                class="flex items-center border border-gray-300 rounded-lg px-4 py-3 focus-within:border-indigo-500">
                                <i class="fas fa-lock text-gray-400 mr-3"></i>
                                <input type="password" name="new_password" placeholder="New password"
                                    class="flex-1 outline-none text-gray-700 placeholder-gray-400" required minlength="8"
                                    id="newPassword">
                            </div>
                        </div>

                        <div class="input-focus-effect rounded-lg transition-all duration-200">
                            <div
                                class="flex items-center border border-gray-300 rounded-lg px-4 py-3 focus-within:border-indigo-500">
                                <i class="fas fa-lock text-gray-400 mr-3"></i>
                                <input type="password" name="confirm_password" placeholder="Confirm new password"
                                    class="flex-1 outline-none text-gray-700 placeholder-gray-400" required minlength="8"
                                    id="confirmPassword">
                            </div>
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit" id="resetBtn"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                <span id="resetText">Reset Password</span>
                                <i id="resetSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                            </button>

                            <button type="button" onclick="window.location.href='forgot_password.php'"
                                class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                <i class="fas fa-arrow-left mr-2"></i>
                                <span>Back</span>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>

        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', () => {
            // Continue button loading state with 1-second delay
            const usernameForm = document.getElementById('usernameForm');
            const continueBtn = document.getElementById('continueBtn');
            const continueText = document.getElementById('continueText');
            const continueSpinner = document.getElementById('continueSpinner');

            if (usernameForm) {
                usernameForm.addEventListener('submit', function (e) {
                    // Prevent immediate form submission
                    e.preventDefault();

                    // Show loading state
                    continueBtn.disabled = true;
                    continueText.textContent = 'Verifying...';
                    continueSpinner.classList.remove('hidden');

                    // Submit form after 1 second
                    setTimeout(() => {
                        this.submit();
                    }, 1000);
                });
            }

            // Reset button loading state with 1-second delay
            const resetForm = document.getElementById('resetForm');
            const resetBtn = document.getElementById('resetBtn');
            const resetText = document.getElementById('resetText');
            const resetSpinner = document.getElementById('resetSpinner');

            if (resetForm) {
                resetForm.addEventListener('submit', function (e) {
                    // Prevent immediate form submission
                    e.preventDefault();

                    // Validate password match first
                    const newPassword = document.getElementById('newPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;

                    if (newPassword !== confirmPassword) {
                        alert('Passwords do not match!');
                        return;
                    }

                    // Show loading state
                    resetBtn.disabled = true;
                    resetText.textContent = 'Resetting...';
                    resetSpinner.classList.remove('hidden');

                    // Submit form after 1 second
                    setTimeout(() => {
                        this.submit();
                    }, 1000);
                });
            }

            // Remove shake animation after it plays
            document.querySelectorAll('.shake').forEach(el => {
                el.addEventListener('animationend', () => {
                    el.classList.remove('shake');
                });
            });
        });
    </script>
</body>

</html>