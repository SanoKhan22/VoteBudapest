<?php
/**
 * Session Management
 * Initialize and manage user sessions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 * 
 * @return bool True if user is admin
 */
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * 
 * @return string|null Username or null if not logged in
 */
function get_username() {
    return $_SESSION['username'] ?? null;
}

/**
 * Set user session after successful login
 * 
 * @param array $user User data from database
 */
function set_user_session($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = (bool)$user['is_admin'];
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
}

/**
 * Destroy user session (logout)
 */
function destroy_session() {
    $_SESSION = [];
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Require login - redirect to login page if not logged in
 * 
 * @param string $redirect_to URL to redirect after login
 */
function require_login($redirect_to = null) {
    if (!is_logged_in()) {
        $redirect = $redirect_to ?? $_SERVER['REQUEST_URI'];
        header('Location: /pages/login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

/**
 * Require admin - redirect to homepage if not admin
 */
function require_admin() {
    require_login();
    
    if (!is_admin()) {
        header('Location: /index.php');
        exit;
    }
}

/**
 * Redirect guest to homepage if already logged in
 */
function redirect_if_logged_in() {
    if (is_logged_in()) {
        header('Location: /index.php');
        exit;
    }
}
