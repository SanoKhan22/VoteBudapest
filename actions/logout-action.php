<?php
/**
 * Logout Action Handler
 * MINIMUM REQUIREMENT: Logout works ✓
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Store flash message before destroying session
$_SESSION['flash_message'] = 'You have been logged out successfully';

// Destroy session
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'You have been logged out successfully'
];

redirect('/index.php');
