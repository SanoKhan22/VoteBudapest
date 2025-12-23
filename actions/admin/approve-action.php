<?php
/**
 * Admin Approve Action
 * CORE TASK: Admin can publish (approve) pending projects (1.0 pt)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require admin access
require_admin();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

$project_id = $_POST['project_id'] ?? null;

if (!$project_id || !is_numeric($project_id)) {
    set_flash('error', 'Invalid project ID');
    redirect('/pages/admin/projects-admin.php');
}

try {
    $db = getDB();
    
    // Update project status to approved and set approval date
    $sql = "UPDATE projects 
            SET status_id = ?, approved_at = NOW() 
            WHERE id = ? AND status_id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([STATUS_APPROVED, $project_id, STATUS_PENDING]);
    
    if ($stmt->rowCount() > 0) {
        set_flash('success', 'Project approved successfully and published for voting!');
    } else {
        set_flash('error', 'Project not found or already processed');
    }
    
} catch (PDOException $e) {
    error_log("Approve project error: " . $e->getMessage());
    set_flash('error', 'Failed to approve project');
}

redirect('/pages/admin/projects-admin.php');
