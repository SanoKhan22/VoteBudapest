<?php
/**
 * Login Action Handler
 * Process user login with username and password
 * 
 * MINIMUM REQUIREMENTS:
 * - Login with username + password ✓
 * - Admin user exists (admin/admin) ✓
 * - Password verification using password_verify() ✓
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/login.php');
}

// Get form data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$redirect_to = $_POST['redirect'] ?? '/index.php';

// Validate input
if (empty($username) || empty($password)) {
    set_flash('error', 'Username and password are required');
    redirect('/pages/login.php');
}

try {
    $db = getDB();
    
    // Find user by username
    $sql = "SELECT id, username, email, password_hash, is_admin FROM users WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Check if user exists and password is correct
    if ($user && password_verify($password, $user['password_hash'])) {
        // Set user session
        set_user_session($user);
        
        // Success message
        $greeting = is_admin() ? 'Welcome back, Admin!' : 'Welcome back, ' . htmlspecialchars($username) . '!';
        set_flash('success', $greeting);
        
        // Redirect to requested page or homepage
        redirect($redirect_to);
    } else {
        // Invalid credentials
        set_flash('error', 'Invalid username or password');
        redirect('/pages/login.php');
    }
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    set_flash('error', 'Login failed. Please try again.');
    redirect('/pages/login.php');
}
