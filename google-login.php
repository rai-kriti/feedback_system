<?php
require_once 'vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);
$client->setRedirectUri('http://localhost/feedback_system/google-callback.php');
$client->addScope('email');
$client->addScope('profile');

$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();
?>