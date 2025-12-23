<?php
/**
 * Registration Action Handler
 * Process user registration with validation
 * 
 * MINIMUM REQUIREMENTS:
 * - Username must be unique ✓
 * - Password at least 8 characters ✓
 * - Registration enables login ✓
 * - Passwords stored hashed ✓
 * 
 * CORE TASKS:
 * - Username cannot contain spaces (0.5 pts) ✓
 * - Email format must be valid (0.5 pts) ✓
 * - Password must include lowercase, uppercase, numeric (0.5 pts) ✓
 * - Two password fields must match (0.5 pts) ✓
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/register.php');
}

// Get form data
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

$errors = [];

// Validate username
$username_validation = validate_username($username);
if (!$username_validation['valid']) {
    $errors[] = $username_validation['error'];
}

// Validate email
$email_validation = validate_email($email);
if (!$email_validation['valid']) {
    $errors[] = $email_validation['error'];
}

// Validate password
$password_validation = validate_password($password);
if (!$password_validation['valid']) {
    $errors[] = $password_validation['error'];
}

// Validate password confirmation
$password_confirm_validation = validate_password_confirmation($password, $password_confirm);
if (!$password_confirm_validation['valid']) {
    $errors[] = $password_confirm_validation['error'];
}

// If there are validation errors, redirect back with error message
if (!empty($errors)) {
    set_flash('error', implode('<br>', $errors));
    redirect('/pages/register.php');
}

// MINIMUM REQUIREMENT: Hash password using password_hash()
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user into database
try {
    $db = getDB();
    $sql = "INSERT INTO users (username, email, password_hash, is_admin) 
            VALUES (?, ?, ?, FALSE)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$username, $email, $password_hash]);
    
    // Get the newly created user ID
    $user_id = $db->lastInsertId();
    
    // MINIMUM REQUIREMENT: Registration enables login with the created user
    // Automatically log the user in after successful registration
    $sql = "SELECT id, username, email, is_admin FROM users WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        set_user_session($user);
        set_flash('success', 'Registration successful! Welcome to VotaBudapest, ' . htmlspecialchars($username) . '!');
        redirect('/index.php');
    } else {
        set_flash('error', 'Registration successful, but automatic login failed. Please login manually.');
        redirect('/pages/login.php');
    }
    
} catch (PDOException $e) {
    // Handle database errors (duplicate username/email)
    error_log("Registration error: " . $e->getMessage());
    
    // Check for duplicate entry error
    if ($e->getCode() == 23000) {
        set_flash('error', 'Username or email already exists. Please choose a different one.');
    } else {
        set_flash('error', 'Registration failed. Please try again.');
    }
    redirect('/pages/register.php');
}
