<?php
/**
 * Admin Rework Action
 * EXTRA TASK: Admin can send project for rework with comment (1.0 pt)
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
$comment = trim($_POST['comment'] ?? '');

if (!$project_id || !is_numeric($project_id)) {
    set_flash('error', 'Invalid project ID');
    redirect('/pages/admin/projects-admin.php');
}

if (empty($comment)) {
    set_flash('error', 'Comment is required when sending for rework');
    redirect('/pages/project.php?id=' . $project_id);
}

try {
    $db = getDB();
    $db->beginTransaction();
    
    // Update project status to rework
    $sql = "UPDATE projects 
            SET status_id = ? 
            WHERE id = ? AND status_id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([STATUS_REWORK, $project_id, STATUS_PENDING]);
    
    if ($stmt->rowCount() > 0) {
        // Insert admin comment
        $sql = "INSERT INTO admin_comments (project_id, admin_id, comment) 
                VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$project_id, get_user_id(), $comment]);
        
        $db->commit();
        set_flash('success', 'Project sent for rework. User will be notified.');
    } else {
        $db->rollBack();
        set_flash('error', 'Project not found or already processed');
    }
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Rework project error: " . $e->getMessage());
    set_flash('error', 'Failed to send project for rework');
}

redirect('/pages/project.php?id=' . $project_id);
