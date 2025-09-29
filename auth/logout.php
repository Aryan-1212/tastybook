<?php
/**
 * User Logout
 * Handles user logout process
 */

require_once __DIR__ . '/../includes/functions.php';

// Start session
startSecureSession();

// Clear all session data
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Redirect to home page
setFlashMessage('success', 'You have been logged out successfully.');
redirect('/TastyBook/index.php');
?>
