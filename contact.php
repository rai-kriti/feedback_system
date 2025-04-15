<?php
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');

        // Validate and sanitize input
        function sanitizeInput($data)
        {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $errors   = [];
        $response = ['success' => false, 'message' => ''];

        // Get form data
        $name    = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
        $email   = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
        $subject = isset($_POST['subject']) ? sanitizeInput($_POST['subject']) : '';
        $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';

        // Validate inputs
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        if (empty($subject)) {
            $errors[] = 'Subject is required';
        }
        if (empty($message)) {
            $errors[] = 'Message is required';
        }

        if (count($errors) === 0) {
                                                           // Email configuration
            $to            = 'chaturvediinitin@gmail.com'; // Change to your email
            $email_subject = "New Contact Form Submission: " . $subject;

            $email_body = "You have received a new message from your website contact form.\n\n";
            $email_body .= "Name: $name\n";
            $email_body .= "Email: $email\n";
            $email_body .= "Subject: $subject\n\n";
            $email_body .= "Message:\n$message\n";

            $headers = "From: $email\n";
            $headers .= "Reply-To: $email\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // Send email
            if (mail($to, $email_subject, $email_body, $headers)) {
                $response['success'] = true;
                $response['message'] = 'Thank you! Your message has been sent.';
            } else {
                $response['message'] = 'Sorry, there was an error sending your message. Please try again later.';
            }
        } else {
            $response['message'] = implode('<br>', $errors);
        }

        echo json_encode($response);
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zephyr Group</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="./media/image/favicon.png"  type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            padding-top: 80px;
        }
    </style>
</head>

<body class="min-h-screen bg-white">
    <header
        class="bg-white/80 backdrop-blur-sm fixed top-5 z-50 mx-auto left-0 right-0 w-[90%] max-w-6xl rounded-full shadow-lg border border-gray-100">
        <nav class="flex items-center justify-between px-6 py-3">
            <div class="flex items-center space-x-2">
                <img src="./media/image/weblogo.png" alt="" class="max-h-10 max-w-full object-contain">
            </div>

            <div class="hidden md:flex space-x-6">
                <a href="index.html" class="text-gray-600 hover:text-indigo-600 transition">Home</a>
                <a href="aboutus.html" class="text-gray-600 hover:text-indigo-600 transition">About</a>
                <a href="contact.php" class="text-gray-600 hover:text-indigo-600 transition">Contact</a>
                <a href="faq.html" class="text-gray-600 hover:text-indigo-600 transition">FAQs</a>
                <a href="tnc.html" class="text-gray-600 hover:text-indigo-600 transition">Terms & Conditions</a>
            </div>

            <div class="flex items-center space-x-4">
                <div class="relative group">
                    <button id="loginBtn"
                        class="px-4 py-2 text-gray-600 hover:text-indigo-600 font-medium transition flex items-center">
                        Login
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden z-50 border border-gray-100">
                        <a href="login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">User Login</a>
                        <a href="admin_login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50">Admin Login</a>
                    </div>
                </div>
                <button id="signupBtn"
                    onclick="window.location.href='signup.php'"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition font-medium">Sign
                    Up</button>
            </div>
        </nav>
    </header>

    <main class="relative">
        <div class="relative bg-blue-50 overflow-hidden pt-24 -mt-20">
            <div class="container mx-auto px-4 relative z-10 py-20">
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-4 max-w-2xl">Let's Connect and Power Your
                    Future</h1>
                <p class="text-lg md:text-xl text-gray-600 max-w-2xl mb-8">
                    We're here to answer your questions about our power supply position feedback systems and help you
                    find the
                    perfect solution.
                </p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
        </div>

        <!-- Contact form section -->
        <div class="container mx-auto px-4 relative z-20 -mt-10">
            <div class="border border-gray-200 shadow-xl rounded-lg overflow-hidden">
                <div class="grid md:grid-cols-5 min-h-[300px]">
                    <div
                        class="md:col-span-2 bg-blue-600 p-8 text-white flex flex-col justify-center relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2">
                        </div>
                        <div
                            class="absolute bottom-0 left-0 w-40 h-40 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2">
                        </div>

                        <div class="relative z-10">
                            <h2 class="text-3xl font-bold mb-4">Get in Touch</h2>
                            <p class="mb-6 text-blue-100">
                                Fill out the form and our team will get back to you within 24 hours.
                            </p>

                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="mail" class="h-5 w-5 text-blue-200"></i>
                                    <span>info@zephyrgroup.com</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i data-lucide="phone" class="h-5 w-5 text-blue-200"></i>
                                    <span>+91 1234567890</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i data-lucide="map-pin" class="h-5 w-5 text-blue-200"></i>
                                    <span>144111 LPU Phagwara, Punjab</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-3 p-8">
                        <div id="form-success"
                            class="hidden flex-col items-center justify-center h-full text-center p-8">
                            <div class="rounded-full bg-green-100 p-4 mb-4">
                                <i data-lucide="check-circle" class="h-10 w-10 text-green-600"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-2">Message Sent!</h3>
                            <p class="text-gray-600 max-w-md">
                                Thank you for contacting us. We'll respond to your inquiry shortly.
                            </p>
                        </div>

                        <form id="contact-form" class="space-y-6" method="POST">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="name" class="text-sm font-medium">Your Name</label>
                                    <input id="name" name="name" placeholder="Enter Your Full Name Here" required
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                </div>

                                <div class="space-y-2">
                                    <label for="email" class="text-sm font-medium">Email Address</label>
                                    <input id="email" name="email" type="email" placeholder="Enter your email here"
                                        required
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="subject" class="text-sm font-medium">Subject</label>
                                <div class="relative">
                                    <button type="button" id="select-trigger"
                                        class="flex h-10 w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <span id="select-value" class="text-gray-400">Select a subject</span>
                                        <i data-lucide="chevron-down" class="h-4 w-4 text-gray-400"></i>
                                    </button>
                                    <input type="hidden" id="subject" name="subject" value="">
                                    <div id="select-content"
                                        class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                        <div class="py-1">
                                            <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer" data-value="general">
                                                General Inquiry</div>
                                            <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer" data-value="support">
                                                Technical Support</div>
                                            <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer" data-value="sales">
                                                Sales Information</div>
                                            <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer"
                                                data-value="feedback">Product Feedback</div>
                                            <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer" data-value="other">
                                                Other</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="message" class="text-sm font-medium">Message</label>
                                <textarea id="message" name="message" placeholder="How can we help you?"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm min-h-[120px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required></textarea>
                            </div>

                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-blue-600 text-white hover:bg-blue-700 h-10 px-8 gap-2">
                                Send Message
                                <i data-lucide="arrow-right" class="h-4 w-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact information cards -->
        <div class="max-w-7xl mx-auto px-4 mt-24 mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold">How to Reach Us</h2>
                <p class="text-gray-600 mt-2 max-w-2xl mx-auto">
                    Multiple ways to connect with our team for support, sales, and information
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    class="w-[95%] mx-auto overflow-hidden transition-all hover:-translate-y-2 hover:shadow-lg rounded-lg border border-gray-200">
                    <div class="h-3 bg-blue-600"></div>
                    <div class="pt-6">
                        <div class="flex flex-col items-center text-center p-4">
                            <div class="bg-blue-100 p-4 rounded-full mb-4">
                                <i data-lucide="map-pin" class="h-8 w-8 text-blue-600"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Visit Our Office</h3>
                            <p class="text-gray-600 mb-4">
                                144111 Lovely Professional University
                                <br>
                                Phagwara, Punjab
                                <br>
                                BHARAT
                            </p>
                            <button
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-gray-300 bg-white hover:bg-gray-100 h-9 px-3">
                                Get Directions
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="w-[95%] mx-auto overflow-hidden transition-all hover:-translate-y-2 hover:shadow-lg rounded-lg border border-gray-200">
                    <div class="h-3 bg-blue-600"></div>
                    <div class="pt-6">
                        <div class="flex flex-col items-center text-center p-4">
                            <div class="bg-blue-100 p-4 rounded-full mb-4">
                                <i data-lucide="phone" class="h-8 w-8 text-blue-600"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Call Us</h3>
                            <p class="text-gray-600 mb-4">
                                Main: +91 1234567890
                                <br>
                                Support: +91 7894561230
                                <br>
                                Fax: +91 (555) 456-7890
                            </p>
                            <button
                                class="mt-1 items-center justify-center rounded-md text-sm font-medium transition-colors border border-gray-300 bg-white hover:bg-gray-100 h-9 px-3">
                                Call Now
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="w-[95%] mx-auto overflow-hidden transition-all hover:-translate-y-2 hover:shadow-lg rounded-lg border border-gray-200">
                    <div class="h-3 bg-blue-600"></div>
                    <div class="pt-6">
                        <div class="flex flex-col items-center text-center p-4">
                            <div class="bg-blue-100 p-4 rounded-full mb-4">
                                <i data-lucide="mail" class="h-8 w-8 text-blue-600"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Email Us</h3>
                            <p class="text-gray-600 mb-4">
                                General: info@zephyrgroup.com
                                <br>
                                Support: support@zephyrgroup.com
                                <br>
                                Sales: sales@zephyrgroup.com
                            </p>
                            <button
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-gray-300 bg-white hover:bg-gray-100 h-9 px-3">
                                Email Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business hours and social media -->
        <div class="bg-gray-50 py-16">
            <div class="container mx-auto px-4">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                            <i data-lucide="clock" class="h-6 w-6 text-blue-600"></i>
                            Business Hours
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                <span class="font-medium">Monday - Friday</span>
                                <span>9:00 AM - 6:00 PM</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                <span class="font-medium">Saturday</span>
                                <span>10:00 AM - 2:00 PM</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                <span class="font-medium">Sunday</span>
                                <span>Closed</span>
                            </div>
                        </div>
                        <p class="mt-4 text-gray-600">
                            Technical support is available 24/7 for emergency situations.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-2xl font-bold mb-6">Connect With Us</h3>
                        <p class="text-gray-600 mb-6">
                            Follow us on social media for the latest updates, news, and insights about power supply
                            positioning
                            technology.
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="#"
                                class="flex items-center gap-3 p-4 bg-white rounded-lg hover:bg-blue-50 transition-colors border border-gray-200">
                                <div class="bg-blue-100 p-2 rounded-full">
                                    <i data-lucide="facebook" class="h-5 w-5 text-blue-600"></i>
                                </div>
                                <span class="font-medium">Facebook</span>
                            </a>
                            <a href="#"
                                class="flex items-center gap-3 p-4 bg-white rounded-lg hover:bg-blue-50 transition-colors border border-gray-200">
                                <div class="bg-blue-100 p-2 rounded-full">
                                    <i data-lucide="twitter" class="h-5 w-5 text-blue-600"></i>
                                </div>
                                <span class="font-medium">Twitter</span>
                            </a>
                            <a href="#"
                                class="flex items-center gap-3 p-4 bg-white rounded-lg hover:bg-blue-50 transition-colors border border-gray-200">
                                <div class="bg-blue-100 p-2 rounded-full">
                                    <i data-lucide="linkedin" class="h-5 w-5 text-blue-600"></i>
                                </div>
                                <span class="font-medium">LinkedIn</span>
                            </a>
                            <a href="#"
                                class="flex items-center gap-3 p-4 bg-white rounded-lg hover:bg-blue-50 transition-colors border border-gray-200">
                                <div class="bg-blue-100 p-2 rounded-full">
                                    <i data-lucide="instagram" class="h-5 w-5 text-blue-600"></i>
                                </div>
                                <span class="font-medium">Instagram</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map section -->
        <div class="container mx-auto px-4 py-16">
            <div class="relative rounded-xl overflow-hidden h-[400px] shadow-lg border border-gray-200">
                <div class="absolute inset-0 bg-gray-100 flex items-center justify-center">
                    <img src="./media/image/map.png" alt="">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md text-center border border-gray-200">
                            <h3 class="text-xl font-bold mb-2">Our Headquarters</h3>
                            <p class="mb-4">144111 Lovely Professional University
                                Phagwara, Punjab
                                INDIA</p>
                            <a
                                href="https://maps.app.goo.gl/9dWWij2wW6SC1ycg7" target="_blank">
                                <div
                                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-blue-600 text-white hover:bg-blue-700 h-10 px-4 py-2">
                                    Get Directions
                                </div>
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-zinc-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">

                <div class="space-y-4">
                    <img src="./media/image/weblogo.png" alt="Logo" class="w-32">
                    <p class="text-gray-400">We are specialized in designings, make your business a brand. Try our premium services.</p>
                    <div class="flex space-x-3 text-red-500 text-lg">
                        <a href="#" class="hover:scale-110 transition-transform"><i data-lucide="instagram"></i></a>
                        <a href="#" class="hover:scale-110 transition-transform"><i data-lucide="facebook"></i></a>
                        <a href="#" class="hover:scale-110 transition-transform"><i data-lucide="youtube"></i></a>
                        <a href="#" class="hover:scale-110 transition-transform"><i data-lucide="twitter"></i></a>
                        <a href="#" class="hover:scale-110 transition-transform"><i data-lucide="linkedin"></i></a>
                    </div>
                </div>
    

    
                <div>
                    <h3 class="text-red-400 text-lg font-semibold mb-4">Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.html" class="hover:text-red-400 transition">Home</a></li>
                        <li><a href="aboutus.html" class="hover:text-red-400 transition">About Us</a></li>
                        <li><a href="faq.html" class="hover:text-red-400 transition">FAQs</a></li>
                        <li><a href="tnc.html" class="hover:text-red-400 transition">Terms & Conditions</a></li>
                    </ul>
                </div>
    
                <div>
                    <h3 class="text-red-400 text-lg font-semibold mb-4">Contact</h3>
                    <div class="flex items-center space-x-3 mb-2">
                        <i class="fa fa-location text-red-400"></i>
                        <p class="text-gray-400">144111 Lovely Professional University
                                Phagwara, Punjab
                                INDIA</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa fa-phone text-red-400"></i>
                        <p class="text-gray-400">+1-8755856858</p>
                    </div>
                </div>
            </div>
    
            <div class="mt-8 flex justify-center">
                <form class="flex w-full max-w-sm">
                    <input type="text" placeholder="Email here..." class="flex-grow px-4 py-2 bg-gray-800 text-white rounded-l-md focus:outline-none">
                    <a href="contact.php"><div class="px-2 py-2 bg-pink-500 rounded-r-md text-white"><i data-lucide="send"></i></div></a>
                </form>
            </div>
        </div>
    </footer>
    

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', function () {

            document.getElementById("loginBtn").addEventListener("click", function () {
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle("hidden");
          });
            // Handle form submission
            const contactForm = document.getElementById('contact-form');
            if (contactForm) {
                contactForm.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    try {
                        const response = await fetch('', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Show success message
                            document.getElementById('form-success').classList.remove('hidden');
                            contactForm.classList.add('hidden');

                            // Reset form after 3 seconds
                            setTimeout(function () {
                                contactForm.reset();
                                document.getElementById('form-success').classList.add('hidden');
                                contactForm.classList.remove('hidden');

                                // Reset select value display
                                document.getElementById('select-value').textContent = 'Select a subject';
                                document.getElementById('select-value').classList.add('text-gray-400');
                                document.getElementById('select-value').classList.remove('text-gray-900');
                            }, 3000);
                        } else {
                            alert(result.message);
                        }
                    } catch (error) {
                        alert('An error occurred. Please try again later.');
                        console.error('Error:', error);
                    }
                });
            }

            // Custom select functionality
            const selectTrigger = document.getElementById('select-trigger');
            const selectContent = document.getElementById('select-content');
            const selectValue = document.getElementById('select-value');
            const subjectInput = document.getElementById('subject');

            if (selectTrigger && selectContent && selectValue && subjectInput) {
                selectTrigger.addEventListener('click', function () {
                    selectContent.classList.toggle('hidden');
                });

                document.querySelectorAll('#select-content [data-value]').forEach(item => {
                    item.addEventListener('click', function () {
                        const value = this.getAttribute('data-value');
                        const text = this.textContent.trim();

                        subjectInput.value = value;
                        selectValue.textContent = text;
                        selectValue.classList.remove('text-gray-400');
                        selectValue.classList.add('text-gray-900');
                        selectContent.classList.add('hidden');
                    });
                });

                // Close select when clicking outside
                document.addEventListener('click', function (e) {
                    if (!selectTrigger.contains(e.target) && !selectContent.contains(e.target)) {
                        selectContent.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>

</html>