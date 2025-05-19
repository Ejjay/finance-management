<?php
require_once 'config/database.php';
require_once 'config/google-config.php';
require_once 'vendor/autoload.php';

session_start();

try {
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URL);
    $client->addScope("email");
    $client->addScope("profile");

    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} catch (Exception $e) {
    error_log('Google Login Error: ' . $e->getMessage());
    $_SESSION['auth_error'] = 'Authentication failed: ' . $e->getMessage();
    header('Location: index.php');
    exit();
}