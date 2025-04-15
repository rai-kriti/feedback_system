<?php
require_once 'vendor/autoload.php';
include 'config.php';
$config = require 'keys.php';
session_start();

$client = new Google_Client();
$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);
$client->setRedirectUri('http://localhost/feedback_system/google-callback.php');
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $google_id = $google_account_info->id;

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login existing user
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: user_dashboard.php");
    } else {
        // Register new user
        $username = strtolower(str_replace(' ', '', $name)) . rand(100, 999);
        $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, google_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $name, $email, $password, $google_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['role'] = 'user';
            header("Location: user_dashboard.php");
        } else {
            die("Registration failed");
        }
    }
    exit();
}

if (isset($_GET['error'])) {
    header("Location: login.php");
    exit();
}

$google_login_url = $client->createAuthUrl();