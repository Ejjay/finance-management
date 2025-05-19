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
        
        // Add debug logging
        error_log('Token received: ' . print_r($token, true));
        
        $client->setAccessToken($token);

        // Get user info
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        // Add debug logging
        error_log('Google account info: ' . print_r($google_account_info, true));
        
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $oauth_id = $google_account_info->id;
        $picture = $google_account_info->picture;

        // Initialize database connection
        $db = new Database();
        $conn = $db->getConnection('finance');

        // Test database connection
        try {
            $testStmt = $conn->query("SELECT 1");
            error_log('Database connection test successful');
        } catch (PDOException $e) {
            error_log('Database connection test failed: ' . $e->getMessage());
        }

        // Add debug logging before checking if user exists
        error_log('Checking if user exists in the database');
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE oauth_id = ? AND oauth_provider = 'google'");
        $stmt->execute([$oauth_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Add debug logging for new user creation
            error_log('User not found, creating a new user entry');
            
            // Create new user
            $sql = "INSERT INTO users (username, email, oauth_provider, oauth_id, profile_picture, role) 
                    VALUES (?, ?, 'google', ?, ?, 'user')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $oauth_id, $picture]);
            
            $user_id = $conn->lastInsertId();
            $username = $name;
            $role = 'user';
        } else {
            // Add debug logging for existing user
            error_log('User found in the database');
            
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
    error_log('Google Auth Error Details: ' . $e->getMessage());
    error_log('Error Line: ' . $e->getLine());
    error_log('Error File: ' . $e->getFile());
    error_log('Error Trace: ' . $e->getTraceAsString());
    $_SESSION['auth_error'] = 'Authentication failed: ' . $e->getMessage();
    header('Location: index.php');
    exit();
}