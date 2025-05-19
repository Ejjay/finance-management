<?php
require_once 'config/database.php';
require_once 'config/google-config.php';
require_once 'vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URL);

try {
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);

        // Get user info
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $oauth_id = $google_account_info->id;
        $picture = $google_account_info->picture;

        // Initialize database connection
        $db = new Database();
        $conn = $db->getConnection('finance');

        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE oauth_id = ? AND oauth_provider = 'google'");
        $stmt->execute([$oauth_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Create new user
            $sql = "INSERT INTO users (username, email, oauth_provider, oauth_id, profile_picture, role) 
                    VALUES (?, ?, 'google', ?, ?, 'user')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $oauth_id, $picture]);
            
            $user_id = $conn->lastInsertId();
            $username = $name;
            $role = 'user';
        } else {
            $user_id = $user['user_id'];
            $username = $user['username'];
            $role = $user['role'];
        }

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['profile_picture'] = $picture;
        $_SESSION['oauth_provider'] = 'google';

        header('Location: dashboard.php');
        exit();
    }
} catch (Exception $e) {
    error_log('Google Auth Error: ' . $e->getMessage());
    $_SESSION['auth_error'] = 'Authentication failed. Please try again.';
    header('Location: index.php');
    exit();
}