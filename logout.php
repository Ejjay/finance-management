<?php
session_start();
require_once 'config/google-config.php';
require_once 'vendor/autoload.php';

if (isset($_SESSION['oauth_provider']) && $_SESSION['oauth_provider'] === 'google') {
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    
    if (isset($_SESSION['access_token'])) {
        $client->revokeToken($_SESSION['access_token']);
    }
}

session_destroy();
header('Location: index.php');
exit();