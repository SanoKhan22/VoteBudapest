<?php
/**
 * Edit Project Action Handler
 * EXTRA TASK: Handle project edits and resubmission (2.0 pts)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';

// Require login
require_login();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', 'Invalid request method');
    redirect('/pages/projects-own.php');
}

$project_id = $_POST['project_id'] ?? null;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category_id = $_POST['category_id'] ?? null;
$postal_code = trim($_POST['postal_code'] ?? '');

// Validate project ID
if (!$project_id || !is_numeric($project_id)) {
    set_flash('error', 'Invalid project ID');
    redirect('/pages/projects-own.php');
}

try {
    $db = getDB();
    
    // Get current project
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        set_flash('error', 'Project not found');
        redirect('/pages/projects-own.php');
    }
    
    // Check ownership
    if ($project['user_id'] != get_user_id()) {
        set_flash('error', 'You do not have permission to edit this project');
        redirect('/pages/projects-own.php');
    }
    
    // Check if project is in REWORK status
    if ($project['status_id'] != STATUS_REWORK) {
        set_flash('error', 'Only projects sent back for rework can be edited');
        redirect('/pages/project.php?id=' . $project_id);
    }
    
    // Validate inputs
    $errors = [];
    
    // Title validation
    if (empty($title)) {
        $errors[] = "Title is required";
    } elseif (strlen($title) < 10) {
        $errors[] = "Title must be at least 10 characters";
    }
    
    // Description validation
    if (empty($description)) {
        $errors[] = "Description is required";
    } elseif (strlen($description) < 150) {
        $errors[] = "Description must be at least 150 characters";
    }
    
    // Category validation
    if (empty($category_id) || !is_numeric($category_id)) {
        $errors[] = "Please select a valid category";
    } else {
        // Check if category exists
        $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        if (!$stmt->fetch()) {
            $errors[] = "Invalid category selected";
        }
    }
    
    // Postal code validation
    $postal_validation = validate_postal_code($postal_code);
    if ($postal_validation !== true) {
        $errors[] = $postal_validation;
    }
    
    // Image upload handling (optional)
    $new_image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image_validation = validate_image_upload($_FILES['image']);
        if ($image_validation !== true) {
            $errors[] = $image_validation;
        } else {
            // Process image upload
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid('project_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $new_image_path = 'uploads/' . $new_filename;
                
                // Delete old image if exists
                if ($project['image_path'] && file_exists(__DIR__ . '/../' . $project['image_path'])) {
                    unlink(__DIR__ . '/../' . $project['image_path']);
                }
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    // If there are validation errors, redirect back
    if (!empty($errors)) {
        set_flash('error', implode('<br>', $errors));
        redirect('/pages/edit-project.php?id=' . $project_id);
    }
    
    // Update project
    $sql = "UPDATE projects SET 
            title = ?, 
            description = ?, 
            category_id = ?, 
            postal_code = ?,
            status_id = ?,
            approved_at = NULL,
            updated_at = NOW()";
    
    $params = [$title, $description, $category_id, $postal_code, STATUS_PENDING];
    
    // Add image path to update if new image uploaded
    if ($new_image_path) {
        $sql .= ", image_path = ?";
        $params[] = $new_image_path;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $project_id;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    set_flash('success', 'Project updated and resubmitted for review successfully!');
    redirect('/pages/project.php?id=' . $project_id);
    
} catch (PDOException $e) {
    error_log("Edit project error: " . $e->getMessage());
    set_flash('error', 'Database error occurred');
    redirect('/pages/edit-project.php?id=' . $project_id);
}
